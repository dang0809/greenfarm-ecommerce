<?php

namespace App\Livewire\Customer\Auth;

use App\Models\User;
use App\Services\VerificationEmailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
class AuthModal extends Component
{
    public bool $isOpen = false;
    public string $tab = 'register'; // 'login', 'register', 'forgot', 'reset_password'

    // Hiển thị/ẩn mật khẩu
    public bool $showRegisterPassword = false;
    public bool $showConfirmPassword = false;
    public bool $showLoginPassword = false;
    public bool $showNewPassword = false;
    public bool $showNewPasswordConfirmation = false;

    // Form Đăng ký
    public string $registerName = '';
    public string $registerPhone = '';
    public string $registerEmail = '';
    public string $registerPassword = '';
    public string $registerPasswordConfirmation = '';

    // Form Đăng nhập
    public string $loginAccount = '';
    public string $loginPassword = '';
    public bool $remember = true;

    // Form Quên mật khẩu (Password Reset Token)
    public string $forgotAccount = '';
    public bool $forgotLinkSent = false;

    // Form Đặt lại mật khẩu (Khi bấm link từ Email)
    public string $resetToken = '';
    public string $resetEmail = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public bool $tokenInvalid = false;

    #[On('open-auth-modal')]
    public function openModal(string $tab = 'register'): void
    {
        $this->resetValidation();
        $this->tab = $tab;
        $this->isOpen = true;
    }

    public function mount()
    {
        // 1. Nếu có token đặt lại mật khẩu từ URL / email
        if (request()->has('reset_token')) {
            $this->resetToken = (string) request()->query('reset_token');
            $this->resetEmail = (string) request()->query('email', '');
            $this->tab = 'reset_password';
            $this->isOpen = true;

            // KIỂM TRA NGAY TỨC THÌ: Token có tồn tại trong database và còn hạn (60 phút) không?
            $user = User::where('email', $this->resetEmail)->first();
            if (! $user || ! Password::broker()->tokenExists($user, $this->resetToken)) {
                $this->tokenInvalid = true;
            } else {
                $this->tokenInvalid = false;
            }

            return;
        }

        // 2. Nếu có cờ mở tab quên mật khẩu
        if (request()->has('open_forgot')) {
            $this->tab = 'forgot';
            $this->isOpen = true;
            return;
        }

        // 3. Nếu có cờ Session từ Middleware, tự động bật Modal ngay khi trang vừa tải
        if (session()->has('open_auth_modal')) {
            $this->isOpen = true;
            $this->tab = 'login';
        }
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetValidation();
        $this->reset([
            'registerName', 'registerPhone', 'registerEmail', 'registerPassword', 'registerPasswordConfirmation',
            'loginAccount', 'loginPassword',
            'forgotAccount', 'forgotLinkSent',
            'resetToken', 'resetEmail', 'newPassword', 'newPasswordConfirmation',
            'tokenInvalid'
        ]);
    }

    // CHUYỂN TAB ĐĂNG NHẬP / ĐĂNG KÝ / QUÊN MẬT KHẨU
    public function switchTab(string $tab): void
    {
        $this->resetValidation();
        $this->tab = $tab;
        if ($tab === 'forgot') {
            $this->forgotLinkSent = false;
        }
        if ($tab !== 'reset_password') {
            $this->tokenInvalid = false;
        }
    }

    // Khi link hết hạn, bấm nút này để tự điền lại email và quay về tab Quên mật khẩu
    public function retryForgotPassword(): void
    {
        $email = $this->resetEmail;
        $this->switchTab('forgot');
        $this->forgotAccount = $email;
    }

