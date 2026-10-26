<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Complaint;
use App\Models\Payment;

class OrdersPage extends Component
{
    use WithPagination, WithFileUploads;

    public string $statusFilter = 'all';

    // State cho Modal Hủy đơn đã thanh toán
    public bool $showCancelModal = false;
    public ?int $cancellingOrderId = null;
    public string $cancel_reason = 'Khách đổi ý không muốn mua nữa';
    public string $bank_name = '';
    public string $bank_account_number = '';
    public string $bank_account_name = '';

    // State cho Modal Khiếu nại hoàn tiền nông sản dập nát (24H)
    public bool $showComplaintModal = false;
    public ?int $complainingOrderId = null;
    public string $complaint_reason = 'Nông sản bị dập nát / hư hỏng trong quá trình vận chuyển';
    public ?int $complaint_refund_amount = null;
    public array $complaint_images = [];

    //filter trạng thái đơn hàng
    public function setStatusFilter(string $status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    //Thanh toán lại đơn hàng
    public function payNow(int $orderId)
    {
        $order = Order::with('items')->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->find($orderId);

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng không hợp lệ hoặc đã thanh toán!');
            return;
        }

        // Nếu đơn hàng đã quá hạn 4 phút, không cho phép thanh toán tiếp
        if ($order->created_at->diffInMinutes(now()) >= 4) {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng đã quá hạn thanh toán.');
            return;
        }

        try {
            $payOS = new PayOS(
                config('services.payos.client_id'),
                config('services.payos.api_key'),
                config('services.payos.checksum_key')
            );

            // Kiểm tra mã đơn hàng và gọi lại đúng link/QR thanh toán cũ trên PayOS
            if (is_numeric($order->order_code)) {
                $paymentInfo = $payOS->getPaymentLinkInformation((int) $order->order_code);

                // PayOS trả về 'id', đường link thanh toán web là https://pay.payos.vn/web/{id}
                $checkoutUrl = $paymentInfo['checkoutUrl'] ?? (!empty($paymentInfo['id']) ? "https://pay.payos.vn/web/" . $paymentInfo['id'] : null);

                if (($paymentInfo['status'] ?? '') === 'PAID') {
                    $order->update(['payment_status' => 'paid']);
                    $this->dispatch('show-toast', type: 'success', message: 'Đơn hàng này đã được thanh toán thành công!');
                    return;
                }

                if ($checkoutUrl && ($paymentInfo['status'] ?? '') === 'PENDING') {
                    $this->redirect($checkoutUrl, navigate: false);
                    return;
                }

                if (($paymentInfo['status'] ?? '') === 'CANCELLED') {
                    $this->dispatch('show-toast', type: 'error', message: 'Mã QR thanh toán đã hết hiệu lực. Không thể tiếp tục thanh toán.');
                    return;
                }
            }

            $this->dispatch('show-toast', type: 'warning', message: 'Link thanh toán hiện không còn khả dụng hoặc đã hết hiệu lực.');
        } catch (\Exception $e) {
            Log::error('Lỗi lấy link thanh toán PayOS cho đơn #' . $order->order_code . ': ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Không thể mở cổng thanh toán: ' . $e->getMessage());
        }
    }

