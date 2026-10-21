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

        $users = User::where('role', 'customer')->get()->keyBy('email');$sales = User::where('role', 'sales')->first();

        // Helper chèn Lịch sử trạng thái đơn hàng (Audit Trail)
        $addHistory = function (int $orderId, string$newStatus, ?string $oldStatus = null, ?string $note = null, $createdAt = null, ?int $changedBy = null) use ($sales) {
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
        // 1. ĐƠN THANH TRÚC (Giao thành công + Khiếu nại Hoàn tiền + Đánh giá)
        // ---------------------------------------------------------------------
        $u1 =$users->get('thanhtruc@example.com') ?? User::where('role', 'customer')->first();
        if ($u1) {$createdAtO1 = now()->subDays(5);

            $priceBoxoi  =$pBoxoi?->price ?? 45000;
            $priceCachua =$pCachua?->price ?? 65000;
            $subtotalO1  = ($priceBoxoi * 2) + ($priceCachua * 1);$shipFeeO1   = 20000;
            $totalO1     = $subtotalO1 +$shipFeeO1 - 10000; // Trừ điểm thưởng

            // Đơn giao thành công gốc (status = delivered)
            $o1 = DB::table('orders')->insertGetId([
                'user_id'              => $u1->id, 
                'order_code'           => 'ORD-20261012-001', 
                'order_type'           => 'retail',
                'subtotal'             => $subtotalO1, 
                'shipping_distance_km' => 4.5, 
                'shipping_fee'         => $shipFeeO1,
                'points_used'          => 10, 
                'points_earned'        => 15, 
                'total'                => $totalO1,
                'payment_method'       => 'vnpay', 
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove', 
                'tracking_code'        => 'AHA-99882211',
                'recipient_name'       => $u1->name, 
                'recipient_phone'      => $u1->phone ?? '0909123456',
                'recipient_address'    => '123 Nguyễn Huệ, P. Bến Nghé, Q.1, TP.HCM',
                'status'               => 'delivered', 
                'bank_name'            => $u1->default_bank_name ?? 'MBBank',
                'bank_account_number'  => $u1->default_bank_account_number ?? '0909123456',
                'bank_account_name'    => $u1->default_bank_account_name ?? 'LE THANH TRUC',
                'refund_status'        => 'refunded',
                'refunded_at'          => now()->subDays(2),
                'note'                 => 'Giao trước 11h sáng giúp em', 
                'created_at'           => $createdAtO1,
                'updated_at'           => $createdAtO1->copy()->addHours(4),
            ]);

            // Lịch sử trạng thái
            $addHistory($o1, 'pending', null, 'Khách hàng đặt đơn & thanh toán thành công qua VNPAY', $createdAtO1,$u1->id);
            $addHistory($o1, 'processing', 'pending', 'Nông trại đã xác nhận đơn & đóng gói theo lô FEFO', $createdAtO1->copy()->addMinutes(15),$sales?->id);
            $addHistory($o1, 'shipped', 'processing', 'Đã bàn giao cho tài xế Ahamove (Mã: AHA-99882211)', $createdAtO1->copy()->addHours(2),$sales?->id);
            $addHistory($o1, 'delivered', 'shipped', 'Khách hàng đã nhận hàng thành công', $createdAtO1->copy()->addHours(4),$u1->id);

            // Sản phẩm trong đơn
            $oi1 = DB::table('order_items')->insertGetId([
                'order_id'     => $o1, 
                'product_id'   => $pBoxoi?->id ?? 1, 
                'batch_id'     => $batch?->id ?? 1, 
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ', 
                'price'        => $priceBoxoi, 
                'quantity'     => 2, 
                'subtotal'     => $priceBoxoi * 2,
                'created_at'   =>$createdAtO1,
                'updated_at'   => $createdAtO1,
            ]);
            
            DB::table('order_items')->insert([
                'order_id'     => $o1, 
                'product_id'   => $pCachua?->id ?? 2, 
                'batch_id'     => $batch?->id ?? 1, 
                'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt', 
                'price'        => $priceCachua, 
                'quantity'     => 1, 
                'subtotal'     => $priceCachua * 1,
                'created_at'   =>$createdAtO1,
                'updated_at'   => $createdAtO1,
            ]);

            DB::table('payments')->insert([
                'order_id'         => $o1, 
                'payment_code'     => 'PAY-VNP-1001', 
                'method'           => 'vnpay', 
                'amount'           => $totalO1, 
                'status'           => 'success', 
                'transaction_code' => 'VNP141209348', 
                'paid_at'          => $createdAtO1,
                'created_at'       => $createdAtO1,
                'updated_at'       => $createdAtO1,
            ]);

            // Khiếu nại HOÀN TIỀN
            $comp = DB::table('complaints')->insertGetId([
                'user_id'         => $u1->id, 
                'order_id'        => $o1, 
                'refund_amount'   => $priceCachua,
                'reason'          => 'Hộp cà chua bị móp ở đáy, có 3 quả bị nứt chảy nước.',
                'status'          => 'resolved', 
                'resolved_by'     => $sales?->id ?? 3, 
                'resolution_note' => 'Đã thẩm định hình ảnh dập nát. Duyệt hoàn tiền ' . number_format($priceCachua) . 'đ về tài khoản ngân hàng khách hàng.', 
                'created_at'      => now()->subDays(3),
                'updated_at'      => now()->subDays(2),
            ]);
            
            DB::table('complaint_images')->insert([
                'complaint_id' => $comp, 
                'image'        => 'https://images.unsplash.com/photo-1592282139728-28040d96a922?w=500',
                'created_at'   => now()->subDays(3),
                'updated_at'   => now()->subDays(3),
            ]);

            // Đánh giá 5 sao
            $rev = DB::table('reviews')->insertGetId([
                'user_id'       => $u1->id, 
                'product_id'    => $pBoxoi?->id ?? 1, 
                'order_item_id' => $oi1,
                'rating'        => 5, 
                'content'       => 'Rau cải bó xôi rất tươi giòn, lá xanh đậm không sâu dập. Quét QR ra đúng lô thu hoạch sáng sớm luôn!', 
                'status'        => 'approved',
                'created_at'    => now()->subDays(1),
                'updated_at'    => now()->subDays(1),
            ]);
            
            DB::table('review_images')->insert([
                'review_id'  => $rev, 
                'image'      => 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=500', 
                'sort_order' => 1,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ]);
        }

        // ---------------------------------------------------------------------
        // 2. USER THỨ 4 ($u4): ĐỦ CÁC TRẠNG THÁI CHO ĐƠN LẺ & ĐƠN VIP
        // ---------------------------------------------------------------------
        $u4 = User::where('role', 'customer')->skip(3)->first() 
              ?? User::find(4) 
              ?? User::where('role', 'customer')->latest()->first();

        if ($u4) {$addressU4   = '789 Điện Biên Phủ, Phường 11, Quận 10, TP.HCM';
            $phoneU4     =$u4->phone ?? '0933445566';
            $priceBoxoi  =$pBoxoi?->price ?? 45000;
            $priceCachua =$pCachua?->price ?? 65000;

            // ==========================================
            // A. ĐƠN BÁN LẺ (order_type = 'retail')
            // ==========================================

            // A1. pending (Chờ thanh toán)
            $timeA1 = now()->subMinutes(15);
            $subA1  =$priceBoxoi * 2;
            $oR1 = DB::table('orders')->insertGetId([
                'user_id'              => $u4->id, 
                'order_code'           => 'ORD-20261020-010', 
                'order_type'           => 'retail',
                'subtotal'             => $subA1, 
                'shipping_distance_km' => 3.2, 
                'shipping_fee'         => 18000,
                'points_used'          => 0, 
                'points_earned'        => 9, 
                'total'                => $subA1 + 18000,
                'payment_method'       => 'payos', 
                'payment_status'       => 'pending',
                'shipping_provider'    => 'Ahamove', 
                'recipient_name'       => $u4->name, 
                'recipient_phone'      => $phoneU4, 
                'recipient_address'    => $addressU4,
                'status'               => 'pending', 
                'note'                 => 'Thanh toán qua QR PayOS', 
                'created_at'           => $timeA1,
                'updated_at'           => $timeA1,
            ]);
            $addHistory($oR1, 'pending', null, 'Khách hàng tạo đơn qua PayOS, đang chờ thanh toán', $timeA1,$u4->id);

            DB::table('order_items')->insert([
                'order_id'     => $oR1, 
                'product_id'   => $pBoxoi?->id ?? 1, 
                'batch_id'     => $batch?->id ?? 1, 
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ', 
                'price'        => $priceBoxoi, 
                'quantity'     => 2, 
                'subtotal'     => $subA1,
                'created_at'   => $timeA1,
                'updated_at'   => $timeA1,
            ]);

            // A2. processing (Chờ lấy hàng)
            $timeA2 = now()->subHours(3);
            $subA2  =$priceBoxoi * 4;
            $oR2 = DB::table('orders')->insertGetId([
                'user_id'              => $u4->id, 
                'order_code'           => 'ORD-20261020-012', 
                'order_type'           => 'retail',
                'subtotal'             => $subA2, 
                'shipping_distance_km' => 2.5, 
                'shipping_fee'         => 15000,
                'points_used'          => 0, 
                'points_earned'        => 18, 
                'total'                => $subA2 + 15000,
                'payment_method'       => 'vnpay', 
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove', 
                'tracking_code'        => 'AHA-55443322',
                'recipient_name'       => $u4->name, 
                'recipient_phone'      => $phoneU4, 
                'recipient_address'    => $addressU4,
                'status'               => 'processing', 
                'note'                 => 'Nhân viên kho đang đóng gói theo FEFO', 
                'created_at'           => $timeA2,
                'updated_at'           => $timeA2->copy()->addMinutes(20),
            ]);
            $addHistory($oR2, 'pending', null, 'Thanh toán VNPAY thành công', $timeA2,$u4->id);
            $addHistory($oR2, 'processing', 'pending', 'Shop đã xác nhận, nhân viên kho đang đóng gói theo lô FEFO', $timeA2->copy()->addMinutes(20),$sales?->id);

            DB::table('order_items')->insert([
                'order_id'     => $oR2, 
                'product_id'   => $pBoxoi?->id ?? 1, 
                'batch_id'     => $batch?->id ?? 1, 
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ', 
                'price'        => $priceBoxoi, 
                'quantity'     => 4, 
                'subtotal'     => $subA2,
                'created_at'   => $timeA2,
                'updated_at'   => $timeA2,
            ]);

            // A3. shipped (Đang giao hàng)
            $timeA3 = now()->subMinutes(40);
            $subA3  =$priceCachua * 2;
            $oR3 = DB::table('orders')->insertGetId([
                'user_id'              => $u4->id, 
                'order_code'           => 'ORD-20261020-013', 
                'order_type'           => 'retail',
                'subtotal'             => $subA3, 
                'shipping_distance_km' => 4.1, 
                'shipping_fee'         => 20000,
                'points_used'          => 0, 
                'points_earned'        => 13, 
                'total'                => $subA3 + 20000,
                'payment_method'       => 'vnpay', 
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove', 
                'tracking_code'        => 'AHA-77889900',
                'recipient_name'       => $u4->name, 
                'recipient_phone'      => $phoneU4, 
                'recipient_address'    => $addressU4,
                'status'               => 'shipped', 
                'note'                 => 'Tài xế Ahamove đang giao hàng', 
                'created_at'           => $timeA3->copy()->subHours(2),
                'updated_at'           =>$timeA3,
            ]);
            $addHistory($oR3, 'pending', null, 'Đặt hàng thành công qua VNPAY', $timeA3->copy()->subHours(2),$u4->id);
            $addHistory($oR3, 'processing', 'pending', 'Shop đã đóng gói xong', $timeA3->copy()->subHours(1),$sales?->id);
            $addHistory($oR3, 'shipped', 'processing', 'Bàn giao tài xế Ahamove giao hàng', $timeA3,$sales?->id);

            DB::table('order_items')->insert([
                'order_id'     => $oR3, 
                'product_id'   => $pCachua?->id ?? 2, 
                'batch_id'     => $batch?->id ?? 1, 
                'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt', 
                'price'        => $priceCachua, 
                'quantity'     => 2, 
                'subtotal'     => $subA3,
                'created_at'   => $timeA3->copy()->subHours(2),
                'updated_at'   =>$timeA3->copy()->subHours(2),
            ]);

            // A4. delivered (Đã giao thành công)
            $timeA4 = now()->subDays(2);
            $subA4  =$priceBoxoi * 5;
            $oR4 = DB::table('orders')->insertGetId([
                'user_id'              => $u4->id, 
                'order_code'           => 'ORD-20261018-014', 
                'order_type'           => 'retail',
                'subtotal'             => $subA4, 
                'shipping_distance_km' => 3.8, 
                'shipping_fee'         => 18000,
                'points_used'          => 0, 
                'points_earned'        => 22, 
                'total'                => $subA4 + 18000,
                'payment_method'       => 'vnpay', 
                'payment_status'       => 'paid',
                'shipping_provider'    => 'Ahamove', 
                'tracking_code'        => 'AHA-00112233',
                'recipient_name'       => $u4->name, 
                'recipient_phone'      => $phoneU4, 
                'recipient_address'    => $addressU4,
                'status'               => 'delivered', 
                'note'                 => 'Khách đã nhận hàng thành công', 
                'created_at'           => $timeA4,
                'updated_at'           => $timeA4->copy()->addHours(3),
            ]);
            $addHistory($oR4, 'pending', null, 'Khách hàng tạo đơn', $timeA4,$u4->id);
            $addHistory($oR4, 'processing', 'pending', 'Nông trại đóng gói sản phẩm', $timeA4->copy()->addHour(),$sales?->id);
            $addHistory($oR4, 'shipped', 'processing', 'Shipper nhận hàng giao hỏa tốc', $timeA4->copy()->addHours(2),$sales?->id);
            $addHistory($oR4, 'delivered', 'shipped', 'Giao hàng thành công', $timeA4->copy()->addHours(3),$u4->id);

            DB::table('order_items')->insert([
                'order_id'     => $oR4, 
                'product_id'   => $pBoxoi?->id ?? 1, 
                'batch_id'     => $batch?->id ?? 1, 
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ', 
                'price'        => $priceBoxoi, 
                'quantity'     => 5, 
                'subtotal'     => $subA4,
                'created_at'   => $timeA4,
                'updated_at'   => $timeA4,
            ]);

            // A5. cancelled (Đã hủy & Hoàn tiền STK)
            $timeA5 = now()->subDays(3);
            $subA5  =$priceBoxoi * 2;
            $oR5 = DB::table('orders')->insertGetId([
                'user_id'              => $u4->id, 
                'order_code'           => 'ORD-20261017-015', 
                'order_type'           => 'retail',
                'subtotal'             => $subA5, 
                'shipping_fee'         => 15000,
                'total'                => $subA5 + 15000,
                'payment_method'       => 'vnpay', 
                'payment_status'       => 'paid',
                'status'               => 'cancelled', 
                'cancel_reason'        => 'Khách bận đột xuất không thể nhận hàng', 
                'cancelled_at'         => $timeA5->copy()->addHours(1), 
                'cancelled_by'         => 'customer',
                'bank_name'            => 'MBBank', 
                'bank_account_number'  => '0933445566', 
                'bank_account_name'    => 'DO QUOC BAO', 
                'refund_status'        => 'refunded', 
                'refunded_at'          => $timeA5->copy()->addDays(1),
                'recipient_name'       =>$u4->name, 
                'recipient_phone'      => $phoneU4, 
                'recipient_address'    => $addressU4,
                'created_at'           => $timeA5,
                'updated_at'           => $timeA5->copy()->addDays(1),
            ]);
            $addHistory($oR5, 'pending', null, 'Khách đặt hàng qua VNPAY', $timeA5,$u4->id);
            $addHistory($oR5, 'cancelled', 'pending', 'Khách hàng bận đột xuất tự hủy đơn - Yêu cầu hoàn tiền về MBBank', $timeA5->copy()->addHours(1),$u4->id);

            DB::table('order_items')->insert([
                'order_id'     => $oR5, 
                'product_id'   => $pBoxoi?->id ?? 1, 
                'batch_id'     => $batch?->id ?? 1, 
                'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ', 
                'price'        => $priceBoxoi, 
                'quantity'     => 2, 
                'subtotal'     => $subA5,
                'created_at'   => $timeA5,
                'updated_at'   => $timeA5,
            ]);

            // =========================================================================
            // B. ĐƠN MUA GÓI VIP (`order_type = 'subscription'`) 
            // Không có order_items! Đã thanh toán thành công 1.200.000đ
            // =========================================================================
            $timeVipPay = now()->subDays(10);
            $oVipPay = DB::table('orders')->insertGetId([
                'user_id'           => $u4->id, 
                'order_code'        => 'VIP-SUB-20261001', 
                'order_type'        => 'subscription', 
                'subtotal'          => 1200000, 
                'shipping_fee'      => 0,
                'total'             => 1200000,
                'payment_method'    => 'payos', 
                'payment_status'    => 'paid',
                'recipient_name'    => $u4->name, 
                'recipient_phone'   => $phoneU4, 
                'recipient_address' => $addressU4,
                'status'            => 'delivered', // Đã hoàn tất kích hoạt gói
                'note'              => 'Đăng ký Gói VIP Rau Sạch Hữu Cơ 1 Tháng (4 kỳ)', 
                'created_at'        => $timeVipPay,
                'updated_at'        => $timeVipPay,
            ]);
            $addHistory($oVipPay, 'delivered', 'pending', 'Thanh toán gói VIP thành công & Kích hoạt gói', $timeVipPay);

            // Kích hoạt bản ghi trong bảng subscriptions
            DB::table('subscriptions')->updateOrInsert(
                ['user_id' => $u4->id],
                [
                    'recipient_name'    => $u4->name,
                    'recipient_phone'   => $phoneU4,
                    'recipient_address' => $addressU4,
                    'delivery_days'     => 'T3, T6',
                    'total_price'       => 1200000,
                    'total_deliveries'  => 4,
                    'delivered_count'   => 1,
                    'status'            => 'active',
                    'created_at'        => $timeVipPay,
                    'updated_at'        => $timeVipPay,
                ]
            );

            // Đơn VIP nháp quá hạn 15 phút bị system tự hủy (Sẽ bị ẩn khỏi giao diện khách)
            $timeVipSystemCancel = now()->subDays(12);
            $oVipSys = DB::table('orders')->insertGetId([
                'user_id'           => $u4->id, 
                'order_code'        => 'VIP-SUB-CANCELLED', 
                'order_type'        => 'subscription', 
                'subtotal'          => 1200000, 
                'total'             => 1200000,
                'payment_method'    => 'payos', 
                'payment_status'    => 'pending',
                'recipient_name'    => $u4->name, 
                'recipient_phone'   => $phoneU4, 
                'recipient_address' => $addressU4,
                'status'            => 'cancelled', 
                'cancelled_by'      => 'system',
                'cancel_reason'     => 'Quá hạn 15 phút không quét mã QR thanh toán',
                'created_at'        => $timeVipSystemCancel,
                'updated_at'        => $timeVipSystemCancel->copy()->addMinutes(15),
            ]);
            $addHistory($oVipSys, 'cancelled', 'pending', 'Hệ thống tự động hủy đơn nháp quá hạn 15p', $timeVipSystemCancel->copy()->addMinutes(15));

            // =========================================================================
            // C. ĐƠN GIAO RAU VIP THEO ĐỢT (`order_type = 'subscription_delivery'`)
            // Tới ngày giao -> Nằm ở Tab "Chờ xác nhận" (`status = pending`, `payment_status = paid`, total = 0đ) -> CÓ order_items
            // =========================================================================
            $timeWave2 = now()->subHours(2);
            $oWave2 = DB::table('orders')->insertGetId([
                'user_id'           => $u4->id, 
                'order_code'        => 'VIP-DELIVERY-K2', 
                'order_type'        => 'subscription_delivery', 
                'subtotal'          => 0, 
                'shipping_fee'      => 0,
                'total'             => 0, 
                'payment_method'    => 'vnpay', 
                'payment_status'    => 'paid',
                'shipping_provider' => 'Ahamove', 
                'recipient_name'    => $u4->name, 
                'recipient_phone'   => $phoneU4, 
                'recipient_address' => $addressU4,
                'status'            => 'pending', // Hiện ở Tab Chờ xác nhận
                'note'              => 'Giao rau VIP Đợt 2/4', 
                'created_at'        => $timeWave2,
                'updated_at'        => $timeWave2,
            ]);
            $addHistory($oWave2, 'pending', null, 'Khởi tạo đợt giao 2/4 từ lịch delivery_schedules', $timeWave2);

            // Các loại rau củ khách đã chọn cho đợt 2 này
            DB::table('order_items')->insert([
                [
                    'order_id'     => $oWave2, 
                    'product_id'   => $pBoxoi?->id ?? 1, 
                    'batch_id'     => $batch?->id ?? 1, 
                    'product_name' => $pBoxoi?->name ?? 'Cải Bó Xôi Hữu Cơ', 
                    'price'        => $priceBoxoi, 
                    'quantity'     => 2, 
                    'subtotal'     => 0, 
                    'created_at'   => $timeWave2,
                    'updated_at'   => $timeWave2,
                ],
                [
                    'order_id'     => $oWave2, 
                    'product_id'   => $pCachua?->id ?? 2, 
                    'batch_id'     => $batch?->id ?? 1, 
                    'product_name' => $pCachua?->name ?? 'Cà Chua Cherry Đà Lạt', 
                    'price'        => $priceCachua, 
                    'quantity'     => 1, 
                    'subtotal'     => 0,
                    'created_at'   => $timeWave2,
                    'updated_at'   => $timeWave2,
                ]
            ]);
        }
    }
}