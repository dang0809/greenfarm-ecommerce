<?php

namespace App\Livewire\Sales;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.sales.app')]
#[Title('Quản Lý Gói Rau Định Kỳ VIP | Nông Trại Đất Lành')]
class SubscriptionManager extends Component
{
    public function render()
    {
        return view('livewire.sales.subscription-manager');
    }
}

