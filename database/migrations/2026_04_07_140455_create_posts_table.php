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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // Chủ sở hữu bài viết
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Tự tham chiếu về ID của chính bảng posts - dành cho Logic Share
            $table->foreignId('original_post_id')->nullable()->constrained('posts')->onDelete('set null');

            // Nội dung văn bản và Multimedia
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->enum('media_type', ['image', 'video'])->nullable();

            $table->timestamps();

            // Tối ưu hóa truy vấn News Feed
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
