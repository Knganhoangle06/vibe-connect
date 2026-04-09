@extends('user.master')

@section('content')
<div class="main-layout">
    <aside class="sidebar-left">
        <div class="menu-item active">
            <img src="https://i.pravatar.cc/150?u=me" class="user-pic-small">
            <span>{{ Auth::user()->name ?? 'Người dùng' }}</span>
        </div>
        <div class="menu-item"><i class="fa-solid fa-user-group" style="color: #1877f2;"></i> <span>Bạn bè</span></div>
        <div class="menu-item"><i class="fa-solid fa-clock" style="color: #1877f2;"></i> <span>Kỷ niệm</span></div>
        <div class="menu-item"><i class="fa-solid fa-bookmark" style="color: #c059d7;"></i> <span>Đã lưu</span></div>
        <div class="menu-item"><i class="fa-solid fa-flag" style="color: #f3425f;"></i> <span>Trang</span></div>
    </aside>

    <main class="content-center">
        <div class="card create-post">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="post-input-container">
                    <img src="https://i.pravatar.cc/150?u=me" class="user-pic">
                    <textarea name="content" placeholder="Bạn đang nghĩ gì thế?" rows="2"></textarea>
                </div>
                <hr>
                <div class="post-actions">
                    <label for="file-upload" class="action-item">
                        <i class="fa-solid fa-image" style="color: #45bd62;"></i> Ảnh/video
                        <input id="file-upload" type="file" name="media" style="display:none;">
                    </label>
                    <span><i class="fa-regular fa-face-smile" style="color: #f7b928;"></i> Cảm xúc</span>
                    <button type="submit" class="btn-post">Đăng</button>
                </div>
            </form>
        </div>

        @foreach($posts as $post)
        <div class="card">
            <div class="post-header">
                <div class="user-info">
                    <img src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-pic">
                    <div>
                        <h4 style="font-size: 15px;">{{ $post->user->name }}</h4>
                        <small>{{ $post->created_at->diffForHumans() }} · <i class="fa-solid fa-earth-americas"></i></small>
                    </div>
                </div>

                <div class="post-options">
                    <div class="menu-dots" onclick="toggleMenu(this)">
                        <i class="fa-solid fa-ellipsis"></i>
                    </div>
                    <div class="options-menu">
                        <a href="#"><i class="fa-regular fa-bookmark"></i> Lưu bài viết</a>
                        @if(Auth::id() == $post->user_id)
                        <a href="{{ route('posts.edit', $post->id) }}"><i class="fa-regular fa-pen-to-square"></i> Chỉnh sửa</a>
                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="fa-regular fa-trash-can"></i> Xóa bài viết</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            @if($post->content)
            <p style="margin: 10px 0;">{{ $post->content }}</p>
            @endif

            @if($post->media_url)
            @if($post->media_type === 'image')
            <img src="{{ asset($post->media_url) }}" class="post-img">
            @elseif($post->media_type === 'video')
            <video controls class="post-video">
                <source src="{{ asset($post->media_url) }}" type="video/mp4">
            </video>
            @endif
            @endif

            @if($post->original_post_id && $post->originalPost)
            <div class="original-post-box">
                <div class="post-header" style="margin-bottom: 8px;">
                    <img src="{{ $post->originalPost->user->avatar }}" class="user-pic" style="width: 30px; height: 30px;">
                    <h5 style="font-size: 13px;">{{ $post->originalPost->user->name }}</h5>
                </div>
                <p style="font-size: 14px;">{{ $post->originalPost->content }}</p>
            </div>
            @endif

            <div class="post-footer">
                <span><i class="fa-regular fa-thumbs-up"></i> Thích</span>
                <span><i class="fa-regular fa-comment"></i> Bình luận</span>
                <span><i class="fa-solid fa-share"></i> Chia sẻ</span>
            </div>
        </div>
        @endforeach
    </main>

    <aside class="sidebar-right">
        <h4 style="color: var(--text-gray); margin-bottom: 10px;">Người liên hệ</h4>
        <div class="contact-item">
            <img src="https://i.pravatar.cc/150?u=1" class="user-pic-small">
            <span>Ngọc Anh</span>
        </div>
        <div class="contact-item">
            <img src="https://i.pravatar.cc/150?u=2" class="user-pic-small">
            <span>Thu Trang</span>
        </div>
    </aside>
</div>

@endsection