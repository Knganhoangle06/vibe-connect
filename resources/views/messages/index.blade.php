@extends('layouts.app')

@section('styles')
    <style>
        /* CSS ĐỘC LẬP CHO MESSENGER (Không ảnh hưởng trang khác) */
        .msg-container {
            display: flex;
            height: calc(100vh - 80px);
            max-width: 1200px;
            margin: 10px auto;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .msg-sidebar {
            width: 350px;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            background: var(--white);
        }

        .msg-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .msg-header {
            padding: 15px 20px;
            font-weight: bold;
            font-size: 1.2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .msg-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }

        .msg-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            cursor: pointer;
            text-decoration: none;
            color: #050505;
            transition: 0.2s;
        }

        .msg-item:hover,
        .msg-item.active {
            background: var(--bg-color);
        }

        .chat-box {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #f0f2f5;
        }

        .chat-input-wrapper {
            padding: 15px 20px;
            background: var(--white);
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 10px;
        }

        .chat-input {
            flex: 1;
            border: none;
            background: var(--bg-color);
            padding: 12px 20px;
            border-radius: 50px;
            outline: none;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .btn-send {
            background: transparent;
            border: none;
            color: var(--main-blue);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0 10px;
            transition: 0.2s;
        }

        .btn-send:hover {
            transform: scale(1.1);
        }

        .bubble-wrapper {
            display: flex;
            max-width: 100%;
        }

        .bubble-wrapper.sent {
            justify-content: flex-end;
        }

        .bubble-wrapper.received {
            justify-content: flex-start;
        }

        .bubble {
            max-width: 65%;
            padding: 10px 15px;
            border-radius: 18px;
            font-size: 0.95rem;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .bubble.sent {
            background: var(--main-blue);
            color: var(--white);
            border-bottom-right-radius: 4px;
        }

        .bubble.received {
            background: #e4e6eb;
            color: #050505;
            border-bottom-left-radius: 4px;
        }

        .status-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 14px;
            height: 14px;
            background: #31a24c;
            border: 2px solid #fff;
            border-radius: 50%;
            display: none;
        }

        .status-badge.online {
            display: block;
        }

        /* CSS cho nút thu hồi tin nhắn */
        .msg-unsend-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 0.9rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .bubble-wrapper:hover .msg-unsend-btn {
            opacity: 0.6;
        }

        .msg-unsend-btn:hover {
            opacity: 1 !important;
        }
    </style>
@endsection

@section('content')
    <div class="msg-container">
        <div class="msg-sidebar">
            <div class="msg-header">Tin nhắn</div>
            <div class="msg-list">
                <div style="padding: 0 20px 10px; font-weight: 600; color: var(--text-gray); font-size: 0.85rem;">BẠN BÈ (BẮT
                    ĐẦU CHAT MỚI)</div>
                <div
                    style="display: flex; overflow-x: auto; padding: 0 20px 15px; gap: 12px; border-bottom: 1px solid var(--border-color);">
                    @foreach ($friends as $friend)
                        <a href="{{ route('messages.create', $friend->id) }}"
                            style="flex-shrink: 0; text-decoration: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                            <div style="position: relative;">
                                <img src="{{ $friend->avatar ?? asset('images/default-avatar.png') }}"
                                    style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                <div class="status-badge user-status-{{ $friend->id }}"></div>
                            </div>
                            <span
                                style="font-size: 0.75rem; font-weight: 500; color: #050505;">{{ explode(' ', $friend->name)[0] }}</span>
                        </a>
                    @endforeach
                </div>

                <div style="padding: 15px 20px 5px; font-weight: 600; color: var(--text-gray); font-size: 0.85rem;">GẦN ĐÂY
                </div>
                @foreach ($conversations as $conv)
                    @php
                        $otherUser = $conv->users->where('id', '!=', Auth::id())->first();
                        if (!$otherUser) {
                            continue;
                        }
                        $lastMsg = $conv->messages->first();
                    @endphp
                    <a href="{{ route('messages.index', $conv->id) }}"
                        class="msg-item {{ isset($activeConversation) && $activeConversation->id === $conv->id ? 'active' : '' }}">
                        <div style="position: relative;">
                            <img src="{{ $otherUser->avatar ?? asset('images/default-avatar.png') }}"
                                style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                            <div class="status-badge user-status-{{ $otherUser->id }}"></div>
                        </div>
                        <div style="flex: 1; overflow: hidden;">
                            <div style="font-weight: 600;">{{ $otherUser->name }}</div>
                            <div
                                style="font-size: 0.85rem; color: var(--text-gray); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $lastMsg ? ($lastMsg->sender_id === Auth::id() ? 'Bạn: ' : '') . $lastMsg->content : 'Nhấp để trò chuyện' }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="msg-main">
            @if (isset($activeConversation))
                @php $activeUser = $activeConversation->users->where('id', '!=', Auth::id())->first(); @endphp
                <div class="msg-header">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="{{ $activeUser->avatar ?? asset('images/default-avatar.png') }}"
                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <span>{{ $activeUser->name }}</span>
                    </div>

                    <form action="{{ route('messages.remove_conversation', $activeConversation->id) }}" method="POST"
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ cuộc trò chuyện này không?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            style="background: none; border: none; color: var(--text-gray); cursor: pointer; font-size: 1.1rem; transition: 0.2s;"
                            onmouseover="this.style.color='#dc3545'" onmouseout="this.style.color='var(--text-gray)'"
                            title="Xóa cuộc trò chuyện">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>

                <div class="chat-box" id="chatBox">
                    @foreach ($messages as $msg)
                        <div class="bubble-wrapper {{ $msg->sender_id === Auth::id() ? 'sent' : 'received' }}"
                            style="align-items: center; gap: 8px;">
                            @if ($msg->sender_id === Auth::id())
                                <form action="{{ route('messages.unsend', $msg->id) }}" method="POST"
                                    style="margin: 0; display: flex;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="msg-unsend-btn" title="Thu hồi tin nhắn"
                                        onclick="return confirm('Thu hồi tin nhắn này?');">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                            @endif
                            <div class="bubble {{ $msg->sender_id === Auth::id() ? 'sent' : 'received' }}">
                                {{ $msg->content }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <form id="chatForm" action="{{ route('messages.store', $activeConversation->id) }}"
                    class="chat-input-wrapper">
                    @csrf
                    <input type="text" id="chatInput" name="content" class="chat-input"
                        placeholder="Nhắn tin cho {{ $activeUser->name }}..." required autocomplete="off">
                    <button type="submit" class="btn-send"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            @else
                <div
                    style="flex: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; color: var(--text-gray);">
                    <i class="fa-brands fa-facebook-messenger"
                        style="font-size: 4rem; margin-bottom: 15px; color: #ced0d4;"></i>
                    <h3 style="margin: 0;">Chọn một đoạn chat</h3>
                    <p>Hoặc bắt đầu cuộc trò chuyện mới từ danh sách bạn bè bên trái.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chatBox = document.getElementById("chatBox");
            const chatForm = document.getElementById("chatForm");
            const chatInput = document.getElementById("chatInput");
            const authId = {{ Auth::id() }};

            // 1. Cuộn xuống cuối cùng khi vừa mở trang
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            // 2. Lắng nghe WebSockets (Laravel Echo)
            @if (isset($activeConversation))
                const convId = {{ $activeConversation->id }};
                if (window.Echo) {
                    window.Echo.private(`conversation.${convId}`)
                        .listen('MessageSent', (e) => {
                            // Nếu tin nhắn là của mình thì bỏ qua (vì JS đã tự render rồi)
                            if (e.message.sender_id === authId) return;

                            // Render tin nhắn của người kia
                            const msgHtml = `
                            <div class="bubble-wrapper received">
                                <div class="bubble received">${e.message.content}</div>
                            </div>
                        `;
                            chatBox.insertAdjacentHTML('beforeend', msgHtml);
                            chatBox.scrollTop = chatBox.scrollHeight; // Cuộn xuống
                        });
                }
            @endif

            // 3. Gửi tin nhắn bằng Fetch API (Không load trang)
            if (chatForm) {
                chatForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Chặn hành vi submit mặc định

                    const content = chatInput.value.trim();
                    const token = document.querySelector('input[name="_token"]').value;
                    const url = chatForm.getAttribute('action');

                    if (!content) return;

                    // Xóa ô input ngay lập tức
                    chatInput.value = '';

                    // Gửi request ngầm
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                content: content
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Tự render tin nhắn của chính mình lên UI
                                const myMsgHtml = `
                            <div class="bubble-wrapper sent">
                                <div class="bubble sent">${data.message.content}</div>
                            </div>
                        `;
                                chatBox.insertAdjacentHTML('beforeend', myMsgHtml);
                                chatBox.scrollTop = chatBox.scrollHeight;
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            }
        });
    </script>
@endsection
