<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use Illuminate\Support\Facades\Storage;
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

        $friendIdsArray = $friendIds->toArray();
        $friendIdsArray[] = $authId;

        $pendingRequests = Friendship::query()
            ->with('sender')
            ->where('receiver_id', $authId)
            ->where('status', 'pending')
            ->latest()
            ->get();

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
                'media', // Tải media của bài viết
                'originalPost.media', // Tải media của bài viết gốc
            ])
            ->where(function ($query) use ($authId, $friendIdsArray) {
                $query->where('privacy', 'public')
                    ->orWhere(function ($q) use ($friendIdsArray) {
                        $q->where('privacy', 'friends')
                            ->whereIn('user_id', $friendIdsArray);
                    })
                    ->orWhere(function ($q) use ($authId) {
                        $q->where('privacy', 'private')
                            ->where('user_id', $authId);
                    });
            })
            ->latest()
            ->paginate(10);

        // ĐÃ CHỈNH SỬA: Trả về view 'home' theo cấu trúc chuẩn
        return view('home', compact('posts', 'friends', 'pendingRequests'));
    }


    public function index()
    {
        $authId = Auth::id();
        $friendIdsArray = [];
        if ($authId) {
            $friendIdsArray = Friendship::query()
                ->where('status', 'accepted')
                ->where(function ($query) use ($authId) {
                    $query->where('sender_id', $authId)->orWhere('receiver_id', $authId);
                })
                ->get()
                ->map(function ($friendship) use ($authId) {
                    return $friendship->sender_id === $authId ? $friendship->receiver_id : $friendship->sender_id;
                })
                ->toArray();
            $friendIdsArray[] = $authId;
        }

        $posts = Post::with(['user', 'originalPost.user', 'media', 'originalPost.media'])
            ->where(function ($query) use ($authId, $friendIdsArray) {
                $query->where('privacy', 'public');
                if ($authId) {
                    $query->orWhere(function ($q) use ($friendIdsArray) {
                        $q->where('privacy', 'friends')->whereIn('user_id', $friendIdsArray);
                    })->orWhere(function ($q) use ($authId) {
                        $q->where('privacy', 'private')->where('user_id', $authId);
                    });
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('posts.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,ogg,qt|max:51200',
            'privacy' => 'nullable|in:public,friends,private',
        ]);

        if (!$request->filled('content') && !$request->hasFile('media')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Bài viết không được để trống.'], 422);
            }
            return back()->withErrors(['content' => 'Bài viết không được để trống.']);
        }

        if ($this->containsBannedKeyword($request->input('content'))) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Bài viết chứa từ khóa bị cấm.'], 422);
            }
            return back()->withErrors(['content' => 'Bài viết chứa từ khóa bị cấm.']);
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'privacy' => $request->input('privacy', 'public'),
        ]);

        // Xử lý upload nhiều media
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                if (!$file->isValid()) {
                    continue;
                }

                $path = 'posts/' . $file->hashName();
                Storage::disk('public')->put($path, fopen($file->getPathname(), 'r'));
                $type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';

                $post->media()->create([
                    'file_path' => $path,
                    'file_type' => $type,
                ]);
            }
        }

        // Tải các quan hệ cần thiết và phát sự kiện realtime
        $post->load(['user', 'originalPost.user', 'comments', 'reactions', 'media', 'originalPost.media']);
        broadcast(new PostCreated($post))->toOthers();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đăng bài thành công!',
                'post' => $post
            ]);
        }

        return redirect()->route('home')->with('success', 'Đăng bài thành công!');
    }

    public function edit(Post $post)
    {
        if ($post->user_id !== Auth::id() && Auth::user()?->role !== 'admin') {
            abort(403, 'Bạn không có quyền sửa bài viết này.');
        }

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id() && Auth::user()?->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,ogg,qt|max:51200',
        ]);

        if ($this->containsBannedKeyword($request->input('content'))) {
            return back()->withErrors(['content' => 'Bài viết chứa từ khóa bị cấm.']);
        }

        $data = ['content' => $request->input('content')];

        $post->update($data);

        // Xử lý upload media mới (xóa cũ, thêm mới)
        if ($request->hasFile('media')) {
            // Xóa media cũ
            foreach ($post->media as $media_item) {
                Storage::disk('public')->delete($media_item->file_path);
                $media_item->delete();
            }

            // Thêm media mới
            foreach ($request->file('media') as $file) {
                if (!$file->isValid()) {
                    continue;
                }

                $path = 'posts/' . $file->hashName();
                Storage::disk('public')->put($path, fopen($file->getPathname(), 'r'));
                $type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';
                $post->media()->create([
                    'file_path' => $path,
                    'file_type' => $type,
                ]);
            }
        }

        return redirect()->route('home')->with('success', 'Cập nhật bài viết thành công!');
    }

    public function updatePrivacy(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id() && Auth::user()?->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'privacy' => 'required|in:public,friends,private',
        ]);

        $post->update(['privacy' => $request->input('privacy')]);

        return back()->with('success', 'Đã cập nhật quyền riêng tư bài viết.');
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id() && Auth::user()?->role !== 'admin') {
            abort(403);
        }

        $post->delete();

        return back()->with('success', 'Xóa bài viết thành công!');
    }

    public function share(Request $request, Post $post)
    {
        $request->validate(['content' => 'nullable|string', 'privacy' => 'nullable|in:public,friends,private']);

        $originalPostId = $post->original_post_id ?? $post->id;

        $newPost = Post::create([
            'user_id' => Auth::id(),
            'original_post_id' => $originalPostId,
            'content' => $request->input('content'),
            'privacy' => $request->input('privacy', 'public'),
        ]);

        // Tải các quan hệ cần thiết và phát sự kiện realtime
        $newPost->load(['user', 'originalPost.user']);
        broadcast(new PostCreated($newPost))->toOthers();

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

    private function containsBannedKeyword(?string $content): bool
    {
        $text = mb_strtolower($content ?? '');
        $keywords = config('moderation.banned_keywords', []);

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($text, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    public function show($id)
    {
        $post = Post::with([
            'user',
            'originalPost.user',
            'comments' => function ($query) {
                $query->latest();
            },
            'comments.user',
            'comments.replies.user',
            'reactions',
            'reactions.user',
            'media', // Tải media của bài viết
            'originalPost.media', // Tải media của bài viết gốc
        ])->findOrFail($id);

        $authId = Auth::id();
        if ($post->privacy === 'private' && $post->user_id !== $authId && Auth::user()?->role !== 'admin') {
            abort(403, 'Bài viết này ở chế độ riêng tư.');
        }

        if ($post->privacy === 'friends' && $post->user_id !== $authId && Auth::user()?->role !== 'admin') {
            $isFriend = Friendship::where('status', 'accepted')
                ->where(function ($q) use ($authId, $post) {
                    $q->where('sender_id', $authId)->where('receiver_id', $post->user_id)
                        ->orWhere('sender_id', $post->user_id)->where('receiver_id', $authId);
                })->exists();
            if (!$isFriend) {
                abort(403, 'Bài viết này chỉ dành cho bạn bè.');
            }
        }

        return view('posts.show', compact('post'));
    }
}
