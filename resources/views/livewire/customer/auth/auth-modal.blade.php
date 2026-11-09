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
                    <div class="w-12 h-12 rounded-2xl {{ $tab === 'reset_password' && $tokenInvalid ? 'bg-rose-50 text-rose-500' : 'bg-brand-50 text-brand-600' }} flex items-center justify-center text-xl mx-auto mb-3 shadow-inner">
                        @if ($tab === 'reset_password' && $tokenInvalid)
                            <i class="fa-solid fa-hourglass-end"></i>
                        @elseif (in_array($tab, ['forgot', 'reset_password']))
                            <i class="fa-solid fa-key"></i>
                        @else
                            <i class="fa-solid fa-seedling"></i>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                        @if ($tab === 'login')
                            Chào Mừng Bạn Đến Đất Lành
                        @elseif ($tab === 'register')
                            Tạo Tài Khoản Khách Hàng
                        @elseif ($tab === 'reset_password')
                            {{ $tokenInvalid ? 'Liên Kết Đã Hết Hạn' : 'Đặt Lại Mật Khẩu Mới' }}
                        @else
                            Khôi Phục Mật Khẩu
                        @endif
                    </h1>
                    <p class="text-xs text-gray-400 mt-1">
                        @if ($tab === 'login')
                            Cùng chăm sóc sức khỏe gia đình bằng nông sản sạch
                        @elseif ($tab === 'register')
                            Đăng ký tài khoản để mua sắm và nhận thông báo ưu đãi
                        @elseif ($tab === 'reset_password')
                            {{ $tokenInvalid ? 'Liên kết đặt lại mật khẩu không còn hiệu lực hoặc đã được sử dụng' : 'Nhập mật khẩu mới an toàn cho tài khoản của bạn' }}
                        @else
                            Nhập email tài khoản để nhận liên kết đặt lại mật khẩu
                        @endif
                    </p>
                </div>

                <!-- Tab Chuyển đổi ĐĂNG NHẬP / ĐĂNG KÝ -->
                @if (!in_array($tab, ['forgot', 'reset_password']))
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
                    <form wire:submit.prevent="handleLogin" autocomplete="off" class="space-y-4">
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
                                       autocomplete="off"
                                       data-lpignore="true"
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
                    <form wire:submit.prevent="handleRegister" autocomplete="off" class="space-y-4">
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
                                       autocomplete="off"
                                       data-lpignore="true"
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
                                       autocomplete="off"
                                       data-lpignore="true"
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

                <!-- ================= FORM 3: QUÊN MẬT KHẨU (PASSWORD RESET TOKEN) ================= -->
               @if ($tab === 'forgot')
                    @if ($forgotLinkSent)
                        <!-- Màn hình ĐÃ GỬI LIÊN KẾT THÀNH CÔNG -->
                        <div class="text-center py-4 space-y-4 animate-in fade-in duration-300">
                            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-2xl shadow-inner">
                                <i class="fa-solid fa-paper-plane"></i>
                            </div>

                            <div>
                                <h3 class="text-base font-extrabold text-gray-900">Đã gửi liên kết thành công!</h3>
                                <p class="text-xs text-gray-600 mt-2 leading-relaxed">
                                    Chúng tôi đã gửi đường dẫn đặt lại mật khẩu đến email:
                                    <br>
                                    <strong class="text-gray-900 font-bold">{{ $forgotAccount }}</strong>
                                </p>
                                <p class="text-[11px] text-gray-400 mt-2">
                                    Vui lòng kiểm tra hộp thư đến (kể cả mục Spam) và nhấn vào liên kết để tạo mật khẩu mới. Liên kết có hiệu lực trong 60 phút.
                                </p>
                            </div>

                            <div class="pt-2 space-y-2">
                                <button type="button" 
                                        wire:click="$set('forgotLinkSent', false)" 
                                        class="text-xs font-bold text-brand-600 hover:underline">
                                    Gửi lại hoặc nhập email khác
                                </button>
                                <div>
                                    <button type="button" 
                                            wire:click="switchTab('login')" 
                                            class="w-full inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 rounded-xl text-xs transition">
                                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                        <span>Quay lại Đăng nhập</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- FORM NHẬP EMAIL ĐỂ GỬI LINK -->
                        <form wire:submit.prevent="handleSendResetLink" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Email tài khoản</label>
                                <input type="email" wire:model.blur="forgotAccount" placeholder="nguyenvana@gmail.com"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-brand-500 focus:ring-0">
                                @error('forgotAccount') <span class="text-red-500 text-xs font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" wire:loading.attr="disabled"
                                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2 disabled:opacity-50">
                                <span wire:loading.remove wire:target="handleSendResetLink">Gửi liên kết đặt lại mật khẩu</span>
                                <span wire:loading wire:target="handleSendResetLink"><i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...</span>
                            </button>

                            <div class="text-center pt-2">
                                <button type="button" wire:click="switchTab('login')" class="text-xs font-bold text-gray-500 hover:text-brand-600 transition">
                                    Nhớ mật khẩu rồi? <span class="text-brand-600 underline">Đăng nhập ngay</span>
                                </button>
                            </div>
                        </form>
                    @endif
               @endif

                <!-- ================= FORM 4: ĐẶT LẠI MẬT KHẨU (TỪ TOKEN EMAIL) ================= -->
                @if ($tab === 'reset_password')
                    @if ($tokenInvalid)
                        <!-- MÀN HÌNH BÁO LỖI TOKEN HẾT HẠN / ĐÃ DÙNG NGAY KHI MỞ -->
                        <div class="text-center py-2 space-y-4 animate-in fade-in duration-300">
                            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-left">
                                <div class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm shrink-0 mt-0.5"></i>
                                    <div class="text-xs text-rose-800 space-y-1">
                                        <p class="font-bold">Liên kết đã hết hạn hoặc đã được sử dụng</p>
                                        <p class="text-rose-700 leading-relaxed">
                                            Vì lý do an toàn, mỗi liên kết đặt lại mật khẩu chỉ có hiệu lực trong <strong>60 phút</strong> và chỉ dùng được <strong>1 lần duy nhất</strong>.
                                        </p>
                                        @if ($resetEmail)
                                            <p class="text-gray-600 text-[11px] pt-1.5 border-t border-rose-200/60 mt-1.5">
                                                Tài khoản: <strong class="text-gray-900">{{ $resetEmail }}</strong>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 space-y-2.5">
                                <button type="button" 
                                        wire:click="retryForgotPassword" 
                                        class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-arrow-rotate-right text-xs"></i>
                                    <span>Gửi Lại Liên Kết Mới</span>
                                </button>

                                <button type="button" 
                                        wire:click="switchTab('login')" 
                                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 rounded-2xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                    <span>Quay lại Đăng nhập</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- FORM NHẬP MẬT KHẨU MỚI (Khi token còn hạn và hợp lệ) -->
                        <form wire:submit.prevent="handleResetPassword" autocomplete="off" class="space-y-4">
                            <!-- Email (readonly) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Email tài khoản</label>
                                <div class="relative">
                                    <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="email" 
                                           wire:model="resetEmail" 
                                           readonly
                                           class="w-full bg-gray-100 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-semibold text-gray-600 outline-none cursor-not-allowed">
                                </div>
                                @error('resetEmail') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Mật khẩu mới -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Mật khẩu mới</label>
                                <div class="relative">
                                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="{{ $showNewPassword ? 'text' : 'password' }}" 
                                           wire:model="newPassword" 
                                           autocomplete="off"
                                           data-lpignore="true"
                                           placeholder="Tối thiểu 8 ký tự (hoa, thường, số)" 
                                           class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                    <button type="button" wire:click="$toggle('showNewPassword')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fa-regular {{ $showNewPassword ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                    </button>
                                </div>
                                @error('newPassword') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Xác nhận mật khẩu mới -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Xác nhận mật khẩu mới</label>
                                <div class="relative">
                                    <i class="fa-solid fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="{{ $showNewPasswordConfirmation ? 'text' : 'password' }}" 
                                           wire:model="newPasswordConfirmation" 
                                           autocomplete="off"
                                           data-lpignore="true"
                                           placeholder="Nhập lại mật khẩu mới" 
                                           class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                    <button type="button" wire:click="$toggle('showNewPasswordConfirmation')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fa-regular {{ $showNewPasswordConfirmation ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                    </button>
                                </div>
                                @error('newPasswordConfirmation') <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nút Lưu mật khẩu -->
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer mt-2">
                                <span wire:loading.remove wire:target="handleResetPassword">Lưu Mật Khẩu Mới</span>
                                <span wire:loading wire:target="handleResetPassword"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang lưu mật khẩu...</span>
                            </button>

                            <div class="text-center pt-2">
                                <button type="button" wire:click="switchTab('login')" class="text-xs font-bold text-gray-500 hover:text-brand-600 transition">
                                    <i class="fa-solid fa-arrow-left text-[10px] mr-1"></i> Quay lại Đăng nhập
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