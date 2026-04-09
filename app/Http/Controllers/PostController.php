<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function homepage(): View
    {
        // Lấy bài đăng mới nhất kèm thông tin người dùng và bài viết gốc
        $posts = Post::with(['user', 'originalPost.user'])
                     ->latest()
                     ->get();

        return view('page.homepage', compact('posts'));
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
            'media_url' => 'nullable|string',
            'media_type' => 'nullable|in:image,video'
        ]);

        // Không được đăng bài trống hoàn toàn
        if (!$request->filled('content') && !$request->filled('media_url')) {
            return back()->withErrors(['content' => 'Bài viết không được để trống.']);
        }

        Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
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

        $post->update(['content' => $request->content]);

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
        $request->validate(['content' => 'nullable|string']); // Lời dẫn (Caption) của người chia sẻ

        $originalPostId = $post->original_post_id ?? $post->id;

        Post::create([
            'user_id' => Auth::id(),
            'original_post_id' => $originalPostId,
            'content' => $request->content
        ]);

        return redirect()->route('posts.index')->with('success', 'Chia sẻ bài viết thành công!');
    }
}
