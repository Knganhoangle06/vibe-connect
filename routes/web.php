<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('home');
});

// Route hiển thị trang chủ với danh sách bài đăng
Route::get('/home', [PostController::class, 'homepage'])->name('home');

// Auto login (sử dụng cho mục đích vượt rào trong lúc chưa có login thật)
Route::get('dev-login', function () {
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'dev@vibe-connect.com'],
        [
            'name' => 'Dev Tester',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]
    );

    \Illuminate\Support\Facades\Auth::login($user);

    return redirect()->route('/home');
});

// Nhóm Route yêu cầu đăng nhập
Route::middleware('auth')->group(function () {
    // CRUD cho Post
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Tính năng Share bài viết
    Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');
});

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
use App\Http\Controllers\UserController;

Route::get('/profile', [UserController::class, 'show']);
Route::post('/profile', [UserController::class, 'update']);
