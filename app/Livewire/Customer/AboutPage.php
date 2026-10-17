<?php

namespace App\Livewire\Customer;

use App\Models\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer.app')]
#[Title('Về Nông Trại | Nông Trại Đất Lành')]
class AboutPage extends Component
{
    public function render()
    {
        // 1. Lấy bài viết từ bảng `pages` theo slug 'cau-chuyen-thuong-hieu'[cite: 7]
        $page = Page::where('slug', 'cau-chuyen-thuong-hieu')->first();

        // 2. Lấy hình ảnh từ bảng `farm_images` (sắp xếp theo cột `sort_order`)[cite: 5]
        $farmImages = DB::table('farm_images')
            ->orderBy('sort_order', 'asc')
            ->get();

        // 3. Lấy danh sách chứng nhận từ bảng `certifications` (lọc trạng thái 'active')[cite: 6]
        $certifications = DB::table('certifications')
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();

        return view('livewire.customer.about-page', [
            'page' => $page,
            'farmImages' => $farmImages,
            'certifications' => $certifications,
        ]);
    }
}