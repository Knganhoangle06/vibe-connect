<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Người sẽ nhận được thông báo
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Phân loại thông báo (friend_request, new_comment, v.v.)
            $table->string('type');

            // Dữ liệu chi tiết lưu dưới dạn chuỗi JSON
            $table->json('data');

            $table->timestamp('read_at')->nullable(); //Trạng thái Đã đọc
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
