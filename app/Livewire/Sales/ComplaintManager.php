<?php

namespace App\Livewire\Sales;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.sales.app')]
#[Title('Xử Lý Khiếu Nại & Đổi Trả | Nông Trại Đất Lành')]
class ComplaintManager extends Component
{
    public function render()
    {
        return view('livewire.sales.complaint-manager');
    }
}

