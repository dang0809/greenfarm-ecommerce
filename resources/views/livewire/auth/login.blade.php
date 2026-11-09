<div class="w-full">
    <!-- Card Đăng nhập Quản Trị -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-soft relative overflow-hidden">
        
        <!-- Header Logo & Tiêu đề -->
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group mb-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-700 text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-leaf text-brand-200 text-2xl"></i>
                </div>
                <div class="text-left">
                    <span class="font-black text-2xl text-brand-800 tracking-tight block leading-tight">ĐẤT LÀNH</span>
                    <span class="text-[11px] font-bold text-gray-400 tracking-wider uppercase block">Cổng quản trị nội bộ</span>
                </div>
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">Đăng Nhập Quản Trị</h1>
        </div>

        <!-- Khối Thông Báo Lỗi Toàn Cục / Session Flash -->
        @if (session()->has('error'))
            <div class="p-3.5 mb-5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-2.5 animate-in fade-in duration-200">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Form Đăng nhập -->
        <form wire:submit.prevent="handleLogin" autocomplete="off" class="space-y-4">
            <!-- Email -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Email tài khoản</label>
                <div class="relative">
                    <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="email" 
                           wire:model="email" 
                           placeholder="staff@datlanhfarm.vn" 
                           autocomplete="username"
                           class="w-full bg-gray-50 border @error('email') border-rose-400 bg-rose-50/10 focus:border-rose-500 @else border-gray-200 focus:border-brand-500 @enderror rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:ring-2 focus:ring-brand-100 transition-all">
                </div>
                @error('email') 
                    <span class="text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-triangle-exclamation text-[11px]"></i>
                        <span>{{ $message }}</span>
                    </span> 
                @enderror
            </div>

            <!-- Mật khẩu -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-gray-700">Mật khẩu</label>
                </div>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="{{ $showPassword ? 'text' : 'password' }}" 
                           wire:model="password" 
                           autocomplete="off"
                           data-lpignore="true"
                           placeholder="••••••••"  
                           class="w-full bg-gray-50 border @error('password') border-rose-400 bg-rose-50/10 focus:border-rose-500 @else border-gray-200 focus:border-brand-500 @enderror rounded-2xl pl-11 pr-11 py-3 text-xs sm:text-sm font-medium outline-none focus:bg-white focus:ring-2 focus:ring-brand-100 transition-all">
                    <button type="button" wire:click="$toggle('showPassword')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition p-1">
                        <i class="fa-regular {{ $showPassword ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                    </button>
                </div>
                @error('password') 
                    <span class="text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-triangle-exclamation text-[11px]"></i>
                        <span>{{ $message }}</span>
                    </span> 
                @enderror
            </div>

            <!-- Ghi nhớ đăng nhập & Quên mật khẩu -->
            <div class="flex items-center justify-between text-xs text-gray-500 pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 accent-brand-600">
                    <span class="font-medium text-gray-600">Ghi nhớ đăng nhập</span>
                </label>
                <a href="{{ route('home', ['open_forgot' => 1]) }}" 
                   wire:navigate
                   class="font-semibold text-brand-700 hover:text-brand-800 hover:underline transition">
                    Quên mật khẩu?
                </a>
            </div>

            <!-- Nút Đăng nhập -->
            <button type="submit" 
                    wire:loading.attr="disabled"
                    class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer mt-2">
                <span wire:loading.remove wire:target="handleLogin">Đăng Nhập Quản Trị</span>
                <span wire:loading wire:target="handleLogin"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang xác thực...</span>
                <i wire:loading.remove wire:target="handleLogin" class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

      
    </div>
</div>
