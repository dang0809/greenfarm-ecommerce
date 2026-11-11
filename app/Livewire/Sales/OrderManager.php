<?php

namespace App\Livewire\Sales;

use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\ProductBatch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Traits\WithOrderTimeline;
use App\Services\OrderNotificationService;

#[Layout('layouts.sales.app')]
#[Title('Quản Lý Đơn Hàng | Nông Trại Đất Lành')]
class OrderManager extends Component
{
    use WithPagination, WithOrderTimeline;

    // Bộ lọc & Tìm kiếm (Mặc định hiển thị tab Chờ xác nhận)
    public string $statusFilter = 'pending'; // pending, processing, shipped, delivered, cancelled, all
    public string $search = '';
    public string $typeFilter = '';          // retail, subscription_delivery
    public int $perPage = 15;

    // Modal xem chi tiết đơn hàng
    public bool $showDetailModal = false;
    public ?int $viewingOrderId = null;
    public ?Order $selectedOrder = null;

    // Modal hủy đơn hàng
    public bool $showCancelModal = false;
    public ?int $cancelOrderId = null;
    public string $cancelReason = '';



    // Reset phân trang khi thay đổi bộ lọc
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'pending';
        $this->typeFilter = '';
        $this->resetPage();
    }

    /**
     * Tự động bắt sự kiện nổ đơn từ WebSockets
     */
    #[On('echo:sales-orders,.OrderPaid')]
    public function onOrderPaidReceived(array $payload = []): void
    {
        // 1. Đưa phân trang về trang 1 để đơn mới nhất hiện ngay lên đầu bảng
        $this->resetPage();

        // 2. Lấy dữ liệu đơn vừa nổ và hiển thị Toast màu xanh góc màn hình
        $orderData      = $payload['orderData'] ?? [];
        $orderCode      = $orderData['order_code'] ?? '';
        $customerName   = $orderData['recipient_name'] ?? 'Khách hàng';
        $totalFormatted = !empty($orderData['total']) 
            ? number_format($orderData['total'], 0, ',', '.') . 'đ'
            : '';

        $orderType = $orderData['order_type'] ?? '';
        if ($orderType === 'subscription_delivery') {
            $message = "👑 ĐỢT GIAO VIP MỚI #{$orderCode} từ {$customerName}!";
        } else {
            $message = "🔔 ĐƠN MỚI #{$orderCode} vừa thanh toán";
            if ($totalFormatted) {
                $message .= " {$totalFormatted}";
            }
            $message .= " từ {$customerName}!";
        }

        $this->dispatch('show-toast', type: 'success', message: $message);
    }

    /**
     * Đếm số lượng đơn hàng đã thanh toán theo từng trạng thái để hiển thị badge trên các Tab
     */
    public function getStatusCountsProperty(): array
    {
        $counts = Order::where(function ($q) {
                $q->whereHas('payment', fn($p) => $p->where('status', 'success'))
                  ->orWhere('total', 0);
            })
            ->where('order_type', '!=', 'subscription')
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->toArray();

        return [
            'all'        => array_sum($counts),
            'pending'    => (int) ($counts['pending'] ?? 0),
            'processing' => (int) ($counts['processing'] ?? 0),
            'shipped'    => (int) ($counts['shipped'] ?? 0),
            'delivered'  => (int) ($counts['delivered'] ?? 0),
            'cancelled'  => (int) ($counts['cancelled'] ?? 0),
        ];
    }

    /**
     * Query cơ sở dữ liệu đã lọc (Chỉ hiển thị các đơn đã thanh toán)
     */
    protected function getFilteredOrdersQuery()
    {
        $query = Order::with([
                'items.product',
                'items.batch',
                'payment',
                'user',
            ])
            // CHỈ HIỆN ĐƠN ĐÃ THANH TOÁN THÀNH CÔNG (HOẶC ĐƠN BÙ 0Đ)
            ->where(function ($q) {
                $q->whereHas('payment', fn($p) => $p->where('status', 'success'))
                  ->orWhere('total', 0);
            })
            // Ẩn hóa đơn mua gói VIP (quản lý riêng ở SubscriptionManager)
            ->where('order_type', '!=', 'subscription')
            ->latest('id');

        // Lọc theo Tab trạng thái
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Lọc theo từ khóa tìm kiếm (Chỉ tìm theo mã đơn hàng)
        $rawSearch = ltrim(trim($this->search), '#');
        if ($rawSearch !== '') {
            $query->where('order_code', 'like', "%{$rawSearch}%");
        }

        // Lọc theo Loại đơn (retail hoặc subscription_delivery)
        if (!empty($this->typeFilter)) {
            $query->where('order_type', $this->typeFilter);
        }

        return $query;
    }

    /**
     * Mở modal xem chi tiết đơn hàng
     */
    public function openOrderDetail(int $orderId): void
    {
        $this->viewingOrderId = $orderId;
        $this->selectedOrder = Order::with([
            'items.product',
            'items.batch.plantingBatch.farmPlot',
            'statusHistories.changer',
            'payment',
            'user',
            'deliverySchedule.subscription',
            'complaint',
        ])->find($orderId);

        $this->showDetailModal = true;
    }

    public function closeOrderDetail(): void
    {
        $this->showDetailModal = false;
        $this->viewingOrderId = null;
        $this->selectedOrder = null;
    }

    /**
     * 1. Xác nhận đơn hàng (pending -> processing)
     */
    public function approveOrder(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order || $order->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng không ở trạng thái Chờ xác nhận!');
            return;
        }

        DB::transaction(function () use ($order) {
            $oldStatus = $order->status;
            $order->update(['status' => 'processing']);

            $order->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => 'processing',
                'changed_by' => Auth::id() ?? $order->user_id,
            ]);
        });

        if ($this->viewingOrderId === $orderId) {
            $this->openOrderDetail($orderId);
        }

        $this->dispatch('order-status-changed');
        $this->dispatch('show-toast', type: 'success', message: "Đã duyệt đơn {$order->order_code} (Chuyển sang Chờ lấy hàng).");
    }

    /**
     * 2. Bàn giao Shipper đi giao (processing -> shipped)
     */
    public function dispatchOrder(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order || $order->status !== 'processing') {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng không ở trạng thái Chờ lấy hàng!');
            return;
        }

        DB::transaction(function () use ($order) {
            $oldStatus = $order->status;

            $order->update([
                'status' => 'shipped',
            ]);

            $order->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => 'shipped',
                'changed_by' => Auth::id() ?? $order->user_id ,
            ]);
        });

        if ($this->viewingOrderId === $orderId) {
            $this->openOrderDetail($orderId);
        }

        $this->dispatch('show-toast', type: 'success', message: "Đã xuất kho đơn {$order->order_code} đi giao hàng.");
    }

    /**
     * 3. Xác nhận giao hàng thành công (shipped -> delivered)
     */
    public function completeDelivery(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order || $order->status !== 'shipped') {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng không ở trạng thái Đang giao!');
            return;
        }

        DB::transaction(function () use ($order) {
            $oldStatus = $order->status;
            $order->update(['status' => 'delivered']);

            // 1. Cộng điểm thưởng tích lũy cho khách hàng khi giao thành công
            if ($order->user_id && $order->points_earned > 0) {
                User::where('id', $order->user_id)->increment('reward_points', $order->points_earned);
            }

            // 2. Ghi nhận lịch sử trạng thái
            $order->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => 'delivered',
                'changed_by' => Auth::id() ?? $order->user_id ?? 1,
            ]);

            // 3. Đồng bộ trạng thái cho lịch giao gói VIP (nếu là đơn giao định kỳ)
            if ($order->deliverySchedule) {
                $order->deliverySchedule->update(['status' => 'delivered']);

                $subscription = $order->deliverySchedule->subscription;
                if ($subscription) {
                    $deliveredCount = (int) $subscription->deliverySchedules()->where('status', 'delivered')->count();
                    if ($deliveredCount >= 8) {
                        $subscription->update(['status' => 'completed']);
                    }
                }
            }
        });

        // 3. Gửi thông báo quả chuông trên web & gửi Email xác nhận cho khách
        OrderNotificationService::notifyOrderDelivered($order);

        if ($this->viewingOrderId === $orderId) {
            $this->openOrderDetail($orderId);
        }

        $pointsMsg = ($order->points_earned > 0) ? " (Đã tích lũy +{$order->points_earned} điểm cho khách)" : "";
        $this->dispatch('show-toast', type: 'success', message: "Đơn hàng {$order->order_code} đã giao thành công!{$pointsMsg}");
    }

    /**
     * Mở modal hủy đơn hàng
     */
    public function openCancelModal(int $orderId): void
    {
        $this->cancelOrderId = $orderId;
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancelOrderId = null;
        $this->cancelReason = '';
    }

    /**
     * 4. Xác nhận Hủy đơn hàng và tự động Hoàn trả tồn kho vào Lô hàng (1-1 Integer)
     */
    public function confirmCancelOrder(): void
    {
        if (!$this->cancelOrderId) return;

        $reason = trim($this->cancelReason);
        if (empty($reason)) {
            $this->dispatch('show-toast', type: 'error', message: 'Vui lòng nhập lý do hủy đơn hàng!');
            return;
        }

        $order = DB::transaction(function () use ($reason) {
            $lockedOrder = Order::with(['items', 'payment', 'deliverySchedule'])
                ->whereKey($this->cancelOrderId)
                ->lockForUpdate()
                ->first();

            if (!$lockedOrder || !in_array($lockedOrder->status, ['pending', 'processing'], true)) {
                return null;
            }

            $oldStatus = $lockedOrder->status;

            $lockedOrder->update([
                'status'        => 'cancelled',
                'cancel_reason' => $reason,
                'cancelled_by'  => 'sales',
                'cancelled_at'  => now(),
            ]);

            if ($lockedOrder->order_type === 'retail' && $lockedOrder->payment?->status === 'success') {
                $lockedOrder->adjustProductSoldCounts(-1);
            }

            // 1. Xử lý hoàn kho thực tế và ghi sổ nhật ký return_cancel
            // Toàn bộ đơn ở OrderManager đều đã thanh toán thành công (kho thực tế đã bị trừ qua webhook)
            foreach ($lockedOrder->items as $item) {
                if ($item->batch_id) {
                    ProductBatch::where('id', $item->batch_id)->increment('current_quantity', $item->quantity);

                    InventoryLog::create([
                        'batch_id'   => $item->batch_id,
                        'type'       => 'return_cancel',
                        'quantity'   => $item->quantity,
                        'note'       => "Hoàn kho do NVBH hủy đơn #{$lockedOrder->order_code}: {$reason}",
                        'created_by' => Auth::id() ?? $lockedOrder->user_id ?? 1,
                    ]);
                }
            }

            // 2. Hoàn lại điểm thưởng tích lũy (nếu khách đã tiêu)
            if ($lockedOrder->points_used > 0) {
                User::find($lockedOrder->user_id)?->increment('reward_points', $lockedOrder->points_used);
            }

            // 3. Ghi nhận lịch sử trạng thái
            $lockedOrder->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
                'changed_by' => Auth::id() ?? $lockedOrder->user_id ?? 1,
            ]);

            // 4. Đồng bộ hoàn lại lịch đợt giao cho gói VIP (nếu là đơn giao định kỳ)
            if ($lockedOrder->deliverySchedule) {
                $lockedOrder->deliverySchedule->update([
                    'order_id' => null,
                    'status'   => 'pending',
                ]);
            }

            return $lockedOrder;
        });

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Chỉ có thể hủy đơn hàng khi đang ở trạng thái Chờ xác nhận hoặc Chờ lấy hàng!');
            return;
        }

        $this->closeCancelModal();

        if ($this->viewingOrderId === $order->id) {
            $this->openOrderDetail($order->id);
        }

        $this->dispatch('order-status-changed');
        $this->dispatch('show-toast', type: 'success', message: "Đã hủy đơn {$order->order_code} và hoàn trả kho thành công!");
    }

    public function render()
    {
        $orders = $this->getFilteredOrdersQuery()->paginate($this->perPage);

        return view('livewire.sales.order-manager', [
            'orders'       => $orders,
            'statusCounts' => $this->statusCounts,
        ]);
    }
}
