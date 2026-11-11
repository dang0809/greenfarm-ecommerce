<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-100 flex flex-col justify-between shrink-0 shadow-soft transition-transform duration-300 -translate-x-full lg:translate-x-0 lg:static">
    <div>
        <!-- Logo -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-gray-100">
            <a href="{{ route('farm-staff.farm-plot') }}" wire:navigate class="flex items-center gap-2.5">
                <div
                    class="w-10 h-10 rounded-xl bg-brand-700 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fa-solid fa-leaf text-brand-200 text-xl"></i>
                </div>
                <div>
                    <span class="font-extrabold text-xl text-brand-800 tracking-tight block leading-tight">ĐẤT
                        LÀNH</span>
                    <span
                        class="text-[10px] font-bold text-brand-700 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded uppercase tracking-wider">Farm
                        Staff</span>
                </div>
            </a>
            <button type="button" onclick="toggleSidebar()"
                class="lg:hidden p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Menu Điều Hướng -->
        <nav class="px-3.5 space-y-1.5 mt-6">
            <a href="{{ route('farm-staff.farm-plot') }}" wire:navigate
                class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('farm-staff.farm-plot') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-map-location-dot w-5 text-center"></i>
                <span>Sơ Đồ Luống Đất</span>
            </a>
            <a href="{{ route('farm-staff.product-batch') }}" wire:navigate
                class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('farm-staff.product-batch') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                <span>Quản Lý Lô Nông Sản</span>
            </a>
            <a href="{{ route('farm-staff.certification') }}" wire:navigate
                class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('farm-staff.certification') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-file-shield w-5 text-center"></i>
                <span>Giấy Chứng Nhận</span>
            </a>
            <a href="{{ route('farm-staff.product-manager') }}" wire:navigate
                class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('farm-staff.product-manager') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-brands fa-pagelines w-5 text-center"></i>
                <span>Quản lý sản phẩm</span>
            </a>
            <a href="{{ route('farm-staff.category-manager') }}" wire:navigate
                class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('farm-staff.category-manager') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-layer-group w-5 text-center"></i>
                <span>Quản lý danh mục</span>
            </a>
            <a href="{{ route('farm-staff.profile') }}" wire:navigate
                class="w-full flex items-center space-x-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all {{ request()->routeIs('farm-staff.profile') ? 'menu-active' : 'menu-inactive' }} text-left">
                <i class="fa-solid fa-id-badge w-5 text-center"></i>
                <span>Hồ Sơ Cá Nhân</span>
            </a>
        </nav>
    </div>

    <!-- User Footer & Đăng xuất -->
    <div class="p-4 border-t border-gray-100">
        <a href="{{ route('farm-staff.profile') }}" wire:navigate
            class="flex items-center space-x-3 bg-brand-50/70 hover:bg-brand-100/70 p-3 rounded-2xl border border-brand-100 mb-3 transition-colors group cursor-pointer"
            title="Hồ sơ cá nhân">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="Ảnh đại diện"
                    class="w-9 h-9 rounded-full object-cover border border-brand-200">
            @else
                <div
                    class="w-9 h-9 shrink-0 rounded-full bg-brand-100 text-brand-800 flex items-center justify-center font-bold">
                    {{ mb_strtoupper(mb_substr($user->name ?? 'F', 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-800 truncate group-hover:text-brand-800">
                    {{ $user->name }}
                </p>

                <p class="text-xs text-brand-700 font-semibold truncate">
                    {{ $user->role === 'farm_staff' ? 'Nhân viên Nông trại' : $user->role }}
                </p>
            </div>
        </a>

        <!-- Nút Đăng xuất -->
        <button type="button" wire:click="logout" wire:loading.attr="disabled"
            class="flex items-center space-x-3 text-red-600 hover:bg-red-50 transition w-full px-4 py-2.5 rounded-xl font-bold text-xs cursor-pointer disabled:opacity-50">
            <i wire:loading.remove wire:target="logout" class="fa-solid fa-arrow-right-from-bracket"></i>
            <i wire:loading wire:target="logout" class="fa-solid fa-spinner fa-spin"></i>
            <span>Đăng xuất hệ thống</span>
        </button>
    </div>
</aside>
