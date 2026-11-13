<?php

namespace App\Livewire\FarmOwner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\ProductBatch;
use Livewire\WithPagination;

class ProductBatchManager extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function boot(): void
    {
        $owner = Auth::id()
            ? User::find(Auth::id())
            : null;

        abort_unless(
            $owner
            && $owner->role === 'farm_owner'
            && $owner->status === 'active',
            403,
            'Bạn không có quyền truy cập khu vực chủ trang trại.'
        );
    }

    public function render()
    {
        $batches = ProductBatch::query()
            ->with(['plantingBatch.product', 'plantingBatch.farmPlot'])
            ->search($this->search)
            ->latest('id')
            ->paginate(15);

        return view('livewire.farm-owner.product-batch-manager', [
            'batches' => $batches,
            'statusLabels' => [
                'active' => 'Đang bán',
                'near_expiry' => 'Cận hạn',
                'expired' => 'Hết hạn',
                'reserved' => 'Đã giữ chỗ hết',
                'out_of_stock' => 'Hết hàng',
                'suspended' => 'Tạm dừng',
                'pending_recalled' => 'Chờ tiêu hủy',
                'recalled' => 'Đã tiêu hủy',
            ],
        ])->layout('layouts.farm-owner.app', [
                    'title' => 'Danh sách lô hàng nông sản',
                ]);
    }
}