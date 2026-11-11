<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Livewire\FarmStaff\ProductManager as ProductData;
#[Title('Giỏ Hàng Của Bạn | Nông Trại Đất Lành')]
class CartPage extends Component
{
    public array $selectedItems = []; // Danh sách cart_item_id được tick chọn
    public bool $selectAll = true;

    private function getAvailableStock(int $productId): int
    {
        $product = ProductData::withTotalStock(
            Product::query()
        )->find($productId);

        return ProductData::getTotalStock($product);
    }

    public function mount()
    {
        $this->loadSelectedItems();
        $messages = $this->syncCartStock();
        if (!empty($messages)) {
            $this->dispatch('show-toast', type: 'warning', message: $messages[0]);
        }
    }

    /**
     * Tự động đồng bộ số lượng sản phẩm trong giỏ hàng theo tồn kho thực tế
     */
    public function syncCartStock(): array
    {
        $cart = $this->getCart();
        if (!$cart) return [];

        $messages = [];
        foreach ($cart->items as $item) {
            $stock = ProductData::getTotalStock($item->product);

            if ($stock <= 0) {
                // Hết sạch hàng: tự động loại khỏi danh sách tick chọn
                if (in_array((string) $item->id, $this->selectedItems, true)) {
                    $this->selectedItems = array_values(array_diff($this->selectedItems, [(string) $item->id]));
                    $messages[] = "Sản phẩm \"{$item->product->name}\" hiện đã hết hàng.";
                }
            } elseif ($item->quantity > $stock) {
                // Vượt tồn kho: tự động hạ xuống đúng mức kho còn lại
                $oldQty = $item->quantity + 0;
                $newQty = $stock + 0;
                $item->update(['quantity' => $stock]);
                $item->quantity = $stock;
                $messages[] = "Sản phẩm \"{$item->product->name}\" đã tự động giảm từ {$oldQty} xuống {$newQty} do kho vừa thay đổi.";
            }
        }

        if (!empty($messages)) {
            $this->updatedSelectedItems();
        }

        return $messages;
    }

    // Lấy giỏ hàng của User từ Database
    public function getCart()
    {
        return Cart::with([
            'items.product.category',
            'items.product.productImages',
            'items.product' => fn ($relation) => ProductData::withTotalStock($relation->getQuery()),
        ])
        ->where('user_id', Auth::id())
        ->first();
    }

    // Mặc định chọn tất cả sản phẩm còn hàng khi vừa mở trang
    public function loadSelectedItems()
    {
        $cart = $this->getCart();
        if ($cart) {
            $this->selectedItems = $cart->items
                ->filter(fn ($item) => ProductData::getTotalStock($item->product) > 0)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
            $this->selectAll = count($this->selectedItems) > 0 && count($this->selectedItems) === $cart->items->count();
        }
    }

    // Xử lý khi bấm checkbox "Chọn tất cả"
    public function updatedSelectAll($value)
    {
        $cart = $this->getCart();
        if (!$cart) return;

        if ($value) {
            $this->selectedItems = $cart->items
                ->filter(fn ($item) => ProductData::getTotalStock($item->product) > 0)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
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

    // Xóa 1 sản phẩm
    public function removeItem(int $cartItemId)
    {
        $cartItem = CartItem::whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))->find($cartItemId);
        if ($cartItem) {
            $cartItem->delete();
            $this->selectedItems = array_values(array_diff($this->selectedItems, [(string) $cartItemId]));
            $this->updatedSelectedItems();

            $this->dispatch('cart-updated');
            $this->dispatch('show-toast', type: 'info', message: 'Đã xóa sản phẩm khỏi giỏ hàng.');
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
            type: 'info',
            message: 'Đã xóa các sản phẩm được chọn khỏi giỏ hàng.'
        );
    }

    public function proceedToCheckout()
    {
        // 1. Kiểm tra biến động tồn kho trước khi chuyển trang
        $messages = $this->syncCartStock();
        if (!empty($messages)) {
            $this->dispatch(
                'show-toast',
                type: 'warning',
                message: 'Tồn kho vừa có thay đổi! ' . $messages[0] . ' Vui lòng kiểm tra lại trước khi tiếp tục.'
            );
            return;
        }

        // 2. Kiểm tra danh sách sản phẩm còn hợp lệ
        if (empty($this->selectedItems)) {
            $this->dispatch('show-toast', type: 'error', message: 'Vui lòng chọn ít nhất 1 sản phẩm còn hàng để thanh toán!');
            return;
        }

        // 3. Lưu danh sách ID sản phẩm chọn mua sang Session
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
                $subtotal += $item->product->getPriceBreakdownForQuantity((int) $item->quantity)['total'];
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