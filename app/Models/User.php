<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Các thuộc tính được phép gán hàng loạt (Mass Assignment).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bio',
        'avatar',
    ];

    /**
     * Các thuộc tính cần được ẩn đi khi model chuyển thành mảng hoặc JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Định nghĩa kiểu dữ liệu (Casting) chuẩn Laravel 11.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS (Giữ nguyên cấu trúc của team)
    // =========================================================================

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function sentFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'sender_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'receiver_id');
    }

    public function conversationParticipants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants', 'user_id', 'conversation_id')
            ->withPivot('joined_at');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
