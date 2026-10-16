<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemContentSeeder extends Seeder
{
    public function run(): void
    {
        // 2.1. System Settings
        $settings = [
            ['key' => 'hotline', 'value' => '0987.654.321', 'group' => 'contact'],
            ['key' => 'email', 'value' => 'lienhe@datlanhfarm.vn', 'group' => 'contact'],
            ['key' => 'farm_address', 'value' => 'Thôn 3, Xã Đạ Ròn, Huyện Đơn Dương, Lâm Đồng', 'group' => 'contact'],
            ['key' => 'free_ship_threshold', 'value' => '300000', 'group' => 'shipping'],
            ['key' => 'base_shipping_fee_per_km', 'value' => '5000', 'group' => 'shipping'],
            ['key' => 'vip_monthly_subscription_price', 'value' => '1200000', 'group' => 'subscription'],
        ];
        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(['key' => $setting['key']], $setting);
        }

        // 2.2. Banners
        $banners = [
            [
                'title' => 'Nông Sản Sạch Tươi Ngon Thu Hoạch Mỗi Sớm',
                'description' => 'Xem chi tiết nhật ký gieo trồng, kiểm định chất lượng và thời gian thu hoạch thực tế qua mã QR.',
                'button_text' => 'Khám phá ngay',
                'image' => 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=1600',
                'link' => '/san-pham',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Minh Bạch Nguồn Gốc - Giao Nhanh Tận Bếp',
                'description' => 'Nông sản xuất kho theo nguyên tắc FEFO, giữ trọn hàm lượng vitamin và độ tươi giòn tự nhiên.',
                'button_text' => 'Xem thực đơn hôm nay',
                'image' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1600',
                'link' => '/san-pham',
                'sort_order' => 2,
                'status' => 'active',
            ],
        ];
        foreach ($banners as $banner) {
            DB::table('banners')->updateOrInsert(['title' => $banner['title']], $banner);
        }

        // 2.3. Pages
        $pages = [
            [
                'slug' => 'cau-chuyen-thuong-hieu',
                'title' => 'Câu Chuyện Thương Hiệu Nông Trại Đất Lành',
                'content' => '<p>Đất Lành khởi nguồn từ khát vọng mang đến nguồn rau củ quả thuần khiết nhất...</p>',
                'created_at' => now(),
            ],
            [
                'slug' => 'chinh-sach-doi-tra',
                'title' => 'Quy Trình Bồi Thường & Đổi Trả Nông Sản Dập Nát',
                'content' => '<p>Cam kết hoàn tiền hoặc giao bù trong 24h nếu sản phẩm bị hư hỏng do vận chuyển...</p>',
                'created_at' => now(),
            ],
        ];
        foreach ($pages as $page) {
            DB::table('pages')->updateOrInsert(['slug' => $page['slug']], $page);
        }

        // 2.4. Farm Images (Gallery)
        $farmImages = [
            ['image_url' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=800', 'caption' => 'Hệ thống nhà màng A1 canh tác hữu cơ', 'sort_order' => 1, 'created_at' => now()],
            ['image_url' => 'https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?w=800', 'caption' => 'Hệ thống tưới nhỏ giọt tự động Israel', 'sort_order' => 2, 'created_at' => now()],
        ];
        foreach ($farmImages as $img) {
            DB::table('farm_images')->updateOrInsert(['image_url' => $img['image_url']], $img);
        }

        // 2.5. Notifications
        $customer = User::where('email', 'thanhtruc@example.com')->first();
        if ($customer) {
            $notifications = [
                [
                    'user_id' => $customer->id,
                    'type' => 'order_placed',
                    'title' => 'Đặt đơn hàng thành công',
                    'content' => 'Đơn hàng #ORD-20261012-001 đã được tiếp nhận và đang đóng gói.',
                    'data' => json_encode(['order_id' => 1]),
                    'read_at' => now(),
                ],
                [
                    'user_id' => $customer->id,
                    'type' => 'complaint_resolved',
                    'title' => 'Yêu cầu bồi thường đã duyệt',
                    'content' => 'Yêu cầu bồi thường đơn hàng #ORD-20261012-001 đã được chấp nhận. Đơn bù 0đ đã khởi tạo.',
                    'data' => json_encode(['complaint_id' => 1]),
                    'read_at' => null,
                ],
            ];
            foreach ($notifications as $notif) {
                DB::table('notifications')->insert($notif);
            }
        }
    }
}