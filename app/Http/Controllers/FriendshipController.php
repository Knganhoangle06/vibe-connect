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
            return back()->with('error', 'Không thể kết bạn với chính bạn.');
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
                return back()->with('error', 'Hai bạn đã là bạn bè.');
            }

            if ($existing->sender_id === $authId) {
                return back()->with('error', 'Bạn đã gửi lời mời trước đó.');
            }

            // Nếu phía bên kia đã gửi trước, auto chấp nhận luôn.
            $existing->update(['status' => 'accepted']);
            return back()->with('success', 'Đã chấp nhận lời mời kết bạn.');
        }

        Friendship::create([
            'sender_id' => $authId,
            'receiver_id' => $user->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Đã gửi lời mời kết bạn.');
    }

    public function accept(User $user)
    {
        $friendship = Friendship::query()
            ->where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->update(['status' => 'accepted']);

        return back()->with('success', 'Đã chấp nhận lời mời kết bạn.');
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
    $userId = Auth::id(); // Lấy ID của bạn

    // Lấy các lời mời mà bạn là người nhận và trạng thái là đang chờ
    $requests = \App\Models\Friendship::where('receiver_id', $userId)
        ->where('status', 'pending')
        ->with('sender') // Lấy thông tin người gửi lời mời
        ->get();

    return view('page.friends', compact('requests'));
}
}
