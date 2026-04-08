<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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

    return redirect()->route('posts.index');
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
