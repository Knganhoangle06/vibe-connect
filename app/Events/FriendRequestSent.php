<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sender;
    public $receiverId;

    public function __construct(User $sender, $receiverId)
    {
        $this->sender = $sender;
        $this->receiverId = $receiverId;
    }

    /**
     * Kênh cá nhân, chỉ người nhận có ID tương ứng mới được phép lắng nghe
     */
    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->receiverId);
    }
}
