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
                    <form action="{{ route('posts.share', $post->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-link">🔗 Chia sẻ</button>
                    </form>

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
        @endforeach

    </div>
</body>

</html>
