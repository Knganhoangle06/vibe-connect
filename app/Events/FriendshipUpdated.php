<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendshipUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $senderId;
    public $receiverId;
    public $status;
    public $sender;
    public $receiver;

    public function __construct($senderId, $receiverId, $status)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->status = $status;
        $this->sender = \App\Models\User::find($senderId);
        $this->receiver = \App\Models\User::find($receiverId);
    }

    public function broadcastOn(): array
    {
        // Phát tín hiệu cho kênh của cả 2 user để đồng bộ trạng thái
        return [
            new Channel('user.' . $this->senderId),
            new Channel('user.' . $this->receiverId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'FriendshipUpdated';
    }
}
