@extends('layouts.app')

@section('content')
    <div style="max-width: 1000px; margin: 20px auto; padding: 0 15px;">
        <main class="content-center">

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
                <p style="color: #65676b;">Từ khóa: <strong>{{ request('q') ? request('q') : 'Tất cả' }}</strong></p>
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
                                <img src="{{ $user->avatar ?? 'https://i.pravatar.cc/150?u=' . $user->id }}"
                                    class="avatar-circle">
                                <div class="user-detail">
                                    <a href="{{ route('profile.show', $user->id) }}"
                                        class="user-name-link">{{ $user->name }}</a>
                                    <span class="user-subtext">Học sinh/Sinh viên</span>
                                </div>
                            </div>
                            <div class="user-action">
                                @if (!$friendship)
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
                    @php
                        $myReaction = $post->reactions->firstWhere('user_id', Auth::id());
                    @endphp
                    <div class="card">
                        <div class="post-header">
                            <div class="user-info">
                                <a href="{{ route('profile.show', $post->user->id) }}">
                                    <img src="{{ $post->user->avatar ? (filter_var($post->user->avatar, FILTER_VALIDATE_URL) ? $post->user->avatar : asset('storage/' . $post->user->avatar)) : asset('images/default-avatar.png') }}"
                                        class="user-pic">
                                </a>
                                <div>
                                    <h4 style="font-size: 15px;"><a
                                            href="{{ route('profile.show', $post->user->id) }}">{{ $post->user->name }}</a>
                                    </h4>
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

                            @if (Auth::id() == $post->user_id)
                                <div class="post-options">
                                    <div class="menu-dots" onclick="toggleMenu(this)">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </div>
                                    <div class="options-menu">
                                        <a href="#"
                                            onclick="openPrivacyModal({{ $post->id }}, '{{ $post->privacy ?? 'public' }}'); return false;">
                                            <i class="fa-solid fa-lock"></i> Chỉnh sửa quyền riêng tư
                                        </a>
                                        <a href="{{ route('posts.edit', $post->id) }}"><i
                                                class="fa-regular fa-pen-to-square"></i> Chỉnh sửa</a>
                                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="delete-btn"><i
                                                    class="fa-regular fa-trash-can"></i> Xóa bài viết</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($post->content)
                            <p style="margin: 10px 0;">{{ $post->content }}</p>
                        @endif

                        @if ($post->media && $post->media->isNotEmpty())
                            <div class="post-media-gallery" style="margin-top: 10px;">
                                @foreach ($post->media as $media_item)
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
                                <div class="post-header" style="margin-bottom: 8px;">
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

                        @php
                            $reactionGroups = $post->reactions->groupBy('type');
                            $topLevelComments = $post->comments->whereNull('parent_id');
                            $totalComments = $post->comments->count();
                            $reactionEmojiMap = [
                                'like' => '👍',
                                'love' => '❤️',
                                'haha' => '😆',
                                'wow' => '😮',
                                'sad' => '😢',
                                'angry' => '😡',
                            ];
                        @endphp

                        <div class="post-meta-bar">
                            <div class="meta-left">
                                <strong>{{ $post->reactions->count() }}</strong> cảm xúc
                                @if ($reactionGroups->isNotEmpty())
                                    <span class="reaction-summary-icons">
                                        @foreach ($reactionGroups->keys()->take(3) as $reactionType)
                                            <span
                                                title="{{ ucfirst($reactionType) }}">{{ $reactionEmojiMap[$reactionType] ?? '👍' }}</span>
                                        @endforeach
                                    </span>
                                @endif
                            </div>
                            <button type="button" class="meta-comment-trigger"
                                onclick="toggleCommentPanel({{ $post->id }})"
                                aria-expanded="{{ session('open_comments_post_id') == $post->id ? 'true' : 'false' }}"
                                aria-controls="comment-panel-{{ $post->id }}">
                                {{ $totalComments }} bình luận
                            </button>
                        </div>

                        @if ($post->reactions->count())
                            <details class="reaction-details">
                                <summary>Xem ai đã thả cảm xúc</summary>
                                @foreach ($reactionGroups as $reactionType => $reactionItems)
                                    <div class="reaction-group">
                                        <div class="reaction-group-title">
                                            <span>{{ $reactionEmojiMap[$reactionType] ?? '👍' }}</span>
                                            {{ ucfirst($reactionType) }} ({{ $reactionItems->count() }})
                                        </div>
                                        <div class="reaction-user-list">
                                            @foreach ($reactionItems as $reactionItem)
                                                <a href="{{ route('profile.show', $reactionItem->user->id) }}"
                                                    class="reaction-user-chip">
                                                    <img src="{{ $reactionItem->user->avatar ?? 'https://i.pravatar.cc/150?u=' . $reactionItem->user->id }}"
                                                        class="user-pic-small">
                                                    <span>{{ $reactionItem->user->name }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </details>
                        @endif

                        <div class="post-footer post-footer-actions">
                            <div class="reaction-wrapper" style="position: relative; display: inline-block; ">
                                <div class="reaction-box shadow-sm border" style="margin-bottom: -3px;">
                                    @foreach (['like' => '👍', 'love' => '❤️', 'haha' => '😆', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'] as $type => $emoji)
                                        <form action="{{ route('posts.reaction.toggle', $post->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <input type="hidden" name="type" value="{{ $type }}">
                                            <button type="submit" class="btn-emoji" title="{{ ucfirst($type) }}">
                                                {{ $emoji }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>

                                <form action="{{ route('posts.reaction.toggle', $post->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="like">
                                    <button type="submit" class="btn-main-action {{ $myReaction ? 'is-active' : '' }}">
                                        @if ($myReaction)
                                            @php
                                                $emojis = [
                                                    'like' => '👍',
                                                    'love' => '❤️',
                                                    'haha' => '😆',
                                                    'wow' => '😮',
                                                    'sad' => '😢',
                                                    'angry' => '😡',
                                                ];
                                                $reactionEmoji = $emojis[$myReaction->type] ?? '👍';
                                            @endphp
                                            <span>{{ $reactionEmoji }}</span> {{ ucfirst($myReaction->type) }}
                                        @else
                                            <i class="fa-regular fa-thumbs-up me-1"></i> Thích
                                        @endif
                                    </button>
                                </form>
                            </div>

                            <button type="button" class="post-footer-comment-btn"
                                onclick="toggleCommentPanel({{ $post->id }})"
                                aria-expanded="{{ session('open_comments_post_id') == $post->id ? 'true' : 'false' }}"
                                aria-controls="comment-panel-{{ $post->id }}">
                                <i class="fa-regular fa-comment"></i> Bình luận
                            </button>

                            <form action="{{ route('posts.share', $post->id) }}" method="POST"
                                class="post-footer-share">
                                @csrf
                                <input type="hidden" name="content" value="">
                                <button type="submit" class="post-footer-share-btn">
                                    <i class="fa-solid fa-share"></i> Chia sẻ
                                </button>
                            </form>
                        </div>

                        <div id="comment-panel-{{ $post->id }}"
                            class="comment-panel {{ session('open_comments_post_id') == $post->id ? 'is-open' : '' }}"
                            data-post-id="{{ $post->id }}">
                            @foreach ($topLevelComments as $comment)
                                <div class="comment-block">
                                    <div class="comment-row">
                                        <div>
                                            <strong>{{ $comment->user->name }}</strong>: {{ $comment->content }}
                                            <div class="comment-time">{{ $comment->created_at->diffForHumans() }}</div>
                                        </div>
                                        @if (Auth::id() === $comment->user_id || Auth::id() === $post->user_id)
                                            <form action="{{ route('comments.destroy', $comment->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="comment-delete-btn">Xóa</button>
                                            </form>
                                        @endif
                                    </div>

                                    @foreach ($comment->replies as $reply)
                                        <div class="reply-row">
                                            <div>
                                                <strong>{{ $reply->user->name }}</strong>: {{ $reply->content }}
                                                <div class="comment-time">{{ $reply->created_at->diffForHumans() }}</div>
                                            </div>
                                            @if (Auth::id() === $reply->user_id || Auth::id() === $post->user_id)
                                                <form action="{{ route('comments.destroy', $reply->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="comment-delete-btn">Xóa</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach

                                    <form action="{{ route('comments.store', $post->id) }}" method="POST"
                                        class="reply-form">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                        <input type="text" name="content"
                                            placeholder="Trả lời {{ $comment->user->name }}..." required>
                                        <button type="submit" class="btn-post">Trả lời</button>
                                    </form>
                                </div>
                            @endforeach

                            <form action="{{ route('comments.store', $post->id) }}" method="POST"
                                class="comment-main-form">
                                @csrf
                                <input type="text" id="comment-input-{{ $post->id }}" name="content"
                                    placeholder="Viết bình luận..." autocomplete="off">
                                <button type="submit" class="btn-post">Gửi</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </main>
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
    </script>
@endsection
