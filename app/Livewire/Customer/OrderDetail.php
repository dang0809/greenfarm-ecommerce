<?php

namespace App\Livewire\Customer;

use Livewire\Component;
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
use App\Models\ComplaintImage;
use App\Models\Payment;

class OrderDetail extends Component
{
    use WithFileUploads;

    public string $order_code = '';
    public ?int $orderId = null;

    // State cho Modal Khiếu nại hoàn tiền nông sản dập nát (24H)
    public bool $showComplaintModal = false;
    public string $complaint_reason = 'Nông sản bị dập nát / hư hỏng trong quá trình vận chuyển';
    public ?int $complaint_refund_amount = null;
    public array $complaint_images = [];
    public string $bank_name = '';
    public string $bank_account_number = '';
    public string $bank_account_name = '';

    public function mount(string $order_code)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('order_code', $order_code)
            ->firstOrFail();

        $this->order_code = $order->order_code;
        $this->orderId    = $order->id;
    }

    // Thanh toán lại đơn hàng (dành cho đơn pending chưa trả tiền)
    public function payNow()
    {
        $order = Order::with('items')->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->whereHas('payment', fn($q) => $q->where('status', 'pending'))
            ->find($this->orderId);

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

                $checkoutUrl = $paymentInfo['checkoutUrl'] ?? (!empty($paymentInfo['id']) ? "https://pay.payos.vn/web/" . $paymentInfo['id'] : null);

                if (($paymentInfo['status']) === 'PAID') {
                    Payment::where('order_id', $order->id)->update(['status' => 'success', 'paid_at' => now()]);
                    $this->dispatch('show-toast', type: 'success', message: 'Đơn hàng này đã được thanh toán thành công!');
                    return;
                }

                if ($checkoutUrl && ($paymentInfo['status']) === 'PENDING') {
                    $this->redirect($checkoutUrl);
                    return;
                }

                if (($paymentInfo['status']) === 'CANCELLED') {
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

    // Hủy đơn hàng (Chỉ dành cho đơn pending chưa thanh toán)
    public function confirmCancel()
    {
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->find($this->orderId);

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Không thể hủy đơn hàng này hoặc đơn đã chuyển sang bước xử lý!');
            return;
        }

        // Đơn đã thanh toán: chặn hủy online
        if ($order->payment?->status === 'success') {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng đã thanh toán không thể tự hủy trực tuyến. Vui lòng liên hệ Hotline Nông trại!');
            return;
        }

        $cancelledSuccessfully = DB::transaction(function () use ($order) {
            $lockedOrder = Order::with('items')->whereKey($order->id)->lockForUpdate()->first();

            if (!$lockedOrder || $lockedOrder->status !== 'pending' || $lockedOrder->payment?->status === 'success') {
                return false;
            }

            $oldStatus = $lockedOrder->status;

            $lockedOrder->update([
                'status'        => 'cancelled',
                'cancel_reason' => 'Khách hàng tự hủy đơn',
                'cancelled_at'  => now(),
                'cancelled_by'  => 'customer',
            ]);

            Payment::where('order_id', $lockedOrder->id)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);

            if (method_exists($lockedOrder, 'statusHistories')) {
                $lockedOrder->statusHistories()->create([
                    'old_status' => $oldStatus,
                    'new_status' => 'cancelled',
                    'changed_by' => Auth::id(),
                    'note'       => 'Khách hàng tự hủy đơn hàng trực tuyến.',
                ]);
            }

            // Hoàn lại kho
            foreach ($lockedOrder->items as $item) {
                if ($item->batch_id) {
                    ProductBatch::where('id', $item->batch_id)
                        ->increment('current_quantity', $item->quantity);
                }

            }

            // Hoàn lại điểm tích lũy đã dùng
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

    // Mua lại đơn này: Thêm tất cả sản phẩm vào giỏ hàng
    public function reorder()
    {
        $order = Order::with('items.product')->where('user_id', Auth::id())->find($this->orderId);
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

    // Mở modal Khiếu nại hoàn tiền
    public function openComplaintModal()
    {
        $order = Order::with('statusHistories')
            ->where('user_id', Auth::id())
            ->where('status', 'delivered')
            ->where('order_type', 'retail')
            ->find($this->orderId);

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng không đủ điều kiện khiếu nại (Chỉ áp dụng cho đơn bán lẻ đã giao)!');
            return;
        }

        if ($order->complaint()->exists()) {
            $this->dispatch('show-toast', type: 'warning', message: 'Đơn hàng này đã được gửi khiếu nại trước đó.');
            return;
        }

        $deliveredHistory = $order->statusHistories?->firstWhere('new_status', 'delivered');
        $deliveredAt = $deliveredHistory ? $deliveredHistory->created_at : $order->updated_at;
        if ($deliveredAt && $deliveredAt->diffInHours(now()) >= 24) {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng đã giao quá 24 giờ, đã hết hạn khiếu nại theo chính sách nông sản!');
            return;
        }

        $user = Auth::user();
        $this->complaint_refund_amount = (int) $order->total;
        $this->bank_name               = $user->default_bank_name ?? '';
        $this->bank_account_number     = $user->default_bank_account_number ?? '';
        $this->bank_account_name       = $user->default_bank_account_name ?? mb_strtoupper($user->name);
        $this->complaint_images        = [];
        $this->showComplaintModal      = true;
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
            ->find($this->orderId);

        if (!$order || $order->complaint()->exists()) {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng không đủ điều kiện khiếu nại hoặc đã có khiếu nại trước đó!');
            return;
        }

        $deliveredHistory = $order->statusHistories?->firstWhere('new_status', 'delivered');
        $deliveredAt = $deliveredHistory ? $deliveredHistory->created_at : $order->updated_at;
        if ($deliveredAt && $deliveredAt->diffInHours(now()) >= 24) {
            $this->dispatch('show-toast', type: 'error', message: 'Đã quá thời hạn 24 giờ kể từ khi nhận hàng. Không thể gửi khiếu nại!');
            $this->resetComplaintModal();
            return;
        }

        DB::transaction(function () use ($order) {
            $bankAccName = mb_strtoupper($this->bank_account_name);

            $complaint = Complaint::create([
                'order_id'            => $order->id,
                'user_id'             => Auth::id(),
                'reason'              => $this->complaint_reason,
                'refund_amount'       => min((float) $this->complaint_refund_amount, (float) $order->total),
                'status'              => 'pending',
                'bank_name'           => $this->bank_name,
                'bank_account_number' => $this->bank_account_number,
                'bank_account_name'   => $bankAccName,
            ]);

            if (!empty($this->complaint_images)) {
                foreach ($this->complaint_images as $img) {
                    ComplaintImage::create([
                        'complaint_id' => $complaint->id,
                        'image'        => $img->store('complaints', 'public'),
                    ]);
                }
            }

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
        $this->reset(['showComplaintModal', 'complaint_reason', 'complaint_refund_amount', 'complaint_images', 'bank_name', 'bank_account_number', 'bank_account_name']);
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
            'order'                   => $order,
            'canRefund'               => $canRefund,
            'showComplaintModal'      => $this->showComplaintModal,
            'complaint_reason'        => $this->complaint_reason,
            'complaint_refund_amount' => $this->complaint_refund_amount,
            'bank_name'               => $this->bank_name,
            'bank_account_number'     => $this->bank_account_number,
            'bank_account_name'       => $this->bank_account_name,
        ])->layout('layouts.customer.app', [
            'title' => "Chi Tiết Đơn Hàng #{$order->order_code} | Nông Trại Đất Lành"
        ]);
    }
}
