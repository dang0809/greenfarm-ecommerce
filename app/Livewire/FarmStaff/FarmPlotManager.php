<?php

namespace App\Livewire\FarmStaff;

use App\Models\FarmPlot;
use App\Models\PlantingBatch;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use App\Services\FarmPlotService;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

#[Layout('layouts.farm-staff.app')]
#[Title('Quản Lý Luống Đất & Canh Tác | Nông Trại Đất Lành')]
class FarmPlotManager extends Component
{
    use WithFileUploads;

    public string $search = '';
    public string $filter = 'all';
    public string $activeTab = 'timeline';

    #[Locked]
    public ?int $selectedPlotId = null;

    #[Locked]
    public ?int $selectedBatchId = null;

    #[Locked]
    public ?int $sowPlotId = null;

    #[Locked]
    public ?int $logBatchId = null;

    #[Locked]
    public ?int $harvestBatchId = null;

    #[Locked]
    public ?int $qrBatchId = null;

    public bool $showSowModal = false;

    public bool $showCreatePlotModal = false;
    public string $newPlotName = '';
    public string $newPlotArea = '';
    public bool $showLogModal = false;
    public bool $showHarvestModal = false;

    public ?int $sowProductId = null;
    public string $sowDate = '';

    public string $logType = '';
    public array $logDetails = [];
    public string $logDesc = '';
    public $logPhoto = null;

    public string $harvestDate = '';
    public string $harvestExpiry = '';
    public $harvestQty = '';
    public string $harvestNote = '';
    public string $harvestMode = 'inherit';
    public $harvestPhoto = null;


    public function openCreatePlotModal(): void
    {
        $this->staff();
        $this->closeModals();

        $this->showCreatePlotModal = true;
    }

    public function closeCreatePlotModal(): void
    {
        $this->closeModals();
    }

    public function createPlot(FarmPlotService $service): void
    {
        abort_unless($this->showCreatePlotModal, 404);

        $this->attempt('newPlotName', function () use ($service) {
            $plot = $service->createPlot([
                'name' => $this->newPlotName,
                'area' => $this->newPlotArea,
            ], $this->staff());

            $this->closeModals();

            // Hiện lại danh sách để bộ lọc không che luống mới.
            $this->reset([
                'selectedPlotId',
                'selectedBatchId',
                'search',
                'filter',
                'activeTab',
            ]);

            $this->refreshData();

            $this->success(
                'Đã tạo luống!',
                "Luống {$plot->name} đã sẵn sàng để xuống giống."
            );
        });
    }

    public function requestSow(): void
    {
        $this->staff();

        abort_unless(
            $this->showSowModal && $this->sowPlotId,
            404
        );

        $this->dispatch(
            'confirm-farm-action',
            action: 'sow'
        )->self();
    }

    public function updatedLogType(): void
    {
        $this->reset(['logDetails', 'logDesc']);

        // Giữ ảnh và lỗi ảnh khi đổi loại hoạt động.
        $this->resetValidation(['logType', 'logDetails', 'logDetails.*', 'logDesc']);

        if ($this->logType === 'spraying') {
            $this->logDetails['sprayed_on'] = today()->toDateString();
        }
    }

    #[Computed]
    public function isolationEndDate(): ?string
    {
        if ($this->logType !== 'spraying') {
            return null;
        }

        $date = $this->logDetails['sprayed_on'] ?? null;
        $days = $this->logDetails['isolation_days'] ?? null;

        if (
            !is_string($date)
            || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)
            || (!is_int($days) && !is_string($days))
            || !preg_match('/^\d{1,3}$/D', (string) $days)
            || (int) $days > 365
        ) {
            return null;
        }

