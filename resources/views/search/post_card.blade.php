@php
    $myReaction = $post->reactions->firstWhere('user_id', Auth::id());
    $reactionEmojiMap = [
        'like' => '👍',
        'love' => '❤️',
        'haha' => '😆',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😡',
    ];
    $reactionGroups = $post->reactions->groupBy('type');
@endphp

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
                <a href="{{ route('posts.edit', $post->id) }}"><i class="fa-regular fa-pen-to-square"></i> Chỉnh
                    sửa</a>
                <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="delete-btn"><i class="fa-regular fa-trash-can"></i> Xóa bài
                        viết</button>
                </form>
            </div>
        </div>
    @endif
</div>

@if ($post->content)
    <p style="margin: 10px 0;">{!! nl2br(e($post->content)) !!}</p>
@endif

@if ($post->media && $post->media->isNotEmpty())
    <div class="post-media-gallery" style="margin-top: 10px;">
        @foreach ($post->media as $media_item)
            @if ($media_item->file_type === 'image')
                <a href="{{ route('posts.show', $post->id) }}">
                    <img src="{{ Storage::url($media_item->file_path) }}" class="post-img" style="margin-bottom: 5px;">
                </a>
            @elseif ($media_item->file_type === 'video')
                <video controls class="post-video" style="margin-bottom: 5px;">
                    <source src="{{ Storage::url($media_item->file_path) }}">
                </video>
            @endif
        @endforeach
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
            <p style="font-size: 14px; margin: 0 0 10px 0;">{!! nl2br(e($originalPost->content)) !!}</p>
        @endif

        @if ($originalPost->media->isNotEmpty())
            <div class="post-media-gallery">
                @foreach ($originalPost->media as $media_item)
                    @if ($media_item->file_type === 'image')
                        <a href="{{ route('posts.show', $originalPost->id) }}">
                            <img src="{{ Storage::url($media_item->file_path) }}" class="post-img"
                                style="margin-bottom: 5px;">
                        </a>
                    @elseif ($media_item->file_type === 'video')
                        <video controls class="post-video" style="margin-bottom: 5px;">
                            <source src="{{ Storage::url($media_item->file_path) }}">
                        </video>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endif

<div class="post-meta-bar"
    style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
    <div class="meta-left" onclick="openReactionsModal({{ $post->id }})" style="cursor: pointer;">
        @if ($reactionGroups->isNotEmpty())
            <span class="reaction-summary-icons">
                @foreach ($reactionGroups->keys()->take(3) as $reactionType)
                    <span title="{{ ucfirst($reactionType) }}">{{ $reactionEmojiMap[$reactionType] ?? '👍' }}</span>
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

<div class="post-footer-actions" style="display: flex; justify-content: space-between; padding: 10px 0;">
    <div class="reaction-wrapper" style="position: relative; display: inline-block; flex: 1;">
        <div class="reaction-box shadow-sm border" style="margin-bottom: -3px; display: none;">
            @foreach (['like' => '👍', 'love' => '❤️', 'haha' => '😆', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'] as $type => $emoji)
                <form action="{{ route('posts.reaction.toggle', $post->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <button type="submit" class="btn-emoji" title="{{ ucfirst($type) }}">
                        {{ $emoji }}
                    </button>
                </form>
            @endforeach
        </div>

        <form action="{{ route('posts.reaction.toggle', $post->id) }}" method="POST" style="width: 100%;">
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

    <form action="{{ route('posts.share', $post->id) }}" method="POST" style="flex: 1;">
        @csrf
        <input type="hidden" name="content" value="">
        <button type="submit" class="btn-main-action"
            style="width: 100%; border: none; background: transparent; font-weight: 600; color: var(--text-gray); cursor: pointer; padding: 8px;">
            <i class="fa-solid fa-share"></i> Chia sẻ
        </button>
    </form>
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
        <div class="modal-body" style="max-height: 300px; overflow-y: auto; padding: 0; background: #fff;">
            @forelse ($post->reactions as $reaction)
                <div style="display: flex; align-items: center; padding: 10px 15px; border-bottom: 1px solid #f0f2f5;">
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
            @empty
                <div style="padding: 20px; text-align: center; color: #65676B;">Chưa có cảm xúc
                    nào.
                </div>
            @endforelse
        </div>
    </div>
</div>
