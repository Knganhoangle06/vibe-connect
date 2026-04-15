<aside class="sidebar-left">
    <div class="menu-item active">
        <img src="{{ Auth::user()->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar-small">
        <span>{{ Auth::user()->name }}</span>
    </div>

    <div class="menu-list">
        <div class="menu-item"><i class="fa-solid fa-user-group"></i> <span>Friends</span></div>
        <div class="menu-item"><i class="fa-solid fa-clock"></i> <span>Memories</span></div>
        <div class="menu-item"><i class="fa-solid fa-bookmark"></i> <span>Saved</span></div>
        <div class="menu-item"><i class="fa-solid fa-flag"></i> <span>Pages</span></div>
    </div>

    @auth
        <form action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </button>
        </form>
    @endauth
</aside>
