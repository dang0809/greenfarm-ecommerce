<?php

namespace App\Livewire\Customer\Auth;

use App\Models\User;
use App\Services\VerificationEmailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
class AuthModal extends Component
{
    public bool $isOpen = false;
    public string $tab = 'register'; // 'login', 'register', 'forgot'

    // Hiển thị/ẩn mật khẩu
    public bool $showRegisterPassword = false;
    public bool $showConfirmPassword = false;
    public bool $showLoginPassword = false;
    public bool $showNewPassword = false;

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

    // Form Quên mật khẩu
    public int $forgotStep = 1;
    public string $forgotAccount = '';
    public string $forgotOtp = '';
    public string $forgotNewPassword = '';
    public string $forgotNewPasswordConfirmation = '';

    #[On('open-auth-modal')]
    public function openModal(string $tab = 'register'): void
    {
        $this->resetValidation();
        $this->tab = $tab;
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetValidation();
        $this->reset([
            'registerName', 'registerPhone', 'registerEmail', 'registerPassword', 'registerPasswordConfirmation',
            'loginAccount', 'loginPassword',
            'forgotAccount', 'forgotOtp', 'forgotNewPassword', 'forgotNewPasswordConfirmation', 'forgotStep'
        ]);
    }

    // CHUYỂN TAB ĐĂNG NHẬP / ĐĂNG KÝ / QUÊN MẬT KHẨU
    public function switchTab(string $tab): void
    {
        $this->resetValidation();
        $this->tab = $tab;
        if ($tab === 'forgot') {
            $this->forgotStep = 1;
        }
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
        $this->validate([
            'loginAccount' => 'required|string',
            'loginPassword' => 'required|string',
        ], [
    'loginAccount.required' => 'Vui lòng nhập số điện thoại hoặc email.',
    'loginAccount.string' => 'Tài khoản đăng nhập không đúng định dạng.',
    'loginPassword.required' => 'Vui lòng nhập mật khẩu.',
    'loginPassword.string' => 'Mật khẩu không đúng định dạng.',
]); 
        $throttleKey = Str::lower($this->loginAccount) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('loginAccount', "Thử lại sau $seconds giây.");
            return;
        }


        if (Auth::attempt(['email' => $this->loginAccount, 'password' => $this->loginPassword], (bool) $this->remember)) {
            $user = Auth::user();

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
        $this->addError('loginAccount', 'Thông tin đăng nhập không chính xác.');
    }
    public function prevForgotStep(): void
    {
        if ($this->forgotStep > 1) {
            $this->forgotStep--;
        }
    }

