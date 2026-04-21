@extends('layouts.app')

@section('content')
<div class="main-layout-container" style="display: block; max-width: 800px; margin: 20px auto;">

    <div class="card" style="margin-bottom: 20px;">
        <h2 style="margin:0; font-size: 1.5rem; color: #050505;">Kết quả tìm kiếm</h2>
        <p style="color: var(--text-gray); margin-top: 5px;">Từ khóa: <strong>{{ $keyword !== '' ? $keyword : 'Tất cả' }}</strong></p>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: #050505; border-bottom: 1px solid #ced0d4; padding-bottom: 10px;">Mọi người</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($users as $user)
                <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #ced0d4; padding: 10px; border-radius: 8px;">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <img src="{{ $user->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar-small" style="width: 48px; height: 48px;">
                        <a href="{{ route('profile.show', $user->id) }}" style="font-weight: 600; font-size: 1.1rem; text-decoration: none; color: #050505;">
                            {{ $user->name }}
                        </a>
                    </div>
                    <x-friend-button :targetUser="$user" />
                </div>
            @empty
                <p style="color: var(--text-gray);">Không tìm thấy người dùng phù hợp.</p>
            @endforelse
        </div>
    </div>

    <div class="search-posts-section">
        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: #050505; padding-left: 5px;">Bài viết</h3>

        @if($posts->count() > 0)
            @include('posts.partials.feed', ['posts' => $posts])
        @else
            <div class="card">
                <p style="color: var(--text-gray);">Không tìm thấy bài đăng phù hợp.</p>
            </div>
        @endif
    </div>

</div>
@endsection
