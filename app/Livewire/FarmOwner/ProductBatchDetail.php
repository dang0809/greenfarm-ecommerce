<?php

namespace App\Livewire\FarmOwner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\InventoryLog;
use App\Models\ProductBatch;
use App\Models\TraceabilityEvent;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class ProductBatchDetail extends Component
{
    use WithPagination;

    #[Locked]
    public int $batchId;

    public function boot(): void
    {
        $owner = Auth::id()
            ? User::find(Auth::id())
            : null;

        abort_unless(
            $owner
            && $owner->role === 'farm_owner'
            && $owner->status === 'active',
            403,
            'Bạn không có quyền truy cập khu vực chủ trang trại.'
        );
    }

    public function mount(int $batch): void
    {
        $this->batchId = (int) ProductBatch::findOrFail($batch)->id;
    }

    public function render()
    {
        $batch = ProductBatch::query()
            ->with(['plantingBatch.product', 'plantingBatch.farmPlot'])
            ->findOrFail($this->batchId);

        $logs = $batch->inventoryLogs()
            ->with('creator:id,name')
            ->latest('created_at')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'inventoryPage');

        $events = TraceabilityEvent::query()
            ->where('planting_batch_id', $batch->planting_batch_id)
            ->latest('recorded_at')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'cultivationPage');

        $types = [
            'import' => ['Nhập kho thu hoạch', '+'],
            'export_sale' => ['Xuất bán', '-'],
            'export_spoilage' => ['Xuất hủy', '-'],
            'return_cancel' => ['Hoàn kho do hủy đơn', '+'],
            'return_refund' => ['Hoàn kho do hoàn tiền', '+'],
            'adjustment_in' => ['Kiểm kê tăng', '+'],
            'adjustment_out' => ['Kiểm kê giảm', '-'],
            'expiry_change' => ['Sửa hạn sử dụng', ''],
            'status_change' => ['Đổi trạng thái', ''],
        ];

        $logDisplays = [];

        foreach ($logs as $log) {
            [$label, $sign] = $types[$log->type] ?? [$log->type, ''];

            $logDisplays[$log->id] = $this->inventoryLogDisplay($log) + [
                'label' => $label,
                'quantityText' => $log->quantity === 0
                    ? '—'
                    : $sign . number_format(abs($log->quantity), 0, ',', '.'),
            ];
        }

        $eventImages = [];

        foreach ($events as $event) {
            $path = trim((string) $event->image);

            $eventImages[$event->id] = $path === ''
                ? null
                : (preg_match('/\Ahttps?:\/\//i', $path)
                    ? $path
                    : asset('storage/' . ltrim($path, '/')));
        }

        return view('livewire.farm-owner.product-batch-detail', [
            'batch' => $batch,
            'logs' => $logs,
            'events' => $events,
            'logDisplays' => $logDisplays,
            'eventImages' => $eventImages,
        ])->layout('layouts.farm-owner.app', [
                    'title' => 'Chi tiết lô ' . $batch->batch_code,
                ]);
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
}