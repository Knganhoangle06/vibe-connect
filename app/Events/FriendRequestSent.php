<?php

namespace App\Events;

use App\Models\Friendship;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $friendship;

    public function __construct(Friendship $friendship)
    {
        // Load thông tin người gửi để hiển thị lên sidebar người nhận
        $this->friendship = $friendship->load('sender');
    }

    public function broadcastOn(): array
    {
        // Phát tín hiệu lên kênh riêng của người nhận lời mời
        // Định dạng kênh: user.{id_nguoi_nhan}
        return [
            new Channel('user.' . $this->friendship->receiver_id)
        ];
    }

    public function broadcastAs(): string
    {
        return 'FriendRequestSent';
    }
}
