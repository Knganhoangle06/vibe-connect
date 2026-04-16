@extends('user.master')

@section('content')
<div class="profile-container">
    <section class="profile-hero card no-padding">
        <div class="profile-cover"></div>
        <div class="profile-header-content">
            <div class="profile-avatar-wrapper">
                <img src="{{ $user->avatar ?? 'https://i.pravatar.cc/150?u=' . $user->id }}" class="profile-avatar-big">
            </div>
            <div class="profile-info-main">
                <div class="name-row">
                    <h1>{{ $user->name }}</h1>
                    <div class="profile-actions">
                        @if($isMe)
                            <button class="btn-secondary" onclick="document.getElementById('profile-edit-form').scrollIntoView({behavior: 'smooth'})">
                                <i class="fa-solid fa-pen"></i> Chỉnh sửa
                            </button>
                        @elseif(!$friendship)
                            <form action="{{ route('friends.request', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary"><i class="fa-solid fa-user-plus"></i> Thêm bạn bè</button>
                            </form>
                        @elseif($friendship->status === 'accepted')
                            <form action="{{ route('friends.remove', $user->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger">Hủy kết bạn</button>
                            </form>
                        @else
                            <button class="btn-disabled" disabled>Đã gửi lời mời</button>
                        @endif
                    </div>
                </div>
                <p class="profile-bio-text">{{ $user->bio ?? 'Chưa có mô tả cá nhân.' }}</p>
                <div class="profile-stats-bar">
                    <span><strong>{{ $stats['friends'] }}</strong> bạn bè</span>
                    <span><strong>{{ $stats['posts'] }}</strong> bài viết</span>
                    <span><strong>{{ $stats['photos'] }}</strong> ảnh</span>
                </div>
            </div>
        </div>
        <div class="profile-navigation">
            <a href="#" class="nav-item active">Bài viết</a>
            <a href="#" class="nav-item">Giới thiệu</a>
            <a href="#" class="nav-item">Bạn bè</a>
            <a href="#" class="nav-item">Ảnh</a>
        </div>
    </section>

    <div class="profile-grid">
        <aside class="profile-side">
            <div class="card">
                <h3 class="card-title">Giới thiệu</h3>
                <p class="text-secondary mb-3">{{ $user->bio ?? 'Người dùng chưa cập nhật thông tin.' }}</p>
                <div class="info-list">
                    <div><i class="fa-solid fa-clock"></i> Tham gia từ {{ $user->created_at->format('M Y') }}</div>
                </div>
            </div>

            @if($isMe)
            <div class="card" id="profile-edit-form">
                <h3 class="card-title">Chỉnh sửa thông tin</h3>
                <form action="{{ route('profile.update') }}" method="POST" class="standard-form">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label>Tên hiển thị</label>
                        <input type="text" name="name" value="{{ $user->name }}">
                    </div>
                    <div class="form-group">
                        <label>Link Avatar</label>
                        <input type="text" name="avatar" value="{{ $user->avatar }}">
                    </div>
                    <div class="form-group">
                        <label>Tiểu sử</label>
                        <textarea name="bio" rows="3">{{ $user->bio }}</textarea>
                    </div>
                    <button type="submit" class="btn-primary w-100">Lưu thay đổi</button>
                </form>
            </div>
            @endif
        </aside>

        <main class="profile-main">
            <div class="card-header-title">Bài viết</div>
            @forelse($posts as $post)
                <article class="card post-item">
                    <div class="post-meta">
                        <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="post-content">
                        <p>{{ $post->content }}</p>
                        @if($post->media_url)
                            <img src="{{ $post->media_url }}" class="post-media">
                        @endif
                    </div>
                </article>
            @empty
                <div class="card text-center py-5">
                    <p class="text-secondary">Chưa có bài viết nào để hiển thị.</p>
                </div>
            @endforelse
        </main>
    </div>
</div>
@endsection