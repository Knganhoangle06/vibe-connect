@extends('layouts.app')

@section('content')
    <style>
        .btn-facebook-secondary {
            background-color: #e4e6eb;
            color: #050505;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: background-color 0.2s ease;
        }

        .btn-facebook-secondary:hover {
            background-color: #d8dadf;
        }

        .header-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            /* Thêm khoảng đệm để nút không bị sát viền */
        }
    </style>

    <div class="profile-container">
        <section class="profile-header-section">
            <div class="cover-photo-wrapper">
                {{-- Kiểm tra ảnh bìa: nếu có thì hiện, không thì hiện placeholder --}}
                <img src="{{ $user->background ? (filter_var($user->background, FILTER_VALIDATE_URL) ? $user->background : asset('storage/' . $user->background)) : 'https://via.placeholder.com/1100x400' }}"
                    class="cover-img">

                @if ($isMe)
                    <button class="btn-edit-cover" onclick="openModal()"><i class="fa-solid fa-camera"></i> Chỉnh sửa ảnh
                        bìa</button>
                @endif
            </div>

            <div class="header-details">
                <div class="avatar-container">
                    <img src="{{ $user->avatar ? (filter_var($user->avatar, FILTER_VALIDATE_URL) ? $user->avatar : asset('storage/' . $user->avatar)) : asset('images/default-avatar.png') }}"
                        alt="Avatar" class="avatar-img">
                    @if ($isMe)
                        <div class="upload-avatar-badge" onclick="openModal()"><i class="fa-solid fa-camera"></i></div>
                    @endif
                </div>

                <div class="user-meta-info">
                    <h1>{{ $user->name }}</h1>
                    <p class="friend-count">{{ $stats['friends'] }} bạn bè</p>
                    <p class="post-count">{{ $stats['posts'] }} bài viết</p>
                </div>

                <div class="header-actions">
                    @if ($isMe)
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
                        <div class="info-item"><i class="fa-solid fa-clock"></i> Tham gia từ
                            {{ $user->created_at->format('M Y') }}</div>
                    </div>
                </div>
            </aside>

            <main class="profile-main">
                @foreach ($posts as $post)
                    @php
                        $myReaction = $post->reactions->firstWhere('user_id', Auth::id());
                    @endphp
                    <div class="card1">
                        <div class="post-header">
                            <div class="user-info">
                                <a href="{{ route('profile.show', $post->user->id) }}">
                                    <img src="{{ $user->avatar ? (filter_var($user->avatar, FILTER_VALIDATE_URL) ? $user->avatar : asset('storage/' . $user->avatar)) : asset('images/default-avatar.png') }}"
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

                            @if (Auth::id() == $post->user_id)
                                <div class="post-options">
                                    <div class="menu-dots" onclick="toggleMenu(this)">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </div>
                                    <div class="options-menu">
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

                        @if ($post->media_url)
                            @php
                                // Logic kiểm tra: Nếu bắt đầu bằng http thì là link ngoài, ngược lại là file trong máy
                                $isExternal = strpos($post->media_url, 'http') === 0;
                                $mediaSrc = $isExternal ? $post->media_url : asset('storage/' . $post->media_url);
                            @endphp

                            @if ($post->media_type === 'image')
                                <img src="{{ $mediaSrc }}" class="post-img" alt="Post Image">
                            @elseif($post->media_type === 'video')
                                <video controls class="post-video">
                                    <source src="{{ $mediaSrc }}" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ xem video.
                                </video>
                            @endif
                        @endif

                        @if ($post->original_post_id && $post->originalPost)
                            <div class="original-post-box">
                                <div class="post-header" style="margin-bottom: 8px;">
                                    <img src="{{ $post->originalPost->user->avatar }}" class="user-pic"
                                        style="width: 30px; height: 30px;">
                                    <h5 style="font-size: 13px;">{{ $post->originalPost->user->name }}</h5>
                                </div>
                                <p style="font-size: 14px;">{{ $post->originalPost->content }}</p>
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
                                            <button type="submit" class="btn-emoji" title="{{ ucfirst($type) }}">
                                                {{ $emoji }}
                                            </button>
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
                                        <div style="padding: 20px; text-align: center; color: #65676B;">Chưa có cảm xúc
                                            nào.
                                        </div>
                                    @endif
                                </div>
                            </div>
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
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                class="modal-form">
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

    <script>
        // Mở modal chỉnh sửa cá nhân
        function openModal() {
            document.getElementById('editProfileModal').style.display = 'flex';
        }

        // Đóng modal chỉnh sửa cá nhân
        function closeModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        // Đóng mở menu tùy chọn bài viết (Sửa/Xóa)
        function toggleMenu(element) {
            const menu = element.nextElementSibling;
            document.querySelectorAll('.options-menu').forEach(m => {
                if (m !== menu) m.classList.remove('active');
            });
            if (menu) menu.classList.toggle('active');
        }

        // JS Modal Cảm Xúc
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
