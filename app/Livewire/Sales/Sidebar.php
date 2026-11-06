<?php

namespace App\Livewire\Sales;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    /**
     * Xử lý đăng xuất tài khoản an toàn
     */
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
        return view('livewire.sales.sidebar', [
            'user' => Auth::guard('staff')->user(),
        ]);
    }
}