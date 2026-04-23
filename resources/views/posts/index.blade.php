<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Feed - Vibe Connect</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <div class="container">

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <textarea class="form-control" name="content" rows="3" placeholder="Bạn đang nghĩ gì?"></textarea>

            <label style="font-size: 13px; color: #65676b; cursor: pointer;">
                📷 Thêm ảnh/video: <input type="file" name="media[]" accept="image/*,video/*" multiple>
            </label>

            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Đăng bài</button>
        </form>

        @foreach ($posts as $post)
            <div class="post-card">
                <div class="post-header">
                    <strong>{{ $post->user->name }}</strong>
                    <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                </div>

                <p style="margin: 0 0 10px 0;">{{ $post->content }}</p>

                {{-- Hiển thị media mới (mảng ảnh/video) --}}
                @if ($post->media && $post->media->isNotEmpty())
                    @foreach ($post->media as $mediaItem)
                        @if ($mediaItem->file_type === 'image')
                            <img src="{{ Str::startsWith($mediaItem->file_path, 'http') ? $mediaItem->file_path : asset('storage/' . $mediaItem->file_path) }}"
                                alt="Media" class="post-media">
                        @elseif($mediaItem->file_type === 'video')
                            <video controls class="post-media"
                                src="{{ Str::startsWith($mediaItem->file_path, 'http') ? $mediaItem->file_path : asset('storage/' . $mediaItem->file_path) }}"></video>
                        @endif
                    @endforeach
                @elseif ($post->media_url)
                    {{-- Fallback cho dữ liệu Seeder cũ --}}
                    <img src="{{ Str::startsWith($post->media_url, 'http') ? $post->media_url : asset('storage/' . $post->media_url) }}"
                        alt="Media" class="post-media" onerror="this.style.display='none'">
                @endif

                @if ($post->original_post_id && $post->originalPost)
                    <div class="shared-post">
                        <strong>{{ $post->originalPost->user->name }}</strong>
                        <p style="margin: 5px 0 0 0;">{{ $post->originalPost->content }}</p>
                    </div>
                @endif

                <hr class="post-divider">

                <div class="post-actions">
                    <button type="button" onclick="openShareModal({{ $post->id }})" class="btn-link">🔗 Chia
                        sẻ</button>

                    @if (Auth::id() === $post->user_id)
                        <a href="{{ route('posts.edit', $post->id) }}" class="btn-link edit">✏️ Sửa</a>

                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST"
                            onsubmit="return confirm('Xóa bài viết này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-link delete">🗑️ Xóa</button>
                        </form>
                    @endif
                </div>
            </div>

            <div id="shareModal-{{ $post->id }}" class="fb-modal"
                style="display: none; align-items: center; justify-content: center; z-index: 1060; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8);">
                <div class="modal-content"
                    style="max-width: 500px; width: 100%; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2); background: #fff; margin: auto;">
                    <div class="modal-header"
                        style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #ddd;">
                        <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Chia sẻ bài viết</h3>
                        <span class="close-modal" onclick="closeShareModal({{ $post->id }})"
                            style="cursor: pointer; font-size: 24px; color: #666; line-height: 1;">&times;</span>
                    </div>
                    <form action="{{ route('posts.share', $post->id) }}" method="POST">
                        @csrf
                        <div class="modal-body" style="padding: 15px;">
                            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                <img src="{{ Auth::user()->avatar ? (filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : asset('images/default-avatar.png') }}"
                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 15px;">{{ Auth::user()->name }}</div>
                                    <select name="privacy"
                                        style="margin-top: 5px; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd; font-size: 12px; background: #e4e6eb; font-weight: 600; cursor: pointer;">
                                        <option value="public">Công khai</option>
                                        <option value="friends">Bạn bè</option>
                                        <option value="private">Chỉ mình tôi</option>
                                    </select>
                                </div>
                            </div>
                            <textarea name="content" placeholder="Hãy nói gì đó về nội dung này..."
                                style="width: 100%; border: none; outline: none; resize: none; min-height: 80px; font-size: 16px; font-family: inherit; box-sizing: border-box;"></textarea>

                            <div
                                style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-top: 10px; background: #f9f9f9;">
                                <div style="font-weight: 600; font-size: 14px;">
                                    {{ $post->originalPost ? $post->originalPost->user->name : $post->user->name }}
                                </div>
                                <div
                                    style="font-size: 13px; color: #65676b; margin-top: 5px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $post->originalPost ? $post->originalPost->content : $post->content }}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="padding: 15px; border-top: 1px solid #ddd; text-align: right;">
                            <button type="button" onclick="closeShareModal({{ $post->id }})"
                                style="background: #e4e6eb; color: #050505; border: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 10px;">Hủy</button>
                            <button type="submit"
                                style="background: #1877f2; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">Chia
                                sẻ ngay</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

    </div>

    <script>
        function openShareModal(postId) {
            const modal = document.getElementById('shareModal-' + postId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeShareModal(postId) {
            const modal = document.getElementById('shareModal-' + postId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('fb-modal') && event.target.id.startsWith('shareModal-')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    </script>
</body>

</html>
