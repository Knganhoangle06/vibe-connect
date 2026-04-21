@extends('layouts.app')

@section('content')
    <div class="main-layout-container"
        style="display: grid; grid-template-columns: 1fr; max-width: 680px; margin: 20px auto; padding: 0 20px;">
        <main>
            <div class="post-card"
                style="background: white; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.2); padding: 15px; margin-bottom: 20px;">

                <div class="post-header"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div class="post-user-info" style="display: flex; gap: 10px; align-items: center;">
                        <img src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar"
                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div class="post-meta">
                            <h4 class="user-name" style="margin: 0; font-size: 1rem; color: #050505;">
                                <a href="{{ route('profile.show', $post->user->id) }}"
                                    style="text-decoration: none; color: inherit;">
                                    {{ $post->user->name }}
                                </a>
                            </h4>
                            <small class="post-time" style="color: #65676b; font-size: 0.8rem;">
                                {{ $post->created_at->diffForHumans() }} · <i class="fa-solid fa-earth-americas"></i>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="post-body" style="margin-bottom: 15px;">
                    <p style="font-size: 1rem; line-height: 1.5; margin: 0 0 10px 0; color: #050505;">{{ $post->content }}
                    </p>

                    @if ($post->originalPost)
                        <div class="shared-post-container">
                            <div class="shared-post-header">
                                <img src="{{ $post->originalPost->user->avatar ?? 'https://i.pravatar.cc/150' }}"
                                    class="user-avatar-small" style="width: 32px; height: 32px;">
                                <div>
                                    <div class="shared-post-user-name">{{ $post->originalPost->user->name }}</div>
                                    <div class="shared-post-time">{{ $post->originalPost->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            <div class="shared-post-content">
                                {{ $post->originalPost->content }}
                            </div>
                            @if ($post->originalPost->media_url)
                                <div class="post-media-container" style="margin-top: 10px;">
                                    @if ($post->originalPost->media_type === 'image')
                                        <img src="{{ asset($post->originalPost->media_url) }}"
                                            style="width: 100%; border-radius: 8px;">
                                    @elseif($post->originalPost->media_type === 'video')
                                        <video controls style="width: 100%; border-radius: 8px;">
                                            <source src="{{ asset($post->originalPost->media_url) }}" type="video/mp4">
                                        </video>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    @if (!$post->originalPost && $post->media_url)
                        <div class="post-media-container" style="margin-top: 10px;">
                            @if ($post->media_type === 'image')
                                <img src="{{ asset($post->media_url) }}" style="width: 100%; border-radius: 8px;">
                            @elseif($post->media_type === 'video')
                                <video controls style="width: 100%; border-radius: 8px;">
                                    <source src="{{ asset($post->media_url) }}" type="video/mp4">
                                </video>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="post-footer"
                    style="display: flex; justify-content: space-around; border-top: 1px solid #ced0d4; padding-top: 10px; margin-top: 15px;">
                    <div class="reaction-container footer-btn" style="flex: 1;">
                        <div class="reaction-popover">
                            <!-- Cầu nối tàng hình nối liền popover và nút Thích -->
                            <div style="position: absolute; bottom: -15px; left: 0; width: 100%; height: 15px; background: transparent;"></div>
                            @foreach (['like' => '👍', 'love' => '❤️', 'haha' => '😆', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'] as $type => $emoji)
                                <form action="{{ route('posts.react', $post->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <button type="submit" class="reaction-icon-btn"
                                        title="{{ ucfirst($type) }}">{{ $emoji }}</button>
                                </form>
                            @endforeach
                        </div>
                        @php $myReaction = $post->reactions->where('user_id', auth()->id())->first(); @endphp
                        <div
                            style="cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; color: {{ $myReaction ? '#1877f2' : '#65676b' }}; font-weight: 600;">
                            <i class="fa-regular fa-thumbs-up"></i>
                            {{ $myReaction ? ucfirst($myReaction->type) : 'Thích' }}
                            @if ($post->reactions->count() > 0)
                                ({{ $post->reactions->count() }})
                            @endif
                        </div>
                    </div>

                    <div class="footer-btn"
                        style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; color: #65676b; font-weight: 600;">
                        <i class="fa-regular fa-comment"></i> Bình luận ({{ $post->comments->count() }})
                    </div>

                    <form action="{{ route('posts.share', $post->id) }}" method="POST" style="flex: 1; display: flex;">
                        @csrf
                        <button type="submit" class="footer-btn"
                            style="width: 100%; background: none; border: none; padding: 8px 0; color: #65676b; font-weight: 600; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;"><i
                                class="fa-solid fa-share"></i> Chia sẻ</button>
                    </form>
                </div>

                <div class="post-full-comments" style="margin-top: 20px; border-top: 1px solid #ced0d4; padding-top: 15px;">
                    <form id="comment-form" action="{{ route('comments.store', $post->id) }}" method="POST"
                        style="display: flex; gap: 10px; margin-bottom: 25px; align-items: flex-start;">
                        @csrf
                        <img src="{{ auth()->user()->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar-small"
                            style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                        <div style="flex: 1; display: flex; gap: 8px;">
                            <input type="text" id="comment-input" name="content" placeholder="Viết bình luận..." required
                                style="width: 100%; border-radius: 20px; border: 1px solid #ced0d4; padding: 10px 15px; outline: none; background: #f0f2f5;">
                            <button type="submit" class="btn-primary"
                                style="padding: 8px 16px; border-radius: 20px;">Gửi</button>
                        </div>
                    </form>

                    <div class="comments-list" id="comments-list">
                        @foreach ($post->comments as $comment)
                            @include('posts.partials.comment', ['comment' => $comment, 'post' => $post])
                        @endforeach
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Sử dụng Event Delegation để bắt sự kiện submit của TẤT CẢ các form bình luận (kể cả form trả lời)
            document.body.addEventListener("submit", function(e) {
                const form = e.target;

                // Kiểm tra xem form đang submit có phải là form gửi bình luận mới không
                // Sử dụng regex để nhận diện đường dẫn có dạng: /posts/{id}/comments
                if (form.tagName === "FORM" && form.action.match(/\/posts\/\d+\/comments/)) {
                    e.preventDefault(); // Ngăn load lại trang

                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries()); // Lấy toàn bộ data kể cả parent_id

                    window.axios.post(form.action, data)
                        .then(response => {
                            if (response.data.success) {
                                if (data.parent_id) {
                                    // BÌNH LUẬN CON (Child comment): Chèn vào khối danh sách trả lời của bình luận cha
                                    const repliesContainer = document.getElementById("replies-" + data
                                        .parent_id);
                                    if (repliesContainer) {
                                        repliesContainer.insertAdjacentHTML("beforeend", response.data
                                            .html);
                                    }

                                    form.style.display =
                                    "none"; // Ẩn form trả lời sau khi gửi thành công
                                } else {
                                    // BÌNH LUẬN GỐC (Parent comment): Chèn vào cuối danh sách tổng
                                    document.getElementById("comments-list").insertAdjacentHTML(
                                        "beforeend", response.data.html);
                                }
                                form.reset(); // Xóa nội dung ô nhập liệu
                            }
                        })
                        .catch(error => console.error("Lỗi gửi bình luận:", error));
                }
            });
        });
    </script>
@endsection
