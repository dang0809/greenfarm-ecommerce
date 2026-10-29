<?php

namespace App\Services;

use App\Mail\VipSubscriptionMail;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VipNotificationService
{
    /**
     * Gửi thông báo khi khách thanh toán Gói Rau VIP thành công
     * Trigger: PayOS Webhook xác nhận order_type = 'subscription'
     */
    public static function notifyVipActivated(Subscription $subscription, Order $order): void
    {
        try {
            // 1. Chống trùng lặp: Webhook PayOS có thể retry nhiều lần
            $alreadyNotified = Notification::where('user_id', $subscription->user_id)
                ->where('type', 'vip_activated')
                ->whereJsonContains('data->subscription_id', $subscription->id)
                ->exists();

            if ($alreadyNotified) {
                return;
            }

            // 2. Ghi thông báo Web vào bảng notifications (hiển thị quả chuông Header)
            Notification::create([
                'user_id' => $subscription->user_id,
                'type'    => 'vip_activated',
                'title'   => '👑 Gói Rau VIP đã kích hoạt thành công!',
                'content' => 'Thanh toán ' . number_format($order->total, 0, ',', '.') . 'đ thành công. Vui lòng thiết lập lịch nhận rau và thực đơn cho 8 đợt giao.',
                'data'    => [
                    'subscription_id' => $subscription->id,
                    'order_id'        => $order->id,
                    'order_code'      => $order->order_code,
                    'icon'            => 'fa-crown',
                    'url'             => '/goi-vip/thiet-lap-lich',
                ],
            ]);

            // 3. Gửi Email xác nhận kích hoạt Gói VIP (chuyên biệt, khác mail đơn hàng thường)
            $user = $subscription->user;
            if ($user && !empty($user->email)) {
                Mail::to($user->email)->send(new VipSubscriptionMail($subscription, $order));
            }
        } catch (\Exception $e) {
            Log::error('VIP Notification (Activated) Error: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id ?? null,
                'order_id'        => $order->id ?? null,
            ]);
        }
    }
}

