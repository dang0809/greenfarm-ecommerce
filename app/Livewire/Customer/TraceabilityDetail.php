<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\ProductBatch;

#[Title('Nhật Ký Canh Tác Lô Hàng')]
class TraceabilityDetail extends Component
{
    public string $batchCode;

    public function mount(string $batch): void
    {
        $this->batchCode = strtoupper(trim($batch));
    }

    public function render()
    {
        $productBatch = ProductBatch::with([
            'plantingBatch.product.productImages',
            'plantingBatch.farmPlot',
            'plantingBatch.traceabilityEvents' => fn($query) => $query
                ->orderBy('recorded_at')
                ->orderBy('id'),
        ])
            ->where('batch_code', $this->batchCode)
            ->firstOrFail();

        return view('livewire.customer.traceability-detail', [
            'productBatch' => $productBatch,
            'plantingBatch' => $productBatch->plantingBatch,
        ])->layout('layouts.customer.app');
    }
}