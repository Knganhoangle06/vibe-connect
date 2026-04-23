@extends('layouts.app')

@section('content')
    <div style="max-width: 800px; margin: 20px auto; padding: 0 15px;">

        <main class="content-center" style="width: 100%;">
            <div style="margin-bottom: 15px;">
                <a href="{{ route('home') }}"
                    style="color: var(--main-blue); font-weight: bold; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại Bảng tin
                </a>
            </div>

            @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert-danger">{{ session('error') }}</div>
            @endif

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
                            <h4 style="font-size: 15px;"><a
                                    href="{{ route('profile.show', $post->user->id) }}">{{ $post->user->name }}</a></h4>
                            <small>{{ $post->created_at->diffForHumans() }} · <i
                                    class="fa-solid fa-earth-americas"></i></small>
                        </div>
                    </div>

                    @if (Auth::id() == $post->user_id)
                        <div class="post-options">
                            <div class="menu-dots" onclick="toggleMenu(this)">
                                <i class="fa-solid fa-ellipsis"></i>
                            </div>
                            <div class="options-menu">
                                <a href="{{ route('posts.edit', $post->id) }}"><i class="fa-regular fa-pen-to-square"></i>
                                    Chỉnh sửa</a>
                                <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="delete-btn"><i class="fa-regular fa-trash-can"></i> Xóa
                                        bài viết</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($post->content)
                    <p style="margin: 10px 0;">{{ $post->content }}</p>
                @endif

                @if ($post->media_url)
                    @php
                        $isExternal = strpos($post->media_url, 'http') === 0;
                        $mediaSrc = $isExternal ? $post->media_url : asset('storage/' . $post->media_url);
                    @endphp
                    @if ($post->media_type === 'image')
                        <img src="{{ $mediaSrc }}" class="post-img">
                    @elseif($post->media_type === 'video')
                        <video controls class="post-video">
                            <source src="{{ $mediaSrc }}" type="video/mp4">
                        </video>
                    @endif
                @endif

                <div class="post-meta-bar"
                    style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                    <div class="meta-left" onclick="openReactionsModal({{ $post->id }})" style="cursor: pointer;">
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
                    <div style="color: var(--text-gray); font-size: 14px;">
                        {{ $post->comments->count() }} bình luận
                    </div>
                </div>

                <div class="post-footer-actions"
                    style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                    <div class="reaction-wrapper" style="position: relative; display: inline-block; flex: 1;">
                        <div class="reaction-box shadow-sm border" style="margin-bottom: -3px; display: none;">
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

                    <button class="btn-main-action"
                        style="flex: 1; text-align: center; border: none; background: transparent; font-weight: 600; color: var(--text-gray); cursor: pointer; padding: 8px;">
                        <i class="fa-regular fa-comment"></i> Bình luận
                    </button>

                    <form action="{{ route('posts.share', $post->id) }}" method="POST" style="flex: 1;">
                        @csrf
                        <input type="hidden" name="content" value="">
                        <button type="submit" class="btn-main-action"
                            style="width: 100%; border: none; background: transparent; font-weight: 600; color: var(--text-gray); cursor: pointer; padding: 8px;">
                            <i class="fa-solid fa-share"></i> Chia sẻ
                        </button>
                    </form>
                </div>

                <div class="comment-panel" style="padding-top: 15px; display: block;">

                    @foreach ($post->comments->whereNull('parent_id') as $comment)
                        <div class="comment-block" style="margin-bottom: 15px;">

                            <div class="comment-row" style="display: flex; gap: 10px;">
                                <img src="{{ $comment->user->avatar ? (filter_var($comment->user->avatar, FILTER_VALIDATE_URL) ? $comment->user->avatar : asset('storage/' . $comment->user->avatar)) : asset('images/default-avatar.png') }}"
                                    class="user-pic-small">
                                <div style="flex: 1;">
                                    <div
                                        style="background: #f0f2f5; padding: 8px 12px; border-radius: 18px; display: inline-block;">
                                        <strong style="font-size: 14px;">{{ $comment->user->name }}</strong>
                                        <p style="margin: 3px 0 0 0; font-size: 14px;">{{ $comment->content }}</p>
                                    </div>
                                    <div
                                        style="font-size: 12px; color: #65676B; margin-top: 4px; margin-left: 10px; display: flex; gap: 15px;">
                                        <span>{{ $comment->created_at->diffForHumans() }}</span>
                                        <span style="cursor: pointer; font-weight: 600;"
                                            onclick="document.getElementById('reply-form-{{ $comment->id }}').style.display='flex'">Phản
                                            hồi</span>

                                        @if (Auth::id() === $comment->user_id || Auth::id() === $post->user_id)
                                            <form action="{{ route('comments.destroy', $comment->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    style="background: none; border: none; color: #65676B; cursor: pointer; padding: 0; font-size: 12px; font-weight: 600;">Xóa</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div style="margin-left: 45px; margin-top: 10px;">
                                @foreach ($comment->replies as $reply)
                                    <div class="reply-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                        <img src="{{ $reply->user->avatar ? (filter_var($reply->user->avatar, FILTER_VALIDATE_URL) ? $reply->user->avatar : asset('storage/' . $reply->user->avatar)) : asset('images/default-avatar.png') }}"
                                            class="user-pic-small" style="width: 24px; height: 24px;">
                                        <div style="flex: 1;">
                                            <div
                                                style="background: #f0f2f5; padding: 6px 10px; border-radius: 18px; display: inline-block;">
                                                <strong style="font-size: 13px;">{{ $reply->user->name }}</strong>
                                                <p style="margin: 2px 0 0 0; font-size: 13px;">{{ $reply->content }}</p>
                                            </div>
                                            <div
                                                style="font-size: 11px; color: #65676B; margin-top: 4px; margin-left: 10px; display: flex; gap: 15px;">
                                                <span>{{ $reply->created_at->diffForHumans() }}</span>

                                                @if (Auth::id() === $reply->user_id || Auth::id() === $post->user_id)
                                                    <form action="{{ route('comments.destroy', $reply->id) }}"
                                                        method="POST">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            style="background: none; border: none; color: #65676B; cursor: pointer; padding: 0; font-size: 11px; font-weight: 600;">Xóa</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <form id="reply-form-{{ $comment->id }}"
                                    action="{{ route('comments.store', $post->id) }}" method="POST"
                                    style="display: none; gap: 8px; margin-top: 10px; align-items: center;">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <img src="{{ Auth::user()->avatar ? (filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : asset('images/default-avatar.png') }}"
                                        class="user-pic-small" style="width: 24px; height: 24px;">
                                    <input type="text" name="content"
                                        placeholder="Phản hồi {{ $comment->user->name }}..."
                                        style="flex: 1; border: 1px solid #ccd0d5; border-radius: 20px; padding: 6px 12px; font-size: 13px; outline: none; background: #f0f2f5;"
                                        required>
                                    <button type="submit"
                                        style="background: transparent; border: none; color: var(--main-blue); cursor: pointer; font-size: 18px;"><i
                                            class="fa-solid fa-paper-plane"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <form action="{{ route('comments.store', $post->id) }}" method="POST"
                        style="display: flex; gap: 10px; margin-top: 15px; align-items: center;">
                        @csrf
                        <img src="{{ Auth::user()->avatar ? (filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : asset('images/default-avatar.png') }}"
                            class="user-pic-small">
                        <input type="text" name="content" placeholder="Viết bình luận..."
                            style="flex: 1; border: 1px solid #ccd0d5; border-radius: 20px; padding: 8px 15px; font-size: 14px; outline: none; background: #f0f2f5;"
                            required>
                        <button type="submit"
                            style="background: transparent; border: none; color: var(--main-blue); cursor: pointer; font-size: 20px;"><i
                                class="fa-solid fa-paper-plane"></i></button>
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
                                <div style="padding: 20px; text-align: center; color: #65676B;">Chưa có cảm xúc nào.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Modal Dropdown Chỉnh sửa
        function toggleMenu(element) {
            const menu = element.nextElementSibling;
            document.querySelectorAll('.options-menu').forEach(m => {
                if (m !== menu) m.classList.remove('active');
            });
            if (menu) menu.classList.toggle('active');
        }

        // Modal Cảm xúc
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
        });
    </script>
@endsection
