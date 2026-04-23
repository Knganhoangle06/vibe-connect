<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendshipRemoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $targetId;

    public function __construct($userId, $targetId)
    {
        $this->userId = $userId;
        $this->targetId = $targetId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->targetId);
    }
}
