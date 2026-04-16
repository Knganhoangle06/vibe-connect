<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use App\Events\FriendRequestSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendshipController extends Controller
{
    /**
     * Gửi lời mời kết bạn và phát tín hiệu Real-time.
     */
    public function add(User $user)
    {
        // 1. Chặn hành vi tự kết bạn với chính mình
        if (Auth::id() === $user->id) {
            return back()->withErrors(['friendship' => 'Bạn không thể tự kết bạn với chính mình.']);
        }

        // 2. Kiểm tra xem đã tồn tại mối quan hệ nào chưa (đã gửi hoặc đã là bạn)
        $existing = Friendship::where(function ($query) use ($user) {
            $query->where('sender_id', Auth::id())->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)->where('receiver_id', Auth::id());
        })->first();

        if ($existing) {
            return back()->withErrors(['friendship' => 'Yêu cầu đã tồn tại hoặc hai người đã là bạn bè.']);
        }

        // 3. Tạo lời mời kết bạn mới
        $friendship = Friendship::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $user->id,
            'status' => 'pending'
        ]);

        // 4. PHÁT TÍN HIỆU REAL-TIME (Broadcasting)
        // Gửi thông báo đến người nhận qua Laravel Reverb
        broadcast(new FriendRequestSent($friendship));
        broadcast(new \App\Events\FriendshipUpdated(Auth::id(), $user->id, 'pending'));

        return back()->with('success', 'Đã gửi lời mời kết bạn!');
    }

    /**
     * Chấp nhận lời mời kết bạn.
     */
    public function accept(User $user)
    {
        $friendship = Friendship::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return back()->withErrors(['friendship' => 'Không tìm thấy lời mời kết bạn này.']);
        }

        $friendship->update(['status' => 'accepted']);
        broadcast(new \App\Events\FriendshipUpdated($user->id, Auth::id(), 'accepted'));

        return back()->with('success', 'Đã chấp nhận lời mời kết bạn!');
    }

    /**
     * Từ chối lời mời kết bạn.
     */
    public function decline(User $user)
    {
        $friendship = Friendship::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if ($friendship) {
            $friendship->delete();
            broadcast(new \App\Events\FriendshipUpdated($user->id, Auth::id(), 'declined'));
        }

        return back()->with('success', 'Đã từ chối lời mời kết bạn.');
    }

    /**
     * Hủy kết bạn hoặc thu hồi lời mời.
     */
    public function remove(User $user)
    {
        $friendship = Friendship::where(function ($query) use ($user) {
            $query->where('sender_id', Auth::id())->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)->where('receiver_id', Auth::id());
        })->first();

        if ($friendship) {
            $friendship->delete();
            broadcast(new \App\Events\FriendshipUpdated($friendship->sender_id, $friendship->receiver_id, 'removed'));
        }

        return back()->with('success', 'Thao tác thành công.');
    }
}
