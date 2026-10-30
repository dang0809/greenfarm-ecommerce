<div>
    <!-- MAIN CONTENT -->
    <main class="pt-8 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="pb-6 border-b border-gray-200/80 mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Thanh Toán Đơn Hàng</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-1">Xác nhận thông tin giao nhận nông sản tươi từ Nông trại Đất Lành</p>
        </div>

        <!-- FORM THANH TOÁN -->
        <form wire:submit.prevent="placeOrder">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- CỘT TRÁI (7 CỘT): ĐỊA CHỈ, SHIP HỎA TỐC, THANH TOÁN PAYOS -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- 1. KHỐI ĐỊA CHỈ NHẬN HÀNG -->
                    <x-customer.address-card 
                        :address="$address" 
                        :distance="$distance" 
                        :max-distance="$maxDistance" 
                        :is-exceeded="$isDistanceExceeded"
                        :is-unknown="$isDistanceUnknown">
                        <!-- Ghi chú cho nông trại -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Ghi chú cho nông trại (tùy chọn)</label>
                            <input type="text" 
                                   wire:model="orderNote" 
                                   placeholder="Ví dụ: Giao trước 11h trưa, gọi chuông cửa tầng 2..." 
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-brand-500 transition-all">
                        </div>
                    </x-customer.address-card>

                    <!-- 2. VẬN CHUYỂN -->
                    <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <i class="fa-solid fa-truck-fast text-brand-600"></i> Phương Thức Vận Chuyển
                                </h2>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    @if ($address)
                                        Khoảng cách thực tế: <b class="text-gray-700 font-mono">{{ $distance }} km</b>
                                    @else
                                        Chưa chọn địa chỉ nhận hàng
                                    @endif
                                </p>
                            </div>
                            <span class="text-[11px] font-bold {{ !$address ? 'text-gray-600 bg-gray-100 border-gray-200' : ($isDistanceExceeded ? 'text-red-700 bg-red-50 border-red-200' : 'text-brand-700 bg-brand-50 border-brand-200') }} px-2.5 py-1 rounded-full border">
                                {{ !$address ? 'Chưa chọn địa chỉ' : ($isDistanceExceeded ? 'Vượt bán kính' : 'Hỏa tốc') }}
                            </span>
                        </div>

                        @if (!$address)
                            <!-- CẢNH BÁO CHƯA CHỌN ĐỊA CHỈ -->
                            <div class="p-4 rounded-2xl border border-amber-200 bg-amber-50 text-amber-800 text-xs space-y-1">
                                <p class="font-bold flex items-center gap-1.5 text-sm">
                                    <i class="fa-solid fa-circle-info text-amber-600"></i>
                                    <span>Chưa có thông tin khoảng cách</span>
                                </p>
                                <p class="leading-relaxed">
                                    Vui lòng chọn hoặc thêm địa chỉ nhận hàng ở bước trên để hệ thống tự động tính khoảng cách và phí giao hỏa tốc.
                                </p>
                            </div>
                        @elseif ($isDistanceExceeded)
                            <!-- CẢNH BÁO VƯỢT BÁN KÍNH -->
                            <div class="p-4 rounded-2xl border border-red-200 bg-red-50 text-red-700 text-xs space-y-1">
                                <p class="font-bold flex items-center gap-1.5 text-sm">
                                    <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                                    <span>Rất tiếc, nông trại chưa thể giao đến địa chỉ này!</span>
                                </p>
                                <p class="leading-relaxed">
                                    Khoảng cách từ nông trại đến địa chỉ nhận hàng là <b>{{ $distance }} km</b>, vượt quá bán kính giao hàng hỏa tốc tối đa (tối đa <b>{{ $maxDistance }} km</b>). Vui lòng chọn địa chỉ nhận hàng khác gần hơn.
                                </p>
                            </div>
                        @else
                            <!-- THẺ GIAO HỎA TỐC BÌNH THƯỜNG -->
                            <div class="flex items-start justify-between p-4 rounded-2xl border-2 border-brand-500 bg-brand-50/40">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                        <i class="fa-solid fa-bolt-lightning text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-900">Giao Hỏa Tốc (2H Giữ Trọn Độ Tươi)</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                            Đơn hàng được đóng thùng xốp thoáng khí và điều phối shipper giao ngay, giữ trọn độ tươi giòn tự nhiên và chống dập nát.
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-3">
                                    <span class="text-sm font-extrabold text-brand-700 block">{{ number_format($shippingFee) }}đ</span>
                                    <span class="text-[10px] text-gray-400 font-medium">{{ $distance }} km</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- 3. HÌNH THỨC THANH TOÁN: PAYOS -->
                    <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                        <div class="pb-3 border-b border-gray-100">
                            <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                <i class="fa-solid fa-credit-card text-brand-600"></i> Phương Thức Thanh Toán
                            </h2>
                            <p class="text-xs text-gray-400 mt-0.5">Thanh toán trực tuyến an toàn qua cổng PayOS</p>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-4 rounded-2xl border-2 border-brand-500 bg-brand-50/30">
                                <div class="flex items-center gap-3">
                                    <input type="radio" checked disabled class="text-brand-600 focus:ring-brand-500">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm shrink-0">
                                            <i class="fa-solid fa-qrcode"></i>
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold text-gray-900 block leading-tight">Thanh toán trực tuyến qua PayOS</span>
                                            <span class="text-[11px] text-gray-500">Quét mã VietQR trên ứng dụng ngân hàng hoặc thẻ ATM / Visa</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold bg-brand-100 text-brand-800 px-2 py-0.5 rounded-full border border-brand-200">Mặc định</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- CỘT PHẢI (5 CỘT): TÓM TẮT ĐƠN HÀNG -->
                <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-28">
                    
                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="font-bold text-gray-900 text-base">Đơn Hàng Của Bạn</h3>
                            <a href="{{ route('customer.cart') }}" class="text-xs font-semibold text-brand-600 hover:underline">Sửa giỏ hàng</a>
                        </div>

                        <!-- Danh sách mặt hàng -->
                        <div class="space-y-3.5 max-h-64 overflow-y-auto pr-1 no-scrollbar">
                            @forelse ($items as $item)
                                @php
                                    $product = $item->product;
                                    $rawImage = $product->productImages?->firstWhere('is_primary', true)?->image 
                                        ?? $product->productImages?->first()?->image;

                                    $primaryImage = $rawImage 
                                        ? (str_starts_with($rawImage, 'http') ? $rawImage : asset('storage/' . $rawImage))
                                        : 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=300';

                                    $unitPrice = $product->sale_price ?? $product->price;
                                    $itemSubtotal = $unitPrice * $item->quantity;
                                @endphp

                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="{{ $primaryImage }}" 
                                             alt="{{ $product->name }}" 
                                             class="w-12 h-12 rounded-xl object-cover border border-gray-100 shrink-0">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gray-900 truncate">{{ $product->name }}</p>
                                            <p class="text-[11px] text-gray-400">
                                                {{ number_format($unitPrice) }}đ × {{ $item->quantity + 0 }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-800 shrink-0">
                                        {{ number_format($itemSubtotal) }}đ
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 text-center py-4">Không có sản phẩm nào trong giỏ hàng.</p>
                            @endforelse
                        </div>

                        <!-- Chi tiết thanh toán -->
                        <div class="pt-4 border-t border-gray-100 space-y-3 text-xs sm:text-sm text-gray-600">
                            <!-- Tạm tính tiền hàng -->
                            <div class="flex justify-between">
                                <span>Tạm tính tiền hàng:</span>
                                <span class="font-bold text-gray-900">{{ number_format($subtotal) }}đ</span>
                            </div>

                            <!-- DÙNG ĐIỂM THƯỞNG -->
                            <div class="p-3 bg-gray-50 border border-gray-200/80 rounded-2xl">
                                <label class="flex items-start justify-between gap-3 cursor-pointer">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" 
                                            wire:model.live="usePoints" 
                                            @disabled(!$canUsePoints) 
                                            class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500 cursor-pointer disabled:opacity-50">
                                        <div>
                                            <span class="block text-xs font-bold text-gray-800">
                                                @if($usePoints && $canUsePoints)
                                                    Dùng {{ number_format($pointsUsed) }} điểm (1p = {{ number_format($valuePerPoint) }}đ)
                                                @else
                                                    Dùng điểm thưởng tích lũy
                                                @endif
                                            </span>
                                            <span class="block text-[11px] text-gray-400">
                                                Ví bạn đang có: <b class="text-brand-700">{{ number_format($userPoints) }}</b> điểm
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-brand-700">
                                        -{{ number_format($discount, 0, ',', '.') }}đ
                                    </span>
                                </label>

                                @if($canUsePoints && $maxDiscountPercent > 0)
                                    <p class="text-[10px] text-gray-400 mt-1 italic">
                                        * Trừ tối đa {{ $maxDiscountPercent }}% giá trị tiền hàng.
                                    </p>
                                @elseif($userPoints == 0)
                                    <p class="text-[10px] text-amber-600 mt-1 italic">
                                        Tài khoản chưa có điểm tích lũy.
                                    </p>
                                @endif
                            </div>

                            <!-- Phí vận chuyển hỏa tốc -->
                            <div class="flex justify-between items-center">
                                <span>Phí giao hỏa tốc:</span>
                                @if ($address)
                                    <span class="font-bold text-gray-900">{{ number_format($shippingFee) }}đ <span class="text-xs font-normal text-gray-400">({{ $distance }} km)</span></span>
                                @else
                                    <span class="font-semibold text-amber-600">Chưa chọn địa chỉ</span>
                                @endif
                            </div>
                        </div>

                        <!-- KHỐI ĐIỂM THƯỞNG TÍCH LŨY -->
                        <div class="p-3.5 bg-brand-50 border border-brand-200 rounded-2xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs shrink-0 shadow-xs">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-brand-900 leading-tight">Điểm tích lũy khi mua:</span>
                                    <span class="block text-[11px] text-brand-700">Tỷ lệ: 100k nhận {{ number_format($pointsPer100k) }} điểm</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-sm font-extrabold text-brand-700 block">+{{ number_format($earnedPoints) }} điểm</span>
                                <span class="text-[10px] text-gray-500 font-medium">(= {{ number_format($earnedPointsValue) }}đ)</span>
                            </div>
                        </div>

                        <!-- Tổng thanh toán cuối cùng -->
                        <div class="pt-4 border-t border-gray-100 flex items-baseline justify-between">
                            <div>
                                <span class="block text-xs text-gray-400">Tổng thanh toán:</span>
                                <span class="text-[11px] text-gray-400">(Đã gồm phí ship hỏa tốc)</span>
                            </div>
                            <span class="text-2xl font-black text-brand-700">{{ number_format($totalAmount) }}đ</span>
                        </div>

                        <!-- Nút Xác Nhận Đặt Hàng PayOS Desktop -->
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                @disabled(!$address || $isDistanceExceeded || $isDistanceUnknown)
                                class="w-full bg-brand-600 hover:bg-brand-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed text-white py-3.5 rounded-2xl font-bold text-sm sm:text-base flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-98 cursor-pointer">
                            @if (!$address)
                                <i class="fa-solid fa-location-dot text-xs"></i>
                                <span>Vui lòng chọn địa chỉ nhận hàng</span>
                            @elseif ($isDistanceUnknown)
                                <i class="fa-solid fa-triangle-exclamation text-xs text-amber-500"></i>
                                <span>Địa chỉ chưa xác định được vị trí</span>
                            @elseif ($isDistanceExceeded)
                                <i class="fa-solid fa-ban text-xs"></i>
                                <span>Vượt Bán Kính Giao Hàng (Tối đa {{ $maxDistance }}km)</span>
                            @else
                                <i class="fa-solid fa-lock text-xs" wire:loading.remove></i>
                                <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading></i>
                                <span wire:loading.remove>Xác nhận & Thanh toán PayOS</span>
                                <span wire:loading>Đang kết nối PayOS...</span>
                            @endif
                        </button>

                        <p class="text-[11px] text-gray-400 text-center leading-relaxed">
                            Bằng việc nhấn đặt hàng, bạn đồng ý với <a href="#" class="text-brand-600 hover:underline">Chính sách kiểm hàng & bồi thường 24H</a> của Đất Lành.
                        </p>
                    </div>

                    <!-- Cam kết nhanh -->
                    <div class="bg-brand-50/50 border border-brand-100 rounded-2xl p-4 flex items-center gap-3 text-xs text-brand-900">
                        <i class="fa-solid fa-shield-halved text-brand-600 text-lg shrink-0"></i>
                        <p>Kiểm tra hàng trước khi thanh toán. Đổi trả / bù hàng 24H nếu nông sản bị dập nát.</p>
                    </div>

                </div>
            </div>
        </form>
    </main>

    <!-- MODAL CHỌN ĐỊA CHỈ NHẬN HÀNG -->
    <x-customer.address-modal 
        :show="$showAddressModal" 
        :addresses="$addresses" 
        :selected-id="$selectedAddressId" 
        :max-distance="$maxDistance" />

    <!-- STICKY BAR ĐẶT HÀNG MOBILE (< lg) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-gray-200 p-4 shadow-xl">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div>
                <span class="block text-[10px] text-gray-400">Tổng thanh toán:</span>
                <span class="text-lg font-black text-brand-700 leading-none">{{ number_format($totalAmount) }}đ</span>
                <span class="block text-[10px] text-brand-600 font-semibold mt-0.5">+{{ number_format($earnedPoints) }} điểm tích lũy</span>
            </div>
            
            <button type="button" 
                    wire:click="placeOrder" 
                    wire:loading.attr="disabled"
                    @disabled(!$address || $isDistanceExceeded || $isDistanceUnknown)
                    class="bg-brand-600 hover:bg-brand-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed text-white px-6 py-3 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 shadow-md active:scale-95 transition-all cursor-pointer">
                @if (!$address)
                    <i class="fa-solid fa-location-dot text-[10px]"></i>
                    <span>Chưa chọn địa chỉ</span>
                @elseif ($isDistanceUnknown)
                    <i class="fa-solid fa-triangle-exclamation text-[10px] text-amber-400"></i>
                    <span>Chưa có vị trí</span>
                @elseif ($isDistanceExceeded)
                    <i class="fa-solid fa-ban text-[10px]"></i>
                    <span>Vượt bán kính {{ $maxDistance }}km</span>
                @else
                    <i class="fa-solid fa-lock text-[10px]" wire:loading.remove></i>
                    <i class="fa-solid fa-spinner fa-spin text-[10px]" wire:loading></i>
                    <span>Thanh toán PayOS</span>
                @endif
            </button>
        </div>
    </div>
</div>