    // ================= 1. XỬ LÝ ĐĂNG KÝ =================
    public function handleRegister(VerificationEmailService $emailService): void
    {
        // Đã tối ưu bỏ :dns để nút đăng ký chạy tức thì
        $validated = $this->validate([
            'registerName' => ['required', 'string', 'max:255'],
            'registerPhone' => ['required', 'string', 'regex:/^(0[35789])[0-9]{8}$/'],
            'registerEmail' => ['required', 'string', 'email:rfc', 'max:255'],
            'registerPassword' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'registerPasswordConfirmation' => ['required', 'same:registerPassword'],
        ], [
            'registerName.required' => 'Vui lòng nhập họ và tên.',
            'registerPhone.required' => 'Vui lòng nhập số điện thoại.',
            'registerPhone.regex' => 'Số điện thoại không đúng định dạng (VD: 0912345678).',
            'registerEmail.required' => 'Vui lòng nhập địa chỉ Email.',
            'registerEmail.email' => 'Địa chỉ Email không đúng định dạng.',
            'registerPassword.required' => 'Vui lòng nhập mật khẩu.',
            'registerPassword.min' => 'Mật khẩu phải từ 8 ký tự trở lên.',
            'registerPassword.regex' => 'Mật khẩu phải gồm chữ hoa, chữ thường và chữ số.',
            'registerPasswordConfirmation.required' => 'Vui lòng xác nhận mật khẩu.',
            'registerPasswordConfirmation.same' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $email = strtolower(trim($validated['registerEmail']));
        $phone = trim($validated['registerPhone']);

        $existingUser = User::where('email', $email)->first();
        $existingPhone = User::where('phone', $phone)->first();

        if ($existingPhone && (!$existingUser || $existingPhone->id !== $existingUser->id)) {
            $this->addError('registerPhone', 'Số điện thoại này đã được sử dụng.');
            return;
        }

        if ($existingUser) {
            if ($existingUser->status === 'active') {
                $this->addError('registerEmail', 'Email này đã được đăng ký và kích hoạt. Vui lòng chuyển sang Đăng nhập.');
                return;
            }

            $existingUser->update([
                'name' => trim($validated['registerName']),
                'phone' => $phone,
                'password' => Hash::make($validated['registerPassword']),
            ]);

            $user = $existingUser;
        } else {
            $user = User::create([
                'name' => trim($validated['registerName']),
                'phone' => $phone,
                'email' => $email,
                'password' => Hash::make($validated['registerPassword']),
                'role' => 'customer',
                'status' => 'inactive',
            ]);
        }

        $isSent = $emailService->send($user);

        $this->closeModal();

        if ($isSent) {
            session()->flash('success', 'Đã gửi Email xác thực mới đến ' . $user->email . '. Vui lòng kiểm tra hộp thư!');
        } else {
            session()->flash('error', 'Không thể gửi email xác nhận. Vui lòng thử lại sau.');
        }

        $this->redirect(route('home'), navigate: true);
    }

    // ================= 2. XỬ LÝ ĐĂNG NHẬP =================
    public function handleLogin(): void
    {
        // Chuẩn hóa email tài khoản
        $this->loginAccount = strtolower(trim($this->loginAccount));

        $this->validate([
            'loginAccount'  => 'required|email:rfc',
            'loginPassword' => 'required|string',
        ], [
            'loginAccount.required'  => 'Vui lòng nhập địa chỉ email.',
            'loginAccount.email'     => 'Địa chỉ email không đúng định dạng.',
            'loginPassword.required' => 'Vui lòng nhập mật khẩu.',
            'loginPassword.string'   => 'Mật khẩu không đúng định dạng.',
        ]); 

        $throttleKey = Str::lower($this->loginAccount) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('loginAccount', "Thử lại sau $seconds giây.");
            return;
        }

        if (Auth::attempt(['email' => $this->loginAccount, 'password' => $this->loginPassword], (bool) $this->remember)) {
            $user = Auth::user();

            // Chặn tài khoản quản trị / nhân viên nội bộ đăng nhập ở form khách hàng (báo lỗi chung để bảo mật)
            if ($user->role !== 'customer') {
                Auth::logout();
                RateLimiter::hit($throttleKey, 60);
                $this->addError('loginAccount', 'Email hoặc mật khẩu không chính xác.');
                return;
            }

            if ($user->status !== 'active' || !$user->hasVerifiedEmail()) {
                Auth::logout();
                RateLimiter::hit($throttleKey, 60);
                $this->addError('loginAccount', 'Tài khoản chưa kích hoạt! Vui lòng kiểm tra email để kích hoạt.');
                return;
            }

            RateLimiter::clear($throttleKey);
            session()->regenerate();

            $this->closeModal();
            $this->dispatch('user-authenticated');
            session()->flash('success', 'Đăng nhập thành công!');

            $this->redirectIntended(default: route('home'));
            return;
        }

        RateLimiter::hit($throttleKey, 60);
        $this->addError('loginAccount', 'Email hoặc mật khẩu không chính xác.');
    }
    // ================= 3. XỬ LÝ QUÊN MẬT KHẨU (PASSWORD RESET TOKEN) =================
    public function handleSendResetLink(VerificationEmailService $emailService): void
    {
        $this->forgotAccount = strtolower(trim($this->forgotAccount));

        $this->validate([
            'forgotAccount' => 'required|email:rfc',
        ], [
            'forgotAccount.required' => 'Vui lòng nhập địa chỉ email.',
            'forgotAccount.email' => 'Địa chỉ email không đúng định dạng.',
        ]);

        $emailLower = Str::lower($this->forgotAccount);
        $throttleKey = 'forgot-link:' . $emailLower;

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('forgotAccount', "Bạn vừa gửi yêu cầu rồi. Vui lòng đợi {$seconds} giây trước khi gửi lại.");
            return;
        }

        $user = User::where('email', $this->forgotAccount)->first();

        if ($user) {
            $token = Password::broker()->createToken($user);
            $emailService->sendResetLink($user->email, $token);
        }

        RateLimiter::hit($throttleKey, 60);
        $this->forgotLinkSent = true;
    }

