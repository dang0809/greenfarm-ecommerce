<?php

namespace App\Livewire\Sales;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    /**
     * Xử lý đăng xuất tài khoản an toàn
     */
    public function logout()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        session()->flash('success', 'Đã đăng xuất tài khoản thành công!');

        return $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.sidebar', [
            'user' => Auth::user(),
        ]);
    }
}

