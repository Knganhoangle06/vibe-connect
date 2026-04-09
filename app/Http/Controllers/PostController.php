<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        // Lấy bài đăng mới nhất kèm thông tin người dùng và bài viết gốc
        $posts = Post::with(['user', 'originalPost.user'])
                     ->latest()
                     ->get();

        return view('page.homepage', compact('posts'));
    }
}