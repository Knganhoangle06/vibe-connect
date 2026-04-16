<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PostController extends Controller
{
    public function homepage(): View
    {
        $authId = Auth::id();

        $friendIds = Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($authId) {
                $query->where('sender_id', $authId)->orWhere('receiver_id', $authId);
            })
            ->get()
            ->map(function ($friendship) use ($authId) {
                return $friendship->sender_id === $authId ? $friendship->receiver_id : $friendship->sender_id;
            })
            ->values();

        $friends = User::query()->whereIn('id', $friendIds)->get();

        $pendingRequests = Friendship::query()
            ->with('sender')
            ->where('receiver_id', $authId)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $visibleAuthorIds = $friendIds->toBase()->all();
        $visibleAuthorIds[] = $authId;

        $posts = Post::query()
            ->with([
                'user',
                'originalPost.user',
                'originalPost.reactions',
                'originalPost.comments.user',
                'comments' => function ($query) {
                    $query->latest();
                },
                'comments.user',
                'comments.replies.user',
                'reactions',
                'reactions.user',
            ])
            ->whereIn('user_id', array_unique($visibleAuthorIds))
            ->latest()
            ->paginate(10);

        return view('page.homepage', compact('posts', 'friends', 'pendingRequests'));
    }


    // Hiển thị News Feed
    public function index()
    {
        $posts = Post::with(['user', 'originalPost.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('posts.index', compact('posts'));
    }

    // Xử lý đăng bài mới
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
            'media_url' => 'nullable|string|max:2048',
            'media_type' => 'nullable|in:image,video'
        ]);

        // Không được đăng bài trống hoàn toàn
        if (!$request->filled('content') && !$request->filled('media_url')) {
            return back()->withErrors(['content' => 'Bài viết không được để trống.']);
        }

        Post::create([
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'media_url' => $request->media_url,
            'media_type' => $request->media_type,
        ]);

        return redirect()->route('home')->with('success', 'Đăng bài thành công!');
    }

    // Hiển thị Form sửa bài viết
    public function edit(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa bài viết này.');
        }

        return view('posts.edit', compact('post'));
    }

    // Xử lý cập nhật bài viết
    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['content' => 'required|string']);

        $post->update(['content' => $request->input('content'),]);

        return redirect()->route('home')->with('success', 'Cập nhật bài viết thành công!');
    }

    // Xử lý xóa bài viết
    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        $post->delete();

        return back()->with('success', 'Xóa bài viết thành công!');
    }

    // Xử lý chia sẻ
    public function share(Request $request, Post $post)
    {
        $request->validate(['content' => 'nullable|string']);

        $originalPostId = $post->original_post_id ?? $post->id;

        Post::create([
            'user_id' => Auth::id(),
            'original_post_id' => $originalPostId,
           'content' => $request->input('content'),
        ]);

        return redirect()->route('home')->with('success', 'Chia sẻ bài viết thành công!');
    }

    public function toggleReaction(Request $request, Post $post)
    {
        $request->validate([
            'type' => 'nullable|in:like,love,haha,wow,sad,angry',
        ]);

        $type = $request->input('type', 'like');
        $userId = Auth::id();

        $reaction = Reaction::query()
            ->where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($reaction) {
            if ($reaction->type === $type) {
                $reaction->delete();
                return back()->with('success', 'Đã bỏ cảm xúc bài viết.');
            }

            $reaction->update(['type' => $type]);
            return back()->with('success', 'Đã đổi cảm xúc bài viết.');
        }

        Reaction::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'type' => $type,
        ]);

        return back()->with('success', 'Đã thả cảm xúc bài viết.');
    }
}
