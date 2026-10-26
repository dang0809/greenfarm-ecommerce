<?php
use Illuminate\Support\Facades\Schedule;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

Schedule::call(function () {
    $expiredOrderIds = Order::where('status', 'pending')
        ->where('payment_status', 'pending')
        ->where('created_at', '<=', now()->subMinutes(5))
        ->pluck('id');

    foreach ($expiredOrderIds as $orderId) {
        DB::transaction(function () use ($orderId) {
            // 1. Khóa cứng bản ghi đơn hàng để tránh race condition
            $order = Order::with('items')->whereKey($orderId)->lockForUpdate()->first();

            // 2. Kiểm tra lại trạng thái sau khi lấy khóa
            // Nếu đơn không còn là pending (khách vừa hủy hoặc vừa thanh toán thành công) -> Bỏ qua ngay
            if (!$order || $order->status !== 'pending' || $order->payment_status === 'paid') {
                return;
            }

            // 3. Cập nhật trạng thái hủy
            $order->update([
                'status'        => 'cancelled',
                'cancel_reason' => 'Tự động hủy do quá hạn thanh toán 5 phút',
                'cancelled_at'  => now(),
                'cancelled_by'  => 'system',
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
                    'note'       => 'Hệ thống tự động hủy do quá hạn thanh toán 5 phút.',
                ]);
            }

            // 4. Hoàn lại kho (kho tổng và từng lô)
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                if ($item->batch_id) {
                    ProductBatch::where('id', $item->batch_id)->increment('current_quantity', $item->quantity);
                }
            }

            // 5. Hoàn lại điểm tích lũy
            if ($order->points_used > 0) {
                User::find($order->user_id)?->increment('reward_points', $order->points_used);
            }
        });
    }
})->everyMinute();