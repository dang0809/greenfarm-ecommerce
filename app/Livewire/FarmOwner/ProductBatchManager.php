<?php

namespace App\Livewire\FarmOwner;

use App\Models\ProductBatch;
use Livewire\WithPagination;

class ProductBatchManager extends OwnerPage
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
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