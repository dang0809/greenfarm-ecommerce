<?php

namespace App\Livewire\Sales;

use App\Models\Complaint;
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
        if (!$this->selectedComplaintId)
            return;

        $complaint = Complaint::with('order')->find($this->selectedComplaintId);
        if (!$complaint || $complaint->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Khiếu nại này đã được xử lý trước đó!');
            return;
        }

        if ($this->resolution_type === 'refund') {
            // Số tiền hoàn cố định bằng tổng thanh toán của đơn hàng (tiền hàng + ship - điểm)
            $refundAmount = $complaint->order ? (float) $complaint->order->total : (float) ($complaint->refund_amount ?? 0);

            DB::transaction(function () use ($complaint, $refundAmount) {
                $complaint->update([
                    'status' => 'resolved',
                    'refund_amount' => $refundAmount,
                    'resolved_by' => Auth::id() ?? 1,
                    'resolution_note' => trim($this->resolution_note),
                    'updated_at' => now(),
                ]);
            });

            $formatted = number_format($refundAmount, 0, ',', '.') . 'đ';
            $orderCode = $complaint->order?->order_code ?? '';

            // Gửi Realtime Notification cho khách hàng: đã hoàn tiền xong
            OrderNotificationService::notifyComplaintResolved($complaint);

            $this->dispatch('show-toast', type: 'success', message: "Đã duyệt hoàn tiền {$formatted} cho đơn #{$orderCode} thành công!");

        } elseif ($this->resolution_type === 'reject') {
            $reason = trim($this->reject_reason);
            if (empty($reason)) {
                $this->dispatch('show-toast', type: 'error', message: 'Vui lòng chọn hoặc nhập lý do từ chối khiếu nại!');
                return;
            }

            DB::transaction(function () use ($complaint, $reason) {
                $complaint->update([
                    'status' => 'rejected',
                    'resolved_by' => Auth::id() ?? 1,
                    'resolution_note' => $reason,
                    'updated_at' => now(),
                ]);
            });

            $orderCode = $complaint->order?->order_code ?? '';

            // Gửi Realtime Notification cho khách hàng: từ chối khiếu nại
            OrderNotificationService::notifyComplaintRejected($complaint, $reason);

            $this->dispatch('show-toast', type: 'warning', message: "Đã từ chối khiếu nại cho đơn #{$orderCode}.");
        }

        // Bắn sự kiện cập nhật số đếm badge đỏ trên Sidebar Sales
        $this->dispatch('complaint-status-changed');

        // Cập nhật lại bản ghi hiển thị trên modal
        $this->openComplaint($this->selectedComplaintId);
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