<nav class="main-navigation">
    <div class="nav-container" style="max-width: none; width: 100%; padding: 0 15px; box-sizing: border-box;">
        <div class="nav-left">
            <a href="{{ route('home') }}" class="brand-logo">VibeConnect</a>

            <div class="search-bar">
                <form action="{{ route('search.index') }}" method="GET"
                    style="margin: 0; display: flex; width: 100%; align-items: center;">

                    <button type="submit"
                        style="background: none; border: none; padding: 0; margin-right: 8px; cursor: pointer; display: flex; align-items: center;">
                        <i class="fa-solid fa-search" style="color: #65676b; font-size: 16px;"></i>
                    </button>

                    <input type="text" name="query" placeholder="Tìm kiếm trên VibeConnect"
                        value="{{ request('query') }}"
                        style="border: none; background: transparent; outline: none; width: 100%;">
                </form>
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
                <a href="{{ route('messages.index') }}" class="icon-wrapper" style="text-decoration: none; color: inherit;">
                    <i class="fa-brands fa-facebook-messenger"></i>
                </a>
                <div class="icon-wrapper"><i class="fa-solid fa-bell"></i></div>
            </div>
        </div>
    </div>
</nav>
