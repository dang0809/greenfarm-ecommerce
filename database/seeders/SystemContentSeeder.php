<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemContentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cấu hình lõi hệ thống (system_settings)
        $settings = [
            // Nhóm Liên hệ & Địa chỉ Kho/Nông trại
            ['key' => 'hotline', 'value' => '0987.654.321', 'group' => 'contact'],
            ['key' => 'email', 'value' => 'lienhe@datlanhfarm.vn', 'group' => 'contact'],
            ['key' => 'farm_address', 'value' => 'Số 86 đường Lê Thánh Tôn, Phường Sài Gòn, Quận 1, Thành phố Hồ Chí Minh', 'group' => 'contact'],
            ['key' => 'farm_latitude', 'value' => '10.7769', 'group' => 'contact'],
            ['key' => 'farm_longitude', 'value' => '106.7009', 'group' => 'contact'],

            // Nhóm Vận chuyển
            ['key' => 'max_shipping_distance_km', 'value' => '25', 'group' => 'shipping'],

            // Nhóm Gói thành viên VIP
            ['key' => 'vip_package_name', 'value' => 'Gói Bếp Xanh VIP Gia Đình (1 Tháng)', 'group' => 'subscription'],
            ['key' => 'vip_package_price', 'value' => '1200000', 'group' => 'subscription'],
            ['key' => 'vip_delivery_max_weight', 'value' => '3.5', 'group' => 'subscription'],
            ['key' => 'vip_subscription_enabled', 'value' => 'true', 'group' => 'subscription'],

            // Nhóm Tích điểm & Đổi thưởng (Loyalty)
            ['key' => 'spending_per_point', 'value' => '10000', 'group' => 'loyalty'],
            ['key' => 'value_per_point', 'value' => '100', 'group' => 'loyalty'],
            ['key' => 'max_point_discount_percent', 'value' => '10', 'group' => 'loyalty'],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(['key' => $setting['key']], $setting);
        }

        // 2. Khung 2 trang tĩnh mặc định (có thể chỉnh sửa nội dung thật trong Admin)
        $pages = [
            [
                'slug' => 'cau-chuyen-thuong-hieu',
                'title' => 'Câu Chuyện Thương Hiệu [[farm_name]]',
                'content' => '<p>[[farm_name]] khởi nguồn từ khát vọng mang đến nguồn rau củ quả thuần khiết nhất...</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'chinh-sach-doi-tra',
                'title' => 'Quy Trình Bồi Thường & Đổi Trả Nông Sản Dập Nát',
                'content' => '<p>Cam kết hoàn tiền hoặc giao bù trong 24h nếu sản phẩm bị hư hỏng do vận chuyển...</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
            DB::table('pages')->updateOrInsert(['slug' => $page['slug']], $page);
        }
    }
}
