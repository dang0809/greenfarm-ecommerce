<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;
use App\Models\Subscription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Address;
use PayOS\PayOS;

#[Title('Thanh Toán Gói Rau VIP | Nông Trại Đất Lành')]
class VipCheckout extends Component
{
    public string $paymentMethod = 'payos';
    public string $packageName = 'Gói Bếp Xanh VIP Gia Đình (1 Tháng)';
    public int $price = 1200000;
    public float $deliveryMaxWeight = 3.5;
    public int $totalDeliveries = 8;
    public float $totalWeightMonthly = 28.0;
    public bool $isEnabled = true;
    public string $hotline = '0987.654.321';
    public ?int $pendingSubscriptionId = null;

    public function mount()
    {
        // 1. Tải toàn bộ cấu hình Gói VIP từ Database
        $this->loadVipSettings();

        // 2. Nếu khách đã thanh toán và có gói VIP active -> Tự động chuyển thẳng sang trang Thiết Lập Lịch
        if (Auth::check()) {
            $activeSub = Subscription::where('user_id', Auth::id())
                ->where('status', 'active')
                ->latest()
                ->first();

            if ($activeSub) {
                return $this->redirect(route('customer.vip.setup'), navigate: true);
            }
        }
    }

    public function processPayment()
    {
        // Đồng bộ toàn bộ thông số (Giá tiền, Tên, Trạng thái) thời gian thực từ Database
        $this->loadVipSettings();

        // 1. Kiểm tra đăng nhập
        if (!Auth::check()) {
            session()->flash('error', 'Vui lòng đăng nhập để tiến hành thanh toán Gói Rau VIP.');
            return $this->redirect(route('login'), navigate: true);
        }

        // 2. Kiểm tra trạng thái mở bán thời gian thực từ Database (ngăn chặn trường hợp khách mở trang trước khi Admin tắt)
        if (!$this->isEnabled) {
            $this->dispatch('show-toast', type: 'warning', message: 'Dịch vụ Gói Rau VIP hiện đang tạm dừng nhận đăng ký mới.');
            return;
        }

        $user = Auth::user();

        // 3. KIỂM TRA ĐƠN HÀNG CŨ: NẾU CÒN HẠN THÌ DÙNG LẠI QR CŨ, HẾT HẠN THÌ TẠO MỚI
        $existingOrder = Order::where('user_id', $user->id)
            ->where('order_type', 'subscription')
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->latest()
            ->first();

        $clientId = config('services.payos.client_id');
        $apiKey = config('services.payos.api_key');
        $checksumKey = config('services.payos.checksum_key');
        $payOS = new PayOS($clientId, $apiKey, $checksumKey);

        if ($existingOrder && is_numeric($existingOrder->order_code)) {
            // Nếu đơn cũ có giá khác giá hiện tại vừa được cập nhật trong DB -> Hủy để tạo theo giá mới
            if ((int) $existingOrder->total !== (int) $this->price) {
                $existingOrder->update([
                    'status'        => 'cancelled',
                    'cancel_reason' => 'Giá gói VIP đã thay đổi, cập nhật giao dịch mới',
                    'cancelled_at'  => now(),
                    'cancelled_by'  => 'system',
                ]);
                $existingOrder = null;
            }
        }

        if ($existingOrder && is_numeric($existingOrder->order_code)) {
            // Kiểm tra nếu đơn còn hạn (dưới 5 phút)
            if ($existingOrder->created_at->diffInMinutes(now()) < 5) {
                try {
                    $paymentInfo = $payOS->getPaymentLinkInformation((int) $existingOrder->order_code);
                    $payStatus = $paymentInfo['status'] ?? '';

                    // Nếu thực tế đã thanh toán xong từ trước
                    if ($payStatus === 'PAID') {
                        $existingOrder->update(['payment_status' => 'paid', 'status' => 'delivered']);
                        return $this->redirect(route('customer.checkout.success', ['order_id' => $existingOrder->id]), navigate: false);
                    }

                    // Nếu link cũ vẫn còn PENDING -> DÙNG LẠI LINK VÀ MÃ QR CŨ
                    if ($payStatus === 'PENDING') {
                        $checkoutUrl = $paymentInfo['checkoutUrl'] ?? (!empty($paymentInfo['id']) ? "https://pay.payos.vn/web/" . $paymentInfo['id'] : null);
                        if ($checkoutUrl) {
                            return $this->redirect($checkoutUrl, navigate: false);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Không thể tái sử dụng link PayOS VIP cũ, sẽ tạo đơn mới: ' . $e->getMessage());
                }
            }

            // Nếu đơn cũ đã quá 5 phút hoặc đã CANCELLED trên PayOS -> Hủy đơn cũ để tạo mới
            $existingOrder->update([
                'status'        => 'cancelled',
                'cancel_reason' => 'Đơn VIP hết hạn thanh toán, tạo giao dịch mới',
                'cancelled_at'  => now(),
                'cancelled_by'  => 'system',
            ]);
        }

        // 4. Lấy thông tin địa chỉ giao hàng
        $address = Address::where('user_id', $user->id)->where('is_default', true)->first()
            ?? Address::where('user_id', $user->id)->first();

        $recipientName = $address->recipient_name;
        $recipientPhone = $address->recipient_phone ;
        $shippingAddress = $address 
            ? $address->address_detail . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->province
            : 'Đất Lành Farm (Chờ khách cập nhật khi thiết lập lịch)';

        // 5. Mã PayOS chuẩn (dưới 5 ký tự số nguyên)
        $payosOrderCode = intval(time() . rand(100, 999));

        DB::beginTransaction();
        try {
            // Tạo đơn hàng mua gói VIP
            $order = Order::create([
                'user_id'           => $user->id,
                'order_code'        => (string) $payosOrderCode,
                'order_type'        => 'subscription',
                'recipient_name'    => $recipientName,
                'recipient_phone'   => $recipientPhone,
                'recipient_address' => $shippingAddress,
                'subtotal'          => $this->price,
                'shipping_fee'      => 0,
                'total'             => $this->price,
                'payment_method'    => 'payos',
                'payment_status'    => 'pending',
                'status'            => 'pending',
                'note'              => 'Đăng ký ' . $this->packageName,
            ]);

            // Tạo bản ghi thanh toán pending
            Payment::create([
                'order_id'     => $order->id,
                'payment_code' => 'PAY-' . $order->order_code,
                'method'       => 'payos',
                'amount'       => $this->price,
                'status'       => 'pending',
            ]);

            // 6. Kết nối PayOS tạo link thanh toán mới
            $clientId = config('services.payos.client_id');
            $apiKey = config('services.payos.api_key');
            $checksumKey = config('services.payos.checksum_key');

            $payOS = new PayOS($clientId, $apiKey, $checksumKey);

            $payosData = [
                "orderCode"   => $payosOrderCode,
                "amount"      => $this->price,
                "description" => "TT " . $payosOrderCode,
                "returnUrl"   => route('customer.checkout.success', ['order_id' => $order->id]),
                "cancelUrl"   => route('customer.checkout.cancel', ['order_id' => $order->id]),
                "expiredAt"   => time() + (5 * 60),
            ];

            $response = $payOS->createPaymentLink($payosData);

            DB::commit();

            // Chuyển hướng sang cổng thanh toán PayOS
            return $this->redirect($response['checkoutUrl'], navigate: false);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khởi tạo đơn hàng PayOS VIP: ' . $e->getMessage());

            $this->dispatch('show-toast', type: 'error', message: 'Không thể kết nối cổng PayOS: ' . $e->getMessage());
        }
    }

            /**
     * Tải toàn bộ cấu hình Gói VIP thời gian thực từ database
     */
    /**
     * Tải toàn bộ cấu hình Gói VIP thời gian thực từ Database
     * Tổng số đợt giao cố định luôn là 8 đợt (1 tháng = 4 tuần x 2 đợt/tuần)
     */
    private function loadVipSettings(): void
    {
        $settings = SystemSetting::where('group', 'subscription')->pluck('value', 'key');

        $this->packageName = $settings['vip_package_name'] ?? 'Gói Bếp Xanh VIP Gia Đình (1 Tháng)';
        $this->price = (int) ($settings['vip_package_price'] ?? 1200000);
        $this->deliveryMaxWeight = (float) ($settings['vip_delivery_max_weight'] ?? 3.5);
        $this->totalDeliveries = 8; // Cố định luôn là 8 đợt, không lưu trong CSDL
        $this->isEnabled = filter_var($settings['vip_subscription_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->hotline = $settings['hotline'] ?? '0987.654.321';
        $this->totalWeightMonthly = round($this->deliveryMaxWeight * 8, 1);
    }

    public function render()
    {
        $this->loadVipSettings();

        return view('livewire.customer.vip-checkout', [
            'packageName'           => $this->packageName,
            'price'                 => $this->price,
            'deliveryMaxWeight'     => $this->deliveryMaxWeight,
            'totalDeliveries'       => $this->totalDeliveries,
            'totalWeightMonthly'    => $this->totalWeightMonthly,
            'isEnabled'             => $this->isEnabled,
            'hotline'               => $this->hotline,
            'pendingSubscriptionId' => $this->pendingSubscriptionId,
        ])->layout('layouts.customer.app');
    }
}