<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Social</title>

    @auth
        <meta name="user-id" content="{{ Auth::id() }}">
    @endauth

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')
</head>

<body>
    @include('layouts.navbar')

    <div class="main-layout">
        <main>
            @yield('content')
        </main>
    </div>

    @yield('scripts')

    <!-- Nơi chứa các thông báo Toast -->
    <div id="toast-container" class="toast-container"></div>

    @auth
        <script type="module">
            if (window.Echo) {
                window.Echo.channel('user.{{ Auth::id() }}')
                    .listen('.FriendshipUpdated', (e) => {
                        // Xác định xem mình đang nói chuyện với ID nào để update button
                        const currentUserId = {{ Auth::id() }};
                        const targetUserId = e.senderId === currentUserId ? e.receiverId : e.senderId;

                        // Tìm tất cả các button của user đó hiện trên page
                        const wrappers = document.querySelectorAll(
                            `.friend-button-wrapper[data-user-id="${targetUserId}"]`);

                        wrappers.forEach(wrapper => {
                            let buttonHtml = '';
                            const csrfToken = '{{ csrf_token() }}';

                            if (e.status === 'accepted') {
                                buttonHtml = `
                                <form action="/friendships/remove/${targetUserId}" method="POST" style="margin: 0;">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" style="background: #e4e6eb; color: #050505; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                        <i class="fa-solid fa-user-check"></i> Bạn bè
                                    </button>
                                </form>
                            `;
                            } else if (e.status === 'declined' || e.status === 'removed') {
                                buttonHtml = `
                                <form action="/friendships/add/${targetUserId}" method="POST" style="margin: 0;">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <button type="submit" style="background: #e7f3ff; color: #1877f2; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                        <i class="fa-solid fa-user-plus"></i> Add friend
                                    </button>
                                </form>
                            `;
                            } else if (e.status === 'pending') {
                                if (e.senderId === currentUserId) {
                                    buttonHtml = `
                                    <form action="/friendships/remove/${targetUserId}" method="POST" style="margin: 0;">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" style="background: #e4e6eb; color: #050505; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                            <i class="fa-solid fa-clock-rotate-left"></i> Đang chờ
                                        </button>
                                    </form>
                                `;
                                } else {
                                    buttonHtml = `
                                    <div style="display: flex; gap: 8px;">
                                        <form action="/friendships/accept/${targetUserId}" method="POST" style="margin: 0;">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <button type="submit" style="background: #1877f2; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                                                Xác nhận
                                            </button>
                                        </form>
                                    </div>
                                `;
                                }
                            }

                            if (buttonHtml !== '') wrapper.innerHTML = buttonHtml;
                        });
                    });
            }
        </script>
    @endauth
</body>

</html>
