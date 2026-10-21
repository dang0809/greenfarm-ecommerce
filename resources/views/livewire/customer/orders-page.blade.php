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
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black text-purple-700 bg-purple-100 border border-purple-300 px-2 py-0.5 rounded-md">
                                        <i class="fa-solid fa-truck-ramp-box text-[9px]"></i> GIAO RAU VIP ({{ $order->note ?? 'Đợt định kỳ' }})
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
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                            <i class="fa-solid fa-check text-[10px]"></i> {{ $isVipPayment ? 'Thanh toán & Kích hoạt thành công' : 'Đã giao thành công' }}
                        </span>
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
                            <a href="" 
                               class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow-xs transition-all flex items-center gap-1.5 active:scale-95">
                                <i class="fa-solid fa-qrcode text-xs"></i>
                                <span>Thanh toán ngay</span>
                            </a>
                        @endif

                        <!-- Khiếu nại hoàn tiền (Kiểm tra mốc 24h từ order_status_histories) -->
                        @if ($order->status === 'delivered' && $order->order_type === 'retail')
                            @php
                                $deliveredHistory = $order->statusHistories?->firstWhere('new_status', 'delivered');
                                $deliveredAt = $deliveredHistory ? $deliveredHistory->created_at : $order->updated_at;
                                $hoursSinceDelivered = $deliveredAt ? $deliveredAt->diffInHours(now()) : 999;
                                $canRefund = $hoursSinceDelivered <= 24;
                            @endphp

                            @if ($order->complaint)
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl">
                                    <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
                                    @if ($order->complaint->status === 'resolved')
                                        Đã duyệt hoàn tiền ({{ number_format($order->complaint->refund_amount) }}đ)
                                    @elseif ($order->complaint->status === 'rejected')
                                        Khiếu nại bị từ chối
                                    @else
                                        Đang chờ duyệt hoàn tiền
                                    @endif
                                </span>
                            @elseif ($canRefund)
                                <a href="" 
                                   class="bg-white hover:bg-amber-50 text-amber-700 hover:text-amber-800 border border-amber-200 font-bold text-xs px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-2xs">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                    <span>Khiếu nại hoàn tiền</span>
                                </a>
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