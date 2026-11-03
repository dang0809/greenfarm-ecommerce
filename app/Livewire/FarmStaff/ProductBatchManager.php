<?php

namespace App\Livewire\FarmStaff;

use App\Models\ProductBatch;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.farm-staff.app')]
#[Title('Quản Lý Lô Nông Sản (FEFO) | Nông Trại Đất Lành')]
class ProductBatchManager extends Component
{
    public function render()
    {
        // Lấy danh sách lô hàng theo thứ tự FEFO (hết hạn trước lên đầu)
        $batches = ProductBatch::with(['plantingBatch.product', 'plantingBatch.farmPlot'])
            ->orderBy('expiry_date', 'asc')
            ->get();

        return view('livewire.farm-staff.product-batch-manager', [
            'batches' => $batches,
        ]);
    }
}