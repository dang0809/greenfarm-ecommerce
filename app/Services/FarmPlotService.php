<?php

namespace App\Services;

use App\Models\FarmPlot;
use App\Models\InventoryLog;
use App\Models\PlantingBatch;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\TraceabilityEvent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class FarmPlotService
{
    public function sow(
        FarmPlot $plot,
        ?int $productId,
        string $plantedDate,
        User $staff
    ): PlantingBatch {
        $staff = $this->requireStaff($staff);

        $validated = $this->validateData([
            'sowProductId' => $productId,
            'sowDate' => $plantedDate,
        ], [
            'sowProductId' => ['required', 'integer'],
            'sowDate' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
        ]);

        return DB::transaction(function () use (
            $plot,
            $validated,
            $staff
        ) {
            $lockedPlot = FarmPlot::whereKey($plot->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPlot->status !== 'active') {
                $this->reject(
                    'sowProductId',
                    'Luống đang bảo trì, không thể xuống giống.'
                );
            }

            $occupying = $lockedPlot->plantingBatches()
                ->occupying()
                ->lockForUpdate()
                ->get(['id']);

            if ($occupying->isNotEmpty()) {
                $this->reject(
                    'sowProductId',
                    'Luống đang có cây trồng. Hãy kết thúc vụ hiện tại trước.'
                );
            }

            $product = Product::whereKey($validated['sowProductId'])
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $product) {
                $this->reject(
                    'sowProductId',
                    'Giống trồng không tồn tại hoặc đã ngừng sử dụng.'
                );
            }

            $lastHarvestDate = ProductBatch::whereHas(
                'plantingBatch',
                fn ($query) => $query->where(
                    'farm_plot_id',
                    $lockedPlot->id
                )
            )->max('manufactured_date');

            if (
                $lastHarvestDate
                && $validated['sowDate'] < $lastHarvestDate
            ) {
                $this->reject(
                    'sowDate',
                    'Ngày xuống giống không được trước lần thu hoạch gần nhất của luống.'
                );
            }

            $batch = PlantingBatch::create([
                'farm_plot_id' => $lockedPlot->id,
                'product_id' => $product->id,
                'planting_code' => 'TRONG-'
                    . $lockedPlot->id . '-'
                    . now()->format('Ymd') . '-'
                    . strtoupper((string) Str::uuid()),
                'planted_date' => $validated['sowDate'],
                'status' => 'planting',
            ]);

            $this->appendEvent(
                $batch,
                'Xuống giống',
                "Xuống giống {$product->name} tại {$lockedPlot->name}. "
                    . "Ngày xuống giống: {$validated['sowDate']}.",
                null,
                null,
                $staff
            );

            return $batch;
        });
    }

    public function recordEvent(
        PlantingBatch $batch,
        array $data,
        ?UploadedFile $photo,
        User $staff
    ): TraceabilityEvent {
        $staff = $this->requireStaff($staff);

        $validated = $this->validateData([
            'logName' => trim((string) ($data['event_name'] ?? '')),
            'logDesc' => trim((string) ($data['description'] ?? '')),
            'logPhoto' => $photo,
        ], [
            'logName' => ['required', 'string', 'min:3', 'max:100'],
            'logDesc' => ['required', 'string', 'min:5', 'max:10000'],
            'logPhoto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $imagePath = null;

        try {
            return DB::transaction(function () use (
                $batch,
                $validated,
                $photo,
                $staff,
                &$imagePath
            ) {
                $lockedBatch = $this->lockBatch($batch->id);
                $this->requireOpenBatch($lockedBatch, 'logName');

                $imageHash = $this->savePhoto($photo, $imagePath);

                return $this->appendEvent(
                    $lockedBatch,
                    $validated['logName'],
                    $validated['logDesc'],
                    $imagePath,
                    $imageHash,
                    $staff
                );
            });
        } catch (Throwable $exception) {
            $this->cleanupPhoto($imagePath);

            throw $exception;
        }
    }

    public function harvest(
        PlantingBatch $batch,
        array $data,
        ?UploadedFile $photo,
        User $staff
    ): ProductBatch {
        $staff = $this->requireStaff($staff);

        $validated = $this->validateData([
            'harvestDate' => $data['harvest_date'] ?? null,
            'harvestExpiry' => $data['expiry_date'] ?? null,
            'harvestQty' => $data['quantity'] ?? null,
            'harvestNote' => trim((string) ($data['note'] ?? '')),
            'harvestMode' => $data['mode'] ?? null,
            'harvestToken' => $data['token'] ?? null,
            'harvestPhoto' => $photo,
        ], [
            'harvestDate' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'harvestExpiry' => [
                'required',
                'date_format:Y-m-d',
                'after:harvestDate',
                'after_or_equal:today',
            ],
            'harvestQty' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:100000',
            ],
            'harvestNote' => ['nullable', 'string', 'max:1000'],
            'harvestMode' => ['required', 'in:inherit,finish'],
            'harvestToken' => ['required', 'uuid'],
            'harvestPhoto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);

        $imagePath = null;

        try {
            return DB::transaction(function () use (
                $batch,
                $validated,
                $photo,
                $staff,
                &$imagePath
            ) {
                $lockedBatch = $this->lockBatch($batch->id);

                // Trả lại kết quả cũ khi gửi lại cùng một form.
                $existing = ProductBatch::where(
                    'harvest_token',
                    $validated['harvestToken']
                )->lockForUpdate()->first();

                if ($existing) {
                    if (
                        (int) $existing->planting_batch_id
                        !== (int) $lockedBatch->id
                    ) {
                        $this->reject(
                            'harvestQty',
                            'Mã thao tác thu hoạch không hợp lệ.'
                        );
                    }

                    return $existing;
                }

                $this->requireOpenBatch($lockedBatch, 'harvestQty');

                if (
                    $lockedBatch->planted_date
                    && $validated['harvestDate']
                        < $lockedBatch->planted_date->toDateString()
                ) {
                    $this->reject(
                        'harvestDate',
                        'Ngày thu hoạch không được trước ngày xuống giống.'
                    );
                }

                $quantity = round((float) $validated['harvestQty'], 2);
                $imageHash = $this->savePhoto($photo, $imagePath);

                $productBatch = ProductBatch::create([
                    'planting_batch_id' => $lockedBatch->id,
                    'harvest_token' => $validated['harvestToken'],
                    'batch_code' => 'TH-'
                        . now()->format('Ymd') . '-'
                        . strtoupper((string) Str::uuid()),
                    'status' => 'active',
                    'manufactured_date' => $validated['harvestDate'],
                    'expiry_date' => $validated['harvestExpiry'],
                    'initial_quantity' => $quantity,
                    'current_quantity' => $quantity,
                    'spoiled_quantity' => 0,
                    'discount_percent' => 0,
                ]);

                InventoryLog::create([
                    'batch_id' => $productBatch->id,
                    'type' => 'import',
                    'quantity' => $quantity,
                    'note' => "Thu hoạch từ {$lockedBatch->planting_code}",
                    'created_by' => $staff->id,
                ]);

                $description = "Thu hoạch {$quantity} kg. "
                    . "Ngày thu hái: {$validated['harvestDate']}. "
                    . "Lô thương mại: {$productBatch->batch_code}.";

                if ($validated['harvestNote'] !== '') {
                    $description .= " Ghi chú: {$validated['harvestNote']}";
                }

                $description .= $validated['harvestMode'] === 'finish'
                    ? ' Kết thúc vụ và dọn luống.'
                    : ' Tiếp tục chăm sóc cây trên luống.';

                $this->appendEvent(
                    $lockedBatch,
                    'Thu hoạch',
                    $description,
                    $imagePath,
                    $imageHash,
                    $staff
                );

                $lockedBatch->update([
                    'status' => $validated['harvestMode'] === 'finish'
                        ? 'completed'
                        : 'harvesting',
                ]);

                return $productBatch;
            });
        } catch (Throwable $exception) {
            $this->cleanupPhoto($imagePath);

            throw $exception;
        }
    }

    public function verifyEvent(
        TraceabilityEvent $event,
        User $staff
    ): ?bool {
        $this->requireStaff($staff);

        $dataValid = $event->hasValidHash();

        if ($dataValid !== true) {
            return $dataValid;
        }

        if (! $event->image) {
            return true;
        }

        if (
            ! $event->image_sha256
            || ! Storage::disk('public')->exists($event->image)
        ) {
            return false;
        }

        return hash_equals(
            $event->image_sha256,
            $this->fileHash($event->image)
        );
    }

    private function lockBatch(int $batchId): PlantingBatch
    {
        $plotId = PlantingBatch::whereKey($batchId)
            ->value('farm_plot_id');

        abort_unless($plotId, 404);

        // Luôn khóa theo cùng thứ tự: luống trước, lô trồng sau.
        FarmPlot::whereKey($plotId)
            ->lockForUpdate()
            ->firstOrFail();

        return PlantingBatch::whereKey($batchId)
            ->where('farm_plot_id', $plotId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function requireOpenBatch(
        PlantingBatch $batch,
        string $field
    ): void {
        if (! in_array(
            $batch->status,
            ['planting', 'harvesting'],
            true
        )) {
            $this->reject(
                $field,
                'Vụ trồng đã kết thúc. Vui lòng tải lại thông tin luống.'
            );
        }

        $occupying = PlantingBatch::where(
            'farm_plot_id',
            $batch->farm_plot_id
        )->occupying()->lockForUpdate()->get(['id']);

        if (
            $occupying->count() !== 1
            || (int) $occupying->first()->id !== (int) $batch->id
        ) {
            $this->reject(
                $field,
                'Dữ liệu luống có nhiều vụ đang hoạt động. Cần kiểm tra lại trước khi thao tác.'
            );
        }
    }

    private function appendEvent(
        PlantingBatch $batch,
        string $name,
        string $description,
        ?string $imagePath,
        ?string $imageHash,
        User $staff
    ): TraceabilityEvent {
        $event = new TraceabilityEvent([
            'planting_batch_id' => $batch->id,
            'event_name' => $name,
            'description' => $description,
            'image' => $imagePath,
            'image_sha256' => $imageHash,
            'recorded_by' => $staff->name,
            'recorded_at' => now()->startOfSecond(),
            'hash_version' => 1,
        ]);

        $event->data_hash = $event->calculateDataHash();
        $event->save();

        return $event;
    }

    private function savePhoto(
        ?UploadedFile $photo,
        ?string &$imagePath
    ): ?string {
        if (! $photo) {
            return null;
        }

        $storedPath = $photo->store('farm-logs', 'public');

        if (! is_string($storedPath) || $storedPath === '') {
            throw new RuntimeException('Không thể lưu ảnh nhật ký.');
        }

        $imagePath = $storedPath;

        if (! Storage::disk('public')->exists($imagePath)) {
            throw new RuntimeException('Ảnh nhật ký chưa được lưu thành công.');
        }

        return $this->fileHash($imagePath);
    }

    private function fileHash(string $path): string
    {
        $contents = Storage::disk('public')->get($path);

        if (! is_string($contents)) {
            throw new RuntimeException('Không thể đọc ảnh nhật ký.');
        }

        return hash('sha256', $contents);
    }

    private function cleanupPhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            Storage::disk('public')->delete($path);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function requireStaff(User $staff): User
    {
        $freshUser = $staff->fresh();
        $role = $freshUser?->role;

        if ($role instanceof \BackedEnum) {
            $role = $role->value;
        }

        abort_unless(
            $freshUser
                && $freshUser->status === 'active'
                && $role === 'farm_staff',
            403,
            'Bạn không có quyền thao tác tại nông trại.'
        );

        return $freshUser;
    }

    private function validateData(array $data, array $rules): array
    {
        return Validator::make($data, $rules, [
            'required' => ':attribute là bắt buộc.',
            'integer' => ':attribute không hợp lệ.',
            'numeric' => ':attribute phải là số.',
            'date_format' => ':attribute không đúng định dạng ngày.',
            'before_or_equal' => ':attribute không được ở tương lai.',
            'after' => ':attribute phải sau ngày thu hoạch.',
            'after_or_equal' => ':attribute không được trước hôm nay.',
            'decimal' => ':attribute chỉ nhận tối đa hai chữ số thập phân.',
            'min.numeric' => ':attribute phải từ :min trở lên.',
            'max.numeric' => ':attribute không được vượt quá :max.',
            'min.string' => ':attribute phải có ít nhất :min ký tự.',
            'max.string' => ':attribute tối đa :max ký tự.',
            'max.file' => ':attribute tối đa :max KB.',
            'image' => ':attribute phải là hình ảnh.',
            'mimes' => ':attribute chỉ nhận JPG, PNG hoặc WEBP.',
            'in' => ':attribute không hợp lệ.',
            'uuid' => 'Mã thao tác không hợp lệ. Vui lòng mở lại form.',
        ], [
            'sowProductId' => 'Giống trồng',
            'sowDate' => 'Ngày xuống giống',
            'logName' => 'Tên hoạt động',
            'logDesc' => 'Mô tả nhật ký',
            'logPhoto' => 'Ảnh nhật ký',
            'harvestDate' => 'Ngày thu hoạch',
            'harvestExpiry' => 'Hạn sử dụng',
            'harvestQty' => 'Sản lượng',
            'harvestNote' => 'Ghi chú',
            'harvestMode' => 'Hình thức thu hoạch',
            'harvestToken' => 'Mã thao tác',
            'harvestPhoto' => 'Ảnh thu hoạch',
        ])->validate();
    }

    private function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}