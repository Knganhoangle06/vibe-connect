@extends('user.master')

@section('content')
<div class="profile-container">
    <section class="profile-header-section">
        <div class="cover-photo-wrapper">
            {{-- Kiểm tra ảnh bìa: nếu có thì hiện, không thì hiện placeholder --}}
            <img src="{{ $user->background ? (filter_var($user->background, FILTER_VALIDATE_URL) ? $user->background : asset('storage/' . $user->background)) : 'https://via.placeholder.com/1100x400' }}" class="cover-img">

            @if($isMe)
            <button class="btn-edit-cover" onclick="openModal()"><i class="fa-solid fa-camera"></i> Chỉnh sửa ảnh bìa</button>
            @endif
        </div>

        <div class="header-details">
            <div class="avatar-container">
                <img src="{{ $user->avatar ? (filter_var($user->avatar, FILTER_VALIDATE_URL) ? $user->avatar : asset('storage/' . $user->avatar)) : asset('images/default-avatar.png') }}"
                    alt="Avatar"
                    class="avatar-img">
                @if($isMe)
                <div class="upload-avatar-badge" onclick="openModal()"><i class="fa-solid fa-camera"></i></div>
                @endif
            </div>

            <div class="user-meta-info">
                <h1>{{ $user->name }}</h1>
                <p class="friend-count">{{ $stats['friends'] }} bạn bè</p>
                <p class="post-count">{{ $stats['posts'] }} bài viết</p>
            </div>

            <div class="header-actions">
                @if($isMe)
                <button class="btn-facebook-secondary" onclick="openModal()">
                    <i class="fa-solid fa-pen"></i> Chỉnh sửa cá nhân
                </button>
                @else
                @endif
            </div>
        </div>


    </section>

    <div class="profile-grid">
        <aside class="profile-left">
            <div class="fb-card">
                <h3 class="fb-card-title">Giới thiệu</h3>
                <p class="bio-text text-center">{{ $user->bio ?? 'Chưa có tiểu sử.' }}</p>
                <div class="info-items">
                    <div class="info-item"><i class="fa-solid fa-clock"></i> Tham gia từ {{ $user->created_at->format('M Y') }}</div>
                </div>
            </div>
        </aside>

        <main class="profile-main">
            @foreach($posts as $post)
            @php
            $myReaction = $post->reactions->firstWhere('user_id', Auth::id());
            @endphp
            <div class="card1">
                <div class="post-header">
                    <div class="user-info">
                        <a href="{{ route('profile.show', $post->user->id) }}">
                            <img src="{{ $user->avatar ? (filter_var($user->avatar, FILTER_VALIDATE_URL) ? $user->avatar : asset('storage/' . $user->avatar)) : asset('images/default-avatar.png') }}" class="user-pic">
                        </a>
                        <div>
                            <h4 style="font-size: 15px;"><a href="{{ route('profile.show', $post->user->id) }}">{{ $post->user->name }}</a></h4>
                            <small>{{ $post->created_at->diffForHumans() }} · <i class="fa-solid fa-earth-americas"></i></small>
                        </div>
                    </div>

                    @if(Auth::id() == $post->user_id)
                    <div class="post-options">
                        <div class="menu-dots" onclick="toggleMenu(this)">
                            <i class="fa-solid fa-ellipsis"></i>
                        </div>
                        <div class="options-menu">
                            <a href="{{ route('posts.edit', $post->id) }}"><i class="fa-regular fa-pen-to-square"></i> Chỉnh sửa</a>
                            <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="delete-btn"><i class="fa-regular fa-trash-can"></i> Xóa bài viết</button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>

                @if($post->content)
                <p style="margin: 10px 0;">{{ $post->content }}</p>
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

                @if($post->original_post_id && $post->originalPost)
                <div class="original-post-box">
                    <div class="post-header" style="margin-bottom: 8px;">
                        <img src="{{ $post->originalPost->user->avatar }}" class="user-pic" style="width: 30px; height: 30px;">
                        <h5 style="font-size: 13px;">{{ $post->originalPost->user->name }}</h5>
                    </div>
                    <p style="font-size: 14px;">{{ $post->originalPost->content }}</p>
                </div>
                @endif

                @php
                $reactionGroups = $post->reactions->groupBy('type');
                $topLevelComments = $post->comments->whereNull('parent_id');
                $totalComments = $post->comments->count();
                $reactionEmojiMap = ['like' => '👍', 'love' => '❤️', 'haha' => '😆', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'];
                @endphp

                <div class="post-meta-bar">
                    <div class="meta-left">
                        <strong>{{ $post->reactions->count() }}</strong> cảm xúc
                        @if($reactionGroups->isNotEmpty())
                        <span class="reaction-summary-icons">
                            @foreach($reactionGroups->keys()->take(3) as $reactionType)
                            <span title="{{ ucfirst($reactionType) }}">{{ $reactionEmojiMap[$reactionType] ?? '👍' }}</span>
                            @endforeach
                        </span>
                        @endif
                    </div>
                    <button type="button" class="meta-comment-trigger" onclick="toggleCommentPanel({{ $post->id }})" aria-expanded="{{ session('open_comments_post_id') == $post->id ? 'true' : 'false' }}" aria-controls="comment-panel-{{ $post->id }}">
                        {{ $totalComments }} bình luận
                    </button>
                </div>

                @if($post->reactions->count())
                <details class="reaction-details">
                    <summary>Xem ai đã thả cảm xúc</summary>
                    @foreach($reactionGroups as $reactionType => $reactionItems)
                    <div class="reaction-group">
                        <div class="reaction-group-title">
                            <span>{{ $reactionEmojiMap[$reactionType] ?? '👍' }}</span> {{ ucfirst($reactionType) }} ({{ $reactionItems->count() }})
                        </div>
                        <div class="reaction-user-list">
                            @foreach($reactionItems as $reactionItem)
                            <a href="{{ route('profile.show', $reactionItem->user->id) }}" class="reaction-user-chip">
                                <img src="{{ $reactionItem->user->avatar ?? 'https://i.pravatar.cc/150?u=' . $reactionItem->user->id }}" class="user-pic-small">
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
                            @foreach(['like' => '👍', 'love' => '❤️', 'haha' => '😆', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'] as $type => $emoji)
                            <form action="{{ route('posts.reaction.toggle', $post->id) }}" method="POST" class="d-inline">
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
                                @if($myReaction)
                                @php
                                $emojis = ['like' => '👍', 'love' => '❤️', 'haha' => '😆', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'];
                                $reactionEmoji = $emojis[$myReaction->type] ?? '👍';
                                @endphp
                                <span>{{ $reactionEmoji }}</span> {{ ucfirst($myReaction->type) }}
                                @else
                                <i class="fa-regular fa-thumbs-up me-1"></i> Thích
                                @endif
                            </button>
                        </form>
                    </div>

                    <button type="button" class="post-footer-comment-btn" onclick="toggleCommentPanel({{ $post->id }})" aria-expanded="{{ session('open_comments_post_id') == $post->id ? 'true' : 'false' }}" aria-controls="comment-panel-{{ $post->id }}">
                        <i class="fa-regular fa-comment"></i> Bình luận
                    </button>

                    <form action="{{ route('posts.share', $post->id) }}" method="POST" class="post-footer-share">
                        @csrf
                        <input type="hidden" name="content" value="">
                        <button type="submit" class="post-footer-share-btn">
                            <i class="fa-solid fa-share"></i> Chia sẻ
                        </button>
                    </form>
                </div>

                <div id="comment-panel-{{ $post->id }}" class="comment-panel {{ session('open_comments_post_id') == $post->id ? 'is-open' : '' }}" data-post-id="{{ $post->id }}">
                    @foreach($topLevelComments as $comment)
                    <div class="comment-block">
                        <div class="comment-row">
                            <div>
                                <strong>{{ $comment->user->name }}</strong>: {{ $comment->content }}
                                <div class="comment-time">{{ $comment->created_at->diffForHumans() }}</div>
                            </div>
                            @if(Auth::id() === $comment->user_id || Auth::id() === $post->user_id)
                            <form action="{{ route('comments.destroy', $comment->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="comment-delete-btn">Xóa</button>
                            </form>
                            @endif
                        </div>

                        @foreach($comment->replies as $reply)
                        <div class="reply-row">
                            <div>
                                <strong>{{ $reply->user->name }}</strong>: {{ $reply->content }}
                                <div class="comment-time">{{ $reply->created_at->diffForHumans() }}</div>
                            </div>
                            @if(Auth::id() === $reply->user_id || Auth::id() === $post->user_id)
                            <form action="{{ route('comments.destroy', $reply->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="comment-delete-btn">Xóa</button>
                            </form>
                            @endif
                        </div>
                        @endforeach

                        <form action="{{ route('comments.store', $post->id) }}" method="POST" class="reply-form">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <input type="text" name="content" placeholder="Trả lời {{ $comment->user->name }}..." required>
                            <button type="submit" class="btn-post">Trả lời</button>
                        </form>
                    </div>
                    @endforeach

                    <form action="{{ route('comments.store', $post->id) }}" method="POST" class="comment-main-form">
                        @csrf
                        <input type="text" id="comment-input-{{ $post->id }}" name="content" placeholder="Viết bình luận..." autocomplete="off">
                        <button type="submit" class="btn-post">Gửi</button>
                    </form>
                </div>
            </div>
            @endforeach
        </main>
    </div>
</div>

<div id="editProfileModal" class="fb-modal">
    <div class="modal-content" style="margin-top: 7%;">
        <div class="modal-header">
            <h3>Chỉnh sửa thông tin cá nhân</h3>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="modal-form">
            @csrf @method('PUT')

            <div class="form-group">
                <label>Tên hiển thị</label>
                <input type="text" name="name" value="{{ $user->name }}" class="fb-input">
            </div>

            <div class="form-group">
                <label>Ảnh đại diện</label>
                <input type="file" name="avatar" accept="image/*" class="fb-file-input">
            </div>

            <div class="form-group">
                <label>Ảnh bìa</label>
                <input type="file" name="background" accept="image/*" class="fb-file-input">
            </div>

            <div class="form-group">
                <label>Tiểu sử</label>
                <textarea name="bio" rows="3" class="fb-input">{{ $user->bio }}</textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn-save">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection