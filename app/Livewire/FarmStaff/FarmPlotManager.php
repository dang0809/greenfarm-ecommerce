<?php

namespace App\Livewire\FarmStaff;

use App\Models\FarmPlot;
use App\Models\PlantingBatch;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Services\FarmPlotService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;

#[Layout('layouts.farm-staff.app')]
#[Title('Quản Lý Luống Đất & Canh Tác | Nông Trại Đất Lành')]
class FarmPlotManager extends Component
{
    use WithFileUploads;

    // ----- Bộ lọc danh sách -----
    public string $search = '';
    public string $filter = 'all';          // all|active|empty

    // ----- Điều hướng -----
    public ?int $selectedPlotId = null;
    public string $activeTab = 'timeline';

    // ----- Modal -----
    public bool $showSowModal = false;
    public bool $showLogModal = false;
    public bool $showHarvestModal = false;
    public ?int $sowPlotId = null; 
    public ?string $qrCode = null;
    public ?string $qrManufactured = null;
    public ?string $qrExpiry = null;

    // ----- Form xuống giống -----
    public ?int $sowProductId = null;
    public string $sowDate = '';

    // ----- Form nhật ký -----
    public string $logName = '';
    public string $logDesc = '';
    public $logPhoto = null;

    // ----- Form thu hoạch -----
    public string $harvestDate = '';
    public string $harvestExpiry = '';
    public $harvestQty = '';
    public string $harvestNote = '';
    public string $harvestMode = 'inherit';
    public $harvestPhoto = null;

    #[Computed]
    public function plots(): Collection
    {
        return FarmPlot::query()
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->filter === 'active', fn ($q) => $q->whereHas(
                'plantingBatches', fn ($b) => $b->occupying()
            ))
            ->when($this->filter === 'empty', fn ($q) => $q->whereDoesntHave(
                'plantingBatches', fn ($b) => $b->occupying()
            ))
            ->with(['plantingBatches' => fn ($q) => $q
                ->occupying()
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
        if (! $this->selectedPlotId) {
            return null;
        }

        return FarmPlot::with(['plantingBatches' => fn ($q) => $q
            ->occupying()
            ->with('product')
            ->latest('id'),
        ])->find($this->selectedPlotId);
    }

    #[Computed]
    public function selectedBatch(): ?PlantingBatch
    {
        return $this->selectedPlot?->activeBatch();
    }

    #[Computed]
    public function sowPlot(): ?FarmPlot
    {
        // Ưu tiên luống đang mở chi tiết, sau đó tới luống bấm từ danh sách
        if ($this->selectedPlot) {
            return $this->selectedPlot;
        }

        return $this->sowPlotId ? FarmPlot::find($this->sowPlotId) : null;
    }

    #[Computed]
    public function timelineEvents(): Collection
    {
        if (! $batch = $this->selectedBatch) {
            return collect();
        }

        return $batch->traceabilityEvents()
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->get();
    }

    #[Computed]
    public function harvestBatches(): Collection
    {
        if (! $batch = $this->selectedBatch) {
            return collect();
        }

        return ProductBatch::where('planting_batch_id', $batch->id)
            ->orderByDesc('manufactured_date')
            ->orderByDesc('id')
            ->get();
    }


    /* ================== ĐIỀU HƯỚNG ================== */

    public function selectPlot(int $plotId): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->selectedPlotId = $plotId;
        $this->activeTab = 'timeline';
    }

    public function backToList(): void
    {
        $this->selectedPlotId = null;
        unset($this->plots);
    }

    public function setFilter(string $value): void
    {
        if (! in_array($value, ['all', 'active', 'empty'], true)) {
            return;
        }

        $this->filter = $value;
        unset($this->plots);
    }

    public function updatedSearch(): void
    {
        unset($this->plots);
    }


    /* ================== XUỐNG GIỐNG ================== */
    /* ================== MỞ MODAL TỪ DANH SÁCH ================== */
    public function openSowFor(int $plotId): void
    {
        $this->sowPlotId = $plotId;
        $this->initSowForm();
    }

