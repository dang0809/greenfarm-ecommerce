<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

#[Title('Giỏ Hàng Của Bạn | Nông Trại Đất Lành')]
class CartPage extends Component
{
    public array $selectedItems = []; // Danh sách cart_item_id được tick chọn
    public bool $selectAll = true;

    private function getAvailableStock(int $productId): int
    {
        $product = Product::withSum(['batches as total_stock' => function ($q) {
            $q->where('product_batches.status', 'active')
            ->where('product_batches.current_quantity', '>', 0)
            ->whereDate('product_batches.expiry_date', '>=', now());
        }], 'current_quantity')->find($productId);

        return (int) ($product?->total_stock ?? 0);
    }

    public function mount()
    {
        $this->loadSelectedItems();
    }

    // Lấy giỏ hàng của User từ Database
    public function getCart()
    {
        return Cart::with(['items.product.category', 'items.product.productImages'])
            ->where('user_id', Auth::id())
            ->first();
    }

    // Mặc định chọn tất cả sản phẩm khi vừa mở trang
    public function loadSelectedItems()
    {
        $cart = $this->getCart();
        if ($cart) {
            $this->selectedItems = $cart->items->pluck('id')->map(fn($id) => (string) $id)->toArray();
            $this->selectAll = count($this->selectedItems) > 0;
        }
    }

    // Xử lý khi bấm checkbox "Chọn tất cả"
    public function updatedSelectAll($value)
    {
        $cart = $this->getCart();
        if (!$cart) return;

        if ($value) {
            $this->selectedItems = $cart->items->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    // Xử lý khi bỏ tick/tick từng sản phẩm lẻ
    public function updatedSelectedItems()
    {
        $cart = $this->getCart();
        if (!$cart) return;

        $totalItems = $cart->items->count();
        $this->selectAll = $totalItems > 0 && count($this->selectedItems) === $totalItems;
    }

    // Tăng số lượng
    public function incrementQuantity(int $cartItemId)
    {
        $cartItem = CartItem::whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))->find($cartItemId);
        if (!$cartItem) return;

        $availableStock = $this->getAvailableStock($cartItem->product_id);

        if ($cartItem->quantity + 1 > $availableStock) {
            $this->dispatch(
                'show-toast',
                type: 'error',
                message: "Rất tiếc, kho chỉ còn lại $availableStock sản phẩm!"
            );
            return;
        }

        $cartItem->increment('quantity');
    }

    // Giảm số lượng
    public function decrementQuantity(int $cartItemId)
    {
        $cartItem = CartItem::whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))->find($cartItemId);
        if ($cartItem && $cartItem->quantity > 1) {
            $cartItem->decrement('quantity');
        }
    }

    // Thay đổi số lượng trực tiếp từ ô Input
    public function updateQuantity(int $cartItemId, $qty)
    {
        $cartItem = CartItem::whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))->find($cartItemId);
        if (!$cartItem) return;

        $availableStock = $this->getAvailableStock($cartItem->product_id);
        $requestedQty = (int) $qty;
        
        if ($requestedQty > $availableStock) {
            $cartItem->update(['quantity' => max(1, $availableStock)]);

            $this->dispatch(
                'show-toast',
                type: 'error',
                message: "Số lượng vượt quá tồn kho ($availableStock). Đã điều chỉnh về số lượng tối đa!"
            );
            return;
        }

        $finalQty = max(1, $requestedQty);
        $cartItem->update(['quantity' => $finalQty]);
    }

    // Xóa 1 sản phẩm
    public function removeItem(int $cartItemId)
    {
        $cartItem = CartItem::whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))->find($cartItemId);
        if ($cartItem) {
            $cartItem->delete();
            $this->selectedItems = array_values(array_diff($this->selectedItems, [(string) $cartItemId]));
            $this->updatedSelectedItems();

            $this->dispatch('cart-updated');
            $this->dispatch('show-toast', type: 'success', message: 'Đã xóa sản phẩm khỏi giỏ hàng.');
        }
    }

    // Xóa các món đã tick chọn
    public function removeSelected()
    {
        if (empty($this->selectedItems)) return;

        CartItem::whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))
            ->whereIn('id', $this->selectedItems)
            ->delete();

        $this->selectedItems = [];
        $this->selectAll = false;
        $this->dispatch('cart-updated');
        $this->dispatch(
            'show-toast',
            type: 'success',
            message: 'Đã xóa các sản phẩm được chọn khỏi giỏ hàng.'
        );
    }

    public function proceedToCheckout()
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('show-toast', type: 'error', message: 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán!');
            return;
        }

        // Chỉ lưu danh sách ID sản phẩm chọn mua sang Session
        session([
            'checkout_selected_items' => array_map('intval', $this->selectedItems),
        ]);

        return redirect()->route('customer.checkout');
    }

    public function render()
    {
        $cart = $this->getCart();
        $items = $cart ? $cart->items : collect();

        // Tính tạm tính dựa trên các mục được tick
        $subtotal = 0;
        $selectedCount = 0;

        foreach ($items as $item) {
            if (in_array((string) $item->id, $this->selectedItems)) {
                $price = $item->product->sale_price ?? $item->product->price;
                $subtotal += $price * $item->quantity;
                $selectedCount++;
            }
        }

        return view('livewire.customer.cart-page', [
            'items'         => $items,
            'subtotal'      => $subtotal,
            'total'         => $subtotal, // Tổng tiền giỏ hàng đúng bằng tạm tính
            'selectedCount' => $selectedCount,
        ])->layout('layouts.customer.app');
    }
}