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
#[Title('Cửa Hàng Nông Sản Sạch')]
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

    #[Url(as: 'sale')]
    public bool $onlySale = false;

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }
    public function updatedPriceRange()
    {
        $this->resetPage();
    }
    public function updatedSortBy()
    {
        $this->resetPage();
    }
    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedOnlySale()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->selectedCategory = '';
        $this->priceRange = '';
        $this->search = '';
        $this->sortBy = 'newest';
        $this->onlySale = false;
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::where('is_active', true)
            ->withCount([
                'products' => function ($q) {
                    $q->where('status', 'active');
                }
            ])
            ->get();

        $query = Product::with([
            'category',
            'productImages' => function ($q) {
                $q->where('is_primary', true);
            }
        ])
            ->withTotalStock()
            ->where('status', 'active');

        if (!empty($this->selectedCategory)) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (!empty(trim($this->search))) {
            $query->where('name', 'like', '%' . trim($this->search) . '%');
        }

        $saleScope = fn($q) => $q->whereNotNull('sale_price')
            ->where('sale_price', '>', 0)
            ->whereColumn('sale_price', '<', 'price');

        $saleCount = $saleScope(Product::where('status', 'active'))->count();

        if ($this->onlySale) {
            $saleScope($query);
        }

        $effectivePrice = 'CASE WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price THEN sale_price ELSE price END';

        match ($this->priceRange) {
            'under_50' => $query->whereRaw("({$effectivePrice}) < ?", [50000]),
            '50_100' => $query->whereRaw("({$effectivePrice}) BETWEEN ? AND ?", [50000, 100000]),
            'over_100' => $query->whereRaw("({$effectivePrice}) > ?", [100000]),
            default => null, // Người dùng chọn "Tất cả mức giá" thì không thêm điều kiện
        };

        // Sắp xếp theo tiêu chí người dùng chọn
        match ($this->sortBy) {
            'price_asc' => $query->orderByRaw("({$effectivePrice}) asc"),
            'price_desc' => $query->orderByRaw("({$effectivePrice}) desc"),
            'popular' => $query->orderBy('sold_count', 'desc'),
            default => $query->latest(), // Mặc định: sản phẩm mới nhất
        };

        $products = $query->paginate(9);

        return view('livewire.customer.product-page', [
            'categories' => $categories,
            'products' => $products,
            'saleCount' => $saleCount,
        ]);
    }
}