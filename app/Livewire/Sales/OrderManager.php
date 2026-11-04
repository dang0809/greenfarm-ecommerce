<?php

namespace App\Livewire\Sales;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.sales.app')]
#[Title('Quản Lý Đơn Hàng | Nông Trại Đất Lành')]
class OrderManager extends Component
{
    public function render()
    {
        return view('livewire.sales.order-manager');
    }
}

