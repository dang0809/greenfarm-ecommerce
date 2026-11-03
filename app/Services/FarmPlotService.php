<?php

namespace App\Services;

use App\Models\FarmPlot;
use App\Models\InventoryLog;
use App\Models\PlantingBatch;
use App\Models\ProductBatch;
use App\Models\TraceabilityEvent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FarmPlotService
{
    /**
     * Xuống giống: tạo lô canh tác + ghi sự kiện truy xuất.
     */
    public function sow(FarmPlot $plot, int $productId, string $plantedDate, User $staff): PlantingBatch
    {
        if ($plot->status !== 'active') {
            throw ValidationException::withMessages([
                'sowProductId' => 'Luống đang bảo trì, không thể xuống giống.',
            ]);
        }

        return DB::transaction(function () use ($plot, $productId, $plantedDate, $staff) {
            // Chặn 2 lô cùng chiếm 1 luống
            if ($plot->plantingBatches()->occupying()->exists()) {
                throw ValidationException::withMessages([
                    'sowProductId' => 'Luống này đang có cây trồng. Hãy thu hoạch & dọn luống trước khi gieo vụ mới.',
                ]);
            }

            $batch = PlantingBatch::create([
                'farm_plot_id'  => $plot->id,
                'product_id'    => $productId,
                'planting_code' => $this->makePlantingCode($plot),
                'planted_date'  => $plantedDate,
                'status'        => 'planting',
            ]);

            $this->recordEvent($batch, [
                'event_name'  => 'Xuống giống',
                'description' => "Xuống giống tại {$plot->name}. Mã lô: {$batch->planting_code}.",
            ], null, $staff);

            return $batch;
        });
    }

    /**
     * Ghi nhật ký canh tác + băm SHA-256 (chuỗi băm tất định để đối chiếu sau này).
     */
    public function recordEvent(
        PlantingBatch $batch,
        array $data,
        ?UploadedFile $photo,
        User $staff
    ): TraceabilityEvent {
        $imagePath  = $photo?->store('farm-logs', 'public');
        $recordedAt = now();

        // Payload cố định thứ tự => hash luôn tái tạo được để kiểm chứng
        $payload = implode('|', [
            $batch->id,
            $batch->planting_code,
            $data['event_name'],
            $data['description'] ?? '',
            $recordedAt->toDateTimeString(),
            $staff->id,
        ]);

        return TraceabilityEvent::create([
            'planting_batch_id' => $batch->id,
            'event_name'        => Str::limit($data['event_name'], 100, ''),
            'description'       => $data['description'] ?? null,
            'image'             => $imagePath,
            'recorded_by'       => $staff->name,
            'recorded_at'       => $recordedAt,
            'data_hash'         => hash('sha256', $payload),
        ]);
    }

    /**
     * Thu hoạch: tạo lô thương mại + nhập kho FEFO + ghi nhật ký + cập nhật trạng thái lô.
     */
    public function harvest(
        PlantingBatch $batch,
        array $data,
        ?UploadedFile $photo,
        User $staff
    ): ProductBatch {
        $qty = round((float) $data['quantity'], 2);

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'harvestQty' => 'Sản lượng thu hoạch phải lớn hơn 0 kg.',
            ]);
        }

        return DB::transaction(function () use ($batch, $data, $photo, $staff, $qty) {
            $productBatch = ProductBatch::create([
                'planting_batch_id' => $batch->id,
                'batch_code'        => $this->makeProductBatchCode(),
                'status'            => 'active',
                'manufactured_date' => $data['harvest_date'],
                'expiry_date'       => $data['expiry_date'],
                'initial_quantity'  => $qty,
                'current_quantity'  => $qty,
                'spoiled_quantity'  => 0,
                'discount_percent'  => 0,
            ]);

            InventoryLog::create([
                'batch_id'   => $productBatch->id,
                'type'       => 'import',
                'quantity'   => $qty,
                'note'       => "Thu hoạch từ lô canh tác {$batch->planting_code}",
                'created_by' => $staff->id,
            ]);

            $this->recordEvent($batch, [
                'event_name'  => 'Thu hoạch',
                'description' => "Thu hái {$qty} kg, tạo lô thương mại {$productBatch->batch_code}."
                    . (! empty($data['note']) ? " Ghi chú: {$data['note']}" : ''),
            ], $photo, $staff);

            // finish => dọn luống (completed), inherit => cây vẫn nuôi tiếp
            $batch->update([
                'status' => ($data['mode'] ?? 'inherit') === 'finish' ? 'completed' : 'harvesting',
            ]);

            return $productBatch;
        });
    }

    private function makePlantingCode(FarmPlot $plot): string
    {
        do {
            $code = 'TRONG-' . $plot->id . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (PlantingBatch::where('planting_code', $code)->exists());

        return $code;
    }

    private function makeProductBatchCode(): string
    {
        do {
            $code = 'TH-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (ProductBatch::where('batch_code', $code)->exists());

        return $code;
    }
}