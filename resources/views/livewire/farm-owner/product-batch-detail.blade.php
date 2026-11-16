<div x-data="{
    activeLogTab: 'inventory',

    activateLogTab(tab) {
        this.activeLogTab = tab;

        this.$nextTick(() => {
            this.$refs[tab + 'Tab'].focus();
        });
    }
}" class="flex flex-1 min-h-0 min-w-0 flex-col overflow-hidden">
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center gap-3 px-4 sm:px-8 shrink-0">
        <button type="button" onclick="toggleSidebar()" aria-label="Mở menu"
            class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <a href="{{ route('farm-owner.batches') }}" wire:navigate class="text-brand-700 text-sm font-bold shrink-0">
            <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i>
            Quay lại
        </a>

        <h1 class="text-lg sm:text-2xl font-black text-slate-900 truncate">
            Lô {{ $batch->batch_code }}
        </h1>
    </header>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-5 custom-scrollbar">
        <section class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft space-y-3">
            <h2 class="font-extrabold text-lg">
                {{ $batch->plantingBatch?->product?->name ?? '—' }}
            </h2>

            <p class="text-sm text-slate-600">
                Vụ: {{ $batch->plantingBatch?->planting_code ?? '—' }}
                · Luống: {{ $batch->plantingBatch?->farmPlot?->name ?? '—' }}
            </p>

            <p class="text-sm text-slate-600">
                Gieo: {{ $batch->plantingBatch?->planted_date?->format('d/m/Y') ?? '—' }}
                · Thu hoạch: {{ $batch->manufactured_date?->format('d/m/Y') ?? '—' }}
                · HSD: {{ $batch->expiry_date?->format('d/m/Y') ?? '—' }}
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <p>Nhập ban đầu: <b>{{ $batch->initial_quantity }}</b></p>
                <p>Tồn hiện tại: <b>{{ $batch->current_quantity }}</b></p>
                <p>Đang giữ chỗ: <b>{{ $batch->reserved_quantity }}</b></p>
                <p>Đã hao hụt: <b>{{ $batch->spoiled_quantity }}</b></p>
            </div>

            <p class="text-xs text-slate-500">
                Đơn vị: {{ $batch->plantingBatch?->product?->unit ?? '—' }}
            </p>
        </section>

        {{-- Thanh chuyển tab nhật ký --}}
        <div role="tablist" aria-label="Nhật ký lô hàng"
            x-on:keydown.right.prevent="
        activateLogTab(activeLogTab === 'inventory' ? 'cultivation' : 'inventory')
    "
            x-on:keydown.left.prevent="
        activateLogTab(activeLogTab === 'inventory' ? 'cultivation' : 'inventory')
    "
            x-on:keydown.home.prevent="activateLogTab('inventory')"
            x-on:keydown.end.prevent="activateLogTab('cultivation')"
            class="grid grid-cols-2 gap-2 rounded-2xl border border-emerald-200
        bg-white p-2 shadow-sm">

            {{-- Tab nhật ký biến động lô --}}
            <button type="button" id="inventory-tab" x-ref="inventoryTab" role="tab" aria-controls="inventory-logs"
                x-bind:aria-selected="activeLogTab === 'inventory'"
                x-bind:tabindex="activeLogTab === 'inventory' ? 0 : -1" x-on:click="activateLogTab('inventory')"
                x-bind:class="activeLogTab === 'inventory'
                    ?
                    'bg-emerald-700 text-white shadow-sm' :
                    'bg-white text-slate-600 hover:bg-emerald-50 hover:text-emerald-800'"
                class="flex min-w-0 cursor-pointer items-center justify-center
            gap-2 rounded-xl px-3 py-3 text-xs font-bold transition-colors
            sm:text-sm focus-visible:outline-2 focus-visible:outline-offset-2
            focus-visible:outline-emerald-600">

                <i class="fa-solid fa-arrows-rotate hidden sm:inline" aria-hidden="true"></i>

                <span class="leading-snug">Nhật ký biến động lô</span>
            </button>

            {{-- Tab nhật ký canh tác --}}
            <button type="button" id="cultivation-tab" x-ref="cultivationTab" role="tab"
                aria-controls="cultivation-logs" x-bind:aria-selected="activeLogTab === 'cultivation'"
                x-bind:tabindex="activeLogTab === 'cultivation' ? 0 : -1" x-on:click="activateLogTab('cultivation')"
                x-bind:class="activeLogTab === 'cultivation'
                    ?
                    'bg-emerald-700 text-white shadow-sm' :
                    'bg-white text-slate-600 hover:bg-emerald-50 hover:text-emerald-800'"
                class="flex min-w-0 cursor-pointer items-center justify-center
            gap-2 rounded-xl px-3 py-3 text-xs font-bold transition-colors
            sm:text-sm focus-visible:outline-2 focus-visible:outline-offset-2
            focus-visible:outline-emerald-600">

                <i class="fa-solid fa-seedling hidden sm:inline" aria-hidden="true"></i>

                <span class="leading-snug">Nhật ký canh tác</span>
            </button>
        </div>

        <section id="inventory-logs" role="tabpanel" aria-labelledby="inventory-tab" tabindex="0" x-cloak
            x-show="activeLogTab === 'inventory'"
            class="overflow-hidden rounded-3xl border border-slate-200
        bg-white shadow-soft">

            {{-- Tiêu đề --}}
            <div
                class="flex flex-wrap items-center justify-between gap-2
        border-b border-slate-100 bg-slate-50 px-4 py-4 sm:px-6">

                <h2 class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
                    <i class="fa-solid fa-arrow-right-arrow-left text-brand-700" aria-hidden="true"></i>
                    Lịch sử biến động kho
                </h2>

                <span
                    class="rounded-full border border-brand-200 bg-brand-50
            px-2.5 py-1 text-xs font-bold text-brand-800">
                    {{ number_format($logs->total(), 0, ',', '.') }} bản ghi
                </span>
            </div>

            {{-- Danh sách biến động --}}
            <div class="space-y-3 p-4 sm:p-6">
                @forelse ($logs as $log)
                    @php
                        $display = $logDisplays[$log->id];

                        $isDecrease = in_array($log->type, ['export_sale', 'export_spoilage', 'adjustment_out'], true);

                        $logColor = $isDecrease ? 'text-rose-600' : 'text-brand-700';
                    @endphp

                    <article wire:key="owner-stock-log-{{ $log->id }}"
                        class="flex min-w-0 flex-col gap-3 rounded-2xl
                    border border-slate-200 bg-white p-4
                    transition-colors hover:border-slate-300
                    sm:flex-row sm:items-start sm:justify-between">

                        {{-- Nội dung bên trái --}}
                        <div class="min-w-0 flex-1 space-y-1.5">
                            <h3 class="text-xs font-extrabold sm:text-sm {{ $logColor }}">
                                Thao tác: {{ $display['label'] }}
                            </h3>

                            @if ($display['transition'])
                                <p
                                    class="break-words text-xs font-medium
                            leading-relaxed text-slate-700">
                                    Trạng thái:
                                    <span class="font-bold">
                                        {{ $display['transition'] }}
                                    </span>
                                </p>
                            @endif

                            @if ($display['stockChange'])
                                <p
                                    class="break-words text-xs font-medium
                            leading-relaxed text-slate-700">
                                    Tồn kho:
                                    <span class="font-bold">
                                        {{ $display['stockChange'] }}
                                        {{ $batch->plantingBatch?->product?->unit }}
                                    </span>
                                </p>
                            @endif

                            @if ($display['expiryChange'])
                                <p
                                    class="break-words text-xs font-medium
                            leading-relaxed text-slate-700">
                                    HSD:
                                    <span class="font-bold">
                                        {{ $display['expiryChange'] }}
                                    </span>
                                </p>
                            @endif

                            <p
                                class="whitespace-pre-line break-words text-xs font-medium leading-relaxed text-slate-700">
                                Nội dung: {{ $display['reason'] }}</p>

                            <p
                                class="pt-1 font-mono text-[10px]
                        leading-relaxed text-slate-500 sm:text-[11px]">
                                {{ $log->created_at?->format('d/m/Y H:i') ?? '—' }}
                                <span class="mx-1">·</span>
                                {{ $log->creator?->name ?? '—' }}
                            </p>
                        </div>

                        {{-- Số lượng bên phải --}}
                        <div class="shrink-0 self-end text-right sm:self-start">
                            <span class="block text-[10px] font-medium text-slate-500">
                                Biến động
                            </span>

                            <span
                                class="mt-1 block font-mono text-sm
                        font-black tabular-nums {{ $logColor }}">
                                {{ $display['quantityText'] }}

                                @if ($log->quantity !== 0)
                                    {{ $batch->plantingBatch?->product?->unit }}
                                @endif
                            </span>
                        </div>
                    </article>
                @empty
                    <div
                        class="rounded-2xl border border-dashed border-slate-200
                bg-slate-50 px-4 py-8 text-center">
                        <i class="fa-solid fa-box-open mb-2 text-xl text-slate-400" aria-hidden="true"></i>
                        <p class="text-xs text-slate-500">
                            Chưa có biến động kho.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 bg-slate-50 px-4 py-3 sm:px-6">
                {{ $logs->links(data: ['scrollTo' => '#inventory-logs']) }}
            </div>
        </section>

        <section id="cultivation-logs" role="tabpanel" aria-labelledby="cultivation-tab" tabindex="0" x-cloak
            x-show="activeLogTab === 'cultivation'"
            class="overflow-hidden rounded-3xl border border-slate-200
        bg-white shadow-soft">

            {{-- Tiêu đề --}}
            <div
                class="flex flex-wrap items-center justify-between gap-2
        border-b border-slate-100 bg-slate-50 px-4 py-4 sm:px-6">

                <h2 class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
                    <i class="fa-solid fa-seedling text-brand-700" aria-hidden="true"></i>
                    Nhật ký canh tác gốc
                </h2>

                <span
                    class="rounded-full border border-brand-200 bg-brand-50
            px-2.5 py-1 text-xs font-bold text-brand-800">
                    {{ number_format($events->total(), 0, ',', '.') }} hoạt động
                </span>
            </div>

            <div class="p-4 sm:p-6">
                {{-- Đường timeline --}}
                <div
                    class="relative space-y-4
            before:absolute before:inset-y-4 before:left-[7px]
            before:w-0.5 before:bg-brand-100">

                    @forelse ($events as $event)
                        <article wire:key="owner-farm-event-{{ $event->id }}"
                            class="relative min-w-0 pl-7 sm:pl-8">

                            {{-- Chấm trên timeline --}}
                            <span aria-hidden="true"
                                class="absolute left-0 top-4 h-4 w-4
                            rounded-full border-2 border-white
                            bg-brand-600 shadow-sm">
                            </span>

                            <div
                                class="min-w-0 rounded-2xl border border-slate-200
                        bg-slate-50/60 p-4">

                                <h3
                                    class="break-words text-xs font-extrabold
                            leading-relaxed text-slate-900 sm:text-sm">
                                    {{ $event->event_name }}
                                </h3>

                                @if (filled($event->description))
                                    <p
                                        class="mt-1.5 whitespace-pre-line break-words text-xs leading-relaxed text-slate-600">
                                        {{ $event->description }}</p>
                                @endif

                                <p
                                    class="mt-2 font-mono text-[10px]
                            leading-relaxed text-slate-500 sm:text-[11px]">
                                    {{ $event->recorded_at?->format('d/m/Y H:i') ?? '—' }}
                                    <span class="mx-1">·</span>
                                    {{ $event->recorded_by ?: '—' }}
                                </p>

                                {{-- Ảnh minh chứng --}}
                                @if ($eventImages[$event->id])
                                    <button type="button"
                                        x-on:click="$dispatch('owner-preview-image', {
                                    src: @js($eventImages[$event->id]),
                                    title: 'Ảnh nhật ký canh tác'
                                })"
                                        aria-label="Phóng to ảnh nhật ký canh tác"
                                        class="mt-3 block max-w-full cursor-zoom-in
                                    overflow-hidden rounded-xl
                                    border border-slate-200 bg-white p-1
                                    shadow-sm focus-visible:outline-2
                                    focus-visible:outline-offset-2
                                    focus-visible:outline-brand-700">

                                        <img src="{{ $eventImages[$event->id] }}" alt="Ảnh nhật ký canh tác"
                                            loading="lazy"
                                            class="h-auto max-h-48 w-auto max-w-full
                                        rounded-lg object-contain sm:max-h-64">
                                    </button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div
                            class="relative rounded-2xl border border-dashed
                    border-slate-200 bg-slate-50 px-4 py-8 text-center">
                            <i class="fa-solid fa-seedling mb-2 text-xl text-slate-400" aria-hidden="true"></i>
                            <p class="text-xs text-slate-500">
                                Chưa có nhật ký canh tác.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50 px-4 py-3 sm:px-6">
                {{ $events->links(data: ['scrollTo' => '#cultivation-logs']) }}
            </div>
        </section>
    </div>
</div>
