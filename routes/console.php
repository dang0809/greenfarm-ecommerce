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
        ->whereHas('payment', fn($q) => $q->where('status', 'pending'))
        ->where('created_at', '<=', now()->subMinutes(5))
        ->pluck('id');

    foreach ($expiredOrderIds as $orderId) {
        DB::transaction(function () use ($orderId) {
            // 1. Khóa cứng bản ghi đơn hàng để tránh race condition
            $order = Order::with('items')->whereKey($orderId)->lockForUpdate()->first();

            // 2. Kiểm tra lại trạng thái sau khi lấy khóa
            // Nếu đơn không còn là pending (khách vừa hủy hoặc vừa thanh toán thành công) -> Bỏ qua ngay
            if (!$order || $order->status !== 'pending' || $order->payment?->status === 'success') {
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
                ]);
            }

            // 4. Nhả số lượng giữ chỗ 5 phút (kho thực tế current_quantity chưa hề bị trừ)
            foreach ($order->items as $item) {
                if ($item->batch_id) {
                    ProductBatch::where('id', $item->batch_id)->decrement('reserved_quantity', $item->quantity);
                    ProductBatch::syncProductSalePrice($item->product_id);
                }
            }

            // 5. Hoàn lại điểm tích lũy
            if ($order->points_used > 0) {
                User::find($order->user_id)?->increment('reward_points', $order->points_used);
            }
        });
    }
})->everyMinute();

// Tự động quét và tạo đơn hàng cho các đợt giao rau VIP vào 06:00 sáng mỗi ngày
Schedule::command('vip:generate-orders')
    ->dailyAt('06:00')
    ->withoutOverlapping();

// Tự động nâng mức giảm Flash Sale từ 30% lên 50% khi lô sang ngưỡng <= 1 ngày, hoặc tắt sale nếu hết hạn / ngừng bán
Schedule::call(function () {
    $saleBatches = ProductBatch::with('plantingBatch')
        ->where('discount_percent', '>', 0)
        ->get();

    $affectedProductIds = [];

    foreach ($saleBatches as $batch) {
        if (!$batch->expiry_date) {
            continue;
        }

        $daysLeft = today()->diffInDays($batch->expiry_date, false);
        $newDiscount = $batch->discount_percent;

        if ($batch->status !== 'active' || $daysLeft < 0) {
            $newDiscount = 0;
        } elseif ($daysLeft <= 1 && $batch->discount_percent < 50) {
            $newDiscount = 50;
        }

        if ($newDiscount !== $batch->discount_percent) {
            $batch->update(['discount_percent' => $newDiscount]);

            if ($batch->plantingBatch?->product_id) {
                $affectedProductIds[$batch->plantingBatch->product_id] = true;
            }
        }
    }

    foreach (array_keys($affectedProductIds) as $productId) {
        ProductBatch::syncProductSalePrice($productId);
    }
})->name('sync-flash-sale-batches')->everyMinute();
