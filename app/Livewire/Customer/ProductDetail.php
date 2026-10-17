<?php

namespace App\Livewire\Customer;

use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout; 
use Livewire\Attributes\Title;

#[Layout('layouts.customer.app')]
#[Title('Chi tiết sản phẩm')]
class ProductDetail extends Component
{
    use WithFileUploads;

    public Product $product;
    public string $selectedImage = '';
    public int $quantity = 1;
    public int $totalStock = 0;

    // Tabs
    public string $activeTab = 'desc'; // 'desc' hoặc 'reviews'

    // Form Đánh giá
    public int $rating = 5;
    public string $comment = '';
    public $reviewPhoto = null;

    public function mount(string $slug)
    {
        // 1. Eager load relationship + Sắp xếp FEFO (Lô sắp hết hạn xếp lên đầu)
        $this->product = Product::with([
            'category',
            'productImages',
            'batches' => fn($q) => $q->where('product_batches.status', 'active')
                                    ->where('product_batches.current_quantity', '>', 0)
                                    ->whereDate('product_batches.expiry_date', '>=', now())
                                    ->orderBy('product_batches.expiry_date', 'asc'), // FIX: Đổi sang FEFO
            'reviews.user'
        ])->where('slug', $slug)
          ->where('status', 'active') // FIX: Chặn sản phẩm đã ngưng bán
          ->firstOrFail();

        // 2. Tìm ảnh đại diện (is_primary = true) hoặc lấy ảnh đầu tiên
        $primaryImgObj = $this->product->productImages->firstWhere('is_primary', true) 
            ?? $this->product->productImages->first();

        $rawImage = $primaryImgObj?->image;

        // 3. Gán giá trị chuỗi hợp lệ (không bao giờ dính null)
        if ($rawImage) {
            $this->selectedImage = str_starts_with($rawImage, 'http') 
                ? $rawImage 
                : asset('storage/' . $rawImage);
        } else {
            $this->selectedImage = 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?auto=format&fit=crop&q=80&w=900';
        }

        // 4. Tính tổng tồn kho thực tế từ các lô còn hạn
        $this->totalStock = (int) $this->product->batches->sum('current_quantity');
        if ($this->totalStock == 0) {
            $this->quantity = 0;
        }
    }

    // Tăng số lượng (Không vượt tồn kho)
    public function incrementQuantity(): void
    {
        if ($this->quantity < $this->totalStock) {
            $this->quantity++;
        }
    }

    // Giảm số lượng
    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    // Đổi ảnh hiển thị trong Gallery
    public function selectImage(string $imageUrl): void
    {
        $this->selectedImage = $imageUrl;
    }

    // Đổi Tab (Mô tả / Đánh giá)
    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // Kiểm tra khách hàng đã mua sản phẩm này chưa
    public function getHasPurchasedProperty(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Order::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->whereHas('items', fn($q) => $q->where('product_id', $this->product->id))
            ->exists();
    }

    // Thao tác Thêm vào giỏ hàng (Trả về bool để kiểm tra)
    public function addToCart(int $productId = null, int $qty = null): bool
    {
        // 1. Nếu chưa đăng nhập -> Bật Popup Đăng nhập / Đăng ký
        if (!Auth::check()) {
            $this->dispatch('open-auth-modal', tab: 'login')->to('customer.auth.auth-modal');
            return false;
        }

        $targetProductId = $productId ?? $this->product->id;
        $addQty = $qty ?? $this->quantity;

        // FIX LỖI 1: Tính đúng tồn kho của sản phẩm mục tiêu
        if ($targetProductId === $this->product->id) {
            $availableStock = $this->totalStock;
        } else {
            $targetProduct = Product::withSum(['batches as total_stock' => function ($q) {
                $q->where('product_batches.status', 'active')
                  ->where('product_batches.current_quantity', '>', 0)
                  ->whereDate('product_batches.expiry_date', '>=', now());
            }], 'current_quantity')->find($targetProductId);

            $availableStock = (int) ($targetProduct?->total_stock ?? 0);
        }

        if ($availableStock <= 0 || $addQty < 1) {
            session()->flash('error', 'Sản phẩm hiện tại đã hết hàng!');
            return false;
        }

        // 2. Thêm hoặc cập nhật vào CSDL Giỏ hàng của Khách
        $cartItem = Cart::firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $targetProductId,
        ]);

        $newQty = ($cartItem->exists ? $cartItem->quantity : 0) + $addQty;

        // Kiểm tra không vượt tồn kho
        if ($newQty > $availableStock) {
            session()->flash('error', "Số lượng trong giỏ hàng không được vượt quá tồn kho ($availableStock kg)!");
            return false;
        }

        $cartItem->quantity = $newQty;
        $cartItem->save();

        $this->dispatch('cart-updated');
        session()->flash('success', 'Đã thêm sản phẩm vào giỏ hàng thành công!');
        return true;
    }

    // Thao tác Mua ngay
    public function buyNow()
    {
        if (!Auth::check()) {
            $this->dispatch('open-auth-modal', tab: 'login')->to('customer.auth.auth-modal');
            return;
        }

        // FIX LỖI 3: Chỉ chuyển hướng khi thêm giỏ hàng thành công
        if ($this->addToCart()) {
            return redirect()->route('customer.cart');
        }
    }

    // Gửi Đánh Giá Sản Phẩm
    public function submitReview(): void
    {
        if (!Auth::check()) {
            $this->dispatch('open-auth-modal', tab: 'login')->to('customer.auth.auth-modal');
            return;
        }

        if (!$this->hasPurchased) {
            session()->flash('error', 'Bạn cần mua sản phẩm này để có thể gửi đánh giá!');
            return;
        }

        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
            'reviewPhoto' => 'nullable|image|max:2048', // Tối đa 2MB
        ], [
            'comment.required' => 'Vui lòng nhập nội dung đánh giá.',
            'comment.min' => 'Nội dung đánh giá phải từ 10 ký tự trở lên.',
            'reviewPhoto.image' => 'File đính kèm phải là hình ảnh.',
            'reviewPhoto.max' => 'Ảnh tải lên không được vượt quá 2MB.',
        ]);

        $imagePath = null;
        if ($this->reviewPhoto) {
            $imagePath = $this->reviewPhoto->store('reviews', 'public');
        }

        Review::create([
            'product_id' => $this->product->id,
            'user_id' => Auth::id(),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'image' => $imagePath,
            'is_verified' => true,
        ]);

        // Reset form
        $this->reset(['comment', 'reviewPhoto']);
        $this->rating = 5;
        $this->product->load('reviews.user');

        session()->flash('success', 'Cảm ơn bạn đã gửi đánh giá cho sản phẩm!');
    }

    public function render()
    {
        // Lấy sản phẩm liên quan cùng danh mục kèm tính tổng tồn kho các lô còn hạn (FEFO)
        $relatedProducts = Product::with(['productImages', 'category', 'reviews'])
            ->withSum(['batches as total_stock' => function ($q) {
                $q->where('product_batches.status', 'active')
                  ->where('product_batches.current_quantity', '>', 0)
                  ->whereDate('product_batches.expiry_date', '>=', now());
            }], 'current_quantity')
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->where('status', 'active') // FIX: Chỉ lấy sản phẩm đang kinh doanh
            ->take(4)
            ->get();

        // Lô xuất kho hiện tại (Lô cận hạn nhất theo FEFO)
        $currentBatch = $this->product->batches->first();

        return view('livewire.customer.product-detail', [
            'relatedProducts' => $relatedProducts,
            'currentBatch' => $currentBatch,
        ]);
    }
}