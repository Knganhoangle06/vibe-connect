<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    // Quan hệ: Một bài đăng thuộc về một người dùng
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ: Một bài share trỏ về bài đăng gốc (Self-referencing)
    public function originalPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'original_post_id');
    }
}
