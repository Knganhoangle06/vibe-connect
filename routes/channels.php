<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// KÊNH PRESENCE: Kích hoạt khi có user online/offline
Broadcast::channel('online', function ($user) {
    if (auth()->check()) {
        return ['id' => $user->id, 'name' => $user->name];
    }
});

// KÊNH PRIVATE: Chỉ cho phép người có trong đoạn chat được nghe ngóng tin nhắn
Broadcast::channel('conversation.{id}', function ($user, $id) {
    $conversation = Conversation::find($id);
    if (!$conversation) {
        return false;
    }
    return $conversation->users->contains($user->id);
});
