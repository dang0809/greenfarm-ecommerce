<?php

namespace App\Livewire\Customer\Auth;

use Livewire\Attributes\On;
use Livewire\Component;

class AuthModal extends Component
{
    // Trạng thái ẩn/hiện Modal & Tab được chọn
    public bool $isOpen = false;
    public string $tab = 'login'; // 'login', 'register', 'forgot'

    // Bước của Form Quên mật khẩu (1: Nhập Email/SĐT, 2: Nhập OTP & Mật khẩu mới, 3: Thành công)
    public int $forgotStep = 1;

    // Trạng thái ẩn/hiện mật khẩu
    public bool $showLoginPassword = false;
    public bool $showRegisterPassword = false;
    public bool $showConfirmPassword = false;
    public bool $showNewPassword = false;

    // Lắng nghe sự kiện mở Modal
    #[On('open-auth-modal')]
    public function openModal(string $tab = 'login'): void
    {
        $this->tab = $tab;
        $this->forgotStep = 1;
        $this->isOpen = true;
    }

    // Đóng Modal
    public function closeModal(): void
    {
        $this->isOpen = false;
    }

    // Chuyển đổi giữa các Tab
    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
        $this->forgotStep = 1;
    }

    // Giả lập chuyển bước Quên mật khẩu để test UI
    public function nextForgotStep(): void
    {
        if ($this->forgotStep < 3) {
            $this->forgotStep++;
        }
    }

    public function prevForgotStep(): void
    {
        if ($this->forgotStep > 1) {
            $this->forgotStep--;
        }
    }

    public function render()
    {
        return view('livewire.customer.auth.auth-modal');
    }
}