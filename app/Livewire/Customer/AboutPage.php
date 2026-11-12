<?php

namespace App\Livewire\Customer;

use App\Models\Page;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Certification;
use App\Models\FarmImage;

#[Layout('layouts.customer.app')]
#[Title('Về Nông Trại')]
class AboutPage extends Component
{
    public function render()
    {
        // 1. Lấy bài viết từ bảng `pages` theo slug 'cau-chuyen-thuong-hieu'
        $page = Page::where('slug', 'cau-chuyen-thuong-hieu')->first();

        // 2. Lấy hình ảnh từ bảng `farm_images` (sắp xếp theo cột `sort_order`)
        $farmImages = FarmImage::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // 3. Lấy danh sách chứng nhận từ bảng `certifications` (lọc trạng thái 'active')
        $certifications = Certification::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', today());
            })
            ->orderBy('id')
            ->get();

        return view('livewire.customer.about-page', [
            'page' => $page,
            'farmImages' => $farmImages,
            'certifications' => $certifications,
            'farmAddress' => SystemSetting::contactSettings()['farm_address'],
        ]);
    }
}