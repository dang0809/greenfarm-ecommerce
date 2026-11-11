<?php

namespace App\Livewire\Customer;

use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout; 
use Livewire\Attributes\Title;
use App\Livewire\Traits\HandlesCart;
use App\Models\Cart;
use App\Models\CartItem;
#[Layout('layouts.customer.app')]
#[Title('Chi tiết sản phẩm')]
class ProductDetail extends Component
{
    use WithFileUploads, WithPagination, HandlesCart;

    public Product $product;
    public string $selectedImage = '';
    public int $quantity = 1;
    public float $totalStock = 0;

    // Tabs
    public string $activeTab = 'desc';

    // Form Đánh giá
    public int $rating = 5;
    public string $content = '';
    public $reviewPhotos = [];

    public function mount(string $slug)
    {
        $this->product = Product::with([
            'category',
            'productImages',
            'batches' => fn($q) => $q->where('product_batches.status', 'active')
                                    ->whereRaw('product_batches.current_quantity > COALESCE(product_batches.reserved_quantity, 0)')
                                    ->whereDate('product_batches.expiry_date', '>=', now())
                                    ->orderBy('product_batches.expiry_date', 'asc'),
        ])->where('slug', $slug)
          ->where('status', 'active')
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
          ->firstOrFail();

        if (request()->query('tab') === 'reviews' || request()->has('reviewsPage')) {
            $this->activeTab = 'reviews';
        }

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

        $this->totalStock = (int) ($this->product->total_stock ?? 0);
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
            ->where('status', 'delivered')
            ->whereHas('items', fn($q) => $q->where('product_id', $this->product->id))
            ->exists();
    }

    public function getUnreviewedOrderItemProperty(): ?OrderItem
    {
        if (!Auth::check()) {
            return null;
        }

        // Tìm một món hàng của sản phẩm này trong các đơn đã giao (delivered)
        // mà TRONG ĐƠN HÀNG ĐÓ chưa có bất kỳ đánh giá nào cho sản phẩm này (1 sản phẩm / 1 đơn = 1 đánh giá)
        return OrderItem::where('product_id', $this->product->id)
            ->whereHas('order', function ($query) {
                $query->where('user_id', Auth::id())
                      ->where('status', 'delivered')
                      ->whereDoesntHave('items', function ($itemQuery) {
                          $itemQuery->where('product_id', $this->product->id)
                                    ->whereHas('review');
                      });
            })
            ->first();
    }

    public function getHasReviewedAllPurchasesProperty(): bool
    {
        return $this->hasPurchased && ($this->unreviewedOrderItem === null);
    }

    public function removePhoto(int $index): void
    {
        if (isset($this->reviewPhotos[$index])) {
            unset($this->reviewPhotos[$index]);
            $this->reviewPhotos = array_values($this->reviewPhotos);
        }
    }

    public function buyNow()
    {
        $user = Auth::user();
        if (!$user) {
            $this->dispatch('open-auth-modal', 'login');
            return;
        }

        $qty = $this->quantity > 0 ? (int) $this->quantity : 1;

        session()->put('checkout_buy_now', [
            'product_id' => $this->product->id,
            'quantity'   => $qty,
        ]);
        session()->forget('checkout_selected_items');
        session()->save();

        return redirect()->route('customer.checkout', ['mode' => 'buy_now']);
    }

    public function submitReview(): void
    {
        if (!Auth::check()) {
            $this->dispatch('open-auth-modal', tab: 'login')->to('customer.auth.auth-modal');
            return;
        }

        $orderItem = $this->unreviewedOrderItem;
        if (!$orderItem) {
            $msg = $this->hasPurchased
                ? 'Bạn đã hoàn tất đánh giá cho tất cả các đơn hàng đã mua của sản phẩm này!'
                : 'Bạn cần mua và nhận hàng thành công sản phẩm này để có thể gửi đánh giá!';
            $this->dispatch('show-toast', type: 'error', message: $msg);
            return;
        }

        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|min:10|max:1000',
            'reviewPhotos' => 'nullable|array|max:5',
            'reviewPhotos.*' => 'image|max:3072',
        ], [
            'content.required' => 'Vui lòng nhập nội dung đánh giá.',
            'content.min' => 'Nội dung đánh giá phải từ 10 ký tự trở lên.',
            'content.max' => 'Nội dung đánh giá không được vượt quá 1000 ký tự.',
            'reviewPhotos.max' => 'Chỉ được tải lên tối đa 5 hình ảnh thực tế.',
            'reviewPhotos.*.image' => 'File đính kèm phải là hình ảnh (jpg, png, webp, jpeg).',
            'reviewPhotos.*.max' => 'Mỗi ảnh tải lên không được vượt quá 3MB.',
        ]);

        DB::transaction(function () use ($orderItem) {
            $review = Review::create([
                'user_id' => Auth::id(),
                'product_id' => $this->product->id,
                'order_item_id' => $orderItem->id,
                'rating' => $this->rating,
                'content' => trim($this->content),
            ]);

            if (!empty($this->reviewPhotos)) {
                foreach ($this->reviewPhotos as $index => $photo) {
                    $path = $photo->store('reviews', 'r2');
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'image' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }
        });

        $this->reset(['content', 'reviewPhotos']);
        $this->rating = 5;

        // Xóa cache computed property để Livewire re-render ngay lập tức và ẩn form mà không cần F5
        unset($this->unreviewedOrderItem);
        unset($this->hasPurchased);
        unset($this->hasReviewedAllPurchases);

        $this->resetPage('reviewsPage');

        $this->dispatch('show-toast', type: 'success', message: 'Cảm ơn bạn đã gửi đánh giá thực tế cho sản phẩm!');
    }

    public function render()
    {
        $relatedProducts = Product::with(['productImages', 'category'])
            ->withAvg('reviews', 'rating')
            ->withTotalStock()
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        $reviewStats = Review::where('product_id', $this->product->id)
            ->selectRaw('COUNT(*) as total_count, AVG(rating) as avg_rating')
            ->first();

        $reviewCount = (int) ($reviewStats->total_count ?? 0);
        $avgRating = $reviewCount > 0 ? round((float) $reviewStats->avg_rating, 1) : 5.0;

        $reviews = Review::with(['user', 'images'])
            ->where('product_id', $this->product->id)
            ->latest()
            ->paginate(10, ['*'], 'reviewsPage');

        $currentBatch = $this->product->batches->first();

        return view('livewire.customer.product-detail', [
            'relatedProducts' => $relatedProducts,
            'currentBatch'    => $currentBatch,
            'reviews'         => $reviews,
            'reviewCount'     => $reviewCount,
            'avgRating'       => $avgRating,
        ]);
    }
}