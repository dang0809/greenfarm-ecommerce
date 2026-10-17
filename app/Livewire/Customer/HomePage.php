<?php

namespace App\Livewire\Customer;

use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer.app')]
#[Title('Nông Trại Đất Lành | Nông Sản Sạch Tự Trồng - Tự Bán')]
class HomePage extends Component
{
    // Thao tác Thêm vào giỏ hàng trực tiếp từ Trang chủ
    public function addToCart(int $productId, int $qty = 1): void
    {
        // 1. Kiểm tra đăng nhập
        if (!Auth::check()) {
            $this->dispatch('open-auth-modal', tab: 'login')->to('customer.auth.auth-modal');
            return;
        }

        // 2. Lấy sản phẩm kèm tính tổng tồn kho các lô còn hạn
        $product = Product::withSum(['batches as total_stock' => function ($q) {
            $q->where('product_batches.status', 'active')
              ->where('product_batches.current_quantity', '>', 0)
              ->whereDate('product_batches.expiry_date', '>=', now());
        }], 'current_quantity')->find($productId);

        if (!$product) {
            session()->flash('error', 'Sản phẩm không tồn tại!');
            return;
        }

        $availableStock = (int) ($product->total_stock ?? 0);

        if ($availableStock <= 0) {
            session()->flash('error', 'Sản phẩm hiện tại đã hết hàng!');
            return;
        }

        // 3. Thêm hoặc cập nhật vào giỏ hàng
        $cartItem = Cart::firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $productId,
        ]);

        $newQty = ($cartItem->exists ? $cartItem->quantity : 0) + $qty;

        if ($newQty > $availableStock) {
            session()->flash('error', "Số lượng trong giỏ không được vượt quá tồn kho ($availableStock kg)!");
            return;
        }

        $cartItem->quantity = $newQty;
        $cartItem->save();

        $this->dispatch('cart-updated');
        session()->flash('success', 'Đã thêm sản phẩm vào giỏ hàng thành công!');
    }

    public function render()
    {
        $banners = DB::table('banners')
            ->where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->get();

        $categories = Category::where('is_active', true)->get();

        // Lấy 8 sản phẩm bán chạy nhất kèm tổng tồn kho thực tế (FEFO)
        $products = Product::with([
            'category', 
            'productImages' => function ($query) {
                $query->where('is_primary', true);
            }
        ])
        ->withSum(['batches as total_stock' => function ($q) {
            $q->where('product_batches.status', 'active')
              ->where('product_batches.current_quantity', '>', 0)
              ->whereDate('product_batches.expiry_date', '>=', now());
        }], 'current_quantity')
        ->where('status', 'active')
        ->orderByRaw('CASE WHEN total_stock > 0 THEN 0 ELSE 1 END') // Ưu tiên sản phẩm CÒN HÀNG xếp trước
        ->orderBy('sold_count', 'desc')
        ->take(8)
        ->get();

        return view('livewire.customer.home-page', [
            'banners' => $banners,
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}