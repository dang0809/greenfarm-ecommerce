<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
use App\Livewire\Traits\WithShippingAddress;
use App\Livewire\FarmStaff\ProductManager as ProductData;

#[Title('Thanh Toán Đơn Hàng | Nông Trại Đất Lành')]
class CheckoutPage extends Component
{
    use WithShippingAddress;

    public ?int $selectedAddressId = null;
    public string $orderNote = '';
    public string $paymentMethod = 'payos';
    public bool $showAddressModal = false;
    public array $selectedCartItemIds = [];
    public bool $usePoints = false;
    public bool $isBuyNow = false;

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            $this->dispatch('open-auth-modal', 'login');
            return;
        }

        $mode = request()->query('mode');
        $buyNowData = session('checkout_buy_now');

        if ($mode === 'buy_now' && !empty($buyNowData)) {
            $this->isBuyNow = true;

            $product = ProductData::withTotalStock(Product::query())->find($buyNowData['product_id']);

            if (!$product || $product->status !== 'active') {
                session()->forget('checkout_buy_now');
                session()->flash('error', 'Sản phẩm mua ngay hiện không khả dụng!');
                return redirect()->route('products.index');
            }

            $availableUnits = ProductData::getTotalStock($product);
            if ($availableUnits <= 0) {
                session()->forget('checkout_buy_now');
                session()->flash('error', 'Sản phẩm đã hết hàng trong kho!');
                return redirect()->route('customer.product.detail', $product->slug);
            }

            if ((int) $buyNowData['quantity'] > $availableUnits) {
                $buyNowData['quantity'] = $availableUnits;
                session()->put('checkout_buy_now', $buyNowData);
                session()->flash('warning', 'Số lượng mua đã được tự động điều chỉnh theo tồn kho thực tế.');
            }
        } else {
            $this->isBuyNow = false;
            $rawSelected = session('checkout_selected_items', []);
            $this->selectedCartItemIds = array_map('intval', (array) $rawSelected);

            if (empty($this->selectedCartItemIds)) {
                session()->flash('error', 'Vui lòng chọn sản phẩm để thanh toán!');
                return redirect()->route('customer.cart');
            }

            $cart = Cart::with([
                'items.product' => fn ($relation) => ProductData::withTotalStock($relation->getQuery()),
            ])->where('user_id', $user->id)->first();

            if (!$cart || $cart->items()->count() === 0) {
                session()->flash('error', 'Giỏ hàng của bạn đang rỗng!');
                return redirect()->route('customer.cart');
            }

            // Tự động kiểm tra và đồng bộ lại tồn kho thực tế cho các sản phẩm đã chọn
            $hasStockAdjusted = false;
            $validCheckoutIds = [];

            foreach ($cart->items as $cartItem) {
                if (!in_array((int) $cartItem->id, $this->selectedCartItemIds, true)) {
                    continue;
                }

                $availableUnits = ProductData::getTotalStock($cartItem->product);

                if ($availableUnits <= 0) {
                    $hasStockAdjusted = true;
                    continue; // Bỏ qua món đã hết sạch kho
                }

                if ($cartItem->quantity > $availableUnits) {
                    $cartItem->update(['quantity' => $availableUnits]);
                    $cartItem->quantity = $availableUnits;
                    $hasStockAdjusted = true;
                }

                $validCheckoutIds[] = (int) $cartItem->id;
            }

            $this->selectedCartItemIds = $validCheckoutIds;
            session()->put('checkout_selected_items', $validCheckoutIds);

            if (empty($this->selectedCartItemIds)) {
                session()->flash('error', 'Các sản phẩm bạn chọn hiện đã hết hàng trong kho. Vui lòng kiểm tra lại giỏ hàng!');
                return redirect()->route('customer.cart');
            }

            if ($hasStockAdjusted) {
                session()->flash('warning', 'Một số sản phẩm trong giỏ đã được tự động điều chỉnh số lượng theo tồn kho thực tế.');
            }
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

    // lấy sản phẩm trong giỏ hàng hoặc từ luồng mua ngay
    public function getCheckoutItems()
    {
        if ($this->isBuyNow) {
            $buyNowData = session('checkout_buy_now');
            if (empty($buyNowData)) return collect();

            $product = Product::with(['category', 'productImages'])->find($buyNowData['product_id']);

            if (!$product) return collect();

            $item = new CartItem([
                'product_id' => $product->id,
                'quantity'   => (float) $buyNowData['quantity'],
            ]);
            $item->setRelation('product', $product);

            return collect([$item]);
        }

        $cart = Cart::with(['items.product.category', 'items.product.productImages'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$cart) return collect();

        $selectedIds = array_map('intval', $this->selectedCartItemIds);

        return $cart->items->filter(function ($item) use ($selectedIds) {
            return in_array((int) $item->id, $selectedIds, true);
        });
    }

    // tính phí giao hàng
    public function calculateShippingFee(?float $distanceKm): int
    {
        if ($distanceKm === null) {
            return 0;
        }

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
            $msg = $this->isBuyNow ? 'Sản phẩm mua ngay không hợp lệ!' : 'Giỏ hàng của bạn đang rỗng!';
            $this->dispatch('show-toast', type: 'error', message: $msg);
            return redirect()->route($this->isBuyNow ? 'products.index' : 'customer.cart');
        }

        $address = $this->selectedAddress;
        $distance = $this->getShippingDistance($address->latitude ?? null, $address->longitude ?? null);
        $maxDistance = $this->getMaxShippingDistance();

        if (!$this->validateShippingAddress($address, $distance, $maxDistance)) {
            return;
        }

        DB::beginTransaction();
        try {

            $productIds = $items->pluck('product_id')->toArray();
        
            // Khóa các dòng sản phẩm này lại, không cho người khác mua cùng lúc
            $productsInDB = Product::whereIn('id', $productIds)
                ->tap([ProductData::class, 'withTotalStock'])
                ->lockForUpdate()
                ->get();

            $subtotal = 0;
            foreach ($items as $item) {
                $product = $productsInDB->firstWhere('id', $item->product_id);
                
                // 1. Kiểm tra trạng thái bán
                if (!$product || $product->status !== 'active') {
                    throw new \Exception("Sản phẩm {$item->product->name} hiện không khả dụng.");
                }

                // 2. Kiểm tra tồn kho có đủ số đơn vị mua không
                $availableUnits = ProductData::getTotalStock($product);
                if ($availableUnits < (int) $item->quantity) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ số lượng (Chỉ còn {$availableUnits} {$product->unit}).");
                }

                // 3. Tính tạm tính tiền hàng theo giá FEFO từng lô (lô cận date tính giá sale, phần bù tính giá gốc)
                $subtotal += $product->getPriceBreakdownForQuantity((int) $item->quantity)['total'];
            }

            $loyaltyConfig      = $this->getLoyaltySettings();
            // Số tiền chi tiêu để tích được 1 điểm thưởng (VD: 10.000đ được 1 điểm)
            $spendingPerPoint   = (int) ($loyaltyConfig['spending_per_point'] ?? 10000);
            // Giá trị quy đổi tiền mặt của 1 điểm khi dùng giảm giá (VD: 1 điểm = 100đ)
            $valuePerPoint      = (int) ($loyaltyConfig['value_per_point'] ?? 100);
            // Tỷ lệ giảm giá trần tối đa cho phép từ điểm thưởng (VD: tối đa 10% tiền hàng)
            $maxDiscountPercent = (float) ($loyaltyConfig['max_point_discount_percent'] ?? 10);

            // Số điểm thưởng khách tích lũy được từ đơn hàng này
            $earnedPoints = ($spendingPerPoint > 0) ? (int) floor($subtotal / $spendingPerPoint) : 0;

            $user       = Auth::user();
            // Tổng số điểm thưởng khách hiện có trong ví
            $userPoints = (int) ($user->reward_points ?? 0);
    
            $pointsUsed = 0;
            $discount   = 0;

            if ($this->usePoints && $userPoints > 0) {
                // Số tiền giảm tối đa (VNĐ) theo quy định chính sách trần
                $maxDiscountAllowed = $subtotal * ($maxDiscountPercent / 100);
                // Quy đổi số tiền giảm tối đa sang số điểm thưởng tối đa đơn này chấp nhận
                $maxPointsAllowed   = ($valuePerPoint > 0) ? (int) floor($maxDiscountAllowed / $valuePerPoint) : 0;

                // Số điểm thực tế bị trừ trong ví (không vượt quá điểm khách có và không vượt trần)
                $pointsUsed = min($userPoints, $maxPointsAllowed);
                // Số tiền thực tế được giảm trừ vào tổng đơn
                $discount   = $pointsUsed * $valuePerPoint;
            }

            $shippingFee = $this->calculateShippingFee($distance);
            $totalAmount = max(0, $subtotal - $discount) + $shippingFee;

            // Mã PayOS chuẩn 13 số
            $payosOrderCode = intval(time() . rand(100, 999));
            $fullAddress    = $address->full_address;

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
                'method'           => $this->paymentMethod,
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
                ]);
            }

            foreach ($items as $item) {
                $remainingNeeded = (int) $item->quantity;

                // Lấy các lô hàng còn hạn và còn hàng khả dụng, sắp xếp hạn sử dụng gần nhất lên đầu (FEFO)
                $availableBatches = $item->product->batches()
                    ->where('product_batches.status', 'active')
                    ->whereDate('product_batches.expiry_date', '>=', now())
                    ->whereRaw('product_batches.current_quantity > COALESCE(product_batches.reserved_quantity, 0)')
                    ->orderBy('product_batches.expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($availableBatches as $batch) {
                    if ($remainingNeeded <= 0) break;

                    $availableInBatch = max(0, (int) $batch->current_quantity - (int) ($batch->reserved_quantity ?? 0));
                    $take = min($availableInBatch, $remainingNeeded);
                    if ($take <= 0) continue;

                    // Giá tính chuẩn theo từng lô: Lô có discount_percent > 0 tính giá sale, lô mới tính giá gốc
                    $batchPrice = $batch->discount_percent > 0 
                        ? round($item->product->price * (100 - $batch->discount_percent) / 100) 
                        : (float) $item->product->price;

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $item->product_id,
                        'batch_id'     => $batch->id,
                        'product_name' => $item->product->name,
                        'price'        => $batchPrice,
                        'quantity'     => $take,
                        'subtotal'     => $batchPrice * $take,
                    ]);

                    // Tạm giữ kho trong 5 phút (Tăng reserved_quantity, KHÔNG trừ current_quantity, KHÔNG ghi InventoryLog)
                    $batch->increment('reserved_quantity', $take);

                    $remainingNeeded -= $take;
                }

                // Dự phòng nếu các lô không đủ số lượng mà vẫn còn thiếu
                if ($remainingNeeded > 0) {
                    throw new \Exception(
                        "Lô hàng của sản phẩm {$item->product->name} không đủ số lượng. Vui lòng tải lại trang và thử lại."
                    );
                }

                // Cập nhật lại giá sale ngoài storefront nếu lô sale vừa bị giữ chỗ hết
                \App\Models\ProductBatch::syncProductSalePrice($item->product_id);
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

            if ($this->isBuyNow) {
                session()->forget('checkout_buy_now');
            } else {
                session()->forget('checkout_selected_items');
                CartItem::whereIn('id', $items->pluck('id'))->delete();
                $this->dispatch('cart-updated');
            }

            DB::commit();

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
            $subtotal += $item->product->getPriceBreakdownForQuantity((int) $item->quantity)['total'];
        }

        $loyaltyConfig      = $this->getLoyaltySettings();
        // Số tiền chi tiêu để tích được 1 điểm thưởng (VD: 10.000đ được 1 điểm)
        $spendingPerPoint   = (int) ($loyaltyConfig['spending_per_point'] ?? 10000);
        // Giá trị quy đổi tiền mặt của 1 điểm khi dùng giảm giá (VD: 1 điểm = 100đ)
        $valuePerPoint      = (int) ($loyaltyConfig['value_per_point'] ?? 100);
        // Tỷ lệ giảm giá trần tối đa cho phép từ điểm thưởng (VD: tối đa 10% tiền hàng)
        $maxDiscountPercent = (float) ($loyaltyConfig['max_point_discount_percent'] ?? 10);

        // Số điểm thưởng khách sẽ tích lũy được từ đơn hàng này
        $earnedPoints      = ($spendingPerPoint > 0) ? (int) floor($subtotal / $spendingPerPoint) : 0;
        // Giá trị quy đổi thành tiền mặt (VNĐ) của số điểm thưởng sắp nhận
        $earnedPointsValue = $earnedPoints * $valuePerPoint;
        // Tỷ lệ điểm thưởng nhận được trên mỗi 100.000đ mua hàng (dùng để hiển thị gợi ý trên UI)
        $pointsPer100k     = ($spendingPerPoint > 0) ? (int) floor(100000 / $spendingPerPoint) : 0;

        $user       = Auth::user();
        // Tổng số điểm thưởng khách hiện có trong ví
        $userPoints = (int) ($user->reward_points ?? 0);

        // Số tiền giảm tối đa (VNĐ) theo quy định chính sách trần
        $maxDiscountAllowed = $subtotal * ($maxDiscountPercent / 100);
        // Quy đổi số tiền giảm tối đa sang số điểm thưởng tối đa đơn này chấp nhận
        $maxPointsAllowed   = ($valuePerPoint > 0) ? (int) floor($maxDiscountAllowed / $valuePerPoint) : 0;

        // Số điểm thực tế có thể dùng cho đơn (không vượt quá điểm khách có và không vượt trần)
        $pointsUsed   = min($userPoints, $maxPointsAllowed);
        // Điều kiện cho phép kích hoạt checkbox dùng điểm: khách có điểm và số điểm dùng được > 0
        $canUsePoints = $userPoints > 0 && $pointsUsed > 0;

        // Số tiền giảm thực tế: chỉ tính khi khách tick chọn dùng điểm và thỏa điều kiện dùng điểm
        $discount = ($this->usePoints && $canUsePoints) ? ($pointsUsed * $valuePerPoint) : 0;

        $shippingData       = $this->resolveShippingAddresses();
        $addresses          = $shippingData['addresses'];
        $address            = $shippingData['address'];
        $distance           = $shippingData['distance'];
        $maxDistance        = $shippingData['maxDistance'];
        $isDistanceUnknown  = $shippingData['isDistanceUnknown'];
        $isDistanceExceeded = $shippingData['isDistanceExceeded'];
        $shippingFee        = ($isDistanceExceeded || $isDistanceUnknown || !$address) ? 0 : $this->calculateShippingFee($distance);

        $totalAmount = max(0, $subtotal - $discount) + $shippingFee;

        return view('livewire.customer.checkout-page', [
            'items'              => $items,
            'subtotal'           => $subtotal,
            'shippingFee'        => $shippingFee,
            'discount'           => $discount,
            'pointsUsed'         => $pointsUsed,
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
            'isDistanceUnknown'  => $isDistanceUnknown,
            'addresses'          => $addresses,
            'address'            => $address,
        ])->layout('layouts.customer.app');
    }
}