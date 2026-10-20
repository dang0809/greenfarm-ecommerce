<div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 shadow-sm space-y-6">
    
    <!-- Tiêu đề (Đồng bộ chuẩn 100% với Hồ Sơ Của Tôi) -->
    <div class="pb-4 border-b border-gray-100">
        <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">Đơn Hàng Của Tôi</h2>
        <p class="text-xs text-gray-400 mt-0.5">Theo dõi quá trình chuẩn bị nông sản và giao hỏa tốc tận bếp</p>
    </div>

    <!-- BỘ LỌC TRẠNG THÁI -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
        <button type="button" wire:click="setStatusFilter('all')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'all' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Tất cả ({{ $counts['all'] }})
        </button>
        <button type="button" wire:click="setStatusFilter('pending_payment')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'pending_payment' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Chờ thanh toán ({{ $counts['pending_payment'] }})
        </button>
        <button type="button" wire:click="setStatusFilter('pending_confirmation')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'pending_confirmation' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Chờ xác nhận ({{ $counts['pending_confirmation'] }})
        </button>
        <button type="button" wire:click="setStatusFilter('delivering')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'delivering' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Đang giao ({{ $counts['delivering'] }})
        </button>
        <button type="button" wire:click="setStatusFilter('delivered')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'delivered' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Đã giao ({{ $counts['delivered'] }})
        </button>
        <button type="button" wire:click="setStatusFilter('cancelled')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'cancelled' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Đã hủy ({{ $counts['cancelled'] }})
        </button>
    </div>

    <!-- DANH SÁCH ĐƠN HÀNG -->
    <div class="space-y-5 pt-2">
        @forelse ($orders as $order)
            <div class="bg-gray-50/50 rounded-2xl border border-gray-200 p-4 sm:p-5 space-y-4">
                
                <!-- Header Đơn hàng -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-gray-200/80 gap-2">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-box-archive text-sm"></i>
                        </div>
                        <div>
                            <span class="font-mono font-extrabold text-gray-900 text-sm">#{{ $order->order_code }}</span>
                            <span class="block text-[11px] text-gray-400">Đặt lúc {{ $order->created_at->format('H:i • d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- Badge trạng thái -->
                    @if ($order->status === 'cancelled')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full self-start sm:self-auto">
                            Đã hủy
                        </span>
                    @elseif ($order->status === 'completed')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full self-start sm:self-auto">
                            <i class="fa-solid fa-check text-[10px]"></i> Đã giao thành công
                        </span>
                    @elseif ($order->status === 'shipping')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full self-start sm:self-auto">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Đang giao hỏa tốc
                        </span>
                    @elseif ($order->payment_status === 'pending')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-yellow-800 bg-yellow-50 border border-yellow-200 px-2.5 py-0.5 rounded-full self-start sm:self-auto">
                            <i class="fa-solid fa-hourglass-half text-[10px]"></i> Chờ thanh toán
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-full self-start sm:self-auto">
                            <i class="fa-solid fa-circle-notch text-[10px] animate-spin"></i> Chờ xác nhận
                        </span>
                    @endif
                </div>

                <!-- Danh sách món hàng -->
                <div class="space-y-3">
                    @foreach ($order->items as $item)
                        @php
                            $product = $item->product;
                            $rawImage = $product?->productImages?->firstWhere('is_primary', true)?->image 
                                ?? $product?->productImages?->first()?->image;

                            $primaryImage = $rawImage 
                                ? (str_starts_with($rawImage, 'http') ? $rawImage : asset('storage/' . $rawImage))
                                : 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=200';
                        @endphp

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                            <div class="flex items-start gap-3">
                                <img src="{{ $primaryImage }}" alt="{{ $item->product_name }}" class="w-14 h-14 rounded-xl object-cover border border-gray-200 shrink-0">
                                <div>
                                    <h3 class="text-xs sm:text-sm font-bold text-gray-900">{{ $item->product_name }}</h3>
                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        Đơn giá: {{ number_format($item->price) }}đ • Số lượng: {{ $item->quantity }}
                                    </p>
                                    @if ($item->batch)
                                        <a href="{{ route('customer.traceability.detail', ['batch' => $item->batch->batch_code]) }}" 
                                           class="inline-flex items-center gap-1 text-[10px] font-bold text-brand-700 bg-white border border-brand-200 px-2 py-0.5 rounded mt-1 hover:bg-brand-50 transition-colors">
                                            <i class="fa-solid fa-qrcode text-[9px]"></i>
                                            <span>Lô: {{ $item->batch->batch_code }}</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs sm:text-sm font-extrabold text-gray-900 text-right sm:text-left self-end sm:self-auto">
                                {{ number_format($item->subtotal) }}đ
                            </span>
                        </div>
                    @endforeach
                </div>

                <!-- Footer Đơn Hàng & Nút Thao Tác -->
                <div class="pt-3 border-t border-gray-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-xs text-gray-500">
                        <span>Thanh toán: <b>{{ strtoupper($order->payment_method) }}</b></span>
                        <div class="flex items-baseline gap-1.5 mt-0.5">
                            <span class="text-gray-400">Tổng thanh toán:</span>
                            <span class="text-base font-black text-brand-700">{{ number_format($order->total) }}đ</span>
                            <span class="text-[10px] text-gray-400">(gồm {{ number_format($order->shipping_fee) }}đ ship)</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($order->status === 'pending' || $order->payment_status === 'pending')
                            <button type="button" 
                                    wire:click="cancelOrder({{ $order->id }})" 
                                    wire:confirm="Bạn có chắc chắn muốn hủy đơn hàng này?"
                                    class="bg-white hover:bg-red-50 text-gray-600 hover:text-red-600 border border-gray-200 font-bold text-xs px-3 py-2 rounded-xl transition-all cursor-pointer">
                                Hủy đơn
                            </button>
                        @endif

                        @if ($order->payment_status === 'pending' && $order->status !== 'cancelled')
                            <a href="{{ route('customer.payment.payos', ['order_id' => $order->id]) }}" 
                               class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow-xs transition-all flex items-center gap-1.5 active:scale-95">
                                <i class="fa-solid fa-qrcode text-xs"></i>
                                <span>Thanh toán ngay</span>
                            </a>
                        @endif

                        @if ($order->status === 'completed')
                            <button type="button" 
                                    wire:click="reorder({{ $order->id }})" 
                                    class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow-xs transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                <i class="fa-solid fa-rotate-right text-xs"></i>
                                <span>Mua lại</span>
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        @empty
            <div class="py-12 text-center space-y-2">
                <div class="w-14 h-14 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center text-xl mx-auto border border-gray-100">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-800">Không tìm thấy đơn hàng nào</h3>
                <p class="text-xs text-gray-400 max-w-xs mx-auto">Bạn hiện không có đơn hàng nào thuộc trạng thái này.</p>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>