<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('query', '');

        // 1. Tìm kiếm người dùng (Loại trừ chính mình)
        $users = User::where('name', 'LIKE', "%{$keyword}%")
            ->where('id', '!=', Auth::id())
            ->get();

        // 2. Tìm kiếm bài viết (Kèm theo Eager Loading để tái sử dụng giao diện Feed)
        $posts = Post::with([
                'user',
                'originalPost.user',
                'reactions',
                'comments' => function ($query) {
                    $query->latest()->limit(1);
                },
                'comments.user'
            ])
            ->where('content', 'LIKE', "%{$keyword}%")
            ->latest()
            ->get();

        return view('search.index', compact('users', 'posts', 'keyword'));
    }
}
