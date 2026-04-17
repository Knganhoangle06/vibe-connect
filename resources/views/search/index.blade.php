@extends('user.master')

@section('content')
<div class="main-layout">
    <main class="content-center" style="max-width: 1000px; margin: 0 auto;">
        
        {{-- Thông báo --}}
        @if (session('success'))
            <div class="card" style="color:green; padding: 15px; margin-bottom: 10px;">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="card" style="color:#b3261e; padding: 15px; margin-bottom: 10px;">{{ session('error') }}</div>
        @endif

        {{-- Tiêu đề kết quả --}}
        <div class="card">
            <h2 style="margin:0;">Kết quả tìm kiếm</h2>
            <p style="color: #65676b;">Từ khóa: <strong>{{ $keyword !== '' ? $keyword : 'Tất cả' }}</strong></p>
        </div>

        {{-- Khối Người dùng --}}
        <div class="card search-results-section">
            <h3 class="section-title">Người dùng</h3>
            <div class="user-list">
                @forelse($users as $user)
                    @php
                        $friendship = $friendshipMap[$user->id] ?? null;
                    @endphp
                    <div class="user-item">
                        <div class="user-info">
                            <img src="{{ $user->avatar ?? 'https://i.pravatar.cc/150?u=' . $user->id }}" class="avatar-circle">
                            <div class="user-detail">
                                <a href="{{ route('profile.show', $user->id) }}" class="user-name-link">{{ $user->name }}</a>
                                <span class="user-subtext">Học sinh/Sinh viên</span>
                            </div>
                        </div>
                        <div class="user-action">
                            @if(!$friendship)
                                <form action="{{ route('friends.request', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-action btn-add">Kết bạn</button>
                                </form>
                            @elseif($friendship->status === 'accepted')
                                <form action="{{ route('friends.remove', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-remove">Hủy kết bạn</button>
                                </form>
                            @elseif($friendship->receiver_id === Auth::id())
                                <form action="{{ route('friends.accept', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-action btn-add">Chấp nhận</button>
                                </form>
                            @else
                                <span class="status-label">Đã gửi lời mời</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="empty-msg">Không tìm thấy người dùng phù hợp.</p>
                @endforelse
            </div>
        </div>

        {{-- Khối Bài đăng --}}
        <div class="card search-results-section">
            <h3 class="section-title">Bài đăng</h3>
            @forelse($posts as $post)
                <div class="post-item-mini">
                    <div class="post-item-header">
                        <a href="{{ route('profile.show', $post->user->id) }}" style="text-decoration:none; color:inherit;">
                            <strong>{{ $post->user->name }}</strong>
                        </a>
                        <span class="post-date">{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="post-excerpt">{{ Str::limit($post->content, 150) }}</p>
                </div>
            @empty
                <p class="empty-msg">Không tìm thấy bài đăng phù hợp.</p>
            @endforelse
        </div>

    </main>
</div>
@endsection