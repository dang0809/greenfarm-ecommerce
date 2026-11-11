<?php

namespace App\Livewire\Sales;

use App\Models\Product;
use App\Models\Subscription;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sales.app')]
#[Title('Quản Lý Gói Rau Định Kỳ VIP | Nông Trại Đất Lành')]
class SubscriptionManager extends Component
{
    use WithPagination;

    // Bộ lọc & Tìm kiếm
    public string $statusFilter = 'all'; // all, active, today, completed
    public string $search = '';
    public int $perPage = 10;

    // Modal xem chi tiết hợp đồng (Read-Only)
    public bool $showDetailModal = false;
    public ?Subscription $selectedSubscription = null;
    public ?string $selectedOrderCode = null;
    public array $enrichedSchedules = [];

    // Reset phân trang khi thay đổi tìm kiếm hoặc bộ lọc
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }


    /**
     * Đếm số lượng hợp đồng theo từng tab trạng thái
     */
    public function getStatusCountsProperty(): array
    {
        return [
            'all'       => Subscription::count(),
            'active'    => Subscription::where('status', 'active')->count(),
            'today'     => Subscription::whereHas('deliverySchedules', function ($q) {
                $q->where('status', 'processing');
            })->count(),
            'completed' => Subscription::where('status', 'completed')->count(),
        ];
    }

    /**
     * Mở modal xem chi tiết hợp đồng VIP (Read-Only)
     */
    public function openDetail(int $id): void
    {
        $this->selectedSubscription = Subscription::with([
            'user',
            'deliverySchedules.order' => function ($q) {
                $q->select('id', 'order_code', 'status');
            },
        ])->find($id);

        if (!$this->selectedSubscription) {
            $this->dispatch('show-toast', type: 'error', message: 'Không tìm thấy hợp đồng!');
            return;
        }

        // Lấy mã đơn hàng thanh toán VIP thực tế từ bảng orders
        $this->selectedOrderCode = \App\Models\Order::where('user_id', $this->selectedSubscription->user_id)
            ->where('order_type', 'subscription')
            ->latest('id')
            ->value('order_code') ?? (string) $this->selectedSubscription->id;

        // Tối ưu nạp thông tin kèm ảnh sản phẩm cho 8 đợt giao (tránh N+1 query)
        $schedules = $this->selectedSubscription->deliverySchedules->sortBy('delivery_order');
        $allProdIds = [];
        foreach ($schedules as $s) {
            foreach (($s->items ?? []) as $it) {
                $pId = $it['product_id'] ?? null;
                if ($pId) {
                    $allProdIds[] = (int) $pId;
                }
            }
        }

        $productsKeyed = !empty($allProdIds)
            ? Product::with('productImages')->whereIn('id', array_unique($allProdIds))->get()->keyBy('id')
            : collect();

        $this->enrichedSchedules = [];
        foreach ($schedules as $s) {
            $itemsDetail = [];
            $totalWeight = 0.0;

            foreach (($s->items ?? []) as $it) {
                $pId = $it['product_id'] ?? null;
                $qty = (int) ($it['quantity'] ?? 1);
                $prod = $pId ? $productsKeyed->get($pId) : null;

                $imgUrl = $prod?->cover_image_url
                    ?? 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=100';

                $weight = isset($it['weight']) ? (float) $it['weight'] : round($qty * ($prod?->weight_kg ?? 0.5), 2);
                $totalWeight += $weight;

                $itemsDetail[] = [
                    'name'     => $it['product_name'] ?? $prod?->name ?? 'Rau hữu cơ',
                    'quantity' => $qty,
                    'unit'     => $it['unit'] ?? $prod?->unit ?? 'kg',
                    'weight'   => $weight,
                    'image'    => $imgUrl,
                ];
            }

            $this->enrichedSchedules[] = [
                'order'         => $s->delivery_order,
                'date'          => $s->delivery_date?->format('d/m/Y') ?? 'Chưa đặt',
                'day_of_week'   => $s->delivery_date?->format('l') ?? '',
                'status'        => $s->status ?? 'pending',
                'order_code'    => $s->order?->order_code ?? null,
                'total_weight'  => round($totalWeight, 1),
                'items'         => $itemsDetail,
            ];
        }

        $this->showDetailModal = true;
    }

    /**
     * Đóng modal chi tiết
     */
    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedSubscription = null;
        $this->selectedOrderCode = null;
        $this->enrichedSchedules = [];
    }

    public function render()
    {
        $query = Subscription::with([
            'user',
            'deliverySchedules.order' => function ($q) {
                $q->select('id', 'order_code', 'status');
            },
        ]);

        // Lọc theo Tab trạng thái
        if ($this->statusFilter === 'active') {
            $query->where('status', 'active');
        } elseif ($this->statusFilter === 'completed') {
            $query->where('status', 'completed');
        } elseif ($this->statusFilter === 'today') {
            $query->whereHas('deliverySchedules', function ($q) {
                $q->where('status', 'processing');
            });
        }

        // Lọc theo từ khóa tìm kiếm (Chỉ tìm theo mã đơn hàng VIP hiển thị ngoài bảng)
        $rawSearch = ltrim(trim($this->search), '#');
        if ($rawSearch !== '') {
            $matchingUserIds = \App\Models\Order::where('order_type', 'subscription')
                ->where('order_code', 'like', '%' . $rawSearch . '%')
                ->pluck('user_id');

            $query->whereIn('user_id', $matchingUserIds);
        }

        $subscriptions = $query->latest('id')->paginate($this->perPage);

        // Lấy map mã đơn hàng VIP thực tế theo user_id để hiển thị ngoài bảng
        $vipOrderCodes = \App\Models\Order::where('order_type', 'subscription')
            ->whereIn('user_id', $subscriptions->pluck('user_id')->unique())
            ->latest('id')
            ->pluck('order_code', 'user_id');

        return view('livewire.sales.subscription-manager', [
            'subscriptions' => $subscriptions,
            'vipOrderCodes' => $vipOrderCodes,
            'statusCounts'  => $this->statusCounts,
        ]);
    }
}
