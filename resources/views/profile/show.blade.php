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
                    <div class="card1">
                        @include('partials.post_card', ['post' => $post])
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
                @csrf

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
                @csrf
                @method('PATCH')
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

        // Thay đổi quyền riêng tư
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
            if (event.target.classList.contains('fb-modal') && event.target.id.startsWith('shareModal-')) {
                event.target.style.display = 'none';
            }
        });
    </script>
@endsection
