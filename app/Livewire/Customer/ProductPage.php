<?php

namespace App\Livewire\Customer;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Traits\HandlesCart;
#[Layout('layouts.customer.app')]
#[Title('Cửa Hàng Nông Sản Sạch | Nông Trại Đất Lành')]
class ProductPage extends Component
{
    use WithPagination;
    use HandlesCart;
    #[Url(as: 'category')]
    public $selectedCategory = '';

    #[Url(as: 'keyword')]
    public $search = '';

    #[Url(as: 'price')]
    public $priceRange = '';

    #[Url(as: 'sort')]
    public $sortBy = 'newest';

    public function updatedSelectedCategory() { $this->resetPage(); }
    public function updatedPriceRange() { $this->resetPage(); }
    public function updatedSortBy() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->selectedCategory = '';
        $this->priceRange = '';
        $this->search = '';
        $this->sortBy = 'newest';
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get();

        $query = Product::with([
            'category',
            'productImages' => function ($q) {
                $q->where('is_primary', true);
            }
        ])
        // Tính tổng tồn kho thực tế từ các lô còn hạn (FEFO)
        ->withSum(['batches as total_stock' => function ($q) {
            $q->where('product_batches.status', 'active')
            ->where('product_batches.current_quantity', '>', 0)
            ->whereDate('product_batches.expiry_date', '>=', now());
        }], 'current_quantity')
        ->where('status', 'active');

        if (!empty($this->selectedCategory)) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (!empty(trim($this->search))) {
            $query->where('name', 'like', '%' . trim($this->search) . '%');
        }

        if ($this->priceRange === 'under_50') {
            $query->where('price', '<', 50000);
        } elseif ($this->priceRange === '50_100') {
            $query->whereBetween('price', [50000, 100000]);
        } elseif ($this->priceRange === 'over_100') {
            $query->where('price', '>', 100000);
        }

        switch ($this->sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('sold_count', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(9);

        return view('livewire.customer.product-page', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}