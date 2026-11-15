<?php

namespace App\Livewire\FarmStaff;

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
use Throwable;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

#[Layout('layouts.farm-staff.app')]
#[Title('Quản Lý Lô Nông Sản (FEFO)')]
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

    public function updated($property): void
    {
        if (
            in_array(
                $property,
                ['search', 'productFilter', 'statusFilter'],
                true
            )
        ) {
            $this->resetPage();

            return;
        }

        if (
            in_array(
                $this->modal,
                ['spoilage', 'stock', 'expiry', 'status'],
                true
            )
            && in_array(
                $property,
                ['quantity', 'expiry', 'newStatus', 'note'],
                true
            )
        ) {
            // Chỉ xóa lỗi của trường vừa thay đổi.
            $this->resetValidation($property);
        }
    }

    // Validate dữ liệu
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

        if ($this->modal === 'spoilage') {
            $rules['quantity'] = [
                'required',
                'integer',
                'min:1',
                'max:2147483647',
            ];
        } elseif ($this->modal === 'stock') {
            $rules['quantity'] = [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
            ];
        } elseif ($this->modal === 'expiry') {
            $rules['expiry'] = [
                'required',
                'date_format:Y-m-d',
            ];
        } elseif ($this->modal === 'status') {
            $rules['newStatus'] = [
                'required',
                Rule::in([
                    'active',
                    'suspended',
                ]),
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

            'newStatus.required' => 'Vui lòng chọn trạng thái lô hàng.',
            'newStatus.in' => 'Trạng thái không hợp lệ.',
        ]);
    }

    private function reject(
        string $field,
        string $message
    ): never {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }

    // Bộ lọc lô hàng
    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'productFilter',
            'statusFilter',
        ]);

        $this->resetPage();
    }

    // Modal
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

        if (
            $mode !== 'detail'
            && (
                (int) $batch->current_quantity <= 0
                || in_array($batch->status, ['recalled', 'sold_out'], true)
            )
        ) {
            $this->dispatch(
                'swal',
                icon: 'info',
                title: match ($batch->status) {
                    'sold_out' => 'Lô đã bán hết. Chỉ được xem chi tiết.',
                    'recalled' => 'Lô đã tiêu hủy. Chỉ được xem chi tiết.',
                    default => 'Lô đã hết tồn. Chỉ được xem chi tiết.',
                }
            );

            return;
        }

        if (
            $mode !== 'detail'
            && $batch->status === 'recalled'
        ) {
            $this->dispatch(
                'swal',
                icon: 'info',
                title: 'Lô đã tiêu hủy. Chỉ được xem chi tiết.'
            );

            return;
        }

        if (
            in_array($mode, ['expiry', 'stock', 'status'], true)
            && $batch->expiry_date->lt(today())
        ) {
            $this->dispatch(
                'swal',
                icon: 'error',
                title: 'Lô đã hết hạn. Không được sửa HSD, cân chỉnh hoặc đổi trạng thái.'
            );

            return;
        }

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

    // Lưu thay đổi của kho
    public function requestSave(): void
    {
        $this->staff();
        $this->resetValidation();

        abort_unless(
            $this->batchId
            && in_array(
                $this->modal,
                ['spoilage', 'stock', 'expiry', 'status'],
                true
            ),
            422
        );

        if ($this->modal === 'expiry') {
            // Đọc lại dữ liệu mới nhất khi người dùng nhấn Lưu.
            $batch = ProductBatch::findOrFail($this->batchId);

            if (
                (int) $batch->reserved_quantity > 0
                || $batch->hasUnfinishedOrders()
            ) {
                $this->addError(
                    'form',
                    'Không sửa HSD khi còn giữ chỗ hoặc đơn chưa kết thúc.'
                );

                return;
            }
        }

        $this->validatedInput();

        // Chỉ mở SweetAlert khi vượt qua các kiểm tra.
        $this->dispatch('confirm-batch-change')->self();
    }

    public function saveChange(): void
    {
        $staff = $this->staff();
        $data = $this->validatedInput();
        $mode = $this->modal;

        try {
            $result = DB::transaction(function () use ($staff, $data, $mode) {
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
                        'Tồn kho và giữ chỗ không nhất quán. Cần đối soát trước.'
                    );
                }

                if (
                    $current === 0
                    || in_array($batch->status, ['recalled', 'sold_out'], true)
                ) {
                    $this->reject(
                        'form',
                        'Lô đã hết tồn, đã bán hết hoặc đã tiêu hủy. Chỉ được xem chi tiết.'
                    );
                }

                // hết hạn không được sửa lại dữ liệu lô.
                if (
                    in_array($mode, ['expiry', 'stock', 'status'], true)
                    && $batch->expiry_date->lt(today())
                ) {
                    $this->reject(
                        'form',
                        'Lô đã hết hạn. Không được sửa HSD, cân chỉnh hoặc đổi trạng thái.'
                    );
                }

                $type = '';
                $logQuantity = 0;

                // Ghi nhận dữ liệu trước khi cập nhật.
                $statusBefore = (string) $batch->status;
                $expiryBefore = $batch->expiry_date?->toDateString();

                if ($mode === 'spoilage') {
                    $qty = (int) $data['quantity'];

                    // Chỉ xuất hủy lượng chưa giữ cho đơn hàng.
                    if ($qty <= 0 || $qty > $current - $reserved) {
                        $this->reject(
                            'quantity',
                            'Số lượng xuất hủy không hợp lệ hoặc vượt phần tồn chưa giữ chỗ.'
                        );
                    }

                    // Khi xuất hủy hết tồn, mọi đơn liên quan phải được xử lý xong.
                    if (
                        $qty === $current
                        && $batch->hasUnfinishedOrders()
                    ) {
                        $this->reject(
                            'form',
                            'Xuất hủy hết tồn cần xử lý các đơn chưa kết thúc trước.'
                        );
                    }

                    if ($spoiled > 2147483647 - $qty) {
                        $this->reject(
                            'quantity',
                            'Số lượng xuất hủy vượt giới hạn lưu trữ.'
                        );
                    }

                    $batch->current_quantity = $current - $qty;
                    $batch->spoiled_quantity = $spoiled + $qty;

                    // Chỉ ghi nhận Tiêu hủy khi đã xuất hủy hết tồn.
                    if ($batch->current_quantity === 0) {
                        $batch->status = 'recalled';
                    }

                    $type = 'export_spoilage';
                    $logQuantity = $qty;
                } elseif ($mode === 'stock') {
                    $newQuantity = (int) $data['quantity'];

                    if ($newQuantity < $reserved) {
                        $this->reject(
                            'quantity',
                            'Tồn mới không được thấp hơn số đang giữ chỗ.'
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

                } elseif ($mode === 'expiry') {
                    // Sửa HSD vẫn phải chờ xử lý đơn.
                    if (
                        $reserved > 0
                        || $batch->hasUnfinishedOrders()
                    ) {
                        $this->reject(
                            'form',
                            'Không sửa HSD khi còn giữ chỗ hoặc đơn chưa kết thúc.'
                        );
                    }

                    $lowerDate = $batch->manufactured_date
                        ?? $batch->plantingBatch?->planted_date;

                    if (
                        !$lowerDate
                        || $data['expiry'] <= $lowerDate->toDateString()
                    ) {
                        $this->reject(
                            'expiry',
                            'HSD phải sau ngày thu hoạch/ngày xuống giống.'
                        );
                    }

                    $oldExpiry = $batch->expiry_date->toDateString();

                    if ($data['expiry'] === $oldExpiry) {
                        return 'unchanged';
                    }

                    $batch->expiry_date = $data['expiry'];
                    $type = 'expiry_change';

                } elseif ($mode === 'status') {
                    $target = $data['newStatus'];

                    if ($target === $batch->status) {
                        return 'unchanged';
                    }

                    $batch->status = $target;

                    $type = 'status_change';
                    $logQuantity = 0;
                }

                if (!$batch->save()) {
                    throw new RuntimeException(
                        'Không lưu được lô hàng.'
                    );
                }

                $statusLabels = ProductBatch::STATUS_LABELS;

                $statusFrom = $statusLabels[$statusBefore] ?? $statusBefore;
                $statusTo = $statusLabels[$batch->status] ?? $batch->status;

                $expiryAfter = $batch->expiry_date?->toDateString();

                $note = implode("\n", [
                    'Nhật ký lô hàng',

                    "Trạng thái: {$statusFrom} → {$statusTo}",

                    'Tồn kho: '
                    . $current
                    . ' → '
                    . (int) $batch->current_quantity,

                    'HSD: '
                    . ($expiryBefore ?? '—')
                    . ' → '
                    . ($expiryAfter ?? '—'),

                    'Lý do:',
                    trim($data['note']),
                ]);

                $log = InventoryLog::create([
                    'batch_id' => $batch->id,
                    'type' => $type,
                    'quantity' => $logQuantity,
                    'note' => $note,
                    'created_by' => $staff->id,
                ]);

                if (!$log->exists) {
                    throw new RuntimeException(
                        'Không ghi được nhật ký kho.'
                    );
                }

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

    private function inventoryLogDisplay(InventoryLog $log): array
    {
        $raw = trim((string) ($log->note ?? ''));

        // Chuẩn hóa ký tự xuống dòng.
        $text = str_replace(["\r\n", "\r"], "\n", $raw);

        // Đọc định dạng văn bản mới.
// Toàn bộ nội dung sau dòng "Lý do:" là nội dung người dùng nhập.
        $pattern = '/\ANhật ký lô hàng\n'
            . 'Trạng thái: ([^\n]+?) → ([^\n]+)\n'
            . 'Tồn kho: (\d+) → (\d+)\n'
            . 'HSD: ([^\n]+?) → ([^\n]+)\n'
            . 'Lý do:\n(.*)\z/su';

        if (preg_match($pattern, $text, $matches)) {
            $reason = trim($matches[7]);

            return [
                'reason' => $reason !== ''
                    ? $reason
                    : 'Không có lý do được ghi nhận.',

                'transition' => $matches[1] !== $matches[2]
                    ? $matches[1] . ' → ' . $matches[2]
                    : null,

                'stockChange' => $matches[3] !== $matches[4]
                    ? $matches[3] . ' → ' . $matches[4]
                    : null,

                'expiryChange' => $matches[5] !== $matches[6]
                    ? $matches[5] . ' → ' . $matches[6]
                    : null,
            ];
        }

        $reason = $raw;
        $from = null;
        $to = null;
        $stockChange = null;
        $expiryChange = null;

        $payload = json_decode($raw, true);

        // Chỉ đọc JSON do chức năng này ghi.
        $structured = is_array($payload)
            && ($payload['format'] ?? null) === 'batch-log-v1'
            && is_string($payload['reason'] ?? null);

        if ($structured) {
            $reason = $payload['reason'];

            if ($log->type === 'status_change') {
                $from = is_string($payload['status_from'] ?? null)
                    ? $payload['status_from']
                    : null;

                $to = is_string($payload['status_to'] ?? null)
                    ? $payload['status_to']
                    : null;
            }

            if (
                in_array(
                    $log->type,
                    ['adjustment_in', 'adjustment_out'],
                    true
                )
                && is_int($payload['quantity_from'] ?? null)
                && is_int($payload['quantity_to'] ?? null)
            ) {
                $stockChange = $payload['quantity_from']
                    . ' → '
                    . $payload['quantity_to'];
            }

            if (
                $log->type === 'expiry_change'
                && is_string($payload['expiry_from'] ?? null)
                && is_string($payload['expiry_to'] ?? null)
            ) {
                $expiryChange = $payload['expiry_from']
                    . ' → '
                    . $payload['expiry_to'];
            }
        } else {
            // Tương thích những mẫu ghi chú cũ.
            if (
                $log->type === 'status_change'
                && preg_match(
                    '/\ATrạng thái:\s*([a-z_]+)\s*->\s*([a-z_]+)\.\s*(.*)\z/su',
                    $raw,
                    $matches
                )
            ) {
                $from = $matches[1];
                $to = $matches[2];
                $reason = $matches[3];

                $reason = preg_replace(
                    '/\s+Dừng cấp cho đơn mới; giữ nguyên các đơn đã phân bổ\.\z/u',
                    '',
                    $reason
                ) ?? $reason;
            } elseif (
                in_array(
                    $log->type,
                    ['adjustment_in', 'adjustment_out'],
                    true
                )
                && preg_match(
                    '/\AKiểm kê:\s*(\d+)\s*->\s*(\d+)\.\s*(.*)\z/su',
                    $raw,
                    $matches
                )
            ) {
                $stockChange = $matches[1] . ' → ' . $matches[2];
                $reason = $matches[3];
            } elseif (
                $log->type === 'expiry_change'
                && preg_match(
                    '/\AHSD:\s*(\d{4}-\d{2}-\d{2})\s*->\s*(\d{4}-\d{2}-\d{2})\.\s*(.*)\z/su',
                    $raw,
                    $matches
                )
            ) {
                $expiryChange = $matches[1] . ' → ' . $matches[2];
                $reason = $matches[3];
            }

            // Bỏ các hậu tố hệ thống đã từng được thêm vào log xuất hủy.
            if ($log->type === 'export_spoilage') {
                $reason = preg_replace(
                    '/\s+(?:Đã tiêu hủy hết tồn của lô\.|Đã xuất hủy hết tồn; chuyển [^.]+\.|Còn \d+ đơn vị [^.]+; giữ trạng thái [a-z_]+\.)\z/u',
                    '',
                    $reason
                ) ?? $reason;
            }
        }

        $statusLabels = ProductBatch::STATUS_LABELS;

        $transition = isset($statusLabels[$from], $statusLabels[$to])
            ? $statusLabels[$from] . ' → ' . $statusLabels[$to]
            : null;

        return [
            'reason' => trim($reason) !== ''
                ? trim($reason)
                : 'Không có lý do được ghi nhận.',

            'transition' => $transition,
            'stockChange' => $stockChange,
            'expiryChange' => $expiryChange,
        ];
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

    public function render()
    {
        $today = today()->toDateString();
        $near = today()->addDays(3)->toDateString();

        $available = fn() => ProductBatch::query()
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
        ])
            ->search($this->search);

        if ($this->productFilter !== '') {
            $query->whereHas(
                'plantingBatch',
                fn($q) => $q->where(
                    'product_id',
                    $this->productFilter
                )
            );
        }

        if (
            in_array(
                $this->statusFilter,
                array_keys(ProductBatch::STATUS_LABELS),
                true
            )
        ) {
            $query->where('status', $this->statusFilter);
        }

        $selectedBatch = $this->batchId
            ? ProductBatch::with([
                'plantingBatch.product',
                'plantingBatch.farmPlot',
            ])->find($this->batchId)
            : null;

        $inventoryLogDisplays = [];

        if ($selectedBatch && $this->modal === 'detail') {
            $selectedBatch->load([
                'inventoryLogs' => fn($query) => $query
                    ->with('creator')
                    ->orderByDesc('id'),

                'plantingBatch.traceabilityEvents' => fn($query) => $query
                    ->orderBy('recorded_at')
                    ->orderBy('id'),
            ]);

            foreach ($selectedBatch->inventoryLogs as $log) {
                $inventoryLogDisplays[$log->id] =
                    $this->inventoryLogDisplay($log);
            }
        }

        $hasUnfinishedOrders = $selectedBatch
            && $this->modal === 'expiry'
            && $selectedBatch->hasUnfinishedOrders();

        $qrUrl = null;

        if (
            $selectedBatch
            && (int) $selectedBatch->current_quantity > 0
            && !in_array($selectedBatch->status, ['recalled', 'sold_out'], true)
            && in_array($this->modal, ['detail', 'qr'], true)
        ) {
            $traceUrl = route('customer.traceability.detail', [
                'batch' => $selectedBatch->batch_code,
            ]) . '?batch=' . rawurlencode($selectedBatch->batch_code);

            $qrSvg = (string) QrCode::format('svg')
                ->size(300)
                ->margin(4)
                ->errorCorrection('M')
                ->generate($traceUrl);

            $qrUrl = 'data:image/svg+xml;base64,'
                . base64_encode($qrSvg);
        }

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
            'hasUnfinishedOrders' => $hasUnfinishedOrders,
            'qrUrl' => $qrUrl,
            'inventoryLogDisplays' => $inventoryLogDisplays,
        ]);
    }
}