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
    public function mount()
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Nếu tài khoản là Khách hàng -> Không được ở trang Quản trị, chuyển về Trang chủ
            if ($user->role === 'customer') {
                return $this->redirect(route('home'), navigate: true);
            }

            return $this->redirect($this->getDefaultRouteByRole($user), navigate: true);
        }
    }

    /**
     * Xử lý xác thực đăng nhập người dùng nội bộ
     */
    public function handleLogin()
    {
        // Chuẩn hóa email: cắt khoảng trắng thừa và chuyển chữ thường
        $this->email = strtolower(trim($this->email));

        // 1. Kiểm tra Validate dữ liệu đầu vào (Chỉ đăng nhập bằng Email)
        $this->validate([
            'email'    => 'required|email:rfc',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Vui lòng nhập địa chỉ email tài khoản.',
            'email.email'       => 'Địa chỉ email không đúng định dạng (VD: staff@datlanhfarm.vn).',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // 2. Chống Brute Force (Tối đa 5 lần thử / phút)
        $throttleKey = Str::lower($this->email) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Bạn đã nhập sai quá 5 lần. Vui lòng đợi {$seconds} giây để thử lại.");
            return;
        }

        // 3. Thực hiện đăng nhập bằng Email
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $user = Auth::user();

            // 3.1. Kiểm tra tài khoản có bị khóa không
            if ($user->status === 'blocked') {
                Auth::logout();
                RateLimiter::hit($throttleKey, 60);
                $this->addError('email', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ ban quản trị.');
                return;
            }

            // 3.2. KIỂM TRA PHÂN QUYỀN (Nếu không thuộc nội bộ thì báo sai thông tin như bình thường)
            if (!in_array($user->role, ['admin', 'sales', 'farm_staff'])) {
                Auth::logout();
                RateLimiter::hit($throttleKey, 60);
                $this->addError('email', 'Email hoặc mật khẩu không chính xác.');
                return;
            }

            // Đăng nhập thành công: xóa giới hạn Rate Limiter & làm mới Session
            RateLimiter::clear($throttleKey);
            session()->regenerate();

            // 3.3. Điều hướng thẳng về đúng trang của vai trò
            return $this->redirect($this->getDefaultRouteByRole($user), navigate: true);
        }

        // Đăng nhập thất bại: Tăng số lần thử và hiển thị thông báo lỗi
        RateLimiter::hit($throttleKey, 60);
        $this->addError('email', 'Email hoặc mật khẩu không chính xác.');
    }

    /**
     * Lấy route mặc định tương ứng theo vai trò
     */
    private function getDefaultRouteByRole($user): string
    {
        return match ($user->role) {
            'sales'      => route('sales.orders'),
            'farm_staff' => route('farm-staff.farm-plot'),
            default      => route('home'),
        };
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
