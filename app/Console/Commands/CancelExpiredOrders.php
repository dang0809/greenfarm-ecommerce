<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductBatch;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động hủy các đơn hàng pending quá hạn thanh toán 5 phút, nhả kho giữ chỗ và hoàn lại điểm thưởng';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredOrderIds = Order::where('status', 'pending')
            ->whereHas('payment', fn($q) => $q->where('status', 'pending'))
            ->where('created_at', '<=', now()->subMinutes(5))
            ->pluck('id');

        if ($expiredOrderIds->isEmpty()) {
            return self::SUCCESS;
        }

        $cancelledCount = 0;

        foreach ($expiredOrderIds as $orderId) {
            DB::transaction(function () use ($orderId, &$cancelledCount) {
                // 1. Khóa cứng bản ghi đơn hàng để tránh race condition
                $order = Order::with('items')->whereKey($orderId)->lockForUpdate()->first();

                // 2. Kiểm tra lại trạng thái sau khi lấy khóa
                // Nếu đơn không còn là pending (khách vừa hủy hoặc vừa thanh toán thành công) -> Bỏ qua ngay
                if (!$order || $order->status !== 'pending' || $order->payment?->status === 'success') {
                    return;
                }

                // 3. Cập nhật trạng thái hủy
                $order->update([
                    'status' => 'cancelled',
                    'cancel_reason' => 'Tự động hủy do quá hạn thanh toán 5 phút',
                    'cancelled_at' => now(),
                    'cancelled_by' => 'system',
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

                $cancelledCount++;
            });
        }

        if ($cancelledCount > 0) {
            $this->info("Đã tự động hủy {$cancelledCount} đơn hàng quá hạn thanh toán 5 phút.");
        }

        return self::SUCCESS;
    }
}
