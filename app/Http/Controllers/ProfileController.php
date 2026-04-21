<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Friendship;

class ProfileController extends Controller
{
    public function me()
    {
        return redirect()->route('profile.show', Auth::id());
    }

    public function show(User $user)
    {
        $isMe = Auth::id() === $user->id;
        $posts = $user->posts()->with(['user', 'comments.user'])->latest()->get();

        $friendship = Friendship::where(function ($q) use ($user) {
            $q->where('sender_id', Auth::id())->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->where('receiver_id', Auth::id());
        })->first();

        return view('profile.show', compact('user', 'posts', 'isMe', 'friendship'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $user->update($request->only(['name', 'avatar', 'bio']));
        return back()->with('success', 'Đã cập nhật trang cá nhân.');
    }
}