    // ================= 3. XỬ LÝ QUÊN MẬT KHẨU =================
    public int $resendCooldown = 60;
   // BƯỚC 1: Gửi mã OTP (Tối ưu UX nhập lại)
       public function handleSendResetCode(VerificationEmailService $emailService): void
        {
            $this->reset(['forgotOtp', 'forgotNewPassword', 'forgotNewPasswordConfirmation']);

            $this->validate([
                'forgotAccount' => 'required|email:rfc',
            ], [
                'forgotAccount.required' => 'Vui lòng nhập email.',
                'forgotAccount.email' => 'Email không đúng định dạng.',
            ]);

            $emailLower = Str::lower($this->forgotAccount);
            $verifyThrottleKey = 'forgot-otp-attempts:' . $emailLower;
            $limitThrottleKey = 'forgot-otp-limit:' . $emailLower;

            // 1. Kiểm tra khóa 300s nếu nhập sai quá 5 lần
            if (RateLimiter::tooManyAttempts($verifyThrottleKey, 5)) {
                $seconds = RateLimiter::availableIn($verifyThrottleKey);
                $this->addError('forgotAccount', "Tài khoản bị tạm khóa do nhập sai OTP quá 5 lần. Vui lòng thử lại sau {$seconds} giây.");
                return;
            }

            $existingOtp = Cache::get('password_reset_otp_' . $emailLower);

            // 2. NẾU DÙNG LẠI OTP CŨ: Lấy chính xác số giây CÒN LẠI từ RateLimiter (Ví dụ: 35s)
            if ($existingOtp && RateLimiter::tooManyAttempts($limitThrottleKey, 1)) {
                $this->resendCooldown = RateLimiter::availableIn($limitThrottleKey);
                $this->forgotStep = 2;
                return;
            }

            // 3. NẾU GỬI MÃ MỚI: Đặt đếm ngược từ tròn 60s
            $user = User::where('email', $this->forgotAccount)->first();

            if ($user) {
                $otp = (string) rand(100000, 999999);
                Cache::put('password_reset_otp_' . $emailLower, Hash::make($otp), 600);
                RateLimiter::hit($limitThrottleKey, 60);
                $emailService->sendResetOtp($this->forgotAccount, $otp);
            }

            $this->resendCooldown = 60;
            $this->forgotStep = 2;
        }
        // BƯỚC 2: Xác nhận OTP (Chống Brute-force nhập sai quá 5 lần)
        public function handleVerifyOtp(): void
        {
            $this->validate([
                'forgotOtp' => 'required|string|size:6',
            ], [
                'forgotOtp.required' => 'Vui lòng nhập mã OTP.',
                'forgotOtp.size' => 'Mã OTP phải gồm 6 chữ số.',
            ]);

            // BẢO MẬT 2: Giới hạn tối đa 5 lần nhập sai OTP (Khóa 5 phút)
            $verifyThrottleKey = 'forgot-otp-attempts:' . Str::lower($this->forgotAccount);
            if (RateLimiter::tooManyAttempts($verifyThrottleKey, 5)) {
                $seconds = RateLimiter::availableIn($verifyThrottleKey);
                $this->addError('forgotOtp', "Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau {$seconds} giây.");
                return;
            }

            $hashedOtp = Cache::get('password_reset_otp_' . Str::lower($this->forgotAccount));

            // BẢO MẬT 3: Kiểm tra bằng Hash::check
            if (!$hashedOtp || !Hash::check(trim($this->forgotOtp), $hashedOtp)) {
                RateLimiter::hit($verifyThrottleKey, 300); // Ghi nhận 1 lần nhập sai
                $this->addError('forgotOtp', 'Mã OTP không chính xác hoặc đã hết hạn.');
                return;
            }

            // Nhập đúng -> Xóa bộ đếm nhập sai
            RateLimiter::clear($verifyThrottleKey);
            $this->forgotStep = 3;
        }

        // BƯỚC 3: Đặt mật khẩu mới (Thu hồi tất cả thiết bị đang đăng nhập)
        public function handleResetPassword(): void
        {
            $this->validate([
                'forgotNewPassword' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'forgotNewPasswordConfirmation' => 'required|same:forgotNewPassword',
            ], [
                'forgotNewPassword.required' => 'Vui lòng nhập mật khẩu mới.',
                'forgotNewPassword.min' => 'Mật khẩu phải từ 8 ký tự trở lên.',
                'forgotNewPassword.regex' => 'Mật khẩu phải chứa chữ hoa, chữ thường và chữ số.',
                'forgotNewPasswordConfirmation.required' => 'Vui lòng xác nhận mật khẩu mới.',
                'forgotNewPasswordConfirmation.same' => 'Xác nhận mật khẩu không khớp.',
            ]);

            $hashedOtp = Cache::get('password_reset_otp_' . Str::lower($this->forgotAccount));

            if (!$hashedOtp || !Hash::check(trim($this->forgotOtp), $hashedOtp)) {
                $this->addError('forgotOtp', 'Mã OTP không chính xác hoặc đã hết hạn.');
                $this->forgotStep = 2;
                return;
            }

            $user = User::where('email', $this->forgotAccount)->first();

            if ($user) {
                // BẢO MẬT 6: Cập nhật mật khẩu + Reset remember_token để đăng xuất các thiết bị ghi nhớ
                $user->update([
                    'password' => Hash::make($this->forgotNewPassword),
                    'remember_token' => Str::random(60),
                ]);

                // BẢO MẬT 6: Xóa toàn bộ Session trong Database (Nêu dự án dùng SESSION_DRIVER=database)
                if (config('session.driver') === 'database') {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                }

                // BẢO MẬT 4: Vô hiệu hóa OTP trong Cache ngay lập tức
                Cache::forget('password_reset_otp_' . Str::lower($this->forgotAccount));

                // Dọn dẹp dữ liệu & Chuyển về Tab Đăng nhập
                $this->reset(['forgotAccount', 'forgotOtp', 'forgotNewPassword', 'forgotNewPasswordConfirmation', 'forgotStep']);
                $this->tab = 'login';

                session()->flash('success', 'Đổi mật khẩu thành công! Các phiên đăng nhập cũ đã bị đăng xuất.');
            }
        }

    public function render()
    {
        return view('livewire.customer.auth.auth-modal');
    }
}