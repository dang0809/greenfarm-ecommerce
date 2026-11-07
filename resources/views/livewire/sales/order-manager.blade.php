<div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">
    <!-- ================= 1. HEADER ================= -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5 truncate">
                    <span class="text-brand-700">Nông Trại Đất Lành</span>
                </div>
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">Quản Lý Đơn Hàng</h1>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-700"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </header>

    <!-- ================= 2. NỘI DUNG CUỘN ================= -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-5 lg:p-6 space-y-4 sm:space-y-5 custom-scrollbar pb-28 sm:pb-12 h-[calc(100dvh-5rem)]">

        <!-- ================= TOOLBAR: TÌM KIẾM, BỘ LỌC & TABS ================= -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden space-y-4">
            
            <!-- Hàng Tab Trạng Thái Đơn Hàng -->
            <div class="flex overflow-x-auto border-b border-gray-100 px-4 sm:px-6 pt-3 gap-6 text-sm no-scrollbar bg-gray-50/40">
                <button type="button" wire:click="setStatusFilter('all')" class="pb-3 {{ $statusFilter === 'all' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Tất cả</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full">{{ $statusCounts['all'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('pending')" class="pb-3 {{ $statusFilter === 'pending' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Chờ xác nhận</span>
                    <span class="text-xs font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">{{ $statusCounts['pending'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('processing')" class="pb-3 {{ $statusFilter === 'processing' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer" title="Đã chuẩn bị xong hàng, chờ xuất kho giao">
                    <span>Chờ lấy hàng</span>
                    <span class="text-xs font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">{{ $statusCounts['processing'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('shipped')" class="pb-3 {{ $statusFilter === 'shipped' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đang giao</span>
                    <span class="text-xs font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">{{ $statusCounts['shipped'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('delivered')" class="pb-3 {{ $statusFilter === 'delivered' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đã giao</span>
                    <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">{{ $statusCounts['delivered'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('cancelled')" class="pb-3 {{ $statusFilter === 'cancelled' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đã hủy</span>
                    <span class="text-xs font-bold bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full">{{ $statusCounts['cancelled'] }}</span>
                </button>
            </div>

            <!-- Thanh Tìm Kiếm, Bộ Lọc & Nút Tạo Đơn Mới -->
            <div class="p-3.5 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 flex-1">
                    <!-- Ô tìm kiếm -->
                    <div class="relative w-full sm:w-72 md:w-80">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               placeholder="Tìm mã đơn, tên khách, số điện thoại..." 
                               class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                    </div>

                    <!-- Lọc theo loại đơn hàng CSDL -->
                    <select wire:model.live="typeFilter" class="w-full sm:w-auto bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 transition-all cursor-pointer">
                        <option value="">-- Tất cả loại đơn hàng --</option>
                        <option value="retail">Đơn mua lẻ thông thường</option>
                        <option value="subscription_delivery">Gói rau giao định kỳ</option>
                    </select>

                    <button type="button" wire:click="resetFilters" class="self-end sm:self-auto p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 transition shadow-xs cursor-pointer" title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= BẢNG DANH SÁCH ĐƠN HÀNG ================= -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden">
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-gray-100">
                            <th class="py-3 sm:py-3.5 px-3 sm:px-4 pl-4 sm:pl-6 whitespace-nowrap">Mã & Thời Gian</th>
                            <th class="py-3 sm:py-3.5 px-3 sm:px-4">Khách Hàng & Giao Nhận</th>
                            <th class="py-3 sm:py-3.5 px-3 sm:px-4 text-right whitespace-nowrap">Tổng Tiền</th>
                            <th class="py-3 sm:py-3.5 px-3 sm:px-4 text-center whitespace-nowrap">Trạng Thái</th>
                            <th class="py-3 sm:py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50/60 transition">
                                <!-- Mã đơn, Loại & Thời gian -->
                                <td class="py-3 sm:py-3.5 px-3 sm:px-4 pl-4 sm:pl-6 whitespace-nowrap">
                                    <button type="button" wire:click="openOrderDetail({{ $order->id }})" class="font-mono font-extrabold text-brand-800 hover:text-brand-950 hover:underline flex items-center gap-1.5 cursor-pointer">
                                        <span>#{{ ltrim($order->order_code, '#') }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-gray-400"></i>
                                    </button>
                                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                        @if($order->order_type === 'subscription_delivery')
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                <i class="fa-solid fa-crown text-[8px]"></i> Gói VIP {{ $order->deliverySchedule ? "(Đợt {$order->deliverySchedule->delivery_order}/8)" : '' }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                <i class="fa-solid fa-basket-shopping text-[8px]"></i> Mua Lẻ
                                            </span>
                                        @endif
                                        @if($order->items->contains(fn($i) => empty($i->batch_id)))
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 animate-pulse">
                                                <i class="fa-solid fa-triangle-exclamation text-[8px]"></i> Thiếu kho
                                            </span>
                                        @endif
                                        <span class="text-[11px] font-mono text-gray-500" title="{{ $order->created_at->format('H:i d/m/Y') }}">
                                            {{ $order->created_at->format('H:i • d/m') }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Khách hàng & Địa chỉ giao nhận (Tích hợp khoảng cách) -->
                                <td class="py-3 sm:py-3.5 px-3 sm:px-4 min-w-[180px] max-w-xs sm:max-w-sm">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-gray-900">{{ $order->recipient_name }}</span>
                                        <span class="text-xs text-gray-500 font-mono">({{ $order->recipient_phone }})</span>
                                        @if($order->shipping_distance_km > 0)
                                            <span class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-brand-700 bg-brand-50 border border-brand-200 px-1.5 py-0.2 rounded font-mono" title="Khoảng cách giao hàng">
                                                <i class="fa-solid fa-route text-[9px]"></i> {{ $order->shipping_distance_km }} km
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] sm:text-xs text-gray-500 truncate mt-0.5" title="{{ $order->recipient_address }}">
                                        <i class="fa-solid fa-location-dot text-[10px] text-gray-400 mr-0.5"></i>
                                        {{ $order->recipient_address }}
                                    </p>
                                </td>

                                <!-- Tổng tiền & Thanh toán -->
                                <td class="py-3 sm:py-3.5 px-3 sm:px-4 text-right whitespace-nowrap">
                                    <p class="font-bold font-mono text-gray-900 text-sm sm:text-base">{{ number_format($order->total, 0, ',', '.') }}đ</p>
                                    @php
                                        $payStatus = $order->payment?->status ?? 'pending';
                                        $isPaid = ($payStatus === 'success') || $order->total == 0;
                                        $payMethod = strtoupper($order->payment?->method ?: 'PAYOS');
                                    @endphp
                                    <div class="mt-0.5">
                                        @if($isPaid)
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                                                ĐÃ THANH TOÁN ({{ $payMethod }})
                                            </span>
                                        @elseif($payStatus === 'failed')
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-mono">
                                                TT THẤT BẠI
                                            </span>
                                        @else
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-mono">
                                                CHỜ THANH TOÁN ({{ $payMethod }})
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Trạng thái đơn hàng -->
                                <td class="py-3 sm:py-3.5 px-3 sm:px-4 text-center whitespace-nowrap">
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>Chờ xác nhận</span>
                                            </span>
                                            @break
                                        @case('processing')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-900 border border-amber-300">
                                                <i class="fa-solid fa-boxes-packing text-[10px] text-amber-700"></i>
                                                <span>Chờ lấy hàng</span>
                                            </span>
                                            @break
                                        @case('shipped')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fa-solid fa-truck-fast text-[10px]"></i>
                                                <span>Đang giao</span>
                                            </span>
                                            @break
                                        @case('delivered')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="fa-solid fa-circle-check text-[10px]"></i>
                                                <span>Đã giao</span>
                                            </span>
                                            @break
                                        @case('cancelled')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <i class="fa-solid fa-ban text-[10px]"></i>
                                                <span>Đã hủy</span>
                                            </span>
                                            @break
                                        @default
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                                {{ $order->status }}
                                            </span>
                                    @endswitch
                                </td>

                                <!-- Thao tác -->
                                <td class="py-3 sm:py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <!-- Xem chi tiết -->
                                        <button type="button" 
                                                wire:click="openOrderDetail({{ $order->id }})" 
                                                class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition cursor-pointer" 
                                                title="Xem chi tiết đơn hàng">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </button>

                                        <!-- Nút thao tác nhanh theo trạng thái -->
                                        @if($order->status === 'pending')
                                            <button type="button" 
                                                    wire:click="approveOrder({{ $order->id }})" 
                                                    class="p-2 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 transition cursor-pointer" 
                                                    title="Xác nhận & Chuẩn bị xong">
                                                <i class="fa-solid fa-check text-xs"></i>
                                            </button>
                                        @elseif($order->status === 'processing')
                                            <button type="button" 
                                                    wire:click="dispatchOrder({{ $order->id }})" 
                                                    class="p-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition cursor-pointer" 
                                                    title="Xuất kho đi giao">
                                                <i class="fa-solid fa-truck-fast text-xs"></i>
                                            </button>
                                        @elseif($order->status === 'shipped')
                                            <button type="button" 
                                                    wire:click="completeDelivery({{ $order->id }})" 
                                                    class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer" 
                                                    title="Xác nhận đã giao thành công">
                                                <i class="fa-solid fa-circle-check text-xs"></i>
                                            </button>
                                        @endif

                                        <!-- Nút Hủy đơn (Chỉ cho phép hủy khi còn ở nông trại) -->
                                        @if(in_array($order->status, ['pending', 'processing'], true))
                                            <button type="button" 
                                                    wire:click="openCancelModal({{ $order->id }})" 
                                                    class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer" 
                                                    title="Hủy đơn hàng">
                                                <i class="fa-solid fa-ban text-xs"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="w-16 h-16 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                                        <i class="fa-regular fa-folder-open"></i>
                                    </div>
                                    <p class="text-base font-bold text-gray-700">Không tìm thấy đơn hàng nào phù hợp</p>
                                    <p class="text-xs text-gray-400 mt-1">Vui lòng thay đổi từ khóa tìm kiếm hoặc điều chỉnh bộ lọc trạng thái.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân trang Livewire -->
            @if($orders->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ================= 4. MODAL CHI TIẾT ĐƠN HÀNG ================= -->
    @if($showDetailModal && $selectedOrder)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
            <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-4xl shadow-2xl p-5 sm:p-8 max-h-[90vh] overflow-y-auto custom-scrollbar border border-gray-100 space-y-4 sm:space-y-6">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 sm:pb-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                            <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 font-mono">#{{ ltrim($selectedOrder->order_code, '#') }}</h3>
                            
                            @if($selectedOrder->order_type === 'subscription_delivery')
                                <span class="text-[10px] sm:text-xs font-bold px-2 sm:px-2.5 py-0.5 rounded-full border bg-purple-50 text-purple-700 border-purple-200">Gói Định Kỳ VIP {{ $selectedOrder->deliverySchedule ? "(Đợt {$selectedOrder->deliverySchedule->delivery_order}/8)" : '' }}</span>
                            @else
                                <span class="text-[10px] sm:text-xs font-bold px-2 sm:px-2.5 py-0.5 rounded-full border bg-slate-100 text-slate-700 border-slate-200">Mua Lẻ</span>
                            @endif

                            @switch($selectedOrder->status)
                                @case('pending')
                                    <span class="text-[10px] sm:text-xs font-bold px-2 sm:px-2.5 py-0.5 rounded-full border bg-amber-50 text-amber-800 border-amber-200">Chờ xác nhận</span>
                                    @break
                                @case('processing')
                                    <span class="text-[10px] sm:text-xs font-bold px-2 sm:px-2.5 py-0.5 rounded-full border bg-amber-50 text-amber-900 border-amber-300">Chờ lấy hàng</span>
                                    @break
                                @case('shipped')
                                    <span class="text-[10px] sm:text-xs font-bold px-2 sm:px-2.5 py-0.5 rounded-full border bg-blue-50 text-blue-700 border-blue-200">Đang giao</span>
                                    @break
                                @case('delivered')
                                    <span class="text-[10px] sm:text-xs font-bold px-2 sm:px-2.5 py-0.5 rounded-full border bg-emerald-50 text-emerald-700 border-emerald-200">Đã giao</span>
                                    @break
                                @case('cancelled')
                                    <span class="text-[10px] sm:text-xs font-bold px-2 sm:px-2.5 py-0.5 rounded-full border bg-rose-50 text-rose-700 border-rose-200">Đã hủy</span>
                                    @break
                            @endswitch
                        </div>
                        <p class="text-[11px] sm:text-xs text-gray-400 mt-1 font-mono">
                            Thời gian đặt: <span>{{ $selectedOrder->created_at->format('H:i:s d/m/Y') }}</span>
                        </p>
                    </div>
                    <button type="button" wire:click="closeOrderDetail" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0 cursor-pointer">
                        <i class="fa-solid fa-xmark text-base sm:text-lg"></i>
                    </button>
                </div>

                <!-- THÔNG TIN NGƯỜI NHẬN & VẬN CHUYỂN -->
                <div class="bg-[#FAFAF8] rounded-xl sm:rounded-2xl border border-gray-200/80 p-4 sm:p-5 space-y-3 text-xs">
                    <div class="border-b border-gray-200/60 pb-2">
                        <h4 class="font-extrabold text-gray-900 uppercase tracking-wider text-[11px] flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-brand-700"></i> Thông Tin Người Nhận & Giao Hàng
                        </h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3.5 sm:gap-4">
                        <div>
                            <span class="text-gray-400 block mb-0.5">Người nhận hàng:</span>
                            <p class="font-bold text-gray-900 text-sm">{{ $selectedOrder->recipient_name }}</p>
                            <p class="font-mono text-gray-600 mt-0.5">{{ $selectedOrder->recipient_phone }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-gray-400 block mb-0.5">Địa chỉ nhận hàng:</span>
                            <p class="font-semibold text-gray-800 leading-snug">{{ $selectedOrder->recipient_address }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-0.5">Khoảng cách & Ship:</span>
                            <p class="font-bold text-gray-900 font-mono text-xs">
                                {{ $selectedOrder->shipping_distance_km ? $selectedOrder->shipping_distance_km . ' km' : 'Nội thành' }}
                                <span class="text-gray-400 font-normal">({{ number_format($selectedOrder->shipping_fee, 0, ',', '.') }}đ)</span>
                            </p>
                            <span class="text-[10px] text-gray-500 block mt-0.5">
                                {{ strtoupper($selectedOrder->payment?->method ?? 'PAYOS') }}
                            </span>
                        </div>
                    </div>

                    <!-- KHỐI VẬN CHUYỂN & SHIPPER PHỤ TRÁCH -->
                    @if(in_array($selectedOrder->status, ['processing', 'shipped', 'delivered']))
                        <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-gradient-to-r from-blue-50/80 to-indigo-50/70 border border-blue-200/80 text-blue-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                                    <i class="fa-solid fa-motorcycle"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-extrabold text-xs sm:text-sm text-blue-950">
                                            Shipper: Nguyễn Văn Bình
                                        </span>
                                        <span class="text-[10px] font-bold bg-white text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full font-mono">
                                            Đội xe hỏa tốc Đất Lành
                                        </span>
                                        <span class="text-[11px] text-blue-800 font-mono">
                                            <i class="fa-solid fa-phone text-[10px]"></i> 0938.445.566
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-blue-800 font-mono mt-0.5">
                                        Phương tiện: Xe máy thùng lạnh chuyên dụng • Biển số: 59-X1 889.24
                                    </p>
                                </div>
                            </div>
                            <div class="text-left sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-blue-200/60 font-mono">
                                <span class="text-[10px] text-blue-600 font-bold uppercase tracking-wider block">Mã vận đơn:</span>
                                <span class="text-xs font-black text-blue-900 mt-0.5 block">
                                    {{ $selectedOrder->tracking_code ?: ('SHIP-DL-' . date('Ymd', strtotime($selectedOrder->created_at)) . '-' . str_pad($selectedOrder->id, 4, '0', STR_PAD_LEFT)) }}
                                </span>
                            </div>
                        </div>
                    @endif

                    @if($selectedOrder->note)
                        <div class="bg-amber-50/70 p-3 rounded-xl border border-amber-200/80 text-amber-900 text-xs flex items-start gap-2">
                            <i class="fa-solid fa-circle-exclamation mt-0.5 text-amber-600 shrink-0"></i>
                            <div>
                                <span class="font-bold">Ghi chú của khách:</span>
                                <span class="italic">"{{ $selectedOrder->note }}"</span>
                            </div>
                        </div>
                    @endif

                    @if($selectedOrder->status === 'cancelled')
                        <div class="bg-rose-50 p-3 rounded-xl border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation mt-0.5 text-rose-600 shrink-0"></i>
                            <div>
                                <span class="font-bold">Lý do hủy đơn:</span>
                                <span class="italic">"{{ $selectedOrder->cancel_reason }}"</span>
                                <span class="block text-[10px] text-gray-500 font-mono mt-0.5">Thời gian hủy: {{ $selectedOrder->cancelled_at?->format('H:i d/m/Y') }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- BẢNG NÔNG SẢN & LÔ FEFO -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-extrabold text-gray-900 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fa-solid fa-boxes-stacked text-brand-700"></i> Danh Sách Nông Sản & Lô Xuất Kho FEFO
                            </h4>
                            <p class="text-[11px] text-gray-400">Số lượng kiện/hộp xuất kho trực tiếp theo nguyên tắc hạn gần xuất trước (FEFO).</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl sm:rounded-2xl border border-gray-200/80 bg-white custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-200">
                                    <th class="py-3 px-4">Sản Phẩm Đặt Mua</th>
                                    <th class="py-3 px-4 text-center">Số Lượng</th>
                                    <th class="py-3 px-4 text-right">Đơn Giá & Thành Tiền</th>
                                    <th class="py-3 px-4">Lô Hàng Xuất Kho (FEFO)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium">
                                @foreach($selectedOrder->items as $item)
                                    <tr>
                                        <td class="py-3 px-4">
                                            <p class="font-bold text-gray-900">{{ $item->product_name }}</p>
                                            <span class="text-[10px] text-gray-400 font-mono"> {{ $item->product?->unit ?? 'Hộp' }} ({{ $item->product?->weight_kg ?? 1 }} kg)</span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-bold font-mono text-sm bg-gray-100 text-gray-800 px-2.5 py-1 rounded-lg">
                                                {{ $item->quantity }} {{ $item->product?->unit ?? 'hộp' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono">
                                            <p class="text-gray-500 text-[11px]">{{ number_format($item->price, 0, ',', '.') }}đ / {{ $item->product?->unit ?? 'hộp' }}</p>
                                            <p class="font-bold text-gray-900 text-sm">{{ number_format($item->subtotal, 0, ',', '.') }}đ</p>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($item->batch)
                                                <div class="inline-flex flex-col gap-0.5 bg-amber-50/60 border border-amber-200 px-2.5 py-1 rounded-xl">
                                                    <span class="font-mono font-bold text-amber-900">{{ $item->batch->batch_code }}</span>
                                                    <span class="text-[10px] text-amber-700 font-mono">HSD: {{ $item->batch->expiry_date?->format('d/m/Y') }}</span>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <i class="fa-solid fa-circle-exclamation text-rose-500"></i> Thiếu {{ $item->quantity }} {{ $item->product?->unit ?? 'hộp' }} (Hết kho)
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 rounded-xl sm:rounded-2xl bg-gray-50 border border-gray-200/80 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                        <div class="space-y-0.5 text-gray-500 font-mono">
                            <p>Tiền hàng: <b class="text-gray-800">{{ number_format($selectedOrder->subtotal, 0, ',', '.') }}đ</b></p>
                            <p>Phí vận chuyển: <b class="text-gray-800">{{ number_format($selectedOrder->shipping_fee, 0, ',', '.') }}đ</b></p>
                            @if($selectedOrder->points_used > 0)
                                <p class="text-emerald-700">Điểm thưởng trừ: <b>-{{ number_format($selectedOrder->points_used * 1000, 0, ',', '.') }}đ</b></p>
                            @endif
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-xs text-gray-400 font-bold uppercase tracking-wider block">Tổng Thanh Toán Cuối</span>
                            <div class="flex items-center sm:justify-end gap-2 mt-0.5">
                                <span class="text-xl font-black text-brand-700 font-mono">{{ number_format($selectedOrder->total, 0, ',', '.') }}đ</span>
                                @if($selectedOrder->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-mono">
                                        <i class="fa-solid fa-ban text-[10px]"></i> Đã hủy đơn
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Đã thanh toán
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DÒNG THỜI GIAN LỊCH SỬ TRẠNG THÁI -->
                <div class="space-y-3 pt-3 border-t border-gray-100">
                    <h4 class="font-extrabold text-gray-900 text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-timeline text-brand-700"></i> Lịch Sử Trạng Thái Đơn Hàng
                    </h4>

                    <div class="p-4 rounded-xl sm:rounded-2xl bg-[#FAFAF8] border border-gray-200/60 space-y-3.5">
                        @forelse($this->getOrderTimeline($selectedOrder) as $event)
                            <div class="flex items-start gap-3 text-xs border-b border-gray-100 last:border-b-0 pb-3 last:pb-0">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs shrink-0 mt-0.5 {{ match($event['color']) {
                                    'emerald' => 'bg-emerald-100 text-emerald-700',
                                    'blue'    => 'bg-blue-100 text-blue-700',
                                    'amber'   => 'bg-amber-100 text-amber-700',
                                    'purple'  => 'bg-purple-100 text-purple-700',
                                    'red'     => 'bg-rose-100 text-rose-700',
                                    default   => 'bg-gray-100 text-gray-700',
                                } }}">
                                    <i class="fa-solid {{ $event['icon'] }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-bold text-gray-900 text-xs">
                                            {{ $event['title'] }}
                                        </p>
                                        <span class="text-[10px] text-gray-400 font-mono shrink-0">{{ $event['time']->format('H:i • d/m/Y') }}</span>
                                    </div>
                                    <p class="text-[11px] text-gray-600 mt-0.5 leading-relaxed">
                                        {{ $event['desc'] }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 font-mono mt-1">
                                        Thực hiện bởi: <span class="font-medium text-gray-600">{{ $event['changer'] }}</span>
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400 text-xs italic text-center py-2">Chưa có lịch sử trạng thái ghi nhận.</p>
                        @endforelse
                    </div>
                </div>

                <!-- CÁC NÚT HÀNH ĐỘNG -->
                <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        @if(in_array($selectedOrder->status, ['pending', 'processing'], true))
                            <button type="button" wire:click="openCancelModal({{ $selectedOrder->id }})" class="flex-1 sm:flex-none bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold px-4 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-ban"></i> Hủy Đơn Hàng
                            </button>
                        @endif
                        <button type="button" onclick="window.print()" class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-4 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-print"></i> In Phiếu Đóng Gói
                        </button>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                        @if($selectedOrder->status === 'pending')
                            <button type="button" wire:click="approveOrder({{ $selectedOrder->id }})" class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                                <i class="fa-solid fa-check"></i> Xác Nhận Đơn (Chuẩn Bị Xong)
                            </button>
                        @elseif($selectedOrder->status === 'processing')
                            <button type="button" wire:click="dispatchOrder({{ $selectedOrder->id }})" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                                <i class="fa-solid fa-truck-fast"></i> Xuất Kho Đi Giao
                            </button>
                        @elseif($selectedOrder->status === 'shipped')
                            <button type="button" wire:click="completeDelivery({{ $selectedOrder->id }})" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                                <i class="fa-solid fa-circle-check"></i> Xác Nhận Đã Giao Thành Công
                            </button>
                        @endif

                        <button type="button" wire:click="closeOrderDetail" class="w-full sm:w-auto px-5 py-2.5 rounded-xl sm:rounded-2xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 cursor-pointer">
                            Đóng
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- ================= 5. MODAL HỦY ĐƠN HÀNG ================= -->
    @if($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
            <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-gray-100 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2 text-rose-700">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                        <h3 class="font-extrabold text-gray-900 text-base">Xác Nhận Hủy Đơn Hàng</h3>
                    </div>
                    <button type="button" wire:click="closeCancelModal" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-amber-900 text-xs">
                    <p class="font-bold">⚠️ Lưu ý an toàn tồn kho:</p>
                    <p class="mt-0.5">Số lượng sản phẩm trong đơn sẽ được <b>tự động hoàn trả trực tiếp vào Lô hàng (FEFO)</b> tương ứng trong kho.</p>
                </div>

                <form wire:submit="confirmCancelOrder" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Lý do hủy đơn hàng <span class="text-red-500">*</span></label>
                        <textarea wire:model="cancelReason" rows="3" required placeholder="Nhập lý do khách hủy, trùng đơn, hết hàng..." class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl p-3 text-xs resize-none outline-none focus:border-rose-500"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" wire:click="closeCancelModal" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 cursor-pointer">
                            Đóng
                        </button>
                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-5 py-2 rounded-xl text-xs shadow-sm cursor-pointer">
                            Xác Nhận Hủy Đơn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ================= 7. PHIẾU ĐÓNG GÓI IN NHIỆT / A5 ================= -->
    @if($selectedOrder)
        <div id="printable-packing-slip" class="hidden">
            <div class="max-w-xl mx-auto p-6 font-sans text-xs text-gray-900 border border-gray-300">
                <div class="flex justify-between items-start border-b pb-4 mb-4">
                    <div>
                        <h2 class="text-lg font-black text-emerald-800">NÔNG TRẠI ĐẤT LÀNH</h2>
                        <p class="text-[10px] text-gray-500 font-mono">Thung lũng Đạ Ròn, Đơn Dương, Lâm Đồng</p>
                        <p class="text-[10px] text-gray-500">Hotline: 0987.654.321</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-base font-black font-mono">#{{ ltrim($selectedOrder->order_code, '#') }}</h3>
                        <p class="text-[10px] text-gray-500 font-mono">{{ $selectedOrder->created_at->format('d/m/Y') }}</p>
                        <span class="inline-block text-[10px] font-bold uppercase border px-1.5 py-0.5 rounded">
                            {{ strtoupper($selectedOrder->payment?->method ?: 'PAYOS') }}
                        </span>
                    </div>
                </div>
                <div class="mb-4 space-y-1">
                    <p><b>Người nhận:</b> {{ $selectedOrder->recipient_name }} ({{ $selectedOrder->recipient_phone }})</p>
                    <p><b>Địa chỉ:</b> {{ $selectedOrder->recipient_address }}</p>
                    <p><b>Ghi chú:</b> <i>{{ $selectedOrder->note ?: 'Không có' }}</i></p>
                </div>
                <table class="w-full text-left border-collapse border border-gray-300 mb-4">
                    <thead>
                        <tr class="bg-gray-100 text-[10px] font-bold">
                            <th class="border border-gray-300 p-2">Sản phẩm</th>
                            <th class="border border-gray-300 p-2 text-center">SL</th>
                            <th class="border border-gray-300 p-2">Mã Lô FEFO</th>
                            <th class="border border-gray-300 p-2 text-center">Kiểm Tra</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedOrder->items as $item)
                            <tr>
                                <td class="border border-gray-300 p-2 font-bold">{{ $item->product_name }}</td>
                                <td class="border border-gray-300 p-2 text-center font-mono font-bold">{{ $item->quantity }} {{ $item->product?->unit ?? 'hộp' }}</td>
                                <td class="border border-gray-300 p-2 font-mono"><b>{{ $item->batch?->batch_code ?? 'Lô chưa gán' }}</b></td>
                                <td class="border border-gray-300 p-2 text-center font-mono text-gray-400">[  ] ĐỦ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flex justify-between items-end border-t pt-3">
                    <div class="text-[10px] text-gray-400">
                        <p>Quét mã QR trên tem nhãn để đối chiếu nhật ký nông trại.</p>
                    </div>
                    <div class="text-center font-mono">
                        <p class="font-bold">Ký tên đóng gói</p>
                        <div class="h-10"></div>
                        <p class="text-[10px] text-gray-400">KS. Hoàng Nam</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('styles')
    <style>
        @media print {
            body * { visibility: hidden !important; }
            #printable-packing-slip, #printable-packing-slip * { visibility: visible !important; }
            #printable-packing-slip { position: fixed; left: 0; top: 0; width: 100%; display: block !important; margin: 0; padding: 20px; background: white; }
        }
    </style>
    @endpush
</div>

