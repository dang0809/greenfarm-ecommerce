<div>
    @if ($isOpen)
        <!-- BACKDROP OVERLAY -->
            <div class="fixed inset-0 z-[100] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6 overflow-y-auto py-8 sm:py-12"
     x-data
     x-transition.opacity>
    
    <!-- POPUP CARD CONTAINER -->
    <div class="w-full max-w-md bg-white rounded-3xl border border-gray-100 shadow-2xl p-6 sm:p-8 relative my-auto max-h-[85vh] overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Nút X đóng Popup -->
        <button type="button" 
                wire:click="closeModal" 
                class="absolute top-5 right-5 z-10 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-800 flex items-center justify-center transition-colors">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>

                <!-- Header Logo Mầm Cây -->
                <div class="text-center mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl mx-auto mb-3 shadow-inner">
                        <i class="fa-solid {{ $tab === 'forgot' ? 'fa-key' : 'fa-seedling' }}"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                        @if ($tab === 'login')
                            Chào Mừng Bạn Đến Đất Lành
                        @elseif ($tab === 'register')
                            Tạo Tài Khoản Khách Hàng
                        @else
                            Khôi Phục Mật Khẩu
                        @endif
                    </h1>
                    <p class="text-xs text-gray-400 mt-1">
                        @if ($tab === 'login')
                            Cùng chăm sóc sức khỏe gia đình bằng nông sản sạch
                        @elseif ($tab === 'register')
                            Đăng ký tài khoản để mua sắm và nhận thông báo ưu đãi
                        @else
                            Nhập thông tin, Đất Lành sẽ gửi mã xác thực khôi phục tài khoản
                        @endif
                    </p>
                </div>

                <!-- Tab Chuyển đổi ĐĂNG NHẬP / ĐĂNG KÝ -->
                @if ($tab !== 'forgot')
                    <div class="flex p-1 bg-gray-100 rounded-2xl mb-6 text-xs font-bold">
                        <button type="button" 
                                wire:click="switchTab('login')" 
                                class="flex-1 py-2.5 rounded-xl transition-all {{ $tab === 'login' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                            Đăng nhập
                        </button>
                        <button type="button" 
                                wire:click="switchTab('register')" 
                                class="flex-1 py-2.5 rounded-xl transition-all {{ $tab === 'register' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                            Đăng ký mới
                        </button>
                    </div>
                @endif

                <!-- ================= FORM 1: ĐĂNG NHẬP ================= -->
                @if ($tab === 'login')
                    @if (session()->has('success'))
                        <div class="p-3.5 mb-4 text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-2 animate-in fade-in duration-200">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-sm shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    <form wire:submit.prevent="handleLogin" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5"> Email</label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="text" 
                                       wire:model="loginAccount" 
                                       placeholder="thanhtruc@example.com" 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                            </div>
                            @error('loginAccount') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-gray-700">Mật khẩu</label>
                                <button type="button" wire:click="switchTab('forgot')" class="text-xs font-semibold text-brand-600 hover:underline">Quên?</button>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="{{ $showLoginPassword ? 'text' : 'password' }}" 
                                       wire:model="loginPassword" 
                                       autocomplete="new-password"
                                       placeholder="Nhập mật khẩu" 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                <button type="button" wire:click="$toggle('showLoginPassword')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fa-regular {{ $showLoginPassword ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                </button>
                            </div>
                            @error('loginPassword') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500 pt-0.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" wire:model="remember" class="rounded text-brand-600 focus:ring-brand-500">
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                        </div>

                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="handleLogin">Đăng nhập</span>
                            <span wire:loading wire:target="handleLogin"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang xác thực...</span>
                            <i wire:loading.remove wire:target="handleLogin" class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </form>
                @endif

                <!-- ================= FORM 2: ĐĂNG KÝ (RESEND MAIL INTEGRATED) ================= -->
                @if ($tab === 'register')
                    <form wire:submit.prevent="handleRegister" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Họ và tên của bạn <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="text" 
                                       wire:model="registerName" 
                                       placeholder="Ví dụ: Lê Thanh Trúc" 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                            </div>
                            @error('registerName') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Số điện thoại <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="tel" 
                                       wire:model="registerPhone" 
                                       placeholder="0912345678" 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                            </div>
                            @error('registerPhone') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Địa chỉ Email (Nhận thông báo) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="email" 
                                       wire:model="registerEmail" 
                                       placeholder="thanhtruc@example.com" 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                            </div>
                            @error('registerEmail') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Mật khẩu <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="{{ $showRegisterPassword ? 'text' : 'password' }}" 
                                       wire:model="registerPassword" 
                                       placeholder="Chữ hoa, chữ thường & chữ số (Min 8)" 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                <button type="button" wire:click="$toggle('showRegisterPassword')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fa-regular {{ $showRegisterPassword ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                </button>
                            </div>
                            @error('registerPassword') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Xác nhận mật khẩu <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-solid fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="{{ $showConfirmPassword ? 'text' : 'password' }}" 
                                       wire:model="registerPasswordConfirmation" 
                                       autocomplete="new-password"
                                       placeholder="Nhập lại mật khẩu" 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                <button type="button" wire:click="$toggle('showConfirmPassword')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fa-regular {{ $showConfirmPassword ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                </button>
                            </div>
                            @error('registerPasswordConfirmation') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="handleRegister">Tạo tài khoản khách hàng</span>
                            <span wire:loading wire:target="handleRegister"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang tạo & gửi Mail...</span>
                            <i wire:loading.remove wire:target="handleRegister" class="fa-solid fa-check text-xs"></i>
                        </button>
                    </form>
                @endif

                <!-- ================= FORM 3: QUÊN MẬT KHẨU ================= -->
               @if ($tab === 'forgot')
                <!-- BƯỚC 1: NHẬP EMAIL -->
                @if ($forgotStep === 1)
                    <form wire:submit.prevent="handleSendResetCode" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Email tài khoản</label>
                            <input type="email" wire:model.blur="forgotAccount" placeholder="nguyenvana@gmail.com"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-brand-500 focus:ring-0">
                            @error('forgotAccount') <span class="text-red-500 text-xs font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                                class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="handleSendResetCode">Gửi mã OTP</span>
                            <span wire:loading wire:target="handleSendResetCode"><i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...</span>
                        </button>

                        <div class="text-center pt-2">
                            <button type="button" wire:click="switchTab('login')" class="text-xs font-bold text-gray-500 hover:text-brand-600 transition">
                                Nhớ mật khẩu rồi? <span class="text-brand-600 underline">Đăng nhập ngay</span>
                            </button>
                        </div>
                    </form>

                <!-- BƯỚC 2: NHẬP MÃ OTP + ĐẾM NGƯỢC (CÓ NÚT LÙI VỀ BƯỚC 1) -->
                    @elseif ($forgotStep === 2)
                        <div x-data="{ 
                            cooldown: @entangle('resendCooldown'),
                            timer: null,
                            startTimer() {
                                if (this.timer) clearInterval(this.timer);
                                if (this.cooldown <= 0) return;

                                this.timer = setInterval(() => {
                                    if (this.cooldown > 0) {
                                        this.cooldown--;
                                    } else {
                                        clearInterval(this.timer);
                                        this.timer = null;
                                    }
                                }, 1000);
                            }
                        }" 
                        x-init="
                            startTimer();
                            $watch('cooldown', (value) => {
                                if (value > 0 && !timer) {
                                    startTimer();
                                }
                            });
                        ">
                            <form wire:submit.prevent="handleVerifyOtp" class="space-y-4">
                                <p class="text-xs text-gray-500">Mã OTP đã được gửi tới <strong class="text-gray-800">{{ $forgotAccount }}</strong></p>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Mã xác thực OTP (6 chữ số)</label>
                                    <input type="text" maxlength="6" wire:model.blur="forgotOtp" placeholder="123456" autocomplete="one-time-code"
                                        class="w-full px-4 py-2.5 text-center tracking-[8px] font-extrabold text-lg rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-0">
                                    @error('forgotOtp') <span class="text-red-500 text-xs font-semibold mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <button type="submit" wire:loading.attr="disabled"
                                        class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl text-sm transition disabled:opacity-50">
                                    Xác nhận OTP
                                </button>

                                <!-- HÀNG NÚT: ĐỔI EMAIL (LÙI) & GỬI LẠI OTP -->
                                <div class="flex items-center justify-between pt-2">
                                    <!-- NÚT LÙI LẠI BƯỚC 1 -->
                                    <button type="button" wire:click="prevForgotStep" class="text-xs font-semibold text-gray-500 hover:text-gray-800 transition flex items-center gap-1">
                                        <i class="fa-solid fa-arrow-left text-[10px]"></i> Đổi Email
                                    </button>

                                    <!-- NÚT GỬI LẠI OTP -->
                                    <button type="button" 
                                            @click="if(cooldown === 0) { $wire.handleSendResetCode(); }" 
                                            :disabled="cooldown > 0"
                                            wire:loading.attr="disabled" 
                                            class="text-xs font-semibold text-brand-600 hover:underline disabled:text-gray-400 disabled:no-underline disabled:cursor-not-allowed transition">
                                        <span wire:loading.remove wire:target="handleSendResetCode">
                                            <template x-if="cooldown > 0">
                                                <span>Gửi lại sau (<strong x-text="cooldown"></strong>s)</span>
                                            </template>
                                            <template x-if="cooldown === 0">
                                                <span>Gửi lại OTP</span>
                                            </template>
                                        </span>
                                        <span wire:loading wire:target="handleSendResetCode">
                                            <i class="fa-solid fa-spinner fa-spin"></i> Đang gửi lại...
                                        </span>
                                    </button>
                                </div>
                                @error('forgotAccount') <span class="text-red-500 text-xs font-semibold mt-1 block text-center">{{ $message }}</span> @enderror
                            </form>
                        </div>

                <!-- BƯỚC 3: ĐẶT MẬT KHẨU MỚI (CÓ NÚT LÙI VỀ BƯỚC 2) -->
                @elseif ($forgotStep === 3)
                    <form wire:submit.prevent="handleResetPassword" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Mật khẩu mới</label>
                            <input type="password" wire:model.blur="forgotNewPassword" placeholder="••••••••" autocomplete="new-password"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-brand-500 focus:ring-0">
                            @error('forgotNewPassword') <span class="text-red-500 text-xs font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                            <input type="password" wire:model.blur="forgotNewPasswordConfirmation" placeholder="••••••••" autocomplete="new-password"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-brand-500 focus:ring-0">
                            @error('forgotNewPasswordConfirmation') <span class="text-red-500 text-xs font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                                class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="handleResetPassword">Lưu mật khẩu mới</span>
                            <span wire:loading wire:target="handleResetPassword"><i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...</span>
                        </button>

                        <!-- NÚT LÙI LẠI BƯỚC 2 -->
                        <div class="text-center pt-1">
                            <button type="button" wire:click="prevForgotStep" class="text-xs font-semibold text-gray-400 hover:text-gray-600 transition flex items-center justify-center gap-1 mx-auto">
                                <i class="fa-solid fa-arrow-left text-[10px]"></i> Quay lại nhập OTP
                            </button>
                        </div>
                    </form>
                @endif
            @endif
                <!-- Chân Card -->
                <div class="mt-8 pt-4 border-t border-gray-100 text-center text-[11px] text-gray-400">
                    <i class="fa-solid fa-shield-halved text-brand-600 mr-1"></i> Hệ thống mã hóa bảo mật chuẩn Nông Trại Đất Lành
                </div>

            </div>
        </div>
    @endif
</div>