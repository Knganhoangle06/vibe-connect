@props(['targetUser'])

@php
    // Tránh lỗi nếu chưa đăng nhập hoặc đang tự xem bài viết của chính mình
    if (!Auth::check() || Auth::id() === $targetUser->id) {
        $friendship = 'self';
    } else {
        // Truy vấn xem 2 người đã có mối quan hệ (pending hoặc accepted) nào chưa
        $friendship = \App\Models\Friendship::where(function ($query) use ($targetUser) {
            $query->where('sender_id', Auth::id())->where('receiver_id', $targetUser->id);
        })
            ->orWhere(function ($query) use ($targetUser) {
                $query->where('sender_id', $targetUser->id)->where('receiver_id', Auth::id());
            })
            ->first();
    }
@endphp

@if ($friendship !== 'self')
    <div class="friend-button-wrapper" data-user-id="{{ $targetUser->id }}" style="margin: 0;">
        @if (!$friendship)
            <form action="{{ route('friendships.add', $targetUser->id) }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit"
                    style="background: #e7f3ff; color: #1877f2; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-user-plus"></i> Add friend
                </button>
            </form>
        @elseif($friendship->status === 'accepted')
            <form action="{{ route('friendships.remove', $targetUser->id) }}" method="POST" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit"
                    style="background: #e4e6eb; color: #050505; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-user-check"></i> Bạn bè
                </button>
            </form>
        @elseif($friendship->sender_id === Auth::id())
            <form action="{{ route('friendships.remove', $targetUser->id) }}" method="POST" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit"
                    style="background: #e4e6eb; color: #050505; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-clock-rotate-left"></i> Đang chờ
                </button>
            </form>
        @elseif($friendship->receiver_id === Auth::id())
            <div style="display: flex; gap: 8px;">
                <form action="{{ route('friendships.accept', $targetUser->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit"
                        style="background: #1877f2; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                        Xác nhận
                    </button>
                </form>
            </div>
        @endif
    </div>
@endif
