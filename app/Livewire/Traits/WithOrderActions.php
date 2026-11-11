<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PayOS\PayOS;
use App\Models\Order;
use App\Models\ProductBatch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Complaint;
use App\Models\ComplaintImage;
use App\Models\Payment;
use App\Events\ComplaintSubmittedEvent;

trait WithOrderActions
{
    use WithVietnamBanks;

    // State cho Modal Khiếu nại hoàn tiền nông sản dập nát (24H)
    public bool $showComplaintModal = false;
    public ?int $complainingOrderId = null;
    public string $complaint_reason = 'Nông sản bị dập nát / hư hỏng trong quá trình vận chuyển';
    public ?int $complaint_refund_amount = null;
    public array $complaint_images = [];
    public $new_complaint_images = [];
    public string $bank_name = '';
    public string $bank_account_number = '';
    public string $bank_account_name = '';

    /**
     * Thanh toán lại đơn hàng (dành cho đơn pending chưa trả tiền)
     */
    public function payNow(?int $orderId = null): void
    {
        $targetId = $orderId ?? ($this->orderId ?? null);
        if (!$targetId) return;

        $order = Order::with('items')->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->whereHas('payment', fn($q) => $q->where('status', 'pending'))
            ->find($targetId);

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng không hợp lệ hoặc đã thanh toán!');
            return;
        }

