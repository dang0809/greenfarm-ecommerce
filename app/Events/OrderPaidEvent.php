<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaidEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $orderData;

    public function __construct(Order $order)
    {
        $this->orderData = [
            'id'             => $order->id,
            'order_code'     => $order->order_code,
            'recipient_name' => $order->recipient_name,
            'total'          => $order->total,
            'created_at'     => $order->created_at ? $order->created_at->format('H:i • d/m/Y') : now()->format('H:i • d/m/Y'),
        ];
    }

    /**
     * Kênh phát sóng công khai cho nội bộ nhân viên bán hàng
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sales-orders'),
        ];
    }

    /**
     * Tên sự kiện phát ra
     */
    public function broadcastAs(): string
    {
        return 'OrderPaid';
    }
}

