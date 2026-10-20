<?php

namespace App\Livewire\Customer;

use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout; 
use Livewire\Attributes\Title;
use App\Livewire\Traits\HandlesCart; // 🟢 1. Import Trait
use App\Models\Cart;
use App\Models\CartItem;
#[Layout('layouts.customer.app')]
#[Title('Chi tiết sản phẩm')]
class ProductDetail extends Component
{
    use WithFileUploads, HandlesCart; // 🟢 2. Nhúng Trait vào Component

    public Product $product;
    public string $selectedImage = '';
    public int $quantity = 1;
    public int $totalStock = 0;

    // Tabs
    public string $activeTab = 'desc';

    // Form Đánh giá
    public int $rating = 5;
    public string $comment = '';
    public $reviewPhoto = null;

    public function mount(string $slug)
    {
        $this->product = Product::with([
            'category',
            'productImages',
            'batches' => fn($q) => $q->where('product_batches.status', 'active')
                                    ->where('product_batches.current_quantity', '>', 0)
                                    ->whereDate('product_batches.expiry_date', '>=', now())
                                    ->orderBy('product_batches.expiry_date', 'asc'),
            'reviews.user'
        ])->where('slug', $slug)
          ->where('status', 'active')
          ->firstOrFail();

        $primaryImgObj = $this->product->productImages->firstWhere('is_primary', true) 
            ?? $this->product->productImages->first();

        $rawImage = $primaryImgObj?->image;

        if ($rawImage) {
            $this->selectedImage = str_starts_with($rawImage, 'http') 
                ? $rawImage 
                : asset('storage/' . $rawImage);
        } else {
            $this->selectedImage = 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?auto=format&fit=crop&q=80&w=900';
        }

        $this->totalStock = (int) $this->product->batches->sum('current_quantity');
        if ($this->totalStock == 0) {
            $this->quantity = 0;
        }
    }

    public function incrementQuantity(): void
    {
        if ($this->quantity < $this->totalStock) {
            $this->quantity++;
        }
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function selectImage(string $imageUrl): void
    {
        $this->selectedImage = $imageUrl;
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

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

    // 🟢 HÀM MUA NGAY: Thêm vào giỏ xong chuyển thẳng tới trang Giỏ hàng
    public function buyNow()
    {
        // 1. Kiểm tra đăng nhập
        if (!Auth::check()) {
            session()->flash('error', 'Vui lòng đăng nhập để thực hiện mua hàng!');
            return redirect()->route('login');
        }

        // 2. Kiểm tra tồn kho
        if ($this->totalStock <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Sản phẩm hiện đã hết hàng!');
            return;
        }

        try {
            // 3. Lấy hoặc tạo giỏ hàng của User
            $cart = Cart::firstOrCreate([
                'user_id' => Auth::id(),
            ]);

            // 4. Thêm hoặc cập nhật sản phẩm vào giỏ với số lượng đang chọn ($this->quantity)
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $this->product->id)
                ->first();

            if ($cartItem) {
                // Nếu đã có trong giỏ, gán lại số lượng mới khách chọn
                $cartItem->update([
                    'quantity' => $this->quantity
                ]);
            } else {
                // Nếu chưa có, tạo mới mục trong giỏ
                CartItem::create([
                    'cart_id'    => $cart->id,
                    'product_id' => $this->product->id,
                    'quantity'   => $this->quantity,
                ]);
            }

            // Bắn sự kiện cập nhật số lượng giỏ hàng trên Header
            $this->dispatch('cart-updated');

            // 5. Chuyển hướng thẳng sang trang Thanh toán
            return redirect()->route('customer.checkout');

        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

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
            'reviewPhoto' => 'nullable|image|max:2048',
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

        $this->reset(['comment', 'reviewPhoto']);
        $this->rating = 5;
        $this->product->load('reviews.user');

        session()->flash('success', 'Cảm ơn bạn đã gửi đánh giá cho sản phẩm!');
    }

    public function render()
    {
        $relatedProducts = Product::with(['productImages', 'category', 'reviews'])
            ->withSum(['batches as total_stock' => function ($q) {
                $q->where('product_batches.status', 'active')
                  ->where('product_batches.current_quantity', '>', 0)
                  ->whereDate('product_batches.expiry_date', '>=', now());
            }], 'current_quantity')
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        $currentBatch = $this->product->batches->first();

        return view('livewire.customer.product-detail', [
            'relatedProducts' => $relatedProducts,
            'currentBatch' => $currentBatch,
        ]);
    }
}