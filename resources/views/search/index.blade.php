@extends('layouts.app')

@section('content')
<div class="main-layout">
    <aside class="sidebar-left">
        @include('layouts.partials.sidebar-left')
    </aside>

    <main class="content-center">
        <div class="card1" style="margin-bottom: 20px;">
            <h3 style="margin: 0; color: #1c1e21;">Kết quả tìm kiếm cho: "<span style="color: var(--main-blue);">{{ $query }}</span>"</h3>
        </div>

        <div class="card1" style="margin-bottom: 20px;">
            <h4 style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); color: #1c1e21;">Mọi người</h4>

            @if($users->isEmpty())
                <p style="color: var(--text-gray); font-size: 0.95rem;">Không tìm thấy người dùng nào khớp với từ khóa.</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    @foreach($users as $user)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-color); border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <a href="{{ route('profile.show', $user->id) }}">
                                    <img src="{{ $user->avatar ? (filter_var($user->avatar, FILTER_VALIDATE_URL) ? $user->avatar : asset('storage/' . $user->avatar)) : asset('images/default-avatar.png') }}"
                                         style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                </a>
                                <div>
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <a href="{{ route('profile.show', $user->id) }}">{{ $user->name }}</a>
                                    </h4>
                                    <small style="color: var(--text-gray);">Thành viên VibeConnect</small>
                                </div>
                            </div>

                            @if(Auth::id() !== $user->id)
                                <a href="{{ route('profile.show', $user->id) }}" class="btn-post" style="padding: 6px 16px; text-decoration: none;">Xem trang cá nhân</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card1" style="margin-bottom: 15px;">
            <h4 style="margin: 0; color: #1c1e21;">Bài viết</h4>
        </div>

        @if($posts->isEmpty())
            <div class="card1">
                <p style="color: var(--text-gray); font-size: 0.95rem;">Không tìm thấy bài viết nào khớp với từ khóa.</p>
            </div>
        @else
            <div id="posts-container">
                @foreach($posts as $post)
                    <div class="card1" style="margin-bottom: 15px;">
                        <div class="post-header" style="margin-bottom: 10px;">
                            <div class="user-info">
                                <a href="{{ route('profile.show', $post->user->id) }}">
                                    <img src="{{ $post->user->avatar ? (filter_var($post->user->avatar, FILTER_VALIDATE_URL) ? $post->user->avatar : asset('storage/' . $post->user->avatar)) : asset('images/default-avatar.png') }}" class="user-pic">
                                </a>
                                <div>
