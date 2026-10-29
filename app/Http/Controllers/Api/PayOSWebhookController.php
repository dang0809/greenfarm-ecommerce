<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Payment;
use App\Services\OrderNotificationService;
use App\Services\VipNotificationService;
class PayOSWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // ... (Dán nguyên vẹn đoạn code handleWebhook bạn vừa gửi vào đây) ...
        $body = $request->all();
        
        try {
            $payOS = new PayOS(
                config('services.payos.client_id'),
                config('services.payos.api_key'),
                config('services.payos.checksum_key')
            );

            $webhookData = $payOS->verifyPaymentWebhookData($body);
            
            if ($webhookData['code'] == '00') {
                $payosOrderCode = $webhookData['orderCode'];
                
                $orderId = Order::where('order_code', (string) $payosOrderCode)
                    ->value('id');

                if (!$orderId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Test webhook',
                    ], 200);
                }

                DB::beginTransaction();

                try {
                    // Khóa đơn hàng trong lúc xử lý thanh toán
                    $order = Order::whereKey($orderId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    // Nếu đã thanh toán thì không cộng điểm và sold_count lần nữa
                    if ($order->payment?->status === 'success') {
                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => 'Đơn hàng đã được thanh toán trước đó.',
                        ], 200);
                    }

                    // 2. Chặn race condition: Nếu đơn đã bị hủy (khách hủy hoặc cron tự động hủy)
                    if ($order->status === 'cancelled') {
                        // Cập nhật bản ghi thanh toán vào bảng payments
                        Payment::updateOrCreate(
                            ['order_id' => $order->id],
                            [
                                'payment_code'     => 'PAY-' . $order->order_code,
                                'method'           => 'payos',
                                'amount'           => $webhookData['amount'] ?? $order->total,
                                'status'           => 'success',
                                'transaction_code' => $webhookData['reference'] ?? null,
                                'paid_at'          => now(),
                            ]
                        );

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => 'Đơn hàng đã bị hủy, cần đối soát hoàn tiền.',
                        ], 200);
                    }

                    // Cập nhật biên lai thanh toán vào bảng payments
                    Payment::updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'payment_code'     => 'PAY-' . $order->order_code,
                            'method'           => 'payos',
                            'amount'           => $webhookData['amount'] ?? $order->total,
                            'status'           => 'success',
                            'transaction_code' => $webhookData['reference'] ?? null,
                            'paid_at'          => now(),
                        ]
                    );

                    // Lấy danh sách sản phẩm trong đơn hàng
                    $order->load('items');
                    $subscription = null;
                    if ($order->order_type === 'subscription') {
                        $order->update(['status' => 'delivered']);

                        $subscription = \App\Models\Subscription::where('user_id', $order->user_id)
                            ->where('status', 'active')
                            ->whereNull('delivery_days')
                            ->latest()
                            ->first();

                        if (!$subscription) {
                            $subscription = \App\Models\Subscription::create([
                                'user_id'           => $order->user_id,
                                'recipient_name'    => $order->recipient_name,
                                'recipient_phone'   => $order->recipient_phone,
                                'recipient_address' => $order->recipient_address,
                                'delivery_days'     => null,
                                'total_price'       => $order->total,
                                'status'            => 'active',
                            ]);
                        }
                    } else {
                        $order->update(['status' => 'pending']);
                    }

                    $order->statusHistories()->create([
                        'old_status' => 'pending',
                        'new_status' => 'pending',
                        'changed_by' => $order->user_id,
                        'note'       => 'Xác nhận thanh toán tự động qua Webhook PayOS.',
                    ]);

                    foreach ($order->items as $item) {
                        Product::where('id', $item->product_id)->increment('sold_count', $item->quantity);
                    }

                   
                    DB::commit();

                    // Gửi thông báo sau khi commit thành công
                    if ($order->order_type === 'subscription' && $subscription) {
                        // Email + Notification chuẩn VIP (không dùng mail đơn hàng thường)
                        VipNotificationService::notifyVipActivated($subscription, $order);
                    } else {
                        OrderNotificationService::notifyOrderPaid($order);
                    }

                    return response()->json([
                        'error' => 0,
                        'message' => 'Ok',
                        'data' => null
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Webhook DB Error: ' . $e->getMessage());
                    return response()->json(['error' => 'Có lỗi xảy ra khi cập nhật dữ liệu đơn hàng.'], 500);
                }
            }
            
            return response()->json(['error' => 'Mã phản hồi thanh toán không hợp lệ.'], 400);

        } catch (\Exception $e) {
            Log::error('Webhook Verify Error: ' . $e->getMessage());
            return response()->json(['error' => 'Không thể xác minh tính hợp lệ của webhook PayOS.'], 400);
        }
    }
}