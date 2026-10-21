<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;

#[Title('Thanh Toán Đơn Hàng | Nông Trại Đất Lành')]
class CheckoutPage extends Component
{
    public ?int $selectedAddressId = null;
    public string $orderNote = '';
    public string $paymentMethod = 'payos';
    public bool $showAddressModal = false;

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
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

    public function getCheckoutItems()
    {
        $cart = Cart::with(['items.product.category', 'items.product.productImages'])
            ->where('user_id', Auth::id())
            ->first();

        return $cart ? $cart->items : collect();
    }

    public function getSelectedAddressProperty()
    {
        if (!$this->selectedAddressId) return null;
        return Address::where('user_id', Auth::id())->find($this->selectedAddressId);
    }

    public function selectAddress(int $addressId)
    {
        $address = Address::where('user_id', Auth::id())->find($addressId);
        if ($address) {
            $this->selectedAddressId = $address->id;
            $this->showAddressModal = false;
            $this->dispatch('show-toast', type: 'success', message: 'Đã cập nhật địa chỉ giao hàng.');
        }
    }

    public function calculateShippingFee($distanceKm): int
    {
        if ($distanceKm <= 5) return 25000;
        if ($distanceKm <= 10) return 35000;
        if ($distanceKm <= 15) return 45000;
        return 60000;
    }

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

        DB::beginTransaction();
        try {
            $subtotal = 0;
            foreach ($items as $item) {
                $price = $item->product->sale_price ?? $item->product->price;
                $subtotal += $price * $item->quantity;
            }

            $address = $this->selectedAddress;
            $distance = 8.5;
            $shippingFee = $this->calculateShippingFee($distance);
            
            $discount = 0;
            $totalAmount = max(0, $subtotal - $discount) + $shippingFee;
            $earnedPoints = (int) floor($subtotal * 0.01);

            $fullAddress = $address->address_detail . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->province;

            $order = Order::create([
                'order_code'           => 'DL' . strtoupper(uniqid()),
                'user_id'              => Auth::id(),
                'recipient_name'       => $address->recipient_name ?? $address->receiver_name ?? 'Khách hàng',
                'recipient_phone'      => $address->recipient_phone ?? $address->receiver_phone ?? '',
                'recipient_address'    => $fullAddress,
                'shipping_distance_km' => $distance,
                'shipping_fee'         => $shippingFee,
                'subtotal'             => $subtotal,
                'points_used'          => 0,
                'points_earned'        => $earnedPoints,
                'total'                => $totalAmount,
                'note'                 => $this->orderNote,
                'payment_method'       => 'payos',
                'payment_status'       => 'pending',
                'status'               => 'pending',
            ]);

            foreach ($items as $item) {
                $price = $item->product->sale_price ?? $item->product->price;
                $batchId = $item->product->productBatches->where('status', 'active')->first()->id ?? 1;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item->product_id,
                    'batch_id'     => $batchId,
                    'product_name' => $item->product->name,
                    'price'        => $price,
                    'quantity'     => $item->quantity,
                    'subtotal'     => $price * $item->quantity,
                ]);
            }

            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)->delete();
            }

            DB::commit();

            $this->dispatch('cart-updated');

            return redirect()->route('', ['order_id' => $order->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage());
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

        $address = $this->selectedAddress;
        $distance = 8.5; 
        $shippingFee = $this->calculateShippingFee($distance);

        $discount = 0; 
        $totalAmount = max(0, $subtotal - $discount) + $shippingFee;
        $earnedPoints = (int) floor($subtotal * 0.01);

        $addresses = Address::where('user_id', Auth::id())->get();

        return view('livewire.customer.checkout-page', [
            'items'        => $items,
            'subtotal'     => $subtotal,
            'shippingFee'  => $shippingFee,
            'discount'     => $discount,
            'totalAmount'  => $totalAmount,
            'earnedPoints' => $earnedPoints,
            'distance'     => $distance,
            'addresses'    => $addresses,
            'address'      => $address,
        ])->layout('layouts.customer.app');
    }
}