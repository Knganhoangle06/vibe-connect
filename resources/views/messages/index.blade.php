@extends('user.master')

@section('content')
<div class="main-layout">
    <aside class="sidebar-left">
        <div class="card">
            <h3>Tin nhắn</h3>
            <p style="color:#65676b;font-size:13px;margin-top:4px;">Chọn một người bạn để bắt đầu trò chuyện.</p>
        </div>
        <div class="card">
            @forelse($friends as $friend)
                <a href="{{ route('messages.index', ['friend_id' => $friend->id]) }}" class="contact-item {{ request('friend_id') == $friend->id ? 'active' : '' }}">
                    <img src="{{ $friend->avatar ?? 'https://i.pravatar.cc/150?u=' . $friend->id }}" class="user-pic-small">
                    <span>{{ $friend->name }}</span>
                </a>
            @empty
                <p>Hiện chưa có bạn bè để nhắn tin.</p>
            @endforelse
        </div>
    </aside>

    <main class="content-center">
        @if (session('success'))
            <div class="card" style="color:green;">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="card" style="color:#b3261e;">{{ session('error') }}</div>
        @endif

        @if($selectedConversation)
            <div class="card">
                <h3>Đoạn chat</h3>
                <div class="chat-box">
                    @forelse($messages as $message)
                        <div class="chat-row {{ $message->sender_id === Auth::id() ? 'mine' : '' }}">
                            <div class="chat-bubble">
                                <div class="chat-sender">{{ $message->sender->name }}</div>
                                <div>{{ $message->content }}</div>
                                <small>{{ $message->created_at->format('H:i d/m') }}</small>
                            </div>
                        </div>
                    @empty
                        <p style="color:#65676b;">Chưa có tin nhắn nào.</p>
                    @endforelse
                </div>
                <form action="{{ route('messages.send', $selectedConversation->id) }}" method="POST" class="chat-form">
                    @csrf
                    <input type="text" name="content" placeholder="Nhập tin nhắn..." required>
                    <button type="submit" class="btn-post">Gửi</button>
                </form>
            </div>
        @else
            <div class="card">
                <p>Hãy chọn một người bạn bên trái để bắt đầu nhắn tin.</p>
            </div>
        @endif
    </main>

    <aside class="sidebar-right">
        <div class="card">
            <h4>Gợi ý</h4>
            <p style="color:#65676b;">Tính năng hiện hỗ trợ chat 1-1 giữa các tài khoản đã kết bạn.</p>
        </div>
    </aside>
</div>
@endsection
