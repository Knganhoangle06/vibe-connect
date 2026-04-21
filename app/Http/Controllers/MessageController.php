<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $authId = Auth::id();
        $friends = $this->acceptedFriends($authId);
        $selectedConversation = null;
        $messages = collect();

        if ($request->filled('friend_id')) {
            $friendId = (int) $request->input('friend_id');
            $isFriend = $friends->contains('id', $friendId);

            if ($isFriend) {
                $selectedConversation = $this->findOrCreateConversation($authId, $friendId);
                $messages = Message::query()
                    ->with('sender')
                    ->where('conversation_id', $selectedConversation->id)
                    ->oldest()
                    ->get();
            }
        }

        return view('messages.index', [
            'friends' => $friends,
            'selectedConversation' => $selectedConversation,
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, Conversation $conversation): RedirectResponse
    {
        $authId = Auth::id();

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $isInConversation = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $authId)
            ->exists();

        if (! $isInConversation) {
            return back()->with('error', 'Bạn không có quyền gửi vào cuộc trò chuyện này.');
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $authId,
            'content' => $request->input('content'),
        ]);

        $conversation->touch();

        $friendId = $conversation->users()->where('users.id', '!=', $authId)->value('users.id');

        return redirect()->route('messages.index', ['friend_id' => $friendId])
            ->with('success', 'Đã gửi tin nhắn.');
    }

    private function acceptedFriends(int $authId)
    {
        $friendIds = Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($authId) {
                $query->where('sender_id', $authId)->orWhere('receiver_id', $authId);
            })
            ->get()
            ->map(function ($friendship) use ($authId) {
                return $friendship->sender_id === $authId ? $friendship->receiver_id : $friendship->sender_id;
            })
            ->unique()
            ->values();

        return User::query()
            ->whereIn('id', $friendIds)
            ->where('is_locked', false)
            ->orderBy('name')
            ->get();
    }

    private function findOrCreateConversation(int $authId, int $friendId): Conversation
    {
        $conversation = Conversation::query()
            ->whereHas('users', fn ($query) => $query->where('users.id', $authId))
            ->whereHas('users', fn ($query) => $query->where('users.id', $friendId))
            ->withCount('users')
            ->get()
            ->first(fn ($item) => $item->users_count === 2);

        if ($conversation) {
            return $conversation;
        }

        $conversation = Conversation::create();

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $authId,
            'joined_at' => now(),
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $friendId,
            'joined_at' => now(),
        ]);

        return $conversation;
    }
}
