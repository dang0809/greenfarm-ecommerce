@php
    $items = [
        [
            'route' => 'admin.accounts',
            'label' => 'Quản lý tài khoản',
            'icon' => 'fa-users-gear',
        ],
        [
            'route' => 'admin.banners',
            'label' => 'Banner',
            'icon' => 'fa-images',
        ],
        [
            'route' => 'admin.settings',
            'label' => 'Cấu hình hệ thống',
            'icon' => 'fa-sliders',
        ],
    ];
@endphp

<aside id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-50 w-72 h-[100dvh] min-h-0 bg-white border-r border-slate-200/80 flex flex-col shrink-0 overflow-hidden shadow-soft transition-transform duration-300 -translate-x-full lg:translate-x-0 lg:static">

    <div class="flex flex-col min-h-0 flex-1">
        <div class="h-16 sm:h-20 flex items-center justify-between px-6 border-b border-slate-100 shrink-0">

            <a href="{{ route('admin.accounts') }}" wire:navigate class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center">
                    <i class="fa-solid fa-crown text-amber-400"></i>
                </div>

                <div>
                    <span class="font-extrabold text-lg text-slate-900 block">
                        {{ $farmName }}
                    </span>
                    <span class="text-[10px] font-bold text-amber-800">
                        Chủ Nông Trại
                    </span>
                </div>
            </a>

            <button type="button" onclick="toggleSidebar()" aria-label="Đóng menu"
                class="lg:hidden p-1.5 text-slate-400">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="p-3.5 space-y-1 flex-1 min-h-0 overflow-y-auto custom-scrollbar">
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}" wire:navigate @class([
                    'w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition',
                    'menu-admin-active' => request()->routeIs($item['route']),
                    'menu-admin-inactive' => !request()->routeIs($item['route']),
                ])>
                    <i class="fa-solid {{ $item['icon'] }} w-5 text-center"></i>
                    <span class="truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="p-4 border-t border-slate-100 bg-white shrink-0">
        <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-200 mb-2">

            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center">
                <i class="fa-solid fa-user-shield"></i>
            </div>

            <div class="min-w-0">
                <p class="text-sm font-bold truncate">
                    {{ auth()->user()?->name ?? 'Admin' }}
                </p>
                <p class="text-[11px] text-amber-700 font-bold">
                    Chủ Nông Trại
                </p>
            </div>
        </div>

        <button type="button" wire:click="requestLogout" wire:loading.attr="disabled"
            wire:target="requestLogout,logout"
            class="flex items-center justify-center gap-2 text-rose-600 w-full py-2 rounded-xl font-bold text-xs disabled:opacity-50">

            <i class="fa-solid fa-arrow-right-from-bracket"></i>

            <span wire:loading.remove wire:target="logout">
                Đăng xuất tài khoản
            </span>

            <span wire:loading wire:target="logout">
                Đang đăng xuất...
            </span>
        </button>
    </div>
</aside>

@script
    <script>
        let confirmingLogout = false;

        $wire.on('confirm-admin-logout', async () => {
            if (confirmingLogout) return;

            confirmingLogout = true;

            try {
                const result = await Swal.fire({
                    heightAuto: false,
                    scrollbarPadding: false,
                    icon: 'question',
                    title: 'Xác nhận đăng xuất?',
                    text: 'Bạn có muốn đăng xuất khỏi tài khoản hiện tại?',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    allowOutsideClick: false
                });

                if (result.isConfirmed) {
                    await $wire.logout();
                }
            } catch (error) {
                await Swal.fire({
                    heightAuto: false,
                    scrollbarPadding: false,
                    icon: 'error',
                    title: 'Chưa đăng xuất được',
                    text: 'Vui lòng kiểm tra kết nối và thử lại.',
                    confirmButtonText: 'Đóng',
                    confirmButtonColor: '#15803d'
                });
            } finally {
                confirmingLogout = false;
            }
        });
    </script>
@endscript
