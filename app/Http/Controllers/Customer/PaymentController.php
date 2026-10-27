<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Payment;

class PaymentController extends Controller
{
    /**
     * Khởi tạo PayOS SDK
     */
    private function getPayOS(): PayOS
    {
        return new PayOS(
            config('services.payos.client_id'),
            config('services.payos.api_key'),
            config('services.payos.checksum_key')
        );
    }

    /**
     * Xử lý khi khách hàng thanh toán xong từ PayOS quay về
     */
    public function success(Request $request, $order_id)
    {
        $order = Order::with('items')->findOrFail($order_id);

        $code   = $request->query('code');
        $status = $request->query('status');
        $cancel = $request->query('cancel');

        if ($cancel === 'true' || $status === 'CANCELLED') {
            return redirect()->route('customer.checkout.cancel', ['order_id' => $order->id]);
        }

        // Chỉ kiểm tra PayOS để hiển thị câu thông báo cho đúng, KHÔNG cập nhật DB ở đây nữa
        $isPaid = false;
        try {
            $payosOrderCode = intval($order->order_code);
            $payOS = $this->getPayOS();
            $paymentInfo = $payOS->getPaymentLinkInformation($payosOrderCode);

            if (isset($paymentInfo['status']) && $paymentInfo['status'] === 'PAID') {
                $isPaid = true;
            }
        } catch (\Exception $e) {
            \Log::error('Không thể kiểm tra trạng thái thanh toán PayOS', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('customer.profile', ['tab' => 'orders'])
                ->with('error', 'Không thể kiểm tra thanh toán lúc này. Vui lòng thử lại sau.');
        }

        if (!$isPaid && $order->payment_status !== 'paid') {
            return redirect()->route('customer.profile', ['tab' => 'orders'])
                ->with('error', 'Đơn hàng chưa được thanh toán hoặc đang chờ xử lý.');
        }

        if ($order->order_type === 'subscription') {
            $existingSub = \App\Models\Subscription::where('user_id', $order->user_id)
                ->where('status', 'active')
                ->whereNull('delivery_days')
                ->latest()
                ->first();

            if (!$existingSub) {
                \App\Models\Subscription::create([
                    'user_id'           => $order->user_id,
                    'recipient_name'    => $order->recipient_name,
                    'recipient_phone'   => $order->recipient_phone,
                    'recipient_address' => $order->recipient_address,
                    'delivery_days'     => null,
                    'total_price'       => $order->total,
                    'total_deliveries'  => 8,
                    'delivered_count'   => 0,
                    'status'            => 'active',
                ]);
            }

            $order->update([
                'status'         => 'delivered',
                'payment_status' => 'paid',
            ]);

            return redirect()->route('customer.vip.setup')
                ->with('success', 'Thanh toán Gói Rau VIP thành công! Vui lòng chọn ngày nhận và thực đơn cho 8 đợt giao.');
        }

        return redirect()->route('customer.profile', ['tab' => 'orders'])
            ->with('success', 'Thanh toán thành công! Hệ thống đang xử lý đơn hàng của bạn.');
    }

    public function cancel(Request $request, $order_id = null)
    {
        $orderId = $order_id ?? $request->query('order_id');

        if ($orderId) {
            DB::transaction(function () use ($orderId) {
                // 1. Khóa cứng bản ghi đơn hàng
                $order = Order::with('items')->whereKey($orderId)->lockForUpdate()->first();

                // 2. Kiểm tra lại trạng thái sau khi lấy khóa
                // Nếu đơn không còn là pending (ví dụ cron 5 phút vừa hủy xong hoặc đã thanh toán) -> Bỏ qua
                if (!$order || $order->status !== 'pending' || $order->payment_status === 'paid') {
                    return;
                }

                // 3. Cập nhật trạng thái hủy
                $order->update([
                    'status'        => 'cancelled',
                    'cancel_reason' => 'Khách hủy thanh toán trên cổng PayOS',
                    'cancelled_at'  => now(),
                    'cancelled_by'  => 'customer',
                ]);

                // Đồng bộ trạng thái giao dịch sang failed trong bảng payments
                Payment::where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'failed']);

                if (method_exists($order, 'statusHistories')) {
                    $order->statusHistories()->create([
                        'old_status' => 'pending',
                        'new_status' => 'cancelled',
                        'changed_by' => $order->user_id,
                        'note'       => 'Đơn bị hủy do khách hủy giao dịch trên cổng PayOS.',
                    ]);
                }

                // 4. Hoàn lại kho (kho tổng và từng lô)
                foreach ($order->items as $item) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                    if ($item->batch_id) {
                        \App\Models\ProductBatch::where('id', $item->batch_id)->increment('current_quantity', $item->quantity);
                    }
                }

                // 5. Hoàn lại điểm thưởng
                if ($order->points_used > 0) {
                    $user = \App\Models\User::find($order->user_id);
                    if ($user) {
                        $user->increment('reward_points', $order->points_used);
                    }
                }
            });
        }

        return redirect()->route('customer.profile', ['tab' => 'orders'])
            ->with('error', 'Giao dịch thanh toán đã bị hủy.');
    }
}