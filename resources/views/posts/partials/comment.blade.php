<div class="comment-item" style="margin-bottom: 15px;">
    <div style="display: flex; gap: 10px;">
        <img src="{{ $comment->user->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar-small"
            style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
        <div style="flex: 1;">
            <div style="background: #f0f2f5; padding: 8px 12px; border-radius: 18px; display: inline-block;">
                <strong style="font-size: 0.9rem; color: #050505;">
                    <a href="{{ route('profile.show', $comment->user->id) }}"
                        style="text-decoration: none; color: inherit;">
                        {{ $comment->user->name }}
                    </a>
                </strong>
                <p style="margin:0; font-size: 0.9rem; color: #050505;">{{ $comment->content }}</p>
            </div>

            <div style="font-size: 0.8rem; margin-top: 4px; margin-left: 12px;">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <span style="color: #65676b;">{{ $comment->created_at->diffForHumans() }}</span>
                    <a href="javascript:void(0)"
                        onclick="const form = document.getElementById('reply-form-{{ $comment->id }}'); form.style.display = form.style.display === 'none' ? 'block' : 'none';"
                        style="color: #65676b; font-weight: bold; text-decoration: none;">Trả lời</a>
                </div>

                <form id="reply-form-{{ $comment->id }}" action="{{ route('comments.store', $post->id) }}"
                    method="POST" style="display: none; margin-top: 10px; width: 100%;">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <div style="display: flex; gap: 5px;">
                        <input type="text" name="content" placeholder="Viết phản hồi..." required
                            style="flex: 1; border: 1px solid #ced0d4; border-radius: 15px; padding: 6px 12px; font-size: 0.85rem; outline: none;">
                        <button type="submit"
                            style="background: none; border: none; color: #1877f2; font-weight: bold; cursor: pointer; padding: 0 10px;">Gửi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="comment-replies" id="replies-{{ $comment->id }}">
        @if ($comment->replies && $comment->replies->count() > 0)
            @foreach ($comment->replies as $reply)
                @include('posts.partials.comment', ['comment' => $reply, 'post' => $post])
            @endforeach
        @endif
    </div>
</div>
