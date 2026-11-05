<div>
    <!-- ================= HEADER ================= -->
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 relative z-10 shrink-0 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()"
                class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0"
                aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <div
                    class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5 truncate">
                </div>
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">
                    {{ $this->selectedPlot ? 'Điều Khiển: ' . $this->selectedPlot->name : 'Sơ Đồ Luống Canh Tác' }}
                </h1>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div
                class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-700"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </header>

    <div
        class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative z-10 space-y-5 sm:space-y-6 custom-scrollbar h-[calc(100dvh-5rem)]">

        <!-- ================= DANH SÁCH LUỐNG ================= -->
        @unless($this->selectedPlot)
        <div class="space-y-4 sm:space-y-6">
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-3.5 bg-white p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                <div class="relative w-full md:w-80">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Tìm tên luống (VD: Nhà Màng A1)..."
                        class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                </div>
                <div class="flex flex-wrap items-center gap-3 sm:gap-5 text-xs sm:text-sm font-bold text-gray-600">
                    @foreach ([
                    'all' => 'Tất cả',
                    'active' => 'Đang canh tác',
                    'empty' => 'Đất trống',
                    'maintenance' => 'Bảo trì',
                    ] as $key => $label)
                    <label class="flex items-center gap-1.5 cursor-pointer hover:text-brand-700 transition">
                        <input type="radio" name="filter_plot" value="{{ $key }}" wire:click="setFilter('{{ $key }}')"
                            @checked($filter===$key) class="accent-brand-700">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 pb-12">
                @forelse ($this->plots as $plot)
                @php
                $batch = $plot->activeBatch();
                $isMaintenance = $plot->status === 'maintenance';
                $canSow = ! $batch && ! $isMaintenance;
                @endphp

                <div wire:key="plot-{{ $plot->id }}" class="bg-white rounded-3xl border p-4 sm:p-5 shadow-sm flex flex-col justify-between gap-4
            {{ $isMaintenance
                ? 'border-amber-200'
                : ($batch ? 'border-brand-200' : 'border-dashed border-gray-300') }}">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-extrabold text-gray-900 text-sm">
                                {{ $plot->name }}
                            </h3>

                            <span class="text-[10px] font-bold px-2 py-1 rounded-full shrink-0
                    {{ $isMaintenance
                        ? 'bg-amber-50 text-amber-800'
                        : ($batch ? 'bg-brand-50 text-brand-800' : 'bg-gray-100 text-gray-600') }}">
                                {{ $isMaintenance
                        ? 'Bảo trì'
                        : ($batch
                            ? ($batch->status === 'harvesting'
                                ? 'Đang thu hoạch'
                                : 'Đang canh tác')
                            : 'Đất trống') }}
                            </span>
                        </div>

                        <p class="text-xs text-gray-500">
                            Diện tích:
                            {{ $plot->area !== null
                    ? number_format((float) $plot->area, 2, ',', '.') . ' m²'
                    : '—' }}
                        </p>

                        @if ($batch)
                        <div class="bg-brand-50/50 rounded-xl p-3 space-y-1">
                            <p class="text-xs font-bold text-brand-900">
                                {{ $batch->product?->name ?? 'Chưa rõ giống' }}
                            </p>

                            <p class="text-[10px] font-mono text-gray-500 break-all">
                                {{ $batch->planting_code }}
                            </p>

                            <p class="text-[11px] text-gray-500">
                                Gieo:
                                {{ $batch->planted_date?->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>
                        @else
                        <p class="text-xs text-gray-500 bg-gray-50 rounded-xl p-3">
                            {{ $isMaintenance
                        ? 'Luống đang bảo trì, chưa thể xuống giống.'
                        : 'Luống sẵn sàng cho vụ trồng mới.' }}
                        </p>
                        @endif

                        @if ($plot->plantingBatches->count() > 1)
                        <p class="text-xs text-red-600 font-bold">
                            Cần kiểm tra: có nhiều vụ đang hoạt động.
                        </p>
                        @endif
                    </div>

                    <div class="space-y-2">
                        @if ($canSow)
                        <button type="button" wire:click="openSowFor({{ $plot->id }})"
                            class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 rounded-xl text-xs">
                            <i class="fa-solid fa-seedling mr-1"></i>
                            Xuống Giống Mới
                        </button>
                        @endif

                        <button type="button" wire:click="selectPlot({{ $plot->id }})"
                            class="w-full border border-gray-200 hover:border-brand-300 text-gray-700 font-bold py-2.5 rounded-xl text-xs">
                            {{ $batch ? 'Quản Lý Luống' : 'Xem Luống & Lịch Sử' }}
                        </button>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-12 text-center text-sm text-gray-400 italic">
                    Không tìm thấy luống phù hợp.
                </div>
                @endforelse
            </div>
        </div>
        @endunless

        <!-- CHI TIẾT LUỐNG -->
        @if ($this->selectedPlot)
        @php
        $plot = $this->selectedPlot;
        $batch = $this->selectedBatch;
        @endphp

        <div class="space-y-4 sm:space-y-6 pb-12">
            <div class="flex items-center justify-between">
                <button wire:click="backToList"
                    class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-gray-600 hover:text-brand-700 bg-white border border-gray-200 px-3.5 sm:px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-xs hover:bg-gray-50 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Quay lại Danh sách Luống
                </button>
            </div>

            {{-- Thông tin luống và vụ đang xem --}}
            <div class="bg-white rounded-3xl border border-gray-100 p-4 sm:p-6 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="font-extrabold text-xl text-gray-900">
                                {{ $plot->name }}
                            </h2>

                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border
                    {{ $plot->status === 'maintenance'
                        ? 'bg-amber-50 border-amber-200 text-amber-800'
                        : 'bg-brand-50 border-brand-200 text-brand-800' }}">
                                {{ $plot->status === 'maintenance' ? 'Bảo trì' : 'Đang hoạt động' }}
                            </span>
                        </div>

                        <p class="text-xs text-gray-500 mt-2">
                            Diện tích:
                            <b class="text-gray-800">
                                {{ $plot->area !== null
                        ? number_format((float) $plot->area, 2, ',', '.') . ' m²'
                        : '—' }}
                            </b>
                        </p>

                        <p class="text-xs text-gray-500 mt-1">
                            Hiện trạng:
                            <b class="text-gray-800">
                                {{ $this->currentBatch
                        ? 'Có cây trồng trên luống'
                        : 'Không có vụ đang canh tác' }}
                            </b>
                        </p>
                    </div>

                    @if (! $this->currentBatch && $plot->status === 'active')
                    <button type="button" wire:click="openSowFor({{ $plot->id }})"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 px-4 rounded-xl text-xs shrink-0">
                        <i class="fa-solid fa-seedling mr-1"></i>
                        Xuống Giống Vụ Mới
                    </button>
                    @endif
                </div>

                @if ($batch)
                <div class="border-t border-gray-100 pt-4 space-y-1.5">
                    <p class="text-[11px] uppercase text-gray-400 font-bold">
                        Vụ đang xem
                    </p>

                    <h3 class="font-extrabold text-brand-800 text-sm">
                        {{ $batch->product?->name ?? 'Chưa rõ giống' }}
                    </h3>

                    <p class="font-mono text-xs text-gray-600 break-all">
                        {{ $batch->planting_code }}
                    </p>

                    <p class="text-xs text-gray-500">
                        Gieo:
                        {{ $batch->planted_date?->format('d/m/Y') ?? '—' }}

                        · {{ match ($batch->status) {
                    'planting' => 'Đang canh tác',
                    'harvesting' => 'Đang thu hoạch',
                    'completed' => 'Đã kết thúc vụ',
                    default => $batch->status,
                } }}
                    </p>
                </div>
                @endif
            </div>

            {{-- Danh sách tất cả vụ trồng của luống --}}
            <div class="bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 space-y-3">
                <div>
                    <h3 class="font-extrabold text-sm text-gray-900">
                        Các Vụ Trồng Trên Luống
                    </h3>

                    <p class="text-xs text-gray-500 mt-1">
                        Chọn vụ để xem nhật ký và các lô đã thu hoạch.
                    </p>
                </div>

                @if ($plot->plantingBatches->count() > 1)
                <p class="rounded-xl bg-red-50 border border-red-200 p-3 text-xs text-red-700">
                    Luống đang có nhiều vụ hoạt động cùng lúc.
                    Cần kiểm tra lại dữ liệu trước khi tiếp tục.
                </p>
                @endif

                <div class="flex flex-wrap gap-2">
                    @forelse ($this->historyBatches as $history)
                    <button type="button" wire:key="history-{{ $history->id }}"
                        wire:click="selectBatch({{ $history->id }})" class="text-left border rounded-2xl p-3 text-xs max-w-full
                    {{ $batch?->id === $history->id
                        ? 'border-brand-500 bg-brand-50'
                        : 'border-gray-200 bg-white hover:border-brand-300' }}">
                        <span class="block font-bold text-gray-900">
                            {{ $history->product?->name ?? 'Sản phẩm' }}
                        </span>

                        <span class="block font-mono text-[10px] text-gray-500 mt-1 break-all">
                            {{ $history->planting_code }}
                        </span>

                        <span class="block text-gray-600 mt-1">
                            {{ match ($history->status) {
                        'planting' => 'Đang canh tác',
                        'harvesting' => 'Đang thu hoạch',
                        'completed' => 'Đã kết thúc',
                        default => $history->status,
                    } }}

                            · {{ $history->product_batches_count }} lô thu hoạch
                        </span>
                    </button>
                    @empty
                    <p class="text-xs text-gray-400">
                        Luống chưa có vụ trồng.
                    </p>
                    @endforelse
                </div>
            </div>

            @if ($batch && ! $this->canManageSelectedBatch)
            <p class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                Bạn đang xem dữ liệu của vụ này.
                Chức năng ghi nhật ký và thu hoạch hiện không khả dụng.
            </p>
            @endif

            @if ($batch)
            {{-- TABS --}}
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden">
                <div
                    class="flex overflow-x-auto border-b border-gray-100 px-4 sm:px-6 pt-3 sm:pt-4 gap-6 sm:gap-8 text-xs sm:text-sm no-scrollbar whitespace-nowrap">
                    <button wire:click="$set('activeTab', 'timeline')"
                        class="pb-3 transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'timeline' ? 'font-bold text-brand-700 border-b-2 border-brand-700' : 'text-gray-500 hover:text-gray-900' }}">
                        <i class="fa-solid fa-timeline"></i> Nhật Ký Canh Tác
                    </button>
                    <button wire:click="$set('activeTab', 'harvests')"
                        class="pb-3 transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'harvests' ? 'font-bold text-brand-700 border-b-2 border-brand-700' : 'text-gray-500 hover:text-gray-900' }}">
                        <i class="fa-solid fa-boxes-stacked"></i> Lô Hàng Thu Hoạch
                        ({{ $this->harvestBatches->count() }})
                    </button>
                </div>

                {{-- TAB NHẬT KÝ --}}
                @if ($activeTab === 'timeline')
                <div class="p-4 sm:p-6 lg:p-8 space-y-5">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-gray-900">Dòng Thời Gian Sinh Trưởng &
                                Chăm Sóc</h3>
                        </div>
                        @if ($this->canManageSelectedBatch)
                        <button type="button" wire:click="openLogModal"
                            class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs px-4 py-2.5 rounded-2xl transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square"></i>
                            Ghi Nhật Ký
                        </button>
                        @endif
                    </div>

                    <div
                        class="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-gray-100">
                        @forelse ($this->timelineEvents as $event)
                        @php
                        $isHarvest = str_contains(
                        mb_strtolower($event->event_name),
                        'thu hoạch'
                        );
                        @endphp
                        <div class="relative pl-7" wire:key="evt-{{ $event->id }}">
                            <div
                                class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-2 border-white shadow-xs {{ $isHarvest ? 'bg-blue-600' : 'bg-brand-700' }}">
                            </div>
                            <div
                                class="p-4 rounded-2xl border space-y-1.5 hover:bg-white transition-all
                                                {{ $isHarvest ? 'border-blue-200 bg-blue-50/30' : 'border-gray-200/80 bg-gray-50/60' }}">
                                <div class="flex justify-between items-start gap-2">
                                    <h4
                                        class="font-extrabold text-xs sm:text-sm leading-tight {{ $isHarvest ? 'text-blue-800' : 'text-gray-900' }}">
                                        {{ $event->event_name }}
                                    </h4>
                                </div>
                                <p class="text-[11px] text-gray-500 font-mono">
                                    <i class="fa-regular fa-clock mr-1 text-gray-400"></i>
                                    {{ $event->recorded_at?->format('d/m/Y H:i') ?? '—' }} •
                                    {{ $event->recorded_by }}
                                </p>
                                <button type="button" wire:click="verifyLogHash({{ $event->id }})"
                                    wire:loading.attr="disabled" wire:target="verifyLogHash"
                                    class="text-[11px] font-bold text-brand-700 hover:underline disabled:opacity-50">
                                    Kiểm Chứng Nội Dung & Ảnh
                                </button>
                                @if ($event->description)
                                <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">
                                    {{ $event->description }}
                                </p>
                                @endif
                                @if ($event->image)
                                <img src="{{ str_starts_with($event->image, 'http')
                                    ? $event->image
                                    : asset('storage/' . $event->image) }}"
                                    x-on:click="$dispatch('farm-preview-image', { src: $el.src })"
                                    class="w-24 h-24 rounded-xl object-cover border mt-2 cursor-pointer hover:scale-105 transition"
                                    alt="Ảnh minh chứng">
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-gray-400 italic pl-5">Chưa có nhật ký canh tác nào.</p>
                        @endforelse
                    </div>
                </div>
                @endif

                {{-- TAB LÔ HÀNG THU HOẠCH --}}
                @if ($activeTab === 'harvests')
                <div class="p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-blue-700">Các Lô Hàng Thương Mại Đã
                                Nhập
                                Kho</h3>
                            <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">Mỗi lần thu hoạch sinh ra 1 Lô
                                hàng
                                riêng biệt quản lý theo chuẩn FEFO</p>
                        </div>
                        @if ($this->canManageSelectedBatch)
                        <button type="button" wire:click="openHarvestModal"
                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-2xl transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-basket-shopping"></i>
                            Thu Hoạch — Tạo Lô Mới
                        </button>
                        @endif
                    </div>

                    <div class="w-full overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                            <thead>
                                <tr
                                    class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-y border-gray-100">
                                    <th class="p-3 sm:p-3.5">Mã Lô Hàng</th>
                                    <th class="p-3 sm:p-3.5">Ngày Thu Hái</th>
                                    <th class="p-3 sm:p-3.5 text-center">Sản Lượng Nhập</th>
                                    <th class="p-3 sm:p-3.5 text-center">Tồn Kho</th>
                                    <th class="p-3 sm:p-3.5">Hạn Dùng (FEFO)</th>
                                    <th class="p-3 sm:p-3.5 text-right">In Tem QR</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium">
                                @forelse ($this->harvestBatches as $hb)
                                @php
                                $isExpired = $hb->expiry_date?->isBefore(today()) ?? false;
                                @endphp
                                <tr wire:key="hb-{{ $hb->id }}" class="hover:bg-blue-50/30 transition">
                                    <td class="p-3 sm:p-4 font-mono font-bold text-blue-700 text-xs">
                                        {{ $hb->batch_code }}</td>
                                    <td class="p-3 sm:p-4 font-mono text-gray-600 text-xs">
                                        {{ $hb->manufactured_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="p-3 sm:p-4 font-black text-brand-800 text-sm text-center">
                                        {{ rtrim(rtrim(number_format($hb->initial_quantity, 2, ',', '.'), '0'), ',') }}
                                        kg
                                    </td>
                                    <td class="p-3 sm:p-4 text-center">
                                        <span
                                            class="font-mono font-bold {{ $hb->current_quantity > 0 ? 'text-brand-700' : 'text-gray-400' }}">
                                            {{ rtrim(rtrim(number_format($hb->current_quantity, 2, ',', '.'), '0'), ',') }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">kg</span>
                                    </td>
                                    <td
                                        class="p-3 sm:p-4 font-mono text-xs {{ $isExpired ? 'text-rose-600 font-bold' : 'text-gray-600' }}">
                                        {{ $hb->expiry_date?->format('d/m/Y') ?? '—' }}
                                        @if ($isExpired)
                                        <span
                                            class="ml-1 text-[10px] bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded">Hết
                                            hạn</span>
                                        @endif
                                    </td>
                                    <td class="p-3 sm:p-4 text-right">
                                        <button wire:click="printQr({{ $hb->id }})"
                                            class="bg-white border border-gray-200 text-gray-700 hover:text-brand-700 hover:border-brand-300 font-bold px-3 py-1.5 rounded-xl text-xs transition cursor-pointer">
                                            <i class="fa-solid fa-qrcode mr-1"></i> In Tem 70x80
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-xs text-gray-400">
                                        Chưa có đợt thu hoạch nào cho lô canh tác này.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>
            @endif

        </div>
        @endif

    </div>


    <!-- Modal Xuống Giống -->
    @if ($showSowModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4">
        <div
            class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-7 border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Xuống Giống Trồng Mới</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Thao tác tại: <b class="text-brand-700">{{ $this->sowPlot?->name ?? '—' }}</b>
                    </p>
                </div>
                <button wire:click="closeSowModal" type="button"
                    class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="sow" class="space-y-4 text-xs sm:text-sm">
                @if ($errors->any())
                <ul class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                    @endforeach
                </ul>
                @endif
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Chọn giống cây trồng <span
                            class="text-red-500">*</span></label>
                    <select wire:model="sowProductId"
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-brand-500 font-medium cursor-pointer">
                        <option value="">-- Chọn giống --</option>
                        @foreach ($this->products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->unit }})</option>
                        @endforeach
                    </select>
                    @error('sowProductId') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ngày xuống giống <span
                            class="text-red-500">*</span></label>
                    <input type="date" wire:model="sowDate" max="{{ now()->toDateString() }}"
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-brand-500 font-mono text-xs">
                    @error('sowDate') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="sow"
                    class="w-full mt-2 bg-brand-700 hover:bg-brand-800 disabled:opacity-60 text-white font-bold py-3 rounded-2xl shadow-sm transition active:scale-95 text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer">
                    <span wire:loading.remove wire:target="sow"><i class="fa-solid fa-seedling mr-1"></i> Khởi Tạo
                        Lô
                        Canh Tác</span>
                    <span wire:loading wire:target="sow"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang
                        lưu...</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Ghi Nhật Ký -->
    @if ($showLogModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4">
        <div
            class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-7 border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-3">
                <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Viết Nhật Ký Chăm Sóc</h3>
                <button wire:click="closeLogModal" type="button"
                    class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="saveLog" class="space-y-4 text-xs sm:text-sm">
                @if ($errors->any())
                <ul class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                    @endforeach
                </ul>
                @endif
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Loại hoạt động <span
                            class="text-red-500">*</span></label>
                    <input list="task-list" wire:model="logName" maxlength="100"
                        placeholder="Tưới nước, Bón phân, Bắt sâu..."
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-brand-500 font-medium">
                    <datalist id="task-list">
                        <option value="Tưới nước ngầm công nghệ Israel">
                        <option value="Bón phân hữu cơ trùn quế vi sinh">
                        <option value="Cắt tỉa cành lá, làm cỏ">
                        <option value="Phun chế phẩm sinh học tỏi ớt">
                    </datalist>
                    @error('logName') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Mô tả thông số chi tiết <span
                            class="text-red-500">*</span></label>
                    <textarea wire:model="logDesc" rows="3" placeholder="Ghi nhận độ ẩm đất, liều lượng phân bón..."
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl p-3 outline-none focus:border-brand-500 resize-none text-xs font-medium"></textarea>
                    @error('logDesc') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ảnh thực địa minh chứng</label>
                    <input type="file" wire:model="logPhoto" accept="image/*"
                        class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">
                    <div wire:loading wire:target="logPhoto" class="text-[11px] text-brand-700 mt-1">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang tải ảnh lên...
                    </div>
                    @if (
                    $logPhoto instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
                    && $logPhoto->isPreviewable()
                    && ! $errors->has('logPhoto')
                    )
                    <img src="{{ $logPhoto->temporaryUrl() }}" class="mt-2 w-20 h-20 object-cover rounded-xl border"
                        alt="Ảnh nhật ký">
                    @endif
                    @error('logPhoto') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="saveLog,logPhoto"
                    class="w-full mt-2 bg-brand-700 hover:bg-brand-800 disabled:opacity-60 text-white font-bold py-3 rounded-2xl shadow-sm transition active:scale-95 text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer">
                    <span wire:loading.remove wire:target="saveLog"><i class="fa-solid fa-fingerprint mr-1"></i> Lưu
                        Nhật Ký
                    </span>
                    <span wire:loading wire:target="saveLog"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang
                        lưu...</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL THU HOẠCH & TẠO LÔ KHO MỚI -->
    @if ($showHarvestModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4">
        <div
            class="bg-white rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-7 max-h-[90vh] overflow-y-auto border border-gray-100">
            <div
                class="flex justify-between items-center mb-4 sm:mb-5 border-b border-gray-100 pb-3 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-blue-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-basket-shopping"></i> Thu Hoạch & Tạo Lô Mới
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Tạo Lô Hàng Mới & ghi nhận chuẩn FEFO</p>
                </div>
                <button wire:click="closeHarvestModal" type="button"
                    class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="harvest" class="space-y-3.5 sm:space-y-4 text-xs sm:text-sm">
                @if ($errors->any())
                <ul class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                    @endforeach
                </ul>
                @endif
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Mã Lô Hàng Mới (Tự sinh)</label>
                    <div class="rounded-2xl bg-blue-50 border border-blue-100 p-3 text-xs text-blue-800">
                        Mã lô hàng sẽ được cấp khi lưu thu hoạch.
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Ngày thu hái <span
                                class="text-red-500">*</span></label>
                        <input type="date" wire:model="harvestDate" max="{{ now()->toDateString() }}"
                            min="{{ $this->selectedBatch?->planted_date?->toDateString() }}"
                            class="w-full border rounded-2xl px-3 py-2.5">
                        @error('harvestDate') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Hạn dùng (FEFO) <span
                                class="text-red-500">*</span></label>
                        <input type="date" wire:model="harvestExpiry" min="{{ now()->toDateString() }}"
                            class="w-full border rounded-2xl px-3 py-2.5">
                        @error('harvestExpiry') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}
                        </p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Sản lượng nhập kho (Kg) <span
                            class="text-red-500">*</span></label>
                    <input type="number" wire:model="harvestQty" step="0.01" min="0.01" placeholder="Nhập số kg..."
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-blue-600 font-bold font-mono text-blue-700 text-base">
                    @error('harvestQty') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">
                        Ảnh thu hoạch thực tế
                        <span class="font-normal text-gray-400">— không bắt buộc</span>
                    </label>
                    <input type="file" wire:model="harvestPhoto" accept="image/*"
                        class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 cursor-pointer">
                    <div wire:loading wire:target="harvestPhoto" class="text-[11px] text-blue-600 mt-1">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang tải ảnh...
                    </div>
                    @if (
                    $harvestPhoto instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
                    && $harvestPhoto->isPreviewable()
                    && ! $errors->has('harvestPhoto')
                    )
                    <img src="{{ $harvestPhoto->temporaryUrl() }}" class="mt-2 w-20 h-20 object-cover rounded-xl border"
                        alt="Ảnh thu hoạch">
                    @endif
                    @error('harvestPhoto') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ghi chú nhật ký thu hoạch</label>
                    <textarea wire:model="harvestNote" rows="2"
                        placeholder="Tình trạng quả, độ ngọt, chất lượng đợt thu..."
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl p-3 outline-none focus:border-blue-600 resize-none text-xs font-medium"></textarea>
                </div>

                <div class="bg-gray-50 rounded-2xl border border-gray-200 p-3.5 sm:p-4 space-y-3">
                    <p class="font-bold text-gray-800 text-xs">Sau khi thu hoạch xong đợt này:</p>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="radio" wire:model="harvestMode" value="inherit"
                            class="w-4 h-4 text-brand-700 accent-brand-700 mt-0.5">
                        <div class="text-xs">
                            <span class="font-bold text-brand-800 block">Thu hoạch định kỳ (Tiếp tục mùa vụ)</span>
                            <span class="text-[11px] text-gray-500 block mt-0.5 leading-relaxed">Cây vẫn ở trên
                                luống.
                                Lô hàng mới kế thừa toàn bộ nhật ký trước đó.</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="radio" wire:model="harvestMode" value="finish"
                            class="w-4 h-4 text-red-600 accent-red-600 mt-0.5">
                        <div class="text-xs">
                            <span class="font-bold text-red-800 block">Thu hoạch tổng kết (Kết thúc mùa vụ)</span>
                            <span class="text-[11px] text-gray-500 block mt-0.5 leading-relaxed">Luống đất sẽ chuyển
                                thành Đất trống để chuẩn bị xuống giống mới.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" wire:loading.attr="disabled" wire:target="harvest,harvestPhoto"
                        class="w-full bg-blue-600 text-white rounded-2xl py-3 font-bold disabled:opacity-50">
                        <span wire:loading.remove wire:target="harvest">
                            Lưu Thu Hoạch & Tạo Lô Kho
                        </span>

                        <span wire:loading wire:target="harvest">
                            Đang lưu...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    <!-- MODAL IN TEM QR -->
    @if ($this->qrBatch)
    @php
    $qrBatch = $this->qrBatch;

    // Query batch giữ tương thích với bộ quét QR ở trang chủ.
    $traceUrl = route('customer.traceability.detail', [
    'batch' => $qrBatch->batch_code,
    ]) . '?batch=' . rawurlencode($qrBatch->batch_code);

    $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?'
    . http_build_query([
    'size' => '300x300',
    'data' => $traceUrl,
    ]);
    @endphp

    @teleport('body')
    <div id="farm-print-root" wire:key="qr-modal-{{ $qrBatch->id }}" x-data="{ qrReady: false }" x-init="$nextTick(() => {
                qrReady = $refs.qr.complete && $refs.qr.naturalWidth > 0;
            })" x-on:keydown.escape.window="$wire.closeQr()"
        class="fixed inset-0 z-[100] bg-black/60 flex items-center justify-center p-4">
        <div class="farm-print-card bg-white rounded-3xl p-5 w-full max-w-sm shadow-xl">
            <div class="farm-no-print flex justify-between items-center mb-4">
                <h3 class="font-bold text-sm text-gray-900">
                    In Tem 70 × 80 mm
                </h3>

                <button type="button" wire:click="closeQr" class="text-gray-500 hover:text-red-600 text-xs font-bold">
                    Đóng
                </button>
            </div>

            <div id="farm-thermal-label" class="border border-dashed rounded-xl p-4 text-center space-y-3">
                <p class="font-extrabold text-sm">
                    NÔNG TRẠI ĐẤT LÀNH
                </p>

                <p class="farm-label-name font-bold text-sm leading-tight">
                    {{ $qrBatch->plantingBatch?->product?->name ?? 'Nông sản' }}
                </p>

                <img x-ref="qr" src="{{ $qrImageUrl }}" x-on:load="qrReady = true" x-on:error="qrReady = false"
                    class="w-36 h-36 mx-auto" alt="QR truy xuất nguồn gốc">

                <p class="font-mono text-[9px] break-all">
                    {{ $qrBatch->batch_code }}
                </p>

                <div class="text-[10px] leading-relaxed">
                    <p>
                        Thu hoạch:
                        {{ $qrBatch->manufactured_date?->format('d/m/Y') ?? '—' }}
                    </p>

                    <p>
                        Hạn dùng:
                        {{ $qrBatch->expiry_date?->format('d/m/Y') ?? '—' }}
                    </p>
                </div>

                <p class="text-[9px]">
                    Quét QR để xem nguồn gốc nông sản
                </p>
            </div>

            <p x-show="!qrReady" class="farm-no-print text-xs text-amber-700 mt-3">
                Đang tải QR. Nếu chưa hiện mã, hãy kiểm tra kết nối mạng.
            </p>

            <button type="button" x-bind:disabled="!qrReady" x-on:click="window.print()"
                class="farm-no-print w-full mt-4 rounded-xl bg-brand-700 hover:bg-brand-800 text-white py-3 text-sm font-bold disabled:opacity-50">
                <i class="fa-solid fa-print mr-1"></i>
                In Tem
            </button>
        </div>
    </div>
    @endteleport
    @endif
</div>