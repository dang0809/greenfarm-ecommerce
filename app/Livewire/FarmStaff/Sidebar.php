<?php

namespace App\Livewire\FarmStaff;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    protected $listeners = [
        'profile-updated' => '$refresh',
    ];

    public function requestLogout(): void
    {
        $this->dispatch('confirm-farm-staff-logout')->self();
    }

    // Hàm xử lý đăng xuất
    public function logout()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        session()->flash('success', 'Đã đăng xuất tài khoản thành công!');

        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('livewire.farm-staff.sidebar', [
            'user' => Auth::user(),
        ]);
    }
}