<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        $parentId = $request->input('parent_id');

        if ($parentId) {
            $parentComment = Comment::query()
                ->where('id', $parentId)
                ->where('post_id', $post->id)
                ->first();

            if (! $parentComment) {
                return back()->with('error', 'Bình luận cha không hợp lệ.');
            }
        }

        Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'parent_id' => $parentId,
        ]);

        return back()->with([
            'success' => $parentId ? 'Đã trả lời bình luận!' : 'Đã đăng bình luận!',
            'open_comments_post_id' => $post->id,
        ]);
    }

    public function destroy(Comment $comment)
    {
        $canDelete = Auth::id() === $comment->user_id || Auth::id() === $comment->post->user_id;

        if (! $canDelete) {
            return back()->with('error', 'Bạn không có quyền xóa bình luận này.');
        }

        $postId = $comment->post_id;
        $comment->delete();

        return back()->with([
            'success' => 'Đã xóa bình luận!',
            'open_comments_post_id' => $postId,
        ]);
    }
}
