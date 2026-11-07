<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Livewire\Traits\WithOrderActions;

class OrdersPage extends Component
{
    use WithPagination, WithFileUploads, WithOrderActions;

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    public function mount(): void
    {
        $validStatuses = [
            'all',
            'pending_payment',
            'pending_confirmation',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
            'refund',
        ];

        if (!in_array($this->statusFilter, $validStatuses, true)) {
            $this->statusFilter = 'all';
        }
    }

    // Filter trạng thái đơn hàng
    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function render()
    {
        $userId = Auth::id();

        $pendingPaymentQuery      = fn($q) => $q->where('status', 'pending')
            ->whereHas('payment', fn($q) => $q->where('status', 'pending'));

        $pendingConfirmationQuery = fn($q) => $q->where('status', 'pending')
            ->where(function($sub) {
                $sub->where('order_type', 'subscription_delivery')
                    ->orWhereHas('payment', fn($pq) => $pq->where('status', 'success'));
            });
        $processingQuery          = fn($q) => $q->where('status', 'processing');
        $shippedQuery             = fn($q) => $q->where('status', 'shipped');
        $deliveredQuery           = fn($q) => $q->where('status', 'delivered');
        $cancelledQuery           = fn($q) => $q->where('status', 'cancelled');
        $refundQuery              = fn($q) => $q->where('status', 'delivered')->whereHas('complaint');

        // Đơn mua gói VIP (subscription) và đơn do hệ thống tự động hủy không hiển thị trong danh sách đơn hàng
        $baseQuery = fn() => Order::where('user_id', $userId)
            ->where('order_type', '!=', 'subscription')
            ->where(function($q) {
                $q->where('status', '!=', 'cancelled')
                  ->orWhere(function($sub) {
                      $sub->where('cancelled_by', '!=', 'system')->orWhereNull('cancelled_by');
                  });
            });

        $counts = [
            'all'                  => $baseQuery()->count(),
            'pending_payment'      => $pendingPaymentQuery($baseQuery())->count(),
            'pending_confirmation' => $pendingConfirmationQuery($baseQuery())->count(),
            'processing'           => $processingQuery($baseQuery())->count(),
            'shipped'              => $shippedQuery($baseQuery())->count(),
            'delivered'            => $deliveredQuery($baseQuery())->count(),
            'cancelled'            => $cancelledQuery($baseQuery())->count(),
            'refund'               => $refundQuery($baseQuery())->count(),
        ];

        $query = $baseQuery()->with(['items.product.productImages', 'items.batch', 'complaint', 'statusHistories', 'deliverySchedule.subscription', 'payment']);

        if ($this->statusFilter === 'all') {
            // Không áp dụng thêm bộ lọc, lấy toàn bộ đơn hàng của khách
        } elseif ($this->statusFilter === 'pending_payment') {
            $pendingPaymentQuery($query);
        } elseif ($this->statusFilter === 'pending_confirmation') {
            $pendingConfirmationQuery($query);
        } elseif ($this->statusFilter === 'processing') {
            $processingQuery($query);
        } elseif ($this->statusFilter === 'shipped') {
            $shippedQuery($query);
        } elseif ($this->statusFilter === 'delivered') {
            $deliveredQuery($query);
        } elseif ($this->statusFilter === 'cancelled') {
            $cancelledQuery($query);
        } elseif ($this->statusFilter === 'refund') {
            $refundQuery($query);
        }

        return view('livewire.customer.orders-page', [
            'orders'             => $query->latest()->paginate(10),
            'counts'             => $counts,
            'statusFilter'       => $this->statusFilter,
            'showComplaintModal' => $this->showComplaintModal,
        ]);
    }
}
