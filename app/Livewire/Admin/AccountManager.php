<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin.app')]
#[Title('Quản Lý Tài Khoản | GreenFarm Admin')]
class AccountManager extends Component
{
    public function render()
    {
        return view('livewire.admin.account-manager');
    }
}