@extends('user.master')

@section('content')
<div class="main-layout">
    <div class="sidebar-left" style="top: 20px;">
        <div class="card-report-header">
            <h4>Quản trị</h4>
            <div class="header-dashboard" style="display: flex; flex-direction: row; gap: 10px; margin-top: 15px;">
            <a href="{{ route('admin.dashboard') }}" class="menu-item active"><span>Dashboard</span></a>
            <a href="{{ route('admin.users.index') }}" class="menu-item"><span>Quản lý người dùng</span></a>
            <a href="{{ route('admin.posts.index') }}" class="menu-item"><span>Quản lý bài viết</span></a>
            </div>
        </div>
</div>

    <main class="content-center">
        <div class="card-dashboard">
            <h2>Dashboard Admin</h2>
            <p style="color:#65676b;">Theo dõi nhanh hoạt động hệ thống.</p>
        </div>

        <div class="admin-stats-grid">
            <div class="card-report"><h4>Tổng user</h4><p class="admin-big-number">{{ $totalUsers }}</p></div>
            <div class="card-report"><h4>Tổng bài viết</h4><p class="admin-big-number">{{ $totalPosts }}</p></div>
            <div class="card-report"><h4>Tổng comment</h4><p class="admin-big-number">{{ $totalComments }}</p></div>
        </div>

        <div class="card-report-gender">
            <h3>Tỉ lệ giới tính user đã đăng ký</h3>
            <canvas
                id="genderChart"
                height="100"
                data-male="{{ $genderStats['male'] }}"
                data-female="{{ $genderStats['female'] }}"
                data-other="{{ $genderStats['other'] }}"
            ></canvas>
        </div>
    </main>
</div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const genderCtx = document.getElementById('genderChart');

        if (genderCtx) {
            const genderData = [
                Number(genderCtx.dataset.male || 0),
                Number(genderCtx.dataset.female || 0),
                Number(genderCtx.dataset.other || 0),
            ];

            new Chart(genderCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Nam', 'Nữ', 'Khác'],
                    datasets: [{
                        data: genderData,
                        backgroundColor: ['#4e79ff', '#ff6fa5', '#9ca3af'],
                        borderWidth: 0
                    }]
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    </script>
@endsection
