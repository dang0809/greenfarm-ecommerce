<?php

namespace App\Livewire\Sales;

use App\Models\Complaint;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\ProductBatch;
use Illuminate\Validation\ValidationException;
use App\Services\OrderNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sales.app')]
#[Title('Xử Lý Khiếu Nại & Bồi Thường')]
class ComplaintManager extends Component
{
    use WithPagination;

    // Bộ lọc & Tìm kiếm (Mặc định hiển thị tất cả hoặc pending)
    public string $statusFilter = 'all'; // all, pending, resolved, rejected
    public string $search = '';          // Chỉ tìm theo mã đơn hàng
    public int $perPage = 15;

    // Modal Thẩm định & Phê duyệt
    public bool $showModal = false;
    public ?int $selectedComplaintId = null;
    public ?Complaint $selectedComplaint = null;

    // Dữ liệu form phê duyệt
    public string $resolution_type = 'refund'; // refund | reject
    public string $reject_reason = '';
    public string $resolution_note = '';

    // Modal Phóng to ảnh bằng chứng
    public bool $showZoomModal = false;
    public string $zoomImageUrl = '';

    // Reset phân trang khi gõ tìm kiếm hoặc đổi tab
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    /**
     * Tự động bắt sự kiện khiếu nại mới từ WebSockets (Reverb)
     */
    #[On('echo:sales-complaints,.ComplaintSubmitted')]
    public function onComplaintSubmittedReceived(array $payload = []): void
    {
        // 1. Đưa phân trang về trang 1 để khiếu nại mới nhất hiển thị ngay
        $this->resetPage();

        // 2. Lấy dữ liệu và hiển thị Toast thông báo màu cam/vàng góc màn hình
        $complaintData = $payload['complaintData'] ?? [];
        $orderCode = $complaintData['order_code'] ?? '---';
        $customerName = $complaintData['customer_name'] ?? 'Khách hàng';
        $amount = !empty($complaintData['refund_amount'])
            ? number_format($complaintData['refund_amount'], 0, ',', '.') . 'đ'
            : '';

        $message = "⚠️ KHIẾU NẠI MỚI đơn #{$orderCode} từ {$customerName}";
        if ($amount) {
            $message .= " ({$amount})";
        }
        $message .= "!";

        $this->dispatch('show-toast', type: 'warning', message: $message);
    }

    /**
     * Thống kê KPI trên đầu trang
     */
    public function getKpiPendingCountProperty(): int
    {
        return Complaint::where('status', 'pending')->count();
    }

    public function getKpiRefundAmountProperty(): float
    {
        return (float) Complaint::where('status', 'resolved')->sum('refund_amount');
    }

    public function getKpiRefundCountProperty(): int
    {
        return Complaint::where('status', 'resolved')->count();
    }

