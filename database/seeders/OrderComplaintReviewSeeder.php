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
        // 1. Lấy dữ liệu sản phẩm & lô thu hoạch thực tế từ Database
        $products = Product::all()->keyBy('slug');
        $pBoxoi   = $products->get('cai-bo-xoi-huu-co') ?? Product::first();
        $pCachua  = $products->get('ca-chua-cherry-da-lat') ?? Product::skip(1)->first() ?? $pBoxoi;
        $batch    = DB::table('product_batches')->first();

        // 2. Nhân viên & Khách hàng mục tiêu
        $sales = User::where('role', 'sales')->first() ?? User::where('role', 'admin')->first();

        $targetCustomers = User::where('role', 'customer')
            ->whereIn('email', ['thanhtruc@example.com', 'quocbao@example.com'])
            ->get();
        if ($targetCustomers->isEmpty()) {
            $targetCustomers = User::where('role', 'customer')->take(2)->get();
        }

        // Helper chèn Lịch sử trạng thái đơn hàng (Audit Trail)
        $addHistory = function (int $orderId, string $newStatus, ?string $oldStatus = null, ?string $note = null, $createdAt = null, ?int $changedBy = null) use ($sales) {
            DB::table('order_status_histories')->insert([
                'order_id'   => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $changedBy ?? $sales?->id ?? 1,
                'note'       => $note,
                'created_at' => $createdAt ?? now(),
                'updated_at' => $createdAt ?? now(),
            ]);
        };

        // ---------------------------------------------------------------------
        // DỌN DẸP DỮ LIỆU CŨ TỪ SEEDER ĐỂ TRÁNH LỖI UNIQUE CONSTRAINT VÀ TRÙNG LẶP
        // ---------------------------------------------------------------------
        $existingCodes = [];
        foreach ($targetCustomers as $userIndex => $user) {
            $prefix = ($userIndex === 0) ? 'TT' : 'QB';
            $existingCodes = array_merge($existingCodes, [
                'ORD-' . $prefix . '-WAITPAY-01',
                'VIP-' . $prefix . '-WAITPAY-02',
                'ORD-' . $prefix . '-WAITCONF-01',
                'VIP-' . $prefix . '-DELIV-W1',
                'ORD-' . $prefix . '-PROCES-01',
                'VIP-' . $prefix . '-DELIV-W2',
                'ORD-' . $prefix . '-SHIP-01',
                'VIP-' . $prefix . '-DELIV-W3',
                'ORD-' . $prefix . '-DELIV-01',
                'VIP-' . $prefix . '-DELIV-W4',
                'VIP-' . $prefix . '-SUB-ACTIVE',
                'ORD-' . $prefix . '-CANCEL-01',
                'VIP-' . $prefix . '-CANCEL-02',
                'ORD-' . $prefix . '-CANCEL-03',
                'ORD-' . $prefix . '-REFUND-PENDING',
                'ORD-' . $prefix . '-REFUND-APPROVED',
                'ORD-' . $prefix . '-REFUND-REJECTED',
                'VIP-' . $prefix . '-REFUND-COMPLAINT',
                'ORD-' . $prefix . '-REFUND-RESOLVED',
            ]);
        }

        $existingOrderIds = DB::table('orders')->whereIn('order_code', $existingCodes)->pluck('id');
        if ($existingOrderIds->isNotEmpty()) {
            DB::table('delivery_schedules')->whereIn('order_id', $existingOrderIds)->delete();
            DB::table('order_status_histories')->whereIn('order_id', $existingOrderIds)->delete();
            DB::table('payments')->whereIn('order_id', $existingOrderIds)->delete();
            $compIds = DB::table('complaints')->whereIn('order_id', $existingOrderIds)->pluck('id');
            if ($compIds->isNotEmpty()) {
                DB::table('complaint_images')->whereIn('complaint_id', $compIds)->delete();
                DB::table('complaints')->whereIn('id', $compIds)->delete();
            }
            $itemIds = DB::table('order_items')->whereIn('order_id', $existingOrderIds)->pluck('id');
            if ($itemIds->isNotEmpty()) {
                $revIds = DB::table('reviews')->whereIn('order_item_id', $itemIds)->pluck('id');
                if ($revIds->isNotEmpty()) {
                    DB::table('review_images')->whereIn('review_id', $revIds)->delete();
                    DB::table('reviews')->whereIn('id', $revIds)->delete();
                }
            }
            DB::table('order_items')->whereIn('order_id', $existingOrderIds)->delete();
            DB::table('orders')->whereIn('id', $existingOrderIds)->delete();
        }

        // Dọn dẹp subscriptions và schedules cũ của các khách hàng test
        $customerUserIds = $targetCustomers->pluck('id');
        $oldSubIds = DB::table('subscriptions')->whereIn('user_id', $customerUserIds)->pluck('id');
        if ($oldSubIds->isNotEmpty()) {
            DB::table('delivery_schedules')->whereIn('subscription_id', $oldSubIds)->delete();
            DB::table('subscriptions')->whereIn('id', $oldSubIds)->delete();
        }

        // ---------------------------------------------------------------------
        // SEED ĐỦ 7 TAB CHO TỪNG KHÁCH HÀNG:
        // MỖI TAB CÓ 2-3 ĐƠN (GỒM CẢ ĐƠN BÌNH THƯỜNG VÀ ĐƠN VIP)
        // VỚI ĐẦY ĐỦ LỊCH SỬ TRẠNG THÁI TUẦN TỰ (PENDING -> PROCESSING -> SHIPPED -> DELIVERED)
        // ---------------------------------------------------------------------
        foreach ($targetCustomers as $userIndex => $user) {
            $prefix  = ($userIndex === 0) ? 'TT' : 'QB';
            $address = '123 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP.HCM';
            $phone   = $user->phone ?? '0909123456';
            $priceBoxoi  = $pBoxoi?->price ?? 45000;
            $priceCachua = $pCachua?->price ?? 65000;

            // Khởi tạo Hợp đồng Gói VIP trong bảng subscriptions để liên kết lịch giao
            $subId = DB::table('subscriptions')->insertGetId([
                'user_id'           => $user->id,
                'recipient_name'    => $user->name,
                'recipient_phone'   => $phone,
                'recipient_address' => $address,
                'delivery_days'     => 'T3, T6',
                'total_price'       => 1200000,
                'total_deliveries'  => 4,
                'delivered_count'   => 1,
                'status'            => 'active',
                'created_at'        => now()->subDays(15),
                'updated_at'        => now()->subDays(1),
            ]);

            // =================================================================
            // TAB 1: CHỜ THANH TOÁN (pending_payment: status = pending, payment_status = pending)
            // =================================================================
            // 1.1 Đơn thường bán lẻ
            $t1_time = now()->subMinutes(2);
            $t1_sub = $priceBoxoi * 2;
            $o1_retail = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-WAITPAY-01',
                'order_type'           => 'retail',
                'subtotal'             => $t1_sub,
                'shipping_distance_km' => 3.0,
                'shipping_fee'         => 18000,
                'points_used'          => 0,
                'points_earned'        => 9,
                'total'                => $t1_sub + 18000,
                'payment_method'       => 'payos',
                'payment_status'       => 'pending',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'pending',
                'note'                 => 'Đơn rau sạch giao tận nhà, chờ quét mã PayOS',
                'created_at'           => $t1_time,
                'updated_at'           => $t1_time,
            ]);
            $addHistory($o1_retail, 'pending', null, 'Khách hàng tạo đơn hàng qua cổng PayOS, đang chờ thanh toán.', $t1_time, $user->id);

            DB::table('order_items')->insert([
                'order_id'     => $o1_retail,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                'price'        => $priceBoxoi,
                'quantity'     => 2,
                'subtotal'     => $t1_sub,
                'created_at'   => $t1_time,
                'updated_at'   => $t1_time,
            ]);

            // 1.2 Đơn VIP mua gói đăng ký (chờ thanh toán)
            $t1_vip_time = now()->subMinutes(1);
            $o1_vip = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'VIP-' . $prefix . '-WAITPAY-02',
                'order_type'           => 'subscription',
                'subtotal'             => 1200000,
                'shipping_fee'         => 0,
                'total'                => 1200000,
                'payment_method'       => 'payos',
                'payment_status'       => 'pending',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'pending',
                'note'                 => 'Đăng ký Gói VIP Rau Sạch Hữu Cơ 1 Tháng (4 kỳ giao), chờ thanh toán',
                'created_at'           => $t1_vip_time,
                'updated_at'           => $t1_vip_time,
            ]);
            $addHistory($o1_vip, 'pending', null, 'Khách hàng đăng ký Gói VIP, đang chờ thanh toán qua cổng PayOS.', $t1_vip_time, $user->id);

            DB::table('order_items')->insert([
                'order_id'     => $o1_vip,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => 'Gói VIP Rau Sạch Hữu Cơ 1 Tháng (4 kỳ giao tận nơi)',
                'price'        => 1200000,
                'quantity'     => 1,
                'subtotal'     => 1200000,
                'created_at'   => $t1_vip_time,
                'updated_at'   => $t1_vip_time,
            ]);


            // =================================================================
            // TAB 2: CHỜ XÁC NHẬN (pending_confirmation: status = pending, payment_status = paid)
            // =================================================================
            // 2.1 Đơn thường bán lẻ (đã thanh toán, chờ nông trại duyệt)
            $t2_time = now()->subMinutes(35);
            $t2_sub = $priceCachua * 2;
            $o2_retail = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-WAITCONF-01',
                'order_type'           => 'retail',
                'subtotal'             => $t2_sub,
                'shipping_distance_km' => 2.5,
                'shipping_fee'         => 15000,
                'points_used'          => 0,
                'points_earned'        => 13,
                'total'                => $t2_sub + 15000,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'pending',
                'note'                 => 'Đã chuyển khoản xong, nhờ shop giao sớm giùm',
                'created_at'           => $t2_time,
                'updated_at'           => $t2_time->copy()->addMinutes(2),
            ]);
            $addHistory($o2_retail, 'pending', null, 'Khách hàng tạo đơn hàng bán lẻ.', $t2_time, $user->id);
            $addHistory($o2_retail, 'pending', 'pending', 'Xác nhận thanh toán tự động thành công qua PayOS (Mã GD: POS-CONF-01).', $t2_time->copy()->addMinutes(2), $user->id);

            DB::table('order_items')->insert([
                'order_id'     => $o2_retail,
                'product_id'   => $pCachua?->id ?? 2,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt',
                'price'        => $priceCachua,
                'quantity'     => 2,
                'subtotal'     => $t2_sub,
                'created_at'   => $t2_time,
                'updated_at'   => $t2_time,
            ]);

            // 2.2 Đơn giao rau VIP theo đợt (đã thanh toán gói VIP, chờ duyệt đợt giao)
            $t2_vip_time = now()->subMinutes(25);
            $o2_vip = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'VIP-' . $prefix . '-DELIV-W1',
                'order_type'           => 'subscription_delivery',
                'subtotal'             => 0,
                'shipping_fee'         => 0,
                'total'                => 0,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'pending',
                'note'                 => 'Giao buổi sáng trước 10h giúp mình, bấm chuông cửa',
                'created_at'           => $t2_vip_time,
                'updated_at'           => $t2_vip_time,
            ]);
            $addHistory($o2_vip, 'pending', null, 'Hệ thống tự động kích hoạt Đợt giao rau 1/4 từ Gói VIP.', $t2_vip_time, $user->id);
            $addHistory($o2_vip, 'pending', 'pending', 'Đã đối soát thanh toán hợp lệ từ Gói VIP, chờ nông trại xác nhận xuất kho.', $t2_vip_time->copy()->addMinutes(5), $sales?->id ?? 1);

            DB::table('delivery_schedules')->insert([
                'subscription_id' => $subId,
                'delivery_order'  => 1,
                'delivery_date'   => $t2_vip_time->toDateString(),
                'items'           => json_encode([
                    ['product_id' => $pBoxoi?->id ?? 1, 'name' => 'Cải Bó Xôi Hữu Cơ', 'quantity' => 2],
                    ['product_id' => $pCachua?->id ?? 2, 'name' => 'Cà Chua Cherry Đà Lạt', 'quantity' => 1],
                ]),
                'order_id'        => $o2_vip,
                'status'          => 'pending',
                'created_at'      => $t2_vip_time,
                'updated_at'      => $t2_vip_time,
            ]);

            DB::table('order_items')->insert([
                [
                    'order_id'     => $o2_vip,
                    'product_id'   => $pBoxoi?->id ?? 1,
                    'batch_id'     => $batch?->id ?? 1,
                    'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                    'price'        => $priceBoxoi,
                    'quantity'     => 2,
                    'subtotal'     => 0,
                    'created_at'   => $t2_vip_time,
                    'updated_at'   => $t2_vip_time,
                ],
                [
                    'order_id'     => $o2_vip,
                    'product_id'   => $pCachua?->id ?? 2,
                    'batch_id'     => $batch?->id ?? 1,
                    'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt',
                    'price'        => $priceCachua,
                    'quantity'     => 1,
                    'subtotal'     => 0,
                    'created_at'   => $t2_vip_time,
                    'updated_at'   => $t2_vip_time,
                ]
            ]);


            // =================================================================
            // TAB 3: CHỜ LẤY HÀNG (processing: status = processing, payment_status = paid)
            // =================================================================
            // 3.1 Đơn thường bán lẻ
            $t3_time = now()->subHours(2);
            $t3_sub = ($priceBoxoi * 3) + ($priceCachua * 1);
            $o3_retail = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-PROCES-01',
                'order_type'           => 'retail',
                'subtotal'             => $t3_sub,
                'shipping_distance_km' => 4.0,
                'shipping_fee'         => 20000,
                'points_used'          => 0,
                'points_earned'        => 20,
                'total'                => $t3_sub + 20000,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'processing',
                'note'                 => 'Rau hái tươi đóng thùng giữ lạnh',
                'created_at'           => $t3_time,
                'updated_at'           => $t3_time->copy()->addMinutes(20),
            ]);
            // Đầy đủ lịch sử: pending -> processing
            $addHistory($o3_retail, 'pending', null, 'Khách hàng đặt hàng trực tuyến.', $t3_time, $user->id);
            $addHistory($o3_retail, 'pending', 'pending', 'Thanh toán PayOS thành công.', $t3_time->copy()->addMinutes(3), $user->id);
            $addHistory($o3_retail, 'processing', 'pending', 'Nhà vườn đã xác nhận đơn & nhân viên đang thu hoạch đóng gói theo lô FEFO.', $t3_time->copy()->addMinutes(20), $sales?->id ?? 1);

            DB::table('order_items')->insert([
                [
                    'order_id'     => $o3_retail,
                    'product_id'   => $pBoxoi?->id ?? 1,
                    'batch_id'     => $batch?->id ?? 1,
                    'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                    'price'        => $priceBoxoi,
                    'quantity'     => 3,
                    'subtotal'     => $priceBoxoi * 3,
                    'created_at'   => $t3_time,
                    'updated_at'   => $t3_time,
                ],
                [
                    'order_id'     => $o3_retail,
                    'product_id'   => $pCachua?->id ?? 2,
                    'batch_id'     => $batch?->id ?? 1,
                    'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt',
                    'price'        => $priceCachua,
                    'quantity'     => 1,
                    'subtotal'     => $priceCachua * 1,
                    'created_at'   => $t3_time,
                    'updated_at'   => $t3_time,
                ]
            ]);

            // 3.2 Đơn giao rau VIP theo đợt (đang đóng gói đợt 2)
            $t3_vip_time = now()->subHours(3);
            $o3_vip = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'VIP-' . $prefix . '-DELIV-W2',
                'order_type'           => 'subscription_delivery',
                'subtotal'             => 0,
                'shipping_fee'         => 0,
                'total'                => 0,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'processing',
                'note'                 => 'Để thùng rau ở cửa phòng bảo vệ nếu vắng nhà',
                'created_at'           => $t3_vip_time,
                'updated_at'           => $t3_vip_time->copy()->addMinutes(30),
            ]);
            // Đầy đủ lịch sử: pending -> processing
            $addHistory($o3_vip, 'pending', null, 'Hệ thống khởi tạo đợt giao 2/4 từ Gói VIP.', $t3_vip_time, $user->id);
            $addHistory($o3_vip, 'pending', 'pending', 'Xác nhận hợp lệ thanh toán Gói VIP.', $t3_vip_time, $sales?->id ?? 1);
            $addHistory($o3_vip, 'processing', 'pending', 'Nông trại đang chuẩn bị thùng xốp bảo quản rau VIP chuẩn VietGAP theo lô thu hoạch sớm nhất.', $t3_vip_time->copy()->addMinutes(30), $sales?->id ?? 1);

            DB::table('delivery_schedules')->insert([
                'subscription_id' => $subId,
                'delivery_order'  => 2,
                'delivery_date'   => $t3_vip_time->toDateString(),
                'items'           => json_encode([
                    ['product_id' => $pBoxoi?->id ?? 1, 'name' => 'Cải Bó Xôi Hữu Cơ', 'quantity' => 4],
                ]),
                'order_id'        => $o3_vip,
                'status'          => 'processing',
                'created_at'      => $t3_vip_time,
                'updated_at'      => $t3_vip_time,
            ]);

            DB::table('order_items')->insert([
                'order_id'     => $o3_vip,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                'price'        => $priceBoxoi,
                'quantity'     => 4,
                'subtotal'     => 0,
                'created_at'   => $t3_vip_time,
                'updated_at'   => $t3_vip_time,
            ]);


            // =================================================================
            // TAB 4: ĐANG GIAO (shipped: status = shipped, payment_status = paid)
            // =================================================================
            // 4.1 Đơn thường bán lẻ
            $t4_time = now()->subHours(5);
            $t4_sub = $priceBoxoi * 3;
            $o4_retail = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-SHIP-01',
                'order_type'           => 'retail',
                'subtotal'             => $t4_sub,
                'shipping_distance_km' => 5.2,
                'shipping_fee'         => 25000,
                'points_used'          => 0,
                'points_earned'        => 13,
                'total'                => $t4_sub + 25000,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove',
                'tracking_code'        => 'AHA-' . $prefix . '-7788',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'shipped',
                'note'                 => 'Tài xế Ahamove đang giao hỏa tốc',
                'created_at'           => $t4_time,
                'updated_at'           => $t4_time->copy()->addHours(2),
            ]);
            // Đầy đủ lịch sử: pending -> processing -> shipped
            $addHistory($o4_retail, 'pending', null, 'Khách hàng đặt hàng trực tuyến.', $t4_time, $user->id);
            $addHistory($o4_retail, 'pending', 'pending', 'Thanh toán PayOS thành công.', $t4_time->copy()->addMinutes(2), $user->id);
            $addHistory($o4_retail, 'processing', 'pending', 'Nông trại hoàn tất đóng gói bảo quản mát.', $t4_time->copy()->addHours(1), $sales?->id ?? 1);
            $addHistory($o4_retail, 'shipped', 'processing', 'Đã bàn giao tài xế Ahamove (Mã: AHA-' . $prefix . '-7788) đang trên đường giao hàng.', $t4_time->copy()->addHours(2), $sales?->id ?? 1);

            DB::table('order_items')->insert([
                'order_id'     => $o4_retail,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                'price'        => $priceBoxoi,
                'quantity'     => 3,
                'subtotal'     => $t4_sub,
                'created_at'   => $t4_time,
                'updated_at'   => $t4_time,
            ]);

            // 4.2 Đơn giao rau VIP theo đợt (đang trên đường giao đợt 3)
            $t4_vip_time = now()->subHours(6);
            $o4_vip = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'VIP-' . $prefix . '-DELIV-W3',
                'order_type'           => 'subscription_delivery',
                'subtotal'             => 0,
                'shipping_fee'         => 0,
                'total'                => 0,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove',
                'tracking_code'        => 'AHA-VIP-' . $prefix . '-9900',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'shipped',
                'note'                 => 'Gọi trước khi giao 15 phút, lên thang máy tầng 3',
                'created_at'           => $t4_vip_time,
                'updated_at'           => $t4_vip_time->copy()->addHours(2),
            ]);
            // Đầy đủ lịch sử: pending -> processing -> shipped
            $addHistory($o4_vip, 'pending', null, 'Khởi tạo đợt giao 3/4 từ Gói VIP.', $t4_vip_time, $user->id);
            $addHistory($o4_vip, 'pending', 'pending', 'Xác nhận hợp lệ gói VIP.', $t4_vip_time, $sales?->id ?? 1);
            $addHistory($o4_vip, 'processing', 'pending', 'Đóng gói thùng rau VIP hoàn tất.', $t4_vip_time->copy()->addHours(1), $sales?->id ?? 1);
            $addHistory($o4_vip, 'shipped', 'processing', 'Bàn giao tài xế Ahamove VIP (Mã: AHA-VIP-' . $prefix . '-9900) đang giao tới nhà.', $t4_vip_time->copy()->addHours(2), $sales?->id ?? 1);

            DB::table('delivery_schedules')->insert([
                'subscription_id' => $subId,
                'delivery_order'  => 3,
                'delivery_date'   => $t4_vip_time->toDateString(),
                'items'           => json_encode([
                    ['product_id' => $pBoxoi?->id ?? 1, 'name' => 'Cải Bó Xôi Hữu Cơ', 'quantity' => 2],
                    ['product_id' => $pCachua?->id ?? 2, 'name' => 'Cà Chua Cherry Đà Lạt', 'quantity' => 2],
                ]),
                'order_id'        => $o4_vip,
                'status'          => 'shipped',
                'created_at'      => $t4_vip_time,
                'updated_at'      => $t4_vip_time,
            ]);

            DB::table('order_items')->insert([
                [
                    'order_id'     => $o4_vip,
                    'product_id'   => $pBoxoi?->id ?? 1,
                    'batch_id'     => $batch?->id ?? 1,
                    'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                    'price'        => $priceBoxoi,
                    'quantity'     => 2,
                    'subtotal'     => 0,
                    'created_at'   => $t4_vip_time,
                    'updated_at'   => $t4_vip_time,
                ],
                [
                    'order_id'     => $o4_vip,
                    'product_id'   => $pCachua?->id ?? 2,
                    'batch_id'     => $batch?->id ?? 1,
                    'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt',
                    'price'        => $priceCachua,
                    'quantity'     => 2,
                    'subtotal'     => 0,
                    'created_at'   => $t4_vip_time,
                    'updated_at'   => $t4_vip_time,
                ]
            ]);


            // =================================================================
            // TAB 5: ĐÃ GIAO (delivered: status = delivered, payment_status = paid)
            // =================================================================
            // 5.1 Đơn thường bán lẻ (đã nhận hàng thành công + có đánh giá 5 sao)
            $t5_time = now()->subDays(2);
            $t5_sub = ($priceBoxoi * 2) + ($priceCachua * 2);
            $o5_retail = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-DELIV-01',
                'order_type'           => 'retail',
                'subtotal'             => $t5_sub,
                'shipping_distance_km' => 3.5,
                'shipping_fee'         => 18000,
                'points_used'          => 10,
                'points_earned'        => 22,
                'total'                => $t5_sub + 18000 - 10000,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove',
                'tracking_code'        => 'AHA-' . $prefix . '-3344',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'delivered',
                'note'                 => 'Giao hàng thành công',
                'created_at'           => $t5_time,
                'updated_at'           => $t5_time->copy()->addHours(5),
            ]);
            // ĐẦY ĐỦ LỊCH SỬ TUẦN TỰ: pending -> processing -> shipped -> delivered
            $addHistory($o5_retail, 'pending', null, 'Khách hàng đặt hàng trực tuyến.', $t5_time, $user->id);
            $addHistory($o5_retail, 'pending', 'pending', 'Thanh toán PayOS thành công.', $t5_time->copy()->addMinutes(2), $user->id);
            $addHistory($o5_retail, 'processing', 'pending', 'Nông trại thu hoạch & đóng gói theo lô FEFO.', $t5_time->copy()->addHours(1), $sales?->id ?? 1);
            $addHistory($o5_retail, 'shipped', 'processing', 'Shipper Ahamove nhận hàng đi giao.', $t5_time->copy()->addHours(2), $sales?->id ?? 1);
            $addHistory($o5_retail, 'delivered', 'shipped', 'Giao hàng thành công! Khách hàng đã nhận đủ sản phẩm.', $t5_time->copy()->addHours(5), $user->id);

            $oi5_1 = DB::table('order_items')->insertGetId([
                'order_id'     => $o5_retail,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                'price'        => $priceBoxoi,
                'quantity'     => 2,
                'subtotal'     => $priceBoxoi * 2,
                'created_at'   => $t5_time,
                'updated_at'   => $t5_time,
            ]);

            DB::table('order_items')->insert([
                'order_id'     => $o5_retail,
                'product_id'   => $pCachua?->id ?? 2,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt',
                'price'        => $priceCachua,
                'quantity'     => 2,
                'subtotal'     => $priceCachua * 2,
                'created_at'   => $t5_time,
                'updated_at'   => $t5_time,
            ]);

            // Đánh giá 5 sao cho sản phẩm đơn này
            $revId = DB::table('reviews')->insertGetId([
                'user_id'       => $user->id,
                'product_id'    => $pBoxoi?->id ?? 1,
                'order_item_id' => $oi5_1,
                'rating'        => 5,
                'content'       => 'Rau cải bó xôi rất tươi ngon, cuống xanh giòn không dập nát. Giao hàng Ahamove hỏa tốc đóng gói rất cẩn thận!',
                'status'        => 'approved',
                'created_at'    => $t5_time->copy()->addHours(6),
                'updated_at'    => $t5_time->copy()->addHours(6),
            ]);

            DB::table('review_images')->insert([
                'review_id'  => $revId,
                'image'      => 'https://images.unsplash.com/photo-1592282139728-28040d96a922?w=500',
                'sort_order' => 1,
                'created_at' => $t5_time->copy()->addHours(6),
                'updated_at' => $t5_time->copy()->addHours(6),
            ]);

            // 5.2 Đơn VIP giao đợt 4 (đã giao hoàn tất đợt cuối gói VIP)
            $t5_vip_time = now()->subDays(1);
            $o5_vip_deliv = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'VIP-' . $prefix . '-DELIV-W4',
                'order_type'           => 'subscription_delivery',
                'subtotal'             => 0,
                'shipping_fee'         => 0,
                'total'                => 0,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove',
                'tracking_code'        => 'AHA-VIP-' . $prefix . '-5566',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'delivered',
                'note'                 => 'Giao giờ hành chính, gọi điện trước khi đến',
                'created_at'           => $t5_vip_time,
                'updated_at'           => $t5_vip_time->copy()->addHours(4),
            ]);
            // ĐẦY ĐỦ LỊCH SỬ TUẦN TỰ: pending -> processing -> shipped -> delivered
            $addHistory($o5_vip_deliv, 'pending', null, 'Khởi tạo Đợt giao rau VIP 4/4 (Kỳ cuối gói tháng).', $t5_vip_time, $user->id);
            $addHistory($o5_vip_deliv, 'pending', 'pending', 'Xác nhận hợp lệ thanh toán Gói VIP.', $t5_vip_time, $sales?->id ?? 1);
            $addHistory($o5_vip_deliv, 'processing', 'pending', 'Nông trại thu hoạch đợt rau VIP mới nhất đóng gói bảo quản.', $t5_vip_time->copy()->addHours(2), $sales?->id ?? 1);
            $addHistory($o5_vip_deliv, 'shipped', 'processing', 'Bàn giao tài xế Ahamove VIP giao hỏa tốc đến tận tay hội viên.', $t5_vip_time->copy()->addHours(3), $sales?->id ?? 1);
            $addHistory($o5_vip_deliv, 'delivered', 'shipped', 'Giao thành công! Hoàn tất chu kỳ 4 đợt giao rau tươi cho Hội Viên VIP.', $t5_vip_time->copy()->addHours(4), $user->id);

            DB::table('delivery_schedules')->insert([
                'subscription_id' => $subId,
                'delivery_order'  => 4,
                'delivery_date'   => $t5_vip_time->toDateString(),
                'items'           => json_encode([
                    ['product_id' => $pBoxoi?->id ?? 1, 'name' => 'Cải Bó Xôi Hữu Cơ', 'quantity' => 3],
                    ['product_id' => $pCachua?->id ?? 2, 'name' => 'Cà Chua Cherry Đà Lạt', 'quantity' => 1],
                ]),
                'order_id'        => $o5_vip_deliv,
                'status'          => 'delivered',
                'created_at'      => $t5_vip_time,
                'updated_at'      => $t5_vip_time,
            ]);

            DB::table('order_items')->insert([
                [
                    'order_id'     => $o5_vip_deliv,
                    'product_id'   => $pBoxoi?->id ?? 1,
                    'batch_id'     => $batch?->id ?? 1,
                    'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                    'price'        => $priceBoxoi,
                    'quantity'     => 3,
                    'subtotal'     => 0,
                    'created_at'   => $t5_vip_time,
                    'updated_at'   => $t5_vip_time,
                ],
                [
                    'order_id'     => $o5_vip_deliv,
                    'product_id'   => $pCachua?->id ?? 2,
                    'batch_id'     => $batch?->id ?? 1,
                    'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt',
                    'price'        => $priceCachua,
                    'quantity'     => 1,
                    'subtotal'     => 0,
                    'created_at'   => $t5_vip_time,
                    'updated_at'   => $t5_vip_time,
                ]
            ]);

            // 5.3 Đơn Mua Gói VIP hoàn tất (order_type = subscription, status = delivered)
            $t5_sub_time = now()->subDays(10);
            $o5_vip_sub = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'VIP-' . $prefix . '-SUB-ACTIVE',
                'order_type'           => 'subscription',
                'subtotal'             => 1200000,
                'shipping_fee'         => 0,
                'total'                => 1200000,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'delivered',
                'note'                 => 'Đăng ký thành công Gói VIP Rau Sạch Hữu Cơ 1 Tháng (4 đợt giao)',
                'created_at'           => $t5_sub_time,
                'updated_at'           => $t5_sub_time->copy()->addMinutes(15),
            ]);
            // ĐẦY ĐỦ LỊCH SỬ: pending -> processing -> delivered
            $addHistory($o5_vip_sub, 'pending', null, 'Đăng ký Gói Hội Viên Rau VIP 1 Tháng.', $t5_sub_time, $user->id);
            $addHistory($o5_vip_sub, 'processing', 'pending', 'Bộ phận chăm sóc khách hàng VIP tiếp nhận hồ sơ đăng ký.', $t5_sub_time->copy()->addMinutes(10), $sales?->id ?? 1);
            $addHistory($o5_vip_sub, 'delivered', 'processing', 'Thanh toán PayOS thành công & Kích hoạt chính thức Hợp đồng Hội Viên VIP.', $t5_sub_time->copy()->addMinutes(15), $sales?->id ?? 1);

            DB::table('order_items')->insert([
                'order_id'     => $o5_vip_sub,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => 'Hợp Đồng Gói VIP Rau Sạch Hữu Cơ 1 Tháng',
                'price'        => 1200000,
                'quantity'     => 1,
                'subtotal'     => 1200000,
                'created_at'   => $t5_sub_time,
                'updated_at'   => $t5_sub_time,
            ]);

            // Cập nhật trạng thái hợp đồng Gói VIP
            DB::table('subscriptions')->where('id', $subId)->update([
                'delivered_count' => 1,
                'status'          => 'active',
                'updated_at'      => $t5_sub_time,
            ]);


            // =================================================================
            // TAB 6: ĐÃ HỦY (cancelled: status = cancelled, cancelled_by = customer)
            // =================================================================
            // 6.1 Đơn thường bán lẻ khách tự hủy khi chờ thanh toán
            $t6_time = now()->subDays(3);
            $t6_sub = $priceBoxoi * 2;
            $o6_retail = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-CANCEL-01',
                'order_type'           => 'retail',
                'subtotal'             => $t6_sub,
                'shipping_distance_km' => 2.0,
                'shipping_fee'         => 15000,
                'total'                => $t6_sub + 15000,
                'payment_method'       => 'payos',
                'payment_status'       => 'pending',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'cancelled',
                'cancelled_by'         => 'customer',
                'cancel_reason'        => 'Khách đổi ý muốn đặt lại loại rau củ khác',
                'cancelled_at'         => $t6_time->copy()->addMinutes(15),
                'created_at'           => $t6_time,
                'updated_at'           => $t6_time->copy()->addMinutes(15),
            ]);
            $addHistory($o6_retail, 'pending', null, 'Khách hàng tạo đơn hàng.', $t6_time, $user->id);
            $addHistory($o6_retail, 'cancelled', 'pending', 'Khách hàng tự bấm hủy đơn. Lý do: Khách đổi ý muốn đặt lại loại rau củ khác.', $t6_time->copy()->addMinutes(15), $user->id);

            DB::table('order_items')->insert([
                'order_id'     => $o6_retail,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                'price'        => $priceBoxoi,
                'quantity'     => 2,
                'subtotal'     => $t6_sub,
                'created_at'   => $t6_time,
                'updated_at'   => $t6_time,
            ]);

            // 6.2 Đơn VIP khách chủ động hủy
            $t6_vip_time = now()->subDays(4);
            $o6_vip = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'VIP-' . $prefix . '-CANCEL-02',
                'order_type'           => 'subscription',
                'subtotal'             => 1200000,
                'shipping_fee'         => 0,
                'total'                => 1200000,
                'payment_method'       => 'payos',
                'payment_status'       => 'pending',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'cancelled',
                'cancelled_by'         => 'customer',
                'cancel_reason'        => 'Khách bận công tác dài ngày nên chủ động hủy đăng ký gói',
                'cancelled_at'         => $t6_vip_time->copy()->addMinutes(20),
                'created_at'           => $t6_vip_time,
                'updated_at'           => $t6_vip_time->copy()->addMinutes(20),
            ]);
            $addHistory($o6_vip, 'pending', null, 'Khách hàng gửi yêu cầu đăng ký Gói VIP.', $t6_vip_time, $user->id);
            $addHistory($o6_vip, 'cancelled', 'pending', 'Khách hàng chủ động bấm hủy đơn đăng ký gói VIP.', $t6_vip_time->copy()->addMinutes(20), $user->id);

            DB::table('order_items')->insert([
                'order_id'     => $o6_vip,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => 'Gói VIP Rau Sạch Hữu Cơ 1 Tháng',
                'price'        => 1200000,
                'quantity'     => 1,
                'subtotal'     => 1200000,
                'created_at'   => $t6_vip_time,
                'updated_at'   => $t6_vip_time,
            ]);

            // 6.3 Đơn thường bán lẻ thứ 2: đã sang processing, khách liên hệ hủy
            $t6_time3 = now()->subDays(2);
            $t6_sub3 = $priceCachua * 2;
            $o6_retail_3 = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-CANCEL-03',
                'order_type'           => 'retail',
                'subtotal'             => $t6_sub3,
                'shipping_distance_km' => 2.5,
                'shipping_fee'         => 15000,
                'total'                => $t6_sub3 + 15000,
                'payment_method'       => 'cod',
                'payment_status'       => 'pending',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'cancelled',
                'cancelled_by'         => 'customer',
                'cancel_reason'        => 'Đặt trùng đơn hàng, nhờ shop hủy bớt 1 đơn',
                'cancelled_at'         => $t6_time3->copy()->addMinutes(25),
                'created_at'           => $t6_time3,
                'updated_at'           => $t6_time3->copy()->addMinutes(25),
            ]);
            // Đầy đủ lịch sử: pending -> processing -> cancelled
            $addHistory($o6_retail_3, 'pending', null, 'Khách hàng tạo đơn hàng COD.', $t6_time3, $user->id);
            $addHistory($o6_retail_3, 'processing', 'pending', 'Nhà vườn xác nhận đơn hàng và chuẩn bị hái rau.', $t6_time3->copy()->addMinutes(10), $sales?->id ?? 1);
            $addHistory($o6_retail_3, 'cancelled', 'processing', 'Khách hàng bấm hủy đơn. Lý do: Đặt trùng đơn hàng, nhờ shop hủy bớt 1 đơn.', $t6_time3->copy()->addMinutes(25), $user->id);

            DB::table('order_items')->insert([
                'order_id'     => $o6_retail_3,
                'product_id'   => $pCachua?->id ?? 2,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt',
                'price'        => $priceCachua,
                'quantity'     => 2,
                'subtotal'     => $t6_sub3,
                'created_at'   => $t6_time3,
                'updated_at'   => $t6_time3,
            ]);

            // 6.4 Đơn thường bán lẻ thứ 3: NHÂN VIÊN NÔNG TRẠI CHỦ ĐỘNG HỦY (do luống rau không đạt chuẩn thu hoạch)
            $t6_time4 = now()->subDays(1);
            $t6_sub4 = $priceBoxoi * 2;
            $o6_retail_4 = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-CANCEL-04',
                'order_type'           => 'retail',
                'subtotal'             => $t6_sub4,
                'shipping_distance_km' => 2.0,
                'shipping_fee'         => 15000,
                'total'                => $t6_sub4 + 15000,
                'payment_method'       => 'cod',
                'payment_status'       => 'pending',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'cancelled',
                'cancelled_by'         => 'sales',
                'cancel_reason'        => 'Nông sản tại luống không đạt chuẩn VietGAP, nhân viên nông trại chủ động hủy đơn',
                'cancelled_at'         => $t6_time4,
                'created_at'           => $t6_time4->copy()->subHours(2),
                'updated_at'           => $t6_time4,
            ]);
            $addHistory($o6_retail_4, 'pending', null, 'Khách hàng tạo đơn hàng COD.', $t6_time4->copy()->subHours(2), $user->id);
            $addHistory($o6_retail_4, 'cancelled', 'pending', 'Nhân viên Nông trại Đất Lành liên hệ thông báo hủy đơn do luống rau không đạt tiêu chuẩn thu hoạch.', $t6_time4, $sales?->id ?? 1);

            DB::table('order_items')->insert([
                'order_id'     => $o6_retail_4,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                'price'        => $priceBoxoi,
                'quantity'     => 2,
                'subtotal'     => $t6_sub4,
                'created_at'   => $t6_time4->copy()->subHours(2),
                'updated_at'   => $t6_time4->copy()->subHours(2),
            ]);

            // =================================================================
            // TAB 7: TRẢ HÀNG / HOÀN TIỀN (refund: đơn delivered có complaint)
            // =================================================================
            // 7.1 Đơn thường bán lẻ - Đã giao thành công + Có KHIẾU NẠI DẬP NÁT ĐANG CHỜ DUYỆT
            $t7_time1 = now()->subDays(1);
            $t7_sub1 = $priceCachua * 2;
            $o7_complaint_pending = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-REFUND-PENDING',
                'order_type'           => 'retail',
                'subtotal'             => $t7_sub1,
                'shipping_distance_km' => 2.8,
                'shipping_fee'         => 15000,
                'total'                => $t7_sub1 + 15000,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove',
                'tracking_code'        => 'AHA-' . $prefix . '-8811',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'delivered',
                'note'                 => 'Hộp cà chua bị móp, khách đã gửi khiếu nại',
                'created_at'           => $t7_time1,
                'updated_at'           => $t7_time1->copy()->addHours(3),
            ]);
            // ĐẦY ĐỦ LỊCH SỬ TUẦN TỰ: pending -> processing -> shipped -> delivered
            $addHistory($o7_complaint_pending, 'pending', null, 'Khách đặt đơn và thanh toán PayOS.', $t7_time1, $user->id);
            $addHistory($o7_complaint_pending, 'processing', 'pending', 'Nông trại đóng gói đơn hàng.', $t7_time1->copy()->addHours(1), $sales?->id ?? 1);
            $addHistory($o7_complaint_pending, 'shipped', 'processing', 'Shipper đang giao hàng.', $t7_time1->copy()->addHours(2), $sales?->id ?? 1);
            $addHistory($o7_complaint_pending, 'delivered', 'shipped', 'Khách hàng nhận hàng và gửi khiếu nại dập nát, yêu cầu hoàn tiền.', $t7_time1->copy()->addHours(3), $user->id);

            DB::table('order_items')->insert([
                'order_id'     => $o7_complaint_pending,
                'product_id'   => $pCachua?->id ?? 2,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt',
                'price'        => $priceCachua,
                'quantity'     => 2,
                'subtotal'     => $t7_sub1,
                'created_at'   => $t7_time1,
                'updated_at'   => $t7_time1,
            ]);

            // Bản ghi khiếu nại đang chờ duyệt
            $compId1 = DB::table('complaints')->insertGetId([
                'user_id'             => $user->id,
                'order_id'            => $o7_complaint_pending,
                'refund_amount'       => $priceCachua,
                'reason'              => 'Hộp cà chua cherry bị móp đáy khi giao, có 4 quả bị dập nát chảy nước.',
                'status'              => 'pending',
                'bank_name'           => 'MBBank',
                'bank_account_number' => $phone,
                'bank_account_name'   => mb_strtoupper($user->name),
                'created_at'          => $t7_time1->copy()->addHours(3),
                'updated_at'          => $t7_time1->copy()->addHours(3),
            ]);

            DB::table('complaint_images')->insert([
                'complaint_id' => $compId1,
                'image'        => 'https://images.unsplash.com/photo-1592282139728-28040d96a922?w=500',
                'created_at'   => $t7_time1->copy()->addHours(3),
                'updated_at'   => $t7_time1->copy()->addHours(3),
            ]);

            // 7.2 Đơn thường bán lẻ - Đã giao thành công + KHIẾU NẠI ĐÃ ĐƯỢC DUYỆT THÀNH CÔNG VÀ HOÀN TIỀN
            $t7_res_time = now()->subDays(2);
            $t7_sub_res = $priceBoxoi * 2;
            $o7_complaint_resolved = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-REFUND-APPROVED',
                'order_type'           => 'retail',
                'subtotal'             => $t7_sub_res,
                'shipping_distance_km' => 3.2,
                'shipping_fee'         => 15000,
                'total'                => $t7_sub_res + 15000,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove',
                'tracking_code'        => 'AHA-' . $prefix . '-8899',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'delivered',
                'note'                 => 'Nông trại đã duyệt khiếu nại và hoàn tiền về MBBank',
                'created_at'           => $t7_res_time,
                'updated_at'           => $t7_res_time->copy()->addHours(6),
            ]);
            $addHistory($o7_complaint_resolved, 'pending', null, 'Khách hàng đặt đơn rau tươi và thanh toán PayOS.', $t7_res_time, $user->id);
            $addHistory($o7_complaint_resolved, 'processing', 'pending', 'Nông trại hái rau sạch và đóng thùng.', $t7_res_time->copy()->addHours(1), $sales?->id ?? 1);
            $addHistory($o7_complaint_resolved, 'shipped', 'processing', 'Shipper AhaMove giao hàng hỏa tốc.', $t7_res_time->copy()->addHours(2), $sales?->id ?? 1);
            $addHistory($o7_complaint_resolved, 'delivered', 'shipped', 'Khách hàng nhận hàng, phát hiện rau bị dập và gửi khiếu nại hoàn tiền.', $t7_res_time->copy()->addHours(3), $user->id);

            DB::table('order_items')->insert([
                'order_id'     => $o7_complaint_resolved,
                'product_id'   => $pBoxoi?->id ?? 1,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ',
                'price'        => $priceBoxoi,
                'quantity'     => 2,
                'subtotal'     => $t7_sub_res,
                'created_at'   => $t7_res_time,
                'updated_at'   => $t7_res_time,
            ]);

            $compId2 = DB::table('complaints')->insertGetId([
                'user_id'             => $user->id,
                'order_id'            => $o7_complaint_resolved,
                'refund_amount'       => $t7_sub_res,
                'reason'              => 'Túi rau cải bó xôi bị cấn đè trong thùng xốp, dập úng cuống lá không sử dụng được.',
                'status'              => 'resolved',
                'bank_name'           => 'MBBank',
                'bank_account_number' => $phone,
                'bank_account_name'   => mb_strtoupper($user->name),
                'resolved_by'         => $sales?->id ?? 1,
                'resolution_note'     => 'GreenFarm chân thành xin lỗi quý khách vì sự cố vận chuyển. Bộ phận kế toán đã chuyển khoản bồi hoàn 100% tiền rau về tài khoản MBBank của bạn.',
                'created_at'          => $t7_res_time->copy()->addHours(3),
                'updated_at'          => $t7_res_time->copy()->addHours(6),
            ]);

            DB::table('complaint_images')->insert([
                'complaint_id' => $compId2,
                'image'        => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=500',
                'created_at'   => $t7_res_time->copy()->addHours(3),
                'updated_at'   => $t7_res_time->copy()->addHours(3),
            ]);

            // 7.3 Đơn thường bán lẻ - Đã giao thành công + KHIẾU NẠI BỊ TỪ CHỐI (Có lý do từ chối rõ ràng)
            $t7_rej_time = now()->subDays(3);
            $t7_sub_rej = $priceCachua * 2;
            $o7_complaint_rejected = DB::table('orders')->insertGetId([
                'user_id'              => $user->id,
                'order_code'           => 'ORD-' . $prefix . '-REFUND-REJECTED',
                'order_type'           => 'retail',
                'subtotal'             => $t7_sub_rej,
                'shipping_distance_km' => 2.0,
                'shipping_fee'         => 15000,
                'total'                => $t7_sub_rej + 15000,
                'payment_method'       => 'payos',
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove',
                'tracking_code'        => 'AHA-' . $prefix . '-7722',
                'recipient_name'       => $user->name,
                'recipient_phone'      => $phone,
                'recipient_address'    => $address,
                'status'               => 'delivered',
                'note'                 => 'Khiếu nại không hợp lệ, nông trại từ chối',
                'created_at'           => $t7_rej_time,
                'updated_at'           => $t7_rej_time->copy()->addHours(5),
            ]);
            $addHistory($o7_complaint_rejected, 'pending', null, 'Khách hàng đặt đơn hàng.', $t7_rej_time, $user->id);
            $addHistory($o7_complaint_rejected, 'processing', 'pending', 'Nông trại chuẩn bị đơn hàng.', $t7_rej_time->copy()->addHours(1), $sales?->id ?? 1);
            $addHistory($o7_complaint_rejected, 'shipped', 'processing', 'Đang giao hàng đến khách.', $t7_rej_time->copy()->addHours(2), $sales?->id ?? 1);
            $addHistory($o7_complaint_rejected, 'delivered', 'shipped', 'Giao hàng thành công. Khách gửi khiếu nại hoàn tiền.', $t7_rej_time->copy()->addHours(3), $user->id);

            DB::table('order_items')->insert([
                'order_id'     => $o7_complaint_rejected,
                'product_id'   => $pCachua?->id ?? 2,
                'batch_id'     => $batch?->id ?? 1,
                'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt',
                'price'        => $priceCachua,
                'quantity'     => 2,
                'subtotal'     => $t7_sub_rej,
                'created_at'   => $t7_rej_time,
                'updated_at'   => $t7_rej_time,
            ]);

            $compId3 = DB::table('complaints')->insertGetId([
                'user_id'             => $user->id,
                'order_id'            => $o7_complaint_rejected,
                'refund_amount'       => $t7_sub_rej,
                'reason'              => 'Cà chua vị chua hơn đợt trước mua, yêu cầu trả hàng hoàn tiền.',
                'status'              => 'rejected',
                'bank_name'           => 'MBBank',
                'bank_account_number' => $phone,
                'bank_account_name'   => mb_strtoupper($user->name),
                'resolved_by'         => $sales?->id ?? 1,
                'resolution_note'     => 'Chính sách bảo hành nông sản chỉ áp dụng khi sản phẩm dập nát, úng hỏng vật lý hoặc giao sai quy cách trong vòng 24 giờ. Nông trại rất tiếc không thể hỗ trợ hoàn tiền đối với khẩu vị cá nhân. Cảm ơn quý khách đã đóng góp ý kiến!',
                'created_at'          => $t7_rej_time->copy()->addHours(3),
                'updated_at'          => $t7_rej_time->copy()->addHours(5),
            ]);

            DB::table('complaint_images')->insert([
                'complaint_id' => $compId3,
                'image'        => 'https://images.unsplash.com/photo-1592282139728-28040d96a922?w=500',
                'created_at'   => $t7_rej_time->copy()->addHours(3),
                'updated_at'   => $t7_rej_time->copy()->addHours(3),
            ]);



            // =================================================================
            // ĐỒNG BỘ BIÊN LAI THANH TOÁN CHO BẢNG payments (CHUẨN 1 - 1)
            // =================================================================
            $customerOrders = DB::table('orders')->where('user_id', $user->id)->get();
            foreach ($customerOrders as $cOrder) {
                $isPaid = ($cOrder->payment_status === 'paid');
                $isCancelled = ($cOrder->status === 'cancelled' && $cOrder->payment_status !== 'paid');

                $paymentStatus = $isPaid ? 'success' : ($isCancelled ? 'failed' : 'pending');
                $paidAt = $isPaid ? ($cOrder->updated_at ?? $cOrder->created_at) : null;
                $transCode = $isPaid ? ('POS-REF-' . $prefix . '-' . substr(md5($cOrder->order_code), 0, 8)) : null;

                DB::table('payments')->updateOrInsert(
                    ['order_id' => $cOrder->id],
                    [
                        'payment_code'     => 'PAY-' . $cOrder->order_code,
                        'method'           => $cOrder->payment_method ?? 'payos',
                        'amount'           => $cOrder->total,
                        'status'           => $paymentStatus,
                        'transaction_code' => $transCode,
                        'paid_at'          => $paidAt,
                        'created_at'       => $cOrder->created_at,
                        'updated_at'       => $paidAt ?? $cOrder->updated_at,
                    ]
                );
            }
        }
    }
}