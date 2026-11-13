<?php

namespace App\Livewire\FarmOwner;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;

class Sidebar extends OwnerPage
{
    #[Locked]
    public string $activeRoute = '';

    public function mount(): void
    {
        $this->activeRoute = request()->route()?->getName() ?? '';
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
                'route' => 'farm-owner.customers',
                'label' => 'Danh sách khách hàng',
                'icon' => 'fa-users',
            ],
            [
                'route' => 'farm-owner.products',
                'label' => 'Danh sách sản phẩm',
                'icon' => 'fa-boxes-stacked',
            ],
            [
                'route' => 'farm-owner.batches',
                'label' => 'Danh sách lô hàng nông sản',
                'icon' => 'fa-seedling',
            ],
        ];

        $items = array_map(function (array $item): array {
            $item['url'] = route($item['route']);

            $item['active'] =
                $this->activeRoute === $item['route']
                || str_starts_with(
                    $this->activeRoute,
                    $item['route'] . '.'
                );

            return $item;
        }, $items);

        $user = Auth::guard('web')->user();

        return view('livewire.farm-owner.sidebar', [
            'items' => $items,
            'dashboardUrl' => route('farm-owner.dashboard'),
            'userName' => $user->name,
            'avatarUrl' => $user->avatar_url
                ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150',
        ]);
    }
}