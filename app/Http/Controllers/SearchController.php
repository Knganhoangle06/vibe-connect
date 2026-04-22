<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Friendship;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $authId = Auth::id();

        // Bắt từ khóa từ form của giao diện Reaction (name="q")
        $query = $request->input('q') ?? $request->input('query');

        // Dữ liệu Sidebar (bảo toàn cấu trúc giao diện)
        $friendIds = Friendship::where('status', 'accepted')
            ->where(function ($q) use ($authId) {
                $q->where('sender_id', $authId)->orWhere('receiver_id', $authId);
            })
            ->get()
            ->map(function ($f) use ($authId) {
                return $f->sender_id === $authId ? $f->receiver_id : $f->sender_id;
            });
        $friends = User::whereIn('id', $friendIds)->get();
        $pendingRequests = Friendship::with('sender')->where('receiver_id', $authId)->where('status', 'pending')->latest()->get();

        // Xử lý tìm kiếm
        if (!$query) {
            return view('search.index', [
                'users' => collect(),
                'posts' => collect(),
                'query' => '',
                'friends' => $friends,
                'pendingRequests' => $pendingRequests
            ]);
        }

        // 1. Tìm người dùng
        $users = User::where('name', 'like', "%{$query}%")->get();

        // 2. Tìm bài viết
        $posts = Post::with(['user', 'comments.user', 'reactions', 'originalPost.user'])
            ->where('content', 'like', "%{$query}%")
            ->latest()
            ->get();

        return view('search.index', compact('users', 'posts', 'query', 'friends', 'pendingRequests'));
    }
}