    /**
     * Đếm số lượng theo tab trạng thái
     */
    public function getStatusCountsProperty(): array
    {
        $counts = Complaint::selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->toArray();

        return [
            'all' => array_sum($counts),
            'pending' => (int) ($counts['pending'] ?? 0),
            'resolved' => (int) ($counts['resolved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
        ];
    }

    /**
     * Mở modal thẩm định & xử lý khiếu nại
     */
    public function openComplaint(int $complaintId): void
    {
        $this->selectedComplaintId = $complaintId;
        $this->selectedComplaint = Complaint::with([
            'order.items.product',
            'order.items.batch',
            'order.statusHistories',
            'user',
            'images',
            'resolver',
        ])->find($complaintId);

        if (!$this->selectedComplaint) {
            $this->dispatch('show-toast', type: 'error', message: 'Không tìm thấy thông tin khiếu nại!');
            return;
        }

        $this->resolution_type = 'refund';
        $this->reject_reason = '';
        $this->resolution_note = $this->selectedComplaint->resolution_note ?? '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedComplaintId = null;
        $this->selectedComplaint = null;
        $this->reject_reason = '';
        $this->resolution_note = '';
    }

    /**
     * Phóng to ảnh bằng chứng
     */
    public function openZoom(string $url): void
    {
        $this->zoomImageUrl = $url;
        $this->showZoomModal = true;
    }

    public function closeZoom(): void
    {
        $this->showZoomModal = false;
        $this->zoomImageUrl = '';
    }

    /**
     * Xác nhận xử lý khiếu nại (Hoàn tiền cố định theo $order->total hoặc Từ chối)
     */
    public function confirmResolution(): void
    {
        $staff = Auth::guard('web')->user();

        abort_unless(
            $staff
            && $staff->role === 'sales'
            && $staff->status === 'active',
            403
        );

        if (!$this->selectedComplaintId) {
            return;
        }

        $resolution = $this->resolution_type;
        $rejectReason = trim($this->reject_reason);
        $resolutionNote = trim($this->resolution_note);

        if (!in_array($resolution, ['refund', 'reject'], true)) {
            $this->dispatch(
                'show-toast',
                type: 'error',
                message: 'Phương án xử lý không hợp lệ.'
            );

            return;
        }

        if ($resolution === 'reject' && $rejectReason === '') {
            $this->dispatch(
                'show-toast',
                type: 'error',
                message: 'Vui lòng chọn hoặc nhập lý do từ chối.'
            );

            return;
        }

        $complaintId = (int) $this->selectedComplaintId;

        $orderId = Complaint::query()
            ->whereKey($complaintId)
            ->value('order_id');

        if (!$orderId) {
            $this->dispatch(
                'show-toast',
                type: 'error',
                message: 'Không tìm thấy khiếu nại.'
            );

            return;
        }

        try {
            $result = DB::transaction(function () use ($complaintId, $orderId, $staff, $resolution, $rejectReason, $resolutionNote) {
                // Khóa đơn trước, đồng nhất với luồng thanh toán/hủy đơn.
                $order = Order::query()
                    ->whereKey($orderId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $complaint = Complaint::query()
                    ->whereKey($complaintId)
                    ->where('order_id', $order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Kiểm tra sau khi khóa để tránh hai request cùng hoàn kho.
                if ($complaint->status !== 'pending') {
                    return null;
                }

                if ($resolution === 'reject') {
                    $complaint->update([
                        'status' => 'rejected',
                        'resolved_by' => $staff->id,
                        'resolution_note' => $rejectReason,
                    ]);

                    return $complaint;
                }

                // Khớp điều kiện khiếu nại hiện tại của khách hàng.
                // Đơn cancelled không được hoàn kho thêm lần nữa.
                if (
                    $order->order_type !== 'retail'
                    || $order->status !== 'delivered'
                    || !$order->payments()
                        ->where('status', 'success')
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'resolution_type' =>
                            'Chỉ hoàn kho cho đơn mua lẻ đã thanh toán và giao thành công.',
                    ]);
                }

                $order->load([
                    'items.product',
                    'user:id,name',
                ]);

                // Phải xác định được toàn bộ lô gốc trước khi hoàn kho.
                if (
                    $order->items->isEmpty()
                    || $order->items->contains(
                        fn($item) =>
                            $item->batch_id === null
                            || (int) $item->quantity <= 0
                    )
                ) {
                    throw ValidationException::withMessages([
                        'resolution_type' =>
                            'Đơn hàng có sản phẩm thiếu lô gốc hoặc số lượng không hợp lệ. Vui lòng kiểm tra trước khi hoàn kho.',
                    ]);
                }

                // Một đơn có thể lấy cùng sản phẩm từ nhiều lô.
                $groups = $order->items->groupBy('batch_id');

                $batches = ProductBatch::query()
                    ->whereKey($groups->keys())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($batches->count() !== $groups->count()) {
                    throw ValidationException::withMessages([
                        'resolution_type' =>
                            'Không tìm thấy đầy đủ các lô gốc để hoàn kho.',
                    ]);
                }

                $customerName = $order->user?->name
                    ?: ($order->recipient_name ?: 'Khách hàng');

                // Hoàn toàn bộ hàng: điều chỉnh bộ đếm đã bán một lần.
                $order->adjustProductSoldCounts(-1);

                foreach ($groups as $batchId => $items) {
                    $batch = $batches->get($batchId);

                    // Chỉ hoàn số lượng từng lấy từ chính lô này.
                    $quantity = (int) $items->sum('quantity');
                    $unit = $items->first()->product?->unit ?? 'đơn vị';

                    $before = (int) $batch->current_quantity;
                    $after = $before + $quantity;

                    $batch->update([
                        'current_quantity' => $after,
                    ]);

                    InventoryLog::create([
                        'batch_id' => $batch->id,
                        'type' => 'return_refund',
                        'quantity' => $quantity,
                        'created_by' => $staff->id,

                        'note' => "Khách hàng: {$customerName}\n"
                            . "Mã đơn: #{$order->order_code}\n"
                            . "Hoàn kho khi duyệt hoàn tiền toàn bộ đơn.\n"
                            . "Nhập lại vào lô: {$quantity} {$unit}\n"
                            . "Tồn kho: {$before} → {$after}"
                            . (
                                $resolutionNote !== ''
                                ? "\nGhi chú: {$resolutionNote}"
                                : ''
                            ),
                    ]);
                }

                foreach (
                    $order->items->pluck('product_id')->unique()->sort()
                    as $productId
                ) {
                    ProductBatch::syncProductSalePrice((int) $productId);
                }

                // Cập nhật cùng transaction với cộng kho và ghi nhật ký.
                $complaint->update([
                    'status' => 'resolved',
                    'refund_amount' => $order->total,
                    'resolved_by' => $staff->id,
                    'resolution_note' => $resolutionNote,
                ]);

                return $complaint;
            }, 3);
        } catch (ValidationException $exception) {
            $this->dispatch(
                'show-toast',
                type: 'error',
                message: (string) collect($exception->errors())
                    ->flatten()
                    ->first()
            );

            return;
        } catch (\Throwable $exception) {
            report($exception);

            $this->dispatch(
                'show-toast',
                type: 'error',
                message: 'Không thể xử lý khiếu nại và hoàn kho. Vui lòng thử lại.'
            );

            return;
        }

        if (!$result) {
            $this->dispatch(
                'show-toast',
                type: 'info',
                message: 'Khiếu nại này đã được xử lý trước đó.'
            );

            $this->openComplaint($complaintId);

            return;
        }

        $result->loadMissing('order');
        $orderCode = $result->order?->order_code ?? '';

        // Gửi thông báo sau khi giao dịch database đã hoàn tất.
        if ($result->status === 'resolved') {
            OrderNotificationService::notifyComplaintResolved($result);

            $this->dispatch(
                'show-toast',
                type: 'success',
                message: "Đã duyệt hoàn tiền và hoàn kho cho đơn #{$orderCode}."
            );
        } else {
            OrderNotificationService::notifyComplaintRejected(
                $result,
                $rejectReason
            );

            $this->dispatch(
                'show-toast',
                type: 'warning',
                message: "Đã từ chối khiếu nại cho đơn #{$orderCode}."
            );
        }

        $this->dispatch('complaint-status-changed');
        $this->openComplaint($complaintId);
    }

    public function render()
    {
        $query = Complaint::with([
            'order.items.product',
            'order.items.batch',
            'user',
            'images',
            'resolver',
        ])->latest('id');

        // Lọc theo Tab trạng thái
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Lọc tìm kiếm theo mã đơn hàng
        $rawSearch = ltrim(trim($this->search), '#');
        if ($rawSearch !== '') {
            $query->whereHas('order', function ($q) use ($rawSearch) {
                $q->where('order_code', 'like', "%{$rawSearch}%");
            });
        }

        $complaints = $query->paginate($this->perPage);

        return view('livewire.sales.complaint-manager', [
            'complaints' => $complaints,
            'kpiPendingCount' => $this->kpiPendingCount,
            'kpiRefundAmount' => $this->kpiRefundAmount,
            'kpiRefundCount' => $this->kpiRefundCount,
            'statusCounts' => $this->statusCounts,
        ]);
    }
}