<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm border-0 rounded-4 p-4" style="width: 100%; max-width: 420px;">
        <h2 class="fw-bold fs-4 mb-1">Chào mừng trở lại</h2>
        <p class="text-muted small mb-4">Đăng nhập để tiếp tục</p>

        @if(session('error'))
            <div class="alert alert-danger rounded-3 py-2 px-3 mb-3 small">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-medium small">Email</label>
                <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                    placeholder="example@email.com" value="{{ old('email') }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-medium small mb-0">Mật khẩu</label>
                    <a href="#" class="small text-primary text-decoration-none fw-medium"></a>
                </div>
                <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror"
                    placeholder="••••••••">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold py-2">Đăng nhập</button>
        </form>

        <p class="text-center mt-3 small text-muted">
            Chưa có tài khoản? <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-medium">Đăng ký</a>
        </p>
    </div>
</body>
</html>