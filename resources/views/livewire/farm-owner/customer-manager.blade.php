<div class="flex flex-1 min-h-0 min-w-0 flex-col overflow-hidden">
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center gap-3 px-4 sm:px-8 shrink-0">
        <button type="button" onclick="toggleSidebar()" aria-label="Mở menu"
            class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <h1 class="text-lg sm:text-2xl font-black text-slate-900 truncate">
            Danh sách khách hàng
        </h1>
    </header>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-5 custom-scrollbar">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Tìm tên, email hoặc số điện thoại..."
            class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-brand-600">

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="p-4">Khách hàng</th>
                            <th class="p-4">Điện thoại</th>
                            <th class="p-4">Điểm tích lũy</th>
                            <th class="p-4">Số đơn</th>
                            <th class="p-4">Gói đang hoạt động</th>
                            <th class="p-4">Trạng thái</th>
                            <th class="p-4">Ngày tham gia</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($customers as $customer)
                            <tr wire:key="owner-customer-{{ $customer->id }}" class="hover:bg-slate-50">
                                <td class="p-4">
                                    <b>{{ $customer->name }}</b>
                                    <p class="text-xs text-slate-500">{{ $customer->email }}</p>
                                </td>
                                <td class="p-4">{{ $customer->phone ?: '—' }}</td>
                                <td class="p-4 font-bold text-brand-700">
                                    {{ number_format($customer->reward_points, 0, ',', '.') }}
                                </td>
                                <td class="p-4">{{ $customer->orders_count }}</td>
                                <td class="p-4">{{ $customer->active_subscriptions_count }}</td>
                                <td class="p-4">
                                    {{ [
                                        'active' => 'Hoạt động',
                                        'blocked' => 'Bị khóa',
                                        'inactive' => 'Chưa xác thực',
                                    ][$customer->status] ?? $customer->status }}
                                </td>
                                <td class="p-4">{{ $customer->created_at?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500">
                                    Không tìm thấy khách hàng.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
