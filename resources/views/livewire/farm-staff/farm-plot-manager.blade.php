<div wire:poll.60s x-data="{
    confirmingFarm: false,
    uploadingFarmPhoto: false,

    checkFarmPhoto(event, maxMb) {
        const file = event.target.files?.[0];

        if (!file) return '';

        const extension = file.name.split('.').pop().toLowerCase();
        const mime = (file.type || '').toLowerCase();

        let message = '';

        if (
            !['jpg', 'jpeg', 'png', 'webp'].includes(extension) ||
            (
                mime !== '' &&
                !['image/jpeg', 'image/png', 'image/webp'].includes(mime)
            )
        ) {
            message = 'Chỉ chấp nhận ảnh JPG, JPEG, PNG hoặc WEBP.';
        } else if (file.size > maxMb * 1024 * 1024) {
            message = 'Ảnh không được vượt quá ' + maxMb + ' MB.';
        }

        if (message !== '') {
            event.preventDefault();
            event.stopImmediatePropagation();
            event.target.value = '';
        }

        return message;
    }
}" x-on:farm-confirm-state="confirmingFarm = $event.detail.busy"
    x-on:livewire-upload-start="uploadingFarmPhoto = true" x-on:livewire-upload-finish="uploadingFarmPhoto = false"
    x-on:livewire-upload-error="uploadingFarmPhoto = false" x-on:livewire-upload-cancel="uploadingFarmPhoto = false">
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
                    {{ $this->selectedPlot ? 'Điều Khiển: ' . $this->selectedPlot->name : 'Sơ Đồ Luống Canh Tác' }}
                </h1>
            </div>
        </div>

        @unless ($this->selectedPlot)
            <button type="button" wire:click="openCreatePlotModal" wire:loading.attr="disabled"
                wire:target="openCreatePlotModal"
                class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl transition shadow-md flex items-center gap-2 active:scale-95 shrink-0 whitespace-nowrap cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Tạo luống đất</span>
            </button>
        @endunless

    </header>

    <div
        class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative z-10 space-y-5 sm:space-y-6 custom-scrollbar h-[calc(100dvh-5rem)]">

        @error('plot')
            <p role="alert" class="rounded-xl bg-red-50 p-3 text-sm text-red-700">
                {{ $message }}
            </p>
        @enderror
        <!-- ================= DANH SÁCH LUỐNG ================= -->
        @unless ($this->selectedPlot)
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
            'empty' => 'Đất trống',
            'in-progress' => 'Đang canh tác',
            'available' => 'Sẵn sàng',
            'maintenance' => 'Cần cải tạo',
        ] as $key => $label)
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="filter_plot" value="{{ $key }}"
                                    wire:click="setFilter('{{ $key }}')" @checked($filter === $key)
                                    class="accent-brand-700">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 pb-12">
                    @forelse ($this->plots as $plot)
                        @php
                            $batch = $this->activeBatch($plot);
                            $isMaintenance = $plot->status === 'maintenance';
                            $canSow = !$batch && $plot->status === 'available';
                            $canMaintain = !$batch && in_array($plot->status, ['empty', 'maintenance'], true);

                            $statusLabel = match ($plot->status) {
                                'empty' => 'Đất trống',
                                'maintenance' => 'Cần cải tạo',
                                'available' => 'Sẵn sàng xuống giống',
                                'in-progress' => 'Đang canh tác',
                                default => $plot->status,
                            };
                        @endphp

                        <div wire:key="plot-{{ $plot->id }}"
                            class="bg-white rounded-3xl border p-4 sm:p-5 shadow-sm flex flex-col justify-between gap-4
            {{ $isMaintenance ? 'border-amber-200' : ($batch ? 'border-brand-200' : 'border-dashed border-gray-300') }}">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-extrabold text-gray-900 text-sm">
                                        {{ $plot->name }}
                                    </h3>

                                    <span
                                        class="text-[10px] font-bold px-2 py-1 rounded-full shrink-0
    {{ match ($plot->status) {
        'maintenance' => 'bg-amber-50 text-amber-800',
        'available' => 'bg-brand-50 text-brand-800',
        'in-progress' => 'bg-blue-50 text-blue-800',
        default => 'bg-gray-100 text-gray-600',
    } }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <p class="text-xs text-gray-500">
                                    Diện tích:
                                    {{ $plot->area !== null ? number_format((float) $plot->area, 2, ',', '.') . ' m²' : '—' }}
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
                                        {{ match ($plot->status) {
                                            'empty' => 'Luống mới cần cải tạo trước khi xuống giống.',
                                            'maintenance' => 'Cần cải tạo đất sau khi kết thúc vụ.',
                                            'available' => 'Luống sẵn sàng cho vụ trồng mới.',
                                            default => 'Kiểm tra hiện trạng luống trước khi thao tác.',
                                        } }}
                                    </p>
                                @endif

                                @if ($plot->plantingBatches->count() > 1)
                                    <p class="text-xs text-red-600 font-bold">
                                        Cần kiểm tra: có nhiều vụ đang hoạt động.
                                    </p>
                                @endif
                            </div>

                            <div class="space-y-2">
                                @if ($canMaintain)
                                    <div wire:key="maintenance-{{ $plot->id }}" x-data="{
                                        running: false,
                                        seconds: 3
                                    }">
                                        <button type="button" x-bind:disabled="running || confirmingFarm"
                                            x-on:click="
                if (running) return;

                running = true;
                seconds = 3;

                const timer = setInterval(() => {
                    seconds--;

                    if (seconds <= 0) {
                        clearInterval(timer);

                        $wire.completeMaintenance({{ $plot->id }})
                            .finally(() => {
                                running = false;
                            });
                    }
                }, 1000);
            "
                                            class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 rounded-xl text-xs disabled:opacity-60">
                                            <span x-show="!running">
                                                <i class="fa-solid fa-wrench mr-1"></i>
                                                Cải tạo đất
                                            </span>

                                            <span x-show="running" x-cloak>
                                                Đang cải tạo...
                                                <strong x-text="seconds"></strong>
                                            </span>
                                        </button>
                                    </div>
                                @endif
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

                <!-- THÔNG TIN LUỐNG ĐẤT VÀ VỤ ĐANG TRỒNG -->
                <div class="bg-white rounded-3xl border border-gray-100 p-4 sm:p-6 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="font-extrabold text-xl text-gray-900">
                                    {{ $plot->name }}
                                </h2>

                                <span
                                    class="text-[10px] font-bold px-2.5 py-1 rounded-full border
    {{ $plot->status === 'maintenance'
        ? 'bg-amber-50 border-amber-200 text-amber-800'
        : 'bg-brand-50 border-brand-200 text-brand-800' }}">
                                    {{ match ($plot->status) {
                                        'empty' => 'Đất trống',
                                        'maintenance' => 'Cần cải tạo',
                                        'available' => 'Sẵn sàng xuống giống',
                                        'in-progress' => 'Đang canh tác',
                                        default => $plot->status,
                                    } }}
                                </span>
                            </div>



                            <p class="text-xs text-gray-500 mt-2">
                                Diện tích:
                                <b class="text-gray-800">
                                    {{ $plot->area !== null ? number_format((float) $plot->area, 2, ',', '.') . ' m²' : '—' }}
                                </b>
                            </p>

                            <p class="text-xs text-gray-500 mt-1">
                                Hiện trạng:
                                <b class="text-gray-800">
                                    {{ $this->currentBatch ? 'Có cây trồng trên luống' : 'Không có vụ đang canh tác' }}
                                </b>
                            </p>
                        </div>

                        @if (!$this->currentBatch && in_array($plot->status, ['empty', 'maintenance'], true))
                            <div wire:key="maintenance-detail-{{ $plot->id }}" x-data="{ running: false, seconds: 3 }">
                                <button type="button" x-bind:disabled="running || confirmingFarm"
                                    x-on:click="
                if (running) return;

                running = true;
                seconds = 3;

                const timer = setInterval(() => {
                    seconds--;

                    if (seconds <= 0) {
                        clearInterval(timer);

                        $wire.completeMaintenance({{ $plot->id }})
                            .finally(() => {
                                running = false;
                            });
                    }
                }, 1000);
            "
                                    class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs disabled:opacity-60">
                                    <span x-show="!running">
                                        Cải tạo đất
                                    </span>

                                    <span x-show="running" style="display: none">
                                        Đang cải tạo…
                                        <strong x-text="seconds"></strong>
                                    </span>
                                </button>
                            </div>
                        @elseif (!$this->currentBatch && $plot->status === 'available')
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
                                <span class="font-bold text-brand-800">
                                    {{ $batch->planted_date?->format('d/m/Y') ?? '—' }}
                                </span>

                                {{ match ($batch->status) {
                                    'planting' => 'Đang canh tác',
                                    'harvesting' => 'Đang thu hoạch',
                                    'completed' => 'Đã kết thúc vụ',
                                    default => $batch->status,
                                } }}
                            </p>
                            <p class="text-xs text-gray-500">
                                Ngày thu hoạch tối thiểu:
                                <span class="font-bold text-brand-800">
                                    {{ $batch->min_manufactured_date?->format('d/m/Y') ?? '—' }}
                                </span>
                            </p>
                        </div>
                    @endif
                </div>

                <!-- DANH SÁCH TẤT CẢ CÁC VỤ ĐÃ TRỒNG TRÊN LUỐNG ĐẤT -->
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
                                wire:click="selectBatch({{ $history->id }})"
                                class="text-left border rounded-2xl p-3 text-xs max-w-full
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

                @if ($batch && !$this->canManageSelectedBatch)
                    <p class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                        Bạn đang xem dữ liệu của vụ này.
                        Chức năng ghi nhật ký và thu hoạch hiện không khả dụng.
                    </p>
                @endif

                @if ($batch)
                    <!-- TABS -->
                    <div
                        class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden">
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

                        <!-- TAB NHẬT KÝ -->
                        @if ($activeTab === 'timeline')
                            <div class="p-4 sm:p-6 lg:p-8 space-y-5">
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                                    <div>
                                        <h3 class="text-sm sm:text-base font-extrabold text-gray-900">Dòng Thời Gian
                                            Sinh Trưởng &
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
                                            $isHarvest = str_contains(mb_strtolower($event->event_name), 'thu hoạch');
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
                                                @if ($event->description)
                                                    <p
                                                        class="whitespace-pre-line text-xs sm:text-sm text-gray-700 leading-relaxed">
                                                        {{ $event->description }}
                                                    </p>
                                                @endif
                                                @if ($event->image)
                                                    <img src="{{ str_starts_with($event->image, 'http') ? $event->image : asset('storage/' . $event->image) }}"
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

                        <!-- TAB LÔ HÀNG THU HOẠCH -->
                        @if ($activeTab === 'harvests')
                            <div class="p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6">
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                                    <div>
                                        <h3 class="text-sm sm:text-base font-extrabold text-blue-700">Các Lô Hàng
                                            Thương Mại Đã
                                            Nhập
                                            Kho</h3>
                                        <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">Mỗi lần thu hoạch sinh
                                            ra 1 Lô
                                            hàng
                                            riêng biệt quản lý theo chuẩn FEFO</p>
                                    </div>
                                    @if ($this->canHarvestSelectedBatch)
                                        <button type="button" wire:click="openHarvestModal"
                                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-2xl transition flex items-center justify-center gap-2 cursor-pointer">
                                            <i class="fa-solid fa-basket-shopping"></i>
                                            Thu Hoạch — Tạo Lô Mới
                                        </button>
                                    @endif
                                </div>

                                <div class="w-full overflow-x-auto custom-scrollbar">
                                    <table
                                        class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                                        <thead>
                                            <tr
                                                class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-y border-gray-100">
                                                <th class="p-3 sm:p-3.5">Mã Lô Hàng</th>
                                                <th class="p-3 sm:p-3.5">Ngày Thu Hái</th>
                                                <th class="p-3 sm:p-3.5 text-center">Sản Lượng Nhập</th>
                                                <th class="p-3 sm:p-3.5 text-center">Tồn Kho</th>
                                                <th class="p-3 sm:p-3.5">Hạn Dùng (FEFO)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 font-medium">
                                            @forelse ($this->harvestBatches as $hb)
                                                @php
                                                    $isExpired = $hb->expiry_date?->isBefore(today()) ?? false;
                                                @endphp
                                                <tr wire:key="hb-{{ $hb->id }}"
                                                    class="hover:bg-blue-50/30 transition">
                                                    <td class="p-3 sm:p-4 font-mono font-bold text-blue-700 text-xs">
                                                        {{ $hb->batch_code }}</td>
                                                    <td class="p-3 sm:p-4 font-mono text-gray-600 text-xs">
                                                        {{ $hb->manufactured_date?->format('d/m/Y') ?? '—' }}</td>
                                                    <td
                                                        class="p-3 sm:p-4 font-black text-brand-800 text-sm text-center">
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
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="p-6 text-center text-xs text-gray-400">
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

    <!-- Modal Tạo Luống Đất -->
    @if ($showCreatePlotModal)
        <div wire:key="create-farm-plot-modal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4">
            <div role="dialog" aria-modal="true" aria-labelledby="create-plot-title"
                class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-7 border border-gray-100 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-3">
                    <h3 id="create-plot-title" class="text-base sm:text-lg font-extrabold text-gray-900">
                        Tạo Luống Đất
                    </h3>

                    <button type="button" wire:click="closeCreatePlotModal" x-bind:disabled="confirmingFarm"
                        wire:loading.attr="disabled" wire:target="requestCreatePlot,createPlot,closeCreatePlotModal"
                        aria-label="Đóng" class="text-gray-400 hover:text-red-500 p-1 disabled:opacity-50">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit="requestCreatePlot" novalidate>
                    <fieldset x-bind:disabled="confirmingFarm" wire:loading.attr="disabled"
                        wire:target="requestCreatePlot,createPlot,closeCreatePlotModal" class="space-y-4">
                        <div>
                            <label for="new-plot-name" class="block font-bold text-gray-700 mb-1">
                                Tên luống <span class="text-red-500">*</span>
                            </label>

                            <input type="text" id="new-plot-name" wire:model="newPlotName" maxlength="255"
                                placeholder="Ví dụ: Luống A4"
                                class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:border-brand-500">

                            @error('newPlotName')
                                <p class="text-xs text-red-600 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="new-plot-area" class="block font-bold text-gray-700 mb-1">
                                Diện tích (m²) <span class="text-red-500">*</span>
                            </label>

                            <input type="number" id="new-plot-area" wire:model="newPlotArea" min="0.01"
                                max="9999999999.99" step="0.01" placeholder="Nhập diện tích luống"
                                class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:border-brand-500">

                            @error('newPlotArea')
                                <p class="text-xs text-red-600 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <p class="text-xs text-gray-500">
                            Luống mới ở trạng thái Đất trống.
                            Hãy bấm Cải tạo đất trước khi xuống giống.
                        </p>

                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                            <button type="button" wire:click="closeCreatePlotModal" x-bind:disabled="confirmingFarm"
                                wire:loading.attr="disabled"
                                wire:target="requestCreatePlot,createPlot,closeCreatePlotModal"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 font-bold text-gray-600 disabled:opacity-50">
                                Đóng
                            </button>

                            <button type="submit" x-bind:disabled="confirmingFarm" wire:loading.attr="disabled"
                                wire:target="requestCreatePlot,createPlot"
                                class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold disabled:opacity-50">
                                <span wire:loading.remove wire:target="requestCreatePlot,createPlot">
                                    Tạo luống
                                </span>

                                <span wire:loading wire:target="requestCreatePlot,createPlot">
                                    Đang xử lý…
                                </span>
                            </button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    @endif

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
                    <button wire:click="closeSowModal" type="button" wire:target="requestSow,sow,closeSowModal"
                        x-bind:disabled="confirmingFarm || uploadingFarmPhoto" wire:loading.attr="disabled"
                        class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit="requestSow" novalidate x-data="{ photoError: '' }"
                    x-on:submit.capture="
        if (uploadingFarmPhoto || photoError !== '') {
            $event.preventDefault();
            $event.stopImmediatePropagation();
        }
    "
                    class="space-y-4 text-xs sm:text-sm">
                    <fieldset x-bind:disabled="confirmingFarm" wire:loading.attr="disabled"
                        wire:target="requestSow,sow" class="space-y-4">
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
                            <select wire:model.live="sowProductId"
                                class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-brand-500 font-medium cursor-pointer">
                                <option value="">-- Chọn giống --</option>
                                @foreach ($this->products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sowProductId')
                                <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Ngày xuống giống <span
                                    class="text-red-500">*</span></label>
                            <input type="date" wire:model.live="sowDate" max="{{ now()->toDateString() }}"
                                class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-brand-500 font-mono text-xs">
                            @error('sowDate')
                                <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($sowProductId !== null && $sowDate !== '')
                            <div class="rounded-2xl border border-brand-200 bg-brand-50 p-3">
                                <p class="text-xs font-bold text-brand-800">
                                    Số ngày thu hoạch tối thiểu của giống cây
                                </p>

                                @if ($this->sowMinGrowthDays !== null)
                                    <p class="mt-1 text-lg font-extrabold text-brand-900">
                                        {{ $this->sowMinGrowthDays }} ngày
                                    </p>
                                @else
                                    <p class="mt-1 text-xs text-amber-700">
                                        Giống cây này chưa có số ngày thu hoạch tối thiểu hợp lệ.
                                    </p>
                                @endif
                            </div>
                        @endif

                        @if ($this->sowMinManufacturedDate !== null)
                            <div class="rounded-2xl border border-brand-200 bg-brand-50 p-3">
                                <p class="text-xs font-bold text-brand-800">
                                    Ngày thu hoạch tối thiểu
                                </p>

                                <p class="mt-1 text-lg font-extrabold text-brand-900">
                                    {{ \Carbon\CarbonImmutable::parse($this->sowMinManufacturedDate)->format('d/m/Y') }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <label for="sow-photo" class="block font-bold text-gray-700 mb-1">
                                Ảnh xuống giống <span class="text-red-500">*</span>
                            </label>

                            <p class="text-xs text-gray-500 mb-2">
                                JPG, JPEG, PNG hoặc WEBP; tối đa 2 MB.
                            </p>

                            <input type="file" id="sow-photo" wire:model="sowPhoto"
                                accept=".jpg,.jpeg,.png,.webp" required
                                x-bind:disabled="confirmingFarm || uploadingFarmPhoto"
                                x-on:change.capture="photoError = checkFarmPhoto($event, 2)"
                                x-on:livewire-upload-error="
            photoError = 'Tải ảnh thất bại. Vui lòng chọn lại ảnh.'
        "
                                x-on:livewire-upload-cancel="
            photoError = 'Đã hủy tải ảnh. Vui lòng chọn lại ảnh.'
        "
                                class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">

                            <p x-show="photoError !== ''" x-text="photoError" style="display: none" role="alert"
                                class="text-xs text-red-600 mt-1"></p>

                            <p wire:loading wire:target="sowPhoto" class="text-xs text-brand-700 mt-1">
                                Đang tải ảnh lên…
                            </p>

                            @if (
                                $sowPhoto instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile &&
                                    $sowPhoto->isPreviewable() &&
                                    !$errors->has('sowPhoto'))
                                <img src="{{ $sowPhoto->temporaryUrl() }}" alt="Ảnh xuống giống đang chọn"
                                    class="mt-2 w-24 h-24 object-cover rounded-xl border">
                            @endif

                            @error('sowPhoto')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            x-bind:disabled="confirmingFarm || uploadingFarmPhoto || photoError !== ''"
                            wire:loading.attr="disabled" wire:target="requestSow,sow,sowPhoto"
                            class="w-full mt-2 bg-brand-700 hover:bg-brand-800 disabled:opacity-60 text-white font-bold py-3 rounded-2xl text-xs sm:text-sm">
                            <span wire:loading.remove wire:target="requestSow,sow">
                                Khởi Tạo Lô Canh Tác
                            </span>

                            <span wire:loading wire:target="requestSow,sow">
                                Đang xử lý…
                            </span>
                        </button>
                    </fieldset>
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
                        x-bind:disabled="confirmingFarm || uploadingFarmPhoto" wire:loading.attr="disabled"
                        wire:target="requestSaveLog,saveLog,closeLogModal"
                        class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit="requestSaveLog" novalidate x-data="{ photoError: '' }"
                    x-on:submit.capture="
        if (uploadingFarmPhoto || photoError !== '') {
            $event.preventDefault();
            $event.stopImmediatePropagation();
        }
    "
                    class="space-y-4 text-xs sm:text-sm">
                    <fieldset x-bind:disabled="confirmingFarm" wire:loading.attr="disabled"
                        wire:target="requestSaveLog,saveLog" class="space-y-4">

                        @php
                            $inputClass =
                                'w-full border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:border-brand-500';

                            $fields = match ($logType) {
                                'fertilizing' => [
                                    'fertilizer_name' => ['Tên phân bón', 'select', config('farm-log.fertilizers', [])],
                                    'fertilizer_dose' => ['Liều lượng, kèm đơn vị', 'text', []],
                                    'fertilizer_method' => [
                                        'Phương pháp bón',
                                        'select',
                                        config('farm-log.fertilizer_methods', []),
                                    ],
                                ],
                                'spraying' => [
                                    'pesticide_name' => ['Tên thuốc', 'select', config('farm-log.pesticides', [])],
                                    'concentration' => ['Nồng độ pha, kèm đơn vị', 'text', []],
                                    'pest' => ['Đối tượng sâu bệnh', 'text', []],
                                    'sprayed_on' => ['Ngày phun', 'date', []],
                                    'isolation_days' => ['Thời gian cách ly', 'days', []],
                                ],
                                'watering' => [
                                    'watering_minutes' => ['Thời lượng tưới (phút)', 'number', []],
                                    'water_source' => ['Nguồn nước', 'select', config('farm-log.water_sources', [])],
                                ],
                                default => [],
                            };
                        @endphp

                        <div>
                            <label for="log-type" class="block font-bold text-gray-700 mb-1">
                                Loại hoạt động <span class="text-red-500">*</span>
                            </label>

                            <select id="log-type" wire:model.live="logType" required
                                x-bind:disabled="uploadingFarmPhoto" class="{{ $inputClass }}">
                                <option value="">-- Chọn hoạt động --</option>
                                @foreach (config('farm-log.activities', []) as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>

                            @error('logType')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div wire:key="farm-log-fields-{{ $logType ?: 'none' }}" class="space-y-4">
                            @foreach ($fields as $key => [$label, $type, $options])
                                <div wire:key="farm-log-field-{{ $logType }}-{{ $key }}">
                                    <label for="log-{{ $key }}" class="block font-bold text-gray-700 mb-1">
                                        {{ $label }} <span class="text-red-500">*</span>
                                    </label>

                                    @if ($type === 'select')
                                        <select id="log-{{ $key }}"
                                            wire:model="logDetails.{{ $key }}" required
                                            class="{{ $inputClass }}">
                                            <option value="">-- Chọn --</option>
                                            @foreach ($options as $value => $optionLabel)
                                                <option value="{{ $value }}">{{ $optionLabel }}</option>
                                            @endforeach
                                        </select>
                                        @if ($options === [])
                                            <p class="mt-1 text-xs text-amber-700">Danh mục chưa có lựa chọn.</p>
                                        @endif
                                    @elseif ($type === 'days')
                                        <select id="log-{{ $key }}"
                                            wire:model.live="logDetails.isolation_days" required
                                            class="{{ $inputClass }}">
                                            <option value="">-- Chọn số ngày theo nhãn --</option>
                                            @for ($days = 0; $days <= 365; $days++)
                                                <option value="{{ $days }}">{{ $days }} ngày</option>
                                            @endfor
                                        </select>
                                    @elseif ($type === 'date')
                                        <input id="log-{{ $key }}" type="date" required
                                            wire:model.live="logDetails.sprayed_on"
                                            min="{{ $this->selectedBatch?->planted_date?->toDateString() }}"
                                            max="{{ today()->toDateString() }}" class="{{ $inputClass }}">
                                    @elseif ($type === 'number')
                                        <input id="log-{{ $key }}" type="number" required
                                            wire:model="logDetails.{{ $key }}" min="1"
                                            max="1440" step="1" class="{{ $inputClass }}">
                                    @else
                                        <input id="log-{{ $key }}" type="text" required
                                            wire:model="logDetails.{{ $key }}"
                                            maxlength="{{ $key === 'pest' ? 255 : 100 }}"
                                            class="{{ $inputClass }}">
                                    @endif

                                    @error('logDetails.' . $key)
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach

                            @if ($logType === 'spraying' && $this->isolationEndDate)
                                <p class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800">
                                    Ngày kết thúc cách ly:
                                    <strong>{{ $this->isolationEndDate }}</strong>
                                </p>
                            @endif

                            @if ($logType === 'pruning')
                                <div>
                                    <label for="log-description" class="block font-bold text-gray-700 mb-1">
                                        Mô tả cắt tỉa <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="log-description" wire:model="logDesc" rows="3" required maxlength="10000"
                                        class="{{ $inputClass }}"></textarea>
                                    @error('logDesc')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">
                                Ảnh minh chứng <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">
                                JPG, JPEG, PNG hoặc WEBP; tối đa 2 MB.
                            </p>
                            <input type="file" wire:model="logPhoto" accept=".jpg,.jpeg,.png,.webp" required
                                x-bind:disabled="confirmingFarm || uploadingFarmPhoto"
                                x-on:change.capture="photoError = checkFarmPhoto($event, 2)"
                                x-on:livewire-upload-error="photoError = 'Tải ảnh thất bại. Vui lòng chọn lại ảnh.'"
                                x-on:livewire-upload-cancel="photoError = 'Đã hủy tải ảnh. Vui lòng chọn lại ảnh.'"
                                class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">
                            <p x-show="photoError !== ''" x-text="photoError" style="display: none" role="alert"
                                class="mt-1 text-xs text-red-600"></p>
                            <div wire:loading wire:target="logPhoto" class="text-[11px] text-brand-700 mt-1">
                                <i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang tải ảnh lên...
                            </div>
                            @if (
                                $logPhoto instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile &&
                                    $logPhoto->isPreviewable() &&
                                    !$errors->has('logPhoto'))
                                <img src="{{ $logPhoto->temporaryUrl() }}"
                                    class="mt-2 w-20 h-20 object-cover rounded-xl border" alt="Ảnh nhật ký">
                            @endif
                            @error('logPhoto')
                                <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" x-bind:disabled="confirmingFarm || uploadingFarmPhoto"
                            wire:loading.attr="disabled" wire:target="requestSaveLog,saveLog,logPhoto"
                            class="w-full mt-2 bg-brand-700 hover:bg-brand-800 disabled:opacity-60 text-white font-bold py-3 rounded-2xl text-xs sm:text-sm">
                            <span wire:loading.remove wire:target="requestSaveLog,saveLog">
                                Lưu Nhật Ký
                            </span>

                            <span wire:loading wire:target="requestSaveLog,saveLog">
                                Đang xử lý…
                            </span>
                        </button>
                    </fieldset>
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
                        x-bind:disabled="confirmingFarm || uploadingFarmPhoto" wire:loading.attr="disabled"
                        wire:target="requestHarvest,harvest,closeHarvestModal"
                        class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit="requestHarvest" novalidate x-data="{ photoError: '' }"
                    x-on:submit.capture="
        if (uploadingFarmPhoto || photoError !== '') {
            $event.preventDefault();
            $event.stopImmediatePropagation();
        }
    "
                    class="space-y-3.5 sm:space-y-4 text-xs sm:text-sm">
                    <fieldset x-bind:disabled="confirmingFarm" wire:loading.attr="disabled"
                        wire:target="requestHarvest,harvest" class="space-y-4">
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
                                    min="{{ $this->selectedBatch?->min_manufactured_date?->toDateString() }}"
                                    class="w-full border rounded-2xl px-3 py-2.5">
                                @error('harvestDate')
                                    <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Hạn dùng (FEFO) <span
                                        class="text-red-500">*</span></label>
                                <input type="date" wire:model="harvestExpiry" min="{{ now()->toDateString() }}"
                                    class="w-full border rounded-2xl px-3 py-2.5">
                                @error('harvestExpiry')
                                    <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Sản lượng nhập kho (Kg) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" wire:model="harvestQty" step="1" min="1"
                                max="100000" placeholder="Nhập số kg nguyên..."
                                class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-blue-600 font-bold font-mono text-blue-700 text-base">
                            @error('harvestQty')
                                <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">
                                Ảnh thu hoạch thực tế <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">
                                JPG, JPEG, PNG hoặc WEBP; tối đa 2 MB.
                            </p>
                            <input type="file" wire:model="harvestPhoto" accept=".jpg,.jpeg,.png,.webp" required
                                x-bind:disabled="confirmingFarm || uploadingFarmPhoto"
                                x-on:change.capture="photoError = checkFarmPhoto($event, 2)"
                                x-on:livewire-upload-error="
        photoError = 'Tải ảnh thất bại. Vui lòng chọn lại ảnh.'
    "
                                x-on:livewire-upload-cancel="
        photoError = 'Đã hủy tải ảnh. Vui lòng chọn lại ảnh.'
    "
                                class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 cursor-pointer">

                            <p x-show="photoError !== ''" x-text="photoError" style="display: none" role="alert"
                                class="mt-1 text-xs text-red-600"></p>
                            <div wire:loading wire:target="harvestPhoto" class="text-[11px] text-blue-600 mt-1">
                                <i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang tải ảnh...
                            </div>
                            @if (
                                $harvestPhoto instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile &&
                                    $harvestPhoto->isPreviewable() &&
                                    !$errors->has('harvestPhoto'))
                                <img src="{{ $harvestPhoto->temporaryUrl() }}"
                                    class="mt-2 w-20 h-20 object-cover rounded-xl border" alt="Ảnh thu hoạch">
                            @endif
                            @error('harvestPhoto')
                                <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-gray-50 rounded-2xl border border-gray-200 p-3.5 sm:p-4 space-y-3">
                            <p class="font-bold text-gray-800 text-xs">Sau khi thu hoạch xong đợt này:</p>
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="radio" wire:model="harvestMode" value="inherit"
                                    class="w-4 h-4 text-brand-700 accent-brand-700 mt-0.5">
                                <div class="text-xs">
                                    <span class="font-bold text-brand-800 block">Thu hoạch định kỳ (Tiếp tục mùa
                                        vụ)</span>
                                    <span class="text-[11px] text-gray-500 block mt-0.5 leading-relaxed">Cây vẫn ở trên
                                        luống.
                                        Lô hàng mới kế thừa toàn bộ nhật ký trước đó.</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="radio" wire:model="harvestMode" value="finish"
                                    class="w-4 h-4 text-red-600 accent-red-600 mt-0.5">
                                <div class="text-xs">
                                    <span class="font-bold text-red-800 block">Thu hoạch tổng kết (Kết thúc mùa
                                        vụ)</span>
                                    <span class="text-[11px] text-gray-500 block mt-0.5 leading-relaxed">
                                        Luống chuyển sang trạng thái Cần cải tạo.
                                        Cải tạo xong mới có thể xuống giống vụ mới.</span>
                                </div>
                            </label>
                        </div>

                        <div class="pt-2">
                            <button type="submit" x-bind:disabled="confirmingFarm || uploadingFarmPhoto"
                                wire:loading.attr="disabled" wire:target="requestHarvest,harvest,harvestPhoto"
                                class="w-full bg-blue-600 text-white rounded-2xl py-3 font-bold disabled:opacity-50">
                                <span wire:loading.remove wire:target="requestHarvest,harvest">
                                    Lưu Thu Hoạch & Tạo Lô Kho
                                </span>

                                <span wire:loading wire:target="requestHarvest,harvest">
                                    Đang xử lý…
                                </span>
                            </button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    @endif

    @script
        <script>
            let confirmationOpen = false;

            const operations = {
                create: {
                    title: 'Xác nhận tạo luống đất?',
                    save: () => $wire.createPlot(),
                },

                sow: {
                    title: 'Xác nhận khởi tạo lô canh tác?',
                    save: () => $wire.sow(),
                },

                log: {
                    title: 'Xác nhận lưu nhật ký?',
                    save: () => $wire.saveLog(),
                },

                harvest: {
                    title: 'Xác nhận lưu thu hoạch và tạo lô kho?',
                    save: () => $wire.harvest(),
                },
            };

            function setConfirmationState(busy) {
                $wire.$el.dispatchEvent(
                    new CustomEvent('farm-confirm-state', {
                        detail: {
                            busy
                        },
                    })
                );
            }

            $wire.on('confirm-farm-action', async ({
                action
            }) => {
                if (
                    confirmationOpen ||
                    !Object.hasOwn(operations, action)
                ) {
                    return;
                }

                confirmationOpen = true;
                setConfirmationState(true);

                try {
                    const operation = operations[action];

                    const result = await Swal.fire({
                        icon: 'question',
                        title: operation.title,
                        text: 'Chọn Hủy để hủy thao tác.',
                        showCancelButton: true,
                        confirmButtonText: 'Xác nhận',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#15803d',
                        cancelButtonColor: '#6b7280',
                        reverseButtons: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });

                    if (result.isConfirmed) {
                        await operation.save();
                    }

                    // Chọn Hủy: không gọi hàm lưu, đóng hoặc reset form.
                } catch (error) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Yêu cầu chưa hoàn tất',
                        text: 'Kiểm tra dữ liệu trên form hoặc kết nối rồi thử lại.',
                        confirmButtonColor: '#15803d',
                    });
                } finally {
                    confirmationOpen = false;
                    setConfirmationState(false);
                }
            });
        </script>
    @endscript
</div>
