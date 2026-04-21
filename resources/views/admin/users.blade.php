@extends('user.master')

@section('content')
<div class="main-layout">
    <aside class="sidebar-left">
        <div class="card-report-header">
            <h4>Quản trị</h4>
            <div class="header-dashboard" style="display: flex; flex-direction: row; gap: 10px; margin-top: 15px;">
            <a href="{{ route('admin.dashboard') }}" class="menu-item"><span>Dashboard</span></a>
            <a href="{{ route('admin.users.index') }}" class="menu-item active"><span>Quản lý người dùng</span></a>
            <a href="{{ route('admin.posts.index') }}" class="menu-item"><span>Quản lý bài viết</span></a>
            </div>
        </div>
    </aside>

    <main class="content-center">
        @if (session('success'))
            <div class="card" style="color:green;">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="card" style="color:#b3261e;">{{ session('error') }}</div>
        @endif

        <div class="card-dashboard">
            <h2>Quản lý người dùng</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Giới tính</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>{{ $user->gender ?? 'N/A' }}</td>
                        <td>{{ $user->is_locked ? 'Đang khóa' : 'Hoạt động' }}</td>
                        <td>
                            @if($user->role !== 'admin')
                                <form action="{{ route('admin.users.lock', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-post {{ $user->is_locked ? '' : 'btn-danger' }}">
                                        {{ $user->is_locked ? 'Mở khóa' : 'Khóa tài khoản' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="feed-pagination" style="margin-top:12px;">{{ $users->links() }}</div>
        </div>
    </main>
</div>
@endsection