    public function openSowModal(): void
    {
        $this->sowPlotId = null;   // dùng luống đang mở chi tiết (nếu có)
        $this->initSowForm();
    }

    /** Khởi tạo dữ liệu form xuống giống (dùng chung cho 2 điểm mở modal). */
    private function initSowForm(): void
    {
        $this->resetValidation();
        $this->sowProductId = $this->products->first()?->id;
        $this->sowDate = now()->toDateString();
        $this->showSowModal = true;
    }

    public function closeSowModal(): void
    {
        $this->showSowModal = false;
        $this->sowPlotId = null;
        $this->resetValidation();
        unset($this->sowPlot);
    }

    public function sow(FarmPlotService $service): void
    {
        $this->validate([
            'sowProductId' => ['required', 'integer', 'exists:products,id'],
            'sowDate'      => ['required', 'date', 'before_or_equal:today'],
        ], [
            'sowProductId.required'   => 'Vui lòng chọn giống cây trồng.',
            'sowProductId.exists'     => 'Giống cây không tồn tại trong hệ thống.',
            'sowDate.required'        => 'Vui lòng chọn ngày xuống giống.',
            'sowDate.before_or_equal' => 'Ngày xuống giống không được ở tương lai.',
        ]);
        // Resolve luống: ưu tiên luống đang mở chi tiết, sau đó tới luống bấm từ danh sách
        $plot = $this->sowPlot;

        if (! $plot) {
            $this->addError('sowProductId', 'Không xác định được luống cần xuống giống.');
            return;
        }
        try {
            $service->sow($plot, (int) $this->sowProductId, $this->sowDate, Auth::user());
            $this->showSowModal = false;
            $this->sowPlotId = null;
            $this->reset('sowProductId', 'sowDate');
            unset($this->plots, $this->selectedPlot, $this->selectedBatch);
            $this->dispatch('swal',
                icon: 'success',
                title: 'Xuống giống thành công!',
                text: 'Đã mở lô canh tác mới và ghi sự kiện truy xuất.',
            );
        } catch (ValidationException $e) {
            throw $e;   // Livewire tự render lỗi vào form
        } catch (\Throwable $e) {
            report($e);
            $this->addError('sowProductId', 'Không thể xuống giống, vui lòng thử lại.');
        }
    }

    /* ================== NHẬT KÝ ================== */

    public function openLogModal(): void
    {
        $this->resetValidation();
        $this->reset('logName', 'logDesc', 'logPhoto');
        $this->showLogModal = true;
    }

    public function saveLog(FarmPlotService $service): void
    {
        $this->validate([
            'logName'  => ['required', 'string', 'min:3', 'max:100'],
            'logDesc'  => ['required', 'string', 'min:5'],   // khớp cột description
            'logPhoto' => ['nullable', 'image', 'max:2048'], // khớp nghiệp vụ ảnh minh chứng
        ], [
            'logName.required' => 'Vui lòng nhập loại hoạt động.',
            'logDesc.required' => 'Vui lòng mô tả chi tiết hoạt động.',
            'logPhoto.image'   => 'Tệp đính kèm phải là hình ảnh.',
            'logPhoto.max'     => 'Ảnh không được vượt quá 2MB.',
        ]);

        $batch = $this->selectedBatch;

        if (! $batch) {
            $this->addError('logName', 'Luống này chưa có lô canh tác để ghi nhật ký.');
            return;
        }

        try {
            $service->recordEvent($batch, [
                'event_name'  => $this->logName,
                'description' => $this->logDesc,
            ], $this->logPhoto, Auth::user());

            $this->showLogModal = false;
            $this->reset('logName', 'logDesc', 'logPhoto');
            unset($this->timelineEvents);

            $this->dispatch('swal', icon: 'success', title: 'Đã lưu nhật ký!',
                text: 'Sự kiện đã được băm SHA-256 và lưu trữ.');
        } catch (\Throwable $e) {
            report($e);
            $this->addError('logName', 'Không thể lưu nhật ký, vui lòng thử lại.');
        }
    }

