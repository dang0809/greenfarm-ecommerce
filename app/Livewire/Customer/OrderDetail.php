<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Livewire\Traits\WithOrderActions;

class OrderDetail extends Component
{
    use WithFileUploads, WithOrderActions;

    public string $order_code = '';
    public ?int $orderId = null;

    public function mount(string $order_code)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('order_code', $order_code)
            ->firstOrFail();

        $this->order_code = $order->order_code;
        $this->orderId    = $order->id;
    }

    public function render()
    {
        $order = Order::with([
            'items.product.productImages',
            'items.batch',
            'statusHistories.changer',
            'payment',
            'complaint',
            'deliverySchedule.subscription',
        ])
        ->where('user_id', Auth::id())
        ->where('order_code', $this->order_code)
        ->firstOrFail();

        $deliveredHistory = $order->statusHistories?->firstWhere('new_status', 'delivered');
        $deliveredAt = $deliveredHistory ? $deliveredHistory->created_at : $order->updated_at;
        $canRefund = ($order->status === 'delivered' && $order->order_type === 'retail' && !$order->complaint && $deliveredAt && $deliveredAt->diffInHours(now()) < 24);

        return view('livewire.customer.order-detail', [
            'order'              => $order,
            'canRefund'          => $canRefund,
            'showComplaintModal' => $this->showComplaintModal,
        ])->layout('layouts.customer.app', [
            'title' => "Chi Tiết Đơn Hàng #{$order->order_code} | Nông Trại Đất Lành"
        ]);
    }
}
