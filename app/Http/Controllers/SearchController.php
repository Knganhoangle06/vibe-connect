<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// QUAN TRỌNG: Thêm 2 dòng này để hết lỗi gạch đỏ và xử lý link ảnh
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('q', ''));

        // 1. Tìm kiếm người dùng
        $users = User::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->where('id', '!=', Auth::id())
            ->limit(20)
            ->get();

        // 2. Xử lý bản đồ bạn bè (Friendship Map)
        $friendships = Friendship::query()
            ->where(function ($query) {
                $query->where('sender_id', Auth::id())->orWhere('receiver_id', Auth::id());
            })
            ->get();

        $friendshipMap = $friendships->mapWithKeys(function ($friendship) {
            $otherId = $friendship->sender_id === Auth::id() ? $friendship->receiver_id : $friendship->sender_id;
            return [$otherId => $friendship];
        });

        // 3. Tìm kiếm bài đăng và XỬ LÝ ẢNH
        $posts = Post::query()
            ->with('user')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('content', 'like', '%' . $keyword . '%');
            })
            ->latest()
            ->limit(20)
            ->get();

        // Xử lý đường dẫn ảnh/video trước khi gửi ra View
        $posts->transform(function ($post) {
            if ($post->media_url) {
                // Nếu link bắt đầu bằng http (Pinterest, Google...) thì giữ nguyên
                // Nếu không thì nối thêm asset('storage/...')
                $post->formatted_media_url = Str::startsWith($post->media_url, ['http://', 'https://']) 
                    ? $post->media_url 
                    : asset('storage/' . $post->media_url);
            }
            return $post;
        });

        return view('search.index', compact('keyword', 'users', 'posts', 'friendshipMap'));
    }
}