<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;

class OrdersPage extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public function setStatusFilter(string $status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    // Hủy đơn hàng
    public function cancelOrder(int $orderId)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'pending_payment'])
            ->find($orderId);

        if ($order) {
            $order->update(['status' => 'cancelled']);
            $this->dispatch('show-toast', type: 'success', message: 'Đã hủy đơn hàng #' . $order->order_code);
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'Không thể hủy đơn hàng này!');
        }
    }

    // Mua lại đơn hàng
    public function reorder(int $orderId)
    {
        $order = Order::with('orderItems')->where('user_id', Auth::id())->find($orderId);
        if (!$order) return;

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        foreach ($order->orderItems as $item) {
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $item->product_id)
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $item->quantity);
            } else {
                CartItem::create([
                    'cart_id'    => $cart->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                ]);
            }
        }

        $this->dispatch('cart-updated');
        $this->dispatch('show-toast', type: 'success', message: 'Đã thêm các sản phẩm vào giỏ hàng!');
        return redirect()->route('customer.cart');
    }

    public function render()
    {
        $user = Auth::user();

        // Đếm số lượng theo từng trạng thái
        $counts = [
            'all'                  => Order::where('user_id', $user->id)->count(),
            'pending_payment'      => Order::where('user_id', $user->id)->where('payment_status', 'pending')->where('status', '!=', 'cancelled')->count(),
            'pending_confirmation' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'delivering'           => Order::where('user_id', $user->id)->where('status', 'shipping')->count(),
            'delivered'            => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'cancelled'            => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        // Lọc danh sách đơn hàng
        $query = Order::with(['orderItems.product.productImages', 'orderItems.batch'])
            ->where('user_id', $user->id);

        if ($this->statusFilter === 'pending_payment') {
            $query->where('payment_status', 'pending')->where('status', '!=', 'cancelled');
        } elseif ($this->statusFilter === 'pending_confirmation') {
            $query->where('status', 'pending');
        } elseif ($this->statusFilter === 'delivering') {
            $query->where('status', 'shipping');
        } elseif ($this->statusFilter === 'delivered') {
            $query->where('status', 'completed');
        } elseif ($this->statusFilter === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $orders = $query->latest()->paginate(10);

        return view('livewire.customer.orders-page', [
            'orders' => $orders,
            'counts' => $counts,
        ]);
    }
}