<nav class="main-navigation">
    <div class="nav-container">
        <div class="nav-left">
            <a href="{{ route('home') }}" class="brand-logo">VibeConnect</a>
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Tìm kiếm trên VibeConnect">
            </div>
        </div>

        <div class="nav-center">
            <a href="{{ route('home') }}" class="nav-link active"><i class="fa-solid fa-house"></i></a>
            <a href="#" class="nav-link"><i class="fa-solid fa-user-group"></i></a>
            <a href="#" class="nav-link"><i class="fa-solid fa-video"></i></a>
            <a href="#" class="nav-link"><i class="fa-solid fa-store"></i></a>
        </div>

        <div class="nav-right">
            @auth
                <div class="user-profile-menu">
                    <img src="{{ Auth::user()->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar-small">
                    <span>{{ Auth::user()->name }}</span>
                </div>
            @endauth

            <div class="action-icons">
                <div class="icon-wrapper"><i class="fa-solid fa-bars"></i></div>
                <div class="icon-wrapper"><i class="fa-brands fa-facebook-messenger"></i></div>
                <div class="icon-wrapper"><i class="fa-solid fa-bell"></i></div>
            </div>
        </div>
    </div>
</nav>
