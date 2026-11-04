<?php

namespace App\Livewire\Sales;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.sales.app')]
#[Title('Quản Lý Xả Hàng Nông Sản | Nông Trại Đất Lành')]
class ClearanceManager extends Component
{
    public function render()
    {
        return view('livewire.sales.clearance-manager');
    }
}

