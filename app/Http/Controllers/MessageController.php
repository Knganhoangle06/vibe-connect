<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Friendship;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * TẠO HOẶC MỞ PHÒNG CHAT 1-1
     */
    public function createConversation(User $user)
    {
        $authId = Auth::id();

        if ($user->id === $authId) {
            return back()->with('error', 'Không thể tự nhắn tin cho chính mình.');
        }

        // Tìm đoạn chat 1-1 đã tồn tại giữa Auth::user và $user
        $conversation = Auth::user()->conversations()
            ->whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->has('users', '=', 2)
            ->first();

        // Nếu chưa từng chat, tạo phòng mới và gộp 2 người vào
        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$authId, $user->id]);
        }

        // Chuyển hướng thẳng vào phòng chat
        return redirect()->route('messages.index', $conversation->id);
    }

    /**
     * HIỂN THỊ GIAO DIỆN CHAT
     */
    public function index($conversationId = null)
    {
        $authId = Auth::id();

        // Lấy ID từ route parameters nếu tên biến không khớp (vd: {id} thay vì {conversationId})
        if (!$conversationId) {
            $params = request()->route()->parameters();
            if (!empty($params)) {
                $conversationId = array_values($params)[0];
            } else {
                // Fallback hỗ trợ truyền dạng query string (vd: /messages?1)
                $conversationId = request()->query('id') ?? request()->query('conversation') ?? request()->query('conversationId');
                if (!$conversationId && count(request()->query()) > 0) {
                    $firstKey = array_keys(request()->query())[0];
                    if (is_numeric($firstKey)) {
                        $conversationId = $firstKey;
                    }
                }
            }
        }

        // 1. Lấy danh sách bạn bè an toàn (Dùng thẳng Friendship Model để tránh lỗi Null)
        $friendIds = Friendship::where('status', 'accepted')
            ->where(function ($q) use ($authId) {
                $q->where('sender_id', $authId)->orWhere('receiver_id', $authId);
            })
            ->get()
            ->map(function ($f) use ($authId) {
                return $f->sender_id === $authId ? $f->receiver_id : $f->sender_id;
            });

        $friends = User::whereIn('id', $friendIds)->get();

        // 2. Lấy danh sách các hộp thoại đã từng chat
        $conversations = Auth::user()->conversations()->with(['users', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }])->get();

        // 3. Khởi tạo dữ liệu cho phòng chat đang mở
        $activeConversation = null;
        $messages = collect();

        if ($conversationId) {
            $activeConversation = Auth::user()->conversations()->findOrFail($conversationId);
            $messages = $activeConversation->messages()->with('sender')->oldest()->get();
        }

        return view('messages.index', compact('conversations', 'friends', 'activeConversation', 'messages'));
    }

    /**
     * GỬI TIN NHẮN VÀ PHÁT SÓNG WEBSOCKETS
     */
    public function store(Request $request, Conversation $conversation)
    {
        $request->validate(['content' => 'required|string']);

        // Xác thực người gửi có trong phòng chat này không
        if (!$conversation->users()->where('users.id', Auth::id())->exists()) {
            abort(403);
        }

        // Lưu tin nhắn
        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'content' => $request->content
        ]);

        // Phát sóng cho người kia qua Reverb (Logic chuẩn của Friendships)
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['success' => true, 'message' => $message]);
    }
}
