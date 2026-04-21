@extends('layouts.app')

@section('content')
<div class="profile-container">
    <section class="card" style="padding: 0; overflow: hidden;">
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
                            <form action="{{ route('friendships.add', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary"><i class="fa-solid fa-user-plus"></i> Thêm bạn bè</button>
                            </form>
                        @elseif($friendship->status === 'accepted')
                            <form action="{{ route('friendships.remove', $user->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-secondary"><i class="fa-solid fa-user-check"></i> Bạn bè</button>
                            </form>
                        @elseif($friendship->status === 'pending')
                            <button class="btn-secondary" disabled>Đã gửi lời mời</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="profile-body-layout">
        <aside class="profile-sidebar">
            <div class="card">
                <div class="sidebar-title" style="margin-left: 0; padding-left: 0; font-weight: bold;">Giới thiệu</div>
                <p>{{ $user->bio ?? 'Chưa có tiểu sử.' }}</p>
            </div>

            @if($isMe)
            <div class="card" id="profile-edit-form">
                <div class="sidebar-title" style="margin-left: 0; padding-left: 0; font-weight: bold;">Chỉnh sửa thông tin</div>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div style="margin-bottom: 15px;">
                        <label>Họ và tên</label>
                        <input type="text" name="name" value="{{ $user->name }}" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ced0d4; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Link Avatar</label>
                        <input type="text" name="avatar" value="{{ $user->avatar }}" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ced0d4; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Tiểu sử</label>
                        <textarea name="bio" rows="3" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ced0d4; border-radius: 6px;">{{ $user->bio }}</textarea>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%;">Lưu thay đổi</button>
                </form>
            </div>
            @endif
        </aside>

        <main class="profile-main">
            <div class="sidebar-title" style="margin-left: 0; padding-left: 0; font-weight: bold;">Bài viết</div>
            @forelse($posts as $post)
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-user-info">
                            <img src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar">
                            <div>
                                <p class="user-name">{{ $post->user->name }}</p>
                                <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="post-body">
                        <p class="post-text">{{ $post->content }}</p>
                        @if($post->media_url)
                            <div class="post-media-container">
                                <img src="{{ asset($post->media_url) }}">
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card text-center" style="padding: 40px;">
                    <p style="color: var(--text-gray);">Chưa có bài viết nào để hiển thị.</p>
                </div>
            @endforelse
        </main>
    </div>
</div>
@endsection
