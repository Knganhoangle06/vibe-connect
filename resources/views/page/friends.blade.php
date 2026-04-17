@extends('user.master')

@section('content')
<div class="container py-4">
    <div class="friend-request-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Lời mời kết bạn</h4>
            <a href="#" class="view-all-link">Xem tất cả</a>
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
            @forelse($requests as $request)
                <div class="col">
                    <div class="friend-card shadow-sm h-100">
                        <div class="img-container">
                            <img src="{{ $request->sender->avatar ?? 'https://via.placeholder.com/200' }}" alt="{{ $request->sender->name }}">
                        </div>
                        <div class="friend-info">
                            <h6 class="friend-name" title="{{ $request->sender->name }}">{{ $request->sender->name }}</h6>
                            {{-- Bạn có thể thêm số bạn chung ở đây nếu có logic --}}
                            <p class="mutual-friends">3 bạn chung</p> 
                            
                            <div class="action-buttons">
                                <form action="{{ route('friends.accept', $request->sender->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-confirm">Xác nhận</button>
                                </form>
                                <form action="{{ route('friends.remove', $request->sender->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">Xóa</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="fa-solid fa-user-group mb-3 text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted">Không có lời mời kết bạn nào mới.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection