<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// GUEST ROUTES
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/', [AuthController::class, 'register']);
});

// AUTH ROUTES
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/home', [PostController::class, 'home'])->name('home');

    // POSTS
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::post('/', [PostController::class, 'store'])->name('store');
        Route::get('/{post}', [PostController::class, 'show'])->name('show');
        Route::get('/{post}/edit', [PostController::class, 'edit'])->name('edit');
        Route::put('/{post}', [PostController::class, 'update'])->name('update');
        Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');
        Route::post('/{post}/react', [PostController::class, 'toggleReaction'])->name('react');
        Route::post('/{post}/share', [PostController::class, 'share'])->name('share');
    });

    // COMMENTS
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // PROFILE
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'me'])->name('me');
        Route::get('/{user}', [ProfileController::class, 'show'])->name('show');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });

    // SEARCH
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    // FRIENDSHIPS
    Route::prefix('friendships')->name('friendships.')->group(function () {
        Route::post('/add/{user}', [FriendshipController::class, 'add'])->name('add');
        Route::post('/accept/{user}', [FriendshipController::class, 'accept'])->name('accept');
        Route::post('/decline/{user}', [FriendshipController::class, 'decline'])->name('decline');
        Route::delete('/remove/{user}', [FriendshipController::class, 'remove'])->name('remove');
    });

    // MESSAGES
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::post('/start/{user}', [MessageController::class, 'createConversation'])->name('start');
        Route::get('/{conversationId?}', [MessageController::class, 'index'])->name('index')->where('conversationId', '[0-9]+');
        Route::post('/{conversation}', [MessageController::class, 'store'])->name('store');
        Route::delete('/{message}/unsend', [MessageController::class, 'destroyMessage'])->name('unsend');
        Route::delete('/conversation/{conversation}/remove', [MessageController::class, 'destroyConversation'])->name('remove_conversation');
    });
});
