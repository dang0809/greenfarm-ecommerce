<div x-data="{ confirmingAccount: false }" x-on:account-confirm-state="confirmingAccount = $event.detail.busy"
    class="flex flex-1 min-h-0 min-w-0 flex-col overflow-hidden">
    @php
        $fallbackAvatar =
            'data:image/svg+xml;base64,' .
            base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="80" height="80" rx="40" fill="#dcfce7"/><circle cx="40" cy="30" r="14" fill="#15803d"/><path d="M12 76a28 28 0 0 1 56 0" fill="#15803d"/></svg>',
            );

        $badges = [
            'admin' => [
                'inline-flex items-center gap-1 text-[11px] font-black bg-slate-900 text-amber-300 border border-slate-700 px-2.5 py-0.5 rounded-full',
                'fa-crown',
                'Admin',
            ],
            'farm_owner' => [
                'inline-flex items-center gap-1 text-[11px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200 px-2.5 py-0.5 rounded-full',
                'fa-crown',
                'Chủ trang trại',
            ],
            'farm_staff' => [
                'inline-flex items-center gap-1 text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200 px-2.5 py-0.5 rounded-full',
                'fa-leaf',
                'Farm Staff',
            ],
            'sales' => [
                'inline-flex items-center gap-1 text-[11px] font-extrabold bg-blue-50 text-blue-800 border border-blue-200 px-2.5 py-0.5 rounded-full',
                'fa-headset',
                'Sales',
            ],
            'customer' => [
                'inline-flex items-center gap-1 text-[11px] font-extrabold bg-purple-50 text-purple-800 border border-purple-200 px-2.5 py-0.5 rounded-full',
                'fa-user',
                'Customer',
            ],
        ];

        $hintClasses = [
            'farm_staff' =>
                'p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl text-emerald-950 text-xs flex items-start gap-2',
            'farm_owner' =>
                'p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-amber-950 text-xs flex items-start gap-2',
            'sales' =>
                'p-3 bg-blue-50/70 border border-blue-200 rounded-xl text-blue-950 text-xs flex items-start gap-2',
            'admin' =>
                'p-3 bg-slate-900 border border-slate-700 rounded-xl text-amber-300 text-xs flex items-start gap-2',
            'customer' =>
                'p-3 bg-purple-50/70 border border-purple-200 rounded-xl text-purple-950 text-xs flex items-start gap-2',
        ];

        $hintTitles = [
            'farm_staff' => 'Farm Staff:',
            'farm_owner' => 'Chủ trang trại',
            'sales' => 'Sales / CSKH:',
            'admin' => 'Admin Master:',
            'customer' => 'Customer:',
        ];

        $hintText = [
            'farm_staff' =>
                'Truy cập module Luống đất, Ghi nhật ký canh tác, Thu hoạch tạo lô hàng, In tem nhiệt QR, Lập phiếu hao hụt kho.',
            'farm_owner' => 'Xem báo cáo, khách hàng, sản phẩm, đánh giá và nhật ký lô nông sản.',
            'sales' =>
                'Truy cập module Quản lý Đơn hàng, Phân bổ lô FEFO, Gọi xe hỏa tốc, Xử lý khiếu nại bồi thường, Kích hoạt Flash Sale.',
            'admin' =>
                'Toàn quyền quản trị tài chính, cấu hình hệ thống, thu hồi lô khẩn cấp, xuất báo cáo và cấp tài khoản.',
            'customer' => 'Tài khoản khách hàng mua sắm và đăng ký rau định kỳ.',
    ]; @endphp
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()"
                class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition shrink-0"
                aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>

            <div class="min-w-0">
                <h1 class="text-base sm:text-2xl font-black text-slate-900 tracking-tight truncate leading-tight">
                    Quản Lý Tài Khoản
                </h1>
            </div>
        </div>
    </header>

    <div
        class="flex-1 min-h-0 min-w-0 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12">

        {{-- Thống kê --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5">
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider block">
                        Khách Hàng
                    </span>

                    <div class="flex items-baseline gap-2 mt-1">
                        <span id="stat-cust-count"
                            class="text-2xl sm:text-3xl font-black text-purple-700 tracking-tight font-mono">
                            {{ $counts['customer'] }}
                        </span>
                        <span
                            class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">
                            Customer
                        </span>
                    </div>

                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">
                        Tài khoản mua nông sản sạch
                    </p>
                </div>

                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider block">
                        NÔNG TRẠI (Staff)
                    </span>

                    <div class="flex items-baseline gap-2 mt-1">
                        <span id="stat-staff-count"
                            class="text-2xl sm:text-3xl font-black text-brand-700 tracking-tight font-mono">
                            {{ str_pad((string) $counts['farm_staff'], 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span
                            class="text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full border border-brand-200">
                            Thực địa
                        </span>
                    </div>

                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">
                        Quản lý luống đất, lô & QR code
                    </p>
                </div>

                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-leaf"></i>
                </div>
            </div>

            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider block">
                        Bán Hàng & CSKH (Sales)
                    </span>

                    <div class="flex items-baseline gap-2 mt-1">
                        <span id="stat-sales-count"
                            class="text-2xl sm:text-3xl font-black text-blue-700 tracking-tight font-mono">
                            {{ str_pad((string) $counts['sales'], 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span
                            class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                            Đơn hàng
                        </span>
                    </div>

                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">
                        Xử lý đơn, gọi xe, khiếu nại
                    </p>
                </div>

                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-headset"></i>
                </div>
            </div>

            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-rose-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-rose-600 uppercase tracking-wider block">
                        Tài Khoản Tạm Khóa
                    </span>

                    <div class="flex items-baseline gap-2 mt-1">
                        <span id="stat-blocked-count"
                            class="text-2xl sm:text-3xl font-black text-rose-600 tracking-tight font-mono">
                            {{ str_pad((string) $counts['blocked'], 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span
                            class="text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                            Blocked
                        </span>
                    </div>

                    <p class="text-[10px] sm:text-[11px] text-rose-500 font-medium mt-1">
                        Đã chặn quyền đăng nhập
                    </p>
                </div>

                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-user-lock"></i>
                </div>
            </div>
        </div>

        {{-- Danh sách tài khoản --}}
        <div
            class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden space-y-4">
            <div
                class="p-3.5 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4 bg-slate-50/30 border-b border-slate-100">
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 flex-1">
                    <div class="relative w-full sm:w-72 md:w-80">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
                        <input type="text" id="user-search-input" wire:model.live.debounce.300ms="search"
                            placeholder="Tìm theo Họ tên, Email, Số điện thoại..."
                            class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-slate-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 bg-white font-medium transition-all">
                    </div>

                    <select id="user-filter-role" wire:model.live="roleFilter"
                        class="w-full sm:w-auto bg-white border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-slate-700 outline-none focus:border-brand-600 transition cursor-pointer">
                        <option value="">-- Tất cả vai trò --</option>
                        <option value="admin">Admin (Quản trị)</option>
                        <option value="farm_owner">Chủ trang trại</option>
                        <option value="farm_staff">Farm Staff (Kỹ sư vườn/kho)</option>
                        <option value="sales">Sales / CSKH (Bán hàng)</option>
                        <option value="customer">Customer (Khách hàng)</option>
                    </select>

                    <select id="user-filter-status" wire:model.live="statusFilter"
                        class="w-full sm:w-auto bg-white border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-slate-700 outline-none focus:border-brand-600 transition cursor-pointer">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="active">Đang hoạt động (active)</option>
                        <option value="blocked">Tạm khóa (blocked)</option>
                        <option value="inactive">Chờ xác thực email (inactive)</option>
                    </select>

                    <button type="button" wire:click="resetFilters"
                        class="p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 transition shadow-xs self-end sm:self-auto"
                        title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>

                <button type="button" wire:click="openCreate"
                    class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95 shrink-0 whitespace-nowrap">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Cấp Tài Khoản Mới</span>
                </button>
            </div>

            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr
                            class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-slate-100">
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5 text-left align-middle">
                                Thành Viên
                            </th>
                            <th class="py-3.5 sm:py-4 px-4 text-left align-middle">
                                Email Đăng Nhập
                            </th>
                            <th class="py-3.5 sm:py-4 px-4 text-center align-middle">
                                Số Điện Thoại
                            </th>
                            <th class="py-3.5 sm:py-4 px-4 text-center align-middle">
                                Vai Trò
                            </th>
                            <th class="py-3.5 sm:py-4 px-4 text-center align-middle">
                                Trạng Thái
                            </th>
                            <th class="py-3.5 sm:py-4 px-4 text-center align-middle">
                                Ngày Tạo
                            </th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-6 text-center align-middle">
                                Thao Tác
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 font-medium" id="users-tbody">
                        @foreach ($users as $user)
                            @php
                                $badge = $badges[$user->role] ?? $badges['customer'];

                                $canDelete = $user->role !== 'admin' && (int) $user->id !== (int) auth()->id();
                            @endphp

                            <tr wire:key="account-row-{{ $user->id }}"
                                class="hover:bg-slate-50 transition bg-white border-b border-slate-100">

                                <td class="py-3.5 sm:py-4 px-4 sm:px-5 align-middle">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->avatar_url ?: $fallbackAvatar }}"
                                            alt="{{ $user->name }}"
                                            class="w-10 h-10 rounded-full object-cover border border-slate-200 shrink-0">

                                        <div class="min-w-0">
                                            <b wire:click="openEdit({{ $user->id }})"
                                                class="text-slate-900 text-xs sm:text-sm block truncate hover:text-brand-700 cursor-pointer">
                                                {{ $user->name }}
                                            </b>

                                            <span class="text-[11px] text-slate-400 font-mono">
                                                ID: #{{ $user->id }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 sm:py-4 px-4 font-mono text-slate-700 text-xs align-middle">
                                    {{ $user->email }}
                                </td>

                                <td
                                    class="py-3.5 sm:py-4 px-4 font-mono font-semibold text-slate-800 text-xs text-center align-middle">
                                    @if ($user->phone)
                                        {{ $user->phone }}
                                    @else
                                        <span class="text-slate-400 font-normal italic">Chưa cập nhật</span>
                                    @endif
                                </td>

                                <td class="py-3.5 sm:py-4 px-4 text-center align-middle">
                                    <span class="{{ $badge[0] }}">
                                        <i class="fa-solid {{ $badge[1] }} text-[10px]"></i>
                                        {{ $badge[2] }}
                                    </span>
                                </td>

                                <td class="py-3.5 sm:py-4 px-4 align-middle text-center">
                                    @php
                                        $isSelf = $user->id === (int) auth()->id();

                                        $canToggle = !$isSelf && in_array($user->status, ['active', 'blocked'], true);
                                    @endphp

                                    <div wire:key="account-status-{{ $user->id }}" x-data="{ enabled: @js($user->status === 'active') }"
                                        x-on:account-status-updated.window="
            if (Number($event.detail.id) === {{ $user->id }}) {
                enabled = $event.detail.status === 'active';
            }
        "
                                        class="inline-flex items-center">

                                        <button type="button" role="switch"
                                            wire:click="requestToggleStatus({{ $user->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="requestToggleStatus,saveToggleStatus,cancelToggleStatus"
                                            @disabled(!$canToggle)
                                            x-bind:disabled="confirmingAccount || @js(!$canToggle)"
                                            x-bind:aria-checked="enabled.toString()"
                                            aria-label="Trạng thái tài khoản {{ $user->name }}"
                                            title="{{ $isSelf
                                                ? 'Không thể tự khóa tài khoản đang đăng nhập'
                                                : ($canToggle
                                                    ? 'Bật/tắt trạng thái tài khoản'
                                                    : 'Chờ xác thực email') }}"
                                            x-bind:class="enabled ? 'bg-brand-600' : 'bg-slate-300'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full
                transition-colors duration-300
                focus:outline-none focus:ring-2 focus:ring-brand-500
                focus:ring-offset-2 disabled:opacity-60
                disabled:cursor-not-allowed cursor-pointer">

                                            <span aria-hidden="true"
                                                x-bind:class="enabled ? 'translate-x-5' : 'translate-x-1'"
                                                class="inline-block h-5 w-5 rounded-full bg-white shadow
                    ring-1 ring-slate-200
                    transition-transform duration-300 ease-in-out">
                                            </span>
                                        </button>
                                    </div>
                                </td>

                                <td
                                    class="py-3.5 sm:py-4 px-4 font-mono text-slate-500 text-xs text-center align-middle">
                                    {{ $user->created_at?->format('Y-m-d') }}
                                </td>

                                <td class="py-3.5 sm:py-4 px-4 sm:px-6 text-center align-middle">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <button type="button" wire:click="openEdit({{ $user->id }})"
                                            x-bind:disabled="confirmingAccount"
                                            class="p-2 rounded-xl bg-slate-100 text-slate-700 hover:text-brand-700 hover:bg-brand-50 transition cursor-pointer"
                                            title="Chỉnh sửa phân quyền">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button type="button" wire:click="openResetPassword({{ $user->id }})"
                                            x-bind:disabled="confirmingAccount"
                                            class="p-2 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 transition cursor-pointer"
                                            title="Đặt lại mật khẩu">
                                            <i class="fa-solid fa-key"></i>
                                        </button>

                                        <button type="button" wire:click="requestDeleteAccount({{ $user->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="requestDeleteAccount({{ $user->id }}),deleteAccount,cancelDeleteAccount"
                                            @disabled(!$canDelete)
                                            x-bind:disabled="confirmingAccount || @js(!$canDelete)"
                                            aria-label="Xóa tài khoản"
                                            title="{{ $canDelete ? 'Xóa tài khoản' : 'Không thể xóa tài khoản Admin hoặc tài khoản đang đăng nhập' }}"
                                            class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition {{ $canDelete ? 'cursor-pointer' : 'opacity-40 cursor-not-allowed' }}">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($users->isEmpty())
                <div id="users-empty-state" class="py-16 text-center">
                    <div
                        class="w-16 h-16 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                    <p class="text-base font-bold text-slate-700">
                        Không tìm thấy tài khoản người dùng nào
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        Vui lòng thay đổi từ khóa tìm kiếm hoặc điều chỉnh bộ lọc.
                    </p>
                </div>
            @endif
        </div>

        @if ($users->hasPages())
            <div class="p-3.5 sm:p-5 border-t border-slate-100">
                {{ $users->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    {{-- Modal tạo/sửa tài khoản --}}
    @if ($showUserModal)
        <div id="modal-user-form" wire:key="account-form-{{ $editingId ?? 'new' }}" wire:click.self="closeUserModal"
            x-on:keydown.escape.window="$wire.closeUserModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">

            <div
                class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-7 max-h-[92vh] overflow-y-auto custom-scrollbar border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-lg border border-brand-200 shrink-0">
                            <i class="fa-solid fa-id-badge"></i>
                        </div>

                        <div>
                            <h3 id="modal-user-title"
                                class="text-base sm:text-lg font-extrabold text-slate-900 leading-tight">
                                {{ $editingId ? 'Chỉnh Sửa Quyền: ' . $name : 'Cấp Tài Khoản Người Dùng Mới' }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                {{ $editingId !== null ? 'Thông tin tài khoản và phân quyền' : 'Thông tin đăng nhập và vai trò tài khoản' }}
                            </p>
                        </div>
                    </div>

                    <button type="button" wire:click="closeUserModal"
                        class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition shrink-0">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit="requestSaveAccount" novalidate class="space-y-3.5 text-xs sm:text-sm">

                    @error('form')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div class="space-y-2">
                        <label for="uf-avatar" class="block font-bold text-slate-700 mb-1">
                            Ảnh đại diện
                            <span class="text-slate-400 font-medium">(không bắt buộc)</span>
                        </label>

                        <div class="flex items-center gap-3">
                            @if (
                                $newAvatar instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile &&
                                    $newAvatar->isPreviewable() &&
                                    !$errors->has('newAvatar'))
                                <img src="{{ $newAvatar->temporaryUrl() }}"
                                    wire:key="account-avatar-preview-{{ $avatarInputKey }}-{{ $newAvatar->getFilename() }}"
                                    alt="Ảnh đang chọn"
                                    class="w-16 h-16 rounded-full object-cover border border-slate-200 shrink-0">
                            @else
                                <img src="{{ $currentAvatarUrl ?: $fallbackAvatar }}" alt="Ảnh đại diện"
                                    class="w-16 h-16 rounded-full object-cover border border-slate-200 shrink-0">
                            @endif

                            <div class="min-w-0 flex-1">
                                <input id="uf-avatar" type="file" wire:model="newAvatar"
                                    wire:key="account-avatar-input-{{ $avatarInputKey }}"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">

                                <p class="text-[10px] text-slate-400 mt-1">
                                    JPG, JPEG, PNG hoặc WEBP, tối đa 2 MB.
                                </p>

                                <p wire:loading wire:target="newAvatar" class="text-xs text-brand-700 mt-1">
                                    Đang tải ảnh...
                                </p>

                                @if ($newAvatar)
                                    <button type="button" wire:click="cancelAvatar" wire:loading.attr="disabled"
                                        wire:target="newAvatar,requestSaveAccount,saveAccount"
                                        class="text-[11px] font-bold text-rose-600 hover:underline mt-1">
                                        Hủy ảnh đang chọn
                                    </button>
                                @endif
                            </div>
                        </div>

                        @error('newAvatar')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="uf-name" class="block font-bold text-slate-700 mb-1">
                            Họ và tên <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="uf-name" wire:model="name" required
                            placeholder="VD: KS. Nguyễn Văn Bình"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-xs sm:text-sm outline-none focus:border-brand-600 focus:bg-white font-semibold">
                        @error('name')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="uf-email" class="block font-bold text-slate-700 mb-1">
                                Email đăng nhập <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="uf-email" wire:model="email" required
                                placeholder="vanbinh.farm@greenfarm.vn"
                                class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono outline-none focus:border-brand-600 focus:bg-white">
                            @error('email')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="uf-phone" class="block font-bold text-slate-700 mb-1">
                                Số điện thoại @if ($role !== 'customer')
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <input type="text" id="uf-phone" wire:model="phone"
                                {{ $role !== 'customer' ? 'required' : '' }} placeholder="0918.223.344"
                                class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono outline-none focus:border-brand-600 focus:bg-white font-bold">
                            @error('phone')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if ($editingId === null)
                        <div id="uf-password-wrapper" x-data="{ showPassword: false }">
                            <label for="uf-password" class="block font-bold text-slate-700 mb-1">
                                Mật khẩu khởi tạo
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="password" x-bind:type="showPassword ? 'text' : 'password'"
                                    id="uf-password" wire:model="password" autocomplete="new-password"
                                    maxlength="72" placeholder="Nhập ít nhất 8 ký tự..."
                                    class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl pl-3.5 pr-11 py-2 text-xs font-mono outline-none focus:border-brand-600 focus:bg-white">

                                <button type="button" x-on:click="showPassword = !showPassword"
                                    x-bind:disabled="confirmingAccount"
                                    x-bind:aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
                                    x-bind:title="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
                                    x-bind:aria-pressed="showPassword.toString()" aria-controls="uf-password"
                                    class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-brand-700 transition cursor-pointer disabled:opacity-50">

                                    <i class="fa-solid" x-bind:class="showPassword ? 'fa-eye-slash' : 'fa-eye'">
                                    </i>
                                </button>
                            </div>

                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label for="uf-role" class="block font-bold text-slate-700 mb-1">
                            Vai trò phân quyền<span class="text-red-500">*</span>
                        </label>
                        <select id="uf-role" required wire:model.live="role" @disabled($isEditingAdmin)
                            x-bind:disabled="confirmingAccount || @js($isEditingAdmin)"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold outline-none focus:border-brand-600 cursor-pointer disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed">

                            <option value="farm_staff">
                                Farm Staff (Kỹ sư vườn - Luống đất & Kho FEFO)
                            </option>

                            <option value="sales">
                                Sales / CSKH
                            </option>

                            <option value="admin">
                                Admin Master
                            </option>

                            <option value="farm_owner">
                                Chủ trang trại
                            </option>

                            @if ($editingId !== null)
                                <option value="customer">
                                    Customer (Khách hàng mua sắm)
                                </option>
                            @endif
                        </select>
                        @error('role')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="uf-role-permission-hint"
                        class="{{ $hintClasses[$role] ?? $hintClasses['farm_staff'] }}">
                        <i class="fa-solid fa-shield-halved text-brand-700 mt-0.5 shrink-0"></i>
                        <span id="uf-permission-text" class="leading-relaxed">
                            <b>{{ $hintTitles[$role] ?? '' }}</b>
                            {{ $hintText[$role] ?? '' }}
                        </span>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" wire:click="closeUserModal"
                            class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">
                            Hủy
                        </button>

                        <button type="submit" wire:loading.attr="disabled"
                            wire:target="newAvatar,requestSaveAccount,saveAccount"
                            class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95 flex items-center gap-1.5">

                            <i class="fa-solid fa-check"></i>
                            Lưu Tài Khoản
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal đặt lại mật khẩu --}}
    @if ($showPasswordModal)
        <div id="modal-reset-password" wire:key="password-reset-{{ $resetUserId }}"
            wire:click.self="closeResetPassword" x-on:keydown.escape.window="$wire.closeResetPassword()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">

            <div
                class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-sm">
                                Đặt Lại Mật Khẩu Nhanh
                            </h4>
                            <p id="rp-user-name" class="text-[11px] text-slate-400">
                                Người dùng: {{ $resetUserName }} ({{ $resetUserEmail }})
                            </p>
                        </div>
                    </div>

                    <button type="button" wire:click="closeResetPassword"
                        class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit="requestResetPassword" novalidate class="space-y-3.5 text-xs">

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label for="rp-new-password" class="font-bold text-slate-700">
                                Mật khẩu mới <span class="text-red-500">*</span>
                            </label>
                            <button type="button" wire:click="setPresetPassword"
                                class="text-[11px] text-brand-700 hover:underline font-bold">
                                Tạo pass mẫu: 123456@GreenFarm
                            </button>
                        </div>

                        <input type="text" id="rp-new-password" wire:model="resetPassword" required
                            autocomplete="new-password" maxlength="72" placeholder="Nhập mật khẩu mới..."
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-900 outline-none focus:border-brand-600 focus:bg-white">

                        @error('resetPassword')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" wire:click="closeResetPassword"
                            class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">
                            Hủy
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            wire:target="requestResetPassword,saveResetPassword"
                            class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95">
                            Xác Nhận Đổi Mật Khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@script
    <script>
        let confirming = false;

        function setAccountConfirmationState(busy) {
            $wire.$el.dispatchEvent(
                new CustomEvent('account-confirm-state', {
                    detail: {
                        busy
                    }
                })
            );
        }

        async function confirmAction(options, save, cancel) {
            if (confirming) return;

            confirming = true;
            setAccountConfirmationState(true);

            try {
                const result = await Swal.fire({
                    heightAuto: false,
                    scrollbarPadding: false,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#15803d',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    ...options
                });

                if (result.isConfirmed) {
                    await save();
                } else {
                    await cancel();
                }
            } catch (error) {
                await Swal.fire({
                    heightAuto: false,
                    scrollbarPadding: false,
                    icon: 'error',
                    title: 'Yêu cầu chưa hoàn tất',
                    text: 'Vui lòng kiểm tra kết nối và thử lại.'
                });
            } finally {
                confirming = false;
                setAccountConfirmationState(false);
            }
        }

        $wire.on('confirm-account-save', ({
            editing
        }) => confirmAction({
                title: editing ?
                    'Xác nhận cập nhật tài khoản?' : 'Xác nhận cấp tài khoản mới?'
            },
            () => $wire.saveAccount(),
            () => $wire.cancelSaveAccount()
        ));

        $wire.on('confirm-password-reset', ({
            name
        }) => confirmAction({
                title: 'Xác nhận đặt lại mật khẩu?',
                text: 'Người dùng: ' + name,
                confirmButtonColor: '#d97706'
            },
            () => $wire.saveResetPassword(),
            () => $wire.cancelResetPassword()
        ));

        $wire.on('confirm-account-status', ({
            name,
            blocking
        }) => confirmAction({
                title: blocking ?
                    'Xác nhận khóa tài khoản?' : 'Xác nhận mở khóa tài khoản?',
                text: name
            },
            () => $wire.saveToggleStatus(),
            () => $wire.cancelToggleStatus()
        ));

        $wire.on('confirm-account-delete', ({
            name,
            email
        }) => confirmAction({
                icon: 'warning',
                title: 'Xác nhận xóa tài khoản?',
                text: name + ' (' + email + '). Thao tác này không thể khôi phục.',
                confirmButtonText: 'Xóa tài khoản',
                confirmButtonColor: '#dc2626',
                allowEscapeKey: false,
            },
            () => $wire.deleteAccount(),
            () => $wire.cancelDeleteAccount()
        ));
    </script>
@endscript
