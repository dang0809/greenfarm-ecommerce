<aside id="sidebar" wire:ignore.self
    class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200/80 flex flex-col justify-between shrink-0 shadow-soft transition-transform duration-300 -translate-x-full lg:translate-x-0 lg:static">
    <div class="flex flex-col min-h-0 flex-1">
        <div class="h-16 sm:h-20 flex items-center justify-between px-6 border-b border-slate-100 shrink-0">
            <a href="{{ $dashboardUrl }}" wire:navigate class="flex items-center gap-2.5">
                <div
                    class="w-10 h-10 rounded-xl bg-brand-700 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fa-solid fa-leaf text-brand-200 text-xl"></i>
                </div>

                <div>
                    <span class="font-extrabold text-lg text-slate-900 tracking-tight block leading-tight">
                        {{ $farmName }}
                    </span>

                    <span
                        class="text-[10px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded uppercase tracking-wider">
                        Chủ Trang Trại
                    </span>
                </div>
            </a>

            <button type="button" onclick="toggleSidebar()" aria-label="Đóng menu"
                class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <nav class="p-3.5 space-y-1 overflow-y-auto flex-1 custom-scrollbar">
            @foreach ($items as $item)
                <a wire:navigate wire:key="farm-owner-menu-{{ $item['route'] }}" href="{{ $item['url'] }}"
                    @if ($item['active']) aria-current="page" @endif @class([
                        'w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm transition-all text-left whitespace-nowrap',
                        'font-bold menu-admin-active' => $item['active'],
                        'font-semibold menu-admin-inactive' => !$item['active'],
                    ])>
                    <i @class([
                        'fa-solid ' . $item['icon'] . ' w-5 text-center text-sm',
                        'text-brand-200' => $item['active'],
                        'text-slate-400' => !$item['active'],
                    ])></i>

                    <span class="truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach

            @php
                $profileActive = $activeRoute === 'farm-owner.profile';
            @endphp

            <a href="{{ route('farm-owner.profile') }}" wire:navigate wire:key="farm-owner-menu-profile"
                @if ($profileActive) aria-current="page" @endif @class([
                    'w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm transition-all text-left whitespace-nowrap',
                    'font-bold menu-admin-active' => $profileActive,
                    'font-semibold menu-admin-inactive' => !$profileActive,
                ])>
                <i @class([
                    'fa-solid fa-id-badge w-5 text-center text-sm',
                    'text-brand-200' => $profileActive,
                    'text-slate-400' => !$profileActive,
                ])></i>

                <span class="truncate">Hồ Sơ Cá Nhân</span>
            </a>
        </nav>
    </div>

    <div class="p-3.5 sm:p-4 border-t border-slate-100 bg-white shrink-0">
        <div class="flex items-center space-x-3 bg-slate-50 p-2.5 sm:p-3 rounded-2xl border border-slate-200/80 mb-2">
            <img src="{{ $avatarUrl }}" alt="Avatar"
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-brand-600 shrink-0">

            <div class="min-w-0 flex-1">
                <p class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                    {{ $userName }}
                </p>

                <p class="text-[10px] sm:text-[11px] text-amber-700 font-bold truncate">
                    Chủ Trang Trại
                </p>
            </div>
        </div>

        <button type="button" wire:click="logout" wire:loading.attr="disabled" wire:target="logout"
            class="flex items-center justify-center space-x-2 text-rose-600 hover:bg-rose-50 transition w-full py-2 rounded-xl font-bold text-xs disabled:opacity-50 disabled:cursor-wait">
            <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>

            <span wire:loading.remove wire:target="logout">
                Đăng xuất tài khoản
            </span>

            <span wire:loading wire:target="logout">
                Đang đăng xuất...
            </span>
        </button>
    </div>
</aside>
