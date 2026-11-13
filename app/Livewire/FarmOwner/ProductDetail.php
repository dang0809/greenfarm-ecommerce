<?php

namespace App\Livewire\FarmOwner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class ProductDetail extends Component
{
    use WithPagination;

    #[Locked]
    public int $productId;

    public function boot(): void
    {
        $owner = Auth::id()
            ? User::find(Auth::id())
            : null;

        abort_unless(
            $owner
            && $owner->role === 'farm_owner'
            && $owner->status === 'active',
            403,
            'Bạn không có quyền truy cập khu vực chủ trang trại.'
        );
    }

    public function mount(Product $product): void
    {
        $this->productId = $product->id;
    }

    public function render()
    {
        $product = Product::query()
            ->with([
                'category:id,name',
                'productImages' => fn($query) => $query
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->withTotalStock()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->findOrFail($this->productId);

        $reviews = $product->reviews()
            ->with([
                'user:id,name,avatar',
                'images' => fn($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->latest('created_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'reviewsPage');

        return view(
            'livewire.farm-owner.product-detail',
            compact('product', 'reviews')
        )->layout('layouts.farm-owner.app', [
                    'title' => 'Chi tiết sản phẩm',
                ]);
    }
}