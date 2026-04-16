<aside class="sidebar-right">
    @auth
        @php
            // Lấy danh sách các lời mời kết bạn gửi đến mình và đang chờ duyệt
            // Sử dụng 'with' để Eager Load thông tin người gửi, tránh lỗi N+1 Query
            $pendingRequests = \App\Models\Friendship::with('sender')
                ->where('receiver_id', Auth::id())
                ->where('status', 'pending')
                ->latest()
                ->get();
        @endphp

        <div class="friend-requests-section" id="friend-requests-section"
            style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #ced0d4; {{ $pendingRequests->isEmpty() ? 'display: none;' : '' }}">
            <h4 class="sidebar-title"
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                Lời mời kết bạn
                <span id="request-count"
                    style="background: #e41e3f; color: white; border-radius: 50%; padding: 2px 8px; font-size: 0.8rem;">
                    {{ $pendingRequests->count() }}
                </span>
            </h4>

            <div id="requests-container">
                @if ($pendingRequests->isNotEmpty())
                    @foreach ($pendingRequests as $request)
                        <div class="request-card" style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <img src="{{ $request->sender->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar"
                                style="width: 50px; height: 50px; border-radius: 50%;">

                            <div class="request-info" style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <span
                                        style="font-weight: 600; font-size: 0.95rem; color: #050505;">{{ $request->sender->name }}</span>
                                    <small
                                        style="color: #65676b; font-size: 0.8rem;">{{ $request->created_at->diffForHumans() }}</small>
                                </div>

                                <div class="request-actions" style="display: flex; gap: 8px; margin-top: 8px;">
                                    <form action="{{ route('friendships.accept', $request->sender->id) }}" method="POST"
                                        style="flex: 1;">
                                        @csrf
                                        <button type="submit" class="btn-primary"
                                            style="width: 100%; padding: 6px 0; border-radius: 6px; font-size: 0.85rem;">Xác
                                            nhận</button>
                                    </form>

                                    <form action="{{ route('friendships.decline', $request->sender->id) }}" method="POST"
                                        style="flex: 1;">
                                        @csrf
                                        <button type="submit"
                                            style="width: 100%; padding: 6px 0; border-radius: 6px; font-size: 0.85rem; background: #e4e6eb; border: none; color: #050505; font-weight: 600; cursor: pointer;">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <script type="module">
            const initFriendRequestEcho = () => {
                // Đảm bảo thư viện Echo từ Vite đã sẵn sàng
                if (window.Echo) {
                    window.Echo.channel('user.{{ Auth::id() }}')
                        .listen('.FriendRequestSent', (e) => {
                            console.log('📬 Có lời mời kết bạn mới từ:', e.friendship.sender.name);

                            const section = document.getElementById('friend-requests-section');
                            const container = document.getElementById('requests-container');
                            const badge = document.getElementById('request-count');

                            // 1. Hiển thị khối section nếu nó đang bị ẩn
                            section.style.display = 'block';

                            // 2. Tăng số đếm trên thẻ badge màu đỏ
                            let currentCount = parseInt(badge.innerText) || 0;
                            badge.innerText = currentCount + 1;

                            // 3. Khởi tạo cấu trúc HTML cho lời mời mới vừa nhận
                            const cardHTML = `
                                <div class="request-card" style="display: flex; gap: 10px; margin-bottom: 15px;">
                                    <img src="${e.friendship.sender.avatar || 'https://i.pravatar.cc/150'}" class="user-avatar" style="width: 50px; height: 50px; border-radius: 50%;">
                                    <div class="request-info" style="flex: 1;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                            <span style="font-weight: 600; font-size: 0.95rem; color: #050505;">${e.friendship.sender.name}</span>
                                            <small style="color: #1877f2; font-size: 0.8rem; font-weight: 600;">Vừa xong</small>
                                        </div>
                                        <div class="request-actions" style="display: flex; gap: 8px; margin-top: 8px;">
                                            <form action="/friendships/accept/${e.friendship.sender.id}" method="POST" style="flex: 1;">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <button type="submit" class="btn-primary" style="width: 100%; padding: 6px 0; border-radius: 6px; font-size: 0.85rem;">Xác nhận</button>
                                            </form>
                                            <form action="/friendships/decline/${e.friendship.sender.id}" method="POST" style="flex: 1;">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <button type="submit" style="width: 100%; padding: 6px 0; border-radius: 6px; font-size: 0.85rem; background: #e4e6eb; border: none; color: #050505; font-weight: 600; cursor: pointer;">Xóa</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // 4. Bơm HTML lên đầu danh sách
                            container.insertAdjacentHTML('afterbegin', cardHTML);
                        });
                } else {
                    // Nếu Echo chưa load xong, thử lại sau 100ms
                    setTimeout(initFriendRequestEcho, 100);
                }
            };

            // Khởi chạy hàm
            initFriendRequestEcho();
        </script>
    @endauth

    @auth
        @php
            // TÌM BẠN BÈ: Tìm các mối quan hệ có status = 'accepted' liên quan đến user hiện tại
            $acceptedFriendships = \App\Models\Friendship::where('status', 'accepted')
                ->where(function ($query) {
                    $query->where('sender_id', Auth::id())->orWhere('receiver_id', Auth::id());
                })
                ->get();

            // Trích xuất ra mảng ID của bạn bè (loại trừ ID của chính mình)
            $friendIds = $acceptedFriendships->map(function ($f) {
                return $f->sender_id === Auth::id() ? $f->receiver_id : $f->sender_id;
            });

            $friends = \App\Models\User::whereIn('id', $friendIds)->get();
        @endphp

        <h4 class="sidebar-title" id="contact-count-title">Contacts ({{ $friends->count() }})</h4>
        <div class="contact-list" id="contact-list-container">
            @foreach ($friends as $friend)
                <div class="contact-item" data-contact-id="{{ $friend->id }}">
                    <img src="{{ $friend->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar-small"
                        style="width: 36px; height: 36px; border-radius: 50%;">
                    <span>{{ $friend->name }}</span>
                </div>
            @endforeach
        </div>
    @endauth
</aside>
