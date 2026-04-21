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
            'content' => 'required',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $post->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        // Nếu là request gửi bằng AJAX (Axios), trả về giao diện bình luận dạng JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                // Compile View cục bộ của bình luận trả về HTML
                'html' => view('posts.partials.comment', compact('comment', 'post'))->render()
            ]);
        }

        // Dự phòng cho phương pháp form truyền thống
        return back();
    }


    public function destroy(Comment $comment)
    {
        if (Auth::id() === $comment->user_id) {
            $comment->delete();
        }
        return back();
    }
}
