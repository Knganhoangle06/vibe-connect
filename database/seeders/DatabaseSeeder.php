<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DỮ LIỆU USER (Thêm vài gương mặt mới cho xôm)
        DB::table('users')->insert([
            [
                'id' => 1, 
                'name' => 'Nguyễn Thu Trang', 
                'email' => 'trang@pnv.edu.vn', 
                'password' => Hash::make('123'), 
                'role' => 'user', 'bio' => 'Gen Z học Code 💻', 
                'avatar' => 'https://i.pinimg.com/1200x/0c/3f/86/0c3f86b69a16a7864344c4141400f8da.jpg', 
                'created_at' => now()],
            [
                'id' => 2, 
                'name' => 'Ngọc Anh', 
                'email' => 'anh@pnv.edu.vn', 
                'password' => Hash::make('123'), 
                'role' => 'user', 
                'bio' => 'Chuyên gia Debug dạo', 
                'avatar' => 'https://i.pinimg.com/1200x/01/07/65/01076575bce9b4f2a97847f3c45e3f99.jpg', 
                'created_at' => now()
            ],
            [
                'id' => 3, 
                'name' => 'Lê Văn Nam', 
                'email' => 'namle@example.com', 
                'password' => Hash::make('123'), 
                'role' => 'user', 
                'bio' => 'Thích Laravel và UI/UX', 
                'avatar' => 'https://i.pinimg.com/736x/5e/77/eb/5e77ebf8952632197edaa34fa46502eb.jpg', 
                'created_at' => now()
            ],
            [
                'id' => 4, 
                'name' => 'OpenDev Recruitment', 
                'email' => 'hr@opendev.com', 
                'password' => Hash::make('123'), 
                'role' => 'admin', 
                'bio' => 'Kênh tuyển dụng IT chính thức', 
                'avatar' => 'https://i.pinimg.com/736x/7d/03/e2/7d03e2d3354ea97f1532e6d7a46b98a5.jpg', 
                'created_at' => now()
            ],
            [
                'id' => 5, 
                'name' => 'Nguyễn Thùy Trang', 
                'email' => 'thuytrangpnv27@gmail.com', 
                'password' => Hash::make('trang12345'), 
                'role' => 'admin', 
                'bio' => 'Kênh tuyển dụng IT chính thức', 
                'avatar' => 'https://i.pinimg.com/736x/7d/03/e2/7d03e2d3354ea97f1532e6d7a46b98a5.jpg', 
                'created_at' => now()
            ],
        ])
        ;

        // 2. DỮ LIỆU BÀI ĐĂNG (POSTS) - NHIỀU VÀ THẬT
        $posts = [
            // Bài đăng của Thu Trang
            [
                'id' => 1, 'user_id' => 1, 'original_post_id' => null,
                'content' => 'Vừa học xong buổi PHP MVC tại PNV, mệt nhưng mà cuốn thực sự! Ai có tài liệu hay về Laravel không ạ?',
                'media_url' => 'https://images.unsplash.com/photo-1599507593499-a3f7d7d97667',
                'media_type' => 'image', 'created_at' => Carbon::now()->subDays(2)
            ],
            [
                'id' => 2, 'user_id' => 1, 'original_post_id' => null,
                'content' => 'Review nhẹ chiếc Portfolio mình mới làm bằng HTML/CSS. Mọi người cho mình xin góp ý với nha! ✨',
                'media_url' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8',
                'media_type' => 'image', 'created_at' => Carbon::now()->subDays(1)
            ],

            // Bài đăng của Ngọc Anh
            [
                'id' => 3, 'user_id' => 2, 'original_post_id' => null,
                'content' => 'Cứ ngỡ là Bug, hóa ra là tính năng... Có ai từng thức đến 2h sáng chỉ để tìm một dấu chấm phẩy chưa? 😂',
                'media_url' => null, 'media_type' => null, 'created_at' => Carbon::now()->subHours(10)
            ],
            [
                'id' => 4, 'user_id' => 2, 'original_post_id' => null,
                'content' => 'Sáng nay vừa bảo vệ xong đồ án Library Management. Cảm ơn team mình rất nhiều!',
                'media_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c',
                'media_type' => 'image', 'created_at' => Carbon::now()->subHours(5)
            ],
              
            // Bài đăng tuyển dụng từ Admin/OpenDev
            [
                'id' => 5, 'user_id' => 4, 'original_post_id' => null,
                'content' => '[Hiring] OpenDev đang tìm kiếm 02 bạn thực tập sinh PHP và 01 bạn UX/UI. Môi trường cực năng động tại Đà Nẵng nhé!',
                'media_url' => 'https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d',
                'media_type' => 'image', 'created_at' => Carbon::now()->subHours(2)
            ],
            //
             [
                'id' => 6, 'user_id' => 2, 'original_post_id' => null,
                'content' => 'Cứ ngỡ là Bug, hóa ra là tính năng... 😂',
                'media_url' => null, 'media_type' => null, 'created_at' => Carbon::now()->subHours(10)
            ],
            // Bài đăng của Lê Văn Nam
            [
                'id' => 7, 'user_id' => 3, 'original_post_id' => null,
                'content' => 'Mới phát hiện ra bộ icon cực chất cho mấy bạn làm UX/UI nè. Link ở dưới comment nhé!',
                'media_url' => 'https://i.pinimg.com/736x/f8/f8/ce/f8f8cec5a945d15d9d2bbc5e3fa15100.jpg',
                'media_type' => 'image', 'created_at' => Carbon::now()->subMinutes(30)
            ],

            // CÁC BÀI SHARE (Logic original_post_id)
            [
                'id' => 8, 'user_id' => 1, 'original_post_id' => 5, // Trang share bài tuyển dụng
                'content' => 'Cơ hội tốt cho mấy ông PNV nè, apply lẹ đi!',
                'media_url' => null, 'media_type' => null, 'created_at' => Carbon::now()->subMinutes(15)
            ],
            [
                'id' => 9, 'user_id' => 3, 'original_post_id' => 2, // Nam share bài portfolio của Trang
                'content' => 'Giao diện sạch đẹp lắm Trang ơi!',
                'media_url' => null, 'media_type' => null, 'created_at' => Carbon::now()->subMinutes(5)
            ],
        ];

        // Thêm thêm 5 bài post ngắn ngẫu nhiên cho đủ số lượng
        for ($i = 10; $i <= 13; $i++) {
            $posts[] = [
                'id' => $i,
                'user_id' => rand(1, 3),
                'original_post_id' => null,
                'content' => 'Status ngẫu nhiên thứ ' . $i . ': Đang tập trung code dự án cuối kỳ...',
                'media_url' => null,
                'media_type' => null,
                'created_at' => Carbon::now()->subMinutes($i * 10)
            ];
        }

        DB::table('posts')->insert($posts);

        // 3. TẠO THÊM BÌNH LUẬN CHO XÔM (COMMENTS)
        DB::table('comments')->insert([
            ['user_id' => 2, 'post_id' => 1, 'parent_id' => null, 'content' => 'Inbox mình gửi link khóa học Laravel trên Udemy cho nè.', 'created_at' => now()],
            ['user_id' => 1, 'post_id' => 5, 'parent_id' => null, 'content' => 'Em đã gửi CV qua mail rồi ạ, mong được anh/chị phản hồi!', 'created_at' => now()],
            ['user_id' => 3, 'post_id' => 3, 'parent_id' => null, 'content' => 'Thấu cảm sâu sắc bro ơi, tui cũng vừa bị xong!', 'created_at' => now()],
        ]);

        // 4. REACTION (Cảm xúc)
        DB::table('reactions')->insert([
            ['user_id' => 1, 'post_id' => 3, 'type' => 'haha', 'created_at' => now()],
            ['user_id' => 2, 'post_id' => 5, 'type' => 'love', 'created_at' => now()],
            ['user_id' => 3, 'post_id' => 5, 'type' => 'like', 'created_at' => now()],
            ['user_id' => 4, 'post_id' => 1, 'type' => 'wow', 'created_at' => now()],
            ['user_id' => 5, 'post_id' => 1, 'type' => 'angry', 'created_at' => now()],
        ]);

        // 5. BẠN BÈ
        DB::table('friendships')->insert([
            ['sender_id' => 5, 'receiver_id' => 2, 'status' => 'accepted', 'created_at' => now()],
            ['sender_id' => 5, 'receiver_id' => 1, 'status' => 'accepted', 'created_at' => now()],
            ['sender_id' => 5, 'receiver_id' => 3, 'status' => 'pending', 'created_at' => now()],
        ]);
    }
}