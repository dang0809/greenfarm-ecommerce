<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\ProductBatch;

#[Title('Nhật Ký Canh Tác Lô Hàng | Nông Trại Đất Lành')]
class TraceabilityDetail extends Component
{
    public string $batchCode;

    public function mount(string $batch)
    {
        $this->batchCode = $batch;
    }

    public function render()
    {
        // Truy vấn theo Mã lô sản phẩm (batch_code), nạp Lô trồng (plantingBatch), Mảnh đất (farmPlot), Sản phẩm & Nhật ký
        $productBatch = ProductBatch::with([
            'plantingBatch.product.productImages',
            'plantingBatch.farmPlot',
            'plantingBatch.traceabilityEvents'
        ])
        ->where('batch_code', $this->batchCode)
        ->first();

        return view('livewire.customer.traceability-detail', [
            'productBatch'  => $productBatch,
            'plantingBatch' => $productBatch?->plantingBatch,
        ])->layout('layouts.customer.app');
    }
}