    //Xác nhận hủy đơn hàng 
    public function confirmCancel(int $orderId)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->find($orderId);

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Không thể hủy đơn hàng này hoặc đơn đã chuyển sang bước xử lý!');
            return;
        }

        if (in_array($order->order_type, ['subscription', 'subscription_delivery'])) {
            $this->dispatch('show-toast', type: 'error', message: 'Gói VIP định kỳ không hỗ trợ tự hủy online. Vui lòng liên hệ CSKH!');
            return;
        }

        if ($order->payment_status !== 'paid') {
            $this->executeCancelOrder($order);
            return;
        }

        $user = Auth::user();
        $this->bank_name           = $user->default_bank_name ?? '';
        $this->bank_account_number = $user->default_bank_account_number ?? '';
        $this->bank_account_name   = $user->default_bank_account_name ?? mb_strtoupper($user->name);

        $this->cancellingOrderId = $order->id;
        $this->showCancelModal   = true;
    }

    //Xử lý hủy đơn hàng (đã thanh toán)
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

    //thực hiện hủy đơn hàng
    private function executeCancelOrder(Order $order, array $extraData = [])
    {
        $cancelledSuccessfully = DB::transaction(function () use ($order, $extraData) {
            // 1. Khóa cứng bản ghi đơn hàng
            $lockedOrder = Order::with('items')->whereKey($order->id)->lockForUpdate()->first();

            // 2. Kiểm tra lại trạng thái sau khi lấy khóa
            if (!$lockedOrder || $lockedOrder->status !== 'pending') {
                return false;
            }

            // Nếu đơn đã thanh toán thì không cho phép hủy
            if ($lockedOrder->payment_status === 'paid' && empty($extraData['refund_status'])) {
                return false;
            }

            $oldStatus = $lockedOrder->status;

            // 3. Cập nhật trạng thái hủy
            $lockedOrder->update(array_merge([
                'status'        => 'cancelled',
                'cancel_reason' => $this->cancel_reason,
                'cancelled_at'  => now(),
                'cancelled_by'  => 'customer',
            ], $extraData));

            // Đồng bộ trạng thái giao dịch sang failed trong bảng payments nếu đơn chưa thanh toán
            if ($lockedOrder->payment_status !== 'paid') {
                Payment::where('order_id', $lockedOrder->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'failed']);
            }

            if (method_exists($lockedOrder, 'statusHistories')) {
                $lockedOrder->statusHistories()->create([
                    'old_status' => $oldStatus,
                    'new_status' => 'cancelled',
                    'changed_by' => Auth::id(),
                    'note'       => 'Khách hàng tự hủy đơn. Lý do: ' . $this->cancel_reason,
                ]);
            }

            // 4. Hoàn lại kho (từng lô và kho tổng)
            foreach ($lockedOrder->items as $item) {
                if ($item->batch_id) {
                    ProductBatch::where('id', $item->batch_id)
                        ->increment('current_quantity', $item->quantity);
                }
                Product::where('id', $item->product_id)
                    ->increment('stock', $item->quantity);
            }

            // 5. Hoàn lại điểm thưởng đã dùng
            if ($lockedOrder->points_used > 0) {
                Auth::user()->increment('reward_points', $lockedOrder->points_used);
            }

            return true;
        });

        if ($cancelledSuccessfully) {
            $this->dispatch('show-toast', type: 'success', message: 'Đã hủy thành công đơn hàng #' . $order->order_code);
        } else {
            $this->dispatch('show-toast', type: 'info', message: 'Đơn hàng #' . $order->order_code . ' đã được xử lý hủy trước đó.');
        }
    }

    // Reset modal hủy đơn hàng
    public function resetCancelModal()
    {
        $this->reset(['showCancelModal', 'cancellingOrderId', 'cancel_reason', 'bank_name', 'bank_account_number', 'bank_account_name']);
    }

    // Mở modal Khiếu nại hoàn tiền
    public function openComplaintModal(int $orderId)
    {
        $order = Order::with('statusHistories')
            ->where('user_id', Auth::id())
            ->where('status', 'delivered')
            ->where('order_type', 'retail')
            ->find($orderId);

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng không đủ điều kiện khiếu nại (Chỉ áp dụng cho đơn bán lẻ đã giao)!');
            return;
        }

        // 1. Kiểm tra đơn đã từng khiếu nại chưa
        if ($order->complaint()->exists()) {
            $this->dispatch('show-toast', type: 'warning', message: 'Đơn hàng này đã được gửi khiếu nại trước đó.');
            return;
        }

        // 2. Kiểm tra mốc 24h kể từ khi giao hàng thành công
        $deliveredHistory = $order->statusHistories?->firstWhere('new_status', 'delivered');
        $deliveredAt = $deliveredHistory ? $deliveredHistory->created_at : $order->updated_at;
        if ($deliveredAt && $deliveredAt->diffInHours(now()) >= 24) {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng đã giao quá 24 giờ, đã hết hạn khiếu nại theo chính sách nông sản!');
            return;
        }

        $user = Auth::user();
        $this->complainingOrderId     = $order->id;
        $this->complaint_refund_amount = (int) $order->total;
        $this->bank_name              = $user->default_bank_name ?? '';
        $this->bank_account_number    = $user->default_bank_account_number ?? '';
        $this->bank_account_name      = $user->default_bank_account_name ?? mb_strtoupper($user->name);
        $this->complaint_images       = [];
        $this->showComplaintModal     = true;
    }

    public function submitComplaint()
    {
        $this->validate([
            'complaint_reason'        => 'required|string|max:500',
            'complaint_refund_amount' => 'required|numeric|min:1000',
            'bank_name'               => 'required|string',
            'bank_account_number'     => 'required|string|max:30',
            'bank_account_name'       => 'required|string|max:100',
            'complaint_images.*'      => 'image|max:5120',
        ], [
            'complaint_reason.required'        => 'Vui lòng nhập lý do khiếu nại dập nát / hư hỏng.',
            'complaint_refund_amount.required' => 'Vui lòng nhập số tiền đề xuất hoàn.',
            'bank_name.required'               => 'Vui lòng chọn ngân hàng nhận lại tiền.',
            'bank_account_number.required'     => 'Vui lòng nhập số tài khoản ngân hàng.',
            'bank_account_name.required'       => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        $order = Order::with('statusHistories')
            ->where('user_id', Auth::id())
            ->where('status', 'delivered')
            ->where('order_type', 'retail')
            ->find($this->complainingOrderId);

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng không đủ điều kiện khiếu nại!');
            return;
        }

        // 1. Kiểm tra đơn đã từng khiếu nại chưa
        if ($order->complaint()->exists()) {
            $this->dispatch('show-toast', type: 'warning', message: 'Đơn hàng này đã có khiếu nại được tạo trước đó.');
            $this->resetComplaintModal();
            return;
        }

        // 2. Kiểm tra mốc 24h kể từ khi giao hàng thành công
        $deliveredHistory = $order->statusHistories?->firstWhere('new_status', 'delivered');
        $deliveredAt = $deliveredHistory ? $deliveredHistory->created_at : $order->updated_at;
        if ($deliveredAt && $deliveredAt->diffInHours(now()) >= 24) {
            $this->dispatch('show-toast', type: 'error', message: 'Đã quá thời hạn 24 giờ kể từ khi nhận hàng. Không thể gửi khiếu nại!');
            $this->resetComplaintModal();
            return;
        }

        DB::transaction(function () use ($order) {
            $bankAccName = mb_strtoupper($this->bank_account_name);

            $imagePaths = [];
            if (!empty($this->complaint_images)) {
                foreach ($this->complaint_images as $img) {
                    $imagePaths[] = $img->store('complaints', 'public');
                }
            }

            Complaint::create([
                'order_id'            => $order->id,
                'user_id'             => Auth::id(),
                'reason'              => $this->complaint_reason,
                'refund_amount'       => min((float) $this->complaint_refund_amount, (float) $order->total),
                'status'              => 'pending',
                'bank_name'           => $this->bank_name,
                'bank_account_number' => $this->bank_account_number,
                'bank_account_name'   => $bankAccName,
                'images'              => $imagePaths,
            ]);

            $order->update([
                'refund_status'       => 'pending',
                'bank_name'           => $this->bank_name,
                'bank_account_number' => $this->bank_account_number,
                'bank_account_name'   => $bankAccName,
            ]);

            Auth::user()->update([
                'default_bank_name'           => $this->bank_name,
                'default_bank_account_number' => $this->bank_account_number,
                'default_bank_account_name'   => $bankAccName,
            ]);
        });

        $this->resetComplaintModal();
        $this->dispatch('show-toast', type: 'success', message: 'Đã gửi yêu cầu khiếu nại hoàn tiền. Nông trại sẽ phản hồi trong 24h!');
    }

    public function resetComplaintModal()
    {
        $this->reset(['showComplaintModal', 'complainingOrderId', 'complaint_reason', 'complaint_refund_amount', 'complaint_images', 'bank_name', 'bank_account_number', 'bank_account_name']);
    }

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

        $pendingPaymentQuery      = fn($q) => $q->where('status', 'pending')->where('payment_status', 'pending');
        $pendingConfirmationQuery = fn($q) => $q->where('status', 'pending')->where('payment_status', 'paid');
        $processingQuery          = fn($q) => $q->where('status', 'processing');
        $shippedQuery             = fn($q) => $q->where('status', 'shipped');
        $deliveredQuery           = fn($q) => $q->where('status', 'delivered');
        $cancelledQuery           = fn($q) => $q->where('status', 'cancelled')->where('cancelled_by', 'customer');
        $refundQuery              = fn($q) => $q->where(fn($sub) => $sub->whereIn('refund_status', ['pending', 'refunded'])->orWhereHas('complaint'));

        $counts = [
            'all'                  => Order::where('user_id', $userId)->where(fn($q) => $q->where('status', '!=', 'cancelled')->orWhere('cancelled_by', 'customer'))->count(),
            'pending_payment'      => $pendingPaymentQuery(Order::where('user_id', $userId))->count(),
            'pending_confirmation' => $pendingConfirmationQuery(Order::where('user_id', $userId))->count(),
            'processing'           => $processingQuery(Order::where('user_id', $userId))->count(),
            'shipped'              => $shippedQuery(Order::where('user_id', $userId))->count(),
            'delivered'            => $deliveredQuery(Order::where('user_id', $userId))->count(),
            'cancelled'            => $cancelledQuery(Order::where('user_id', $userId))->count(),
            'refund'               => $refundQuery(Order::where('user_id', $userId))->count(),
        ];

        $query = Order::with(['items.product.productImages', 'items.batch', 'complaint', 'statusHistories', 'deliverySchedule.subscription', 'payment'])
            ->where('user_id', $userId);

        if ($this->statusFilter === 'all') {
            $query->where(fn($q) => $q->where('status', '!=', 'cancelled')->orWhere('cancelled_by', 'customer'));
        } elseif ($this->statusFilter === 'pending_payment') {
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
        }

        return view('livewire.customer.orders-page', [
            'orders' => $query->latest()->paginate(10),
            'counts' => $counts,
        ]);
    }
}
