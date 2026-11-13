<?php

namespace App\Livewire\FarmOwner;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Sidebar extends Component
{
    #[Locked]
    public string $activeRoute = '';

    public function mount(): void
    {
        $this->activeRoute = request()->route()?->getName() ?? '';
    }

    public function unavailable(): void
    {
        $this->dispatch(
            'swal',
            icon: 'info',
            title: 'Chức năng chưa sẵn sàng',
            text: 'Trang này hiện chưa được triển khai.'
        );
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirectRoute('login');
    }

    public function render()
    {
        $items = [
            [
                'route' => 'farm-owner.dashboard',
                'label' => 'Bảng điều khiển & Báo cáo',
                'icon' => 'fa-chart-line',
            ],
            [
                'route' => 'farm-owner.products',
                'label' => 'Quản lý Sản phẩm & Danh mục',
                'icon' => 'fa-boxes-stacked',
            ],
            [
                'route' => 'farm-owner.spoilage',
                'label' => 'Báo cáo Hao hụt & Xuất hủy',
                'icon' => 'fa-trash-can-arrow-up',
            ],
            [
                'route' => 'farm-owner.certifications',
                'label' => 'Chứng nhận & Kiểm định',
                'icon' => 'fa-file-shield',
            ],
            [
                'route' => 'farm-owner.users',
                'label' => 'Quản lý Nhân sự & Khách hàng',
                'icon' => 'fa-users-gear',
            ],
            [
                'route' => 'farm-owner.cms',
                'label' => 'Nội dung & Banner Website',
                'icon' => 'fa-newspaper',
            ],
            [
                'route' => 'farm-owner.settings',
                'label' => 'Cài đặt Cấu hình Hệ thống',
                'icon' => 'fa-sliders',
            ],
        ];

        $items = array_map(function (array $item): array {
            $item['active'] = $this->activeRoute === $item['route'];

            $item['url'] = Route::has($item['route'])
                ? route($item['route'])
                : null;

            return $item;
        }, $items);

        $user = Auth::guard('web')->user();

        return view('livewire.farm-owner.sidebar', [
            'items' => $items,
            'dashboardUrl' => $items[0]['url'] ?? route('home'),
            'userName' => $user?->name ?? 'Chủ trang trại',
            'avatarUrl' => $user?->avatar_url
                ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150',
        ]);
    }
}