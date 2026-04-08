<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa bài viết</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="container post-form-box" style="margin-top: 50px;">
        <h2 style="margin-top: 0;">Chỉnh sửa bài viết</h2>

        <form action="{{ route('posts.update', $post->id) }}" method="POST">
            @csrf
            @method('PUT')

            <textarea class="form-control" name="content" rows="5">{{ $post->content }}</textarea>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-success">Cập nhật</button>
                <a href="{{ route('posts.index') }}" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</body>

</html>