    /* ================== THU HOẠCH ================== */

    public function openHarvestModal(): void
    {
        $this->resetValidation();
        $this->reset('harvestQty', 'harvestNote', 'harvestPhoto');
        $this->harvestDate = now()->toDateString();
        $this->harvestExpiry = now()->addDays(15)->toDateString();
        $this->harvestMode = 'inherit';
        $this->showHarvestModal = true;
    }

    public function harvest(FarmPlotService $service): void
    {
        $this->validate([
            'harvestDate'   => ['required', 'date', 'before_or_equal:today'],
            'harvestExpiry' => ['required', 'date', 'after:harvestDate'],
            'harvestQty'    => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'harvestNote'   => ['nullable', 'string', 'max:1000'],
            'harvestMode'   => ['required', 'in:inherit,finish'],
            'harvestPhoto'  => ['nullable', 'image', 'max:4096'],
        ], [
            'harvestDate.required'        => 'Vui lòng chọn ngày thu hái.',
            'harvestDate.before_or_equal' => 'Ngày thu hái không được ở tương lai.',
            'harvestExpiry.required'      => 'Vui lòng chọn hạn sử dụng.',
            'harvestExpiry.after'         => 'Hạn sử dụng phải sau ngày thu hái.',
            'harvestQty.required'         => 'Vui lòng nhập sản lượng thu hoạch.',
            'harvestQty.min'              => 'Sản lượng phải lớn hơn 0 kg.',
            'harvestPhoto.image'          => 'Tệp đính kèm phải là hình ảnh.',
            'harvestPhoto.max'            => 'Ảnh không được vượt quá 4MB.',
        ]);
        $batch = $this->selectedBatch;
        if (! $batch) {
            $this->addError('harvestQty', 'Luống này chưa có lô canh tác để thu hoạch.');
            return;
        }
        try {
            $productBatch = $service->harvest($batch, [
                'harvest_date' => $this->harvestDate,
                'expiry_date'  => $this->harvestExpiry,
                'quantity'     => (float) $this->harvestQty,
                'note'         => $this->harvestNote,
                'mode'         => $this->harvestMode,
            ], $this->harvestPhoto, Auth::user());
            $isFinish = $this->harvestMode === 'finish';
            $this->showHarvestModal = false;
            $this->reset('harvestQty', 'harvestNote', 'harvestPhoto', 'harvestMode');
            unset(
                $this->plots, $this->selectedPlot, $this->selectedBatch,
                $this->timelineEvents, $this->harvestBatches
            );
            if ($isFinish) {
                $this->selectedPlotId = null;   // luống đã trống => về danh sách
            } else {
                $this->activeTab = 'harvests';
            }
            $this->dispatch('swal',
                icon: 'success',
                title: $isFinish ? 'Thu hoạch & dọn luống thành công!' : 'Thu hoạch thành công!',
                text: "Đã tạo lô thương mại {$productBatch->batch_code} và nhập kho {$productBatch->initial_quantity} kg.",
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('harvestQty', 'Không thể lưu thu hoạch, vui lòng thử lại.');
        }
    }
/* ================== IN TEM QR ================== */
    public function printQr(int $productBatchId): void
    {
        $pb = $this->harvestBatches->firstWhere('id', $productBatchId);
        if (! $pb) {
            return;
        }
        $this->qrCode         = $pb->batch_code;
        $this->qrManufactured = $pb->manufactured_date?->format('d/m/Y');
        $this->qrExpiry       = $pb->expiry_date?->format('d/m/Y');
    }
    public function closeQr(): void
    {
        $this->reset('qrCode', 'qrManufactured', 'qrExpiry');
    }
    /* ================== RENDER ================== */
    public function render()
    {
        return view('livewire.farm-staff.farm-plot-manager');
    }
}