        $sprayedOn = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $date,
            new \DateTimeZone(config('app.timezone'))
        );

        $plantedOn = $this->selectedBatch?->planted_date?->toDateString();

        if (
            !$sprayedOn
            || $sprayedOn->format('Y-m-d') !== $date
            || $date > today()->toDateString()
            || ($plantedOn !== null && $date < $plantedOn)
        ) {
            return null;
        }

        return $sprayedOn->modify('+' . (int) $days . ' days')->format('d/m/Y');
    }

    private function logFormData(): array
    {
        return [
            'type' => $this->logType,
            'details' => $this->logDetails,
            'description' => $this->logDesc,
        ];
    }

    public function requestSaveLog(FarmPlotService $service): void
    {
        $this->staff();
        abort_unless($this->showLogModal && $this->logBatchId, 404);

        $this->resetValidation();

        $service->validateLogData(
            $this->logFormData(),
            $this->logPhoto,
            $this->batchForForm($this->logBatchId)
        );

        $this->dispatch('confirm-farm-action', action: 'log')->self();
    }

    public function requestHarvest(FarmPlotService $service): void
    {
        $this->staff();

        abort_unless(
            $this->showHarvestModal && $this->harvestBatchId,
            404
        );

        $this->resetValidation();

        $batch = $this->batchForForm($this->harvestBatchId);

        $service->validateHarvestData([
            'harvest_date' => $this->harvestDate,
            'expiry_date' => $this->harvestExpiry,
            'quantity' => $this->harvestQty,
            'note' => $this->harvestNote,
            'mode' => $this->harvestMode,
        ], $this->harvestPhoto, $batch);

        $this->dispatch(
            'confirm-farm-action',
            action: 'harvest'
        )->self();
    }
    public function boot(): void
    {
        $this->staff();
    }

    #[Computed]
    public function plots(): Collection
    {
        return FarmPlot::query()
            ->when(
                trim($this->search) !== '',
                fn($query) => $query->where(
                    'name',
                    'like',
                    '%' . trim($this->search) . '%'
                )
            )
            ->when(
                $this->filter === 'active',
                fn($query) => $query->whereHas(
                    'plantingBatches',
                    fn($batches) => $batches->whereIn('status', ['planting', 'harvesting'])
                )
            )
            ->when(
                $this->filter === 'empty',
                fn($query) => $query
                    ->whereIn('status', ['available', 'active'])
                    ->whereDoesntHave(
                        'plantingBatches',
                        fn($batches) => $batches->whereIn('status', ['planting', 'harvesting'])
                    )
            )
            ->when(
                $this->filter === 'maintenance',
                fn($query) => $query->where('status', 'maintenance')
            )
            ->with([
                'plantingBatches' => fn($query) => $query
                    ->whereIn('status', ['planting', 'harvesting'])
                    ->with('product')
                    ->latest('id'),
            ])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function products(): Collection
    {
        return Product::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'unit']);
    }

    #[Computed]
    public function selectedPlot(): ?FarmPlot
    {
        if (!$this->selectedPlotId) {
            return null;
        }

        return FarmPlot::with([
            'plantingBatches' => fn($query) => $query
                ->whereIn('status', ['planting', 'harvesting'])
                ->with('product')
                ->latest('id'),
        ])->find($this->selectedPlotId);
    }

    #[Computed]
    public function currentBatch(): ?PlantingBatch
    {
        $plot = $this->selectedPlot;

        return $plot !== null
            ? $this->activeBatch($plot)
            : null;
    }

    #[Computed]
    public function selectedBatch(): ?PlantingBatch
    {
        if (!$this->selectedPlotId) {
            return null;
        }

        $batchId = $this->selectedBatchId ?? $this->currentBatch?->id;

        return $batchId
            ? PlantingBatch::with('product')
                ->where('farm_plot_id', $this->selectedPlotId)
                ->find($batchId)
            : null;
    }

    #[Computed]
    public function historyBatches(): Collection
    {
        if (!$this->selectedPlotId) {
            return collect();
        }

        return PlantingBatch::where(
            'farm_plot_id',
            $this->selectedPlotId
        )
            ->with('product')
            ->withCount('productBatches')
            ->latest('id')
            ->get();
    }

    #[Computed]
    public function canManageSelectedBatch(): bool
    {
        return $this->selectedBatch
            && $this->currentBatch
            && $this->selectedBatch->id === $this->currentBatch->id
            && in_array(
                $this->selectedBatch->status,
                ['planting', 'harvesting'],
                true
            )
            && $this->selectedPlot->plantingBatches->count() === 1;
    }

    #[Computed]
    public function sowPlot(): ?FarmPlot
    {
        return $this->sowPlotId
            ? FarmPlot::find($this->sowPlotId)
            : null;
    }

    #[Computed]
    public function timelineEvents(): Collection
    {
        return $this->selectedBatch
            ? $this->selectedBatch->traceabilityEvents()
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->get()
            : collect();
    }

    #[Computed]
    public function harvestBatches(): Collection
    {
        return $this->selectedBatch
            ? $this->selectedBatch->productBatches()
                ->orderByDesc('manufactured_date')
                ->orderByDesc('id')
                ->get()
            : collect();
    }

    #[Computed]
    public function qrBatch(): ?ProductBatch
    {
        if (!$this->qrBatchId || !$this->selectedBatch) {
            return null;
        }

        return ProductBatch::with('plantingBatch.product')
            ->where('planting_batch_id', $this->selectedBatch->id)
            ->find($this->qrBatchId);
    }

    public function setFilter(string $value): void
    {
        if (
            !in_array(
                $value,
                ['all', 'available', 'in-progress',  'empty', 'maintenance'],
                true
            )
        ) {
            return;
        }

        $this->filter = $value;
        unset($this->plots);
    }

    public function updatedSearch(): void
    {
        unset($this->plots);
    }

    public function selectPlot(int $plotId): void
    {
        FarmPlot::findOrFail($plotId);

        $this->closeModals();
        $this->selectedPlotId = $plotId;
        $this->selectedBatchId = null;
        $this->activeTab = 'timeline';

        $this->refreshData();
        $this->selectedBatchId = $this->currentBatch?->id;

        unset($this->selectedBatch);
    }

    public function selectBatch(int $batchId): void
    {
        PlantingBatch::where('farm_plot_id', $this->selectedPlotId)
            ->findOrFail($batchId);

        $this->closeModals();
        $this->selectedBatchId = $batchId;
        $this->activeTab = 'timeline';
        $this->refreshData();
    }

    public function activeBatch(FarmPlot $plot): ?PlantingBatch
    {
        if ($plot->relationLoaded('plantingBatches')) {
            return $plot->plantingBatches
                ->sortByDesc('id')
                ->first(
                    fn(PlantingBatch $batch) => in_array(
                        $batch->status,
                        ['planting', 'harvesting'],
                        true
                    )
                );
        }

        return $plot->plantingBatches()
            ->whereIn('status', ['planting', 'harvesting'])
            ->with('product')
            ->latest('id')
            ->first();
    }

    public function backToList(): void
    {
        $this->closeModals();
        $this->reset('selectedPlotId', 'selectedBatchId', 'activeTab');
        $this->refreshData();
    }

    public function openSowFor(int $plotId): void
    {
        $plot = FarmPlot::findOrFail($plotId);
        $this->closeModals();

        if (
            !in_array($plot->status, ['available', 'active'], true)
            || $plot->plantingBatches()
                ->whereIn('status', ['planting', 'harvesting'])
                ->exists()
        ) {
            $this->dispatch(
                'swal',
                icon: 'warning',
                title: 'Chưa thể xuống giống',
                text: 'Luống phải sẵn sàng và chưa có vụ đang canh tác.'
            );

            return;
        }

        $this->sowPlotId = $plot->id;
        $this->sowProductId = $this->products->first()?->id;
        $this->sowDate = now()->toDateString();
        $this->showSowModal = true;

        unset($this->sowPlot);
    }

    public function sow(FarmPlotService $service): void
    {
        abort_unless($this->sowPlotId, 404);

        $this->attempt('sowProductId', function () use ($service) {
            $batch = $service->sow(
                FarmPlot::findOrFail($this->sowPlotId),
                $this->sowProductId,
                $this->sowDate,
                $this->staff()
            );

            $this->closeModals();
            $this->selectedPlotId = $batch->farm_plot_id;
            $this->selectedBatchId = $batch->id;
            $this->activeTab = 'timeline';
            $this->refreshData();

            $this->success(
                'Xuống giống thành công!',
                "Đã tạo lô canh tác {$batch->planting_code}."
            );
        });
    }

    public function openLogModal(): void
    {
        $this->refreshData();

        if (!$this->canManageSelectedBatch) {
            $this->cannotManage();
            return;
        }

        $batchId = $this->selectedBatch->id;
        $this->closeModals();

        $this->logBatchId = $batchId;
        $this->showLogModal = true;
    }

    public function saveLog(FarmPlotService $service): void
    {
        abort_unless($this->logBatchId, 404);

        $this->attempt('logType', function () use ($service) {
            $batch = $this->batchForForm($this->logBatchId);

            $service->recordEvent(
                $batch,
                $this->logFormData(),
                $this->logPhoto,
                $this->staff()
            );

            $this->closeModals();
            $this->refreshData();

            $this->success(
                'Đã lưu nhật ký!',
                'Nội dung và ảnh minh chứng đã được ghi nhận.'
            );
        });
    }

    public function openHarvestModal(): void
    {
        $this->refreshData();

        if (!$this->canManageSelectedBatch) {
            $this->cannotManage();
            return;
        }

        $batchId = $this->selectedBatch->id;
        $this->closeModals();

        $this->harvestBatchId = $batchId;
        $this->harvestDate = now()->toDateString();
        $this->harvestExpiry = now()->addDays(15)->toDateString();
        $this->showHarvestModal = true;
    }

    public function harvest(FarmPlotService $service): void
    {
        abort_unless($this->harvestBatchId, 404);

        $this->attempt('harvestQty', function () use ($service) {
            $batch = $this->batchForForm($this->harvestBatchId);

            $productBatch = $service->harvest($batch, [
                'harvest_date' => $this->harvestDate,
                'expiry_date' => $this->harvestExpiry,
                'quantity' => $this->harvestQty,
                'note' => $this->harvestNote,
                'mode' => $this->harvestMode,
            ], $this->harvestPhoto, $this->staff());

            $this->closeModals();
            $this->selectedBatchId = $productBatch->planting_batch_id;
            $this->activeTab = 'harvests';
            $this->refreshData();

            $this->success(
                'Đã ghi nhận thu hoạch!',
                "Lô {$productBatch->batch_code}: "
                . number_format($productBatch->initial_quantity, 2)
                . ' kg.'
            );
        });
    }

    public function printQr(int $productBatchId): void
    {
        abort_unless($this->selectedBatch, 404);

        $batch = ProductBatch::where(
            'planting_batch_id',
            $this->selectedBatch->id
        )->findOrFail($productBatchId);

        $this->closeModals();
        $this->qrBatchId = $batch->id;

        unset($this->qrBatch);
    }

    public function updatedLogPhoto(): void
    {
        $this->validateOnly('logPhoto', [
            'logPhoto' => [
                'bail',
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'logPhoto.required' => 'Vui lòng tải ảnh minh chứng nhật ký.',
            'logPhoto.image' => 'Tệp phải là hình ảnh.',
            'logPhoto.mimes' => 'Chỉ nhận JPG, PNG hoặc WEBP.',
            'logPhoto.max' => 'Ảnh nhật ký tối đa 2 MB.',
        ]);
    }

    public function updatedHarvestPhoto(): void
    {
        $this->validateOnly('harvestPhoto', [
            'harvestPhoto' => [
                'bail',
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ], [
            'harvestPhoto.required' => 'Vui lòng tải ảnh thu hoạch thực tế.',
            'harvestPhoto.image' => 'Tệp phải là hình ảnh.',
            'harvestPhoto.mimes' => 'Chỉ nhận JPG, PNG hoặc WEBP.',
            'harvestPhoto.max' => 'Ảnh thu hoạch tối đa 4 MB.',
        ]);
    }

    public function closeSowModal(): void
    {
        $this->closeModals();
    }

    public function closeLogModal(): void
    {
        $this->closeModals();
    }

    public function closeHarvestModal(): void
    {
        $this->closeModals();
    }

    public function closeQr(): void
    {
        $this->closeModals();
    }

    public function closeModals(): void
    {
        $this->reset([
            'showSowModal',
            'showLogModal',
            'showHarvestModal',
            'sowPlotId',
            'logBatchId',
            'harvestBatchId',
            'qrBatchId',
            'sowProductId',
            'showCreatePlotModal',
            'newPlotName',
            'newPlotArea',
            'sowDate',
            'logType',
            'logDetails',
            'logDesc',
            'logPhoto',
            'harvestDate',
            'harvestExpiry',
            'harvestQty',
            'harvestNote',
            'harvestMode',
            'harvestPhoto',
        ]);

        $this->resetValidation();
        unset($this->sowPlot, $this->qrBatch);
    }

    private function batchForForm(int $batchId): PlantingBatch
    {
        return PlantingBatch::where(
            'farm_plot_id',
            $this->selectedPlotId
        )->findOrFail($batchId);
    }

    private function refreshData(): void
    {
        unset(
            $this->plots,
            $this->selectedPlot,
            $this->currentBatch,
            $this->selectedBatch,
            $this->historyBatches,
            $this->canManageSelectedBatch,
            $this->timelineEvents,
            $this->harvestBatches,
            $this->sowPlot,
            $this->qrBatch
        );
    }

    private function staff(): User
    {
        $user = Auth::id() ? User::find(Auth::id()) : null;
        $role = $user?->role;

        if ($role instanceof \BackedEnum) {
            $role = $role->value;
        }

        abort_unless(
            $user && $user->status === 'active' && $role === 'farm_staff',
            403,
            'Bạn không có quyền truy cập chức năng này.'
        );

        return $user;
    }

    private function attempt(string $field, Closure $action): void
    {
        try {
            $action();
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            if ($exception instanceof HttpExceptionInterface) {
                throw $exception;
            }

            report($exception);

            $message = 'Không thể hoàn thành thao tác. Vui lòng thử lại.';

            $this->addError($field, $message);

            $this->dispatch(
                'swal',
                icon: 'error',
                title: 'Thao tác chưa hoàn tất',
                text: $message
            );
        }
    }

    private function success(string $title, string $text): void
    {
        $this->dispatch(
            'swal',
            icon: 'success',
            title: $title,
            text: $text
        );
    }

    private function cannotManage(): void
    {
        $this->dispatch(
            'swal',
            icon: 'warning',
            title: 'Chưa thể thao tác',
            text: 'Hãy chọn vụ đang hoạt động. Vụ đã kết thúc chỉ được xem lại.'
        );
    }

    public function render()
    {
        return view('livewire.farm-staff.farm-plot-manager');
    }
}