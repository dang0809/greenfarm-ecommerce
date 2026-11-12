<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
class Sidebar extends Component
{
    public function logout(): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.sidebar');
    }
}