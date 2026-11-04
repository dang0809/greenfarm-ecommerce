<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-100 flex flex-col justify-between shrink-0 shadow-soft transition-transform duration-300 -translate-x-full lg:translate-x-0 lg:static">
    <div>
        <!-- Logo Portal -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-gray-100">
            <a href="{{ route('sales.orders') }}" wire:navigate class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-brand-700 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fa-solid fa-leaf text-brand-200 text-xl"></i>
                </div>
                <div>
                    <span class="font-extrabold text-xl text-brand-800 tracking-tight block leading-tight">ĐẤT LÀNH</span>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded uppercase tracking-wider">Sale</span>
                </div>
            </a>
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Menu Điều Hướng (Đúng 4 Tab Yêu Cầu) -->
        <nav class="px-3.5 space-y-1.5 mt-6">
            <!-- 1. Quản lý đơn hàng -->
            <a href="{{ route('sales.orders') }}" wire:navigate class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('sales.orders*') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                <span>Quản Lý Đơn Hàng</span>
            </a>

            <!-- 2. Xử lý khiếu nại -->
            <a href="{{ route('sales.complaints') }}" wire:navigate class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('sales.complaints*') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-shield-heart w-5 text-center"></i>
                <span>Xử Lý Khiếu Nại</span>
            </a>

            <!-- 3. Quản lý xả hàng -->
            <a href="{{ route('sales.clearance') }}" wire:navigate class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('sales.clearance*') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-tags w-5 text-center"></i>
                <span>Quản Lý Xả Hàng</span>
            </a>

            <!-- 4. Gói rau định kỳ VIP -->
            <a href="{{ route('sales.subscriptions') }}" wire:navigate class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('sales.subscriptions*') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-calendar-check w-5 text-center"></i>
                <span>Gói Rau Định Kỳ VIP</span>
            </a>
        </nav>
    </div>

    <!-- User Footer & Đăng xuất -->
    <div class="p-4 border-t border-gray-100">
        <div class="flex items-center space-x-3 bg-brand-50/70 p-3 rounded-2xl border border-brand-100 mb-3">
            <img src="{{ $user->avatar ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150' }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover border border-brand-200">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-800 truncate">{{ $user->name ?? 'Nhân viên Sales' }}</p>
                <p class="text-xs text-brand-700 font-semibold truncate">Nhân viên bán hàng</p>
            </div>
        </div>
        
        <!-- Nút Đăng xuất -->
        <button type="button" 
                wire:click="logout"
                wire:loading.attr="disabled"
                class="flex items-center space-x-3 text-red-600 hover:bg-red-50 transition w-full px-4 py-2.5 rounded-xl font-bold text-xs cursor-pointer disabled:opacity-50">
            <i wire:loading.remove wire:target="logout" class="fa-solid fa-arrow-right-from-bracket"></i>
            <i wire:loading wire:target="logout" class="fa-solid fa-spinner fa-spin"></i>
            <span>Đăng xuất hệ thống</span>
        </button>
    </div>
</aside>

