<?php

namespace App\Livewire\FarmOwner;

use App\Models\Product;
use Livewire\WithPagination;

class ProductManager extends OwnerPage
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

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