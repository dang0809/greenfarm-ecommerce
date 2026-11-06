<div x-data="{ confirmingBatch: false }" x-on:batch-confirm-state="confirmingBatch = $event.detail.busy">

    @php
    $selectedProduct = $selectedBatch?->plantingBatch?->product;
    $selectedUnit = $selectedProduct?->unit ?? 'đơn vị';
    @endphp

    <!-- Header -->
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 relative z-10 shrink-0 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()"
                class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0"
                aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>

            <div class="min-w-0">
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">
                    Quản Lý Lô Nông Sản
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div
                class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-600"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>{{ today()->format('d/m/Y') }}</span>
            </div>
        </div>
    </header>

    <!-- Main -->
    <div
        class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative z-10 space-y-5 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12 h-[calc(100dvh-5rem)]">

        <!-- Thống kê -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-500">
                        Tổng số lô hàng
                    </span>

                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-gray-50 text-gray-700 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>

                <div id="kpi-total" class="text-xl sm:text-2xl font-black text-gray-900">
                    {{ number_format($stats['total']) }}
                </div>

                <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1">
                    Lô đã nhập kho
                </p>
            </div>

            <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-brand-700">
                        Lô đang bán (Tồn)
                    </span>

                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <div id="kpi-active" class="text-xl sm:text-2xl font-black text-brand-700">
                    {{ number_format($stats['active']) }}
                </div>

                <p class="text-[10px] sm:text-[11px] text-brand-600 font-medium mt-1">
                    Sẵn sàng xuất bán
                </p>
            </div>

            <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-amber-600">
                        Lô cận hạn (&le; 3N)
                    </span>

                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>

                <div id="kpi-near-expiry" class="text-xl sm:text-2xl font-black text-amber-700">
                    {{ number_format($stats['near']) }}
                </div>

                <p class="text-[10px] sm:text-[11px] text-amber-700 font-medium mt-1">
                    Ưu tiên đẩy bán FEFO
                </p>
            </div>

            <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-rose-100 shadow-soft">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-rose-600">
                        Lô đã hết hạn
                    </span>

                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                </div>

                <div id="kpi-expired" class="text-xl sm:text-2xl font-black text-rose-600">
                    {{ number_format($stats['expired']) }}
                </div>

                <p class="text-[10px] sm:text-[11px] text-rose-500 font-medium mt-1">
                    Cần lập phiếu hủy kho
                </p>
            </div>

            <div
                class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-rose-700">
                        Hao hụt xuất hủy
                    </span>

                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                </div>

                <div id="kpi-spoiled" class="text-xl sm:text-2xl font-black text-rose-700">
                    @forelse ($spoiledByUnit as $group)
                    <span>
                        {{ number_format($group->quantity) }}
                        {{ $group->unit }}
                    </span>

                    @unless ($loop->last)
                    <span> · </span>
                    @endunless
                    @empty
                    0
                    @endforelse
                </div>

                <p class="text-[10px] sm:text-[11px] text-rose-500 font-medium mt-1">
                    Ghi log xuất hủy
                </p>
            </div>
        </div>

        <!-- Bảng và bộ lọc -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden space-y-4">

            <div
                class="p-3.5 sm:p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4 bg-gray-50/60">
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 flex-1">
                    <div class="relative w-full sm:w-72 md:w-80">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>

                        <input type="text" id="filter-search" wire:model.live.debounce.300ms="search"
                            placeholder="Tìm mã lô, tên nông sản, luống..."
                            class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white font-medium transition-all">
                    </div>

                    <select id="filter-product" wire:model.live="productFilter"
                        class="bg-white border border-gray-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold text-gray-700 outline-none focus:border-brand-500 transition-all cursor-pointer">
                        <option value="">-- Tất cả nông sản --</option>

                        @foreach ($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->name }}
                        </option>
                        @endforeach
                    </select>

                    <select id="filter-status" wire:model.live="statusFilter"
                        class="bg-white border border-gray-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold text-gray-700 outline-none focus:border-brand-500 transition-all cursor-pointer">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="active">Đang mở bán, còn khả dụng</option>
                        <option value="near_expiry">Cận hạn FEFO (&le; 3 ngày)</option>
                        <option value="expired">Quá HSD, còn tồn</option>
                        <option value="out_of_stock">Đã hết tồn</option>
                        <option value="suspended">Tạm dừng</option>
                        <option value="recalled">Đã thu hồi</option>
                    </select>
                </div>

                <button type="button" wire:click="resetFilters"
                    class="self-end md:self-auto p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 transition-colors shadow-xs cursor-pointer"
                    title="Đặt lại bộ lọc">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>

            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                    <thead>
                        <tr
                            class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100 whitespace-nowrap">
                            <th class="py-3.5 px-4 pl-5">Mã Lô Hàng</th>
                            <th class="py-3.5 px-4">Sản Phẩm</th>
                            <th class="py-3.5 px-4">Vị Trí Trồng</th>
                            <th class="py-3.5 px-4">Ngày Thu Hái</th>
                            <th class="py-3.5 px-4">Hạn Sử Dụng (FEFO)</th>
                            <th class="py-3.5 px-4 text-center">Tồn Kho</th>
                            <th class="py-3.5 px-4 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-4 pr-5 text-right min-w-[340px]">
                                Tác Vụ Quản Trị
                            </th>
                        </tr>
                    </thead>

                    <tbody id="batches-table-body" class="divide-y divide-gray-100 font-medium whitespace-nowrap">

                        @forelse ($batches as $batch)
                        @php
                        $rowProduct = $batch->plantingBatch?->product;
                        $state = $batch->display_status;

                        [$label, $badge] = match ($state) {
                        'recalled' => [
                        'Đã thu hồi',
                        'bg-red-100 text-red-700 border-red-200',
                        ],
                        'suspended' => [
                        'Tạm dừng',
                        'bg-slate-100 text-slate-600 border-slate-300',
                        ],
                        'expired' => [
                        'Đã hết hạn',
                        'bg-red-100 text-red-700 border-red-200',
                        ],
                        'out_of_stock' => [
                        'Đã hết hàng',
                        'bg-gray-100 text-gray-600 border-gray-200',
                        ],
                        'reserved' => [
                        'Đã giữ chỗ hết',
                        'bg-gray-100 text-gray-600 border-gray-200',
                        ],
                        'near_expiry' => [
                        'Cận hạn · FEFO',
                        'bg-amber-100 text-amber-800 border-amber-300 font-bold',
                        ],
                        default => [
                        'Đang mở bán',
                        'bg-brand-100 text-brand-800 border-brand-200',
                        ],
                        };
                        @endphp

                        <tr wire:key="batch-{{ $batch->id }}"
                            class="hover:bg-brand-50/40 transition-colors bg-white border-b border-gray-100 whitespace-nowrap">

                            <td class="py-3 px-4 pl-5">
                                <span wire:click="openModal('detail', {{ $batch->id }})"
                                    class="font-mono font-bold text-brand-800 hover:underline cursor-pointer text-xs block">
                                    {{ $batch->batch_code }}
                                </span>

                                <span class="text-[10px] text-gray-400 font-mono">
                                    ID: {{ $batch->id }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-bold text-gray-900 text-xs">
                                    {{ $rowProduct?->name ?? '—' }}
                                </span>

                                <span class="text-[10px] text-gray-400 font-mono block">
                                    {{ $rowProduct?->sku ?? '—' }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <span
                                    class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-700 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded-lg">
                                    <i class="fa-solid fa-map-pin text-brand-600 text-[10px]"></i>
                                    {{ $batch->plantingBatch?->farmPlot?->name ?? '—' }}
                                </span>
                            </td>

                            <td class="py-3 px-4 font-mono text-gray-600 text-xs">
                                {{ $batch->manufactured_date?->format('d/m/Y') ?? '—' }}
                            </td>

                            <td class="py-3 px-4 font-mono text-xs">
                                <span
                                    class="{{ $batch->expiry_date->lt(today()) ? 'text-red-600 font-bold' : ($state === 'near_expiry' ? 'text-amber-800 font-bold' : 'text-gray-700') }}">
                                    {{ $batch->expiry_date->format('d/m/Y') }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-center font-mono">
                                <span
                                    class="font-extrabold {{ $batch->current_quantity > 0 ? 'text-brand-700 text-sm' : 'text-gray-400 text-xs' }}">
                                    {{ $batch->current_quantity }}
                                </span>

                                <span class="text-[11px] text-gray-400">
                                    /{{ $batch->initial_quantity }}
                                    {{ $rowProduct?->unit }}
                                </span>

                                <span class="text-[10px] text-gray-400 font-mono block">
                                    Giữ: {{ $batch->reserved_quantity }}
                                    · Khả dụng: {{ $batch->available_quantity }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-center">
                                <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>

                            <td class="py-3 px-4 pr-5 text-right space-x-1">
                                <button type="button" wire:click="openModal('spoilage', {{ $batch->id }})"
                                    wire:loading.attr="disabled" wire:target="openModal"
                                    @disabled($batch->available_quantity <= 0)
                                        class="px-2 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition font-bold text-[11px] cursor-pointer">
                                        <i class="fa-solid fa-trash-can mr-1"></i>Xuất hủy
                                </button>

                                <button type="button" wire:click="openModal('expiry', {{ $batch->id }})"
                                    wire:loading.attr="disabled" wire:target="openModal" @disabled($batch->status ===
                                    'recalled')
                                    class="px-2 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100
                                    border border-purple-200 transition font-bold text-[11px] cursor-pointer">
                                    <i class="fa-solid fa-calendar-day mr-1"></i>Sửa HSD
                                </button>

                                <button type="button" wire:click="openModal('stock', {{ $batch->id }})"
                                    wire:loading.attr="disabled" wire:target="openModal" @disabled($batch->status ===
                                    'recalled')
                                    class="px-2 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 border
                                    border-blue-200 transition font-bold text-[11px] cursor-pointer">
                                    <i class="fa-solid fa-scale-balanced mr-1"></i>Cân chỉnh
                                </button>

                                <button type="button" wire:click="openModal('status', {{ $batch->id }})"
                                    wire:loading.attr="disabled" wire:target="openModal" @disabled($batch->status ===
                                    'recalled')
                                    class="px-2 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border
                                    border-slate-200 transition font-bold text-[11px] cursor-pointer">
                                    Trạng thái
                                </button>

                                <button type="button" wire:click="openModal('qr', {{ $batch->id }})"
                                    wire:loading.attr="disabled" wire:target="openModal"
                                    class="px-2 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition font-bold text-[11px] cursor-pointer">
                                    <i class="fa-solid fa-qrcode mr-1"></i>QR
                                </button>

                                <button type="button" wire:click="openModal('detail', {{ $batch->id }})"
                                    wire:loading.attr="disabled" wire:target="openModal"
                                    class="p-1.5 rounded-lg bg-emerald-50 text-brand-800 hover:bg-brand-100 border border-emerald-200 transition cursor-pointer">
                                    <i class="fa-solid fa-fingerprint"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-xs text-gray-400">
                                Không tìm thấy lô hàng phù hợp.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($batches->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $batches->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal xuất hủy / kiểm kê / HSD / trạng thái -->
    @if ($selectedBatch && in_array($modal, ['spoilage', 'stock', 'expiry', 'status'], true))
    @php
    [$modalId, $modalTitle, $modalIcon, $submitLabel, $submitClass] = match ($modal) {
    'spoilage' => [
    'modal-spoilage',
    'Lập Phiếu Báo Hỏng / Xuất Hủy',
    'fa-trash-can',
    'Xác Nhận Xuất Hủy',
    'bg-rose-600 hover:bg-rose-700',
    ],
    'stock' => [
    'modal-adjust-stock',
    'Cân Chỉnh Tồn Kho Thực Tế',
    'fa-scale-balanced',
    'Lưu Tồn Kho Mới',
    'bg-blue-600 hover:bg-blue-700',
    ],
    'expiry' => [
    'modal-adjust-expiry',
    'Sửa Hạn Sử Dụng (HSD)',
    'fa-calendar-day',
    'Lưu Hạn Dùng Mới',
    'bg-purple-600 hover:bg-purple-700',
    ],
    default => [
    'modal-status',
    'Thay Đổi Trạng Thái Lô Hàng',
    'fa-toggle-on',
    'Lưu Trạng Thái',
    'bg-brand-700 hover:bg-brand-800',
    ],
    };
    @endphp

    <div id="{{ $modalId }}" wire:key="batch-edit-{{ $modal }}-{{ $batchId }}"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4
                    class="font-extrabold {{ $modal === 'spoilage' ? 'text-rose-600' : 'text-slate-900' }} text-sm flex items-center gap-1.5">
                    <i class="fa-solid {{ $modalIcon }}"></i>
                    {{ $modalTitle }}
                </h4>

                <button type="button" wire:click="closeModal" x-bind:disabled="confirmingBatch"
                    wire:loading.attr="disabled" wire:target="requestSave,saveChange,closeModal"
                    class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="requestSave" class="space-y-3 text-xs">
                @if ($errors->any())
                <div role="alert">
                    @foreach ($errors->all() as $message)
                    <p>{{ $message }}</p>
                    @endforeach
                </div>
                @endif

                <fieldset x-bind:disabled="confirmingBatch" wire:loading.attr="disabled"
                    wire:target="requestSave,saveChange,closeModal" class="space-y-3">

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Mã lô hàng:
                        </label>

                        <input type="text" value="{{ $selectedBatch->batch_code }}" readonly
                            class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold text-slate-700 outline-none">
                    </div>

                    @if ($modal === 'spoilage')
                    <div>
                        <div class="flex justify-between mb-1">
                            <label for="sp-qty" class="font-bold text-slate-700">
                                Số lượng xuất hủy ({{ $selectedUnit }})
                                <span class="text-red-500">*</span>
                            </label>

                            <span id="sp-max-notice" class="text-rose-600 font-mono font-bold">
                                Tối đa: {{ $selectedBatch->available_quantity }}
                            </span>
                        </div>

                        <input type="number" id="sp-qty" wire:model="quantity" step="1" min="1"
                            max="{{ $selectedBatch->available_quantity }}" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold text-rose-600 text-sm outline-none focus:border-rose-500">
                    </div>
                    @elseif ($modal === 'stock')
                    <div>
                        <label for="as-quantity" class="block font-bold text-slate-700 mb-1">
                            Số lượng tồn mới ({{ $selectedUnit }})
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="number" id="as-quantity" wire:model="quantity" step="1"
                            min="{{ $selectedBatch->reserved_quantity }}" max="2147483647" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 font-mono font-bold text-sm text-slate-900 outline-none focus:border-brand-600">

                        <p>
                            Đang giữ chỗ:
                            {{ $selectedBatch->reserved_quantity }}
                            {{ $selectedUnit }}.
                            Tồn mới không được thấp hơn số này.
                        </p>
                    </div>
                    @elseif ($modal === 'expiry')
                    <div>
                        <label for="ae-expiry" class="block font-bold text-slate-700 mb-1">
                            Hạn sử dụng mới
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="date" id="ae-expiry" wire:model="expiry" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono outline-none focus:border-brand-600 font-bold text-sm">

                        <p>
                            HSD hiện tại:
                            {{ $selectedBatch->expiry_date->format('d/m/Y') }}.
                            HSD mới phải sau ngày thu hoạch/ngày xuống giống.
                        </p>
                    </div>
                    @else
                    <div>
                        <label for="batch-status" class="block font-bold text-slate-700 mb-1">
                            Trạng thái mới
                            <span class="text-red-500">*</span>
                        </label>

                        <select id="batch-status" wire:model="newStatus" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none">
                            <option value="active">Đang mở bán</option>
                            <option value="suspended">Tạm dừng</option>
                            <option value="recalled">Thu hồi</option>
                        </select>

                        <p>
                            Thu hồi dừng cấp lô cho đơn mới.
                            Muốn giảm tồn, lập phiếu xuất hủy riêng.
                        </p>
                    </div>
                    @endif

                    <div>
                        <label for="batch-note" class="block font-bold text-slate-700 mb-1">
                            Lý do thực hiện
                            <span class="text-red-500">*</span>
                        </label>

                        <textarea id="batch-note" wire:model="note" rows="2" maxlength="1000" required
                            placeholder="Nhập lý do chi tiết..."
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-2.5 outline-none {{ $modal === 'spoilage' ? 'focus:border-rose-500' : 'focus:border-brand-600' }} resize-none"></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                            Hủy
                        </button>

                        <button type="submit"
                            class="{{ $submitClass }} text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95 cursor-pointer">
                            <span wire:loading.remove wire:target="requestSave,saveChange">
                                {{ $submitLabel }}
                            </span>

                            <span wire:loading wire:target="requestSave,saveChange">
                                Đang xử lý...
                            </span>
                        </button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal chi tiết -->
    @if ($selectedBatch && $modal === 'detail')
    <div id="modal-detail" wire:key="batch-detail-{{ $selectedBatch->id }}"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-3xl shadow-2xl p-5 sm:p-7 max-h-[90vh] overflow-y-auto custom-scrollbar space-y-4 sm:space-y-6 border border-slate-100">

            <div class="flex justify-between items-center border-b border-slate-100 pb-3 sm:pb-4">
                <div class="min-w-0">
                    <h3 class="text-base sm:text-xl font-extrabold text-slate-900 truncate">
                        Chi Tiết Lô Hàng Thương Mại & Nhật Ký Gốc
                    </h3>

                    <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 truncate">
                        Mã truy xuất:
                        <b id="dt-code" class="text-brand-700 font-mono text-xs sm:text-sm">
                            {{ $selectedBatch->batch_code }}
                        </b>
                    </p>
                </div>

                <button type="button" wire:click="closeModal"
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition-colors shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div
                        class="w-14 h-14 rounded-xl bg-white border border-brand-200 p-1 flex items-center justify-center shrink-0 shadow-sm">
                        <img id="dt-qr-img" src="{{ $qrUrl }}" class="w-full h-full object-contain" alt="QR lô hàng">
                    </div>

                    <div class="min-w-0">
                        <h4 id="dt-crop" class="text-base sm:text-lg font-extrabold text-slate-900 truncate">
                            {{ $selectedProduct?->name ?? '—' }}
                        </h4>

                        <p id="dt-planting-info"
                            class="text-[11px] sm:text-xs text-brand-700 font-mono font-bold mt-0.5 truncate">
                            Lô canh tác:
                            {{ $selectedBatch->plantingBatch?->planting_code ?? '—' }}
                            ({{ $selectedBatch->plantingBatch?->farmPlot?->name ?? '—' }})
                        </p>

                        <div
                            class="flex flex-wrap items-center gap-2 sm:gap-3 text-[11px] sm:text-xs text-slate-500 font-mono mt-1">
                            <span>
                                Thu:
                                <b id="dt-mfg" class="text-slate-800">
                                    {{ $selectedBatch->manufactured_date?->format('d/m/Y') ?? '—' }}
                                </b>
                            </span>

                            <span>•</span>

                            <span>
                                HSD:
                                <b id="dt-exp" class="text-slate-800">
                                    {{ $selectedBatch->expiry_date->format('d/m/Y') }}
                                </b>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="text-left sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-brand-100">
                    <span class="text-[10px] sm:text-xs text-slate-500 block">
                        Tồn kho hiện tại
                    </span>

                    <span id="dt-current-qty" class="text-lg sm:text-xl font-black text-brand-700">
                        {{ $selectedBatch->current_quantity }}
                        {{ $selectedUnit }}
                    </span>

                    <p class="text-[10px] sm:text-xs text-slate-500">
                        Giữ chỗ: {{ $selectedBatch->reserved_quantity }}
                        · Khả dụng: {{ $selectedBatch->available_quantity }}
                    </p>
                </div>
            </div>

            <!-- Nhật ký kho -->
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-trash-can text-red-500"></i>
                    Lịch sử biến động kho
                </h4>

                <div id="dt-logs-list" class="space-y-2">
                    @forelse ($selectedBatch->inventoryLogs as $log)
                    @php
                    [$logLabel, $sign] = match ($log->type) {
                    'import' => ['Nhập kho thu hoạch', '+'],
                    'export_sale' => ['Xuất bán', '-'],
                    'export_spoilage' => ['Xuất hủy', '-'],
                    'return_cancel' => ['Hoàn kho do hủy đơn', '+'],
                    'adjustment_in' => ['Kiểm kê tăng', '+'],
                    'adjustment_out' => ['Kiểm kê giảm', '-'],
                    'expiry_change' => ['Sửa hạn sử dụng', ''],
                    'status_change' => ['Đổi trạng thái', ''],
                    default => [$log->type, ''],
                    };

                    $logColor = $sign === '-'
                    ? 'text-rose-600'
                    : 'text-brand-700';
                    @endphp

                    <div wire:key="batch-log-{{ $log->id }}"
                        class="p-3 rounded-xl border border-slate-100 bg-white flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold {{ $logColor }}">
                                {{ $logLabel }}
                            </span>

                            <p class="text-xs text-slate-700 mt-0.5 font-medium">
                                {{ $log->note }}
                            </p>

                            <span class="text-[10px] text-slate-400 font-mono">
                                {{ $log->created_at?->format('d/m/Y H:i') }}
                                · {{ $log->creator?->name ?? '—' }}
                            </span>
                        </div>

                        <span class="font-mono font-black {{ $logColor }} text-xs sm:text-sm">
                            @if ($log->quantity !== 0)
                            {{ $sign }}{{ number_format($log->quantity) }}
                            {{ $selectedUnit }}
                            @else
                            —
                            @endif
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic">
                        Chưa có biến động kho.
                    </p>
                    @endforelse
                </div>
            </div>

            <!-- Nhật ký canh tác -->
            <div class="space-y-2.5 pt-3 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-seedling text-brand-600"></i>
                    Nhật ký canh tác gốc
                </h4>

                <div id="dt-events-list"
                    class="space-y-3 relative before:absolute before:inset-0 before:left-3 before:w-0.5 before:bg-slate-100">
                    @forelse ($selectedBatch->plantingBatch?->traceabilityEvents ?? collect() as $event)
                    <div wire:key="batch-event-{{ $event->id }}" class="relative pl-6 text-xs">
                        <div
                            class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-brand-600 border-2 border-white shadow-xs">
                        </div>

                        <p class="font-bold text-slate-900">
                            {{ $event->event_name }}
                        </p>

                        <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">
                            {{ $event->description }}
                        </p>

                        <span class="text-[10px] text-slate-400 font-mono">
                            {{ $event->recorded_at?->format('d/m/Y H:i') }}
                            · {{ $event->recorded_by }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic pl-5">
                        Chưa có nhật ký canh tác.
                    </p>
                    @endforelse
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="button" wire:click="closeModal"
                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors cursor-pointer">
                    Đóng cửa sổ
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal QR và in tem -->
    @if ($selectedBatch && $modal === 'qr')
    @teleport('body')
    <div id="farm-print-root" wire:key="batch-qr-{{ $selectedBatch->id }}" x-data="{ qrReady: false, qrFailed: false }"
        x-init="$nextTick(() => {
                    qrReady = $refs.qr.complete && $refs.qr.naturalWidth > 0;
                })"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">

        <div
            class="farm-print-card bg-white rounded-2xl sm:rounded-3xl w-full max-w-[320px] shadow-2xl p-5 sm:p-6 text-center border border-slate-100">
            <div class="farm-no-print flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                    Tem Nhiệt Dán Hộp 70x80
                </span>

                <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-red-500 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div id="farm-thermal-label"
                class="p-4 border-2 border-dashed border-brand-500 rounded-2xl bg-brand-50/30 mb-4">
                <div
                    class="flex items-center justify-center gap-1.5 text-brand-900 border-b border-brand-200 pb-1.5 mb-2">
                    <i class="fa-solid fa-leaf text-brand-600"></i>

                    <span class="font-extrabold text-xs tracking-wider uppercase">
                        ĐẤT LÀNH FARM
                    </span>
                </div>

                <p id="print-name" class="farm-label-name font-bold text-sm text-slate-900 mb-2 leading-tight">
                    {{ $selectedProduct?->name ?? 'Nông sản' }}
                </p>

                <div class="w-32 h-32 mx-auto bg-white border border-slate-200 rounded-xl p-1.5 mb-2 shadow-xs">
                    <img id="print-img" x-ref="qr" src="{{ $qrUrl }}" x-on:load="qrReady = true; qrFailed = false"
                        x-on:error="qrReady = false; qrFailed = true" class="w-full h-full object-contain"
                        alt="QR truy xuất nguồn gốc">
                </div>

                <div
                    class="text-[10px] text-slate-600 font-mono leading-tight space-y-0.5 border-t border-brand-200 pt-2">
                    <p id="print-code" class="font-bold text-slate-900">
                        MÃ LÔ: {{ $selectedBatch->batch_code }}
                    </p>

                    <p id="print-date">
                        Thu:
                        {{ $selectedBatch->manufactured_date?->format('d/m/Y') ?? '—' }}
                        · HSD:
                        {{ $selectedBatch->expiry_date->format('d/m/Y') }}
                    </p>
                </div>

                <p class="text-[9px] text-slate-400 italic mt-1.5">
                    Quét mã QR để xem nhật ký nguồn gốc
                </p>
            </div>

            <template x-if="!qrReady">
                <p class="farm-no-print text-xs" x-text="qrFailed
                                ? 'Không tải được QR. Kiểm tra kết nối rồi mở lại modal.'
                                : 'Đang tải mã QR...'"></p>
            </template>

            <button type="button" x-bind:disabled="!qrReady" x-on:click="if (qrReady) window.print()"
                class="farm-no-print w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 rounded-xl transition active:scale-95 flex items-center justify-center gap-2 shadow-md text-xs cursor-pointer">
                <i class="fa-solid fa-print"></i>
                In Tem Dán Bao Bì
            </button>
        </div>
    </div>
    @endteleport
    @endif

    @script
    <script>
    let confirming = false;

    function setConfirmationState(busy) {
        $wire.$el.dispatchEvent(
            new CustomEvent('batch-confirm-state', {
                detail: {
                    busy
                }
            })
        );
    }

    $wire.on('confirm-batch-change', async () => {
        if (confirming) return;

        confirming = true;
        setConfirmationState(true);

        const titles = {
            spoilage: 'Xác nhận xuất hủy?',
            stock: 'Xác nhận điều chỉnh tồn kho?',
            expiry: 'Xác nhận sửa hạn sử dụng?',
            status: 'Xác nhận đổi trạng thái lô?'
        };

        try {
            const result = await Swal.fire({
                icon: $wire.modal === 'spoilage' ?
                    'warning' :
                    'question',

                title: titles[$wire.modal] ??
                    'Xác nhận cập nhật lô hàng?',

                text: 'Thao tác sẽ cập nhật dữ liệu và ghi nhật ký kho.',
                showCancelButton: true,
                confirmButtonText: 'Xác nhận',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#15803d',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                allowOutsideClick: false
            });

            if (result.isConfirmed) {
                await $wire.saveChange();
            }

            // Hủy trong SweetAlert:
            // giữ modal và dữ liệu đang nhập.
        } catch (error) {
            await Swal.fire({
                icon: 'error',
                title: 'Yêu cầu chưa hoàn tất',
                text: 'Kiểm tra dữ liệu trên form hoặc kết nối rồi thử lại.',
                confirmButtonColor: '#15803d'
            });
        } finally {
            confirming = false;
            setConfirmationState(false);
        }
    });
    </script>
    @endscript
</div>