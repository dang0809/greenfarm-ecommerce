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

        // 1. Địa chỉ mặc định
        DB::table('addresses')->updateOrInsert(
            ['user_id' => $customer->id, 'is_default' => true],
            [
                'recipient_name'    => 'Lê Thanh Trúc',
                'recipient_phone'   => '0909123456',
                'province'          => 'TP. Hồ Chí Minh',
                'district'          => 'Quận 1',
                'ward'              => 'Phường Bến Nghé',
                'address_detail'    => 'Số 123 Đường Nguyễn Huệ, Căn hộ 4B',
                'latitude'          => 10.7743,
                'longitude'         => 106.7032,
                'is_default'        => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        // 2. Giỏ hàng & Sản phẩm trong giỏ
        $oldCartIds = DB::table('carts')->where('user_id', $customer->id)->pluck('id');
        if ($oldCartIds->isNotEmpty()) {
            DB::table('cart_items')->whereIn('cart_id', $oldCartIds)->delete();
            DB::table('carts')->whereIn('id', $oldCartIds)->delete();
        }

        $cartId = DB::table('carts')->insertGetId([
            'user_id'    => $customer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productBoxoi = Product::where('slug', 'cai-bo-xoi-huu-co')->first();
        if ($productBoxoi) {
            DB::table('cart_items')->insert([
                'cart_id'    => $cartId,
                'product_id' => $productBoxoi->id,
                'quantity'   => 2,
                'price'      => 45000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Đăng ký Gói VIP (Subscriptions)
        $oldSubIds = DB::table('subscriptions')->where('user_id', $customer->id)->pluck('id');
        if ($oldSubIds->isNotEmpty()) {
            DB::table('delivery_schedules')->whereIn('subscription_id', $oldSubIds)->delete();
            DB::table('subscriptions')->whereIn('id', $oldSubIds)->delete();
        }

        $subId = DB::table('subscriptions')->insertGetId([
            'user_id'           => $customer->id,
            'recipient_name'    => 'Lê Thanh Trúc',
            'recipient_phone'   => '0909123456',
            'recipient_address' => 'Số 123 Đường Nguyễn Huệ, P. Bến Nghé, Q.1, TP.HCM',
            'delivery_days'     => 'T3, T6',
            'total_price'       => 1200000,

            'status'            => 'active',
            'created_at'        => now()->subDays(10),
            'updated_at'        => now()->subDays(10),
        ]);

        // 4. Lịch giao hàng từng đợt (Delivery Schedules)
        for ($i = 1; $i <= 8; $i++) {
            DB::table('delivery_schedules')->insert([
                'subscription_id' => $subId,
                'delivery_order'  => $i,
                'delivery_date'   => now()->addDays(($i - 2) * 3)->format('Y-m-d'),
                'items'           => json_encode([
                    ['product_id' => 1, 'product_name' => 'Cải Bó Xôi Hữu Cơ', 'quantity' => 1],
                    ['product_id' => 2, 'product_name' => 'Cà Chua Cherry Đà Lạt', 'quantity' => 1],
                ]),
                'order_id'        => null,
                'status'          => $i <= 2 ? 'delivered' : 'pending',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}