<?php

namespace App\Livewire\Customer;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Livewire\Traits\HandlesCart; 
#[Layout('layouts.customer.app')]
#[Title('Nông Trại Đất Lành | Nông Sản Sạch Tự Trồng - Tự Bán')]
class HomePage extends Component
{
   use HandlesCart;

    public function render()
    {
        $banners = DB::table('banners')
            ->where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->get();

        $categories = Category::where('is_active', true)->get();

        // Lấy 8 sản phẩm bán chạy nhất kèm tổng tồn kho thực tế (FEFO)
        $products = Product::with([
            'category', 
            'productImages' => function ($query) {
                $query->where('is_primary', true);
            }
        ])
        ->withSum(['batches as total_stock' => function ($q) {
            $q->where('product_batches.status', 'active')
              ->where('product_batches.current_quantity', '>', 0)
              ->whereDate('product_batches.expiry_date', '>=', now());
        }], 'current_quantity')
        ->where('status', 'active')
        ->orderByRaw('CASE WHEN total_stock > 0 THEN 0 ELSE 1 END') // Ưu tiên sản phẩm CÒN HÀNG xếp trước
        ->orderBy('sold_count', 'desc')
        ->take(8)
        ->get();

        return view('livewire.customer.home-page', [
            'banners' => $banners,
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}