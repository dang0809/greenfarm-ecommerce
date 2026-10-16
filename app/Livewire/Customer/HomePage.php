<?php

namespace App\Livewire\Customer;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer.app')]
#[Title('Nông Trại Đất Lành | Nông Sản Sạch Tự Trồng - Tự Bán')]
class HomePage extends Component
{
    public function render()
    {
        $banners = DB::table('banners')
            ->where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->get();

        $categories = Category::where('is_active', true)->get();

        $products = Product::with([
            'category', 
            'productImages' => function ($query) {
                $query->where('is_primary', true);
            }
        ])
        ->where('status', 'active')
        ->orderBy('sold_count', 'desc') // Lấy 8 sản phẩm bán chạy nhất
        ->take(8)
        ->get();

       
        return view('livewire.customer.home-page', [
            'banners' => $banners,
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}