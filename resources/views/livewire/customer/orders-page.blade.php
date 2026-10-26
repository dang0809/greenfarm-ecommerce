<div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 shadow-sm space-y-6">
    
    <!-- Tiêu đề -->
    <div class="pb-4 border-b border-gray-100">
        <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">Đơn Hàng Của Tôi</h2>
        <p class="text-xs text-gray-400 mt-0.5">Theo dõi quá trình chuẩn bị nông sản và giao hỏa tốc tận bếp</p>
    </div>

    <!-- BỘ LỌC TRẠNG THÁI (8 TAB) -->
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

        <button type="button" wire:click="setStatusFilter('processing')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'processing' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Chờ lấy hàng ({{ $counts['processing'] }})
        </button>

        <button type="button" wire:click="setStatusFilter('shipped')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'shipped' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Đang giao ({{ $counts['shipped'] }})
        </button>

        <button type="button" wire:click="setStatusFilter('delivered')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'delivered' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Đã giao ({{ $counts['delivered'] }})
        </button>

        <button type="button" wire:click="setStatusFilter('cancelled')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'cancelled' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Đã hủy ({{ $counts['cancelled'] }})
        </button>

        <button type="button" wire:click="setStatusFilter('refund')" 
                class="px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'refund' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-500 hover:text-brand-700' }}">
            Trả hàng / Hoàn tiền ({{ $counts['refund'] }})
        </button>
    </div>

    <!-- DANH SÁCH ĐƠN HÀNG -->
    <div class="space-y-5 pt-2">
        @forelse ($orders as $order)
            @php 
                $isVipPayment  = $order->order_type === 'subscription';           // Đơn mua gói VIP
                $isVipDelivery = $order->order_type === 'subscription_delivery';  // Đơn giao rau VIP từng đợt
            @endphp

            <div class="{{ ($isVipPayment || $isVipDelivery) ? 'bg-purple-50/40 border-purple-200' : 'bg-gray-50/50 border-gray-200' }} rounded-2xl border p-4 sm:p-5 space-y-4">
                
                <!-- Header Đơn hàng -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b {{ ($isVipPayment || $isVipDelivery) ? 'border-purple-100' : 'border-gray-200/80' }} gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl {{ ($isVipPayment || $isVipDelivery) ? 'bg-purple-100 text-purple-700' : 'bg-brand-50 text-brand-600' }} flex items-center justify-center shrink-0">
                            <i class="{{ ($isVipPayment || $isVipDelivery) ? 'fa-solid fa-crown' : 'fa-solid fa-box-archive' }} text-sm"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-extrabold text-gray-900 text-sm">#{{ $order->order_code }}</span>
                                
                                @if ($isVipPayment)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black text-purple-700 bg-purple-100 border border-purple-300 px-2 py-0.5 rounded-md">
                                        <i class="fa-solid fa-credit-card text-[9px]"></i> ĐƠN MUA GÓI VIP
                                    </span>
                                @elseif ($isVipDelivery)
                                    @php
                                        $sched = $order->deliverySchedule;
                                        if ($sched) {
                                            $total = $sched->subscription?->total_deliveries ?? 4;
                                            $batchInfo = " (Đợt {$sched->delivery_order}/{$total})";
                                        } else {
                                            preg_match('/Đợt\s+\d+\/\d+/ui', $order->note ?? '', $batchMatches);
                                            $batchInfo = !empty($batchMatches[0]) ? ' (' . $batchMatches[0] . ')' : '';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black text-purple-700 bg-purple-100 border border-purple-300 px-2 py-0.5 rounded-md">
                                        <i class="fa-solid fa-truck-ramp-box text-[9px]"></i> GIAO RAU VIP{{ $batchInfo }}
                                    </span>
                                @endif
                            </div>
                            <span class="block text-[11px] text-gray-400 mt-0.5">Thời gian: {{ $order->created_at->format('H:i • d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- Badge Trạng thái -->
                    @if ($order->status === 'cancelled')
                        <div class="flex items-center gap-1.5 flex-wrap justify-end">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">
                                Đã hủy {{ $order->cancelled_by === 'customer' ? '(Bởi bạn)' : '(Bởi Nông trại)' }}
                            </span>
                            @if ($order->payment_status === 'paid' || $order->refund_status !== 'none')
                                @if ($order->refund_status === 'pending')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                                        <i class="fa-solid fa-clock text-[9px] animate-pulse"></i> Chờ hoàn tiền
                                    </span>
                                @elseif ($order->refund_status === 'refunded')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                        <i class="fa-solid fa-circle-check text-[9px]"></i> Đã hoàn tiền
                                    </span>
                                @endif
                            @endif
                        </div>
                    @elseif ($order->status === 'delivered')
                        <div class="flex items-center gap-1.5 flex-wrap justify-end">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                <i class="fa-solid fa-check text-[10px]"></i> {{ $isVipPayment ? 'Thanh toán & Kích hoạt thành công' : 'Đã giao thành công' }}
                            </span>
                            @if ($order->complaint)
                                @if ($order->complaint->status === 'resolved' || $order->refund_status === 'refunded')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                        <i class="fa-solid fa-circle-check text-[9px]"></i> Đã hoàn tiền
                                    </span>
                                @elseif ($order->complaint->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-red-800 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">
                                        <i class="fa-solid fa-circle-xmark text-[9px]"></i> Khiếu nại bị từ chối
                                    </span>
                                @elseif ($order->complaint->status === 'pending')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                                        <i class="fa-solid fa-clock text-[9px] animate-pulse"></i> Chờ duyệt khiếu nại
                                    </span>
                                @endif
                            @endif
                        </div>
                    @elseif ($order->status === 'shipped')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Đang giao
                        </span>
                    @elseif ($order->status === 'processing')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full">
                            <i class="fa-solid fa-boxes-packing text-[10px]"></i> Chờ lấy hàng
                        </span>
                    @elseif ($order->status === 'pending' && $order->payment_status === 'pending')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-yellow-800 bg-yellow-50 border border-yellow-200 px-2.5 py-0.5 rounded-full">
                            <i class="fa-solid fa-hourglass-half text-[10px]"></i> Chờ thanh toán
                        </span>
                    @elseif ($order->status === 'pending' && $order->payment_status === 'paid')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-full">
                            <i class="fa-solid fa-circle-notch text-[10px] animate-spin"></i> Chờ xác nhận
                        </span>
                    @endif
                </div>

                <!-- HIỂN THỊ NỘI DUNG SẢN PHẨM -->
                @if ($isVipPayment)
                    <!-- CASE 1: ĐƠN MUA GÓI VIP (Không có items rau củ lẻ) -->
                    <div class="py-2 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-purple-950">Gói Rau Sạch VIP Hữu Cơ (4 Kỳ Giao Tận Nhà)</h3>
                            <p class="text-xs text-purple-700 mt-0.5">Thanh toán quyền sử dụng dịch vụ VIP • Miễn phí vận chuyển 100% các kỳ giao</p>
                        </div>
                        <span class="text-sm font-black text-purple-900">{{ number_format($order->total) }}đ</span>
                    </div>
                @else
                    <!-- CASE 2: ĐƠN BÁN LẺ HOẶC ĐƠN GIAO RAU VIP THEO ĐỢT (Có order_items) -->
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
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            @if ($isVipDelivery)
                                                Số lượng: {{ $item->quantity }} (Đã gồm trong Gói VIP)
                                            @else
                                                Đơn giá: {{ number_format($item->price) }}đ • Số lượng: {{ $item->quantity }}
                                            @endif
                                        </p>
                                        
                                        @if ($item->batch && $item->batch_id)
                                            <a href="{{ route('customer.traceability.detail', ['batch' => $item->batch->batch_code]) }}" 
                                               class="inline-flex items-center gap-1 text-[10px] font-bold text-brand-700 bg-white border border-brand-200 px-2 py-0.5 rounded mt-1 hover:bg-brand-50 transition-colors">
                                                <i class="fa-solid fa-qrcode text-[9px]"></i>
                                                <span>Lô: {{ $item->batch->batch_code }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs sm:text-sm font-extrabold text-gray-900">
                                    {{ $isVipDelivery ? '0đ' : number_format($item->subtotal) . 'đ' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($order->complaint)
                    <div class="rounded-xl p-3 text-xs {{ $order->complaint->status === 'rejected' ? 'bg-red-50/80 border border-red-200 text-red-900' : ($order->complaint->status === 'resolved' || $order->refund_status === 'refunded' ? 'bg-emerald-50/80 border border-emerald-200 text-emerald-900' : 'bg-amber-50/80 border border-amber-200 text-amber-900') }}">
                        <div class="flex items-start gap-2.5">
                            @if ($order->complaint->status === 'rejected')
                                <i class="fa-solid fa-circle-xmark text-red-600 text-sm mt-0.5 shrink-0"></i>
                                <div class="space-y-1 flex-1">
                                    <div class="font-bold text-red-800 flex items-center justify-between">
                                        <span>Khiếu nại bị từ chối</span>
                                        @if ($order->complaint->updated_at)
                                            <span class="text-[11px] font-normal text-red-600">{{ $order->complaint->updated_at->format('H:i • d/m/Y') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-gray-700 leading-relaxed">
                                        <span class="font-bold text-red-700">Lý do từ chối:</span> 
                                        {{ $order->complaint->resolution_note ?: 'Yêu cầu không đáp ứng chính sách bảo hành / đổi trả nông sản của GreenFarm.' }}
                                    </p>
                                    @if ($order->complaint->reason)
                                        <p class="text-gray-500 text-[11px] pt-1 border-t border-red-100/80">
                                            <span class="font-medium text-gray-600">Nội dung khiếu nại của bạn:</span> {{ $order->complaint->reason }}
                                        </p>
                                    @endif
                                </div>
                            @elseif ($order->complaint->status === 'resolved' || $order->refund_status === 'refunded')
                                <i class="fa-solid fa-circle-check text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                                <div class="space-y-1 flex-1">
                                    <div class="font-bold text-emerald-800 flex items-center justify-between">
                                        <span>Khiếu nại thành công • Đã hoàn tiền</span>
                                        @if ($order->refunded_at || $order->complaint->updated_at)
                                            <span class="text-[11px] font-normal text-emerald-600">{{ \Carbon\Carbon::parse($order->refunded_at ?? $order->complaint->updated_at)->format('H:i • d/m/Y') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-gray-700 leading-relaxed">
                                        Số tiền hoàn: <b class="text-emerald-700 font-extrabold">{{ number_format($order->complaint->refund_amount ?? $order->total) }}đ</b>
                                        @if ($order->bank_name && $order->bank_account_number)
                                            về STK <span class="font-bold text-gray-800">{{ $order->bank_name }} - {{ $order->bank_account_number }}</span> ({{ $order->bank_account_name }})
                                        @endif
                                    </p>
                                    @if (!empty($order->complaint->resolution_note))
                                        <p class="text-gray-600 text-[11px] pt-1 border-t border-emerald-100/80">
                                            <span class="font-semibold text-emerald-700">Ghi chú từ Nông trại:</span> {{ $order->complaint->resolution_note }}
                                        </p>
                                    @endif
                                </div>
                            @else
                                <i class="fa-solid fa-clock-rotate-left text-amber-600 text-sm mt-0.5 shrink-0"></i>
                                <div class="space-y-1 flex-1">
                                    <div class="font-bold text-amber-800 flex items-center justify-between">
                                        <span>Đang xử lý yêu cầu khiếu nại</span>
                                        <span class="text-[11px] font-normal text-amber-700">Chờ duyệt trong 24h</span>
                                    </div>
                                    <p class="text-gray-700 leading-relaxed">
                                        Yêu cầu hoàn tiền: <b class="text-amber-800 font-bold">{{ number_format($order->complaint->refund_amount ?? $order->total) }}đ</b>. Nông trại đang đối soát đơn hàng và sẽ phản hồi sớm nhất.
                                    </p>
                                    @if ($order->complaint->reason)
                                        <p class="text-gray-500 text-[11px] pt-1 border-t border-amber-100/80">
                                            <span class="font-medium text-gray-600">Lý do khiếu nại:</span> {{ $order->complaint->reason }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif ($order->status === 'cancelled' && $order->refund_status === 'refunded')
                    <div class="rounded-xl p-3 text-xs bg-emerald-50/80 border border-emerald-200 text-emerald-900">
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                            <div class="space-y-1 flex-1">
                                <div class="font-bold text-emerald-800 flex items-center justify-between">
                                    <span>Đã hoàn tiền thành công</span>
                                    @if ($order->refunded_at)
                                        <span class="text-[11px] font-normal text-emerald-600">{{ \Carbon\Carbon::parse($order->refunded_at)->format('H:i • d/m/Y') }}</span>
                                    @endif
                                </div>
                                <p class="text-gray-700 leading-relaxed">
                                    Đã chuyển khoản hoàn <b class="text-emerald-700 font-extrabold">{{ number_format($order->total) }}đ</b>
                                    @if ($order->bank_name && $order->bank_account_number)
                                        về tài khoản <span class="font-bold text-gray-800">{{ $order->bank_name }} - {{ $order->bank_account_number }}</span> ({{ $order->bank_account_name }})
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Footer Đơn Hàng & Thao tác -->
                <div class="pt-3 border-t {{ ($isVipPayment || $isVipDelivery) ? 'border-purple-100' : 'border-gray-200/80' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-xs text-gray-500">
                        <span>Cổng thanh toán: <b class="uppercase text-brand-700 font-extrabold">{{ $order->payment_method }}</b></span>
                        <div class="flex items-baseline gap-1.5 mt-0.5">
                            <span class="text-gray-400">Tổng thanh toán:</span>
                            <span class="text-base font-black text-brand-700">
                                {{ $isVipDelivery ? '0đ (Đã thanh toán qua Gói VIP)' : number_format($order->total) . 'đ' }}
                            </span>
                        </div>
                        @if (!empty($order->note))
                            <div class="mt-2 text-[11px] text-gray-500 flex items-center gap-1.5 bg-gray-50/80 rounded-lg px-2.5 py-1 border border-gray-100 w-fit">
                                <i class="fa-regular fa-comment-dots text-gray-400"></i>
                                <span>Ghi chú của bạn: <span class="text-gray-700 font-medium italic">{{ $order->note }}</span></span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Nút Hủy đơn: Chỉ dành cho đơn lẻ retail đang pending -->
                        @if ($order->status === 'pending' && $order->order_type === 'retail')
                            <button type="button" 
                                    wire:click="confirmCancel({{ $order->id }})" 
                                    wire:confirm="Bạn có chắc chắn muốn hủy đơn hàng #{{ $order->order_code }} không?"
                                    class="bg-white hover:bg-red-50 text-gray-600 hover:text-red-600 border border-gray-200 font-bold text-xs px-3.5 py-2 rounded-xl transition-all cursor-pointer">
                                Hủy đơn
                            </button>
                        @endif

                        <!-- Nút Thanh toán ngay: Dành cho đơn pending chưa trả tiền -->
                        @if ($order->status === 'pending' && $order->payment_status === 'pending')
                            <button type="button" 
                                    wire:click="payNow({{ $order->id }})" 
                                    wire:loading.attr="disabled"
                                    class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow-xs transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                <i class="fa-solid fa-qrcode text-xs" wire:loading.remove></i>
                                <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading></i>
                                <span>Thanh toán ngay</span>
                            </button>
                        @endif

                        <!-- Khiếu nại hoàn tiền (Kiểm tra mốc 24h từ order_status_histories) -->
                        @if ($order->status === 'delivered' && $order->order_type === 'retail')
                            @php
                                $deliveredHistory = $order->statusHistories?->firstWhere('new_status', 'delivered');
                                $deliveredAt = $deliveredHistory ? $deliveredHistory->created_at : $order->updated_at;
                                $canRefund = $deliveredAt ? ($deliveredAt->diffInHours(now()) < 24) : false;
                            @endphp

                            @if ($order->complaint)
                                @if ($order->complaint->status === 'resolved' || $order->refund_status === 'refunded')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-2 rounded-xl">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                        <span>Đã hoàn tiền ({{ number_format($order->complaint->refund_amount ?? $order->total) }}đ)</span>
                                    </span>
                                @elseif ($order->complaint->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-800 bg-red-50 border border-red-200 px-3 py-2 rounded-xl">
                                        <i class="fa-solid fa-circle-xmark text-red-600"></i>
                                        <span>Khiếu nại bị từ chối</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl">
                                        <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
                                        <span>Đang chờ duyệt hoàn tiền</span>
                                    </span>
                                @endif
                            @elseif ($canRefund)
                                <button type="button" 
                                        wire:click="openComplaintModal({{ $order->id }})"
                                        class="bg-white hover:bg-amber-50 text-amber-700 hover:text-amber-800 border border-amber-200 font-bold text-xs px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                    <span>Khiếu nại hoàn tiền</span>
                                </button>
                            @endif
                        @endif

                        <!-- Nút Mua lại: Chỉ hiển thị với đơn lẻ đã giao hoặc đã hủy -->
                        @if (in_array($order->status, ['delivered', 'cancelled']) && $order->order_type === 'retail')
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
                <div class="w-14 h-14 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center text-xl mx-auto border border-gray-100 text-center mx-auto">
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

    <!-- MODAL BẮT NHẬP STK HOÀN TIỀN KHI HỦY ĐƠN ĐÃ THANH TOÁN -->
    @if ($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-3xl p-6 max-w-md w-full space-y-4 shadow-xl">
                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Yêu Cầu Hoàn Tiền Hủy Đơn</h3>
                    <button type="button" wire:click="resetCancelModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <p class="text-xs text-amber-700 bg-amber-50 p-3 rounded-xl border border-amber-200">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Đơn hàng này đã thanh toán. Vui lòng cung cấp STK ngân hàng để Nông Trại Đất Lành hoàn tiền lại cho bạn trong vòng 24h.
                </p>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Lý do hủy đơn</label>
                        <input type="text" wire:model="cancel_reason" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500" placeholder="Nhập lý do hủy...">
                        @error('cancel_reason') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Tên ngân hàng</label>
                        <select wire:model="bank_name" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500">
                            <option value="">-- Chọn ngân hàng --</option>
                            <option value="MBBank">MB Bank (Ngân hàng Quân Đội)</option>
                            <option value="Vietcombank">Vietcombank</option>
                            <option value="Techcombank">Techcombank</option>
                            <option value="ACB">ACB</option>
                            <option value="VPBank">VPBank</option>
                            <option value="VietinBank">VietinBank</option>
                            <option value="BIDV">BIDV</option>
                            <option value="MoMo">Ví MoMo</option>
                        </select>
                        @error('bank_name') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Số tài khoản / Số điện thoại MoMo</label>
                        <input type="text" wire:model="bank_account_number" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500" placeholder="VD: 0391000123456">
                        @error('bank_account_number') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Tên chủ tài khoản (Viết hoa không dấu)</label>
                        <input type="text" wire:model="bank_account_name" class="w-full rounded-xl border-gray-200 text-xs uppercase focus:ring-brand-500" placeholder="VD: NGUYEN VAN A">
                        @error('bank_account_name') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" wire:click="resetCancelModal" class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200">
                        Bỏ qua
                    </button>
                    <button type="button" wire:click="processPaidCancel" class="px-4 py-2 rounded-xl text-xs font-bold bg-red-600 text-white hover:bg-red-700 shadow-xs">
                        Xác nhận hủy & Hoàn tiền
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
    <!-- MODAL KHIẾU NẠI HOÀN TIỀN DẬP NÁT (24H) -->
    @if ($showComplaintModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-3xl p-6 max-w-lg w-full space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Khiếu Nại Hoàn Tiền Nông Sản (24H)</h3>
                    <button type="button" wire:click="resetComplaintModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <p class="text-xs text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-200 leading-relaxed">
                    <i class="fa-solid fa-shield-halved text-amber-600 mr-1"></i>
                    Chính sách cam kết tươi giòn: Nông trại hỗ trợ hoàn tiền trực tiếp vào tài khoản ngân hàng nếu rau củ bị dập nát, héo úa hoặc hư hại do vận chuyển.
                </p>

                <form wire:submit.prevent="submitComplaint" class="space-y-3.5 text-xs">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Lý do khiếu nại</label>
                        <textarea wire:model="complaint_reason" rows="2" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500" placeholder="Mô tả tình trạng nông sản..."></textarea>
                        @error('complaint_reason') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Số tiền đề xuất hoàn lại (VNĐ)</label>
                        <input type="number" wire:model="complaint_refund_amount" class="w-full rounded-xl border-gray-200 text-xs font-mono font-bold focus:ring-brand-500">
                        @error('complaint_refund_amount') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="font-bold text-gray-700 block mb-1">Ngân hàng nhận lại tiền</label>
                            <select wire:model="bank_name" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500">
                                <option value="">-- Chọn ngân hàng --</option>
                                <option value="MBBank">MB Bank</option>
                                <option value="Vietcombank">Vietcombank</option>
                                <option value="Techcombank">Techcombank</option>
                                <option value="ACB">ACB</option>
                                <option value="VPBank">VPBank</option>
                                <option value="VietinBank">VietinBank</option>
                                <option value="BIDV">BIDV</option>
                                <option value="MoMo">Ví MoMo</option>
                            </select>
                            @error('bank_name') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="font-bold text-gray-700 block mb-1">Số tài khoản / SĐT MoMo</label>
                            <input type="text" wire:model="bank_account_number" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500" placeholder="VD: 0391000123456">
                            @error('bank_account_number') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Tên chủ tài khoản (In hoa không dấu)</label>
                        <input type="text" wire:model="bank_account_name" class="w-full rounded-xl border-gray-200 text-xs uppercase focus:ring-brand-500" placeholder="VD: NGUYEN VAN A">
                        @error('bank_account_name') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Ảnh chụp nông sản bị dập / hư hỏng</label>
                        <input type="file" wire:model="complaint_images" multiple accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                        @error('complaint_images.*') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" wire:click="resetComplaintModal" class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200">
                            Hủy bỏ
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 text-white hover:bg-amber-700 shadow-xs flex items-center gap-1.5">
                            <span wire:loading.remove>Gửi yêu cầu hoàn tiền</span>
                            <span wire:loading>Đang gửi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
