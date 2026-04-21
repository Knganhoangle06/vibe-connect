<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm border-0 rounded-4 p-4" style="width: 100%; max-width: 420px;">
        <h2 class="fw-bold fs-4 mb-1">Tạo tài khoản</h2>
        <p class="text-muted small mb-4">Tham gia Vibe Connect ngay hôm nay</p>

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-medium small">Họ và tên</label>
                <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                    placeholder="Nguyễn Văn A" value="{{ old('name') }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium small">Email</label>
                <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                    placeholder="example@email.com" value="{{ old('email') }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium small">Giới tính</label>
                <select name="gender" class="form-control rounded-3 @error('gender') is-invalid @enderror">
                    <option value="">-- Chọn giới tính --</option>
                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Nam</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Nữ</option>
                    <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Khác</option>
                </select>
                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium small">Mật khẩu</label>
                <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror"
                    placeholder="••••••••">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-medium small">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" class="form-control rounded-3"
                    placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold py-2">Đăng ký</button>
        </form>

        <p class="text-center mt-3 small text-muted">
            Đã có tài khoản? <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-medium">Đăng nhập</a>
        </p>
    </div>
</body>
</html>