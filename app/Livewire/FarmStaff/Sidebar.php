<?php

namespace App\Livewire\FarmStaff;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    // Hàm xử lý đăng xuất an toàn
    public function logout()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        session()->flash('success', 'Đã đăng xuất tài khoản thành công!');

        return $this->redirect(route('home'), navigate: true);
    }

    public function render()
    {
        return view('livewire.farm-staff.sidebar', [
            'user' => Auth::user(),
        ]);
    }
}