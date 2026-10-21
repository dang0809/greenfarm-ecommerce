<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;

class OrdersPage extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public bool $showCancelModal = false;
    public ?int $cancellingOrderId = null;
    public string $cancel_reason = 'Khách đổi ý không muốn mua nữa';
    public string $bank_name = '';
    public string $bank_account_number = '';
    public string $bank_account_name = '';

    public function setStatusFilter(string $status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    // Kích hoạt khi bấm nút "Hủy đơn"
    public function confirmCancel(int $orderId)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->find($orderId);

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Không thể hủy đơn hàng này hoặc đơn đã chuyển sang bước xử lý!');
            return;
        }

        // Khóa không cho tự hủy gói VIP hoặc đơn giao rau VIP online
        if (in_array($order->order_type, ['subscription', 'subscription_delivery'])) {
            $this->dispatch('show-toast', type: 'error', message: 'Gói VIP định kỳ không hỗ trợ tự hủy online. Vui lòng liên hệ CSKH!');
            return;
        }

        // 1. Đơn CHƯA thanh toán (Tab Chờ thanh toán) -> Hủy ngay lập tức
        if ($order->payment_status !== 'paid') {
            $this->executeCancelOrder($order);
            return;
        }

        // 2. Đơn ĐÃ thanh toán (Tab Chờ xác nhận) -> Nạp STK & Mở Modal
        $user = Auth::user();
        $this->bank_name           = $user->default_bank_name ?? '';
        $this->bank_account_number = $user->default_bank_account_number ?? '';
        $this->bank_account_name   = $user->default_bank_account_name ?? mb_strtoupper($user->name);

        $this->cancellingOrderId = $order->id;
        $this->showCancelModal   = true;
    }

    // Thực thi Hủy đơn khi submit Form Modal nhập STK
    public function processPaidCancel()
    {
        $this->validate([
            'cancel_reason'       => 'required|string|max:255',
            'bank_name'           => 'required|string',
            'bank_account_number' => 'required|string|max:30',
            'bank_account_name'   => 'required|string|max:100',
        ], [
            'cancel_reason.required'       => 'Vui lòng chọn hoặc nhập lý do hủy.',
            'bank_name.required'           => 'Vui lòng chọn ngân hàng nhận lại tiền.',
            'bank_account_number.required' => 'Vui lòng nhập số tài khoản ngân hàng.',
            'bank_account_name.required'   => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        $order = Order::with('items')->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->find($this->cancellingOrderId);

        if ($order && $order->order_type === 'retail') {
            $bankAccName = mb_strtoupper($this->bank_account_name);

            $this->executeCancelOrder($order, [
                'bank_name'           => $this->bank_name,
                'bank_account_number' => $this->bank_account_number,
                'bank_account_name'   => $bankAccName,
                'refund_status'       => 'pending',
            ]);

            Auth::user()->update([
                'default_bank_name'           => $this->bank_name,
                'default_bank_account_number' => $this->bank_account_number,
                'default_bank_account_name'   => $bankAccName,
            ]);
        }

        $this->resetCancelModal();
    }

    // Core xử lý Hủy đơn, Restock kho & Hoàn điểm thưởng
    private function executeCancelOrder(Order $order, array $extraData = [])
    {
        DB::transaction(function () use ($order, $extraData) {
            $oldStatus = $order->status;

            $order->update(array_merge([
                'status'        => 'cancelled',
                'cancel_reason' => $this->cancel_reason,
                'cancelled_at'  => now(),
                'cancelled_by'  => 'customer',
            ], $extraData));

            if (method_exists($order, 'statusHistories')) {
                $order->statusHistories()->create([
                    'old_status' => $oldStatus,
                    'new_status' => 'cancelled',
                    'changed_by' => Auth::id(),
                    'note'       => 'Khách hàng tự hủy đơn. Lý do: ' . $this->cancel_reason,
                ]);
            }

            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->increment('stock_quantity', $item->quantity);
                }
                if ($item->batch_id) {
                    DB::table('product_batches')
                        ->where('id', $item->batch_id)
                        ->increment('quantity', $item->quantity);
                }
            }

            if ($order->points_used > 0) {
                Auth::user()->increment('reward_points', $order->points_used);
            }
        });

        $this->dispatch('show-toast', type: 'success', message: 'Đã hủy thành công đơn hàng #' . $order->order_code);
    }

    public function resetCancelModal()
    {
        $this->reset(['showCancelModal', 'cancellingOrderId', 'cancel_reason', 'bank_name', 'bank_account_number', 'bank_account_name']);
    }

    // Mua lại đơn hàng bán lẻ
    public function reorder(int $orderId)
    {
        $order = Order::with('items.product')->where('user_id', Auth::id())->find($orderId);
        if (!$order || $order->order_type !== 'retail') return;

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $addedCount = 0;

        foreach ($order->items as $item) {
            if (!$item->product) continue;

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
            $addedCount++;
        }

        if ($addedCount > 0) {
            $this->dispatch('cart-updated');
            $this->dispatch('show-toast', type: 'success', message: 'Đã thêm các sản phẩm vào giỏ hàng!');
            return redirect()->route('customer.cart');
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'Các sản phẩm trong đơn hàng này hiện không còn tồn tại!');
        }
    }

    public function render()
    {
        $userId = Auth::id();

        // 1. Chờ thanh toán: Đơn pending chưa trả tiền
        $pendingPaymentQuery = fn($q) => $q->where('status', 'pending')->where('payment_status', 'pending');

        // 2. Chờ xác nhận: Đơn pending đã trả tiền (Gồm Đơn lẻ + Đơn giao rau VIP từng đợt)
        $pendingConfirmationQuery = fn($q) => $q->where('status', 'pending')->where('payment_status', 'paid');

        // 3. Chờ lấy hàng (processing)
        $processingQuery = fn($q) => $q->where('status', 'processing');

        // 4. Đang giao (shipped)
        $shippedQuery = fn($q) => $q->where('status', 'shipped');

        // 5. Đã giao (delivered)
        $deliveredQuery = fn($q) => $q->where('status', 'delivered');

        // 6. Đã hủy: Ẩn đơn rác tự hủy do hết hạn QR system
        $cancelledQuery = fn($q) => $q->where('status', 'cancelled')->where('cancelled_by', '!=', 'system');

        // 7. Trả hàng / Hoàn tiền
        $refundQuery = fn($q) => $q->where(fn($sub) => $sub->whereIn('refund_status', ['pending', 'refunded'])->orWhereHas('complaint'));

        // Scope chuẩn bỏ đơn nháp system
        $baseUserOrders = Order::where('user_id', $userId)
            ->where(fn($q) => $q->where('status', '!=', 'cancelled')->orWhere('cancelled_by', '!=', 'system'));

        $counts = [
            'all'                  => (clone $baseUserOrders)->count(),
            'pending_payment'      => $pendingPaymentQuery(Order::where('user_id', $userId))->count(),
            'pending_confirmation' => $pendingConfirmationQuery(Order::where('user_id', $userId))->count(),
            'processing'           => $processingQuery(Order::where('user_id', $userId))->count(),
            'shipped'              => $shippedQuery(Order::where('user_id', $userId))->count(),
            'delivered'            => $deliveredQuery(Order::where('user_id', $userId))->count(),
            'cancelled'            => $cancelledQuery(Order::where('user_id', $userId))->count(),
            'refund'               => $refundQuery(Order::where('user_id', $userId))->count(),
        ];

        $query = Order::with(['items.product.productImages', 'items.batch', 'complaint', 'statusHistories'])
            ->where('user_id', $userId);

        if ($this->statusFilter === 'pending_payment') {
            $pendingPaymentQuery($query);
        } elseif ($this->statusFilter === 'pending_confirmation') {
            $pendingConfirmationQuery($query);
        } elseif ($this->statusFilter === 'processing') {
            $processingQuery($query);
        } elseif ($this->statusFilter === 'shipped') {
            $shippedQuery($query);
        } elseif ($this->statusFilter === 'delivered') {
            $deliveredQuery($query);
        } elseif ($this->statusFilter === 'cancelled') {
            $cancelledQuery($query);
        } elseif ($this->statusFilter === 'refund') {
            $refundQuery($query);
        } else {
            $query->where(fn($q) => $q->where('status', '!=', 'cancelled')->orWhere('cancelled_by', '!=', 'system'));
        }

        return view('livewire.customer.orders-page', [
            'orders' => $query->latest()->paginate(10),
            'counts' => $counts,
        ]);
    }
}