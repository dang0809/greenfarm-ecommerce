<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin.app')]
#[Title('Cấu Hình Hệ Thống | GreenFarm Admin')]
class SettingManager extends Component
{
    public function render()
    {
        return view('livewire.admin.setting-manager');
    }
}