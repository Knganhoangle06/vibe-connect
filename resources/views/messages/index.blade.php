@extends('layouts.app')

@section('styles')
<style>
    .messenger-wrapper { display: flex; height: calc(100vh - 76px); background: #fff; border-radius: 8px; box-shadow: var(--shadow); overflow: hidden; margin-top: 10px; }
    .messenger-sidebar { width: 350px; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; background: #fff; }
    .messenger-chat-area { flex: 1; display: flex; flex-direction: column; background: #fff; }
    .messenger-header { padding: 15px; border-bottom: 1px solid var(--border-color); font-weight: bold; font-size: 1.2rem; background: #fff; display: flex; align-items: center; justify-content: space-between; }
    .conversation-list { flex: 1; overflow-y: auto; }
    .conversation-item { display: flex; align-items: center; gap: 10px; padding: 10px 15px; cursor: pointer; transition: background 0.2s; }
    .conversation-item:hover, .conversation-item.active { background: var(--bg-color); }
    .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #f0f2f5; }
    .chat-input-area { padding: 15px; background: white; border-top: 1px solid var(--border-color); display: flex; gap: 10px; }

    .message-wrapper { display: flex; align-items: center; gap: 10px; max-width: 100%; position: relative; }
    .message-wrapper.sent { justify-content: flex-end; }
    .message-wrapper.received { justify-content: flex-start; }
    .message-bubble { max-width: 60%; padding: 10px 15px; border-radius: 18px; font-size: 0.95rem; word-wrap: break-word; }
    .message-bubble.received { background: var(--border-color); color: #050505; }
    .message-bubble.sent { background: var(--main-blue); color: white; }

    .msg-unsend-btn { opacity: 0; background: none; border: none; color: #f3425f; cursor: pointer; font-size: 0.9rem; padding: 5px; transition: opacity 0.2s; border-radius: 50%; }
    .msg-unsend-btn:hover { background: #f2f2f2; }
    .message-wrapper.sent:hover .msg-unsend-btn { opacity: 1; }

    /* Chấm xanh */
    .user-avatar-wrapper { position: relative; display: inline-flex; }
    .status-dot { position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px; background-color: #31a24c; border: 2px solid white; border-radius: 50%; display: none; }
    .status-dot.online { display: block; }
</style>
@endsection

@section('content')
<div class="main-layout" style="display: block; max-width: 1300px;">
    <div class="messenger-wrapper">
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
                                    <img src="{{ $friend->avatar ? (filter_var($friend->avatar, FILTER_VALIDATE_URL) ? $friend->avatar : asset('storage/' . $friend->avatar)) : asset('images/default-avatar.png') }}" class="user-pic-small" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                    <div class="status-dot user-status-{{ $friend->id }}"></div>
                                </div>
                                <span style="font-size: 0.8rem; white-space: nowrap; color: #1c1e21;">{{ explode(' ', $friend->name)[0] }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>

                <div style="padding: 10px 15px; font-weight: 600; color: var(--text-gray); font-size: 0.9rem; border-top: 1px solid var(--border-color);">Gần đây</div>
                @foreach($conversations as $conv)
                    @php
                        $otherUser = $conv->users->where('id', '!=', Auth::id())->first();
                        if(!$otherUser) continue;
                    @endphp
                    <a href="{{ route('messages.index', $conv->id) }}" class="conversation-item {{ isset($activeConversation) && $activeConversation->id === $conv->id ? 'active' : '' }}" style="text-decoration:none;">
                        <div class="user-avatar-wrapper">
                            <img src="{{ $otherUser->avatar ? (filter_var($otherUser->avatar, FILTER_VALIDATE_URL) ? $otherUser->avatar : asset('storage/' . $otherUser->avatar)) : asset('images/default-avatar.png') }}" class="user-pic-small" style="width:48px; height:48px; border-radius:50%; object-fit:cover;">
                            <div class="status-dot user-status-{{ $otherUser->id }}"></div>
                        </div>
                        <div style="flex: 1; overflow: hidden; color: #1c1e21;">
                            <div style="font-weight: 600;">{{ $otherUser->name }}</div>
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
                <div class="messenger-header">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="user-avatar-wrapper">
                            <img src="{{ $activeOtherUser->avatar ? (filter_var($activeOtherUser->avatar, FILTER_VALIDATE_URL) ? $activeOtherUser->avatar : asset('storage/' . $activeOtherUser->avatar)) : asset('images/default-avatar.png') }}" class="user-pic-small" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                            <div class="status-dot user-status-{{ $activeOtherUser->id }}"></div>
                        </div>
                        <div style="font-size: 1rem; font-weight:600; color: #1c1e21;">{{ $activeOtherUser->name }}</div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 15px;">
                        <button onclick="alert('Tính năng Gọi thoại đang được phát triển!')" title="Bắt đầu gọi thoại" style="background: none; border: none; color: var(--main-blue); cursor: pointer; font-size: 1.2rem; padding: 5px;"><i class="fa-solid fa-phone"></i></button>
                        <button onclick="alert('Tính năng Gọi video đang được phát triển!')" title="Bắt đầu gọi video" style="background: none; border: none; color: var(--main-blue); cursor: pointer; font-size: 1.2rem; padding: 5px;"><i class="fa-solid fa-video"></i></button>
                        <div style="width: 1px; height: 20px; background-color: var(--border-color);"></div>
                        <button onclick="deleteConversation({{ $activeConversation->id }})" title="Xóa đoạn chat này" style="background: none; border: none; color: #f3425f; cursor: pointer; font-size: 1.2rem; padding: 5px;"><i class="fa-solid fa-trash"></i></button>
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
                    <input type="text" id="chat-input" name="content" placeholder="Nhập tin nhắn..." required autocomplete="off" style="flex: 1; border: none; border-radius: 20px; background: var(--bg-color); padding: 10px 15px; outline: none; font-size: 1rem; color: #1c1e21;">
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
@endsection

@section('script')
<script type="module">
    document.addEventListener("DOMContentLoaded", function() {
        const chatBox = document.getElementById("chat-messages");
        const csrfToken = document.querySelector('input[name="_token"]').value;

        if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;

        @if(isset($activeConversation))
            const conversationId = {{ $activeConversation->id }};
            const authUserId = {{ Auth::id() }};

            if (window.Echo) {
                window.Echo.private(`conversation.${conversationId}`)
                    .listen('MessageSent', (e) => {
                        if (e.message.sender_id === authUserId) return;
                        const msgHtml = `<div class="message-wrapper received" id="msg-${e.message.id}"><div class="message-bubble received">${e.message.content}</div></div>`;
                        chatBox.insertAdjacentHTML('beforeend', msgHtml);
                        chatBox.scrollTop = chatBox.scrollHeight;
                    })
                    .listen('MessageDeleted', (e) => {
                        const targetMsg = document.getElementById(`msg-${e.messageId}`);
                        if (targetMsg) targetMsg.remove();
                    });
            }

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
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ content: content })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const myMsgHtml = `<div class="message-wrapper sent" id="msg-${data.message.id}"><button class="msg-unsend-btn" onclick="unsendMessage(${data.message.id})" title="Thu hồi tin nhắn"><i class="fa-solid fa-rotate-left"></i></button><div class="message-bubble sent">${data.message.content}</div></div>`;
                            chatBox.insertAdjacentHTML('beforeend', myMsgHtml);
                            chatBox.scrollTop = chatBox.scrollHeight;
                        }
                    });
                });
            }

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

            window.deleteConversation = function(convId) {
                if(!confirm("Xóa đoạn chat này khỏi hộp thư của bạn?")) return;
                fetch(`/messages/conversation/${convId}/remove`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                }).then(res => res.json()).then(data => {
                    if (data.success) window.location.href = '/messages';
                });
            };
        @endif
    });
</script>
@endsection
