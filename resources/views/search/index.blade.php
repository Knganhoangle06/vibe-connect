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
                    <div class="card">
                        @include('partials.post_card', ['post' => $post])
                    </div>
                @empty
                    <p class="empty-msg">Không tìm thấy bài đăng phù hợp.</p>
                @endforelse
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
                }
    </script>
@endsection
