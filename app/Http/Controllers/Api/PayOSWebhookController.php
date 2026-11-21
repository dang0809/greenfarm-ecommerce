<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\InventoryLog;
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
                        // Chỉ chặn đối với đơn bán lẻ thông thường vì tồn kho thực tế đã bị nhả
                        if ($order->order_type !== 'subscription') {
                            Payment::updateOrCreate(
                                ['order_id' => $order->id],
                                [
                                    'payment_code' => 'PAY-' . $order->order_code,
                                    'method' => 'payos',
                                    'amount' => $webhookData['amount'] ?? $order->total,
                                    'status' => 'success',
                                    'transaction_code' => $webhookData['reference'] ?? null,
                                    'paid_at' => now(),
                                ]
                            );

                            DB::commit();

                            return response()->json([
                                'success' => true,
                                'message' => 'Đơn hàng đã bị hủy, cần đối soát hoàn tiền.',
                            ], 200);
                        }

                        // Đơn mua gói VIP (subscription) không chiếm giữ tồn kho thực tế,
                        // nên nếu tiền đã vào tài khoản thành công thì tiếp tục phục hồi đơn và kích hoạt gói VIP cho khách!
                    }

                    // Cập nhật biên lai thanh toán vào bảng payments
                    Payment::updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'payment_code' => 'PAY-' . $order->order_code,
                            'method' => 'payos',
                            'amount' => $webhookData['amount'] ?? $order->total,
                            'status' => 'success',
                            'transaction_code' => $webhookData['reference'] ?? null,
                            'paid_at' => now(),
                        ]
                    );

                    // Lấy danh sách sản phẩm trong đơn hàng
                    $order->load([
                        'items.product',
                        'user:id,name,email',
                    ]);

                    $previousStatus = $order->status;
                    $subscription = null;
                    if ($order->order_type === 'subscription') {
                        $order->update([
                            'status' => 'delivered',
                            'cancel_reason' => null,
                            'cancelled_at' => null,
                            'cancelled_by' => null,
                        ]);

                        $subscription = \App\Models\Subscription::where('user_id', $order->user_id)
                            ->where('status', 'active')
                            ->whereNull('delivery_days')
                            ->latest()
                            ->first();

                        if (!$subscription) {
                            $subscription = \App\Models\Subscription::create([
                                'user_id' => $order->user_id,
                                'recipient_name' => $order->recipient_name,
                                'recipient_phone' => $order->recipient_phone,
                                'recipient_address' => $order->recipient_address,
                                'delivery_days' => null,
                                'total_price' => $order->total,
                                'status' => 'active',
                            ]);
                        }
                    }

                    $newStatus = ($order->order_type === 'subscription') ? 'delivered' : 'pending';
                    $order->statusHistories()->create([
                        'old_status' => $previousStatus,
                        'new_status' => $newStatus,
                        'changed_by' => $order->user_id,
                    ]);

                    if ($order->order_type === 'retail') {
                        $order->adjustProductSoldCounts(1);
                    }

                    foreach ($order->items as $item) {
                        if (!$item->batch_id) {
                            continue;
                        }

                        // Khóa lô để đọc đúng tồn kho mới nhất.
                        $batch = ProductBatch::query()
                            ->lockForUpdate()
                            ->findOrFail($item->batch_id);

                        $quantity = (int) $item->quantity;
                        $current = (int) $batch->current_quantity;
                        $reserved = (int) $batch->reserved_quantity;

                        if (
                            $quantity <= 0
                            || $current < 0
                            || $reserved < 0
                            || $reserved > $current
                            || $quantity > $current
                            || $quantity > $reserved
                            || !in_array($batch->status, ['active', 'suspended'], true)
                        ) {
                            throw new \RuntimeException(
                                "Tồn kho hoặc giữ chỗ của lô {$batch->batch_code} không hợp lệ."
                            );
                        }

                        // Thanh toán thành công: nhả giữ chỗ và trừ tồn thực tế.
                        $batch->reserved_quantity = $reserved - $quantity;
                        $batch->current_quantity = $current - $quantity;

                        if ($batch->current_quantity === 0) {
                            $batch->status = 'sold_out';
                        }

                        $batch->saveOrFail();

                        ProductBatch::syncProductSalePrice($item->product_id);

                        $customerName = $order->user?->name
                            ?: ($order->recipient_name ?: 'Khách hàng');

                        $unit = $item->product?->unit ?? 'đơn vị';

                        // Loại biến động kho
                        InventoryLog::create([
                            'batch_id' => $batch->id,
                            'type' => 'export_sale',
                            'quantity' => $quantity,

                            'note' => "Khách hàng: {$customerName}\n"
                                . "Mã đơn: #{$order->order_code}\n"
                                . "Thanh toán thành công.\n"
                                . "Xuất khỏi lô: {$quantity} {$unit}",

                            'created_by' => $order->user_id,
                        ]);
                    }


                    DB::commit();

                    // Phát sóng sự kiện Realtime cho giao diện NVBH (OrderManager & Sidebar)
                    if ($order->order_type !== 'subscription') {
                        try {
                            broadcast(new \App\Events\OrderPaidEvent($order));
                        } catch (\Throwable $e) {
                            Log::warning('Không thể phát sóng OrderPaidEvent (Reverb offline?): ' . $e->getMessage());
                        }
                    }

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