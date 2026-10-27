<header class="fixed w-full top-0 z-50 glass-header border-b border-gray-100 transition-all duration-300" 
        id="header"
        x-data="{ mobileMenuOpen: false, userMenuOpen: false, notifMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-2 group">
                <i class="fa-solid fa-leaf text-brand-600 text-2xl group-hover:text-brand-500 transition-colors"></i>
                <span class="font-extrabold text-2xl text-brand-800 tracking-tight">Đất Lành</span>
            </a>

            <!-- Desktop Navigation Menu -->
            <nav class="hidden lg:flex space-x-10">
                <a href="{{ route('home') }}" 
                   class="{{ request()->routeIs('home') ? 'text-brand-700 font-bold' : 'text-gray-600 hover:text-brand-600 font-medium' }} transition-colors">
                    Trang chủ
                </a>
                
                <a href="{{ route('products.index') }}" 
                   class="{{ request()->routeIs('products.index') ? 'text-brand-700 font-bold' : 'text-gray-600 hover:text-brand-600 font-medium' }} transition-colors">
                    Sản phẩm
                </a>
                
                <a href="{{ route('about') }}" 
                   class="{{ request()->routeIs('about') ? 'text-brand-700 font-bold' : 'text-gray-600 hover:text-brand-600 font-medium' }} transition-colors">
                    Về Nông trại
                </a>
            </nav>

            <!-- Desktop Action Icons -->
            <div class="hidden lg:flex items-center space-x-5">
                @auth
                    <!-- 1. NÚT GIỎ HÀNG (DESKTOP - CHỈ HIỆN KHI ĐĂNG NHẬP) -->
                    <a href="{{ route('customer.cart') }}"
                       class="text-gray-500 hover:text-brand-600 transition relative p-2" title="Giỏ hàng của bạn">
                        <i class="fa-solid fa-cart-shopping text-xl"></i>
                        @if($cartCount > 0)
                            <span class="absolute top-0 right-0 bg-red-500 text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center border-2 border-white">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif
                    </a>

                    <!-- 2. DROPDOWN THÔNG BÁO (DESKTOP) -->
                    <div class="relative">
                        <button @click="notifMenuOpen = !notifMenuOpen; userMenuOpen = false" 
                                @click.outside="notifMenuOpen = false"
                                class="text-gray-500 hover:text-brand-600 transition relative p-2 focus:outline-none flex items-center justify-center" 
                                title="Thông báo">
                            <i class="fa-regular fa-bell text-xl"></i>
                            @if($unreadNotifCount > 0)
                                <span class="absolute top-0 right-0 bg-brand-500 text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center border-2 border-white">
                                    {{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Sổ danh sách Thông báo -->
                        <div x-show="notifMenuOpen" 
                             x-transition
                             style="display: none;"
                             class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-gray-100 py-3 z-50">
                            <div class="px-4 pb-2.5 border-b border-gray-100 flex items-center justify-between">
                                <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider">Thông Báo Mới</h4>
                                @if($unreadNotifCount > 0)
                                    <button type="button" wire:click="markAsRead" class="text-[11px] font-semibold text-brand-600 hover:underline">
                                        Đánh dấu đã đọc tất cả
                                    </button>
                                @endif
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                @forelse($notifications as $notif)
                                    @php
                                        $icon = $notif->data['icon'] ?? ($notif->type === 'order_delivered' ? 'fa-circle-check' : ($notif->type === 'order_paid' ? 'fa-receipt' : 'fa-bell'));
                                        $iconColor = $notif->type === 'order_delivered' ? 'text-emerald-700 bg-emerald-100' : ($notif->type === 'order_paid' ? 'text-brand-700 bg-brand-100' : 'text-amber-600 bg-amber-100');
                                    @endphp
                                    <div wire:click="markAsRead({{ $notif->id }})" 
                                        class="p-3.5 hover:bg-brand-50/50 transition-colors cursor-pointer flex items-start gap-3 {{ is_null($notif->read_at) ? 'bg-brand-50/30' : '' }}">
                                        <div class="w-8 h-8 rounded-full {{ $iconColor }} flex items-center justify-center shrink-0 mt-0.5">
                                            <i class="fa-solid {{ $icon }} text-xs"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gray-800 line-clamp-1">{{ $notif->title }}</p>
                                            <p class="text-[11px] text-gray-500 line-clamp-2 mt-0.5 leading-snug">{{ $notif->content ?? $notif->message }}</p>
                                            <span class="text-[10px] text-gray-400 font-mono mt-1 block">
                                                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                            </span>
                                        </div>
                                       @if(is_null($notif->read_at))
                                            <span class="w-2 h-2 rounded-full bg-brand-500 shrink-0 mt-2"></span>
                                        @endif
                                    </div>
                                @empty
                                    <div class="py-8 text-center text-gray-400 text-xs">
                                        <i class="fa-regular fa-bell-slash text-2xl mb-2 block text-gray-300"></i>
                                        Chưa có thông báo nào
                                    </div>
                                @endforelse
                            </div>

                            @if($totalNotifsCount > 5)
                                <div class="pt-2 border-t border-gray-100 text-center px-4">
                                    <button type="button" 
                                            wire:click="toggleShowAllNotifs" 
                                            class="text-xs font-bold text-brand-700 hover:text-brand-800 transition py-1 cursor-pointer">
                                        {{ $showAllNotifs ? 'Thu gọn thông báo' : 'Hiển thị tất cả thông báo (' . $totalNotifsCount . ')' }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. DROPDOWN TÀI KHOẢN THÀNH VIÊN (DESKTOP) -->
                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen; notifMenuOpen = false" 
                                @click.outside="userMenuOpen = false"
                                class="flex items-center gap-2.5 p-1 rounded-full hover:bg-gray-50 transition-colors focus:outline-none">
                            <img src="{{ Auth::user()->avatar ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150' }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover border border-brand-200">
                            <div class="text-left hidden xl:block">
                                <div class="flex items-center gap-1.5 leading-tight">
                                    <span class="text-xs font-bold text-gray-800">{{ Auth::user()->name }}</span>
                                    @if (\App\Models\Subscription::where('user_id', Auth::id())->where('status', 'active')->exists())
                                        <span class="inline-flex items-center gap-0.5 bg-amber-100 text-amber-800 text-[10px] font-black px-1.5 py-0.5 rounded-md border border-amber-300">
                                            <i class="fa-solid fa-crown text-[9px] text-amber-600"></i> VIP
                                        </span>
                                    @endif
                                </div>
                                <span class="block text-[11px] font-semibold text-brand-600">✨ {{ Auth::user()->reward_points ?? 0 }} điểm</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 ml-0.5"></i>
                        </button>

                        <div x-show="userMenuOpen" 
                             x-transition
                             style="display: none;"
                             class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-xs text-gray-400">Tài khoản thành viên</p>
                                <p class="text-sm font-bold text-gray-800 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('customer.profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                                    <i class="fa-regular fa-user text-gray-400 w-4"></i> Thông tin cá nhân
                                </a>
                                @if (\App\Models\Subscription::where('user_id', Auth::id())->where('status', 'active')->exists())
                                <a href="{{ route('customer.vip.setup') }}" wire:navigate class="flex items-center gap-2.5 px-4 py-2 text-sm text-amber-900 bg-amber-50 hover:bg-amber-100/80 font-bold transition">
                                    <i class="fa-solid fa-crown text-amber-600 w-4"></i> Lịch Giao Rau VIP
                                </a>
                                @endif
                               
                            </div>
                            <div class="border-t border-gray-100 pt-1">
                                <button type="button" 
                                        wire:click="logout" 
                                        wire:loading.attr="disabled"
                                        class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 text-left font-medium disabled:opacity-50 transition">
                                    <i wire:loading.remove wire:target="logout" class="fa-solid fa-arrow-right-from-bracket w-4"></i>
                                    <i wire:loading wire:target="logout" class="fa-solid fa-spinner fa-spin w-4"></i>
                                    <span>Đăng xuất</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <button type="button" 
                                wire:click="$dispatchTo('customer.auth.auth-modal', 'open-auth-modal', { tab: 'login' })" 
                                class="text-sm font-bold text-gray-700 hover:text-brand-600 px-3 py-2 transition-colors">
                            Đăng nhập
                        </button>
                        <button type="button" 
                                wire:click="$dispatchTo('customer.auth.auth-modal', 'open-auth-modal', { tab: 'register' })" 
                                class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition-all shadow-sm">
                            Đăng ký
                        </button>
                    </div>          
                @endauth
            </div>

            <!-- Mobile Action Icons -->
            <div class="lg:hidden flex items-center gap-3">
                @auth
                    <!-- Nút Giỏ Hàng Mobile (Chỉ hiện khi đăng nhập) -->
                    <a href="{{ route('customer.cart') }}"
                       class="text-gray-600 hover:text-brand-600 p-2 relative" title="Giỏ hàng của bạn">
                        <i class="fa-solid fa-cart-shopping text-xl"></i>
                        @if($cartCount > 0)
                            <span class="absolute top-0 right-0 bg-red-500 text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center border-2 border-white">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif
                    </a>
                @endauth

                <!-- Nút Toggle Menu Mobile -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-600 hover:text-brand-600 focus:outline-none p-2">
                    <i class="fa-solid text-2xl" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Drawer -->
    <div x-show="mobileMenuOpen" 
         x-transition
         style="display: none;"
         class="lg:hidden bg-white border-t border-gray-100 shadow-xl absolute w-full left-0 top-full max-h-[85vh] overflow-y-auto">
        <div class="px-4 pt-3 pb-6 space-y-3">
            @auth
                <div class="flex items-center gap-3 p-3 bg-brand-50/70 border border-brand-100 rounded-2xl">
                    <img src="{{ $user->avatar ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150' }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover border border-brand-200">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-800 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                        <span class="inline-block mt-1 text-[11px] font-bold text-brand-700 bg-white px-2 py-0.5 rounded-full border border-brand-200">
                            ✨ {{ $user->reward_points ?? 0 }} điểm thưởng
                        </span>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2 pt-1 pb-2">
                    <button type="button" 
                            wire:click="$dispatchTo('customer.auth.auth-modal', 'open-auth-modal', { tab: 'login' })" 
                            class="text-center py-2.5 border border-gray-200 text-gray-800 font-bold text-sm rounded-xl hover:bg-gray-50">
                        Đăng nhập
                    </button>
                    <button type="button" 
                            wire:click="$dispatchTo('customer.auth.auth-modal', 'open-auth-modal', { tab: 'register' })" 
                            class="text-center py-2.5 bg-brand-600 text-white font-bold text-sm rounded-xl hover:bg-brand-700">
                        Đăng ký ngay
                    </button>
                </div>
            @endauth

            <div class="space-y-1 pt-1">
                <a href="{{ route('home') }}" 
                   class="block px-3 py-2.5 text-base {{ request()->routeIs('home') ? 'font-semibold text-brand-700 bg-brand-50' : 'font-medium text-gray-700 hover:bg-gray-50' }} rounded-xl">
                    Trang chủ
                </a>

                <a href="{{ route('products.index') }}" 
                   class="block px-3 py-2.5 text-base {{ request()->routeIs('products.index') ? 'font-semibold text-brand-700 bg-brand-50' : 'font-medium text-gray-700 hover:bg-gray-50' }} rounded-xl">
                    Sản phẩm
                </a>

                <a href="{{ route('about') }}" 
                   class="block px-3 py-2.5 text-base {{ request()->routeIs('about') ? 'font-semibold text-brand-700 bg-brand-50' : 'font-medium text-gray-700 hover:bg-gray-50' }} rounded-xl">
                    Về Nông trại
                </a>
            </div>

            @auth
                <div class="pt-2 border-t border-gray-100 space-y-1">
                    <a href="{{ route('customer.profile') }}" class="flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl">
                        <i class="fa-regular fa-user text-gray-400 w-4"></i> Thông tin cá nhân
                    </a>
                    <a href="{{ route('customer.profile', ['tab' => 'orders']) }}" class="flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl">
                        <i class="fa-solid fa-box-archive text-gray-400 w-4"></i> Đơn hàng của tôi
                    </a>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <button type="button" 
                            wire:click="logout"
                            class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 transition-colors">
                        <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Đăng xuất
                    </button>
                </div>
            @endauth
        </div>
    </div>
</header>