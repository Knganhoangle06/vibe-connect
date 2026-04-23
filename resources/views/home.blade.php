@extends('layouts.app')

@section('content')
    <div class="main-layout">
        <aside class="sidebar-left">
            <div class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                <img src="{{ Auth::user()->avatar ? (filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : asset('images/default-avatar.png') }}"
                    class="user-pic-small">
                <a href="{{ route('profile.me') }}"><span>{{ Auth::user()->name ?? 'Người dùng' }}</span></a>
            </div>
            <div class="menu-item"><i class="fa-solid fa-user-group" style="color: #1877f2;"></i> <a
                    href="{{ route('friend.show') }}"><span>Bạn bè ({{ $friends->count() }})</span></a></div>
            <div class="menu-item"><i class="fa-solid fa-magnifying-glass" style="color: #1877f2;"></i> <a
                    href="{{ route('search.index') }}"><span>Khám phá</span></a></div>
            <div class="menu-item"><i class="fa-solid fa-user" style="color: #c059d7;"></i> <a
                    href="{{ route('profile.me') }}"><span>Trang cá nhân</span></a></div>

        </aside>

        <main class="content-center">
            @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card create-post-trigger">
                <div class="post-input-container">
                    <img src="{{ Auth::user()->avatar ? (filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : asset('images/default-avatar.png') }}"
                        class="user-pic">
                    <div class="fake-input" onclick="openPostModal()">
                        {{ Auth::user()->name }} ơi, bạn đang nghĩ gì thế?
                    </div>
                </div>
                <hr>
                <div class="post-actions-trigger" style="display: flex; justify-content: space-around; padding-top: 10px;">
                    <div class="action-btn" onclick="openPostModal()">
                        <i class="fa-solid fa-video" style="color: #f3425f;"></i> Video trực tiếp
                    </div>
                    <div class="action-btn" onclick="openPostModal()">
                        <i class="fa-solid fa-images" style="color: #45bd62;"></i> Ảnh/video
                    </div>
                    <div class="action-btn" onclick="openPostModal()">
                        <i class="fa-solid fa-face-smile" style="color: #f7b928;"></i> Cảm xúc
                    </div>
                </div>
            </div>

            <div id="postModal" class="fb-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Tạo bài viết</h3>
                        <span class="close-modal" onclick="closePostModal()">&times;</span>
                    </div>

                    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="user-info-post">
                                <img src="{{ Auth::user()->avatar ? (filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : asset('images/default-avatar.png') }}"
                                    class="user-pic">
                                <div>
                                    <strong>{{ Auth::user()->name }}</strong>
                                    <select name="privacy"
                                        style="border: none; background: #e4e6eb; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; outline: none; cursor: pointer; margin-top: 5px;">
                                        <option value="public">🌐 Công khai</option>
                                        <option value="friends" selected>👥 Bạn bè</option>
                                        <option value="private">🔒 Chỉ mình tôi</option>
                                    </select>
                                </div>
                            </div>
                            <textarea name="content" placeholder="Bạn đang nghĩ gì thế?" required></textarea>
                            <div class="upload-area">
                                <label for="file-upload" class="file-label">
                                    <div class="upload-icon"><i class="fa-solid fa-square-plus"></i></div>
                                    <p>Thêm ảnh/video</p>
                                    <span>hoặc kéo và thả</span>
                                </label>
                                <input type="file" id="file-upload" name="media[]" accept="image/*,video/*" multiple
                                    hidden onchange="previewFiles(this)">
                                <div id="preview-container"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn-submit-post">Đăng</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($posts as $post)
                @php
                    $myReaction = $post->reactions->firstWhere('user_id', Auth::id());
                    $reactionGroups = $post->reactions->groupBy('type');
                    $reactionEmojiMap = [
                        'like' => '👍',
                        'love' => '❤️',
                        'haha' => '😆',
                        'wow' => '😮',
                        'sad' => '😢',
                        'angry' => '😡',
                    ];
                @endphp
                <div class="card">
                    <div class="post-header">
                        <div class="user-info">
                            <a href="{{ route('profile.show', $post->user->id) }}">
                                <img src="{{ $post->user->avatar ? (filter_var($post->user->avatar, FILTER_VALIDATE_URL) ? $post->user->avatar : asset('storage/' . $post->user->avatar)) : asset('images/default-avatar.png') }}"
                                    class="user-pic">
                            </a>
                            <div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <h4 style="font-size: 15px; margin: 0;">
                                        <a href="{{ route('profile.show', $post->user->id) }}"
                                            style="color: #050505;">{{ $post->user->name }}</a>
                                    </h4>
                                    @if (Auth::id() !== $post->user_id && !$friends->contains('id', $post->user_id))
                                        <span style="color: var(--text-gray); font-size: 12px;">•</span>
                                        <form action="{{ route('friends.request', $post->user_id) }}" method="POST"
                                            class="ajax-form" style="margin: 0;">
                                            @csrf
                                            <button type="submit"
                                                style="background: transparent; border: none; color: var(--main-blue); font-weight: 600; cursor: pointer; font-size: 14px; padding: 0;">Thêm
                                                bạn bè</button>
                                        </form>
                                    @endif
                                </div>
                                <small>{{ $post->created_at->diffForHumans() }} ·
                                    @if (($post->privacy ?? 'public') === 'public')
                                        <i class="fa-solid fa-earth-americas" title="Công khai"></i>
                                    @elseif(($post->privacy ?? 'public') === 'friends')
                                        <i class="fa-solid fa-user-group" title="Bạn bè"></i>
                                    @else
                                        <i class="fa-solid fa-lock" title="Chỉ mình tôi"></i>
                                    @endif
                                </small>
                            </div>
                        </div>

                        @if (Auth::id() == $post->user_id || Auth::user()?->role === 'admin')
                            <div class="post-options">
                                <div class="menu-dots" onclick="toggleMenu(this)">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </div>
                                <div class="options-menu">
                                    @if (Auth::id() == $post->user_id)
                                        <a href="#"
                                            onclick="openPrivacyModal({{ $post->id }}, '{{ $post->privacy ?? 'public' }}'); return false;">
                                            <i class="fa-solid fa-lock"></i> Chỉnh sửa quyền riêng tư
                                        </a>
                                    @endif
                                    <a href="{{ route('posts.edit', $post->id) }}"><i
                                            class="fa-regular fa-pen-to-square"></i> Chỉnh sửa</a>
                                    <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="delete-btn"><i class="fa-regular fa-trash-can"></i>
                                            Xóa bài viết</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($post->content)
                        <p style="margin: 10px 0;">{{ $post->content }}</p>
                    @endif

                    @if ($post->media->isNotEmpty())
                        <div class="post-media-gallery" style="margin-top: 10px;">
                            @foreach ($post->media as $media_item)
                                @if ($media_item->file_type === 'image')
                                    <img src="{{ Storage::url($media_item->file_path) }}" class="post-img"
                                        style="margin-bottom: 5px;">
                                @elseif ($media_item->file_type === 'video')
                                    <video controls class="post-video" style="margin-bottom: 5px;">
                                        <source src="{{ Storage::url($media_item->file_path) }}">
                                        Trình duyệt của bạn không hỗ trợ xem video.
                                    </video>
                                @endif
                            @endforeach
                        </div>
                    @elseif ($post->media_url)
                        <div class="post-media-gallery" style="margin-top: 10px;">
                            @if ($post->media_type === 'image')
                                <img src="{{ filter_var($post->media_url, FILTER_VALIDATE_URL) ? $post->media_url : Storage::url($post->media_url) }}"
                                    class="post-img" style="margin-bottom: 5px;">
                            @elseif ($post->media_type === 'video')
                                <video controls class="post-video" style="margin-bottom: 5px;">
                                    <source
                                        src="{{ filter_var($post->media_url, FILTER_VALIDATE_URL) ? $post->media_url : Storage::url($post->media_url) }}">
                                </video>
                            @endif
                        </div>
                    @endif

                    @if ($post->original_post_id && $post->originalPost)
                        @php $originalPost = $post->originalPost; @endphp
                        <div class="original-post-box">
                            <div class="user-info" style="margin-bottom: 8px;">
                                <a href="{{ route('profile.show', $originalPost->user->id) }}">
                                    <img src="{{ $originalPost->user->avatar ? (filter_var($originalPost->user->avatar, FILTER_VALIDATE_URL) ? $originalPost->user->avatar : asset('storage/' . $originalPost->user->avatar)) : asset('images/default-avatar.png') }}"
                                        class="user-pic" style="width: 30px; height: 30px;">
                                </a>
                                <div>
                                    <h5 style="font-size: 13px; margin:0;"><a
                                            href="{{ route('profile.show', $originalPost->user->id) }}">{{ $originalPost->user->name }}</a>
                                    </h5>
                                    <small
                                        style="font-size: 12px; color: var(--text-gray);">{{ $originalPost->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @if ($originalPost->content)
                                <p style="font-size: 14px; margin: 0 0 10px 0;">{{ $originalPost->content }}</p>
                            @endif

                            @if ($originalPost->media->isNotEmpty())
                                <div class="post-media-gallery">
                                    @foreach ($originalPost->media as $media_item)
                                        @if ($media_item->file_type === 'image')
                                            <img src="{{ Storage::url($media_item->file_path) }}" class="post-img"
                                                style="margin-bottom: 5px;">
                                        @elseif ($media_item->file_type === 'video')
                                            <video controls class="post-video" style="margin-bottom: 5px;">
                                                <source src="{{ Storage::url($media_item->file_path) }}">
                                            </video>
                                        @endif
                                    @endforeach
                                </div>
                            @elseif ($originalPost->media_url)
                                <div class="post-media-gallery">
                                    @if ($originalPost->media_type === 'image')
                                        <img src="{{ filter_var($originalPost->media_url, FILTER_VALIDATE_URL) ? $originalPost->media_url : Storage::url($originalPost->media_url) }}"
                                            class="post-img" style="margin-bottom: 5px;">
                                    @elseif ($originalPost->media_type === 'video')
                                        <video controls class="post-video" style="margin-bottom: 5px;">
                                            <source
                                                src="{{ filter_var($originalPost->media_url, FILTER_VALIDATE_URL) ? $originalPost->media_url : Storage::url($originalPost->media_url) }}">
                                        </video>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="post-meta-bar"
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                        <div class="meta-left" onclick="openReactionsModal({{ $post->id }})"
                            style="cursor: pointer;">
                            @if ($reactionGroups->isNotEmpty())
                                <span class="reaction-summary-icons">
                                    @foreach ($reactionGroups->keys()->take(3) as $reactionType)
                                        <span
                                            title="{{ ucfirst($reactionType) }}">{{ $reactionEmojiMap[$reactionType] ?? '👍' }}</span>
                                    @endforeach
                                </span>
                            @endif
                            <strong style="margin-left: 5px;">{{ $post->reactions->count() }}</strong> cảm xúc
                        </div>

                        <a href="{{ route('posts.show', $post->id) }}"
                            style="color: var(--text-gray); text-decoration: none; font-size: 14px;">
                            {{ $post->comments->count() }} bình luận
                        </a>
                    </div>

                    <div class="post-footer-actions"
                        style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <div class="reaction-wrapper" style="position: relative; display: inline-block; flex: 1;">
                            <div class="reaction-box shadow-sm border" style="margin-bottom: -3px;">
                                @foreach (['like' => '👍', 'love' => '❤️', 'haha' => '😆', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'] as $type => $emoji)
                                    <form action="{{ route('posts.reaction.toggle', $post->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="{{ $type }}">
                                        <button type="submit" class="btn-emoji"
                                            title="{{ ucfirst($type) }}">{{ $emoji }}</button>
                                    </form>
                                @endforeach
                            </div>

                            <form action="{{ route('posts.reaction.toggle', $post->id) }}" method="POST"
                                style="width: 100%;">
                                @csrf
                                <input type="hidden" name="type" value="like">
                                <button type="submit" class="btn-main-action {{ $myReaction ? 'is-active' : '' }}"
                                    style="width: 100%; border: none; background: transparent; font-weight: 600; color: var(--text-gray); cursor: pointer; padding: 8px;">
                                    @if ($myReaction)
                                        <span>{{ $reactionEmojiMap[$myReaction->type] ?? '👍' }}</span>
                                        {{ ucfirst($myReaction->type) }}
                                    @else
                                        <i class="fa-regular fa-thumbs-up"></i> Thích
                                    @endif
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('posts.show', $post->id) }}" class="btn-main-action"
                            style="flex: 1; text-align: center; text-decoration: none; display: flex; justify-content: center; align-items: center; gap: 8px; color: var(--text-gray); font-weight: 600;">
                            <i class="fa-regular fa-comment"></i> Bình luận
                        </a>

                        <button type="button" onclick="openShareModal({{ $post->id }})" class="btn-main-action"
                            style="flex: 1; border: none; background: transparent; font-weight: 600; color: var(--text-gray); cursor: pointer; padding: 8px;">
                            <i class="fa-solid fa-share"></i> Chia sẻ
                        </button>
                    </div>

                    <div id="reactionsModal-{{ $post->id }}" class="fb-modal"
                        style="display: none; align-items: center; justify-content: center; z-index: 1050;">
                        <div class="modal-content"
                            style="max-width: 400px; width: 100%; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                            <div class="modal-header"
                                style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #ddd; background: #fff;">
                                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Cảm xúc</h3>
                                <span class="close-modal" onclick="closeReactionsModal({{ $post->id }})"
                                    style="cursor: pointer; font-size: 24px; color: #666; line-height: 1;">&times;</span>
                            </div>
                            <div class="modal-body"
                                style="max-height: 300px; overflow-y: auto; padding: 0; background: #fff;">
                                @if ($post->reactions->count() > 0)
                                    @foreach ($post->reactions as $reaction)
                                        <div
                                            style="display: flex; align-items: center; padding: 10px 15px; border-bottom: 1px solid #f0f2f5;">
                                            <div style="position: relative; margin-right: 12px;">
                                                <img src="{{ $reaction->user->avatar ? (filter_var($reaction->user->avatar, FILTER_VALIDATE_URL) ? $reaction->user->avatar : asset('storage/' . $reaction->user->avatar)) : asset('images/default-avatar.png') }}"
                                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                                <div
                                                    style="position: absolute; bottom: -2px; right: -2px; background: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.2); font-size: 10px;">
                                                    {{ $reactionEmojiMap[$reaction->type] ?? '👍' }}
                                                </div>
                                            </div>
                                            <a href="{{ route('profile.show', $reaction->user->id) }}"
                                                style="text-decoration: none; color: #050505; font-weight: 600; font-size: 15px;">
                                                {{ $reaction->user->name }}
                                            </a>
                                        </div>
                                    @endforeach
                                @else
                                    <div style="padding: 20px; text-align: center; color: #65676B;">Chưa có cảm xúc nào.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="shareModal-{{ $post->id }}" class="fb-modal"
                        style="display: none; align-items: center; justify-content: center; z-index: 1060;">
                        <div class="modal-content"
                            style="max-width: 500px; width: 100%; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                            <div class="modal-header"
                                style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #ddd; background: #fff;">
                                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Chia sẻ bài viết</h3>
                                <span class="close-modal" onclick="closeShareModal({{ $post->id }})"
                                    style="cursor: pointer; font-size: 24px; color: #666; line-height: 1;">&times;</span>
                            </div>
                            <form action="{{ route('posts.share', $post->id) }}" method="POST">
                                @csrf
                                <div class="modal-body" style="padding: 15px; background: #fff;">
                                    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                        <img src="{{ Auth::user()->avatar ? (filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : asset('images/default-avatar.png') }}"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; font-size: 15px;">{{ Auth::user()->name }}</div>
                                            <select name="privacy"
                                                style="margin-top: 5px; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd; font-size: 12px; background: #e4e6eb; font-weight: 600; cursor: pointer;">
                                                <option value="public">Công khai</option>
                                                <option value="friends">Bạn bè</option>
                                                <option value="private">Chỉ mình tôi</option>
                                            </select>
                                        </div>
                                    </div>
                                    <textarea name="content" placeholder="Hãy nói gì đó về nội dung này..."
                                        style="width: 100%; border: none; outline: none; resize: none; min-height: 80px; font-size: 16px; font-family: inherit;"></textarea>

                                    <div
                                        style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-top: 10px; background: #f9f9f9;">
                                        <div style="font-weight: 600; font-size: 14px;">
                                            {{ $post->originalPost ? $post->originalPost->user->name : $post->user->name }}
                                        </div>
                                        <div
                                            style="font-size: 13px; color: #65676b; margin-top: 5px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $post->originalPost ? $post->originalPost->content : $post->content }}
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer"
                                    style="padding: 15px; border-top: 1px solid #ddd; text-align: right;">
                                    <button type="button" onclick="closeShareModal({{ $post->id }})"
                                        style="background: #e4e6eb; color: #050505; border: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 10px;">Hủy</button>
                                    <button type="submit"
                                        style="background: #1877f2; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">Chia
                                        sẻ ngay</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="feed-pagination card">
                {{ $posts->links() }}
            </div>
        </main>

        <aside class="sidebar-right">
            <div id="friend-requests-wrapper">
                @if (isset($pendingRequests) && $pendingRequests->count() > 0)
                    <div class="card" style="margin-bottom: 15px; padding: 15px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="color: var(--text-gray); margin: 0; font-size: 16px;">Lời mời kết bạn</h4>
                            <a href="{{ route('friend.show') }}"
                                style="color: var(--main-blue); text-decoration: none; font-size: 14px; font-weight: 500;">Xem
                                thêm</a>
                        </div>

                        @php $firstRequest = $pendingRequests->first(); @endphp
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <a href="{{ route('profile.show', $firstRequest->sender_id) }}">
                                <img src="{{ $firstRequest->sender->avatar ? (filter_var($firstRequest->sender->avatar, FILTER_VALIDATE_URL) ? $firstRequest->sender->avatar : asset('storage/' . $firstRequest->sender->avatar)) : asset('images/default-avatar.png') }}"
                                    class="user-pic"
                                    style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                            </a>
                            <div style="flex: 1; overflow: hidden;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <a href="{{ route('profile.show', $firstRequest->sender_id) }}"
                                        style="font-weight: 600; color: #050505; text-decoration: none; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px;">
                                        {{ $firstRequest->sender->name }}
                                    </a>
                                    <span
                                        style="font-size: 12px; color: var(--text-gray); white-space: nowrap;">{{ $firstRequest->created_at->diffForHumans(null, true) }}</span>
                                </div>

                                <div style="display: flex; gap: 8px; margin-top: 10px;">
                                    <form action="{{ route('friends.accept', $firstRequest->sender_id) }}" method="POST"
                                        class="accept-friend-form" style="flex: 1; margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn-confirm"
                                            style="padding: 6px 10px; width: 100%;">Xác nhận</button>
                                    </form>
                                    <form action="{{ route('friends.remove', $firstRequest->sender_id) }}" method="POST"
                                        style="flex: 1; margin: 0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-delete"
                                            style="padding: 6px 10px; width: 100%;">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 15px 0;">
                @endif
            </div>

            <h4 style="color: var(--text-gray); margin-bottom: 15px; padding-left: 5px;">Người liên hệ</h4>
            <div id="contact-list">
                @forelse($friends as $friend)
                    <div class="contact-item"
                        style="display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px; transition: background-color 0.2s;">
                        <div class="user-avatar-wrapper" style="position: relative; display: inline-flex;">
                            <img src="{{ $friend->avatar ? (filter_var($friend->avatar, FILTER_VALIDATE_URL) ? $friend->avatar : asset('storage/' . $friend->avatar)) : asset('images/default-avatar.png') }}"
                                class="user-pic-small"
                                style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                            <div class="status-dot user-status-{{ $friend->id }}"
                                style="position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; background-color: #31a24c; border: 2px solid white; border-radius: 50%; display: none;">
                            </div>
                        </div>
                        <a href="{{ route('profile.show', $friend->id) }}"
                            style="flex: 1; font-weight: 500; color: #050505; text-decoration: none;">
                            <span>{{ $friend->name }}</span>
                        </a>
                        <div style="display: flex; gap: 6px; margin-left: auto;">
                            <form action="{{ route('friends.remove', $friend->id) }}" method="POST" style="margin: 0;">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    style="border: none; background: #fce8eb; color: #f3425f; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;"
                                    title="Hủy kết bạn">
                                    <i class="fa-solid fa-user-xmark"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div id="no-friends-placeholder" class="card"
                        style="text-align: center; color: var(--text-gray); padding: 15px;">
                        Chưa có bạn bè nào. Hãy khám phá và kết bạn nhé!
                    </div>
                @endforelse
            </div>
        </aside>
    </div>

    <!-- Modal Quyền Riêng Tư -->
    <div id="privacyModal" class="fb-modal"
        style="display: none; align-items: center; justify-content: center; z-index: 1060;">
        <div class="modal-content"
            style="max-width: 400px; width: 100%; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            <div class="modal-header"
                style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #ddd; background: #fff;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Chọn đối tượng</h3>
                <span class="close-modal" onclick="closePrivacyModal()"
                    style="cursor: pointer; font-size: 24px; color: #666; line-height: 1;">&times;</span>
            </div>
            <form id="privacyForm" method="POST" action="">
                @csrf @method('PATCH')
                <div class="modal-body" style="padding: 15px; background: #fff;">
                    <div style="margin-bottom: 15px;">Ai có thể xem bài viết của bạn?</div>

                    <label
                        style="display: flex; align-items: center; padding: 10px 0; cursor: pointer; border-bottom: 1px solid #eee;">
                        <input type="radio" name="privacy" value="public"
                            style="margin-right: 10px; transform: scale(1.2);">
                        <div>
                            <div style="font-weight: 600;"><i class="fa-solid fa-earth-americas"></i> Công khai</div>
                            <div style="font-size: 12px; color: #65676b;">Bất kỳ ai trên hoặc ngoài Vibe Connect</div>
                        </div>
                    </label>

                    <label
                        style="display: flex; align-items: center; padding: 10px 0; cursor: pointer; border-bottom: 1px solid #eee;">
                        <input type="radio" name="privacy" value="friends"
                            style="margin-right: 10px; transform: scale(1.2);">
                        <div>
                            <div style="font-weight: 600;"><i class="fa-solid fa-user-group"></i> Bạn bè</div>
                            <div style="font-size: 12px; color: #65676b;">Bạn bè của bạn trên Vibe Connect</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; padding: 10px 0; cursor: pointer;">
                        <input type="radio" name="privacy" value="private"
                            style="margin-right: 10px; transform: scale(1.2);">
                        <div>
                            <div style="font-weight: 600;"><i class="fa-solid fa-lock"></i> Chỉ mình tôi</div>
                            <div style="font-size: 12px; color: #65676b;">Chỉ bạn mới có thể xem bài viết này</div>
                        </div>
                    </label>
                </div>
                <div class="modal-footer" style="padding: 15px; text-align: right; border-top: 1px solid #ddd;">
                    <button type="button" class="btn-cancel" onclick="closePrivacyModal()"
                        style="margin-right: 10px; padding: 8px 15px; border-radius: 5px; border: none; cursor: pointer; background-color: #e4e6eb;">Hủy</button>
                    <button type="submit" class="btn-primary"
                        style="padding: 8px 15px; border-radius: 5px; border: none; cursor: pointer; width: auto; background-color: #1877f2; color: #fff;">Xong</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // JS Modal Cảm Xúc (Kế thừa nguyên bản)
        function openReactionsModal(postId) {
            const modal = document.getElementById('reactionsModal-' + postId);
            if (modal) modal.style.display = 'flex';
        }

        function closeReactionsModal(postId) {
            const modal = document.getElementById('reactionsModal-' + postId);
            if (modal) modal.style.display = 'none';
        }
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('fb-modal') && event.target.id.startsWith('reactionsModal-')) {
                event.target.style.display = 'none';
            }
            if (event.target.classList.contains('fb-modal') && event.target.id.startsWith('shareModal-')) {
                event.target.style.display = 'none';
            }
        });

        // CSS JS Post Modal
        function openPostModal() {
            document.getElementById('postModal').style.display = 'block';
        }

        function closePostModal() {
            document.getElementById('postModal').style.display = 'none';
        }

        function toggleMenu(element) {
            const menu = element.nextElementSibling;
            document.querySelectorAll('.options-menu').forEach(m => {
                if (m !== menu) m.classList.remove('active');
            });
            if (menu) menu.classList.toggle('active');
        }

        function openPrivacyModal(postId, currentPrivacy) {
            const modal = document.getElementById('privacyModal');
            if (modal) {
                const form = document.getElementById('privacyForm');
                form.action = `/posts/${postId}/privacy`;
                const radios = form.querySelectorAll('input[name="privacy"]');
                radios.forEach(radio => {
                    radio.checked = (radio.value === currentPrivacy);
                });
                modal.style.display = 'flex';
            }
        }

        function closePrivacyModal() {
            const modal = document.getElementById('privacyModal');
            if (modal) modal.style.display = 'none';
        }
        window.addEventListener('click', function(event) {
            if (event.target.id === 'privacyModal') closePrivacyModal();
        });

        // Realtime cho Bài viết mới với WebSockets
        document.addEventListener("DOMContentLoaded", function() {
            const authId = {{ Auth::id() ?? 'null' }};

            if (window.Echo) {
                window.Echo.channel('posts')
                    .listen('PostCreated', (e) => {
                        const post = e.post;

                        // Bỏ qua nếu chính mình là người đăng (giữ nguyên quy trình cũ)
                        if (post.user_id === authId) return;

                        // Xử lý HTML cho media (ảnh/video)
                        let mediaHtml = '';
                        if (post.media && post.media.length > 0) {
                            mediaHtml += `<div class="post-media-gallery" style="margin-top: 10px;">`;
                            post.media.forEach(media_item => {
                                const mediaSrc = `/storage/${media_item.file_path}`;
                                if (media_item.file_type === 'image') {
                                    mediaHtml +=
                                        `<img src="${mediaSrc}" class="post-img" style="margin-bottom: 5px;">`;
                                } else if (media_item.file_type === 'video') {
                                    mediaHtml +=
                                        `<video controls class="post-video" style="margin-bottom: 5px;"><source src="${mediaSrc}"></video>`;
                                }
                            });
                            mediaHtml += `</div>`;
                        }

                        // Xử lý HTML nếu bài viết là một bài chia sẻ (Share)
                        let originalPostHtml = '';
                        const origPost = post.original_post || post.originalPost;
                        if (post.original_post_id && origPost) {
                            let origAvatar = origPost.user.avatar ? (origPost.user.avatar.startsWith('http') ?
                                    origPost.user.avatar : `/storage/${origPost.user.avatar}`) :
                                '{{ asset('images/default-avatar.png') }}';
                            originalPostHtml = `
                                <div class="original-post-box" style="border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; margin-top: 10px; background: #fafbff;">
                                    <div class="post-header" style="margin-bottom: 8px;">
                                        <img src="${origAvatar}" class="user-pic" style="width: 30px; height: 30px;">
                                        <h5 style="font-size: 13px;">${origPost.user.name}</h5>
                                    </div>
                                    <p style="font-size: 14px;">${origPost.content || ''}</p>
                                </div>
                            `;
                        }

                        const csrfToken = document.querySelector('input[name="_token"]')?.value || '';
                        let avatar = post.user.avatar ? (post.user.avatar.startsWith('http') ? post.user
                                .avatar : `/storage/${post.user.avatar}`) :
                            '{{ asset('images/default-avatar.png') }}';

                        let privacyIcon = '<i class="fa-solid fa-earth-americas" title="Công khai"></i>';
                        if (post.privacy === 'friends') privacyIcon =
                            '<i class="fa-solid fa-user-group" title="Bạn bè"></i>';
                        if (post.privacy === 'private') privacyIcon =
                            '<i class="fa-solid fa-lock" title="Chỉ mình tôi"></i>';

                        // Render khung bài viết
                        const postHtml = `
                            <div class="card" style="animation: fadeIn 0.5s ease-in-out; border: 1px solid #1877f2; transition: all 0.3s;">
                                <div class="post-header">
                                    <div class="user-info">
                                        <a href="/profile/${post.user.id}">
                                            <img src="${avatar}" class="user-pic">
                                        </a>
                                        <div>
                                            <h4 style="font-size: 15px;"><a href="/profile/${post.user.id}">${post.user.name}</a></h4>
                                            <small>Vừa xong · ${privacyIcon}</small>
                                        </div>
                                    </div>
                                </div>

                                ${post.content ? `<p style="margin: 10px 0;">${post.content}</p>` : ''}
                                ${mediaHtml}
                                ${originalPostHtml}

                                <div class="post-meta-bar" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                                    <div class="meta-left" style="cursor: pointer;"><strong style="margin-left: 5px;">0</strong> cảm xúc</div>
                                    <a href="/posts/${post.id}" style="color: var(--text-gray); text-decoration: none; font-size: 14px;">0 bình luận</a>
                                </div>

                                <div class="post-footer-actions" style="display: flex; justify-content: space-between; padding: 10px 0;">
                                    <div class="reaction-wrapper" style="position: relative; display: inline-block; flex: 1;"><a href="/posts/${post.id}" class="btn-main-action"><i class="fa-regular fa-thumbs-up"></i> Thích</a></div>
                                    <a href="/posts/${post.id}" class="btn-main-action" style="flex: 1; text-align: center;"><i class="fa-regular fa-comment"></i> Bình luận</a>
                                </div>
                            </div>
                        `;

                        // Chèn bài viết mới nhất ngay bên dưới Modal (dành cho phần đầu của Feed)
                        const postModal = document.getElementById('postModal');
                        if (postModal) postModal.insertAdjacentHTML('afterend', postHtml);
                    });

                // Realtime lắng nghe Lời mời kết bạn
                if (authId) {
                    window.Echo.private('App.Models.User.' + authId)
                        .listen('FriendRequestSent', (e) => {
                            const sender = e.sender;
                            const wrapper = document.getElementById('friend-requests-wrapper');
                            if (!wrapper) return;

                            const avatarUrl = sender.avatar ? (sender.avatar.startsWith('http') ? sender
                                    .avatar : '/storage/' + sender.avatar) :
                                '{{ asset('images/default-avatar.png') }}';
                            const csrfToken = document.querySelector('input[name="_token"]')?.value || '';

                            const html = `
                            <div class="card" style="margin-bottom: 15px; padding: 15px; animation: fadeIn 0.5s ease-in-out; border: 1px solid #1877f2; box-shadow: 0 0 10px rgba(24,119,242,0.1);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h4 style="color: var(--text-gray); margin: 0; font-size: 16px;">Lời mời kết bạn</h4>
                                    <a href="/friend-requests" style="color: var(--main-blue); text-decoration: none; font-size: 14px; font-weight: 500;">Xem thêm</a>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <a href="/profile/${sender.id}">
                                        <img src="${avatarUrl}" class="user-pic" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                    </a>
                                    <div style="flex: 1; overflow: hidden;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <a href="/profile/${sender.id}" style="font-weight: 600; color: #050505; text-decoration: none; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px;">
                                                ${sender.name}
                                            </a>
                                            <span style="font-size: 12px; color: var(--main-blue); font-weight: 600; white-space: nowrap;">Vừa xong</span>
                                        </div>
                                        <div style="display: flex; gap: 8px; margin-top: 10px;">
                                            <form action="/friends/${sender.id}/accept" method="POST" class="accept-friend-form" style="flex: 1; margin: 0;">
                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                <button type="submit" class="btn-confirm" style="padding: 6px 10px; width: 100%;">Xác nhận</button>
                                            </form>
                                            <form action="/friends/${sender.id}" method="POST" style="flex: 1; margin: 0;">
                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn-delete" style="padding: 6px 10px; width: 100%;">Xóa</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 15px 0;">
                        `;

                            wrapper.innerHTML = html;
                        })
                        .listen('FriendRequestAccepted', (e) => {
                            // 1. Xác định ai là bạn mới
                            const newFriend = e.user1.id === authId ? e.user2 : e.user1;

                            // 2. Tìm và cập nhật tất cả nút "Thêm bạn bè" của người đó trên trang
                            const addFriendForms = document.querySelectorAll(
                                `form.ajax-form[action*="/friends/request/${newFriend.id}"]`);
                            addFriendForms.forEach(form => {
                                form.innerHTML =
                                    `<span style="color: #28a745; font-size: 14px; font-weight: 600;"><i class="fa-solid fa-user-check"></i> Bạn bè</span>`;
                            });

                            // 3. Thêm bạn mới vào danh sách "Người liên hệ"
                            const contactList = document.getElementById('contact-list');
                            if (!contactList) return;

                            // Xóa thông báo "chưa có bạn bè" nếu có
                            const placeholder = document.getElementById('no-friends-placeholder');
                            if (placeholder) placeholder.remove();

                            // Tạo HTML cho người bạn mới
                            const avatarUrl = newFriend.avatar ? (newFriend.avatar.startsWith(
                                    'http') ? newFriend.avatar : '/storage/' + newFriend
                                .avatar) : '{{ asset('images/default-avatar.png') }}';
                            const csrfToken = document.querySelector('input[name="_token"]')
                                ?.value || '';

                            const newContactHtml = `
                                <div class="contact-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px; animation: fadeIn 0.5s;">
                                    <div class="user-avatar-wrapper" style="position: relative; display: inline-flex;">
                                        <img src="${avatarUrl}" class="user-pic-small" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                                    </div>
                                    <a href="/profile/${newFriend.id}" style="flex: 1; font-weight: 500; color: #050505; text-decoration: none;">
                                        <span>${newFriend.name}</span>
                                    </a>
                                    <div style="display: flex; gap: 6px; margin-left: auto;">
                                        <form action="/friends/${newFriend.id}" method="POST" style="margin: 0;">
                                            <input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" style="border: none; background: #fce8eb; color: #f3425f; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;" title="Hủy kết bạn">
                                                <i class="fa-solid fa-user-xmark"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>`;

                            contactList.insertAdjacentHTML('beforeend', newContactHtml);
                        });
                }
            }
        });

        // Xử lý chặn Reload trang bằng AJAX cho các nút Thêm bạn bè / Chấp nhận
        document.addEventListener('submit', function(e) {
            // 1. Dành cho nút "Thêm bạn bè" (Form đã có sẵn class ajax-form)
            if (e.target.matches('form.ajax-form')) {
                e.preventDefault();
                const form = e.target;

                fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Đổi nút thành dòng chữ thông báo
                            form.innerHTML =
                                '<span style="color: var(--text-gray); font-size: 14px; font-weight: 500;"><i class="fa-solid fa-check"></i> Đã gửi yêu cầu</span>';
                        } else {
                            alert(data.error || 'Có lỗi xảy ra!');
                        }
                    }).catch(err => console.error(err));
            }

            // 2. Dành cho nút "Xác nhận" lời mời ở sidebar phải
            if (e.target.matches('form.accept-friend-form')) {
                e.preventDefault();
                const form = e.target;

                fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Làm mờ và ẩn khung lời mời đó đi cho mượt
                            const card = form.closest('.card');
                            if (card) {
                                card.style.opacity = '0.5';
                                setTimeout(() => card.remove(), 200);
                            }
                        }
                    }).catch(err => console.error(err));
            }
        });
    </script>
@endsection
