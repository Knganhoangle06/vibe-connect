<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use App\Models\Friendship;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function myProfile()
    {
        /** @var User $user */
        $user = Auth::user();
        $posts = $this->postsOfUser($user->id);

        return view('profile.show', [
            'user' => $user,
            'posts' => $posts,
            'stats' => $this->profileStats($user->id, $posts),
            'isMe' => true,
            'friendship' => null,
        ]);
    }

    public function show(User $user)
    {
        $authId = Auth::id();
        $posts = $this->postsOfUser($user->id);

        $friendship = Friendship::query()
            ->where(function ($query) use ($authId, $user) {
                $query->where('sender_id', $authId)->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($authId, $user) {
                $query->where('sender_id', $user->id)->where('receiver_id', $authId);
            })
            ->first();

        return view('profile.show', [
            'user' => $user,
            'posts' => $posts,
            'stats' => $this->profileStats($user->id, $posts),
            'isMe' => $authId === $user->id,
            'friendship' => $friendship,
        ]);
    }

   public function update(Request $request)
{
    /** @var User $user */
    $user = Auth::user();

    $request->validate([
        'name' => 'required|string|max:255',
        'bio' => 'nullable|string|max:500',
        'avatar' => 'nullable|image|max:2048', 
        'background' => 'nullable|image|max:2048', // Thêm validate cho ảnh bìa
    ]);

    $data = [
        'name' => $request->name,
        'bio' => $request->bio,
    ];

    // Xử lý upload Avatar
    if ($request->hasFile('avatar')) {
        $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
    }

    // Xử lý upload Ảnh bìa (Cái này lúc nãy bạn thiếu nè)
    if ($request->hasFile('background')) {
        $data['background'] = $request->file('background')->store('covers', 'public');
    }

    $user->update($data);

    return back()->with('success', 'Đã cập nhật thông tin cá nhân.');
}

    private function postsOfUser(int $userId)
    {
        return Post::query()
            ->with(['user', 'originalPost.user', 'comments.user', 'reactions'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    private function profileStats(int $userId, $posts): array
    {
        $friendCount = Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->count();

        return [
            'posts' => $posts->count(),
            'friends' => $friendCount,
            'photos' => $posts->where('media_type', 'image')->count(),
        ];
    }
}
