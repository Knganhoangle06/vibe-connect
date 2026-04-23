@extends('layouts.app')

@section('content')
<div class="main-layout">
    <aside class="sidebar-left">
        <div class="card-report-header">
            <h4>Quản trị</h4>
            <div class="header-dashboard" style="display: flex; flex-direction: row; gap: 10px; margin-top: 15px;">
                <a href="{{ route('admin.dashboard') }}" class="menu-item "><span>Dashboard</span></a>
                <a href="{{ route('admin.users.index') }}" class="menu-item"><span>Quản lý người dùng</span></a>
                <a href="{{ route('admin.posts.index') }}" class="menu-item active"><span>Quản lý bài viết</span></a>
            </div>
        </div>
    </aside>

    <main class="content-center">
        @if (session('success'))
        <div class="card" style="color:green;">{{ session('success') }}</div>
        @endif

        <div class="card-dashboard">
            <h2>Quản lý bài viết</h2>
            <form method="GET" action="{{ route('admin.posts.index') }}" style="display:flex;gap:8px;margin:10px 0 14px;">
                <input type="text" name="q" value="{{ $query }}" placeholder="Tìm theo nội dung..." style="flex:1;padding:8px;border:1px solid #ddd;border-radius:8px;">
                <button type="submit" class="btn-post">Tìm</button>
            </form>

            @foreach($posts as $post)
            <div class="card1" style="margin-bottom:10px; width: 800px;">
                <div style="display:flex;justify-content:space-between;gap:12px;">
                    <div>
                        <strong>{{ $post->user->name }}</strong>
                        <p style="margin-top:6px;">{{ $post->content }}</p>
                        @if(!empty($post->matched_keywords))
                        <div style="margin-top:6px;color:#b3261e;font-size:13px;">
                            Từ khóa cấm: {{ implode(', ', $post->matched_keywords) }}
                        </div>
                        @endif

                        @if($post->media_url)
                        @php
                        // Logic kiểm tra: Nếu bắt đầu bằng http thì là link ngoài, ngược lại là file trong máy
                        $isExternal = strpos($post->media_url, 'http') === 0;
                        $mediaSrc = $isExternal ? $post->media_url : asset('storage/' . $post->media_url);
                        @endphp

                        @if($post->media_type === 'image')
                        <img src="{{ $mediaSrc }}" class="post-img" alt="Post Image">
                        @elseif($post->media_type === 'video')
                        <video controls class="post-video">
                            <source src="{{ $mediaSrc }}" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ xem video.
                        </video>
                        @endif
                        @endif
                    </div>
                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-post btn-danger">Xóa bài</button>
                    </form>
                </div>
            </div>
            @endforeach

            <div class="feed-pagination">{{ $posts->links() }}</div>
        </div>
    </main>
</div>
@endsection
