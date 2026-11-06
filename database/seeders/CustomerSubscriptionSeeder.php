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
    }
}