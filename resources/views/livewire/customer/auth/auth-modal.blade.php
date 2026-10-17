<div>
    @if ($isOpen)
        <!-- BACKDROP OVERLAY -->
        <div class="fixed inset-0 z-[100] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6 overflow-y-auto py-8 sm:py-12"
             wire:click.self="closeModal"
             x-data
             x-transition.opacity>
            
            <!-- POPUP CARD CONTAINER: Đã thêm cờ ẩn scrollbar ([scrollbar-width:none] [&::-webkit-scrollbar]:hidden) -->
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
                    <form wire:submit.prevent="$refresh" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Số điện thoại hoặc Email</label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="text" placeholder="thanhtruc@example.com hoặc 0912..." required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-gray-700">Mật khẩu</label>
                                <button type="button" wire:click="switchTab('forgot')" class="text-xs font-semibold text-brand-600 hover:underline">Quên?</button>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="{{ $showLoginPassword ? 'text' : 'password' }}" placeholder="Nhập mật khẩu" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                <button type="button" wire:click="$toggle('showLoginPassword')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fa-regular {{ $showLoginPassword ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500 pt-0.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" checked class="rounded text-brand-600 focus:ring-brand-500">
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span>Đăng nhập</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </form>
                @endif

                <!-- ================= FORM 2: ĐĂNG KÝ ================= -->
                @if ($tab === 'register')
                    <form wire:submit.prevent="$refresh" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Họ và tên của bạn</label>
                            <div class="relative">
                                <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="text" placeholder="Ví dụ: Lê Thanh Trúc" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Số điện thoại</label>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="tel" placeholder="0912 345 678" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Địa chỉ Email (Nhận thông báo)</label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="email" placeholder="thanhtruc@example.com" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Mật khẩu</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="{{ $showRegisterPassword ? 'text' : 'password' }}" placeholder="Tối thiểu 8 ký tự" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                <button type="button" wire:click="$toggle('showRegisterPassword')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fa-regular {{ $showRegisterPassword ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Xác nhận mật khẩu</label>
                            <div class="relative">
                                <i class="fa-solid fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="{{ $showConfirmPassword ? 'text' : 'password' }}" placeholder="Nhập lại mật khẩu" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                <button type="button" wire:click="$toggle('showConfirmPassword')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fa-regular {{ $showConfirmPassword ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span>Tạo tài khoản khách hàng</span>
                            <i class="fa-solid fa-check text-xs"></i>
                        </button>
                    </form>
                @endif

                <!-- ================= FORM 3: QUÊN MẬT KHẨU ================= -->
                @if ($tab === 'forgot')
                    @if ($forgotStep === 1)
                        <form wire:submit.prevent="nextForgotStep" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Email hoặc Số điện thoại</label>
                                <div class="relative">
                                    <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="text" placeholder="thanhtruc@example.com hoặc 0912..." required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                                <span>Gửi mã xác thực</span>
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </button>

                            <div class="pt-2 text-center">
                                <button type="button" wire:click="switchTab('login')" class="text-xs font-bold text-gray-500 hover:text-brand-700 transition">
                                    Nhớ mật khẩu rồi? <span class="text-brand-600 underline">Đăng nhập ngay</span>
                                </button>
                            </div>
                        </form>
                    @elseif ($forgotStep === 2)
                        <form wire:submit.prevent="nextForgotStep" class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-gray-700">Mã xác thực (OTP)</label>
                                    <span class="text-[11px] font-semibold text-brand-600">Gửi lại sau 60s</span>
                                </div>
                                <div class="relative">
                                    <i class="fa-solid fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="text" maxlength="6" placeholder="Nhập 6 số" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-center tracking-widest font-mono text-base font-bold outline-none focus:bg-white focus:border-brand-500 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Mật khẩu mới</label>
                                <div class="relative">
                                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="{{ $showNewPassword ? 'text' : 'password' }}" placeholder="Tối thiểu 8 ký tự" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                    <button type="button" wire:click="$toggle('showNewPassword')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fa-regular {{ $showNewPassword ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Xác nhận mật khẩu mới</label>
                                <div class="relative">
                                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="{{ $showNewPassword ? 'text' : 'password' }}" placeholder="Nhập lại mật khẩu mới" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-check text-xs"></i>
                                <span>Xác nhận & Cập nhật mật khẩu</span>
                            </button>

                            <div class="pt-1 text-center">
                                <button type="button" wire:click="prevForgotStep" class="text-xs font-semibold text-gray-400 hover:text-gray-600">
                                    <i class="fa-solid fa-chevron-left text-[10px] mr-1"></i> Quay lại bước trước
                                </button>
                            </div>
                        </form>
                    @elseif ($forgotStep === 3)
                        <div class="space-y-5 text-center pt-2">
                            <div class="w-16 h-16 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center text-3xl mx-auto shadow-inner">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900">Đổi mật khẩu thành công!</h3>
                                <p class="text-xs text-gray-500 mt-1 max-w-xs mx-auto leading-relaxed">
                                    Mật khẩu tài khoản của bạn đã được cập nhật an toàn.
                                </p>
                            </div>
                            <button type="button" wire:click="switchTab('login')" class="inline-flex w-full items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-95">
                                <span>Đăng nhập ngay</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        </div>
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