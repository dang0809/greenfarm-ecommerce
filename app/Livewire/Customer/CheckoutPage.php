<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SystemSetting;
use App\Models\Product;
use App\Models\Payment;
#[Title('Thanh Toán Đơn Hàng | Nông Trại Đất Lành')]
class CheckoutPage extends Component
{
    public ?int $selectedAddressId = null;
    public string $orderNote = '';
    public string $paymentMethod = 'payos';
    public bool $showAddressModal = false;
    public array $selectedCartItemIds = [];
    public bool $usePoints = false;

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            $this->dispatch('open-auth-modal', 'login');
            return ;
        }

        $buyNowId = request()->query('buy_now');

        if ($buyNowId) {
            $this->selectedCartItemIds = [(int) $buyNowId];
            session()->put('checkout_selected_items', $this->selectedCartItemIds);
            session()->put('checkout_use_points', false);
            session()->save();
        } else {
            $rawSelected = session('checkout_selected_items', []);
            $this->selectedCartItemIds = array_map('intval', (array) $rawSelected);
            $this->usePoints = session('checkout_use_points', false);
        }

        if (empty($this->selectedCartItemIds)) {
            session()->flash('error', 'Vui lòng chọn sản phẩm để thanh toán!');
            return redirect()->route('customer.cart');
        }

        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart || $cart->items()->count() === 0) {
            session()->flash('error', 'Giỏ hàng của bạn đang rỗng!');
            return redirect()->route('customer.cart');
        }

        $defaultAddress = Address::where('user_id', $user->id)
            ->where('is_default', true)
            ->first() ?? Address::where('user_id', $user->id)->first();

        if ($defaultAddress) {
            $this->selectedAddressId = $defaultAddress->id;
        }
    }

    // lấy cài đặt loyalty
    private function getLoyaltySettings(): array
    {
        return Cache::remember('system_loyalty_settings', 86400, function () {
            return SystemSetting::where('group', 'loyalty')
                ->pluck('value', 'key')
                ->toArray();
        });
    }
    // lấy tọa độ nông trại
    private function getFarmCoordinates(): array
    {
        return Cache::remember('system_farm_coordinates', 86400, function () {
            $settings = SystemSetting::whereIn('key', [
                'farm_latitude', 'farm_longitude'
            ])->pluck('value', 'key')->toArray();

            return [
                'lat' => (float) ($settings['farm_latitude'] ?? 10.7769),
                'lng' => (float) ($settings['farm_longitude'] ?? 106.7009),
            ];
        });
    }

    // lấy khoảng cách giao hàng tối đa
    private function getMaxShippingDistance(): float
    {
        return Cache::remember('system_max_shipping_distance', 86400, function () {
            $val = SystemSetting::where('key', 'max_shipping_distance_km')
                ->value('value');

            return (float) ($val ?? 25.0);
        });
    }

    // tính khoảng cách bằng OSRM API (được Cache 24h để không gây delay khi click)
    private function getShippingDistance(?float $destLat, ?float $destLng): float
    {
        if (!$destLat || !$destLng) {
            return 0.0;
        }

        $cacheKey = 'osrm_dist_' . md5("{$destLat}_{$destLng}");
        return Cache::remember($cacheKey, 86400, function () use ($destLat, $destLng) {
            $farm = $this->getFarmCoordinates();
            $farmLat = $farm['lat'];
            $farmLng = $farm['lng'];

            try {
                $coordinates = "{$farmLng},{$farmLat};{$destLng},{$destLat}";
                $url = "https://router.project-osrm.org/route/v1/driving/{$coordinates}?overview=false";

                $response = Http::timeout(1.5)->get($url);
                if ($response->successful() && isset($response->json()['routes'][0]['distance'])) {
                    return round($response->json()['routes'][0]['distance'] / 1000, 1);
                }
            } catch (\Exception $e) {
                Log::warning('Lỗi kết nối OSRM API: ' . $e->getMessage());
            }
            return (float) 2.0;
        });
    }

    // lấy sản phẩm trong giỏ hàng
    public function getCheckoutItems()
    {
        $cart = Cart::with(['items.product.category', 'items.product.productImages', 'items.product.batches'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$cart) return collect();

        $selectedIds = array_map('intval', $this->selectedCartItemIds);

        return $cart->items->filter(function ($item) use ($selectedIds) {
            return in_array((int) $item->id, $selectedIds, true);
        });
    }

    // lấy địa chỉ được chọn
    public function getSelectedAddressProperty()
    {
        if (!$this->selectedAddressId) return null;
        return Address::where('user_id', Auth::id())->find($this->selectedAddressId);
    }

    // chọn địa chỉ
    public function selectAddress(int $addressId)
    {
        $address = Address::where('user_id', Auth::id())->find($addressId);
        if ($address) {
            $this->selectedAddressId = $address->id;
            $this->showAddressModal = false;
            $this->dispatch('show-toast', type: 'success', message: 'Đã cập nhật địa chỉ giao hàng.');
        }
    }

    // tính phí giao hàng
    public function calculateShippingFee($distanceKm): int
    {
        if ($distanceKm <= 2) {
            return 15000;
        }

        $extraKm = floor($distanceKm - 2);
        return 15000 + ((int) $extraKm * 5000);
    }

    // đặt hàng
    public function placeOrder()
    {
        $items = $this->getCheckoutItems();

        if ($items->isEmpty()) {
            $this->dispatch('show-toast', type: 'error', message: 'Giỏ hàng của bạn đang rỗng!');
            return redirect()->route('customer.cart');
        }

        if (!$this->selectedAddress) {
            $this->dispatch('show-toast', type: 'error', message: 'Vui lòng chọn địa chỉ nhận hàng!');
            return;
        }

        $address     = $this->selectedAddress;
        $distance    = $this->getShippingDistance($address->latitude ?? null, $address->longitude ?? null);
        $maxDistance = $this->getMaxShippingDistance();

        if ($distance > $maxDistance) {
            $this->dispatch(
                'show-toast', 
                type: 'error', 
                message: "Rất tiếc, khoảng cách ({$distance} km) vượt quá bán kính giao hàng tối đa ({$maxDistance} km) của nông trại!"
            );
            return;
        }

        DB::beginTransaction();
        try {

            $productIds = $items->pluck('product_id')->toArray();
        
            // Khóa các dòng sản phẩm này lại, không cho người khác mua cùng lúc
            $productsInDB = Product::whereIn('id', $productIds)
                ->withSum(['batches as total_stock' => function ($q) {
                    $q->where('product_batches.status', 'active')
                      ->where('product_batches.current_quantity', '>', 0)
                      ->whereDate('product_batches.expiry_date', '>=', now());
                }], 'current_quantity')
                ->lockForUpdate()
                ->get();

            foreach ($items as $item) {
                $product = $productsInDB->firstWhere('id', $item->product_id);
                
                // 1. Kiểm tra trạng thái bán
                if (!$product || $product->status !== 'active') { // Giả sử cột trạng thái là active
                    throw new \Exception("Sản phẩm {$item->product->name} hiện không khả dụng.");
                }

                // 2. Kiểm tra tồn kho có đủ không
                $availableStock = (int) ($product->total_stock ?? 0);
                if ($availableStock < $item->quantity) {
                    throw new \Exception("Sản phẩm {$item->product->name} không đủ số lượng (Chỉ còn {$availableStock}).");
                }

            }
            $subtotal = 0;
            foreach ($items as $item) {
                $price = $item->product->sale_price ?? $item->product->price;
                $subtotal += $price * $item->quantity;
            }

            $loyaltyConfig      = $this->getLoyaltySettings();
            $spendingPerPoint   = (int) ($loyaltyConfig['spending_per_point'] ?? 10000);
            $valuePerPoint      = (int) ($loyaltyConfig['value_per_point'] ?? 100);
            $maxDiscountPercent = (float) ($loyaltyConfig['max_point_discount_percent'] ?? 10);

            $earnedPoints = ($spendingPerPoint > 0) ? (int) floor($subtotal / $spendingPerPoint) : 0;

            $user       = Auth::user();
            $userPoints = (int) ($user->reward_points ?? 0);
    
            $pointsUsed = 0;
            $discount   = 0;

            if ($this->usePoints && $userPoints > 0) {
                $maxDiscountAllowed = $subtotal * ($maxDiscountPercent / 100);
                $maxPointsAllowed   = ($valuePerPoint > 0) ? (int) floor($maxDiscountAllowed / $valuePerPoint) : 0;

                $pointsUsed = min($userPoints, $maxPointsAllowed);
                $discount   = $pointsUsed * $valuePerPoint;
            }

            $shippingFee = $this->calculateShippingFee($distance);
            $totalAmount = max(0, $subtotal - $discount) + $shippingFee;

            // Mã PayOS chuẩn 13 số
            $payosOrderCode = intval(time() . rand(100, 999));
            $fullAddress    = $address->address_detail . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->province;

            $order = Order::create([
                'order_code'           => (string) $payosOrderCode,
                'order_type'           => 'retail',
                'user_id'              => Auth::id(),
                'recipient_name'       => $address->recipient_name ?? 'Khách hàng',
                'recipient_phone'      => $address->recipient_phone ?? '',
                'recipient_address'    => $fullAddress,
                'shipping_distance_km' => $distance,
                'shipping_fee'         => $shippingFee,
                'subtotal'             => $subtotal,
                'points_used'          => $pointsUsed,
                'points_earned'        => $earnedPoints,
                'total'                => $totalAmount,
                'note'                 => $this->orderNote,

                'status'               => 'pending',
            ]);
            if ($pointsUsed > 0) {
                $user->decrement('reward_points', $pointsUsed);
            }

            // Tạo bản ghi thanh toán ban đầu trong bảng payments
            Payment::create([
                'order_id'         => $order->id,
                'payment_code'     => 'PAY-' . $order->order_code,
                'method'           => 'payos',
                'amount'           => $totalAmount,
                'status'           => 'pending',
                'transaction_code' => null,
                'paid_at'          => null,
            ]);
            // Ghi nhận lịch sử trạng thái ban đầu
            if (method_exists($order, 'statusHistories')) {
                $order->statusHistories()->create([
                    'old_status' => null,
                    'new_status' => 'pending',
                    'changed_by' => Auth::id(),
                    'note'       => 'Khách tạo đơn hàng qua cổng trực tuyến PayOS',
                ]);
            }

            foreach ($items as $item) {
                $price = $item->product->sale_price ?? $item->product->price;
                $remainingQtyNeeded = (int) $item->quantity;

                // Lấy các lô hàng còn hạn và còn hàng, sắp xếp hạn sử dụng gần nhất lên đầu (FEFO)
                $availableBatches = $item->product->batches()
                    ->where('product_batches.status', 'active')
                    ->whereDate('product_batches.expiry_date', '>=', now())
                    ->where('product_batches.current_quantity', '>', 0)
                    ->orderBy('product_batches.expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($availableBatches as $batch) {
                    if ($remainingQtyNeeded <= 0) break;

                    $takeQty = min($batch->current_quantity, $remainingQtyNeeded);

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $item->product_id,
                        'batch_id'     => $batch->id,
                        'product_name' => $item->product->name,
                        'price'        => $price,
                        'quantity'     => $takeQty,
                        'subtotal'     => $price * $takeQty,
                    ]);

                    // Trừ số lượng tồn kho của lô này ngay lập tức khi tạo đơn
                    $batch->decrement('current_quantity', $takeQty);

                    $remainingQtyNeeded -= $takeQty;
                }

                // Dự phòng nếu các lô không đủ số lượng mà vẫn còn thiếu
                if ($remainingQtyNeeded > 0) {
                    throw new \Exception(
                        "Lô hàng của sản phẩm {$item->product->name} không đủ số lượng. Vui lòng tải lại trang và thử lại."
                    );
                }
            }

            // Gọi PayOS tạo link thanh toán (Description <= 25 ký tự)
            $payOS = new PayOS(
                config('services.payos.client_id'),
                config('services.payos.api_key'),
                config('services.payos.checksum_key')
            );

            $payosData = [
                "orderCode"   => $payosOrderCode,
                "amount"      => (int) $totalAmount,
                "description" => "TT " . $payosOrderCode,
                "returnUrl"   => route('customer.checkout.success', ['order_id' => $order->id]),
                "cancelUrl"   => route('customer.checkout.cancel', ['order_id' => $order->id]),
                "expiredAt"   => time() + (5 * 60),
            ];

            $response = $payOS->createPaymentLink($payosData);

            session()->forget(['checkout_selected_items', 'checkout_use_points']);

            CartItem::whereIn('id', $items->pluck('id'))->delete();
            DB::commit();

            $this->dispatch('cart-updated');

            return redirect()->away($response['checkoutUrl']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo đơn PayOS: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Có lỗi xảy ra khi tạo link thanh toán: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $items = $this->getCheckoutItems();

        $subtotal = 0;
        foreach ($items as $item) {
            $price = $item->product->sale_price ?? $item->product->price;
            $subtotal += $price * $item->quantity;
        }

        $loyaltyConfig      = $this->getLoyaltySettings();
        $spendingPerPoint   = (int) ($loyaltyConfig['spending_per_point'] ?? 10000);
        $valuePerPoint      = (int) ($loyaltyConfig['value_per_point'] ?? 100);
        $maxDiscountPercent = (float) ($loyaltyConfig['max_point_discount_percent'] ?? 10);

        $earnedPoints      = ($spendingPerPoint > 0) ? (int) floor($subtotal / $spendingPerPoint) : 0;
        $earnedPointsValue = $earnedPoints * $valuePerPoint;
        $pointsPer100k     = ($spendingPerPoint > 0) ? (int) floor(100000 / $spendingPerPoint) : 0;

        $user       = Auth::user();
        $userPoints = (int) ($user->reward_points ?? 0);

        $maxDiscountAllowed = $subtotal * ($maxDiscountPercent / 100);
        $maxPointsAllowed   = ($valuePerPoint > 0) ? (int) floor($maxDiscountAllowed / $valuePerPoint) : 0;

        $pointsToUse  = min($userPoints, $maxPointsAllowed);
        $canUsePoints = $userPoints > 0 && $pointsToUse > 0;

        $discount = ($this->usePoints && $canUsePoints) ? ($pointsToUse * $valuePerPoint) : 0;

        $address = $this->selectedAddress;
        $maxDistance = $this->getMaxShippingDistance();

        if ($address) {
            $distance           = $this->getShippingDistance($address->latitude ?? null, $address->longitude ?? null);
            $isDistanceExceeded = $distance > $maxDistance;
            $shippingFee        = $isDistanceExceeded ? 0 : $this->calculateShippingFee($distance);
        } else {
            $distance           = 0;
            $isDistanceExceeded = false;
            $shippingFee        = 0;
        }

        $totalAmount = max(0, $subtotal - $discount) + $shippingFee;
        $addresses   = Address::where('user_id', Auth::id())->get();

        return view('livewire.customer.checkout-page', [
            'items'              => $items,
            'subtotal'           => $subtotal,
            'shippingFee'        => $shippingFee,
            'discount'           => $discount,
            'pointsUsed'         => $pointsToUse,
            'userPoints'         => $userPoints,
            'canUsePoints'       => $canUsePoints,
            'valuePerPoint'      => $valuePerPoint,
            'maxDiscountPercent' => $maxDiscountPercent,
            'totalAmount'        => $totalAmount,
            'earnedPoints'       => $earnedPoints,
            'earnedPointsValue'  => $earnedPointsValue,
            'pointsPer100k'      => $pointsPer100k,
            'distance'           => $distance,
            'maxDistance'        => $maxDistance,
            'isDistanceExceeded' => $isDistanceExceeded,
            'addresses'          => $addresses,
            'address'            => $address,
        ])->layout('layouts.customer.app');
    }
}
