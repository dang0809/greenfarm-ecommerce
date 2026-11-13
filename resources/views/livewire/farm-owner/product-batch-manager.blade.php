<div class="flex flex-1 min-h-0 min-w-0 flex-col overflow-hidden">
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center gap-3 px-4 sm:px-8 shrink-0">
        <button type="button" onclick="toggleSidebar()" aria-label="Mở menu"
            class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <h1 class="text-lg sm:text-2xl font-black text-slate-900 truncate">
            Danh sách lô hàng nông sản
        </h1>
    </header>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-5 custom-scrollbar">
        <input type="search" wire:model.live.debounce.300ms="search"
            placeholder="Tìm mã lô, mã vụ, sản phẩm hoặc luống..."
            class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-brand-600">

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="p-4">Lô / Vụ trồng</th>
                            <th class="p-4">Sản phẩm / Luống</th>
                            <th class="p-4">Thu hoạch / HSD</th>
                            <th class="p-4">Tồn / Giữ chỗ</th>
                            <th class="p-4">Trạng thái</th>
                            <th class="p-4"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($batches as $batch)
                            <tr wire:key="owner-batch-{{ $batch->id }}" class="hover:bg-slate-50">
                                <td class="p-4">
                                    <b>{{ $batch->batch_code }}</b>
                                    <p class="text-xs text-slate-500">
                                        {{ $batch->plantingBatch?->planting_code }}
                                    </p>
                                </td>
                                <td class="p-4">
                                    {{ $batch->plantingBatch?->product?->name ?? '—' }}
                                    <p class="text-xs text-slate-500">
                                        {{ $batch->plantingBatch?->farmPlot?->name ?? '—' }}
                                    </p>
                                </td>
                                <td class="p-4">
                                    {{ $batch->manufactured_date?->format('d/m/Y') }}
                                    <p class="text-xs text-slate-500">
                                        {{ $batch->expiry_date?->format('d/m/Y') }}
                                    </p>
                                </td>
                                <td class="p-4">
                                    {{ $batch->current_quantity }} / {{ $batch->reserved_quantity }}
                                    {{ $batch->plantingBatch?->product?->unit }}
                                </td>
                                <td class="p-4">
                                    {{ $statusLabels[$batch->display_status] ?? $batch->display_status }}
                                </td>
                                <td class="p-4">
                                    <a href="{{ route('farm-owner.batches.show', $batch->id) }}" wire:navigate
                                        class="inline-flex bg-brand-50 text-brand-700 px-3 py-2 rounded-xl font-bold text-xs">
                                        Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">
                                    Không tìm thấy lô hàng.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">{{ $batches->links() }}</div>
        </div>
    </div>
</div>
