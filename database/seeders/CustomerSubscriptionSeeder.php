<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'thanhtruc@example.com')->first();
        if (!$customer) return;

        // 5.1. Addresses
        DB::table('addresses')->updateOrInsert(
            ['user_id' => $customer->id, 'is_default' => true],
            [
                'recipient_name' => 'Lê Thanh Trúc',
                'recipient_phone' => '0909123456',
                'province' => 'TP. Hồ Chí Minh',
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'address_detail' => 'Số 123 Đường Nguyễn Huệ, Căn hộ 4B',
                'is_default' => true,
            ]
        );

        // 5.2. Carts & Cart Items
        $cartId = DB::table('carts')->insertGetId([
            'user_id' => $customer->id,
        ]);

        $productBoxoi = Product::where('slug', 'cai-bo-xoi-huu-co')->first();
        if ($productBoxoi) {
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_id' => $productBoxoi->id,
                'quantity' => 2,
                'price' => 45000,
            ]);
        }

        // 5.3. Subscriptions (Gói VIP Rau Tuần 1 Tháng)
        $subId = DB::table('subscriptions')->insertGetId([
            'user_id' => $customer->id,
            'recipient_name' => 'Lê Thanh Trúc',
            'recipient_phone' => '0909123456',
            'recipient_address' => 'Số 123 Đường Nguyễn Huệ, P. Bến Nghé, Q.1, TP.HCM',
            'delivery_days' => 'T3, T6',
            'total_price' => 1200000,
            'total_deliveries' => 8,
            'delivered_count' => 2,
            'status' => 'active',
            'created_at' => now()->subDays(10),
        ]);

        // 5.4. Delivery Schedules (8 Đợt giao)
        for ($i = 1; $i <= 8; $i++) {
            DB::table('delivery_schedules')->insert([
                'subscription_id' => $subId,
                'delivery_order' => $i,
                'delivery_date' => now()->addDays(($i - 2) * 3)->format('Y-m-d'),
                'items' => json_encode([
                    ['product_id' => 1, 'product_name' => 'Cải Bó Xôi Hữu Cơ', 'quantity' => 1],
                    ['product_id' => 2, 'product_name' => 'Cà Chua Cherry Đà Lạt', 'quantity' => 1],
                ]),
                'order_id' => $i <= 2 ? $i : null, // Đã sinh đơn cho 2 đợt đầu
                'status' => $i <= 2 ? 'delivered' : 'pending',
            ]);
        }
    }
}