<?php

namespace App\Services;

use App\Models\ProductBatch;

class ProductBatchStatusService
{
    public function suspendExpiredBatches(): int
    {
        return ProductBatch::query()
            ->where('status', 'active')
            ->whereDate(
                'expiry_date',
                '<',
                today()->toDateString()
            )
            ->update([
                'status' => 'suspended',
            ]);
    }
}