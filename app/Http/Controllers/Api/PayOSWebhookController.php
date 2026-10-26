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
                    if ($order->payment_status === 'paid') {
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

                        $order->update([
                            'payment_status' => 'paid',
                            'refund_status'  => 'pending',
                            'cancel_reason'  => ($order->cancel_reason ? $order->cancel_reason . ' | ' : '') . 'Khách thanh toán sau khi đơn đã bị hủy, cần đối soát hoàn tiền.',
                        ]);

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
                    $order->update([
                        'payment_status' => 'paid',
                        'status'         => 'pending',
                    ]);

                    $order->statusHistories()->create([
                        'old_status' => 'pending',
                        'new_status' => 'pending',
                        'changed_by' => $order->user_id,
                        'note'       => 'Xác nhận thanh toán tự động qua Webhook PayOS.',
                    ]);

                    foreach ($order->items as $item) {
                        Product::where('id', $item->product_id)->increment('sold_count', $item->quantity);
                    }

                    $user = User::find($order->user_id);
                    if ($user) {
                        if ($order->points_earned > 0) {
                            $user->increment('reward_points', $order->points_earned);
                        }
                    }

                    DB::commit();
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