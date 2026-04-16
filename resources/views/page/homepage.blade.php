@extends('user.master')

@section('content')
<div class="main-layout">
    <aside class="sidebar-left">
        <div class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <img src="{{ Auth::user()->avatar ?? 'https://i.pravatar.cc/150?u=me' }}" class="user-pic-small">
            <a href="{{ route('profile.me') }}"><span>{{ Auth::user()->name ?? 'Người dùng' }}</span></a>
        </div>
        <div class="menu-item"><i class="fa-solid fa-user-group" style="color: #1877f2;"></i> <a href="{{ route('friend.show') }}"><span>Bạn bè ({{ $friends->count() }})</span></a></div>
        <div class="menu-item"><i class="fa-solid fa-magnifying-glass" style="color: #1877f2;"></i> <a href="{{ route('search.index') }}"><span>Khám phá</span></a></div>
        <div class="menu-item"><i class="fa-solid fa-user" style="color: #c059d7;"></i> <a href="{{ route('profile.me') }}"><span>Trang cá nhân</span></a></div>

        @if($pendingRequests->count())
        <div class="card1" style="margin-top:12px;">
            <h4>Lời mời kết bạn</h4>
            @foreach($pendingRequests as $request)
            <div style="margin-top:8px;">
                <strong>{{ $request->sender->name }}</strong>
                <form action="{{ route('friends.accept', $request->sender_id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-post" style="margin-top:6px;">Chấp nhận</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </aside>

    <main class="content-center">
        @if (session('success'))
        <div class="card1" style="color:green;">{{ session('success') }}</div>
        @endif
        @if (session('error'))
        <div class="card1" style="color:#b3261e;">{{ session('error') }}</div>
        @endif

        <div class="card1 create-post">
            <form action="{{ route('posts.store') }}" method="POST">
                @csrf
                <div class="post-input-container">
                    <img src="{{ Auth::user()->avatar ?? 'https://i.pravatar.cc/150?u=me' }}" class="user-pic">
                    <textarea name="content" placeholder="Bạn đang nghĩ gì thế?" rows="2"></textarea>
                </div>
                <input type="text" name="media_url" placeholder="Dán URL ảnh/video (nếu có)" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;">
                <div class="post-actions">
                    <select name="media_type" style="padding:6px;border-radius:6px;border:1px solid #ddd;">
                        <option value="">Không có media</option>
                        <option value="image">Ảnh</option>
                        <option value="video">Video</option>
                    </select>
                    <button type="submit" class="btn-post">Đăng</button>
                </div>
            </form>
        </div>

        @foreach($posts as $post)
        @php
        $myReaction = $post->reactions->firstWhere('user_id', Auth::id());
        @endphp
        <div class="card1">
            <div class="post-header">
                <div class="user-info">
                    <a href="{{ route('profile.show', $post->user->id) }}">
                        <img src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-pic">
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
            @if($post->media_type === 'image')
            <img src="{{ $post->media_url }}" class="post-img">
            @elseif($post->media_type === 'video')
            <video controls class="post-video">
                <source src="{{ $post->media_url }}" type="video/mp4">
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
                <div class="meta-right">{{ $totalComments }} bình luận</div>
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

            <div class="post-footer">
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

                <form action="{{ route('posts.share', $post->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="content" value="">
                    <button type="submit" style="border:none;background:transparent;cursor:pointer;color:#65676b">
                        <i class="fa-solid fa-share"></i> Chia sẻ
                    </button>
                </form>
            </div>

            <div style="margin-top: 12px;">
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

                <form action="{{ route('comments.store', $post->id) }}" method="POST" style="display:flex;gap:8px;margin-top:8px;">
                    @csrf
                    <input type="text" name="content" placeholder="Viết bình luận..." style="flex:1;padding:8px;border:1px solid #ddd;border-radius:6px;">
                    <button type="submit" class="btn-post">Gửi</button>
                </form>
            </div>
        </div>
        @endforeach

        <div class="feed-pagination card1">
            {{ $posts->links() }}
        </div>
    </main>

    <aside class="sidebar-right" >
        <h4 style="color: var(--text-gray); margin-bottom: 10px;">Bạn bè từ dữ liệu seed</h4>
        @forelse($friends as $friend)
        <div class="contact-item">
            <img src="{{ $friend->avatar ?? 'https://i.pravatar.cc/150?u=' . $friend->id }}" class="user-pic-small">
            <a href="{{ route('profile.show', $friend->id) }}"><span>{{ $friend->name }}</span></a>
            <form action="{{ route('friends.remove', $friend->id) }}" method="POST" style="margin-left:auto;">
                @csrf
                @method('DELETE')
                <button type="submit" style="border:none;background:transparent;color:#f3425f;cursor:pointer;">Hủy</button>
            </form>
        </div>
        @empty
        <div class="card1">Chưa có bạn bè nào. Hãy tìm kiếm và kết bạn!</div>
        @endforelse
    </aside>
</div>

@endsection