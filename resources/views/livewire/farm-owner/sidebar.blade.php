<aside id="sidebar" wire:ignore.self
    class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200/80 flex flex-col justify-between shrink-0 shadow-soft transition-transform duration-300 -translate-x-full lg:translate-x-0 lg:static">
    <div class="flex flex-col min-h-0 flex-1">
        <div class="h-16 sm:h-20 flex items-center justify-between px-6 border-b border-slate-100 shrink-0">
            <a href="{{ $dashboardUrl }}" wire:navigate class="flex items-center gap-2.5">
                <div
                    class="w-10 h-10 rounded-xl bg-slate-900 text-brand-400 flex items-center justify-center shrink-0 shadow-sm border border-slate-700">
                    <i class="fa-solid fa-crown text-amber-400 text-base"></i>
                </div>

                <div>
                    <span class="font-extrabold text-lg text-slate-900 tracking-tight block leading-tight">
                        ĐẤT LÀNH
                    </span>

                    <span
                        class="text-[10px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded uppercase tracking-wider">
                        Quản Trị Farm
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

        <button type="button" wire:click="requestLogout" wire:loading.attr="disabled"
            wire:target="requestLogout,logout"
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
@script
    <script>
        let confirmingLogout = false;

        $wire.on('confirm-farm-owner-logout', async () => {
            if (confirmingLogout) return;

            confirmingLogout = true;

            try {
                const result = await Swal.fire({
                    heightAuto: false,
                    icon: 'question',
                    title: 'Xác nhận đăng xuất?',
                    text: 'Bạn có muốn đăng xuất khỏi tài khoản hiện tại?',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    focusCancel: true,
                    allowOutsideClick: false
                });

                if (result.isConfirmed) {
                    // đăng xuất, ghi flash và chuyển về login.
                    await $wire.logout();
                }
            } catch (error) {
                await Swal.fire({
                    heightAuto: false,
                    icon: 'error',
                    title: 'Chưa hoàn tất đăng xuất',
                    text: 'Vui lòng tải lại trang để kiểm tra và thử lại.',
                    confirmButtonText: 'Đóng',
                    confirmButtonColor: '#15803d'
                });
            } finally {
                confirmingLogout = false;
            }
        });
    </script>
@endscript
