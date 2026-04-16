<h1>Profile</h1>

<form method="POST" action="/profile">
    @csrf

    <input name="name" value="{{ $user->name }}">
    <br>

    <input name="email" value="{{ $user->email }}">
    <br>

    <input name="password" placeholder="password mới">
    <br>

    <button>Cập nhật</button>
</form>
