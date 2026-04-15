<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Events\PostCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::with(['user', 'originalPost.user'])
                     ->latest()
                     ->get();

        return view('home', compact('posts'));
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
        // Trả lại cấu trúc if tường minh, dễ đọc và dễ mở rộng
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa bài viết này.');
        }

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        // Trả lại cấu trúc if tường minh
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa bài viết này.');
        }

        $request->validate(['content' => 'required|string']);

        $post->update(['content' => $request->content]);

        return to_route('home')->with('success', 'Cập nhật bài viết thành công!');
    }

    public function destroy(Post $post): RedirectResponse
    {
        // Trả lại cấu trúc if tường minh
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
}
