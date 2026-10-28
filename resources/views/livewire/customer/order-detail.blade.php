<div class="pt-28 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @php
        $isVipPayment  = $order->order_type === 'subscription';
        $isVipDelivery = $order->order_type === 'subscription_delivery';

        // Xử lý đợt giao VIP
        $batchInfo = '';
        if ($isVipDelivery) {
            $sched = $order->deliverySchedule;
            if ($sched) {
                $total = 8;
                $batchInfo = " (Đợt {$sched->delivery_order}/{$total})";
            }
        }
    @endphp


    <!-- 2. TIÊU ĐỀ, BADGES & NÚT COPY MÃ ĐƠN -->
    <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-7 shadow-sm mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-2 flex-wrap">
                <!-- Mã đơn kèm nút sao chép -->
                <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 px-3 py-1 rounded-xl">
                    <span class="text-base sm:text-lg font-black text-gray-900 tracking-tight font-mono">
                        #{{ $order->order_code }}
                    </span>
                    <button type="button" 
                            x-data 
                            @click="navigator.clipboard.writeText('{{ $order->order_code }}'); $dispatch('show-toast', {type: 'success', message: 'Đã sao chép mã đơn: {{ $order->order_code }}'})" 
                            class="text-gray-400 hover:text-brand-600 transition cursor-pointer p-0.5" 
                            title="Sao chép mã đơn hàng">
                        <i class="fa-regular fa-copy text-xs"></i>
                    </button>
                </div>

                <!-- Badge loại đơn -->
                @if ($isVipPayment)
                    <span class="inline-flex items-center gap-1 text-xs font-extrabold text-purple-700 bg-purple-50 border border-purple-200 px-3 py-1 rounded-full shadow-2xs">
                        <i class="fa-solid fa-crown text-amber-500 text-[10px]"></i> Đơn Mua Gói VIP
                    </span>
                @elseif ($isVipDelivery)
                    <span class="inline-flex items-center gap-1 text-xs font-extrabold text-purple-700 bg-purple-50 border border-purple-200 px-3 py-1 rounded-full shadow-2xs">
                        <i class="fa-solid fa-truck-ramp-box text-purple-600 text-[10px]"></i> Giao Rau VIP{{ $batchInfo }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-gray-700 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-full">
                        <i class="fa-solid fa-basket-shopping text-brand-600 text-[10px]"></i> Đơn Nông Sản Lẻ
                    </span>
                @endif

                <!-- Badge trạng thái đơn hàng -->
                @if ($order->status === 'delivered')
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                            Đã giao hàng
                        </span>
                        @if ($order->complaint && $order->complaint->status === 'resolved')
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                                Đã hoàn tiền
                            </span>
                        @elseif ($order->complaint && $order->complaint->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-clock text-amber-600 text-xs animate-pulse"></i>
                                Đang yêu cầu hoàn tiền
                            </span>
                        @elseif ($order->complaint && $order->complaint->status === 'rejected')
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-800 bg-red-50 border border-red-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-circle-xmark text-red-600 text-xs"></i>
                                Khiếu nại bị từ chối
                            </span>
                        @endif
                    </div>
                @elseif ($order->status === 'cancelled')
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @if ($order->cancelled_by === 'customer')
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-circle-xmark text-xs"></i>
                                Người dùng hủy
                            </span>
                        @elseif ($order->cancelled_by === 'system')
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 bg-gray-100 border border-gray-300 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-clock text-xs"></i>
                                Hệ thống hủy
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-circle-xmark text-xs"></i>
                                Nhân viên hủy
                            </span>
                        @endif

                    </div>
                @elseif ($order->status === 'shipped')
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        Đang giao hàng
                    </span>
                @elseif ($order->status === 'processing')
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Chờ lấy hàng
                    </span>
                @elseif ($order->status === 'pending')
                    @if ($order->payment?->status === 'success')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Chờ xác nhận
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Chờ thanh toán
                        </span>
                    @endif
                @endif
            </div>

            <p class="text-xs sm:text-sm text-gray-500">
                Thời gian đặt: <span class="font-medium text-gray-700">{{ $order->created_at->format('H:i • d/m/Y') }}</span>
            </p>

            <!-- Thông báo tóm tắt trạng thái hiện tại -->
            @php
                $statusSummary = match(true) {
                    $order->status === 'pending' && $order->payment?->status === 'pending' => 'Đơn hàng của bạn đã được hệ thống ghi nhận. Vui lòng hoàn tất thanh toán để nông trại bắt đầu chuẩn bị hàng nhé!',
                    $order->status === 'pending' && $order->payment?->status === 'success' && $isVipDelivery => "Đợt giao rau định kỳ{$batchInfo} đã được khởi tạo. Hệ thống đang chờ nhân viên Nông trại Đất Lành kiểm tra và xác nhận đơn hàng.",
                    $order->status === 'pending' && $order->payment?->status === 'success' => 'Bạn đã thanh toán thành công. Hệ thống đang chờ nhân viên Nông trại Đất Lành kiểm tra và xác nhận đơn hàng.',
                    $order->status === 'processing' && $isVipDelivery => "Nông trại đang thu hoạch & đóng gói thùng rau VIP{$batchInfo} theo chuẩn VietGAP, chờ shipper đến lấy hàng.",
                    $order->status === 'processing' => 'Đơn hàng đã được xác nhận & đang đóng gói, chờ shipper đến lấy hàng.',
                    $order->status === 'shipped' => 'Shipper đã lấy hàng thành công và đang trên đường giao đơn hàng này tới bạn. Vui lòng chú ý điện thoại nhé!',
                    $order->status === 'delivered' && $isVipDelivery => "Đã giao thành công đợt rau VIP{$batchInfo} đến bạn!",
                    $order->status === 'delivered' => 'Đơn hàng đã được giao thành công đến bạn! Cảm ơn bạn đã tin chọn nông sản sạch GreenFarm.',
                    $order->status === 'cancelled' => 'Đơn hàng này đã được hủy ' . ($order->cancelled_by === 'customer' ? 'bởi bạn.' : ($order->cancelled_by === 'system' ? 'bởi hệ thống (quá hạn thanh toán).' : 'bởi Nhân viên nông trại.')),
                    default => ''
                };
            @endphp
            @if ($statusSummary)
                <div class="mt-3 p-3 bg-brand-50/70 border border-brand-100 rounded-2xl flex items-center gap-2.5 text-xs text-brand-900">
                    <i class="fa-solid fa-circle-info text-brand-600 text-sm shrink-0"></i>
                    <p class="leading-relaxed font-medium">{{ $statusSummary }}</p>
                </div>
            @endif
        </div>

        <a href="{{ route('customer.profile', ['tab' => 'orders']) }}" class="inline-flex items-center justify-center gap-2 text-xs font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 px-4 py-2.5 rounded-xl transition-all self-start sm:self-auto shadow-2xs">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span>Danh sách đơn hàng</span>
        </a>
    </div>

    <!-- 3. BANNER KHIẾU NẠI & HOÀN TIỀN (NẾU CÓ HOÀN TIỀN HOẶC KHIẾU NẠI) -->
    @if ($order->complaint && $order->complaint->status === 'resolved')
        <div class="rounded-3xl p-5 sm:p-6 mb-6 bg-gradient-to-r from-emerald-50 via-teal-50 to-green-50 border border-emerald-200 text-emerald-950 shadow-sm relative overflow-hidden">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md">
                    <i class="fa-solid fa-hand-holding-dollar text-base"></i>
                </div>
                <div class="space-y-2 flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm sm:text-base text-emerald-900">Khiếu nại thành công • Đã hoàn tiền</h3>
                            <span class="text-[10px] font-extrabold uppercase bg-emerald-200 text-emerald-900 px-2 py-0.5 rounded-md">Bồi hoàn 100%</span>
                        </div>
                        @if($order->complaint->updated_at)
                            <span class="text-xs font-semibold text-emerald-700 font-mono">{{ $order->complaint->updated_at->format('H:i • d/m/Y') }}</span>
                        @endif
                    </div>

                    <p class="text-xs sm:text-sm text-emerald-900 leading-relaxed">
                        Số tiền hoàn: <b class="text-emerald-800 text-base font-black">{{ number_format($order->complaint->refund_amount ?? $order->total) }}đ</b> 
                        @if(!empty($order->complaint->bank_name))
                            về STK <span class="font-bold font-mono bg-white/90 px-2 py-0.5 rounded-lg border border-emerald-200">{{ $order->complaint->bank_name }} - {{ $order->complaint->bank_account_number }} ({{ $order->complaint->bank_account_name }})</span>
                        @endif
                    </p>

                    @if(!empty($order->complaint->resolution_note))
                        <div class="p-3 bg-white/70 rounded-2xl border border-emerald-200/80 text-xs text-gray-700 leading-relaxed">
                            <span class="font-bold text-emerald-800"><i class="fa-solid fa-shield-cat mr-1"></i>Ghi chú từ Nông trại:</span> 
                            {{ $order->complaint->resolution_note }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @elseif ($order->complaint && $order->complaint->status === 'rejected')
        <div class="rounded-3xl p-5 sm:p-6 mb-6 bg-red-50 border border-red-200 text-red-950 shadow-sm flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-red-600 text-white flex items-center justify-center shrink-0 shadow-md">
                <i class="fa-solid fa-circle-xmark text-base"></i>
            </div>
            <div class="space-y-1.5 flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm sm:text-base text-red-900">Khiếu nại không được chấp thuận</h3>
                        <span class="text-[10px] font-extrabold uppercase bg-red-200 text-red-900 px-2 py-0.5 rounded-md">Từ chối bồi hoàn</span>
                    </div>
                    @if($order->complaint->updated_at)
                        <span class="text-xs font-semibold text-red-700 font-mono">{{ $order->complaint->updated_at->format('H:i • d/m/Y') }}</span>
                    @endif
                </div>
                <div class="p-3 bg-white/80 rounded-2xl border border-red-200 text-xs text-gray-800 leading-relaxed">
                    <span class="font-bold text-red-700"><i class="fa-solid fa-circle-exclamation mr-1"></i>Lý do từ chối:</span> 
                    {{ $order->complaint->resolution_note ?: 'Sản phẩm không đáp ứng điều kiện bảo hành hoặc thời gian khiếu nại quá 24h.' }}
                </div>
                @if ($order->complaint->reason)
                    <p class="text-[11px] text-gray-500 pt-0.5">
                        <span class="font-semibold text-gray-600">Nội dung khiếu nại của bạn:</span> "{{ $order->complaint->reason }}"
                    </p>
                @endif
            </div>
        </div>
    @elseif ($order->complaint && $order->complaint->status === 'pending')
        <div class="rounded-3xl p-5 sm:p-6 mb-6 bg-amber-50 border border-amber-200 text-amber-950 shadow-sm flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md">
                <i class="fa-solid fa-clock-rotate-left text-base"></i>
            </div>
            <div class="space-y-1 flex-1">
                <h3 class="font-bold text-sm sm:text-base text-amber-900">Yêu cầu khiếu nại đang chờ xử lý</h3>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Đội ngũ Chăm sóc khách hàng Nông trại Đất Lành đang kiểm định ảnh hư hỏng và sẽ phản hồi bồi hoàn trong vòng 24 giờ.
                </p>
            </div>
        </div>
    @endif

    <!-- 4. BỐ CỤC 2 CỘT CHUẨN (7 CỘT TRÁI - 5 CỘT PHẢI - GAP-8) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- CỘT TRÁI (7 CỘT): NÔNG SẢN, ĐỊA CHỈ & TIMELINE ĐƠN HÀNG -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Khối 1: Danh sách sản phẩm trong đơn -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-7 shadow-sm space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-basket-shopping text-brand-600"></i> Nông Sản Trong Đơn ({{ $order->items->count() }} món)
                    </h2>
                    <span class="text-xs font-semibold text-brand-700 bg-brand-50 border border-brand-200 px-2.5 py-0.5 rounded-lg">
                        Canh tác hữu cơ
                    </span>
                </div>

                <!-- Danh sách mặt hàng -->
                <div class="space-y-4">
                    @forelse ($order->items as $item)
                        @php
                            $product = $item->product;
                            $thumb = $product?->productImages?->first()?->image_url ?? $product?->image_url ?? 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?auto=format&fit=crop&q=80&w=200';
                            $unit = $product?->unit ?? 'phần';
                        @endphp
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-50 last:border-0 last:pb-0">
                            <div class="flex items-start gap-3.5">
                                <img src="{{ $thumb }}" alt="{{ $item->product_name }}" class="w-16 h-16 rounded-2xl object-cover border border-gray-100 shrink-0">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">
                                        @if ($product && !empty($product->slug))
                                            <a href="{{ route('customer.product.detail', $product->slug) }}" class="hover:text-brand-600 transition">
                                                {{ $item->product_name }}
                                            </a>
                                        @else
                                            <span>{{ $item->product_name }}</span>
                                        @endif
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        @if ($isVipDelivery)
                                            <span class="text-purple-700 font-semibold">Số lượng: {{ $item->quantity }} • Đã bao gồm trong Gói VIP</span>
                                        @else
                                            {{ number_format($item->price, 0, ',', '.') }}đ/{{ $unit }} × {{ $item->quantity }}
                                        @endif
                                    </p>
                                    
                                    <!-- Nút truy xuất nguồn gốc lô hàng -->
                                    @if ($item->batch && !empty($item->batch->batch_code))
                                        <a href="{{ route('customer.traceability.detail', $item->batch->batch_code) }}" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 border border-brand-200 px-2.5 py-1 rounded-lg mt-2 transition-colors">
                                            <i class="fa-solid fa-qrcode text-[10px]"></i>
                                            <span>Lô: {{ $item->batch->batch_code }} (Xem nhật ký)</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <span class="text-sm font-extrabold {{ $isVipDelivery ? 'text-purple-700' : 'text-gray-900' }} self-end sm:self-auto">
                                {{ $isVipDelivery ? '0đ (VIP)' : number_format($item->subtotal, 0, ',', '.') . 'đ' }}
                            </span>
                        </div>
                    @empty
                        @if ($isVipPayment)
                            <div class="py-4 text-center text-xs text-gray-500">
                                <i class="fa-solid fa-crown text-purple-600 text-xl mb-1 block"></i>
                                Gói Hội Viên Rau Củ VIP (Kích hoạt hợp đồng giao định kỳ)
                            </div>
                        @else
                            <div class="py-4 text-center text-xs text-gray-400">
                                Không có thông tin sản phẩm
                            </div>
                        @endif
                    @endforelse
                </div>
            </div>

            <!-- Khối 2: Thông tin nhận hàng -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-7 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-brand-600"></i> Thông Tin Nhận Hàng
                </h2>

                <div class="space-y-2 text-xs sm:text-sm text-gray-600">
                    <p class="font-bold text-gray-900 text-sm">
                        {{ $order->recipient_name }} • <span class="font-mono">{{ $order->recipient_phone }}</span>
                    </p>
                    <p class="leading-relaxed">{{ $order->recipient_address }}</p>
                    <div class="pt-2 border-t border-gray-50 flex flex-col gap-1 text-xs">
                        <p class="text-gray-500">
                            Hình thức giao: <span class="font-semibold text-gray-800">{{ $order->shipping_provider ?? 'Giao hỏa tốc (Nông trại tự giao)' }}</span>
                        </p>
                        @if (!empty($order->tracking_code))
                            <p class="text-gray-500">
                                Mã vận đơn: <span class="font-mono font-bold text-gray-800">{{ $order->tracking_code }}</span>
                            </p>
                        @endif
                        @if (!empty($order->note))
                            <p class="text-gray-500">
                                Ghi chú giao hàng: <span class="italic text-gray-800 font-medium">"{{ $order->note }}"</span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Khối 3: Timeline đơn hàng (Chỉ hiện các mốc thực tế) -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-7 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-timeline text-brand-600"></i> Lịch Sử Trạng Thái Đơn Hàng
                    </h2>
                    <span class="text-[11px] font-semibold text-gray-400">Thời gian thực</span>
                </div>

                @php
                    $timelineEvents = [];

                    // 1. Duyệt qua statusHistories
                    foreach ($order->statusHistories as $h) {
                        $status = $h->new_status;
                        $old    = $h->old_status;
                        $note   = mb_strtolower($h->note ?? '', 'UTF-8');
                        $time   = $h->created_at;

                        if ($status === 'delivered') {
                            $timelineEvents[] = [
                                'title' => 'Giao hàng thành công',
                                'desc'  => $isVipDelivery ? "Đã giao thành công đợt rau VIP{$batchInfo} đến bạn!" : 'Đơn hàng đã được giao thành công đến bạn! Cảm ơn bạn đã tin chọn nông sản sạch GreenFarm.',
                                'icon'  => 'fa-circle-check',
                                'color' => 'emerald',
                                'time'  => $time,
                                'order' => 10,
                            ];
                        } elseif ($status === 'shipped') {
                            $timelineEvents[] = [
                                'title' => 'Đang giao hàng',
                                'desc'  => 'Shipper đã lấy hàng thành công và đang trên đường giao đơn hàng này tới bạn. Vui lòng chú ý điện thoại nhé!',
                                'icon'  => 'fa-truck-fast',
                                'color' => 'blue',
                                'time'  => $time,
                                'order' => 5,
                            ];
                        } elseif ($status === 'processing') {
                            $timelineEvents[] = [
                                'title' => 'Chờ lấy hàng',
                                'desc'  => $isVipDelivery ? "Nông trại đang thu hoạch & đóng gói thùng rau VIP{$batchInfo} theo chuẩn VietGAP, chờ shipper đến lấy hàng." : 'Đơn hàng đã được xác nhận & đang đóng gói, chờ shipper đến lấy hàng.',
                                'icon'  => 'fa-box-open',
                                'color' => 'amber',
                                'time'  => $time,
                                'order' => 4,
                            ];
                        } elseif ($status === 'pending' && $old === 'pending') {
                            if ($isVipDelivery) {
                                $timelineEvents[] = [
                                    'title' => "Chờ xác nhận đợt giao VIP{$batchInfo}",
                                    'desc'  => "Đợt giao rau định kỳ{$batchInfo} đã được khởi tạo. Hệ thống đang chờ nhân viên Nông trại Đất Lành kiểm tra và xác nhận đơn hàng.",
                                    'icon'  => 'fa-crown',
                                    'color' => 'purple',
                                    'time'  => $time,
                                    'order' => 2,
                                ];
                            } else {
                                $timelineEvents[] = [
                                    'title' => 'Chờ xác nhận',
                                    'desc'  => 'Bạn đã thanh toán thành công. Hệ thống đang chờ nhân viên Nông trại Đất Lành kiểm tra và xác nhận đơn hàng.',
                                    'icon'  => 'fa-credit-card',
                                    'color' => 'emerald',
                                    'time'  => $time,
                                    'order' => 2,
                                ];
                            }
                        } elseif ($status === 'pending' && empty($old)) {
                            if ($isVipDelivery) {
                                $timelineEvents[] = [
                                    'title' => "Khởi tạo đợt giao VIP{$batchInfo}",
                                    'desc'  => "Hệ thống tự động kích hoạt đợt giao rau{$batchInfo} từ Gói VIP.",
                                    'icon'  => 'fa-truck-ramp-box',
                                    'color' => 'purple',
                                    'time'  => $time,
                                    'order' => 1,
                                ];
                            } else {
                                $timelineEvents[] = [
                                    'title' => ($order->payment?->status === 'success') ? 'Đơn hàng đã được đặt' : 'Chờ thanh toán',
                                    'desc'  => ($order->payment?->status === 'success') ? 'Đơn hàng của bạn đã được hệ thống ghi nhận.' : 'Đơn hàng của bạn đã được hệ thống ghi nhận. Vui lòng hoàn tất thanh toán để nông trại bắt đầu chuẩn bị hàng nhé!',
                                    'icon'  => 'fa-file-lines',
                                    'color' => 'gray',
                                    'time'  => $time,
                                    'order' => 1,
                                ];
                            }
                        } elseif ($status === 'cancelled') {
                            $timelineEvents[] = [
                                'title' => 'Đơn hàng đã hủy',
                                'desc'  => $h->note ?: ($order->cancel_reason ?: 'Đơn hàng đã được hủy.'),
                                'icon'  => 'fa-circle-xmark',
                                'color' => 'red',
                                'time'  => $time,
                                'order' => 20,
                            ];
                        }
                    }

                    // 2. Toàn bộ tiến trình khiếu nại đọc từ bảng complaints
                    if ($order->complaint) {
                        $c = $order->complaint;
                        $cTime = $c->created_at ?? $order->updated_at;

                        // Mốc 1: Khách gửi khiếu nại
                        $timelineEvents[] = [
                            'title' => 'Khách gửi khiếu nại hoàn tiền',
                            'desc'  => 'Nội dung: "' . $c->reason . '"',
                            'icon'  => 'fa-triangle-exclamation',
                            'color' => 'amber',
                            'time'  => $cTime,
                            'order' => 20,
                        ];

                        // Mốc 2: Kết quả xử lý khiếu nại (Đã hoàn tiền hoặc Từ chối)
                        if ($c->status === 'resolved') {
                            $timelineEvents[] = [
                                'title' => 'Bồi hoàn tiền thành công',
                                'desc'  => $c->resolution_note ?: 'Nông trại đã chuyển khoản bồi hoàn tiền cho đơn hàng này.',
                                'icon'  => 'fa-hand-holding-dollar',
                                'color' => 'emerald',
                                'time'  => $c->updated_at ?? $cTime,
                                'order' => 30,
                            ];
                        } elseif ($c->status === 'rejected') {
                            $timelineEvents[] = [
                                'title' => 'Từ chối khiếu nại bồi hoàn',
                                'desc'  => $c->resolution_note ?: 'Nông trại từ chối khiếu nại bồi hoàn cho đơn hàng này.',
                                'icon'  => 'fa-circle-xmark',
                                'color' => 'red',
                                'time'  => $c->updated_at ?? $cTime,
                                'order' => 30,
                            ];
                        }
                    }

                    // Sắp xếp sự kiện mới nhất lên trên
                    usort($timelineEvents, function($a, $b) {
                        if ($a['time']->equalTo($b['time'])) {
                            return $b['order'] <=> $a['order'];
                        }
                        return $b['time'] <=> $a['time'];
                    });
                @endphp

                @if (!empty($timelineEvents))
                    <div class="mt-6 space-y-7 relative before:absolute before:left-[17px] before:top-3 before:bottom-3 before:w-[2px] before:bg-gray-100">
                        @foreach ($timelineEvents as $index => $event)
                            @php
                                $isLatest  = ($index === 0);
                                $title     = $event['title'];
                                $desc      = $event['desc'];
                                $icon      = $event['icon'];
                                $stepColor = $event['color'];
                                $time      = $event['time'];

                                if ($isLatest) {
                                    $iconBg = match($stepColor) {
                                        'red'     => 'bg-red-600 text-white shadow-lg ring-4 ring-red-100',
                                        'emerald' => 'bg-emerald-600 text-white shadow-lg ring-4 ring-emerald-100',
                                        'amber'   => 'bg-amber-500 text-white shadow-lg ring-4 ring-amber-100 animate-pulse',
                                        'blue'    => 'bg-blue-600 text-white shadow-lg ring-4 ring-blue-100 animate-pulse',
                                        'purple'  => 'bg-purple-600 text-white shadow-lg ring-4 ring-purple-100',
                                        default   => 'bg-brand-600 text-white shadow-lg ring-4 ring-brand-100',
                                    };
                                    $cardBoxBg = match($stepColor) {
                                        'red'     => 'bg-red-50/80 border border-red-200 text-red-900',
                                        'emerald' => 'bg-emerald-50/80 border border-emerald-200 text-emerald-900',
                                        'amber'   => 'bg-amber-50/80 border border-amber-200 text-amber-900',
                                        'blue'    => 'bg-blue-50/80 border border-blue-200 text-blue-900',
                                        'purple'  => 'bg-purple-50/80 border border-purple-200 text-purple-900',
                                        default   => 'bg-gray-50 border border-gray-200 text-gray-900',
                                    };
                                    $badgeBg = match($stepColor) {
                                        'red'     => 'bg-red-200 text-red-900',
                                        'emerald' => 'bg-emerald-200 text-emerald-900',
                                        'amber'   => 'bg-amber-200 text-amber-900',
                                        'blue'    => 'bg-blue-200 text-blue-900',
                                        'purple'  => 'bg-purple-200 text-purple-900',
                                        default   => 'bg-gray-200 text-gray-800',
                                    };
                                } else {
                                    $iconBg = match($stepColor) {
                                        'red'     => 'bg-red-50 border-2 border-red-300 text-red-600',
                                        'amber'   => 'bg-amber-50 border-2 border-amber-300 text-amber-600',
                                        'emerald' => 'bg-emerald-50 border-2 border-emerald-300 text-emerald-600',
                                        'blue'    => 'bg-blue-50 border-2 border-blue-300 text-blue-600',
                                        'purple'  => 'bg-purple-50 border-2 border-purple-300 text-purple-600',
                                        default   => 'bg-gray-50 border-2 border-gray-300 text-gray-600',
                                    };
                                }
                            @endphp

                            <div class="relative flex items-start gap-4">
                                <span class="w-9 h-9 rounded-full {{ $iconBg }} flex items-center justify-center shrink-0 z-10">
                                    <i class="fa-solid {{ $icon }} text-xs"></i>
                                </span>

                                @if ($isLatest)
                                    <div class="flex-1 -mt-0.5 {{ $cardBoxBg }} p-3.5 rounded-2xl">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-xs sm:text-sm font-extrabold">
                                                    {{ $title }}
                                                </h3>
                                                <span class="text-[9px] font-bold {{ $badgeBg }} px-1.5 py-0.5 rounded">
                                                    Hiện tại
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-bold font-mono opacity-80">
                                                {{ $time->format('H:i • d/m/Y') }}
                                            </span>
                                        </div>
                                        @if (!empty($desc))
                                            <p class="text-xs mt-1 leading-relaxed">
                                                {{ $desc }}
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <div class="flex-1 -mt-0.5">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                                            <h3 class="text-xs sm:text-sm font-semibold {{ $stepColor === 'red' ? 'text-red-700' : ($stepColor === 'amber' ? 'text-amber-800' : ($stepColor === 'purple' ? 'text-purple-800' : 'text-gray-800')) }}">{{ $title }}</h3>
                                            <span class="text-[11px] text-gray-400 font-mono">{{ $time->format('H:i • d/m/Y') }}</span>
                                        </div>
                                        @if (!empty($desc))
                                            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $desc }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-gray-400">
                        Chưa có lịch sử trạng thái
                    </div>
                @endif
            </div>

        </div>

        <!-- CỘT PHẢI (5 CỘT): CHI PHÍ, BIÊN LAI THANH TOÁN PAYOS & HÀNH ĐỘNG -->
        <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-28">
            
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-5">
                <h2 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-brand-600"></i> Chi Tiết Thanh Toán
                </h2>

                <!-- Bảng tính chi phí -->
                <div class="space-y-3 text-xs sm:text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>Tạm tính tiền hàng:</span>
                        <span class="font-bold text-gray-900">
                            @if ($isVipDelivery)
                                0đ <span class="text-xs font-normal text-purple-600">(Đã gồm trong gói VIP)</span>
                            @else
                                {{ number_format($order->subtotal, 0, ',', '.') }}đ
                            @endif
                        </span>
                    </div>

                    @if ($order->points_used > 0)
                        <div class="flex justify-between text-brand-700">
                            <span>Khấu trừ điểm thưởng:</span>
                            <span class="font-bold">-{{ number_format($order->points_used * 1000, 0, ',', '.') }}đ</span>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <span>Phí vận chuyển:</span>
                        <span class="font-bold text-gray-900">
                            {{ $order->shipping_fee > 0 ? number_format($order->shipping_fee, 0, ',', '.') . 'đ' : 'Miễn phí' }}
                        </span>
                    </div>
                </div>

                <!-- Điểm tích lũy của đơn hàng -->
                @if (!$isVipDelivery)
                    <div class="p-3.5 bg-brand-50 border border-brand-200 rounded-2xl flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-coins text-brand-600"></i>
                            <span class="font-bold text-brand-900">Điểm tích lũy nhận được:</span>
                        </div>
                        <span class="font-black text-brand-700">+{{ number_format($order->points_earned ?? 0) }} điểm</span>
                    </div>
                @endif

                <!-- Tổng thanh toán -->
                <div class="pt-3 border-t border-gray-100 flex items-baseline justify-between">
                    <div>
                        <span class="text-xs text-gray-500 font-medium block">Tổng thanh toán:</span>
                        @if ($isVipPayment)
                            <span class="text-[11px] text-purple-700 font-bold">Gói Hội Viên VIP</span>
                        @elseif ($isVipDelivery)
                            <span class="text-[11px] text-purple-700 font-bold">Giao Định Kỳ VIP{{ $batchInfo }}</span>
                        @endif
                    </div>
                    <span class="text-2xl font-black {{ ($isVipPayment || $isVipDelivery) ? 'text-purple-700' : 'text-brand-700' }}">
                        {{ number_format($order->total, 0, ',', '.') }}đ
                    </span>
                </div>

                <!-- KHỐI BIÊN LAI THANH TOÁN PAYOS -->
                @php
                    $payRefCode = $order->payment?->transaction_code ?? ('POS-ORD-' . $order->order_code);
                @endphp
                <div class="p-4 bg-gray-50 border border-gray-200/80 rounded-2xl space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Cổng thanh toán:</span>
                        <span class="font-bold text-gray-800 flex items-center gap-1.5 uppercase">
                            <i class="fa-solid fa-qrcode text-brand-600"></i> {{ strtoupper($order->payment?->method ?? 'PAYOS') }} 
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Trạng thái GD:</span>
                        @if ($order->payment?->status === 'success')
                            <span class="font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-[10px]"></i> Đã thanh toán (PAID)
                            </span>
                        @elseif ($order->payment?->status === 'failed')
                            <span class="font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                <i class="fa-solid fa-circle-xmark text-[10px]"></i> Thất bại / Đã hủy
                            </span>
                        @else
                            <span class="font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                <i class="fa-solid fa-clock text-[10px]"></i> Đang chờ quét mã
                            </span>
                        @endif
                    </div>
                    
                    <!-- Mã đối chiếu ngân hàng kèm nút Copy -->
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Mã đối chiếu GD:</span>
                        <div class="flex items-center gap-1.5 bg-white border border-gray-200 px-2 py-0.5 rounded-lg shadow-2xs">
                            <span class="font-mono text-gray-800 font-bold text-xs select-all">{{ $payRefCode }}</span>
                            <button type="button" 
                                    x-data 
                                    @click="navigator.clipboard.writeText('{{ $payRefCode }}'); $dispatch('show-toast', {type: 'success', message: 'Đã sao chép mã GD: {{ $payRefCode }}'})" 
                                    class="text-gray-400 hover:text-brand-600 transition cursor-pointer p-0.5" 
                                    title="Sao chép mã đối chiếu ngân hàng">
                                <i class="fa-regular fa-copy text-[11px]"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-gray-400 pt-1.5 border-t border-gray-200/60">
                        <span>Thời gian ghi nhận:</span>
                        <span class="font-mono">
                            {{ $order->payment?->paid_at ? $order->payment->paid_at->format('H:i • d/m/Y') : 'Chưa ghi nhận' }}
                        </span>
                    </div>
                </div>

                <!-- Nhóm nút hành động tinh gọn -->
                <div class="pt-1 space-y-2.5">
                    <!-- 1. Mua lại đơn này: Dành cho đơn bán lẻ đã giao hoặc đã hủy -->
                    @if (in_array($order->status, ['delivered', 'cancelled']) && !in_array($order->order_type, ['subscription', 'subscription_delivery']))
                        <button type="button" 
                                wire:click="reorder" 
                                class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-rotate-right text-xs"></i>
                            <span>Mua lại đơn này</span>
                        </button>
                    @endif

                    <!-- 2. Thanh toán ngay: Dành cho đơn pending chưa trả tiền -->
                    @if ($order->status === 'pending' && $order->payment?->status === 'pending')
                        <button type="button" 
                                wire:click="payNow" 
                                wire:loading.attr="disabled"
                                class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-qrcode text-xs" wire:loading.remove></i>
                            <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading></i>
                            <span>Thanh toán ngay</span>
                        </button>

                        <button type="button" 
                                wire:click="confirmCancel" 
                                wire:confirm="Bạn có chắc chắn muốn hủy đơn hàng #{{ $order->order_code }} không?"
                                class="w-full bg-white hover:bg-red-50 text-gray-600 hover:text-red-600 border border-gray-200 font-bold py-2.5 rounded-2xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-regular fa-trash-can text-xs"></i>
                            <span>Hủy đơn hàng này</span>
                        </button>
                    @endif

                    <!-- 3. Yêu cầu xử lý / Khiếu nại hoàn tiền dập nát (24H) -->
                    @if ($order->status === 'delivered' && $order->order_type === 'retail' && !$order->complaint)
                        <button type="button" 
                                wire:click="openComplaintModal"
                                class="w-full bg-white hover:bg-amber-50 text-amber-700 hover:text-amber-800 border border-amber-200 hover:border-amber-300 font-semibold py-2.5 rounded-2xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-shield-cat text-xs text-amber-600"></i>
                            <span>Khiếu nại / Yêu cầu bồi hoàn (24H)</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Cam kết bảo hiểm nông sản 24H -->
            <div class="bg-brand-50/60 border border-brand-100 rounded-3xl p-5 flex items-center gap-3.5 text-xs text-brand-900 shadow-2xs">
                <i class="fa-solid fa-shield-halved text-brand-600 text-2xl shrink-0"></i>
                <p class="leading-relaxed">
                    <b>Bảo hiểm tươi ngon 24H:</b> Cam kết bồi thường 100% tiền hoặc giao bù nếu nông sản phát hiện dập úng khi nhận hàng.
                </p>
            </div>

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
                        <label class="font-bold text-gray-700 block mb-1">Mô tả chi tiết tình trạng hư hỏng *</label>
                        <textarea wire:model="complaint_reason" rows="3" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500 focus:border-brand-500" placeholder="VD: Cải bó xôi bị dập nát nhiều lá, cà chua bị vỡ hỏng do vận chuyển..."></textarea>
                        @error('complaint_reason') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Số tiền đề xuất hoàn (VNĐ) *</label>
                        <input type="number" wire:model="complaint_refund_amount" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500 focus:border-brand-500" placeholder="VD: 55000">
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Tối đa: {{ number_format($order->total) }}đ (Tổng giá trị đơn hàng)</span>
                        @error('complaint_refund_amount') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-1">
                            <label class="font-bold text-gray-700 block mb-1">Ngân hàng *</label>
                            <select wire:model="bank_name" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500 focus:border-brand-500">
                                <option value="">-- Chọn --</option>
                                <option value="MBBank">MB Bank</option>
                                <option value="Vietcombank">Vietcombank</option>
                                <option value="Techcombank">Techcombank</option>
                                <option value="ACB">ACB</option>
                                <option value="VPBank">VPBank</option>
                                <option value="VietinBank">VietinBank</option>
                                <option value="BIDV">BIDV</option>
                                <option value="MoMo">Ví MoMo</option>
                            </select>
                            @error('bank_name') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="font-bold text-gray-700 block mb-1">Số tài khoản / SĐT MoMo *</label>
                            <input type="text" wire:model="bank_account_number" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500 focus:border-brand-500" placeholder="VD: 0391000123456">
                            @error('bank_account_number') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Tên chủ tài khoản (Viết hoa không dấu) *</label>
                        <input type="text" wire:model="bank_account_name" class="w-full rounded-xl border-gray-200 text-xs uppercase focus:ring-brand-500 focus:border-brand-500" placeholder="VD: NGUYEN VAN A">
                        @error('bank_account_name') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Hình ảnh bằng chứng nông sản hư hại (Tối đa 3 ảnh)</label>
                        <input type="file" wire:model="complaint_images" multiple accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                        @error('complaint_images.*') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" wire:click="resetComplaintModal" class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200">
                            Hủy bỏ
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 rounded-xl text-xs font-bold bg-amber-600 text-white hover:bg-amber-700 shadow-sm flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading></i>
                            <span wire:loading.remove><i class="fa-solid fa-paper-plane mr-1"></i>Gửi khiếu nại</span>
                            <span wire:loading>Đang gửi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
