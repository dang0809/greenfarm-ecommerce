<?php

namespace App\Console\Commands;

use App\Events\OrderPaidEvent;
use App\Events\UserNotificationSent;
use App\Models\DeliverySchedule;
use App\Models\InventoryLog;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateVipDeliveryOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vip:generate-orders {--date= : Chỉ định ngày quét (định dạng Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quét lịch giao gói VIP và tự động tạo đơn hàng pending, trừ kho theo FEFO';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDateStr = $this->option('date');
        $targetDate = $targetDateStr ? Carbon::parse($targetDateStr)->toDateString() : today()->toDateString();

        $this->info("Bắt đầu quét các đợt giao gói VIP đến ngày [{$targetDate}]...");

        $schedules = DeliverySchedule::with(['subscription.user'])
            ->whereDate('delivery_date', '<=', $targetDate)
            ->where('status', 'pending')
            ->whereNull('order_id')
            ->whereHas('subscription', function ($q) {
                $q->where('status', 'active');
            })
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('Không có đợt giao VIP nào cần tạo đơn.');
            return self::SUCCESS;
        }

        $this->info("Tìm thấy {$schedules->count()} đợt giao cần tạo đơn.");
        $successCount = 0;

        foreach ($schedules as $schedule) {
            $subscription = $schedule->subscription;
            if (!$subscription || !$subscription->user) {
                $this->warn("Bỏ qua lịch ID #{$schedule->id} vì không tìm thấy hợp đồng hoặc người dùng.");
                continue;
            }

            try {
                DB::transaction(function () use ($schedule, $subscription, &$successCount) {
                    // 1. Tạo đơn hàng mới ở trạng thái 'pending' để nhân viên kiểm tra duyệt (Mã 13 số đồng bộ toàn hệ thống)
                    $orderCode = (string) intval(time() . rand(100, 999));

                    $order = Order::create([
                        'user_id'              => $subscription->user_id,
                        'order_code'           => $orderCode,
                        'order_type'           => 'subscription_delivery',
                        'recipient_name'       => $subscription->recipient_name,
                        'recipient_phone'      => $subscription->recipient_phone,
                        'recipient_address'    => $subscription->recipient_address,
                        'shipping_distance_km' => 0,
                        'subtotal'             => 0,
                        'shipping_fee'         => 0,
                        'total'                => 0,
                        'status'               => 'pending',
                        'note'                 => null,
                    ]);

                    // Ghi nhận lịch sử trạng thái ban đầu
                    $order->statusHistories()->create([
                        'old_status' => null,
                        'new_status' => 'pending',
                        'changed_by' => $subscription->user_id,
                    ]);

                    // 2. Duyệt các món trong items của đợt giao
                    $items = $schedule->items ?? [];

                    foreach ($items as $itemData) {
                        $productId = (int) ($itemData['product_id'] ?? 0);
                        $quantityNeeded = (int) ($itemData['quantity'] ?? 1);
                        $productName = $itemData['product_name'] ?? ('Rau củ hữu cơ #' . $productId);

                        if (!$productId || $quantityNeeded <= 0) {
                            continue;
                        }

                        // Tìm các lô hàng còn hạn và còn số lượng khả dụng (trừ phần reserved_quantity - chuẩn FEFO)
                        $product = Product::find($productId);
                        $batches = $product
                            ? $product->batches()
                                ->where('product_batches.status', 'active')
                                ->whereDate('product_batches.expiry_date', '>=', now())
                                ->whereRaw('product_batches.current_quantity > COALESCE(product_batches.reserved_quantity, 0)')
                                ->orderBy('product_batches.expiry_date', 'asc')
                                ->lockForUpdate()
                                ->get()
                            : collect();

                        foreach ($batches as $batch) {
                            if ($quantityNeeded <= 0) {
                                break;
                            }

                            // Chỉ lấy phần tồn kho thực sự khả dụng (trừ đi phần đang giữ chỗ)
                            $availableInBatch = max(0, (int) $batch->current_quantity - (int) ($batch->reserved_quantity ?? 0));
                            $take = min($availableInBatch, $quantityNeeded);
                            if ($take <= 0) {
                                continue;
                            }

                            // Trừ kho thực tế của lô
                            $batch->decrement('current_quantity', $take);

                            // Tạo OrderItem đã gán lô
                            OrderItem::create([
                                'order_id'     => $order->id,
                                'product_id'   => $productId,
                                'batch_id'     => $batch->id,
                                'product_name' => $productName,
                                'price'        => 0,
                                'quantity'     => $take,
                                'subtotal'     => 0,
                            ]);

                            // Ghi sổ nhật ký kho xuất bán
                            InventoryLog::create([
                                'batch_id'   => $batch->id,
                                'type'       => 'export_sale',
                                'quantity'   => $take,
                                'note'       => "Xuất kho giao đợt VIP #{$schedule->delivery_order}/8 đơn {$order->order_code}",
                                'created_by' => $subscription->user_id,
                            ]);

                            $quantityNeeded -= $take;
                        }

                        // Nếu không đủ kho hoặc hết sạch: Tạo OrderItem với batch_id = null (hiển thị thiếu hàng trên giao diện)
                        if ($quantityNeeded > 0) {
                            OrderItem::create([
                                'order_id'     => $order->id,
                                'product_id'   => $productId,
                                'batch_id'     => null,
                                'product_name' => $productName,
                                'price'        => 0,
                                'quantity'     => $quantityNeeded,
                                'subtotal'     => 0,
                            ]);
                        }
                    }

                    // 3. Cập nhật Lịch giao sang processing
                    $schedule->update([
                        'order_id' => $order->id,
                        'status'   => 'processing',
                    ]);

                    // 4. Bắn thông báo Realtime quả chuông cho khách
                    $notification = Notification::create([
                        'user_id' => $subscription->user_id,
                        'type'    => 'vip_delivery_created',
                        'title'   => "📦 Đợt rau VIP số {$schedule->delivery_order}/8 đã được tạo đơn",
                        'content' => "Đợt giao rau số {$schedule->delivery_order}/8 của bạn (Đơn #{$order->order_code}) đã được tạo. Nông trại đang chuẩn bị thu hoạch và đóng gói.",
                        'data'    => [
                            'order_id'   => $order->id,
                            'order_code' => $order->order_code,
                            'icon'       => 'fa-crown',
                            'url'        => '/ho-so?tab=orders',
                        ],
                    ]);

                    broadcast(new UserNotificationSent($notification));

                    // 5. Bắn sự kiện Realtime lên trang Quản lý đơn hàng của Sales (kênh sales-orders)
                    broadcast(new OrderPaidEvent($order));

                    $successCount++;
                    $this->info("Đã tạo thành công đơn hàng [#{$order->order_code}] cho lịch giao ID #{$schedule->id} (Đợt {$schedule->delivery_order}/8).");
                });
            } catch (\Throwable $e) {
                Log::error("Lỗi khi tự động tạo đơn VIP cho lịch ID #{$schedule->id}: " . $e->getMessage(), [
                    'exception' => $e,
                ]);
                $this->error("Thất bại khi xử lý lịch ID #{$schedule->id}: " . $e->getMessage());
            }
        }

        $this->info("Hoàn tất! Đã tạo thành công {$successCount}/{$schedules->count()} đơn hàng VIP.");
        return self::SUCCESS;
    }
}