    // ================= 4. ĐẶT LẠI MẬT KHẨU BẰNG TOKEN =================
    public function handleResetPassword(): void
    {
        $this->resetEmail = strtolower(trim($this->resetEmail));

        $this->validate([
            'resetEmail' => ['required', 'email:rfc'],
            'newPassword' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            'newPasswordConfirmation' => ['required', 'same:newPassword'],
        ], [
            'resetEmail.required' => 'Vui lòng nhập email.',
            'resetEmail.email' => 'Email không đúng định dạng.',
            'newPassword.required' => 'Vui lòng nhập mật khẩu mới.',
            'newPassword.min' => 'Mật khẩu phải từ 8 ký tự trở lên.',
            'newPassword.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường và 1 chữ số.',
            'newPasswordConfirmation.required' => 'Vui lòng xác nhận mật khẩu mới.',
            'newPasswordConfirmation.same' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $status = Password::broker()->reset(
            [
                'token' => $this->resetToken,
                'email' => $this->resetEmail,
                'password' => $this->newPassword,
                'password_confirmation' => $this->newPasswordConfirmation,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                if (config('session.driver') === 'database') {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                }
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $email = $this->resetEmail;
            $this->reset(['resetToken', 'resetEmail', 'newPassword', 'newPasswordConfirmation']);
            $this->loginAccount = $email;
            $this->tab = 'login';
            session()->flash('success', 'Mật khẩu đã được cập nhật thành công! Vui lòng đăng nhập bằng mật khẩu mới.');
            return;
        }

        if ($status === Password::INVALID_TOKEN) {
            $this->tokenInvalid = true;
            return;
        }

        if ($status === Password::INVALID_USER) {
            $this->addError('resetEmail', 'Không tìm thấy tài khoản với địa chỉ email này.');
            return;
        }

        $this->addError('resetEmail', 'Không thể đặt lại mật khẩu. Vui lòng thử lại sau.');
    }

    public function render()
    {
        return view('livewire.customer.auth.auth-modal');
    }
}