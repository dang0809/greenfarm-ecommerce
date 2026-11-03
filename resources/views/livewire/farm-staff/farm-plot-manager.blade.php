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
                    <span class="text-brand-700">Nông Trại Đất Lành</span>
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

        {{-- ================= DANH SÁCH LUỐNG ================= --}}
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
                    @foreach (['all' => 'Tất cả luống', 'active' => 'Đang canh tác', 'empty' => 'Đất trống'] as $key =>
                    $label)
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
                @php($batch = $plot->activeBatch())

                @if (! $batch)
                <div
                    class="bg-white rounded-2xl sm:rounded-3xl border border-dashed border-gray-300 p-4 sm:p-5 flex flex-col justify-between min-h-[190px] hover:bg-gray-50 transition shadow-sm">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-extrabold text-gray-800 text-sm sm:text-base">{{ $plot->name }}</h3>
                            <span class="bg-gray-100 text-gray-500 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Đất
                                trống</span>
                        </div>
                        <p class="text-xs text-gray-400">Diện tích: {{ $plot->area }}</p>
                        <p
                            class="text-xs text-gray-500 font-medium mt-2.5 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                            Chưa có cây trồng</p>
                    </div>
                    <button wire:click="openSowFor({{ $plot->id }})"
                        class="w-full mt-3 bg-white hover:bg-brand-50 text-brand-700 border border-brand-200 font-bold py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                        <i class="fa-solid fa-seedling text-xs"></i> <span>Xuống Giống Mới</span>
                    </button>
                </div>
                @else
                <div
                    class="bg-white rounded-2xl sm:rounded-3xl border border-brand-100 p-4 sm:p-5 shadow-soft hover:shadow-card hover:border-brand-300 transition-all flex flex-col justify-between min-h-[190px]">
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <h3 class="font-extrabold text-gray-900 text-sm sm:text-base">{{ $plot->name }}</h3>
                            <span class="bg-brand-100 text-brand-800 px-2.5 py-0.5 rounded-full text-[10px] font-bold">
                                {{ $batch->status === 'harvesting' ? 'Đang thu hoạch' : 'Đang canh tác' }}
                            </span>
                        </div>
                        <div class="flex items-start gap-3 mb-2">
                            <div
                                class="w-10 h-10 rounded-2xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100">
                                <i class="fa-solid fa-leaf text-brand-700"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-sm font-extrabold text-brand-900 truncate leading-tight">
                                    {{ $batch->product?->name ?? 'Chưa rõ giống' }}
                                </h4>
                                <p class="text-[11px] text-gray-400 font-mono mt-0.5 truncate">Mã:
                                    {{ $batch->planting_code }}</p>
                                <p class="text-[11px] text-gray-400 font-mono">
                                    Gieo: {{ $batch->planted_date?->format('d/m/Y') ?? '—' }}
                                    @if ($batch->planted_date)
                                    <span class="text-brand-600">({{ (int) $batch->planted_date->diffInDays(now()) }}
                                        ngày)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2.5 border-t border-gray-100 flex items-center justify-end">
                        <button wire:click="selectPlot({{ $plot->id }})"
                            class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold py-2 px-3.5 rounded-xl text-xs transition flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                            <span>Quản Lý Luống</span> <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>
                @endif
                @empty
                <div class="col-span-full py-12 text-center text-sm text-gray-400 italic">
                    Không tìm thấy luống nào phù hợp với bộ lọc.
                </div>
                @endforelse
            </div>
        </div>
        @endunless

        {{-- ================= CHI TIẾT LUỐNG ================= --}}
        @if ($this->selectedPlot)
        @php($plot = $this->selectedPlot)
        @php($batch = $this->selectedBatch)

        <div class="space-y-4 sm:space-y-6 pb-12">
            <div class="flex items-center justify-between">
                <button wire:click="backToList"
                    class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-gray-600 hover:text-brand-700 bg-white border border-gray-200 px-3.5 sm:px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-xs hover:bg-gray-50 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Quay lại Danh sách Luống
                </button>
            </div>

            <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 p-4 sm:p-6 shadow-soft">
                <div class="flex items-center gap-3.5 sm:gap-5 min-w-0">
                    <div
                        class="w-12 h-12 sm:w-16 sm:h-16 bg-brand-50 border border-brand-100 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-seedling text-xl sm:text-2xl text-brand-700"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 sm:gap-3 mb-1">
                            <h2 class="font-extrabold text-xl sm:text-2xl text-gray-900 truncate">{{ $plot->name }}</h2>
                            <span
                                class="text-[10px] sm:text-xs font-bold px-2.5 py-1 rounded-full border shrink-0
                                    {{ $batch ? 'bg-brand-100 text-brand-800 border-brand-200' : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                {{ $batch ? ($batch->status === 'harvesting' ? 'Đang thu hoạch' : 'Đang canh tác') : 'Đất trống' }}
                            </span>
                        </div>

                        @if ($batch)
                        <h3 class="text-sm sm:text-base font-extrabold text-brand-700 truncate">
                            {{ $batch->product?->name ?? 'Chưa rõ giống' }}
                        </h3>
                        <div
                            class="flex flex-wrap items-center gap-2 sm:gap-4 text-[11px] sm:text-xs text-gray-500 font-mono mt-1">
                            <span>Mã: <b class="text-gray-800">{{ $batch->planting_code }}</b></span>
                            <span>•</span>
                            <span>Gieo hạt: <b
                                    class="text-gray-800">{{ $batch->planted_date?->format('d/m/Y') ?? '—' }}</b></span>
                            <span>•</span>
                            <span>Diện tích: <b class="text-gray-800">{{ $plot->area }}</b></span>
                        </div>
                        @else
                        <p class="text-xs text-gray-500 mt-1">Luống chưa có cây trồng.</p>
                        <button wire:click="openSowFor({{ $plot->id }})"
                            class="mt-3 bg-brand-700 hover:bg-brand-800 text-white font-bold py-2 px-4 rounded-xl text-xs transition inline-flex items-center gap-1.5 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-seedling"></i> Xuống Giống Mới
                        </button>
                        @endif
                    </div>
                </div>
            </div>

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
                            <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">Lưu trữ minh chứng & băm mã SHA-256
                                đối chiếu</p>
                        </div>
                        <button wire:click="openLogModal"
                            class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs px-4 py-2.5 rounded-2xl transition flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square text-brand-200"></i>
                            <span>Ghi Nhật Ký</span>
                        </button>
                    </div>

                    <div
                        class="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-gray-100">
                        @forelse ($this->timelineEvents as $event)
                        @php($isHarvest = $event->event_name === 'Thu hoạch' || str_contains($event->event_name, 'Thu
                        hoạch'))
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
                                    <span class="text-[10px] font-mono text-gray-400 shrink-0"
                                        title="{{ $event->data_hash }}">
                                        Hash: {{ substr($event->data_hash, 0, 8) }}...
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 font-mono">
                                    <i class="fa-regular fa-clock mr-1 text-gray-400"></i>
                                    {{ $event->recorded_at?->format('d/m/Y H:i') ?? '—' }} • {{ $event->recorded_by }}
                                </p>
                                @if ($event->description)
                                <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">{{ $event->description }}
                                </p>
                                @endif
                                @if ($event->image)
                                <img src="{{ str_starts_with($event->image, 'http') ? $event->image : asset('storage/' . $event->image) }}"
                                    class="w-24 h-24 rounded-xl object-cover border mt-2 cursor-pointer hover:scale-105 transition"
                                    onclick="previewImage(this.src)" alt="Ảnh minh chứng">
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
                            <h3 class="text-sm sm:text-base font-extrabold text-blue-700">Các Lô Hàng Thương Mại Đã Nhập
                                Kho</h3>
                            <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">Mỗi lần thu hoạch sinh ra 1 Lô hàng
                                riêng biệt quản lý theo chuẩn FEFO</p>
                        </div>
                        <button wire:click="openHarvestModal"
                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-2xl transition flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-basket-shopping"></i>
                            <span>Thu Hoạch (Tạo Lô Mới)</span>
                        </button>
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
                                @php($isExpired = $hb->expiry_date?->isPast())
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

    {{-- ================= MODALS ĐẶT Ở CẤP ĐỘ GỐC ================= --}}

    {{-- Modal Xuống Giống --}}
    @if (!empty($showSowModal) && $showSowModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4">
        <div
            class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-7 border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Xuống Giống Trồng Mới</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Thao tác tại: <b
                            class="text-brand-700">{{ $this->selectedPlot?->name ?? ($sowPlotId ? 'Luống #' . $sowPlotId : '—') }}</b>
                    </p>
                </div>
                <button wire:click="$set('showSowModal', false)" type="button"
                    class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="sow" class="space-y-4 text-xs sm:text-sm">
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
                    @error('sowDate') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="sow"
                    class="w-full mt-2 bg-brand-700 hover:bg-brand-800 disabled:opacity-60 text-white font-bold py-3 rounded-2xl shadow-sm transition active:scale-95 text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer">
                    <span wire:loading.remove wire:target="sow"><i class="fa-solid fa-seedling mr-1"></i> Khởi Tạo Lô
                        Canh Tác</span>
                    <span wire:loading wire:target="sow"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang
                        lưu...</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal Ghi Nhật Ký --}}
    @if (!empty($showLogModal) && $showLogModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4">
        <div
            class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-7 border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-3">
                <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Viết Nhật Ký Chăm Sóc</h3>
                <button wire:click="$set('showLogModal', false)" type="button"
                    class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="saveLog" class="space-y-4 text-xs sm:text-sm">
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
                    @error('logName') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Mô tả thông số chi tiết <span
                            class="text-red-500">*</span></label>
                    <textarea wire:model="logDesc" rows="3" placeholder="Ghi nhận độ ẩm đất, liều lượng phân bón..."
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl p-3 outline-none focus:border-brand-500 resize-none text-xs font-medium"></textarea>
                    @error('logDesc') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ảnh thực địa minh chứng</label>
                    <input type="file" wire:model="logPhoto" accept="image/*"
                        class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">
                    <div wire:loading wire:target="logPhoto" class="text-[11px] text-brand-700 mt-1">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang tải ảnh lên...
                    </div>
                    @if ($logPhoto && ! $errors->has('logPhoto'))
                    <img src="{{ $logPhoto->temporaryUrl() }}"
                        class="mt-2 w-20 h-20 object-cover rounded-xl border border-gray-200" alt="Xem trước ảnh">
                    @endif
                    @error('logPhoto') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="saveLog,logPhoto"
                    class="w-full mt-2 bg-brand-700 hover:bg-brand-800 disabled:opacity-60 text-white font-bold py-3 rounded-2xl shadow-sm transition active:scale-95 text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer">
                    <span wire:loading.remove wire:target="saveLog"><i class="fa-solid fa-fingerprint mr-1"></i> Lưu Tức
                        Thì &amp; Băm Hash</span>
                    <span wire:loading wire:target="saveLog"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang
                        lưu...</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- ================= MODAL THU HOẠCH & TẠO LÔ KHO MỚI ================= --}}
    @if (!empty($showHarvestModal) && $showHarvestModal)
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
                <button wire:click="$set('showHarvestModal', false)" type="button"
                    class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="saveHarvest" class="space-y-3.5 sm:space-y-4 text-xs sm:text-sm">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Mã Lô Hàng Mới (Tự sinh)</label>
                    <input type="text" wire:model="harvestBatchCode" readonly
                        class="w-full bg-gray-100 border border-gray-200 rounded-2xl px-3.5 py-2.5 font-mono font-bold text-blue-800 text-xs outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Ngày thu hái <span
                                class="text-red-500">*</span></label>
                        <input type="date" wire:model="harvestDate"
                            class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-blue-600 font-mono text-xs">
                        @error('harvestDate') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Hạn dùng (FEFO) <span
                                class="text-red-500">*</span></label>
                        <input type="date" wire:model="harvestExpiryDate"
                            class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-blue-600 font-mono text-xs">
                        @error('harvestExpiryDate') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}
                        </p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Sản lượng nhập kho (Kg) <span
                            class="text-red-500">*</span></label>
                    <input type="number" wire:model="harvestQuantity" step="0.01" min="0.01" placeholder="Nhập số kg..."
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-3.5 py-2.5 outline-none focus:border-blue-600 font-bold font-mono text-blue-700 text-base">
                    @error('harvestQuantity') <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ảnh thu hoạch thực tế <span
                            class="text-red-500">*</span></label>
                    <input type="file" wire:model="harvestPhoto" accept="image/*"
                        class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 cursor-pointer">
                    <div wire:loading wire:target="harvestPhoto" class="text-[11px] text-blue-600 mt-1">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang tải ảnh...
                    </div>
                    @if ($harvestPhoto && ! $errors->has('harvestPhoto'))
                    <img src="{{ $harvestPhoto->temporaryUrl() }}"
                        class="mt-2 w-20 h-20 object-cover rounded-xl border border-blue-200 shadow-xs"
                        alt="Xem trước ảnh thu hoạch">
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
                            <span class="font-bold text-brand-800 block">Tiếp tục nuôi cây (Kế thừa lô cũ)</span>
                            <span class="text-[11px] text-gray-500 block mt-0.5 leading-relaxed">Cây vẫn ở trên luống.
                                Lô hàng mới kế thừa toàn bộ nhật ký trước đó.</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="radio" wire:model="harvestMode" value="finish"
                            class="w-4 h-4 text-red-600 accent-red-600 mt-0.5">
                        <div class="text-xs">
                            <span class="font-bold text-red-800 block">Dọn luống kết thúc vụ</span>
                            <span class="text-[11px] text-gray-500 block mt-0.5 leading-relaxed">Luống đất sẽ chuyển
                                thành Đất trống để chuẩn bị xuống giống mới.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveHarvest,harvestPhoto"
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-bold py-3 rounded-2xl shadow-sm transition active:scale-95 text-xs sm:text-sm flex justify-center items-center gap-2 cursor-pointer">
                        <span wire:loading.remove wire:target="saveHarvest"><i class="fa-solid fa-box"></i> Lưu Thu
                            Hoạch & Tạo Lô Kho</span>
                        <span wire:loading wire:target="saveHarvest"><i class="fa-solid fa-spinner fa-spin"></i> Đang xử
                            lý...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ================= MODAL IN TEM NHIỆT QR 70x80 ================= --}}
    @if (!empty($showPrintQrModal) && $showPrintQrModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4">
        <div class="bg-white rounded-3xl w-full max-w-[320px] shadow-2xl p-5 sm:p-6 text-center border border-gray-100">
            <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tem Nhiệt Dán Thùng 70x80</span>
                <button wire:click="$set('showPrintQrModal', false)" type="button"
                    class="text-gray-400 hover:text-red-500 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div id="printable-thermal-label"
                class="p-4 border-2 border-dashed border-brand-500 rounded-2xl bg-brand-50/20 mb-5">
                <div
                    class="flex items-center justify-center gap-1.5 text-brand-900 border-b border-brand-200 pb-1.5 mb-2">
                    <i class="fa-solid fa-leaf text-brand-600"></i>
                    <span class="font-extrabold text-[11px] tracking-wider uppercase">ĐẤT LÀNH FARM</span>
                </div>
                <p class="font-bold text-sm text-gray-900 mb-2 leading-tight">
                    {{ $printBatchCropName ?? 'Nông Sản Sạch' }}</p>
                <div
                    class="w-32 h-32 mx-auto bg-white border border-gray-200 rounded-xl p-1.5 mb-2 shadow-xs flex items-center justify-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ $printBatchCode ?? 'FARM-BATCH' }}"
                        class="w-full h-full object-contain" alt="QR Code Lô">
                </div>
                <div
                    class="text-[10px] text-gray-600 font-mono leading-tight space-y-0.5 border-t border-brand-200 pt-2">
                    <p class="font-bold text-gray-900">MÃ LÔ: {{ $printBatchCode ?? '—' }}</p>
                    <p>Thu: {{ $printBatchDate ?? '—' }} • HSD: {{ $printBatchExp ?? '—' }}</p>
                </div>
                <p class="text-[8px] text-gray-400 italic mt-1.5">Quét mã QR để xem nhật ký nguồn gốc</p>
            </div>
            <button onclick="window.print()"
                class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 sm:py-3 rounded-2xl transition active:scale-95 flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer">
                <i class="fa-solid fa-print"></i> In Tem Dán Bao Bì
            </button>
        </div>
    </div>
    @endif
</div>