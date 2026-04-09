<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập</title>
</head>
<body>
    <h2>Đăng nhập</h2>

    @if($errors->any())
        @foreach($errors->all() as $error)
            <p style="color:red">{{ $error }}</p>
        @endforeach
    @endif

    <form method="POST" action="/login">
        @csrf
        <input type="email" name="email" placeholder="Email"><br>
        <input type="password" name="password" placeholder="Mật khẩu"><br>
        <button type="submit">Đăng nhập</button>
    </form>

    <a href="/register">Chưa có tài khoản? Đăng ký</a>
</body>
</html>
