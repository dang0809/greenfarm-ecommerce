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
use App\Livewire\FarmStaff\ProductManager as ProductData;
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
<<<<<<< HEAD
        ->withTotalStock()
        ->where('status', 'active')
        ->whereHas('category', function ($query) {
            $query->where('is_active', true);
        });
=======
        ->tap([ProductData::class, 'withTotalStock'])
        ->where('status', 'active');
>>>>>>> 903568b21a33506118ffffa22b6a692299a57a6e

        if (!empty($this->selectedCategory)) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (!empty(trim($this->search))) {
            $query->where('name', 'like', '%' . trim($this->search) . '%');
        }

        match ($this->priceRange) {
            'under_50' => $query->where('price', '<', 50000),
            '50_100'   => $query->whereBetween('price', [50000, 100000]),
            'over_100' => $query->where('price', '>', 100000),
            default    => null, // Người dùng chọn "Tất cả mức giá" thì không thêm điều kiện
        };

        match ($this->sortBy) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'popular'    => $query->orderBy('sold_count', 'desc'),
            default      => $query->latest(), // Mặc định: sản phẩm mới nhất
        };

        $products = $query->paginate(9);

        return view('livewire.customer.product-page', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}