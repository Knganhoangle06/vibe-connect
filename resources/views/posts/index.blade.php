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

        <div class="post-form-box">
            <form action="{{ route('posts.store') }}" method="POST">
                @csrf
                <textarea class="form-control" name="content" rows="3" placeholder="Bạn đang nghĩ gì?"></textarea>
                <input type="text" class="form-control" name="media_url" placeholder="Nhập URL ảnh (nếu có)">
                <button type="submit" class="btn btn-primary">Đăng bài</button>
            </form>
        </div>

        @foreach ($posts as $post)
            <div class="post-card">
                <div class="post-header">
                    <strong>{{ $post->user->name }}</strong>
                    <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                </div>

                <p style="margin: 0 0 10px 0;">{{ $post->content }}</p>

                @if ($post->media_url)
                    <img src="{{ $post->media_url }}" alt="Media" class="post-media">
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
