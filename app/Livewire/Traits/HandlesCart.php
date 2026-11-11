<?php

namespace App\Livewire\Traits;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
trait HandlesCart
{
    public function addToCart(?int $productId = null): bool
    {
        // 1. Kiểm tra đăng nhập
        if (!Auth::check()) {
            $this->dispatch('open-auth-modal', tab: 'login')->to('customer.auth.auth-modal');
            return false;
        }

        $targetProductId = $productId ?? ($this->product->id ?? null);
        if (!$targetProductId) return false;

        // 2. Lấy thông tin sản phẩm & tính tổng tồn kho các lô còn hạn
        $product = Product::withTotalStock()->find($targetProductId);

        $availableStock = (int) ($product?->total_stock ?? 0);

        if ($availableStock <= 0 ) {
            $this->dispatch(
            'show-toast',
            type: 'error',
            message: 'Sản phẩm hiện tại đã hết hàng!'
        );
            return false;
        }

        // 3. Tìm hoặc tạo Giỏ hàng trong bảng `carts`
        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        // 4. Tìm sản phẩm trong bảng `cart_items`
        $cartItem = CartItem::where('cart_id',$cart->id)
            ->where('product_id', $targetProductId)
            ->first();

        $currentQty = $cartItem ? $cartItem->quantity : 0;
        $newQty = $currentQty + 1;

        // Kiểm tra số lượng vượt tồn kho
        if ($newQty >$availableStock) {
            $this->dispatch(
                'show-toast',
                type: 'error',
                message: "Số lượng trong giỏ hàng không được vượt quá tồn kho còn lại ($availableStock)!"
            );
            return false;
        }

        if ($cartItem) {
            $cartItem->update(['quantity' =>$newQty]);
        } else {
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $targetProductId,
                'quantity'   => 1,
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