<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('q', ''));

        $users = User::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->where('id', '!=', Auth::id())
            ->limit(20)
            ->get();

        $friendships = Friendship::query()
            ->where(function ($query) {
                $query->where('sender_id', Auth::id())->orWhere('receiver_id', Auth::id());
            })
            ->get();

        $friendshipMap = $friendships->mapWithKeys(function ($friendship) {
            $otherId = $friendship->sender_id === Auth::id() ? $friendship->receiver_id : $friendship->sender_id;
            return [$otherId => $friendship];
        });

        $posts = Post::query()
            ->with('user')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('content', 'like', '%' . $keyword . '%');
            })
            ->latest()
            ->limit(20)
            ->get();

        return view('search.index', compact('keyword', 'users', 'posts', 'friendshipMap'));
    }
}
