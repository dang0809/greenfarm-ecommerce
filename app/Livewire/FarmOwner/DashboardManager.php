<?php

namespace App\Livewire\FarmOwner;

use Livewire\Component;

class DashboardManager extends Component
{
    public function render()
    {
        return view('livewire.farm-owner.dashboard-manager')
            ->layout('layouts.farm-owner.app', [
                'title' => 'Bảng Điều Khiển Quản Trị | Nông Trại Đất Lành',
            ]);
    }
}