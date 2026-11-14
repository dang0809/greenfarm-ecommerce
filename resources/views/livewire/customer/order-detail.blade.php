<div class="pt-28 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @php
        $isVipPayment = $order->order_type === 'subscription';
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
    <div
        class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-7 shadow-sm mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-2 flex-wrap">
                <!-- Mã đơn kèm nút sao chép -->
                <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 px-3 py-1 rounded-xl">
                    <span class="text-base sm:text-lg font-black text-gray-900 tracking-tight font-mono">
                        #{{ $order->order_code }}
                    </span>
                    <button type="button" x-data
                        @click="navigator.clipboard.writeText('{{ $order->order_code }}'); $dispatch('show-toast', {type: 'success', message: 'Đã sao chép mã đơn: {{ $order->order_code }}'})"
                        class="text-gray-400 hover:text-brand-600 transition cursor-pointer p-0.5"
                        title="Sao chép mã đơn hàng">
                        <i class="fa-regular fa-copy text-xs"></i>
                    </button>
                </div>

                <!-- Badge loại đơn -->
                @if ($isVipPayment)
                    <span
                        class="inline-flex items-center gap-1 text-xs font-extrabold text-purple-700 bg-purple-50 border border-purple-200 px-3 py-1 rounded-full shadow-2xs">
                        <i class="fa-solid fa-crown text-amber-500 text-[10px]"></i> Đơn Mua Gói VIP
                    </span>
                @elseif ($isVipDelivery)
                    <span
                        class="inline-flex items-center gap-1 text-xs font-extrabold text-purple-700 bg-purple-50 border border-purple-200 px-3 py-1 rounded-full shadow-2xs">
                        <i class="fa-solid fa-truck-ramp-box text-purple-600 text-[10px]"></i> Giao Rau
                        VIP{{ $batchInfo }}
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1 text-xs font-bold text-gray-700 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-full">
                        <i class="fa-solid fa-basket-shopping text-brand-600 text-[10px]"></i> Đơn Nông Sản Lẻ
                    </span>
                @endif

                <!-- Badge trạng thái đơn hàng -->
                @if ($order->status === 'delivered')
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                            Đã giao hàng
                        </span>
                        @if ($order->complaint && $order->complaint->status === 'resolved')
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                                Đã hoàn tiền
                            </span>
                        @elseif ($order->complaint && $order->complaint->status === 'pending')
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-clock text-amber-600 text-xs animate-pulse"></i>
                                Đang yêu cầu hoàn tiền
                            </span>
                        @elseif ($order->complaint && $order->complaint->status === 'rejected')
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-red-800 bg-red-50 border border-red-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-circle-xmark text-red-600 text-xs"></i>
                                Khiếu nại bị từ chối
                            </span>
                        @endif
                    </div>
                @elseif ($order->status === 'cancelled')
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @if ($order->cancelled_by === 'customer')
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-circle-xmark text-xs"></i>
                                Người dùng hủy
                            </span>
                        @elseif ($order->cancelled_by === 'system')
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 bg-gray-100 border border-gray-300 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-clock text-xs"></i>
                                Hệ thống hủy
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-3 py-1 rounded-full">
                                <i class="fa-solid fa-circle-xmark text-xs"></i>
                                Nhân viên hủy
                            </span>
                        @endif

                    </div>
                @elseif ($order->status === 'shipped')
                    <span
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        Đang giao hàng
                    </span>
                @elseif ($order->status === 'processing')
                    <span
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Chờ lấy hàng
                    </span>
                @elseif ($order->status === 'pending')
                    @if ($isVipDelivery || $order->payment?->status === 'success')
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Chờ xác nhận
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Chờ thanh toán
                        </span>
                    @endif
                @endif
            </div>

            <p class="text-xs sm:text-sm text-gray-500">
                Thời gian đặt: <span
                    class="font-medium text-gray-700">{{ $order->created_at->format('H:i • d/m/Y') }}</span>
            </p>

            <!-- Thông báo tóm tắt trạng thái hiện tại -->
            @php
                $statusSummary = match (true) {
                    $order->status === 'pending' && $isVipDelivery
                        => "Đợt giao rau định kỳ{$batchInfo} đã được khởi tạo từ gói VIP trả trước. Hệ thống đang chờ nhân viên Nông trại {$farmName} kiểm tra và xác nhận đơn hàng.",
                    $order->status === 'pending' && $order->payment?->status === 'pending'
                        => 'Đơn hàng của bạn đã được hệ thống ghi nhận. Vui lòng hoàn tất thanh toán để nông trại bắt đầu chuẩn bị hàng nhé!',
                    $order->status === 'pending' && $order->payment?->status === 'success'
                        => 'Bạn đã thanh toán thành công. Hệ thống đang chờ nhân viên Nông trại {$farmName} kiểm tra và xác nhận đơn hàng.',
                    $order->status === 'processing' && $isVipDelivery
                        => "Nông trại đang thu hoạch & đóng gói thùng rau VIP{$batchInfo} theo chuẩn VietGAP, chờ shipper đến lấy hàng.",
                    $order->status === 'processing'
                        => 'Đơn hàng đã được xác nhận & đang đóng gói, chờ shipper đến lấy hàng.',
                    $order->status === 'shipped'
                        => 'Shipper đã lấy hàng thành công và đang trên đường giao đơn hàng này tới bạn. Vui lòng chú ý điện thoại nhé!',
                    $order->status === 'delivered' && $isVipDelivery
                        => "Đã giao thành công đợt rau VIP{$batchInfo} đến bạn!",
                    $order->status === 'delivered'
                        => 'Đơn hàng đã được giao thành công đến bạn! Cảm ơn bạn đã tin chọn nông sản sạch {$farmName}.',
                    $order->status === 'cancelled' => 'Đơn hàng này đã được hủy ' .
                        ($order->cancelled_by === 'customer'
                            ? 'bởi bạn.'
                            : ($order->cancelled_by === 'system'
                                ? 'bởi hệ thống (quá hạn thanh toán).'
                                : 'bởi Nhân viên nông trại.')),
                    default => '',
                };
            @endphp
            @if ($statusSummary)
                <div
                    class="mt-3 p-3 bg-brand-50/70 border border-brand-100 rounded-2xl flex items-center gap-2.5 text-xs text-brand-900">
                    <i class="fa-solid fa-circle-info text-brand-600 text-sm shrink-0"></i>
                    <p class="leading-relaxed font-medium">{{ $statusSummary }}</p>
                </div>
            @endif
        </div>

        @php
            $targetStatus = match (true) {
                $order->status === 'pending' && ($isVipDelivery || $order->payment?->status === 'success')
                    => 'pending_confirmation',
                $order->status === 'pending' && $order->payment?->status === 'pending' => 'pending_payment',
                $order->status === 'processing' => 'processing',
                $order->status === 'shipped' => 'shipped',
                $order->status === 'delivered' => $order->complaint ? 'refund' : 'delivered',
                $order->status === 'cancelled' => 'cancelled',
                default => 'all',
            };
        @endphp

        <a href="{{ route('customer.profile', ['tab' => 'orders', 'status' => $targetStatus]) }}"
            class="inline-flex items-center justify-center gap-2 text-xs font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 px-4 py-2.5 rounded-xl transition-all self-start sm:self-auto shadow-2xs">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span>Danh sách đơn hàng</span>
        </a>
    </div>

    <!-- 3. BANNER KHIẾU NẠI & HOÀN TIỀN (NẾU CÓ HOÀN TIỀN HOẶC KHIẾU NẠI) -->
    @if ($order->complaint && $order->complaint->status === 'resolved')
        <div
            class="rounded-3xl p-5 sm:p-6 mb-6 bg-gradient-to-r from-emerald-50 via-teal-50 to-green-50 border border-emerald-200 text-emerald-950 shadow-sm relative overflow-hidden">
            <div class="flex items-start gap-3.5">
                <div
                    class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md">
                    <i class="fa-solid fa-hand-holding-dollar text-base"></i>
                </div>
                <div class="space-y-2 flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm sm:text-base text-emerald-900">Khiếu nại thành công • Đã hoàn
                                tiền</h3>
                            <span
                                class="text-[10px] font-extrabold uppercase bg-emerald-200 text-emerald-900 px-2 py-0.5 rounded-md">Bồi
                                hoàn 100%</span>
                        </div>
                        @if ($order->complaint->updated_at)
                            <span
                                class="text-xs font-semibold text-emerald-700 font-mono">{{ $order->complaint->updated_at->format('H:i • d/m/Y') }}</span>
                        @endif
                    </div>

                    <p class="text-xs sm:text-sm text-emerald-900 leading-relaxed">
                        Số tiền hoàn: <b
                            class="text-emerald-800 text-base font-black">{{ number_format($order->complaint->refund_amount ?? $order->total) }}đ</b>
                        @if (!empty($order->complaint->bank_name))
                            về STK <span
                                class="font-bold font-mono bg-white/90 px-2 py-0.5 rounded-lg border border-emerald-200">{{ $order->complaint->bank_name }}
                                - {{ $order->complaint->bank_account_number }}
                                ({{ $order->complaint->bank_account_name }})</span>
                        @endif
                    </p>

                    @if (!empty($order->complaint->resolution_note))
                        <div
                            class="p-3 bg-white/70 rounded-2xl border border-emerald-200/80 text-xs text-gray-700 leading-relaxed">
                            <span class="font-bold text-emerald-800"><i class="fa-solid fa-shield-cat mr-1"></i>Ghi chú
                                từ Nông trại:</span>
                            {{ $order->complaint->resolution_note }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @elseif ($order->complaint && $order->complaint->status === 'rejected')
        <div
            class="rounded-3xl p-5 sm:p-6 mb-6 bg-red-50 border border-red-200 text-red-950 shadow-sm flex items-start gap-3.5">
            <div
                class="w-10 h-10 rounded-2xl bg-red-600 text-white flex items-center justify-center shrink-0 shadow-md">
                <i class="fa-solid fa-circle-xmark text-base"></i>
            </div>
            <div class="space-y-1.5 flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm sm:text-base text-red-900">Khiếu nại không được chấp thuận</h3>
                        <span
                            class="text-[10px] font-extrabold uppercase bg-red-200 text-red-900 px-2 py-0.5 rounded-md">Từ
                            chối bồi hoàn</span>
                    </div>
                    @if ($order->complaint->updated_at)
                        <span
                            class="text-xs font-semibold text-red-700 font-mono">{{ $order->complaint->updated_at->format('H:i • d/m/Y') }}</span>
                    @endif
                </div>
                <div class="p-3 bg-white/80 rounded-2xl border border-red-200 text-xs text-gray-800 leading-relaxed">
                    <span class="font-bold text-red-700"><i class="fa-solid fa-circle-exclamation mr-1"></i>Lý do từ
                        chối:</span>
                    {{ $order->complaint->resolution_note ?: 'Sản phẩm không đáp ứng điều kiện bảo hành hoặc thời gian khiếu nại quá 24h.' }}
                </div>
                @if ($order->complaint->reason)
                    <p class="text-[11px] text-gray-500 pt-0.5">
                        <span class="font-semibold text-gray-600">Nội dung khiếu nại của bạn:</span>
                        "{{ $order->complaint->reason }}"
                    </p>
                @endif
            </div>
        </div>
    @elseif ($order->complaint && $order->complaint->status === 'pending')
        <div
            class="rounded-3xl p-5 sm:p-6 mb-6 bg-amber-50 border border-amber-200 text-amber-950 shadow-sm flex items-start gap-3.5">
            <div
                class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md">
                <i class="fa-solid fa-clock-rotate-left text-base"></i>
            </div>
            <div class="space-y-1 flex-1">
                <h3 class="font-bold text-sm sm:text-base text-amber-900">Yêu cầu khiếu nại đang chờ xử lý</h3>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Đội ngũ Chăm sóc khách hàng Nông trại {{ $farmName }} đang kiểm định ảnh hư hỏng và sẽ phản hồi
                    bồi hoàn trong vòng 24 giờ.
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
                        <i class="fa-solid fa-basket-shopping text-brand-600"></i> Nông Sản Trong Đơn
                        ({{ $order->items->count() }} món)
                    </h2>
                    <span
                        class="text-xs font-semibold text-brand-700 bg-brand-50 border border-brand-200 px-2.5 py-0.5 rounded-lg">
                        Canh tác hữu cơ
                    </span>
                </div>

                <!-- Danh sách mặt hàng -->
                <div class="space-y-4">
                    @forelse ($order->items as $item)
                        @php
                            $product = $item->product;
                            $thumb =
                                $product?->cover_image_url ??
                                'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?auto=format&fit=crop&q=80&w=200';
                            $unit = $product?->unit ?? 'phần';
                        @endphp
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-50 last:border-0 last:pb-0">
                            <div class="flex items-start gap-3.5">
                                <img src="{{ $thumb }}" alt="{{ $item->product_name }}"
                                    class="w-16 h-16 rounded-2xl object-cover border border-gray-100 shrink-0"
                                    onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?auto=format&fit=crop&q=80&w=200'">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">
                                        @if ($product && !empty($product->slug))
                                            <a href="{{ route('customer.product.detail', $product->slug) }}"
                                                class="hover:text-brand-600 transition">
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
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        @if ($item->batch && !empty($item->batch->batch_code))
                                            <a href="{{ route('customer.traceability.detail', $item->batch->batch_code) }}"
                                                class="inline-flex items-center gap-1.5 text-[11px] font-bold text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 border border-brand-200 px-2.5 py-1 rounded-lg transition-colors">
                                                <i class="fa-solid fa-qrcode text-[10px]"></i>
                                                <span>Lô: {{ $item->batch->batch_code }} (Xem nhật ký)</span>
                                            </a>
                                        @endif

                                        <!-- Nút Đánh giá sản phẩm nếu đơn đã giao thành công (1 sản phẩm / 1 đơn = 1 đánh giá) -->
                                        @if ($order->status === 'delivered' && $product && !empty($product->slug))
                                            @php
                                                // Tìm xem trong đơn hàng này, sản phẩm này đã có đánh giá nào chưa (dù bị chia nhiều lô)
                                                $productReview = $order->items
                                                    ->where('product_id', $item->product_id)
                                                    ->pluck('review')
                                                    ->filter()
                                                    ->first();

                                                // Chỉ hiển thị nút đánh giá ở dòng đầu tiên của sản phẩm trong đơn để tránh bị lặp nút
                                                $isFirstItemOfProduct =
                                                    $order->items->where('product_id', $item->product_id)->first()
                                                        ?->id === $item->id;
                                            @endphp

                                            @if ($productReview)
                                                <span
                                                    class="inline-flex items-center gap-1.5 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg">
                                                    <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                                                    <span>Đã đánh giá ({{ $productReview->rating }} sao)</span>
                                                </span>
                                            @elseif ($isFirstItemOfProduct)
                                                <a href="{{ route('customer.product.detail', $product->slug) }}?tab=reviews#reviews"
                                                    class="inline-flex items-center gap-1.5 text-[11px] font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-2xs px-3 py-1 rounded-lg transition-all active:scale-95">
                                                    <i class="fa-regular fa-star text-[10px]"></i>
                                                    <span>Đánh giá sản phẩm</span>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span
                                class="text-sm font-extrabold {{ $isVipDelivery ? 'text-purple-700' : 'text-gray-900' }} self-end sm:self-auto">
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
                            Hình thức giao: <span
                                class="font-semibold text-gray-800">{{ $order->shipping_provider ?? 'Giao hỏa tốc (Nông trại tự giao)' }}</span>
                        </p>
                        <p class="text-gray-500">
                            Mã vận đơn: <span
                                class="font-mono font-bold text-gray-800">{{ $order->tracking_code ?: 'SHIP-DL-' . date('Ymd', strtotime($order->created_at)) . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </p>
                        @if (!empty($order->note))
                            <p class="text-gray-500">
                                Ghi chú giao hàng: <span
                                    class="italic text-gray-800 font-medium">"{{ $order->note }}"</span>
                            </p>
                        @endif
                    </div>

                    <!-- THẺ SHIPPER PHỤ TRÁCH GIAO HÀNG (MOCK) -->
                    @if (in_array($order->status, ['processing', 'shipped', 'delivered']))
                        <div
                            class="mt-3 p-3.5 sm:p-4 rounded-2xl bg-gradient-to-r from-blue-50/80 to-indigo-50/70 border border-blue-200/80 text-blue-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                                    <i class="fa-solid fa-motorcycle"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-extrabold text-xs sm:text-sm text-blue-950">
                                            Shipper: Nguyễn Văn Bình
                                        </span>
                                        <span
                                            class="text-[10px] font-bold bg-white text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full font-mono">
                                            Đội xe hỏa tốc {{ $farmName }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-blue-800 font-mono mt-0.5">
                                        Xe máy thùng lạnh chuyên dụng • 59-X1 889.24
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="tel:0938445566"
                                    class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition shadow-xs cursor-pointer">
                                    <i class="fa-solid fa-phone text-[10px]"></i> Gọi 0938.445.566
                                </a>
                            </div>
                        </div>
                    @endif
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

                @if (!empty($timeline))
                    <div
                        class="mt-6 space-y-7 relative before:absolute before:left-[17px] before:top-3 before:bottom-3 before:w-[2px] before:bg-gray-100">
                        @foreach ($timeline as $index => $event)
                            @php
                                $isLatest = $index === 0;
                                $title = $event['title'];
                                $desc = $event['desc'];
                                $icon = $event['icon'];
                                $stepColor = $event['color'];
                                $time = $event['time'];

                                if ($isLatest) {
                                    $iconBg = match ($stepColor) {
                                        'red' => 'bg-red-600 text-white shadow-lg ring-4 ring-red-100',
                                        'emerald' => 'bg-emerald-600 text-white shadow-lg ring-4 ring-emerald-100',
                                        'amber'
                                            => 'bg-amber-500 text-white shadow-lg ring-4 ring-amber-100 animate-pulse',
                                        'blue' => 'bg-blue-600 text-white shadow-lg ring-4 ring-blue-100 animate-pulse',
                                        'purple' => 'bg-purple-600 text-white shadow-lg ring-4 ring-purple-100',
                                        default => 'bg-brand-600 text-white shadow-lg ring-4 ring-brand-100',
                                    };
                                    $cardBoxBg = match ($stepColor) {
                                        'red' => 'bg-red-50/80 border border-red-200 text-red-900',
                                        'emerald' => 'bg-emerald-50/80 border border-emerald-200 text-emerald-900',
                                        'amber' => 'bg-amber-50/80 border border-amber-200 text-amber-900',
                                        'blue' => 'bg-blue-50/80 border border-blue-200 text-blue-900',
                                        'purple' => 'bg-purple-50/80 border border-purple-200 text-purple-900',
                                        default => 'bg-gray-50 border border-gray-200 text-gray-900',
                                    };
                                    $badgeBg = match ($stepColor) {
                                        'red' => 'bg-red-200 text-red-900',
                                        'emerald' => 'bg-emerald-200 text-emerald-900',
                                        'amber' => 'bg-amber-200 text-amber-900',
                                        'blue' => 'bg-blue-200 text-blue-900',
                                        'purple' => 'bg-purple-200 text-purple-900',
                                        default => 'bg-gray-200 text-gray-800',
                                    };
                                } else {
                                    $iconBg = match ($stepColor) {
                                        'red' => 'bg-red-50 border-2 border-red-300 text-red-600',
                                        'amber' => 'bg-amber-50 border-2 border-amber-300 text-amber-600',
                                        'emerald' => 'bg-emerald-50 border-2 border-emerald-300 text-emerald-600',
                                        'blue' => 'bg-blue-50 border-2 border-blue-300 text-blue-600',
                                        'purple' => 'bg-purple-50 border-2 border-purple-300 text-purple-600',
                                        default => 'bg-gray-50 border-2 border-gray-300 text-gray-600',
                                    };
                                }
                            @endphp

                            <div class="relative flex items-start gap-4">
                                <span
                                    class="w-9 h-9 rounded-full {{ $iconBg }} flex items-center justify-center shrink-0 z-10">
                                    <i class="fa-solid {{ $icon }} text-xs"></i>
                                </span>

                                @if ($isLatest)
                                    <div class="flex-1 -mt-0.5 {{ $cardBoxBg }} p-3.5 rounded-2xl">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-xs sm:text-sm font-extrabold">
                                                    {{ $title }}
                                                </h3>
                                                <span
                                                    class="text-[9px] font-bold {{ $badgeBg }} px-1.5 py-0.5 rounded">
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
                                            <h3
                                                class="text-xs sm:text-sm font-semibold {{ $stepColor === 'red' ? 'text-red-700' : ($stepColor === 'amber' ? 'text-amber-800' : ($stepColor === 'purple' ? 'text-purple-800' : 'text-gray-800')) }}">
                                                {{ $title }}</h3>
                                            <span
                                                class="text-[11px] text-gray-400 font-mono">{{ $time->format('H:i • d/m/Y') }}</span>
                                        </div>
                                        @if (!empty($desc))
                                            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                                {{ $desc }}</p>
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

                    @php
                        $pointDiscount = max(0, $order->subtotal + $order->shipping_fee - $order->total);
                    @endphp

                    @if ($order->points_used > 0)
                        <div class="flex justify-between text-brand-700">
                            <span>Khấu trừ điểm thưởng ({{ number_format($order->points_used) }} điểm):</span>
                            <span class="font-bold">-{{ number_format($pointDiscount, 0, ',', '.') }}đ</span>
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
                    <div
                        class="p-3.5 bg-brand-50 border border-brand-200 rounded-2xl flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-coins text-brand-600"></i>
                            <span class="font-bold text-brand-900">Điểm tích lũy nhận được:</span>
                        </div>
                        <span class="font-black text-brand-700">+{{ number_format($order->points_earned ?? 0) }}
                            điểm</span>
                    </div>
                @endif

                <!-- Tổng thanh toán -->
                <div class="pt-3 border-t border-gray-100 flex items-baseline justify-between">
                    <div>
                        <span class="text-xs text-gray-500 font-medium block">Tổng thanh toán:</span>
                        @if ($isVipPayment)
                            <span class="text-[11px] text-purple-700 font-bold">Gói Hội Viên VIP</span>
                        @elseif ($isVipDelivery)
                            <span class="text-[11px] text-purple-700 font-bold">Giao Định Kỳ
                                VIP{{ $batchInfo }}</span>
                        @endif
                    </div>
                    <span
                        class="text-2xl font-black {{ $isVipPayment || $isVipDelivery ? 'text-purple-700' : 'text-brand-700' }}">
                        {{ number_format($order->total, 0, ',', '.') }}đ
                    </span>
                </div>

                <!-- KHỐI BIÊN LAI THANH TOÁN HOẶC THÔNG TIN GÓI VIP -->
                @if ($isVipDelivery)
                    <div class="p-4 bg-purple-50/70 border border-purple-200/80 rounded-2xl space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-purple-900 font-semibold">Hình thức giao hàng:</span>
                            <span class="font-bold text-purple-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-crown text-purple-600"></i> Gói Định Kỳ VIP Trả Trước
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-purple-900 font-semibold">Thanh toán đợt này:</span>
                            <span
                                class="font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md inline-flex items-center gap-1 font-mono">
                                <i class="fa-solid fa-circle-check text-[10px]"></i> Đã thanh toán trọn gói (0đ)
                            </span>
                        </div>
                    </div>
                @elseif ($order->payment)
                    <div class="p-4 bg-gray-50 border border-gray-200/80 rounded-2xl space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Cổng thanh toán:</span>
                            <span class="font-bold text-gray-800 flex items-center gap-1.5 uppercase">
                                <i class="fa-solid fa-qrcode text-brand-600"></i>
                                {{ strtoupper($order->payment->method ?? 'PAYOS') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Trạng thái GD:</span>
                            @if ($order->payment->status === 'success')
                                <span
                                    class="font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Đã thanh toán (PAID)
                                </span>
                            @elseif ($order->payment->status === 'failed')
                                <span
                                    class="font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                    <i class="fa-solid fa-circle-xmark text-[10px]"></i> Thất bại / Đã hủy
                                </span>
                            @else
                                <span
                                    class="font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                    <i class="fa-solid fa-clock text-[10px]"></i> Đang chờ quét mã
                                </span>
                            @endif
                        </div>

                        <!-- Mã đối chiếu ngân hàng kèm nút Copy (chỉ hiện khi có transaction_code) -->
                        @if (!empty($order->payment->transaction_code))
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Mã đối chiếu GD:</span>
                                <div
                                    class="flex items-center gap-1.5 bg-white border border-gray-200 px-2 py-0.5 rounded-lg shadow-2xs">
                                    <span
                                        class="font-mono text-gray-800 font-bold text-xs select-all">{{ $order->payment->transaction_code }}</span>
                                    <button type="button" x-data
                                        @click="navigator.clipboard.writeText('{{ $order->payment->transaction_code }}'); $dispatch('show-toast', {type: 'success', message: 'Đã sao chép mã GD: {{ $order->payment->transaction_code }}'})"
                                        class="text-gray-400 hover:text-brand-600 transition cursor-pointer p-0.5"
                                        title="Sao chép mã đối chiếu ngân hàng">
                                        <i class="fa-regular fa-copy text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if ($order->payment->paid_at)
                            <div
                                class="flex items-center justify-between text-[11px] text-gray-400 pt-1.5 border-t border-gray-200/60">
                                <span>Thời gian ghi nhận:</span>
                                <span class="font-mono">
                                    {{ $order->payment->paid_at->format('H:i • d/m/Y') }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Nhóm nút hành động tinh gọn -->
                <div class="pt-1 space-y-2.5">
                    <!-- 1. Mua lại đơn này: Dành cho đơn bán lẻ đã giao hoặc đã hủy -->
                    @if (in_array($order->status, ['delivered', 'cancelled']) &&
                            !in_array($order->order_type, ['subscription', 'subscription_delivery']))
                        <button type="button" wire:click="reorder({{ $order->id }})"
                            class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-rotate-right text-xs"></i>
                            <span>Mua lại đơn này</span>
                        </button>
                    @endif

                    <!-- 2. Thanh toán ngay: Dành cho đơn pending chưa trả tiền -->
                    @if ($order->status === 'pending' && $order->payment?->status === 'pending')
                        <button type="button" wire:click="payNow({{ $order->id }})" wire:loading.attr="disabled"
                            class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-qrcode text-xs" wire:loading.remove></i>
                            <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading></i>
                            <span>Thanh toán ngay</span>
                        </button>

                        <button type="button" wire:click="confirmCancel({{ $order->id }})"
                            wire:confirm="Bạn có chắc chắn muốn hủy đơn hàng #{{ $order->order_code }} không?"
                            class="w-full bg-white hover:bg-red-50 text-gray-600 hover:text-red-600 border border-gray-200 font-bold py-2.5 rounded-2xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-regular fa-trash-can text-xs"></i>
                            <span>Hủy đơn hàng này</span>
                        </button>
                    @endif

                    <!-- 3. Yêu cầu xử lý / Khiếu nại hoàn tiền dập nát (24H) -->
                    @if ($order->status === 'delivered' && $order->order_type === 'retail' && !$order->complaint)
                        <button type="button" wire:click="openComplaintModal({{ $order->id }})"
                            class="w-full bg-white hover:bg-amber-50 text-amber-700 hover:text-amber-800 border border-amber-200 hover:border-amber-300 font-semibold py-2.5 rounded-2xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-shield-cat text-xs text-amber-600"></i>
                            <span>Khiếu nại / Yêu cầu bồi hoàn (24H)</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Cam kết bảo hiểm nông sản 24H -->
            <div
                class="bg-brand-50/60 border border-brand-100 rounded-3xl p-5 flex items-center gap-3.5 text-xs text-brand-900 shadow-2xs">
                <i class="fa-solid fa-shield-halved text-brand-600 text-2xl shrink-0"></i>
                <p class="leading-relaxed">
                    <b>Bảo hiểm tươi ngon 24H:</b> Cam kết bồi thường 100% tiền hoặc giao bù nếu nông sản phát hiện dập
                    úng khi nhận hàng.
                </p>
            </div>

        </div>
    </div>

    <!-- MODAL KHIẾU NẠI HOÀN TIỀN DẬP NÁT (24H) -->
    <x-customer.complaint-modal :show="$showComplaintModal" :refund-amount="$complaint_refund_amount" :images="$complaint_images" :banks="$showComplaintModal ? $this->banks() : []" />
</div>
