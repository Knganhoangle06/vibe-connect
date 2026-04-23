<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FriendshipController extends Controller
{
    public function send(User $user)
    {
        $authId = Auth::id();

        if ($user->id === $authId) {
            return request()->wantsJson()
                ? response()->json(['error' => 'Không thể kết bạn với chính bạn.'], 400)
                : back()->with('error', 'Không thể kết bạn với chính bạn.');
        }

        $existing = Friendship::query()
            ->where(function ($query) use ($authId, $user) {
                $query->where('sender_id', $authId)->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($authId, $user) {
                $query->where('sender_id', $user->id)->where('receiver_id', $authId);
            })
            ->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                return request()->wantsJson()
                    ? response()->json(['error' => 'Hai bạn đã là bạn bè.'], 400)
                    : back()->with('error', 'Hai bạn đã là bạn bè.');
            }

            if ($existing->sender_id === $authId) {
                return request()->wantsJson()
                    ? response()->json(['error' => 'Bạn đã gửi lời mời trước đó.'], 400)
                    : back()->with('error', 'Bạn đã gửi lời mời trước đó.');
            }

            $existing->update(['status' => 'accepted']);

            return request()->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Đã chấp nhận lời mời kết bạn.'])
                : back()->with('success', 'Đã chấp nhận lời mời kết bạn.');
        }

        Friendship::create([
            'sender_id' => $authId,
            'receiver_id' => $user->id,
            'status' => 'pending',
        ]);

        // Phát sự kiện qua WebSockets cho người nhận
        broadcast(new \App\Events\FriendRequestSent(Auth::user(), $user->id))->toOthers();

        return request()->wantsJson()
            ? response()->json(['success' => true, 'message' => 'Đã gửi lời mời kết bạn.'])
            : back()->with('success', 'Đã gửi lời mời kết bạn.');
    }

    public function accept(User $user)
    {
        $friendship = Friendship::query()
            ->where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->update(['status' => 'accepted']);

        // Phát sóng sự kiện kết bạn thành công cho cả 2 người
        $sender = $user;
        $acceptor = Auth::user();
        broadcast(new \App\Events\FriendRequestAccepted($sender, $acceptor))->toOthers();

        return request()->wantsJson()
            ? response()->json(['success' => true, 'message' => 'Đã chấp nhận lời mời kết bạn.'])
            : back()->with('success', 'Đã chấp nhận lời mời kết bạn.');
    }

    public function remove(User $user)
    {
        Friendship::query()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', Auth::id())->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)->where('receiver_id', Auth::id());
            })
            ->delete();

        return back()->with('success', 'Đã hủy kết nối bạn bè.');
    }

    public function friendRequests()
    {
        $userId = Auth::id();

        $requests = \App\Models\Friendship::where('receiver_id', $userId)
            ->where('status', 'pending')
            ->with('sender')
            ->get();

        // ĐÃ CHỈNH SỬA: Trả về view 'friends.index' theo cấu trúc chuẩn
        return view('friends.index', compact('requests'));
    }
}
