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
            class="bg-white p-5 rounded-3xl border border-slate-200/80
        shadow-soft space-y-4">
            @forelse ($logs as $log)
                <article wire:key="owner-stock-log-{{ $log->id }}"
                    class="p-4 rounded-2xl border border-slate-100 space-y-2 text-sm">
                    <div class="flex justify-between gap-3 font-bold">
                        <span>{{ $logDisplays[$log->id]['label'] }}</span>
                        <span>{{ $logDisplays[$log->id]['quantityText'] }}</span>
                    </div>

                    @if ($logDisplays[$log->id]['transition'])
                        <p>Trạng thái: {{ $logDisplays[$log->id]['transition'] }}</p>
                    @endif

                    @if ($logDisplays[$log->id]['stockChange'])
                        <p>Tồn kho: {{ $logDisplays[$log->id]['stockChange'] }}</p>
                    @endif

                    @if ($logDisplays[$log->id]['expiryChange'])
                        <p>HSD: {{ $logDisplays[$log->id]['expiryChange'] }}</p>
                    @endif

                    <p class="whitespace-pre-line text-slate-600">{{ $logDisplays[$log->id]['reason'] }}</p>

                    <p class="text-xs text-slate-400">
                        {{ $log->created_at?->format('d/m/Y H:i') }}
                        · {{ $log->creator?->name ?? '—' }}
                    </p>
                </article>
            @empty
                <p class="text-sm text-slate-500">Chưa có biến động lô.</p>
            @endforelse

            {{ $logs->links(data: ['scrollTo' => '#inventory-logs']) }}
        </section>

        <section id="cultivation-logs" role="tabpanel" aria-labelledby="cultivation-tab" tabindex="0" x-cloak
            x-show="activeLogTab === 'cultivation'"
            class="bg-white p-5 rounded-3xl border border-slate-200/80
        shadow-soft space-y-4">
            @forelse ($events as $event)
                <article wire:key="owner-farm-event-{{ $event->id }}"
                    class="p-4 rounded-2xl border border-slate-100 space-y-2">
                    <h3 class="font-bold">{{ $event->event_name }}</h3>
                    <p class="text-sm text-slate-600 whitespace-pre-line">{{ $event->description }}</p>

                    <p class="text-xs text-slate-400">
                        {{ $event->recorded_at?->format('d/m/Y H:i') }}
                        · {{ $event->recorded_by }}
                    </p>

                    @if ($eventImages[$event->id])
                        <button type="button"
                            x-on:click="$dispatch('owner-preview-image', {
        src: @js($eventImages[$event->id]),
        title: 'Ảnh nhật ký canh tác'
    })"
                            aria-label="Phóng to ảnh nhật ký canh tác"
                            class="block cursor-zoom-in rounded-xl
        focus-visible:outline-2 focus-visible:outline-brand-700">
                            <img src="{{ $eventImages[$event->id] }}" alt="Ảnh nhật ký canh tác" loading="lazy"
                                class="max-h-64 rounded-xl object-contain">
                        </button>
                    @endif
                </article>
            @empty
                <p class="text-sm text-slate-500">Chưa có nhật ký canh tác.</p>
            @endforelse

            {{ $events->links(data: ['scrollTo' => '#cultivation-logs']) }}
        </section>
    </div>
</div>
