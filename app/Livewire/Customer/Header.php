<?php

namespace App\Livewire\Customer;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Header extends Component
{
    public int $cartCount = 0;

    // Bắt sự kiện đăng nhập thành công để tự động làm mới giao diện Header
    protected $listeners = ['user-authenticated' => '$refresh'];

    public function logout(): void
    {
        Auth::logout();

        // Hủy session hiện tại và tạo CSRF token mới
        session()->invalidate();
        session()->regenerateToken();

        // Bắn thông báo Toast thành công
        session()->flash('success', 'Đã đăng xuất tài khoản thành công!');

        // Điều hướng SPA về trang chủ
        $this->redirect(route('home'));
    }
    public function render()
    {
        $user = Auth::user();
        
        return view('livewire.customer.header', [
            'user' => $user,
        ]);
    }

    // Component này KHÔNG có @push('styles')
}