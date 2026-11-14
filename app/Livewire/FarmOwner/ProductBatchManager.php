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
            'statusLabels' => ProductBatch::STATUS_LABELS,
        ])->layout('layouts.farm-owner.app', [
                    'title' => 'Danh sách lô hàng nông sản',
                ]);
    }
}