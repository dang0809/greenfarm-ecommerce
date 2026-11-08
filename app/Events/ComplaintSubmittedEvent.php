<?php

namespace App\Events;

use App\Models\Complaint;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplaintSubmittedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $complaintData;

    public function __construct(Complaint $complaint)
    {
        $complaint->loadMissing(['order', 'user']);

        $this->complaintData = [
            'id'            => $complaint->id,
            'order_id'      => $complaint->order_id,
            'order_code'    => $complaint->order->order_code,
            'customer_name' => $complaint->user->name,
            'reason'        => $complaint->reason,
            'refund_amount' => (float) $complaint->refund_amount,
            'status'        => $complaint->status,
            'created_at'    => $complaint->created_at->format('H:i • d/m/Y'),
        ];
    }

    /**
     * Kênh phát sóng công khai cho nội bộ nhân viên bán hàng
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sales-complaints'),
        ];
    }

    /**
     * Tên sự kiện phát ra
     */
    public function broadcastAs(): string
    {
        return 'ComplaintSubmitted';
    }
}

