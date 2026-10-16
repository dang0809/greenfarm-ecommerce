<?php

namespace App\Livewire\Customer;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Header extends Component
{
    public int $cartCount = 0;

    public function render()
    {
        $user = Auth::user();

        return view('livewire.customer.header', [
            'user' => $user,
        ]);
    }
}