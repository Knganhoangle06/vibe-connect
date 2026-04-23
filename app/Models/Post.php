<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function originalPost()
    {
        return $this->belongsTo(Post::class, 'original_post_id');
    }

    public function shares()
    {
        return $this->hasMany(Post::class, 'original_post_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function media()
    {
        return $this->hasMany(PostMedia::class);
    }

    protected static function booted(): void
    {
        static::deleting(function ($post) {
            foreach ($post->media as $media_item) {
                Storage::disk('public')->delete($media_item->file_path);
            }
        });
    }
}
