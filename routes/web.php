<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/', [AuthController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/home', [PostController::class, 'homepage'])->name('home');

    // BÀI VIẾT (POSTS)
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show'); // ROUTE MỚI THÊM
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::patch('/posts/{post}/privacy', [PostController::class, 'updatePrivacy'])->name('posts.privacy.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::post('/posts/{post}/reaction', [PostController::class, 'toggleReaction'])->name('posts.reaction.toggle');
    Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');

    // BÌNH LUẬN (COMMENTS)
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // TRANG CÁ NHÂN (PROFILE)
    Route::get('/profile', [ProfileController::class, 'myProfile'])->name('profile.me');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // TÌM KIẾM
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    // TIN NHẮN (MESSAGES)
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/create/{user}', [\App\Http\Controllers\MessageController::class, 'createConversation'])->name('create');
        Route::get('/{conversationId?}', [\App\Http\Controllers\MessageController::class, 'index'])->name('index')->where('conversationId', '[0-9]+');
        Route::post('/{conversation}', [\App\Http\Controllers\MessageController::class, 'store'])->name('store');
        Route::delete('/{message}/unsend', [\App\Http\Controllers\MessageController::class, 'destroyMessage'])->name('unsend');
        Route::delete('/conversation/{conversation}/remove', [\App\Http\Controllers\MessageController::class, 'destroyConversation'])->name('remove_conversation');
    });

    // BẠN BÈ (FRIENDSHIPS)
    Route::get('/friend-requests', [FriendshipController::class, 'friendRequests'])->name('friend.show');
    Route::post('/friends/{user}/request', [FriendshipController::class, 'send'])->name('friends.request');
    Route::post('/friends/{user}/accept', [FriendshipController::class, 'accept'])->name('friends.accept');
    Route::delete('/friends/{user}', [FriendshipController::class, 'remove'])->name('friends.remove');
});

// QUẢN TRỊ (ADMIN)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::patch('/users/{user}/lock', [AdminController::class, 'toggleLock'])->name('users.lock');
    Route::get('/posts', [AdminController::class, 'posts'])->name('posts.index');
    Route::delete('/posts/{post}', [AdminController::class, 'destroyPost'])->name('posts.destroy');
});
