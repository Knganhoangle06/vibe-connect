<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $maleCount = User::query()->where('gender', 'male')->count();
        $femaleCount = User::query()->where('gender', 'female')->count();
        $otherCount = User::query()->where('gender', 'other')->count();

        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),
            'totalPosts' => Post::query()->count(),
            'totalComments' => Comment::query()->count(),
            'genderStats' => [
                'male' => $maleCount,
                'female' => $femaleCount,
                'other' => $otherCount,
            ],
        ]);
    }

    public function users(): View
    {
        $users = User::query()->latest()->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function toggleLock(User $user): RedirectResponse
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Không thể khóa tài khoản admin.');
        }

        $user->update([
            'is_locked' => ! $user->is_locked,
        ]);

        return back()->with('success', $user->is_locked ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
    }

    public function posts(Request $request): View
    {
        $bannedKeywords = config('moderation.banned_keywords', []);
        $query = trim((string) $request->input('q', ''));

        $posts = Post::query()
            ->with('user')
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where('content', 'like', '%' . $query . '%');
            })
            ->latest()
            ->paginate(15)
            ->through(function (Post $post) use ($bannedKeywords) {
                $content = mb_strtolower($post->content ?? '');
                $matched = collect($bannedKeywords)
                    ->filter(fn ($keyword) => str_contains($content, mb_strtolower($keyword)))
                    ->values()
                    ->all();

                $post->matched_keywords = $matched;
                return $post;
            });

        return view('admin.posts', compact('posts', 'query'));
    }

    public function destroyPost(Post $post): RedirectResponse
    {
        $post->delete();

        return back()->with('success', 'Đã xóa bài viết vi phạm.');
    }
}
