<nav>
    <a href="{{ route('home') }}" class="logo">MiniSocial</a>
    <div class="search-box">
        <form action="{{ route('search.index') }}" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm người dùng hoặc bài đăng...">
        </form>
    </div>
    <div class="nav-icon">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}" title="Bảng tin">
            <i class="fa-solid fa-house icon"></i><span>Bảng tin</span>
        </a>
        <a href="{{ route('search.index') }}" class="{{ request()->routeIs('search.index') ? 'active' : '' }}" title="Khám phá">
            <i class="fa-solid fa-compass icon"></i><span>Khám phá</span>
        </a>
        <a href="{{ route('friend.show') }}" class="{{ request()->routeIs('friend.show') ? 'active' : '' }}" title="Bạn bè">
            <i class="fa-solid fa-user-group icon"></i><span>Bạn bè</span>
        </a>
        <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}" title="Nhắn tin">
            <i class="fa-solid fa-message icon"></i><span>Tin nhắn</span>
        </a>
        @if(Auth::user()?->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}" title="Quản trị">
                <i class="fa-solid fa-chart-line icon"></i><span>Admin</span>
            </a>
        @endif
    </div>

   <div class="profile-menu-wrapper">
    <button type="button" class="avatar-toggle" onclick="toggleProfileMenu(event)">
        <img src="{{ Auth::user()->avatar ? (filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : asset('images/default-avatar.png') }}"
             class="user-pic">
        <i class="fa-solid fa-chevron-down arrow-icon"></i>
    </button>

    <div class="profile-dropdown" id="profileDropdown">
        <div class="user-info-card">
            <div class="user-info-header">
               <img src="{{ Auth::user()->avatar ? (filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : asset('images/default-avatar.png') }}"
                     class="user-pic-large">
                <span class="user-name">{{ Auth::user()->name }}</span>
            </div>
            <hr>
            <a href="{{ route('profile.me') }}" class="view-all-profile">Xem tất cả trang cá nhân</a>
        </div>

        <ul class="menu-list">
            <li>
                <a href="#"><i class="fa-solid fa-gear"></i> Cài đặt và quyền riêng tư <i class="fa-solid fa-chevron-right arrow-right"></i></a>
            </li>
            <li>
                <a href="#"><i class="fa-solid fa-circle-question"></i> Trợ giúp và hỗ trợ <i class="fa-solid fa-chevron-right arrow-right"></i></a>
            </li>
            <li>
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                    </button>
                </form>
            </li>
        </ul>

        <div class="footer-links">
            Quyền riêng tư · Điều khoản · Quảng cáo
        </div>
    </div>
</div>
</nav>
