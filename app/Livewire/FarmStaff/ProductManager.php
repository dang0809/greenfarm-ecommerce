<?php

namespace App\Livewire\FarmStaff;

use App\Models\Product;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.farm-staff.app')]
#[Title('Quản Lý Sản Phẩm Nông Sản | Nông Trại Đất Lành')]
class ProductManager extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';
    public $selectedStatus = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleStatus($productId)
    {
        $product = Product::find($productId);
        if ($product) {
            $product->status = $product->status === 'active' ? 'hidden' : 'active';
            $product->save();
            session()->flash('success', 'Đã cập nhật trạng thái hiển thị sản phẩm!');
        }
    }

    public function render()
    {
        $query = Product::with(['category', 'batches']);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedCategory)) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (!empty($this->selectedStatus)) {
            if ($this->selectedStatus === 'active') {
                $query->where('status', 'active');
            } elseif ($this->selectedStatus === 'hidden') {
                $query->where('status', 'hidden');
            }
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::all();

        return view('livewire.farm-staff.product-manager', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}