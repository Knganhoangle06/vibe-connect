<?php

use App\Http\Controllers\PostController; // Đừng quên import Controller nhé
use Illuminate\Support\Facades\Route;

// Route hiển thị trang chủ với danh sách bài đăng
Route::get('/home', [PostController::class, 'index']);




