<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\MessageDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index($conversationId = null)
    {
        $userId = Auth::id();

        $friendIds = Friendship::where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->get()
            ->map(function ($f) use ($userId) {
                return $f->sender_id === $userId ? $f->receiver_id : $f->sender_id;
            });

        $friends = $friendIds->isNotEmpty() ? User::whereIn('id', $friendIds)->get() : collect();

        // Chỉ lấy những conversation mà user hiện tại CHƯA XÓA (tức là còn tồn tại trong bảng pivot)
        $conversations = Auth::user()->conversations()->with(['users', 'messages' => function($q) {
            $q->latest()->limit(1);
        }])->get();

        $activeConversation = null;
        $messages = collect();

        if ($conversationId) {
            $activeConversation = Auth::user()->conversations()->where('conversations.id', $conversationId)->firstOrFail();
            $messages = $activeConversation->messages()->with('sender')->oldest()->get();
        }

        return view('messages.index', compact('conversations', 'friends', 'activeConversation', 'messages'));
    }

    public function store(Request $request, Conversation $conversation)
    {
        $request->validate(['content' => 'required|string']);

        if (!$conversation->users()->where('users.id', Auth::id())->exists()) {
            abort(403);
        }

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'content' => $request->content
        ]);

        broadcast(new MessageSent($message))->toOthers();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back();
    }

    public function createConversation(User $user)
    {
        $authId = Auth::id();

        $conversation = Auth::user()->conversations()
            ->whereHas('users', function($q) use ($user) { $q->where('users.id', $user->id); })
            ->whereHas('users', function($q) use ($authId) { $q->where('users.id', $authId); }, '=', 2)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->participants()->createMany([
                ['user_id' => $authId],
                ['user_id' => $user->id]
            ]);
        }

        return redirect()->route('messages.index', $conversation->id);
    }

    // 1. TÍNH NĂNG THU HỒI TIN NHẮN
    public function destroyMessage(Message $message)
    {
        // Chỉ người gửi mới được xóa
        if ($message->sender_id !== Auth::id()) {
            return response()->json(['success' => false], 403);
        }

        $messageId = $message->id;
        $conversationId = $message->conversation_id;

        $message->delete();

        // Phát sóng cho người kia để ẩn tin nhắn
        broadcast(new MessageDeleted($messageId, $conversationId))->toOthers();

        return response()->json(['success' => true]);
    }

    // 2. TÍNH NĂNG XÓA ĐOẠN CHAT CỦA BẢN THÂN
    public function destroyConversation(Conversation $conversation)
    {
        // Xóa liên kết (Participant) của chính mình, không đụng tới của người kia
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['success' => true]);
    }
}
