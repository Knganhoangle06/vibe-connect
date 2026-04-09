<!DOCTYPE html>
<html>
<head>
    <title>Đăng ký</title>
</head>
<body>
    <h2>Đăng ký</h2>

    @if($errors->any())
        @foreach($errors->all() as $error)
            <p style="color:red">{{ $error }}</p>
        @endforeach
    @endif

    <form method="POST" action="/register">
        @csrf
        <input type="text" name="name" placeholder="Họ tên"><br>
        <input type="email" name="email" placeholder="Email"><br>
        <input type="password" name="password" placeholder="Mật khẩu"><br>
        <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu"><br>
        <button type="submit">Đăng ký</button>
    </form>

    <a href="/login">Đã có tài khoản? Đăng nhập</a>
</body>
</html>
