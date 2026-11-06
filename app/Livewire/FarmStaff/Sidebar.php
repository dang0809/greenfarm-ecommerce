<?php

namespace App\Livewire\FarmStaff;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    protected $listeners = [
        'profile-updated' => '$refresh',
    ];
    // Hàm xử lý đăng xuất
    public function logout(): void
    {
        Auth::guard('staff')->logout();

        // Giữ danh tính khách hàng trong guard web.
        session()->migrate(true);

        session()->flash(
            'success',
            'Đã đăng xuất tài khoản nội bộ!'
        );

        $this->redirect(route('login'));
    }

    public function render()
    {
        return view('livewire.farm-staff.sidebar', [
            'user' => Auth::guard('staff')->user(),
        ]);
    }
}