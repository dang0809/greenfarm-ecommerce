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
                                <a href="{{ route('customer.order.detail', $order->order_code) }}" class="font-mono font-extrabold text-gray-900 text-sm hover:text-brand-600 transition flex items-center gap-1 group" title="Xem chi tiết đơn hàng">
                                    <span>#{{ $order->order_code }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-gray-400 group-hover:text-brand-600 transition"></i>
                                </a>
                                
                                @if ($isVipPayment)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black text-purple-700 bg-purple-100 border border-purple-300 px-2 py-0.5 rounded-md">
                                        <i class="fa-solid fa-credit-card text-[9px]"></i> ĐƠN MUA GÓI VIP
                                    </span>
                                @elseif ($isVipDelivery)
                                    @php
                                        $sched = $order->deliverySchedule;
                                        if ($sched) {
                                            $total = 8;
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

                    <!-- Badge Trạng thái: Đồng bộ chính xác theo từng tab & khiếu nại, không ngoặc đơn -->
                    @if ($order->status === 'delivered')
                        <div class="flex items-center gap-1.5 flex-wrap justify-end">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                <i class="fa-solid fa-check text-[10px]"></i> Đã giao
                            </span>
                            @if ($order->complaint && $order->complaint->status === 'resolved')
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                    <i class="fa-solid fa-circle-check text-[10px] text-emerald-600"></i> Đã hoàn tiền
                                </span>
                            @elseif ($order->complaint && $order->complaint->status === 'pending')
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                                    <i class="fa-solid fa-clock text-[10px] text-amber-600 animate-pulse"></i> Đang yêu cầu hoàn tiền
                                </span>
                            @elseif ($order->complaint && $order->complaint->status === 'rejected')
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-red-800 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">
                                    <i class="fa-solid fa-circle-xmark text-[10px] text-red-600"></i> Khiếu nại bị từ chối
                                </span>
                            @endif
                        </div>
                    @elseif ($order->status === 'cancelled')
                        @if ($order->cancelled_by === 'customer')
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle-xmark text-[10px]"></i> Người dùng hủy
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle-xmark text-[10px]"></i> Nhân viên hủy
                            </span>
                        @endif
                    @elseif ($order->status === 'shipped')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> Đang giao
                        </span>
                    @elseif ($order->status === 'processing')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                            <i class="fa-solid fa-boxes-packing text-[10px]"></i> Chờ lấy hàng
                        </span>
                    @elseif ($order->status === 'pending' && $order->payment?->status === 'pending')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-yellow-800 bg-yellow-50 border border-yellow-200 px-2.5 py-0.5 rounded-full">
                            <i class="fa-solid fa-hourglass-half text-[10px]"></i> Chờ thanh toán
                        </span>
                    @elseif ($order->status === 'pending' && $order->payment?->status === 'success')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                            <i class="fa-solid fa-circle-notch text-[10px] animate-spin"></i> Chờ xác nhận
                        </span>
                    @endif
                </div>

                <!-- THÔNG BÁO TÓM TẮT TRẠNG THÁI -->
                @php
                    $orderStatusMsg = match(true) {
                        $order->status === 'pending' && $order->payment?->status === 'pending' => 'Đơn hàng của bạn đã được hệ thống ghi nhận. Vui lòng hoàn tất thanh toán để nông trại bắt đầu chuẩn bị hàng nhé!',
                        $order->status === 'pending' && $order->payment?->status === 'success' && $isVipDelivery => "Đợt giao rau định kỳ{$batchInfo} đã được khởi tạo. Hệ thống đang chờ nhân viên Nông trại Đất Lành kiểm tra và xác nhận đơn hàng.",
                        $order->status === 'pending' && $order->payment?->status === 'success' => 'Bạn đã thanh toán thành công. Hệ thống đang chờ nhân viên Nông trại Đất Lành kiểm tra và xác nhận đơn hàng.',
                        $order->status === 'processing' && $isVipDelivery => "Nông trại đang thu hoạch & đóng gói thùng rau VIP{$batchInfo} theo chuẩn VietGAP, chờ shipper đến lấy hàng.",
                        $order->status === 'processing' => 'Đơn hàng đã được xác nhận & đang đóng gói, chờ shipper đến lấy hàng.',
                        $order->status === 'shipped' => 'Shipper đã lấy hàng thành công và đang trên đường giao đơn hàng này tới bạn. Vui lòng chú ý điện thoại nhé!',
                        $order->status === 'delivered' && $isVipDelivery => "Đã giao thành công đợt rau VIP{$batchInfo} đến bạn!",
                        $order->status === 'delivered' => 'Đơn hàng đã được giao thành công đến bạn! Cảm ơn bạn đã tin chọn nông sản sạch GreenFarm.',
                        default => null
                    };
                @endphp
                @if ($orderStatusMsg)
                    <div class="px-3.5 py-2 rounded-xl {{ $isVipDelivery ? 'bg-purple-100/60 text-purple-900 border border-purple-200' : 'bg-gray-100/80 text-gray-700 border border-gray-200/60' }} text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-info {{ $isVipDelivery ? 'text-purple-600' : 'text-brand-600' }} text-xs shrink-0"></i>
                        <span class="leading-relaxed">{{ $orderStatusMsg }}</span>
                    </div>
                @endif

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
                                    <a href="{{ route('customer.order.detail', $order->order_code) }}" class="shrink-0">
                                        <img src="{{ $primaryImage }}" alt="{{ $item->product_name }}" class="w-14 h-14 rounded-xl object-cover border border-gray-200 shrink-0 hover:opacity-90 transition">
                                    </a>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-bold text-gray-900">
                                            <a href="{{ route('customer.order.detail', $order->order_code) }}" class="hover:text-brand-600 transition">
                                                {{ $item->product_name }}
                                            </a>
                                        </h3>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            @if ($isVipDelivery)
                                                Số lượng: {{ $item->quantity + 0 }} (Đã gồm trong Gói VIP)
                                            @else
                                                Đơn giá: {{ number_format($item->price) }}đ • Số lượng: {{ $item->quantity + 0 }}
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

                <!-- Footer Đơn Hàng & Thao tác -->
                <div class="pt-3 border-t {{ ($isVipPayment || $isVipDelivery) ? 'border-purple-100' : 'border-gray-200/80' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-xs text-gray-500">
                        <span>Cổng thanh toán: <b class="uppercase text-brand-700 font-extrabold">{{ $order->payment?->method ?? 'PAYOS' }}</b></span>
                        <div class="flex items-baseline gap-1.5 mt-0.5">
                            <span class="text-gray-400">Tổng thanh toán:</span>
                            <span class="text-base font-black text-brand-700">
                                {{ $isVipDelivery ? '0đ (Đã thanh toán qua Gói VIP)' : number_format($order->total) . 'đ' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Nút Hủy cho đơn CHƯA thanh toán (pending) -->
                        @if ($order->status === 'pending' && $order->payment?->status === 'pending' && $order->order_type === 'retail')
                            <button type="button" 
                                    wire:click="confirmCancel({{ $order->id }})" 
                                    wire:confirm="Bạn có chắc chắn muốn hủy đơn hàng #{{ $order->order_code }} không?"
                                    class="bg-white hover:bg-red-50 text-gray-600 hover:text-red-600 border border-gray-200 font-bold text-xs px-3.5 py-2 rounded-xl transition-all cursor-pointer">
                                Hủy đơn
                            </button>
                        @endif

                        <!-- Nút Thanh toán ngay: Dành cho đơn pending chưa trả tiền -->
                        @if ($order->status === 'pending' && $order->payment?->status === 'pending')
                            <button type="button" 
                                    wire:click="payNow({{ $order->id }})" 
                                    wire:loading.attr="disabled"
                                    class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow-xs transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                <i class="fa-solid fa-qrcode text-xs" wire:loading.remove></i>
                                <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading></i>
                                <span>Thanh toán ngay</span>
                            </button>
                        @endif

                        <!-- Nút Khiếu nại: Chỉ hiển thị cho đơn lẻ đã giao, chưa gửi khiếu nại -->
                        @if ($order->status === 'delivered' && $order->order_type === 'retail' && !$order->complaint)
                            <button type="button" 
                                    wire:click="openComplaintModal({{ $order->id }})"
                                    class="bg-white hover:bg-amber-50 text-amber-700 hover:text-amber-800 border border-amber-200 font-bold text-xs px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                <i class="fa-solid fa-rotate-left text-xs"></i>
                                <span>Khiếu nại</span>
                            </button>
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

                        <!-- Nút Xem chi tiết -->
                        <a href="{{ route('customer.order.detail', $order->order_code) }}" 
                           class="bg-white hover:bg-gray-100 text-gray-700 hover:text-brand-700 border border-gray-200 font-bold text-xs px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-2xs">
                            <i class="fa-solid fa-eye text-xs text-gray-400"></i>
                            <span>Xem chi tiết</span>
                        </a>
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

</div>
