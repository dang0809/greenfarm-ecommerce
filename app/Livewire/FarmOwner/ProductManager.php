<?php

namespace App\Livewire\FarmOwner;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->with(['category:id,name', 'coverImage'])
            ->search($this->search)
            ->when(
                in_array($this->status, ['active', 'hidden'], true),
                fn($query) => $query->where('status', $this->status)
            )
            ->withTotalStock()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest('id')
            ->paginate(15);

        return view(
            'livewire.farm-owner.product-manager',
            compact('products')
        )->layout('layouts.farm-owner.app', [
                    'title' => 'Danh sách sản phẩm',
                ]);
    }
}