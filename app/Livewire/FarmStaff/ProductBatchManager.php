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

    #[Locked]
    public bool $destroyAll = false;

    #[Locked]
    public int $destroyQuantity = 0;

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
            // Chỉ validate số lượng nhập tay với xuất hủy thông thường.
            if (!$this->destroyAll) {
                $rules['quantity'] = [
                    'required',
                    'integer',
                    'min:1',
                    'max:2147483647',
                ];
            }
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
                    'pending_recalled',
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
            $mode === 'qr'
            && $batch->status === 'pending_recalled'
        ) {
            $this->dispatch(
                'swal',
                icon: 'info',
                title: 'Lô đang chờ tiêu hủy nên không mở mã QR.'
            );

            return;
        }

        if (
            $mode !== 'detail'
            && (int) $batch->current_quantity <= 0
        ) {
            $this->dispatch(
                'swal',
                icon: 'info',
                title: 'Lô đã hết tồn. Chỉ được xem chi tiết.'
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
            && (
                $batch->expiry_date->lt(today())
                || $batch->status === 'pending_recalled'
            )
        ) {
            $this->dispatch(
                'swal',
                icon: 'error',
                title: 'Lô đã hết hạn hoặc chờ tiêu hủy. Không được sửa HSD, cân chỉnh hoặc đổi trạng thái.'
            );

            return;
        }

        $this->closeModal();

        $this->batchId = $batch->id;
        $this->modal = $mode;

        $this->destroyAll = $mode === 'spoilage'
            && $batch->status === 'pending_recalled';

        $this->destroyQuantity = $this->destroyAll
            ? (int) $batch->current_quantity
            : 0;

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
            'destroyAll',
            'destroyQuantity',
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
        $this->resetValidation('form');
        $this->validatedInput();

        // Chưa cập nhật DB ở bước yêu cầu xác nhận.
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
                    || $batch->status === 'recalled'
                ) {
                    $this->reject(
                        'form',
                        'Lô đã hết tồn hoặc đã tiêu hủy. Chỉ được xem chi tiết.'
                    );
                }

                // Giữ quy tắc đã thống nhất:
                // hết hạn hoặc chờ tiêu hủy không được sửa lại dữ liệu lô.
                if (
                    in_array($mode, ['expiry', 'stock', 'status'], true)
                    && (
                        $batch->expiry_date->lt(today())
                        || $batch->status === 'pending_recalled'
                    )
                ) {
                    $this->reject(
                        'form',
                        'Lô đã hết hạn hoặc đang chờ tiêu hủy. Không được sửa HSD, cân chỉnh hoặc đổi trạng thái.'
                    );
                }

                $type = '';
                $logQuantity = 0;

                // Ghi nhận dữ liệu trước khi cập nhật.
                $statusBefore = (string) $batch->status;
                $expiryBefore = $batch->expiry_date?->toDateString();

                if ($mode === 'spoilage') {
                    $pending = $batch->status === 'pending_recalled';

                    if ($pending !== $this->destroyAll) {
                        $this->reject(
                            'form',
                            'Trạng thái lô đã đổi. Đóng và mở lại modal.'
                        );
                    }

                    if ($this->destroyAll) {
                        // Tiêu hủy toàn bộ:
                        // phải xử lý cả giữ chỗ và các đơn đang phân bổ.
                        if (
                            $reserved > 0
                            || $batch->hasUnfinishedOrders()
                        ) {
                            $this->reject(
                                'form',
                                'Còn giữ chỗ hoặc đơn chưa kết thúc. Sales cần xử lý các phân bổ trước khi tiêu hủy toàn bộ.'
                            );
                        }

                        if ($current !== $this->destroyQuantity) {
                            $this->reject(
                                'form',
                                'Tồn kho đã thay đổi. Mở lại modal để xác nhận số lượng mới.'
                            );
                        }

                        // Dùng số lượng thật từ bản ghi đã khóa.
                        $qty = $current;
                    } else {
                        // Xuất hủy thông thường:
                        // chỉ được dùng phần chưa giữ cho đơn hàng.
                        $qty = (int) $data['quantity'];

                        if ($qty > $current - $reserved) {
                            $this->reject(
                                'quantity',
                                'Số lượng xuất hủy vượt phần tồn chưa giữ chỗ.'
                            );
                        }

                        // Không hoàn tất tiêu hủy toàn lô khi vẫn còn
                        // các đơn liên quan chưa xử lý.
                        if (
                            $qty === $current
                            && $batch->hasUnfinishedOrders()
                        ) {
                            $this->reject(
                                'form',
                                'Xuất hủy hết tồn cần xử lý các đơn chưa kết thúc trước.'
                            );
                        }
                    }

                    if (
                        $qty <= 0
                        || $spoiled > 2147483647 - $qty
                    ) {
                        $this->reject(
                            'quantity',
                            'Số lượng xuất hủy không hợp lệ hoặc vượt giới hạn.'
                        );
                    }

                    $batch->current_quantity = $current - $qty;
                    $batch->spoiled_quantity = $spoiled + $qty;

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

                    // Chỉ việc thu hồi toàn bộ yêu cầu giải quyết
                    // các phân bổ trước. Tạm dừng không bị chặn.
                    if (
                        $target === 'pending_recalled'
                        && (
                            $reserved > 0
                            || $batch->hasUnfinishedOrders()
                        )
                    ) {
                        $this->reject(
                            'newStatus',
                            'Hãy Tạm dừng lô trước. Sales cần chuyển lô hoặc xử lý các đơn liên quan, rồi mới Thu hồi toàn bộ.'
                        );
                    }

                    $batch->status = $target;
                    $type = 'status_change';
                }

                if (!$batch->save()) {
                    throw new RuntimeException(
                        'Không lưu được lô hàng.'
                    );
                }

                $log = InventoryLog::create([
                    'batch_id' => $batch->id,
                    'type' => $type,
                    'quantity' => $logQuantity,

                    'note' => json_encode([
                        'format' => 'batch-log-v1',

                        // Chỉ chứa lý do người dùng nhập.
                        'reason' => $data['note'],

                        // Dữ liệu lịch sử, lấy trước/sau thao tác.
                        'status_from' => $statusBefore,
                        'status_to' => (string) $batch->status,

                        'quantity_from' => $current,
                        'quantity_to' => (int) $batch->current_quantity,

                        'expiry_from' => $expiryBefore,
                        'expiry_to' => $batch->expiry_date?->toDateString(),
                    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),

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
                    '/\s+(?:Đã tiêu hủy hết tồn của lô\.|Đã xuất hủy hết tồn; chuyển Đã tiêu hủy\.|Đã xuất hủy hết tồn; chuyển recalled\.|Còn \d+ đơn vị chờ tiêu hủy; giữ trạng thái pending_recalled\.)\z/u',
                    '',
                    $reason
                ) ?? $reason;
            }
        }

        $statusLabels = [
            'active' => 'Đang bán',
            'suspended' => 'Tạm dừng',
            'pending_recalled' => 'Chờ tiêu hủy',
            'recalled' => 'Đã tiêu hủy',
        ];

        $transition = $from !== null && $to !== null
            ? ($statusLabels[$from] ?? $from)
            . ' → '
            . ($statusLabels[$to] ?? $to)
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
            case 'pending_recalled':
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
            && in_array(
                $this->modal,
                ['status', 'spoilage', 'detail'],
                true
            )
            && $selectedBatch->hasUnfinishedOrders();

        $qrUrl = null;

        if (
            $selectedBatch
            && (int) $selectedBatch->current_quantity > 0
            && !in_array(
                $selectedBatch->status,
                ['pending_recalled', 'recalled'],
                true
            )
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
            'qrUrl' => $qrUrl,
            'hasUnfinishedOrders' => $hasUnfinishedOrders,
            'inventoryLogDisplays' => $inventoryLogDisplays,
        ]);
    }
}