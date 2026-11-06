<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Đăng Nhập Quản Trị | Nông Trại Đất Lành')]
class Login extends Component
{
    // Thông tin tài khoản đăng nhập
    public string $email = '';
    public string $password = '';
    public bool $remember = true;

    // Trạng thái ẩn/hiện mật khẩu
    public bool $showPassword = false;

    /**
     * Khởi tạo: nếu đã đăng nhập thì tự động điều hướng theo quyền
     */
    public function mount(): void
    {
        // Chỉ xét danh tính nội bộ.
        // Khách hàng đang đăng nhập ở guard web không ảnh hưởng.
        $user = Auth::guard('staff')->user();

        if ($user) {
            $this->redirect(
                $this->getDefaultRouteByRole($user)
            );
        }
    }

    /**
     * Xử lý xác thực đăng nhập người dùng nội bộ
     */
    public function handleLogin(): void
    {
        $this->email = Str::lower(trim($this->email));

        $this->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $key = 'login:staff:'
            .$this->email
            .'|'
            .request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            $this->addError(
                'email',
                "Vui lòng thử lại sau {$seconds} giây."
            );

            return;
        }

        $authenticated = Auth::guard('staff')->attempt([
            'email' => $this->email,
            'password' => $this->password,
            'role' => ['farm_staff', 'sales', 'admin'],
            'status' => 'active',
        ], (bool) $this->remember);

        if (! $authenticated) {
            RateLimiter::hit($key, 60);

            $this->addError(
                'email',
                'Thông tin đăng nhập không hợp lệ hoặc tài khoản không có quyền truy cập.'
            );

            return;
        }

        RateLimiter::clear($key);

        $this->reset('password');

        $user = Auth::guard('staff')->user();

        $this->redirect(
            $this->getDefaultRouteByRole($user)
        );
    }

    /**
     * Lấy route mặc định tương ứng theo vai trò
     */
    private function getDefaultRouteByRole($user): string
    {
        return match ($user->role) {
            'sales'      => route('sales.orders'),
            'farm_staff' => route('farm-staff.dashboard'),
            default      => route('home'),
        };
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}