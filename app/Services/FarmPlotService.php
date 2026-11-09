<?php

namespace App\Services;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
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
    public function createPlot(array $data, User $staff): FarmPlot
    {
        $this->requireStaff($staff);

        $area = $data['area'] ?? null;

        $validated = $this->validateData([
            'newPlotName' => trim((string) ($data['name'] ?? '')),
            'newPlotArea' => $area === '' ? null : $area,
        ], [
            'newPlotName' => [
                'required',
                'string',
                'max:255',
            ],
            'newPlotArea' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
                'max:9999999999.99',
            ],
        ]);

        return FarmPlot::create([
            'name' => $validated['newPlotName'],
            'area' => $validated['newPlotArea'] ?? null,
            'status' => 'available',
        ]);
    }

    public function sow(FarmPlot $plot, ?int $productId, string $plantedDate, User $staff): PlantingBatch
    {
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

        return DB::transaction(function () use ($plot, $validated, $staff) {
            $lockedPlot = FarmPlot::whereKey($plot->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                !in_array(
                    $lockedPlot->status,
                    ['available', 'active'],
                    true
                )
            ) {
                $this->reject(
                    'sowProductId',
                    'Luống chưa sẵn sàng để xuống giống.'
                );
            }

            $occupying = $lockedPlot->plantingBatches()
                ->whereIn('status', ['planting', 'harvesting'])
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

            if (!$product) {
                $this->reject(
                    'sowProductId',
                    'Giống trồng không tồn tại hoặc đã ngừng sử dụng.'
                );
            }

            $lastHarvestDate = ProductBatch::whereHas(
                'plantingBatch',
                fn($query) => $query->where(
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
                $staff
            );

            $lockedPlot->update([
                'status' => 'in-progress',
            ]);
            return $batch;
        });
    }

    public function validateLogData(
        array $data,
        ?UploadedFile $photo,
        PlantingBatch $batch
    ): array {
        $types = config('farm-log.activities', []);
        $fertilizers = config('farm-log.fertilizers', []);
        $pesticides = config('farm-log.pesticides', []);
        $methods = config('farm-log.fertilizer_methods', []);
        $sources = config('farm-log.water_sources', []);

        $type = $data['type'] ?? '';
        $details = $data['details'] ?? [];

        if (is_array($details)) {
            $details = array_map(
                fn($value) => is_string($value) ? trim($value) : $value,
                $details
            );
        }

        $description = $data['description'] ?? '';

        $rules = [
            'logType' => ['bail', 'required', 'string', Rule::in(array_keys($types))],
            'logDetails' => ['array'],
            'logPhoto' => ['bail', 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        $specificRules = match ($type) {
            'fertilizing' => [
                'logDetails.fertilizer_name' => ['required', 'string', Rule::in(array_keys($fertilizers))],
                'logDetails.fertilizer_dose' => ['required', 'string', 'max:100'],
                'logDetails.fertilizer_method' => ['required', 'string', Rule::in(array_keys($methods))],
            ],
            'spraying' => [
                'logDetails.pesticide_name' => ['required', 'string', Rule::in(array_keys($pesticides))],
                'logDetails.concentration' => ['required', 'string', 'max:100'],
                'logDetails.pest' => ['required', 'string', 'max:255'],
                'logDetails.sprayed_on' => ['bail', 'required', 'date_format:Y-m-d', 'before_or_equal:today'],
                'logDetails.isolation_days' => ['required', 'integer', 'min:0', 'max:365'],
            ],
            'watering' => [
                'logDetails.watering_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
                'logDetails.water_source' => ['required', 'string', Rule::in(array_keys($sources))],
            ],
            'pruning' => [
                'logDesc' => ['required', 'string', 'min:5', 'max:10000'],
            ],
            default => [],
        };

        $validated = Validator::make([
            'logType' => $type,
            'logDetails' => $details,
            'logDesc' => is_string($description) ? trim($description) : $description,
            'logPhoto' => $photo,
        ], array_merge($rules, $specificRules), [
            'required' => ':attribute là bắt buộc.',
            'in' => ':attribute không hợp lệ.',
            'string' => ':attribute phải là văn bản.',
            'integer' => ':attribute phải là số nguyên.',
            'array' => 'Dữ liệu hoạt động không hợp lệ.',
            'min.string' => ':attribute phải có ít nhất :min ký tự.',
            'max.string' => ':attribute tối đa :max ký tự.',
            'min.numeric' => ':attribute phải từ :min trở lên.',
            'max.numeric' => ':attribute không được vượt quá :max.',
            'date_format' => ':attribute không đúng định dạng ngày.',
            'before_or_equal' => ':attribute không được ở tương lai.',
            'image' => 'Tệp tải lên phải là ảnh.',
            'mimes' => 'Chỉ nhận ảnh JPG, JPEG, PNG hoặc WEBP.',
            'logPhoto.required' => 'Vui lòng tải ảnh minh chứng.',
            'logPhoto.max' => 'Ảnh nhật ký tối đa 2 MB.',
        ], [
            'logType' => 'Loại hoạt động',
            'logDesc' => 'Mô tả cắt tỉa',
            'logDetails.fertilizer_name' => 'Tên phân bón',
            'logDetails.fertilizer_dose' => 'Liều lượng',
            'logDetails.fertilizer_method' => 'Phương pháp bón',
            'logDetails.pesticide_name' => 'Tên thuốc',
            'logDetails.concentration' => 'Nồng độ pha',
            'logDetails.pest' => 'Đối tượng sâu bệnh',
            'logDetails.sprayed_on' => 'Ngày phun',
            'logDetails.isolation_days' => 'Thời gian cách ly',
            'logDetails.watering_minutes' => 'Thời lượng tưới',
            'logDetails.water_source' => 'Nguồn nước',
        ])->validate();

        $d = $validated['logDetails'] ?? [];

        if ($type === 'spraying') {
            if (
                $batch->planted_date
                && $d['sprayed_on'] < $batch->planted_date->toDateString()
            ) {
                $this->reject(
                    'logDetails.sprayed_on',
                    'Ngày phun không được trước ngày xuống giống.'
                );
            }

            $sprayedOn = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $d['sprayed_on'],
                config('app.timezone')
            );

            $isolationEnd = $sprayedOn
                ->addDays((int) $d['isolation_days'])
                ->format('d/m/Y');
        }

        $summary = match ($type) {
            'fertilizing' => implode("\n", [
                'Tên phân bón: ' . $fertilizers[$d['fertilizer_name']],
                'Liều lượng: ' . $d['fertilizer_dose'],
                'Phương pháp bón: ' . $methods[$d['fertilizer_method']],
            ]),
            'spraying' => implode("\n", [
                'Tên thuốc: ' . $pesticides[$d['pesticide_name']],
                'Nồng độ pha: ' . $d['concentration'],
                'Đối tượng sâu bệnh: ' . $d['pest'],
                'Ngày phun: ' . $sprayedOn->format('d/m/Y'),
                'Thời gian cách ly: ' . $d['isolation_days'] . ' ngày',
                'Ngày kết thúc cách ly: ' . $isolationEnd,
            ]),
            'watering' => implode("\n", [
                'Thời lượng tưới: ' . $d['watering_minutes'] . ' phút',
                'Nguồn nước: ' . $sources[$d['water_source']],
            ]),
            'pruning' => $validated['logDesc'],
        };

        return [
            'logName' => $types[$type],
            'logDesc' => $summary,
        ];
    }

    public function validateHarvestData(
        array $data,
        ?UploadedFile $photo,
        PlantingBatch $batch
    ): array {
        $validated = $this->validateData([
            'harvestDate' => $data['harvest_date'] ?? null,
            'harvestExpiry' => $data['expiry_date'] ?? null,
            'harvestQty' => $data['quantity'] ?? null,
            'harvestNote' => trim((string) ($data['note'] ?? '')),
            'harvestMode' => $data['mode'] ?? null,
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
                'integer',
                'min:1',
                'max:100000',
            ],
            'harvestNote' => ['nullable', 'string', 'max:1000'],
            'harvestMode' => ['required', 'in:inherit,finish'],
            'harvestPhoto' => [
                'bail',
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if (
            $batch->planted_date
            && $validated['harvestDate']
            < $batch->planted_date->toDateString()
        ) {
            $this->reject(
                'harvestDate',
                'Ngày thu hoạch không được trước ngày xuống giống.'
            );
        }

        return $validated;
    }

    public function recordEvent(
        PlantingBatch $batch,
        array $data,
        ?UploadedFile $photo,
        User $staff
    ): TraceabilityEvent {
        $staff = $this->requireStaff($staff);

        

        $imagePath = null;

        try {
            return DB::transaction(function () use ($batch, $data, $photo, $staff, &$imagePath) {
                $lockedBatch = $this->lockBatch($batch->id);
                $this->requireOpenBatch($lockedBatch, 'logType');

                $validated = $this->validateLogData($data, $photo, $lockedBatch);

                $this->savePhoto($photo, $imagePath);

                return $this->appendEvent(
                    $lockedBatch,
                    $validated['logName'],
                    $validated['logDesc'],
                    $imagePath,
                    $staff
                );
            });
        } catch (Throwable $exception) {
            $this->cleanupPhoto($imagePath);

            throw $exception;
        }
    }

    public function harvest(PlantingBatch $batch, array $data, ?UploadedFile $photo, User $staff): ProductBatch
    {
        $staff = $this->requireStaff($staff);

        $validated = $this->validateHarvestData(
            $data,
            $photo,
            $batch
        );

        $imagePath = null;

        try {
            return DB::transaction(function () use ($batch, $validated, $photo, $staff, &$imagePath) {
                $lockedBatch = $this->lockBatch($batch->id);

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

                $quantity = (int) $validated['harvestQty'];

                $this->savePhoto($photo, $imagePath);

                // Sinh mã lô thu hoạch
                $batchCode = 'TH-'
                    . now()->format('Ymd') . '-'
                    . strtoupper((string) Str::uuid());

                $productBatch = ProductBatch::create([
                    'planting_batch_id' => $lockedBatch->id,
                    'batch_code' => $batchCode,
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
                    $staff
                );

                $lockedBatch->update([
                    'status' => $validated['harvestMode'] === 'finish'
                        ? 'completed'
                        : 'harvesting',
                ]);

                FarmPlot::whereKey($lockedBatch->farm_plot_id)
                    ->whereIn('status', ['available', 'active', 'in-progress'])
                    ->update([
                        'status' => $validated['harvestMode'] === 'finish'
                            ? 'available'
                            : 'in-progress',
                    ]);

                return $productBatch;
            });
        } catch (Throwable $exception) {
            $this->cleanupPhoto($imagePath);

            throw $exception;
        }
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
        if (
            !in_array(
                $batch->status,
                ['planting', 'harvesting'],
                true
            )
        ) {
            $this->reject(
                $field,
                'Vụ trồng đã kết thúc. Vui lòng tải lại thông tin luống.'
            );
        }

        $occupying = PlantingBatch::where(
            'farm_plot_id',
            $batch->farm_plot_id
        )->whereIn('status', ['planting', 'harvesting'])->lockForUpdate()->get(['id']);

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
        User $staff
    ): TraceabilityEvent {
        return TraceabilityEvent::create([
            'planting_batch_id' => $batch->id,
            'event_name' => $name,
            'description' => $description,
            'image' => $imagePath,
            'recorded_by' => $staff->name,
            'recorded_at' => now()->startOfSecond(),
        ]);
    }

    private function savePhoto(?UploadedFile $photo, ?string &$imagePath): void
    {
        if ($photo === null) {
            return;
        }

        $storedPath = $photo->store('farm-logs', 'public');

        if (!is_string($storedPath) || $storedPath === '') {
            throw new RuntimeException(
                'Không thể lưu ảnh nhật ký.'
            );
        }

        // Gán sớm để cleanupPhoto() có thể dọn file nếu bước sau lỗi.
        $imagePath = $storedPath;

        if (!Storage::disk('public')->exists($imagePath)) {
            throw new RuntimeException(
                'Ảnh nhật ký chưa được lưu thành công.'
            );
        }
    }

    private function cleanupPhoto(?string $path): void
    {
        if (!$path) {
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
            'date_format' => ':attribute không đúng định dạng ngày.',
            'before_or_equal' => ':attribute không được ở tương lai.',
            'after' => ':attribute phải sau ngày thu hoạch.',
            'after_or_equal' => ':attribute không được trước hôm nay.',
            'min.numeric' => ':attribute phải từ :min trở lên.',
            'max.numeric' => ':attribute không được vượt quá :max.',
            'min.string' => ':attribute phải có ít nhất :min ký tự.',
            'max.string' => ':attribute tối đa :max ký tự.',
            'max.file' => ':attribute tối đa :max KB.',
            'image' => ':attribute phải là hình ảnh.',
            'mimes' => ':attribute chỉ nhận JPG, PNG hoặc WEBP.',
            'in' => ':attribute không hợp lệ.',
            'newPlotName.string' => 'Tên luống không hợp lệ.',
            'newPlotArea.numeric' => 'Diện tích phải là số.',
            'newPlotArea.decimal' => 'Diện tích chỉ nhận tối đa hai chữ số thập phân.',
            'newPlotArea.gt' => 'Diện tích phải lớn hơn 0.',
            'newPlotArea.max' => 'Diện tích vượt quá giới hạn cho phép.',
        ], [
            'sowProductId' => 'Giống trồng',
            'sowDate' => 'Ngày xuống giống',
            'logName' => 'Tên hoạt động',
            'logDesc' => 'Mô tả nhật ký',
            'logPhoto' => 'Ảnh nhật ký',
            'logPhoto.required' => 'Vui lòng tải ảnh minh chứng nhật ký.',
            'logPhoto.max' => 'Ảnh nhật ký tối đa 2 MB.',
            'harvestPhoto.required' => 'Vui lòng tải ảnh thu hoạch thực tế.',
            'harvestPhoto.max' => 'Ảnh thu hoạch tối đa 4 MB.',
            'harvestDate' => 'Ngày thu hoạch',
            'harvestExpiry' => 'Hạn sử dụng',
            'harvestQty' => 'Sản lượng',
            'harvestNote' => 'Ghi chú',
            'harvestMode' => 'Hình thức thu hoạch',
            'harvestPhoto' => 'Ảnh thu hoạch',
            'newPlotName' => 'Tên luống',
            'newPlotArea' => 'Diện tích',
        ])->validate();
    }

    private function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}