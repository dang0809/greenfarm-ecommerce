<?php

namespace App\Services;

use App\Mail\OrderNotificationMail;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    /**
     * Thông báo khi thanh toán thành công (đặt hàng thành công)
     */
    public static function notifyOrderPaid(Order $order): void
    {
        try {
            if (!$order->user_id) {
                return;
            }

            // 1. Chống trùng lặp (nếu Webhook PayOS retry nhiều lần)
            $alreadyNotified = Notification::where('user_id', $order->user_id)
                ->where('type', 'order_paid')
                ->whereJsonContains('data->order_id', $order->id)
                ->exists();

            if ($alreadyNotified) {
                return;
            }

            // 2. Ghi thông báo Web vào bảng notifications (hiển thị quả chuông Header)
            Notification::create([
                'user_id' => $order->user_id,
                'type'    => 'order_paid',
                'title'   => '🛒 Đơn hàng #' . $order->order_code . ' đã thanh toán thành công',
                'content' => 'Đơn hàng #' . $order->order_code . ' trị giá ' . number_format($order->total, 0, ',', '.') . 'đ đã thanh toán thành công qua PayOS. Nông trại đang chuẩn bị nông sản cho bạn.',
                'data'    => [
                    'order_id'   => $order->id,
                    'order_code' => $order->order_code,
                    'icon'       => 'fa-receipt',
                    'url'        => '/ho-so?tab=orders',
                ],
            ]);

            // 3. Gửi Email biên nhận chi tiết
            $user = $order->user;
            if ($user && !empty($user->email)) {
                Mail::to($user->email)->send(new OrderNotificationMail($order, 'paid'));
            }
        } catch (\Exception $e) {
            Log::error('Order Notification (Paid) Error: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
            ]);
        }
    }

    /**
     * Thông báo khi giao hàng thành công
     */
    public static function notifyOrderDelivered(Order $order): void
    {
        try {
            if (!$order->user_id) {
                return;
            }

            // 1. Chống trùng lặp
            $alreadyNotified = Notification::where('user_id', $order->user_id)
                ->where('type', 'order_delivered')
                ->whereJsonContains('data->order_id', $order->id)
                ->exists();

            if ($alreadyNotified) {
                return;
            }

            // 2. CỘNG ĐIỂM THƯỞNG KHI ĐƠN HÀNG HOÀN THÀNH (Đã giao thành công)
            if ($order->points_earned > 0) {
                User::where('id', $order->user_id)->increment('reward_points', $order->points_earned);
            }

            // 3. Ghi thông báo Web vào bảng notifications (hiển thị quả chuông Header)
            $pointsNote = $order->points_earned > 0 ? " Bạn được tích lũy +{$order->points_earned} điểm thưởng!" : '';
            Notification::create([
                'user_id' => $order->user_id,
                'type'    => 'order_delivered',
                'title'   => '🎉 Giao hàng thành công đơn #' . $order->order_code,
                'content' => 'Đơn hàng #' . $order->order_code . ' đã được giao thành công.' . $pointsNote . ' Chúc bạn có những bữa ăn tươi ngon và an lành!',
                'data'    => [
                    'order_id'      => $order->id,
                    'order_code'    => $order->order_code,
                    'points_earned' => $order->points_earned,
                    'icon'          => 'fa-circle-check',
                    'url'           => '/ho-so?tab=orders',
                ],
            ]);

            // 3. Gửi Email thông báo đã nhận hàng & bảo hiểm 24H
            $user = $order->user;
            if ($user && !empty($user->email)) {
                Mail::to($user->email)->send(new OrderNotificationMail($order, 'delivered'));
            }
        } catch (\Exception $e) {
            Log::error('Order Notification (Delivered) Error: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
            ]);
        }
    }
}
