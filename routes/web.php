<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FriendshipController;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------------------------------
// GUEST ROUTES (Chưa đăng nhập)
// --------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// --------------------------------------------------------------------------
// AUTH ROUTES (Đã đăng nhập)
// --------------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // News Feed chính
    Route::get('/home', [PostController::class, 'index'])->name('home');

    // Quản lý Bài viết
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::post('/', [PostController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [PostController::class, 'edit'])->name('edit');
        Route::put('/{post}', [PostController::class, 'update'])->name('update');
        Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');

        // Tính năng Share
        Route::post('/{post}/share', [PostController::class, 'share'])->name('share');
    });

    // ----------------------------------------------------------------------
    // TÍNH NĂNG KẾT BẠN (FRIENDSHIPS)
    // ----------------------------------------------------------------------
    Route::prefix('friendships')->name('friendships.')->group(function () {
        Route::post('/add/{user}', [FriendshipController::class, 'add'])->name('add');
        Route::post('/accept/{user}', [FriendshipController::class, 'accept'])->name('accept');
        Route::post('/decline/{user}', [FriendshipController::class, 'decline'])->name('decline');
        Route::delete('/remove/{user}', [FriendshipController::class, 'remove'])->name('remove');
    });

    // Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
