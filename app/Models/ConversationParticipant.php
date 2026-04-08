<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    protected $guarded = [];

    // Tắt auto-increment vì sử dụng Composite Primary Key
    public $incrementing = false;

    // Không thiết lập created_at, update_at
    public $timestamps = false;

    public function conversation() {
        return $this->belongsTo(Conversation::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