        // Nếu đơn hàng đã quá hạn 5 phút, không cho phép thanh toán tiếp
        if ($order->created_at->diffInMinutes(now()) >= 5) {
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

    /**
     * Xác nhận hủy đơn hàng (Chỉ cho phép đơn pending chưa thanh toán)
     */
    public function confirmCancel(?int $orderId = null): void
    {
        $targetId = $orderId ?? ($this->orderId ?? null);
        if (!$targetId) return;

        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->find($targetId);

        if (!$order) {
            $this->dispatch('show-toast', type: 'error', message: 'Không thể hủy đơn hàng này hoặc đơn đã chuyển sang bước xử lý!');
            return;
        }

        if (in_array($order->order_type, ['subscription', 'subscription_delivery'])) {
            $this->dispatch('show-toast', type: 'error', message: 'Gói VIP định kỳ không hỗ trợ tự hủy online. Vui lòng liên hệ CSKH!');
            return;
        }

        // Đơn đã thanh toán: Chặn hủy trực tuyến
        if ($order->payment?->status === 'success') {
            $this->dispatch('show-toast', type: 'error', message: 'Đơn hàng đã thanh toán không thể tự hủy trực tuyến. Vui lòng liên hệ Hotline Nông trại!');
            return;
        }

        $this->executeCancelOrder($order);
    }

    /**
     * Thực hiện hủy đơn hàng pending chưa thanh toán
     */
    protected function executeCancelOrder(Order $order): bool
    {
        $cancelledSuccessfully = DB::transaction(function () use ($order) {
            // 1. Khóa cứng bản ghi đơn hàng
            $lockedOrder = Order::with('items')->whereKey($order->id)->lockForUpdate()->first();

            // 2. Kiểm tra lại trạng thái sau khi lấy khóa
            if (!$lockedOrder || $lockedOrder->status !== 'pending' || $lockedOrder->payment?->status === 'success') {
                return false;
            }

            $oldStatus = $lockedOrder->status;

            // 3. Cập nhật trạng thái hủy
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
                ]);
            }

            // Nhả số lượng giữ chỗ (kho thực tế current_quantity chưa hề bị trừ)
            foreach ($lockedOrder->items as $item) {
                if ($item->batch_id) {
                    ProductBatch::where('id', $item->batch_id)
                        ->decrement('reserved_quantity', $item->quantity);
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

        return $cancelledSuccessfully;
    }

    /**
     * Mua lại đơn này: Thêm tất cả sản phẩm vào giỏ hàng
     */
    public function reorder(?int $orderId = null)
    {
        $targetId = $orderId ?? ($this->orderId ?? null);
        if (!$targetId) return;

        $order = Order::with('items.product')->where('user_id', Auth::id())->find($targetId);
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

    /**
     * Mở modal Khiếu nại hoàn tiền
     */
    public function openComplaintModal(?int $orderId = null): void
    {
        $targetId = $orderId ?? ($this->orderId ?? null);
        if (!$targetId) return;

        $order = Order::with('statusHistories')
            ->where('user_id', Auth::id())
            ->where('status', 'delivered')
            ->where('order_type', 'retail')
            ->find($targetId);

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
        $this->complainingOrderId      = $order->id;
        $this->complaint_refund_amount = (int) $order->total;
        $this->bank_name               = $user->default_bank_name ?? '';
        $this->bank_account_number     = $user->default_bank_account_number ?? '';
        $this->bank_account_name       = $user->default_bank_account_name ?? mb_strtoupper($user->name);
        $this->complaint_images        = [];
        $this->new_complaint_images    = [];
        $this->resetValidation();
        $this->showComplaintModal      = true;
    }

    /**
     * Cộng dồn ảnh mới được chọn vào danh sách (tối đa 4 ảnh) mà không làm mất ảnh cũ
     */
    public function updatedNewComplaintImages(): void
    {
        if (empty($this->new_complaint_images)) {
            return;
        }

        $files = is_array($this->new_complaint_images)
            ? $this->new_complaint_images
            : [$this->new_complaint_images];

        try {
            $this->validate([
                'new_complaint_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            ], [
                'new_complaint_images.*.image' => 'Tập tin tải lên phải là hình ảnh.',
                'new_complaint_images.*.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, webp.',
                'new_complaint_images.*.max'   => 'Mỗi hình ảnh không được vượt quá 5MB.',
            ]);
        } catch (ValidationException $e) {
            $this->new_complaint_images = [];
            throw $e;
        }

        $remainingSlots = max(0, 4 - count($this->complaint_images));
        if ($remainingSlots <= 0) {
            $this->new_complaint_images = [];
            $this->dispatch('show-toast', type: 'warning', message: 'Chỉ được tải lên tối đa 4 hình ảnh bằng chứng.');
            return;
        }

        if (count($files) > $remainingSlots) {
            $files = array_slice($files, 0, $remainingSlots);
            $this->dispatch('show-toast', type: 'warning', message: 'Chỉ được tải lên tối đa 4 hình ảnh bằng chứng.');
        }

        foreach ($files as $file) {
            $this->complaint_images[] = $file;
        }

        $this->new_complaint_images = [];
        $this->resetErrorBag(['complaint_images', 'complaint_images.*', 'new_complaint_images.*']);
    }

    /**
     * Xóa từng ảnh bằng chứng khỏi danh sách đã chọn trước khi gửi
     */
    public function removeComplaintImage(int $index): void
    {
        if (isset($this->complaint_images[$index])) {
            unset($this->complaint_images[$index]);
            $this->complaint_images = array_values($this->complaint_images);
            $this->resetErrorBag(['complaint_images', 'complaint_images.*', 'new_complaint_images.*']);
        }
    }

    /**
     * Gửi yêu cầu khiếu nại hoàn tiền
     */
    public function submitComplaint(): void
    {
        $targetId = $this->complainingOrderId ?? ($this->orderId ?? null);
        $order = Order::with('statusHistories')
            ->where('user_id', Auth::id())
            ->where('status', 'delivered')
            ->where('order_type', 'retail')
            ->find($targetId);

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

        $this->validate([
            'complaint_reason'        => 'required|string|min:10|max:500',
            'bank_name'               => 'required|string',
            'bank_account_number'     => 'required|string|max:30',
            'bank_account_name'       => 'required|string|max:100',
            'complaint_images'        => 'required|array|min:1|max:4',
            'complaint_images.*'      => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'complaint_reason.required'        => 'Vui lòng nhập lý do khiếu nại dập nát / hư hỏng.',
            'complaint_reason.min'             => 'Lý do khiếu nại phải có ít nhất 10 ký tự để tiện thẩm định.',
            'bank_name.required'               => 'Vui lòng chọn ngân hàng nhận lại tiền.',
            'bank_account_number.required'     => 'Vui lòng nhập số tài khoản ngân hàng.',
            'bank_account_name.required'       => 'Vui lòng nhập tên chủ tài khoản.',
            'complaint_images.required'        => 'Vui lòng tải lên ít nhất 1 hình ảnh bằng chứng.',
            'complaint_images.min'             => 'Vui lòng tải lên ít nhất 1 hình ảnh bằng chứng.',
            'complaint_images.max'             => 'Chỉ được tải lên tối đa 4 hình ảnh bằng chứng.',
            'complaint_images.*.image'         => 'Tập tin tải lên phải là hình ảnh.',
            'complaint_images.*.mimes'         => 'Hình ảnh phải có định dạng: jpeg, png, jpg, webp.',
            'complaint_images.*.max'           => 'Mỗi hình ảnh không được vượt quá 5MB.',
        ]);

        $complaint = null;

        DB::transaction(function () use ($order, &$complaint) {
            $bankAccName = mb_strtoupper($this->bank_account_name);

            $complaint = Complaint::create([
                'order_id'            => $order->id,
                'user_id'             => Auth::id(),
                'reason'              => $this->complaint_reason,
                'refund_amount'       => (float) $order->total,
                'status'              => 'pending',
                'bank_name'           => $this->bank_name,
                'bank_account_number' => $this->bank_account_number,
                'bank_account_name'   => $bankAccName,
            ]);

            if (!empty($this->complaint_images)) {
                foreach ($this->complaint_images as $img) {
                    ComplaintImage::create([
                        'complaint_id' => $complaint->id,
                        'image'        => $img->store('complaints', 'r2'),
                    ]);
                }
            }

            Auth::user()->update([
                'default_bank_name'           => $this->bank_name,
                'default_bank_account_number' => $this->bank_account_number,
                'default_bank_account_name'   => $bankAccName,
            ]);
        });

        // Phát sóng sự kiện Realtime cho bộ phận Sales
        if ($complaint) {
            broadcast(new ComplaintSubmittedEvent($complaint));
        }

        $this->resetComplaintModal();
        $this->dispatch('show-toast', type: 'success', message: 'Đã gửi yêu cầu khiếu nại hoàn tiền. Nông trại sẽ phản hồi trong 24h!');
    }

    /**
     * Đóng và reset form khiếu nại
     */
    public function resetComplaintModal(): void
    {
        $this->reset([
            'showComplaintModal',
            'complainingOrderId',
            'complaint_reason',
            'complaint_refund_amount',
            'complaint_images',
            'new_complaint_images',
            'bank_name',
            'bank_account_number',
            'bank_account_name',
        ]);
        $this->resetValidation();
    }
}

