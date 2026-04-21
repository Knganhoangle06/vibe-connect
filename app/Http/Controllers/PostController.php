<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use App\Models\Friendship;
use App\Models\User;
use App\Events\PostCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    /**
     * HIỂN THỊ BẢNG TIN CÔNG CỘNG
     * Lấy toàn bộ bài viết từ Database thay vì chỉ lấy của bạn bè.
     */
    public function home(): View
    {
        $authId = Auth::id();

        // Vẫn lấy danh sách bạn bè và lời mời để hiển thị Sidebar Right (Bảo toàn friendships)
        $friendIds = Friendship::where('status', 'accepted')
            ->where(function ($query) use ($authId) {
                $query->where('sender_id', $authId)->orWhere('receiver_id', $authId);
            })
            ->get()
            ->map(function ($friendship) use ($authId) {
                return $friendship->sender_id === $authId ? $friendship->receiver_id : $friendship->sender_id;
            });

        $friends = User::whereIn('id', $friendIds)->get();

        $pendingRequests = Friendship::with('sender')
            ->where('receiver_id', $authId)
            ->where('status', 'pending')
            ->latest()
            ->get();

        // CHỈNH SỬA: Lấy toàn bộ bài viết (Public Feed)
        $posts = Post::with([
                'user',
                'originalPost.user', // Lấy thông tin người đăng bài gốc
                'reactions',
                'comments' => function ($query) {
                    $query->latest()->limit(1);
                },
                'comments.user'
            ])
            ->latest()
            ->get();

        return view('home', compact('posts', 'friends', 'pendingRequests'));
    }

    public function show(Post $post): View
    {
        $post->load([
            'user',
            'originalPost.user',
            'reactions',
            'comments' => function ($query) {
                $query->whereNull('parent_id')->with(['replies.user', 'user'])->latest();
            }
        ]);

        return view('posts.show', compact('post'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'content' => 'nullable|string',
            'media_url' => 'nullable|string',
            'media_type' => 'nullable|in:image,video'
        ]);

        if (!$request->filled('content') && !$request->filled('media_url')) {
            return back()->withErrors(['content' => 'Bài viết không được để trống.']);
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'media_url' => $request->media_url,
            'media_type' => $request->media_type,
        ]);

        $post->load('user');
        broadcast(new PostCreated($post));

        return to_route('home')->with('success', 'Đăng bài thành công!');
    }

    public function edit(Post $post): View
    {
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa bài viết này.');
        }

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa bài viết này.');
        }

        $request->validate(['content' => 'required|string']);

        $post->update(['content' => $request->content]);

        return to_route('home')->with('success', 'Cập nhật bài viết thành công!');
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xóa bài viết này.');
        }

        $post->delete();

        return back()->with('success', 'Xóa bài viết thành công!');
    }

    public function share(Request $request, Post $post): RedirectResponse
    {
        $request->validate(['content' => 'nullable|string']);

        $originalPostId = $post->original_post_id ?? $post->id;

        Post::create([
            'user_id' => Auth::id(),
            'original_post_id' => $originalPostId,
            'content' => $request->content
        ]);

        return to_route('home')->with('success', 'Chia sẻ bài viết thành công!');
    }

    public function toggleReaction(Request $request, Post $post): RedirectResponse
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
                return back();
            }

            $reaction->update(['type' => $type]);
            return back();
        }

        Reaction::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'type' => $type,
        ]);

        return back();
    }
}
