<?php

namespace App\Livewire\Traits;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
trait HandlesCart
{
    public function addToCart(int $productId, int $quantity = 1): bool
    {
        // 1. Kiểm tra đăng nhập
        if (!Auth::check()) {
            $this->dispatch('open-auth-modal', tab: 'login')->to('customer.auth.auth-modal');
            return false;
        }

        // 2. Lấy thông tin sản phẩm & tính tổng tồn kho các lô còn hạn
        $product = Product::withTotalStock()
            ->where('status', 'active')
            ->whereHas('category', fn($q) => $q->where('is_active', true))
            ->find($productId);

        if (!$product) {
            $this->dispatch(
                'show-toast',
                type: 'error',
                message: 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh!'
            );
            return false;
        }

        $availableStock = (int) ($product->total_stock ?? 0);

        if ($availableStock <= 0) {
            $this->dispatch(
                'show-toast',
                type: 'error',
                message: 'Sản phẩm hiện tại đã hết hàng!'
            );
            return false;
        }

        $qtyToAdd = max(1, $quantity);

        // 3. Tìm hoặc tạo Giỏ hàng trong bảng `carts`
        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        // 4. Tìm sản phẩm trong bảng `cart_items`
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        $currentQty = $cartItem ? (int) $cartItem->quantity : 0;
        $newQty = $currentQty + $qtyToAdd;

        // Kiểm tra số lượng vượt tồn kho
        if ($newQty > $availableStock) {
            $this->dispatch(
                'show-toast',
                type: 'error',
                message: "Số lượng trong giỏ hàng không được vượt quá tồn kho còn lại ({$availableStock})!"
            );
            return false;
        }

        if ($cartItem) {
            $cartItem->update(['quantity' => $newQty]);
        } else {
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $productId,
                'quantity'   => $newQty,
            ]);
        }

        // 5. Cập nhật Badge Header & Bắn thông báo
        $this->dispatch('cart-updated');
        $this->dispatch(
            'show-toast',
            type: 'success',
            message: 'Đã thêm sản phẩm vào giỏ hàng thành công!'
        );
        return true;
    }
}