<?php

namespace App\Console\Commands;

use App\Models\ProductBatch;
use Illuminate\Console\Command;

class SyncFlashSaleBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batches:sync-flash-sale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động nâng mức giảm Flash Sale lên 50% khi lô cận hạn (<= 1 ngày) hoặc tắt sale nếu hết hạn / ngừng bán';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $saleBatches = ProductBatch::with('plantingBatch')
            ->where('discount_percent', '>', 0)
            ->get();

        $affectedProductIds = [];
        $updatedCount = 0;

        foreach ($saleBatches as $batch) {
            if (!$batch->expiry_date) {
                continue;
            }

            $daysLeft = today()->diffInDays($batch->expiry_date, false);
            $newDiscount = $batch->discount_percent;

            if ($batch->status !== 'active' || $daysLeft < 0) {
                $newDiscount = 0;
            } elseif ($daysLeft <= 1 && $batch->discount_percent < 50) {
                $newDiscount = 50;
            }

            if ($newDiscount !== $batch->discount_percent) {
                $batch->update(['discount_percent' => $newDiscount]);
                $updatedCount++;

                if ($batch->plantingBatch?->product_id) {
                    $affectedProductIds[$batch->plantingBatch->product_id] = true;
                }
            }
        }

        foreach (array_keys($affectedProductIds) as $productId) {
            ProductBatch::syncProductSalePrice($productId);
        }

        if ($updatedCount > 0) {
            $this->info("Đã đồng bộ Flash Sale cho {$updatedCount} lô hàng và " . count($affectedProductIds) . " sản phẩm liên quan.");
        }

        return self::SUCCESS;
    }
}
