<?php

namespace App\Livewire\Sales;

use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sales.app')]
#[Title('Quản Lý Xả Hàng Nông Sản | Nông Trại Đất Lành')]
class ClearanceManager extends Component
{
    use WithPagination;

    // Bộ lọc & Tìm kiếm
    public string $tabFilter = 'all'; // all, on_sale, not_sale
    public string $search = '';
    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTabFilter(): void
    {
        $this->resetPage();
    }

    public function setTabFilter(string $tab): void
    {
        $this->tabFilter = $tab;
        $this->resetPage();
    }

    /**
     * Bật Flash Sale cho một lô cận date
     * Quy tắc:
     * - Còn <= 1 ngày (hoặc hết hạn hôm nay): Giảm cố định 50%
     * - Còn 2 ngày: Giảm cố định 30%
     */
    public function enableFlashSale(int $batchId): void
    {
        $batch = ProductBatch::with('plantingBatch.product')->find($batchId);

        if (!$batch) {
            $this->dispatch('show-toast', type: 'error', message: 'Lô hàng không tồn tại!');
            return;
        }

        if ($batch->expiry_date->lt(today())) {
            $this->dispatch('show-toast', type: 'error', message: 'Lô hàng đã quá hạn sử dụng, không thể kích hoạt xả kho!');
            return;
        }

        // Tính số ngày còn lại (0 = hôm nay/1 ngày, 1 = ngày mai/2 ngày)
        $daysLeft = today()->diffInDays($batch->expiry_date, false);

        if ($daysLeft <= 1) {
            $discountPercent = 50;
        } elseif ($daysLeft <= 2) {
            $discountPercent = 30;
        } else {
            $this->dispatch('show-toast', type: 'warning', message: 'Lô hàng còn hạn trên 2 ngày, chưa thuộc diện xả cận date!');
            return;
        }

        $batch->discount_percent = $discountPercent;
        $batch->save();

        // Đồng bộ cập nhật sale_price của sản phẩm ra ngoài storefront
        if ($batch->plantingBatch?->product_id) {
            ProductBatch::syncProductSalePrice($batch->plantingBatch->product_id);
        }

        $this->dispatch('clearance-updated');
        $this->dispatch('show-toast', type: 'success', message: "Đã kích hoạt Flash Sale giảm {$discountPercent}% cho lô {$batch->batch_code}!");
    }

    /**
     * Tắt Flash Sale cho một lô (trả về giá gốc)
     */
    public function disableFlashSale(int $batchId): void
    {
        $batch = ProductBatch::with('plantingBatch')->find($batchId);

        if (!$batch) {
            $this->dispatch('show-toast', type: 'error', message: 'Lô hàng không tồn tại!');
            return;
        }

        $batch->discount_percent = 0;
        $batch->save();

        // Tự động kiểm tra và trả về giá gốc nếu không còn lô nào khác sale
        if ($batch->plantingBatch?->product_id) {
            ProductBatch::syncProductSalePrice($batch->plantingBatch->product_id);
        }

        $this->dispatch('clearance-updated');
        $this->dispatch('show-toast', type: 'info', message: "Đã tắt Flash Sale cho lô {$batch->batch_code}, đưa về giá gốc.");
    }

    /**
     * Bật Flash Sale nhanh cho TẤT CẢ các lô cận date đang chưa áp dụng
     */
    public function enableAllNearExpiry(): void
    {
        $batches = ProductBatch::with('plantingBatch')
            ->where('status', 'active')
            ->where('current_quantity', '>', 0)
            ->whereDate('expiry_date', '>=', today())
            ->whereDate('expiry_date', '<=', today()->addDays(2))
            ->where('discount_percent', 0)
            ->get();

        if ($batches->isEmpty()) {
            $this->dispatch('show-toast', type: 'warning', message: 'Không có lô cận date nào chưa áp dụng Flash Sale!');
            return;
        }

        $affectedProductIds = [];
        $count = 0;
        foreach ($batches as $batch) {
            $daysLeft = today()->diffInDays($batch->expiry_date, false);
            $discountPercent = ($daysLeft <= 1) ? 50 : 30;

            $batch->discount_percent = $discountPercent;
            $batch->save();

            if ($batch->plantingBatch?->product_id) {
                $affectedProductIds[$batch->plantingBatch->product_id] = true;
            }
            $count++;
        }

        foreach (array_keys($affectedProductIds) as $productId) {
            ProductBatch::syncProductSalePrice($productId);
        }

        $this->dispatch('clearance-updated');
        $this->dispatch('show-toast', type: 'success', message: "Đã bật Flash Sale thành công cho {$count} lô cận date!");
    }

    public function render()
    {
        // 1. Query cơ sở: Lô active, còn tồn kho khả dụng, hạn dùng trong vòng 2 ngày (<= 48h)
        $baseQuery = ProductBatch::with([
                'plantingBatch.product.productImages',
                'plantingBatch.product.category'
            ])
            ->where('status', 'active')
            ->where('current_quantity', '>', 0)
            ->whereDate('expiry_date', '>=', today())
            ->whereDate('expiry_date', '<=', today()->addDays(2));

        // 2. Thống kê số liệu 3 Thẻ KPI
        // Thẻ 1: Lô nguy cấp <= 1 ngày (Sale 50%)
        $urgentCount = (clone $baseQuery)
            ->whereDate('expiry_date', '<=', today()->addDay())
            ->count();

        // Thẻ 2: Lô cận date đúng 2 ngày (Sale 30%)
        $nearCount = (clone $baseQuery)
            ->whereDate('expiry_date', '>', today()->addDay())
            ->whereDate('expiry_date', '<=', today()->addDays(2))
            ->count();

        // Tổng tất cả lô cận date <= 2 ngày (cho Tab Tất cả)
        $totalCount = (clone $baseQuery)->count();

        $activeSaleBatches = (clone $baseQuery)
            ->where('discount_percent', '>', 0)
            ->get();

        $activeSaleCount = $activeSaleBatches->count();
        $activeSaleQty   = (int) $activeSaleBatches->sum(fn($b) => $b->available_quantity);

        // Số liệu cho các Tabs
        $notSaleCount = (clone $baseQuery)->where('discount_percent', 0)->count();

        // 3. Áp dụng bộ lọc Tabs
        $query = clone $baseQuery;

        if ($this->tabFilter === 'on_sale') {
            $query->where('discount_percent', '>', 0);
        } elseif ($this->tabFilter === 'not_sale') {
            $query->where('discount_percent', 0);
        }

        // 4. Áp dụng tìm kiếm
        $rawSearch = trim($this->search);
        if ($rawSearch !== '') {
            $query->where(function ($q) use ($rawSearch) {
                $q->where('batch_code', 'like', "%{$rawSearch}%")
                  ->orWhereHas('plantingBatch.product', function ($sub) use ($rawSearch) {
                      $sub->where('name', 'like', "%{$rawSearch}%");
                  });
            });
        }

        // FEFO: Lô hết hạn trước xếp lên đầu
        $batches = $query->orderBy('expiry_date', 'asc')->paginate($this->perPage);

        return view('livewire.sales.clearance-manager', [
            'batches'           => $batches,
            'urgentCount'       => $urgentCount,
            'nearCount'         => $nearCount,
            'totalCount'        => $totalCount,
            'activeSaleCount'   => $activeSaleCount,
            'activeSaleQty'     => $activeSaleQty,
            'notSaleCount'      => $notSaleCount,
        ]);
    }
}
