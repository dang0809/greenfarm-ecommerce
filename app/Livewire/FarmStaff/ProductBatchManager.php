<?php

namespace App\Livewire\FarmStaff;

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
use Throwable;

#[Layout('layouts.farm-staff.app')]
#[Title('Quản Lý Lô Nông Sản (FEFO) | Nông Trại Đất Lành')]
class ProductBatchManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $productFilter = '';
    public string $statusFilter = '';

    #[Locked]
    public string $modal = '';

    #[Locked]
    public ?int $batchId = null;

    public string $quantity = '';
    public string $note = '';
    public string $expiry = '';
    public string $newStatus = 'active';

    public function boot(): void
    {
        $this->staff();
    }

    private function staff(): User
    {
        // Workspace hiện tại đang dùng guard web cho Farm Staff.
        $id = Auth::guard('web')->id();
        $user = $id ? User::find($id) : null;

        abort_unless(
            $user
                && $user->role === 'farm_staff'
                && $user->status === 'active',
            403
        );

        return $user;
    }

    public function updated($property): void
    {
        if (in_array(
            $property,
            ['search', 'productFilter', 'statusFilter'],
            true
        )) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'productFilter',
            'statusFilter',
        ]);

        $this->resetPage();
    }

    public function openModal(string $mode, int $id): void
    {
        abort_unless(
            in_array($mode, [
                'spoilage',
                'stock',
                'expiry',
                'status',
                'detail',
                'qr',
            ], true),
            404
        );

        $batch = ProductBatch::findOrFail($id);

        $this->closeModal();

        $this->batchId = $batch->id;
        $this->modal = $mode;

        $this->quantity = $mode === 'stock'
            ? (string) $batch->current_quantity
            : '';

        $this->expiry = $batch->expiry_date->toDateString();
        $this->newStatus = $batch->status;
    }

    public function closeModal(): void
    {
        $this->reset([
            'modal',
            'batchId',
            'quantity',
            'note',
            'expiry',
            'newStatus',
        ]);

        $this->resetValidation();
    }

    private function validatedInput(): array
    {
        abort_unless(
            $this->batchId
                && in_array(
                    $this->modal,
                    ['spoilage', 'stock', 'expiry', 'status'],
                    true
                ),
            422
        );

        $this->note = trim($this->note);

        $rules = [
            'note' => ['required', 'string', 'max:1000'],
        ];

        if (in_array($this->modal, ['spoilage', 'stock'], true)) {
            $rules['quantity'] = [
                'required',
                'integer',
                'min:'.($this->modal === 'spoilage' ? 1 : 0),
                'max:2147483647',
            ];
        } elseif ($this->modal === 'expiry') {
            $rules['expiry'] = [
                'required',
                'date_format:Y-m-d',
            ];
        } else {
            $rules['newStatus'] = [
                'required',
                Rule::in(['active', 'suspended', 'recalled']),
            ];
        }

        return $this->validate($rules, [
            'note.required' => 'Vui lòng nhập lý do thực hiện.',
            'note.max' => 'Lý do tối đa 1.000 ký tự.',

            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên theo đơn vị bán.',
            'quantity.min' => 'Số lượng chưa đạt giá trị tối thiểu.',
            'quantity.max' => 'Số lượng vượt giới hạn lưu trữ.',

            'expiry.required' => 'Vui lòng chọn hạn sử dụng.',
            'expiry.date_format' => 'Hạn sử dụng không hợp lệ.',

            'newStatus.in' => 'Trạng thái không hợp lệ.',
        ]);
    }

    public function requestSave(): void
    {
        $this->resetValidation('form');
        $this->validatedInput();

        // Chưa cập nhật DB ở bước yêu cầu xác nhận.
        $this->dispatch('confirm-batch-change')->self();
    }

    private function reject(
        string $field,
        string $message
    ): never {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }

    public function saveChange(): void
    {
        $staff = $this->staff();
        $data = $this->validatedInput();
        $mode = $this->modal;

        try {
            $result = DB::transaction(function () use (
                $staff,
                $data,
                $mode
            ) {
                // Đọc lại và khóa bản ghi khi thực sự cập nhật.
                $batch = ProductBatch::query()
                    ->lockForUpdate()
                    ->findOrFail($this->batchId);

                $current = (int) $batch->current_quantity;
                $reserved = (int) $batch->reserved_quantity;
                $spoiled = (int) $batch->spoiled_quantity;

                if (
                    $current < 0
                    || $reserved < 0
                    || $reserved > $current
                    || $spoiled < 0
                ) {
                    $this->reject(
                        'form',
                        'Tồn kho/giữ chỗ không nhất quán. Cần đối soát trước khi sửa.'
                    );
                }

                // Không sửa HSD/trạng thái khi còn đơn chưa kết thúc.
                if (
                    in_array($mode, ['expiry', 'status'], true)
                    && $batch->orderItems()
                        ->whereHas('order', fn ($q) => $q
                            ->whereNotIn('status', [
                                'delivered',
                                'cancelled',
                            ]))
                        ->exists()
                ) {
                    $this->reject(
                        'form',
                        'Lô còn liên quan đơn chưa kết thúc. Cần xử lý đơn trước khi sửa HSD/trạng thái.'
                    );
                }

                $logQuantity = 0;
                $logNote = $data['note'];

                if ($mode === 'spoilage') {
                    $qty = (int) $data['quantity'];

                    if ($qty > $current - $reserved) {
                        $this->reject(
                            'quantity',
                            'Số lượng xuất hủy vượt phần tồn chưa giữ chỗ.'
                        );
                    }

                    if ($spoiled > 2147483647 - $qty) {
                        $this->reject(
                            'quantity',
                            'Tổng hao hụt vượt giới hạn lưu trữ.'
                        );
                    }

                    $batch->current_quantity = $current - $qty;
                    $batch->spoiled_quantity = $spoiled + $qty;

                    if ($batch->current_quantity === 0) {
                        $batch->status = 'recalled';

                        $logNote .=
                            ' Đã xuất hủy hết tồn; chuyển recalled.';
                    }

                    $type = 'export_spoilage';
                    $logQuantity = $qty;
                } elseif ($mode === 'stock') {
                    if ($batch->status === 'recalled') {
                        $this->reject(
                            'form',
                            'Lô đã thu hồi không được kiểm kê để nhập lại hàng.'
                        );
                    }

                    $newQuantity = (int) $data['quantity'];

                    if ($newQuantity < $reserved) {
                        $this->reject(
                            'quantity',
                            'Tồn mới không được thấp hơn số lượng đang giữ chỗ.'
                        );
                    }

                    if ($newQuantity === $current) {
                        return 'unchanged';
                    }

                    $batch->current_quantity = $newQuantity;

                    $type = $newQuantity > $current
                        ? 'adjustment_in'
                        : 'adjustment_out';

                    $logQuantity = abs($newQuantity - $current);

                    $logNote =
                        "Kiểm kê: {$current} -> {$newQuantity}. "
                        .$logNote;
                } elseif ($mode === 'expiry') {
                    if (
                        $batch->status === 'recalled'
                        || $reserved > 0
                    ) {
                        $this->reject(
                            'form',
                            'Không sửa HSD lô đã thu hồi hoặc đang giữ chỗ cho đơn hàng.'
                        );
                    }

                    $lowerDate = $batch->manufactured_date
                        ?? $batch->plantingBatch?->planted_date;

                    if (
                        ! $lowerDate
                        || $data['expiry']
                            <= $lowerDate->toDateString()
                    ) {
                        $this->reject(
                            'expiry',
                            'HSD phải sau ngày thu hoạch/ngày xuống giống.'
                        );
                    }

                    $oldExpiry =
                        $batch->expiry_date->toDateString();

                    if ($data['expiry'] === $oldExpiry) {
                        return 'unchanged';
                    }

                    $batch->expiry_date = $data['expiry'];
                    $type = 'expiry_change';

                    $logNote =
                        "HSD: {$oldExpiry} -> {$data['expiry']}. "
                        .$logNote;
                } else {
                    if ($data['newStatus'] === $batch->status) {
                        return 'unchanged';
                    }

                    if (
                        $batch->status === 'recalled'
                        || $reserved > 0
                    ) {
                        $this->reject(
                            'form',
                            'Không đổi trạng thái lô đã thu hồi hoặc đang giữ chỗ.'
                        );
                    }

                    if (
                        $data['newStatus'] === 'active'
                        && (
                            $batch->expiry_date->lt(today())
                            || $current <= 0
                        )
                    ) {
                        $this->reject(
                            'newStatus',
                            'Chỉ mở bán lại lô còn hạn và còn tồn.'
                        );
                    }

                    $oldStatus = $batch->status;
                    $batch->status = $data['newStatus'];
                    $type = 'status_change';

                    $logNote =
                        "Trạng thái: {$oldStatus} -> {$data['newStatus']}. "
                        .$logNote;
                }

                if (! $batch->save()) {
                    throw new RuntimeException(
                        'Không lưu được lô hàng.'
                    );
                }

                InventoryLog::create([
                    'batch_id' => $batch->id,
                    'type' => $type,
                    'quantity' => $logQuantity,
                    'note' => $logNote,
                    'created_by' => $staff->id,
                ]);

                return 'saved';
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            $this->addError(
                'form',
                'Không thể cập nhật lô hàng. Vui lòng thử lại.'
            );

            return;
        }

        if ($result === 'unchanged') {
            $this->dispatch(
                'swal',
                icon: 'info',
                title: 'Không có thay đổi nào.'
            );

            return;
        }

        $this->closeModal();

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Đã cập nhật lô và ghi nhật ký!'
        );
    }

    public function render()
    {
        $today = today()->toDateString();
        $near = today()->addDays(3)->toDateString();

        $available = fn () => ProductBatch::query()
            ->where('status', 'active')
            ->whereDate('expiry_date', '>=', $today)
            ->whereColumn(
                'current_quantity',
                '>',
                'reserved_quantity'
            );

        // Thống kê toàn kho, độc lập bộ lọc của bảng.
        $stats = [
            'total' => ProductBatch::count(),
            'active' => $available()->count(),

            'near' => $available()
                ->whereDate('expiry_date', '<=', $near)
                ->count(),

            'expired' => ProductBatch::whereDate(
                'expiry_date',
                '<',
                $today
            )->where('current_quantity', '>', 0)->count(),
        ];

        // Không cộng kg, bó, hộp thành một tổng kg.
        $spoiledByUnit = DB::table('product_batches as b')
            ->join(
                'planting_batches as pb',
                'pb.id',
                '=',
                'b.planting_batch_id'
            )
            ->join('products as p', 'p.id', '=', 'pb.product_id')
            ->select('p.unit')
            ->selectRaw('SUM(b.spoiled_quantity) AS quantity')
            ->groupBy('p.unit')
            ->get();

        $query = ProductBatch::with([
            'plantingBatch.product',
            'plantingBatch.farmPlot',
        ]);

        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'batch_code',
                    'like',
                    '%'.$search.'%'
                )
                    ->orWhereHas('plantingBatch', fn ($q) => $q
                        ->where(
                            'planting_code',
                            'like',
                            '%'.$search.'%'
                        ))
                    ->orWhereHas(
                        'plantingBatch.product',
                        fn ($q) => $q->where(fn ($q) => $q
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('sku', 'like', '%'.$search.'%'))
                    )
                    ->orWhereHas(
                        'plantingBatch.farmPlot',
                        fn ($q) => $q->where(
                            'name',
                            'like',
                            '%'.$search.'%'
                        )
                    );
            });
        }

        if ($this->productFilter !== '') {
            $query->whereHas(
                'plantingBatch',
                fn ($q) => $q->where(
                    'product_id',
                    $this->productFilter
                )
            );
        }

        switch ($this->statusFilter) {
            case 'active':
            case 'near_expiry':
                $query->where('status', 'active')
                    ->whereDate('expiry_date', '>=', $today)
                    ->whereColumn(
                        'current_quantity',
                        '>',
                        'reserved_quantity'
                    );

                if ($this->statusFilter === 'near_expiry') {
                    $query->whereDate(
                        'expiry_date',
                        '<=',
                        $near
                    );
                }
                break;

            case 'expired':
                $query->whereDate('expiry_date', '<', $today)
                    ->where('current_quantity', '>', 0);
                break;

            case 'out_of_stock':
                $query->where('current_quantity', '<=', 0);
                break;

            case 'suspended':
            case 'recalled':
                $query->where('status', $this->statusFilter);
                break;
        }

        $selectedBatch = $this->batchId
            ? ProductBatch::with([
                'plantingBatch.product',
                'plantingBatch.farmPlot',
            ])->find($this->batchId)
            : null;

        if ($selectedBatch && $this->modal === 'detail') {
            $selectedBatch->load([
                'inventoryLogs' => fn ($q) => $q
                    ->with('creator')
                    ->orderByDesc('id'),

                'plantingBatch.traceabilityEvents' => fn ($q) => $q
                    ->orderBy('recorded_at')
                    ->orderBy('id'),
            ]);
        }

        $traceUrl = $selectedBatch
            ? route('customer.traceability.detail', [
                'batch' => $selectedBatch->batch_code,
            ]).'?batch='.rawurlencode($selectedBatch->batch_code)
            : null;

        $qrUrl = $traceUrl
            ? 'https://api.qrserver.com/v1/create-qr-code/?'
                .http_build_query([
                    'size' => '300x300',
                    'data' => $traceUrl,
                ])
            : null;

        return view('livewire.farm-staff.product-batch-manager', [
            'batches' => $query
                ->orderBy('expiry_date')
                ->orderBy('id')
                ->paginate(10),

            'products' => Product::orderBy('name')
                ->get(['id', 'name']),

            'stats' => $stats,
            'spoiledByUnit' => $spoiledByUnit,
            'selectedBatch' => $selectedBatch,
            'qrUrl' => $qrUrl,
        ]);
    }
}