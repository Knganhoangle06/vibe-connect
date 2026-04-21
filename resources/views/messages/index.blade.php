@extends('layouts.app')

@section('content')
<div class="main-layout-container" style="display: block; max-width: 1400px; padding: 0 20px;">
    <div class="messenger-container">

        <div class="messenger-sidebar">
            <div class="messenger-header">Đoạn chat</div>
            <div class="conversation-list">
                <div style="padding: 10px 15px; font-weight: 600; color: var(--text-gray); font-size: 0.9rem;">Bạn bè</div>
                <div style="display: flex; overflow-x: auto; padding: 0 15px 10px; gap: 10px;">
                    @foreach($friends as $friend)
                        <form action="{{ route('messages.start', $friend->id) }}" method="POST">
                            @csrf
                            <button type="submit" style="background: none; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                                <div class="user-avatar-wrapper">
                                    <img src="{{ $friend->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar-small">
                                    <div class="status-dot user-status-{{ $friend->id }}"></div>
                                </div>
                                <span style="font-size: 0.8rem; white-space: nowrap;">{{ explode(' ', $friend->name)[0] }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>

                <div style="padding: 10px 15px; font-weight: 600; color: var(--text-gray); font-size: 0.9rem; border-top: 1px solid #ced0d4;">Gần đây</div>
                @foreach($conversations as $conv)
                    @php
                        $otherUser = $conv->users->where('id', '!=', Auth::id())->first();
                        if(!$otherUser) continue;
                    @endphp
                    <a href="{{ route('messages.index', $conv->id) }}" class="conversation-item {{ isset($activeConversation) && $activeConversation->id === $conv->id ? 'active' : '' }}">
                        <div class="user-avatar-wrapper">
                            <img src="{{ $otherUser->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar">
                            <div class="status-dot user-status-{{ $otherUser->id }}"></div>
                        </div>
                        <div style="flex: 1; overflow: hidden;">
                            <div style="font-weight: 600; color: #050505;">{{ $otherUser->name }}</div>
                            <div style="font-size: 0.85rem; color: var(--text-gray); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                Nhấp để xem trò chuyện
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="messenger-chat-area">
            @if(isset($activeConversation))
                @php $activeOtherUser = $activeConversation->users->where('id', '!=', Auth::id())->first(); @endphp

                <div class="messenger-header" style="display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="user-avatar-wrapper">
                            <img src="{{ $activeOtherUser->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar-small">
                            <div class="status-dot user-status-{{ $activeOtherUser->id }}"></div>
                        </div>
                        <div style="font-size: 1rem; font-weight: 600;">{{ $activeOtherUser->name }}</div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 15px;">
                        <button onclick="alert('Tính năng Gọi thoại đang được phát triển!')" title="Bắt đầu gọi thoại" style="background: none; border: none; color: var(--main-blue); cursor: pointer; font-size: 1.2rem; padding: 5px;">
                            <i class="fa-solid fa-phone"></i>
                        </button>

                        <button onclick="alert('Tính năng Gọi video đang được phát triển!')" title="Bắt đầu gọi video" style="background: none; border: none; color: var(--main-blue); cursor: pointer; font-size: 1.2rem; padding: 5px;">
                            <i class="fa-solid fa-video"></i>
                        </button>

                        <div style="width: 1px; height: 20px; background-color: #ced0d4;"></div>

                        <button onclick="deleteConversation({{ $activeConversation->id }})" title="Xóa đoạn chat này" style="background: none; border: none; color: #f3425f; cursor: pointer; font-size: 1.2rem; padding: 5px;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="chat-messages" id="chat-messages">
                    @foreach($messages as $msg)
                        <div class="message-wrapper {{ $msg->sender_id === Auth::id() ? 'sent' : 'received' }}" id="msg-{{ $msg->id }}">
                            @if($msg->sender_id === Auth::id())
                                <button class="msg-unsend-btn" onclick="unsendMessage({{ $msg->id }})" title="Thu hồi tin nhắn"><i class="fa-solid fa-rotate-left"></i></button>
                            @endif
                            <div class="message-bubble {{ $msg->sender_id === Auth::id() ? 'sent' : 'received' }}">
                                {{ $msg->content }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <form id="chat-form" action="{{ route('messages.store', $activeConversation->id) }}" method="POST" class="chat-input-area">
                    @csrf
                    <input type="text" id="chat-input" name="content" placeholder="Nhập tin nhắn..." required autocomplete="off" style="flex: 1; border: none; border-radius: 20px; background: #f0f2f5; padding: 10px 15px; outline: none; font-size: 1rem;">
                    <button type="submit" style="background: none; border: none; color: var(--main-blue); cursor: pointer; font-size: 1.5rem; padding-right: 10px;"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            @else
                <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: var(--text-gray); font-size: 1.1rem;">
                    Chọn một đoạn chat hoặc bắt đầu cuộc trò chuyện mới.
                </div>
            @endif
        </div>
    </div>
</div>

<script type="module">
    document.addEventListener("DOMContentLoaded", function() {
        const chatBox = document.getElementById("chat-messages");
        const csrfToken = document.querySelector('input[name="_token"]').value;

        if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;

        @if(isset($activeConversation))
            const conversationId = {{ $activeConversation->id }};
            const authUserId = {{ Auth::id() }};

            // LẮNG NGHE SÓNG TỪ MÁY CHỦ (NHẬN VÀ XÓA REAL-TIME)
            if (window.Echo) {
                window.Echo.private(`conversation.${conversationId}`)
                    // 1. Lắng nghe tin nhắn MỚI
                    .listen('MessageSent', (e) => {
                        if (e.message.sender_id === authUserId) return;
                        const msgHtml = `
                            <div class="message-wrapper received" id="msg-${e.message.id}">
                                <div class="message-bubble received">${e.message.content}</div>
                            </div>`;
                        chatBox.insertAdjacentHTML('beforeend', msgHtml);
                        chatBox.scrollTop = chatBox.scrollHeight;
                    })
                    // 2. Lắng nghe lệnh THU HỒI
                    .listen('MessageDeleted', (e) => {
                        const targetMsg = document.getElementById(`msg-${e.messageId}`);
                        if (targetMsg) targetMsg.remove();
                    });
            }

            // GỬI TIN NHẮN (AJAX)
            const chatForm = document.getElementById('chat-form');
            const chatInput = document.getElementById('chat-input');

            if (chatForm) {
                chatForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const content = chatInput.value.trim();
                    if (!content) return;

                    chatInput.value = '';

                    fetch(chatForm.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content: content })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const myMsgHtml = `
                                <div class="message-wrapper sent" id="msg-${data.message.id}">
                                    <button class="msg-unsend-btn" onclick="unsendMessage(${data.message.id})" title="Thu hồi tin nhắn"><i class="fa-solid fa-rotate-left"></i></button>
                                    <div class="message-bubble sent">${data.message.content}</div>
                                </div>`;
                            chatBox.insertAdjacentHTML('beforeend', myMsgHtml);
                            chatBox.scrollTop = chatBox.scrollHeight;
                        }
                    });
                });
            }

            // HÀM: THU HỒI TIN NHẮN
            window.unsendMessage = function(messageId) {
                if(!confirm("Bạn có chắc chắn muốn thu hồi tin nhắn này?")) return;

                fetch(`/messages/${messageId}/unsend`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        const el = document.getElementById(`msg-${messageId}`);
                        if (el) el.remove();
                    }
                });
            };

            // HÀM: XÓA ĐOẠN CHAT
            window.deleteConversation = function(convId) {
                if(!confirm("Xóa đoạn chat này khỏi hộp thư của bạn? (Người kia vẫn sẽ nhìn thấy)")) return;

                fetch(`/messages/conversation/${convId}/remove`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        window.location.href = '/messages';
                    }
                });
            };
        @endif
    });
</script>
@endsection
