<?php

namespace App\Services;

use App\Events\UserNotificationSent;
use App\Mail\OrderNotificationMail;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Complaint;
use App\Models\User;
use App\Models\SystemSetting;
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
            $notification = Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order_paid',
                'title' => '🛒 Đơn hàng #' . $order->order_code . ' đã thanh toán thành công',
                'content' => 'Đơn hàng #' . $order->order_code . ' trị giá ' . number_format($order->total, 0, ',', '.') . 'đ đã thanh toán thành công qua PayOS. Nông trại đang chuẩn bị nông sản cho bạn.',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'icon' => 'fa-receipt',
                    'url' => '/ho-so?tab=orders',
                ],
            ]);

            // Phát sóng Realtime tới Quả chuông trên Header của người dùng
            broadcast(new UserNotificationSent($notification));

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

            // 2. Ghi thông báo Web vào bảng notifications (hiển thị quả chuông Header)
            $pointsNote = $order->points_earned > 0 ? " Bạn được tích lũy +{$order->points_earned} điểm thưởng!" : '';
            $notification = Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order_delivered',
                'title' => '🎉 Giao hàng thành công đơn #' . $order->order_code,
                'content' => 'Đơn hàng #' . $order->order_code . ' đã được giao thành công.' . $pointsNote . ' Chúc bạn có những bữa ăn tươi ngon và an lành!',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'points_earned' => $order->points_earned,
                    'icon' => 'fa-circle-check',
                    'url' => '/ho-so?tab=orders',
                ],
            ]);

            // Phát sóng Realtime tới Quả chuông trên Header của người dùng
            broadcast(new UserNotificationSent($notification));

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

    /**
     * Thông báo Realtime khi Sales duyệt hoàn tiền khiếu nại (đã hoàn tất chuyển khoản)
     */
    public static function notifyComplaintResolved(Complaint $complaint): void
    {
        try {
            $complaint->loadMissing(['order', 'user']);
            $userId = (int) $complaint->user_id;
            $orderCode = $complaint->order->order_code;
            $refundFormatted = number_format($complaint->refund_amount, 0, ',', '.') . 'đ';
            $bankInfo = trim("{$complaint->bank_name} ({$complaint->bank_account_number})");
            $farmName = SystemSetting::farmName();
            // 1. Ghi thông báo Web vào bảng notifications
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => 'complaint_resolved',
                'title' => '💰 Đã hoàn tiền khiếu nại đơn #' . $orderCode,
                'content' => $farmName . ' đã hoàn tất chuyển khoản '
                    . $refundFormatted . ' vào tài khoản ' . $bankInfo
                    . ' của bạn. Vui lòng kiểm tra tài khoản ngân hàng.',
                'data' => [
                    'order_id' => $complaint->order_id,
                    'order_code' => $orderCode,
                    'complaint_id' => $complaint->id,
                    'refund_amount' => $complaint->refund_amount,
                    'icon' => 'fa-hand-holding-dollar',
                    'toast_type' => 'success',
                    'url' => '/ho-so?tab=orders',
                ],
            ]);

            // 2. Phát sóng Realtime tới Quả chuông trên Header của người dùng
            broadcast(new UserNotificationSent($notification));

        } catch (\Exception $e) {
            Log::error('Complaint Notification (Resolved) Error: ' . $e->getMessage(), [
                'complaint_id' => $complaint->id ?? null,
            ]);
        }
    }

    /**
     * Thông báo Realtime khi Sales từ chối bồi hoàn khiếu nại
     */
    public static function notifyComplaintRejected(Complaint $complaint, string $reason = ''): void
    {
        try {
            $complaint->loadMissing(['order', 'user']);
            $userId = (int) $complaint->user_id;
            $orderCode = $complaint->order->order_code;
            $reasonText = !empty($reason) ? $reason : ($complaint->resolution_note ?? 'Không đáp ứng điều kiện bảo hành 24h');

            // 1. Ghi thông báo Web vào bảng notifications
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => 'complaint_rejected',
                'title' => '❌ Khiếu nại đơn #' . $orderCode . ' không được chấp thuận',
                'content' => 'Yêu cầu bồi hoàn cho đơn #' . $orderCode . ' đã bị từ chối. Lý do: "' . $reasonText . '". Nếu cần hỗ trợ thêm, vui lòng liên hệ hotline nông trại.',
                'data' => [
                    'order_id' => $complaint->order_id,
                    'order_code' => $orderCode,
                    'complaint_id' => $complaint->id,
                    'reject_reason' => $reasonText,
                    'icon' => 'fa-circle-xmark',
                    'toast_type' => 'error',
                    'url' => '/ho-so?tab=orders',
                ],
            ]);

            // 2. Phát sóng Realtime tới Quả chuông trên Header của người dùng
            broadcast(new UserNotificationSent($notification));

        } catch (\Exception $e) {
            Log::error('Complaint Notification (Rejected) Error: ' . $e->getMessage(), [
                'complaint_id' => $complaint->id ?? null,
            ]);
        }
    }
}