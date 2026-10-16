<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderComplaintReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'customer')->get()->keyBy('email');
        $sales = User::where('role', 'sales')->first();
        $pBoxoi = Product::where('slug', 'cai-bo-xoi-huu-co')->first();
        $pCachua = Product::where('slug', 'ca-chua-cherry-da-lat')->first();
        $batch = DB::table('product_batches')->first();

        // 1. Đơn hàng #1: Đã giao + Đánh giá + Khiếu nại
        $u1 = $users->get('thanhtruc@example.com');
        if ($u1) {
            $o1 = DB::table('orders')->insertGetId([
                'user_id' => $u1->id, 'order_code' => '#ORD-20261012-001', 'order_type' => 'retail',
                'subtotal' => 155000, 'shipping_distance_km' => 4.5, 'shipping_fee' => 20000,
                'points_used' => 10, 'points_earned' => 15, 'total' => 165000,
                'payment_method' => 'vnpay', 'payment_status' => 'paid',
                'shipping_provider' => 'Ahamove', 'tracking_code' => 'AHA-99882211',
                'recipient_name' => $u1->name, 'recipient_phone' => $u1->phone,
                'recipient_address' => '123 Nguyễn Huệ, P. Bến Nghé, Q.1, TP.HCM',
                'status' => 'delivered', 'note' => 'Giao trước 11h sáng giúp em', 'created_at' => now()->subDays(3),
            ]);

            $oi1 = DB::table('order_items')->insertGetId(['order_id' => $o1, 'product_id' => $pBoxoi?->id ?? 1, 'batch_id' => $batch?->id ?? 1, 'product_name' => 'Cải Bó Xôi Hữu Cơ (Spinach)', 'price' => 45000, 'quantity' => 2, 'subtotal' => 90000]);
            $oi2 = DB::table('order_items')->insertGetId(['order_id' => $o1, 'product_id' => $pCachua?->id ?? 2, 'batch_id' => $batch?->id ?? 1, 'product_name' => 'Cà Chua Cherry Đà Lạt Nguyên Chùm', 'price' => 65000, 'quantity' => 1, 'subtotal' => 65000]);

            DB::table('payments')->insert(['order_id' => $o1, 'payment_code' => 'PAY-VNP-1001', 'method' => 'vnpay', 'amount' => 165000, 'status' => 'success', 'transaction_code' => 'VNP141209348', 'paid_at' => now()->subDays(3)]);

            // Khiếu nại đền bù
            $comp = DB::table('complaints')->insertGetId([
                'user_id' => $u1->id, 'order_id' => $o1, 'type' => 'exchange', 'reason' => 'Hộp cà chua bị móp ở đáy, có 3 quả bị nứt chảy nước.',
                'status' => 'resolved', 'resolved_by' => $sales?->id ?? 3, 'resolution_note' => 'Đã thẩm định hình ảnh. Duyệt giao bù 1 hộp mẻ mới 0đ.', 'created_at' => now()->subDays(1),
            ]);
            DB::table('complaint_images')->insert(['complaint_id' => $comp, 'image' => 'https://images.unsplash.com/photo-1592282139728-28040d96a922?w=500']);

            // Đánh giá 5 sao
            $rev = DB::table('reviews')->insertGetId([
                'user_id' => $u1->id, 'product_id' => $pBoxoi?->id ?? 1, 'order_item_id' => $oi1,
                'rating' => 5, 'content' => 'Rau cải bó xôi rất tươi giòn, lá xanh đậm không sâu dập. Quét QR ra đúng lô thu hoạch sáng sớm luôn!', 'status' => 'approved',
            ]);
            DB::table('review_images')->insert(['review_id' => $rev, 'image' => 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=500', 'sort_order' => 1]);
        }

        // 2. Đơn hàng #2: Đang giao (Processing)
        $u2 = $users->get('minhhoang@example.com');
        if ($u2) {
            DB::table('orders')->insert([
                'user_id' => $u2->id, 'order_code' => '#ORD-20261015-002', 'order_type' => 'retail',
                'subtotal' => 120000, 'shipping_distance_km' => 6.2, 'shipping_fee' => 25000,
                'points_used' => 0, 'points_earned' => 12, 'total' => 145000,
                'payment_method' => 'cod', 'payment_status' => 'pending',
                'shipping_provider' => 'Lalamove', 'tracking_code' => 'LALA-77665544',
                'recipient_name' => $u2->name, 'recipient_phone' => $u2->phone,
                'recipient_address' => '456 Lê Văn Sỹ, Q.3, TP.HCM',
                'status' => 'processing', 'note' => 'Gọi trước khi giao 15 phút', 'created_at' => now()->subHours(4),
            ]);
        }

        // 3. Đơn hàng #3: Đơn giao bù khiếu nại (Compensation 0đ)
        if ($u1) {
            DB::table('orders')->insert([
                'user_id' => $u1->id, 'order_code' => '#ORD-20261016-BUP', 'order_type' => 'compensation',
                'subtotal' => 0, 'shipping_distance_km' => 4.5, 'shipping_fee' => 0,
                'points_used' => 0, 'points_earned' => 0, 'total' => 0,
                'payment_method' => 'cod', 'payment_status' => 'paid',
                'shipping_provider' => 'Ahamove', 'tracking_code' => 'AHA-11223344',
                'recipient_name' => $u1->name, 'recipient_phone' => $u1->phone,
                'recipient_address' => '123 Nguyễn Huệ, P. Bến Nghé, Q.1, TP.HCM',
                'status' => 'shipped', 'note' => 'Đơn giao bù khiếu nại dập nát - Miễn phí 100%', 'created_at' => now()->subHours(2),
            ]);
        }
    }
}