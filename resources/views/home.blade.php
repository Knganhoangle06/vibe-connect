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

            @if (isset($pendingRequests) && $pendingRequests->count())
                <div class="card" style="margin-top:12px;">
                    <h4>Lời mời kết bạn</h4>
                    @foreach ($pendingRequests as $request)
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
                                    <div class="privacy-badge"><i class="fa-solid fa-users"></i> Bạn bè</div>
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
                                <h4 style="font-size: 15px;"><a
                                        href="{{ route('profile.show', $post->user->id) }}">{{ $post->user->name }}</a>
                                </h4>
                                <small>{{ $post->created_at->diffForHumans() }} · <i
                                        class="fa-solid fa-earth-americas"></i></small>
                            </div>
                        </div>

                        @if (Auth::id() == $post->user_id || Auth::user()?->role === 'admin')
                            <div class="post-options">
                                <div class="menu-dots" onclick="toggleMenu(this)">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </div>
                                <div class="options-menu">
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
                </div>
            @endforeach

            <div class="feed-pagination card">
                {{ $posts->links() }}
            </div>
        </main>

        <aside class="sidebar-right">
            <h4 style="color: var(--text-gray); margin-bottom: 15px; padding-left: 5px;">Người liên hệ</h4>
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
                <div class="card" style="text-align: center; color: var(--text-gray); padding: 15px;">
                    Chưa có bạn bè nào. Hãy khám phá và kết bạn nhé!
                </div>
            @endforelse
        </aside>
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
    </script>
@endsection
