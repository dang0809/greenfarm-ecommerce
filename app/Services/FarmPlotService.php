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
    private const PHOTO_DISK = 'r2';
    private const PHOTO_DIRECTORY = 'farm-logs/r2';

    // Chuyển vụ đang trồng sang giai đoạn thu hoạch khi đến ngày tối thiểu.
    public function syncHarvestingStatuses(): int
    {
        return PlantingBatch::query()
            ->where('status', 'planting')
            ->whereNotNull('min_manufactured_date')
            ->where(
                'min_manufactured_date',
                '<=',
                today()->toDateString()
            )
            ->update([
                'status' => 'harvesting',
                'updated_at' => now(),
            ]);
    }

    // Validate dữ liệu
    public function validatePlotData(array $data): array
    {
        $area = $data['area'] ?? null;

        return $this->validateData([
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
    }

    // Tạo luống mới sau khi cải tạo đất
    public function createPlot(array $data, User $staff): FarmPlot
    {
        $this->requireStaff($staff);

        $validated = $this->validatePlotData($data);

        return FarmPlot::create([
            'name' => $validated['newPlotName'],
            'area' => $validated['newPlotArea'],
            // Luống mới phải cải tạo trước khi trồng.
            'status' => 'empty',
        ]);
    }

    public function completeMaintenance(
        FarmPlot $plot,
        User $staff
    ): FarmPlot {
        $this->requireStaff($staff);

        return DB::transaction(function () use ($plot) {
            $lockedPlot = FarmPlot::query()
                ->lockForUpdate()
                ->findOrFail($plot->id);

            if (
                !in_array(
                    $lockedPlot->status,
                    ['empty', 'maintenance'],
                    true
                )
            ) {
                $this->reject(
                    'plot',
                    'Luống hiện không ở trạng thái cần cải tạo.'
                );
            }

            $hasActiveBatch = $lockedPlot->plantingBatches()
                ->whereIn('status', ['planting', 'harvesting'])
                ->exists();

            if ($hasActiveBatch) {
                $this->reject(
                    'plot',
                    'Luống vẫn còn vụ đang canh tác.'
                );
            }

            $lockedPlot->update([
                'status' => 'available',
            ]);

            return $lockedPlot->fresh();
        });
    }

    // 	Kiểm tra sản phẩm, ngày xuống giống và ảnh minh chứng
    public function validateSowData(?int $productId, string $plantedDate, ?UploadedFile $photo): array
    {
        return $this->validateData([
            'sowProductId' => $productId,
            'sowDate' => $plantedDate,
            'sowPhoto' => $photo,
        ], [
            'sowProductId' => ['required', 'integer'],
            'sowDate' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'sowPhoto' => [
                'bail',
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);
    }

    // 	Khóa luống, tạo vụ trồng và ghi sự kiện xuống giống
    public function sow(FarmPlot $plot, ?int $productId, string $plantedDate, ?UploadedFile $photo, User $staff): PlantingBatch
    {
        $staff = $this->requireStaff($staff);

        // Kiểm tra lại khi người dùng xác nhận lưu.
        $validated = $this->validateSowData(
            $productId,
            $plantedDate,
            $photo
        );

        $imagePath = null;

        try {
            return DB::transaction(function () use ($plot, $validated, $photo, $staff, &$imagePath) {
                $lockedPlot = FarmPlot::whereKey($plot->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedPlot->status !== 'available') {
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

                $growthDays = (int) $product->min_growth_days;

                if ($growthDays < 1) {
                    $this->reject(
                        'sowProductId',
                        'Giống cây chưa có số ngày thu hoạch tối thiểu hợp lệ.'
                    );
                }

                $minManufacturedDate = CarbonImmutable::createFromFormat(
                    '!Y-m-d',
                    $validated['sowDate'],
                    config('app.timezone')
                )
                    ->addDays($growthDays)
                    ->toDateString();

                // Lưu ảnh thật trước khi tạo nhật ký.
                $this->savePhoto($photo, $imagePath);

                $batch = PlantingBatch::create([
                    'farm_plot_id' => $lockedPlot->id,
                    'product_id' => $product->id,
                    'planting_code' => 'TRONG-'
                        . $lockedPlot->id . '-'
                        . now()->format('Ymd') . '-'
                        . strtoupper((string) Str::uuid()),
                    'planted_date' => $validated['sowDate'],
                    'min_manufactured_date' => $minManufacturedDate,
                    'status' => 'planting',
                ]);

                $this->appendEvent(
                    $batch,
                    'Xuống giống',
                    "Xuống giống {$product->name} tại {$lockedPlot->name}. "
                    . "Ngày xuống giống: {$validated['sowDate']}.",
                    $imagePath,
                    $staff
                );

                $lockedPlot->update([
                    'status' => 'in-progress',
                ]);

                return $batch;
            });
        } catch (Throwable $exception) {
            // Transaction rollback thì dọn ảnh vừa lưu.
            $this->cleanupPhoto($imagePath);

            throw $exception;
        }
    }

    // Kiểm tra dữ liệu theo loại hoạt động và tạo nội dung nhật ký
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

    // Kiểm tra ngày thu hoạch, hạn dùng, sản lượng và điều kiện thu hoạch
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

        $this->requireHarvestable(
            $batch,
            $validated['harvestDate']
        );

        return $validated;
    }

    // Khóa vụ đang hoạt động và ghi nhật ký canh tác
    public function recordEvent(PlantingBatch $batch, array $data, ?UploadedFile $photo, User $staff): TraceabilityEvent
    {
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

    // 	Tạo lô thu hoạch, nhập kho và cập nhật trạng thái vụ, luống
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
                $this->requireHarvestable(
                    $lockedBatch,
                    $validated['harvestDate']
                );

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
                    ->whereIn('status', ['available', 'in-progress'])
                    ->update([
                        'status' => $validated['harvestMode'] === 'finish'
                            ? 'maintenance'
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

    // Kiểm tra trạng thái và ngày thu hoạch tối thiểu của vụ
    private function requireHarvestable(
        PlantingBatch $batch,
        string $harvestDate
    ): void {
        if ($batch->status !== 'harvesting') {
            $this->reject(
                'harvestDate',
                'Vụ trồng chưa ở giai đoạn thu hoạch.'
            );
        }

        $minimum = $batch->min_manufactured_date?->toDateString();

        if ($minimum === null) {
            $this->reject(
                'harvestDate',
                'Vụ trồng chưa có ngày thu hoạch tối thiểu.'
            );
        }

        if ($minimum > today()->toDateString()) {
            $this->reject(
                'harvestDate',
                'Vụ trồng chưa đến ngày thu hoạch tối thiểu.'
            );
        }

        if ($harvestDate < $minimum) {
            $this->reject(
                'harvestDate',
                'Ngày thu hoạch không được trước ngày thu hoạch tối thiểu.'
            );
        }
    }

    private function requireOpenBatch(PlantingBatch $batch, string $field): void
    {
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

    // Tạo sự kiện phục vụ truy xuất nhật ký
    private function appendEvent(PlantingBatch $batch, string $name, string $description, ?string $imagePath, User $staff): TraceabilityEvent
    {
        return TraceabilityEvent::create([
            'planting_batch_id' => $batch->id,
            'event_name' => $name,
            'description' => $description,
            'image' => $imagePath,
            'recorded_by' => $staff->name,
            'recorded_at' => now()->startOfSecond(),
        ]);
    }

    // 	Lưu ảnh R2
    private function savePhoto(
        ?UploadedFile $photo,
        ?string &$imagePath
    ): void {
        if ($photo === null) {
            throw new RuntimeException('Thiếu ảnh minh chứng.');
        }

        if (empty(config('filesystems.disks.r2.url'))) {
            throw new RuntimeException(
                'Chưa cấu hình URL công khai của R2.'
            );
        }

        $storedPath = $photo->store(
            self::PHOTO_DIRECTORY,
            self::PHOTO_DISK
        );

        if (!is_string($storedPath) || $storedPath === '') {
            throw new RuntimeException(
                'Không thể lưu ảnh nhật ký lên R2.'
            );
        }

        // Gán trước khi kiểm tra để catch có thể dọn file khi xảy ra lỗi.
        $imagePath = $storedPath;

        if (!Storage::disk(self::PHOTO_DISK)->exists($imagePath)) {
            throw new RuntimeException(
                'Ảnh nhật ký chưa được lưu thành công trên R2.'
            );
        }
    }

    private function cleanupPhoto(?string $path): void
    {
        if (
            !$path
            || !str_starts_with($path, self::PHOTO_DIRECTORY . '/')
            || str_contains($path, '..')
        ) {
            return;
        }

        try {
            // Giữ file nếu vẫn có nhật ký tham chiếu.
            if (TraceabilityEvent::where('image', $path)->exists()) {
                return;
            }

            if (!Storage::disk(self::PHOTO_DISK)->delete($path)) {
                throw new RuntimeException(
                    'Không thể dọn ảnh nhật ký trên R2.'
                );
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    // Kiểm tra tài khoản nhân viên còn hoạt động và đúng vai trò
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

    // Thực hiện validation dùng chung với thông báo theo trường dữ liệu
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

            'max.string' => ':attribute tối đa :max ký tự.',
            'max.file' => ':attribute tối đa :max KB.',
            'image' => ':attribute phải là hình ảnh.',
            'mimes' => ':attribute chỉ nhận JPG, JPEG, PNG hoặc WEBP.',
            'in' => ':attribute không hợp lệ.',

            'newPlotName.string' => 'Tên luống không hợp lệ.',
            'newPlotArea.numeric' => 'Diện tích phải là số.',
            'newPlotArea.decimal' => 'Diện tích chỉ nhận tối đa hai chữ số thập phân.',
            'newPlotArea.gt' => 'Diện tích phải lớn hơn 0.',
            'newPlotArea.max' => 'Diện tích vượt quá giới hạn cho phép.',
            'sowPhoto.required' => 'Vui lòng tải ảnh xuống giống.',
            'sowPhoto.image' => 'Tệp tải lên phải là ảnh.',
            'sowPhoto.mimes' => 'Chỉ nhận JPG, JPEG, PNG hoặc WEBP.',
            'sowPhoto.max' => 'Ảnh xuống giống tối đa 2 MB.',
            'harvestPhoto.required' => 'Vui lòng tải ảnh thu hoạch thực tế.',
            'harvestPhoto.max' => 'Ảnh thu hoạch tối đa 2 MB.',
        ], [
            'sowProductId' => 'Giống trồng',
            'sowDate' => 'Ngày xuống giống',
            'sowPhoto' => 'Ảnh xuống giống',
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

    // 	Ném lỗi validation cho trường dữ liệu tương ứng
    private function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}