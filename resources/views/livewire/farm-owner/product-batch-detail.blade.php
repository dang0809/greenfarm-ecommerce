<x-farm-owner.page :title="'Lô ' . $batch->batch_code" :back="route('farm-owner.batches')">
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

    <section id="inventory-logs" class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
        <h2 class="font-extrabold">Nhật ký biến động lô</h2>

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

    <section id="cultivation-logs" class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
        <h2 class="font-extrabold">Nhật ký canh tác</h2>

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
                    <a href="{{ $eventImages[$event->id] }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ $eventImages[$event->id] }}" alt="Ảnh nhật ký canh tác" loading="lazy"
                            class="max-h-64 rounded-xl object-contain">
                    </a>
                @endif
            </article>
        @empty
            <p class="text-sm text-slate-500">Chưa có nhật ký canh tác.</p>
        @endforelse

        {{ $events->links(data: ['scrollTo' => '#cultivation-logs']) }}
    </section>
</x-farm-owner.page>
