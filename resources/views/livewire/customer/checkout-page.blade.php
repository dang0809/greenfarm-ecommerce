<div>
    <!-- MAIN CONTENT -->
    <main class="pt-8 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs -->
        <nav class="flex text-xs sm:text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-2">
                <li><a href="{{ route('home') }}" class="hover:text-brand-600">Trang chủ</a></li>
                <li><i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i></li>
                <li><a href="{{ route('customer.cart') }}" class="hover:text-brand-600">Giỏ hàng</a></li>
                <li><i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i></li>
                <li class="text-gray-900 font-semibold">Thanh toán</li>
            </ol>
        </nav>

        <div class="pb-6 border-b border-gray-200/80 mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Thanh Toán Đơn Hàng</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-1">Xác nhận thông tin giao nhận nông sản tươi từ Nông trại Đất Lành</p>
        </div>

        <!-- FORM THANH TOÁN -->
        <form wire:submit.prevent="placeOrder">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- CỘT TRÁI (7 CỘT): ĐỊA CHỈ, SHIP HỎA TỐC, THANH TOÁN PAYOS -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- 1. KHỐI ĐỊA CHỈ NHẬN HÀNG (CHỈ HIỆN 1 TRONG 2) -->
                    <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                <i class="fa-solid fa-location-dot text-brand-600"></i> Địa Chỉ Nhận Hàng
                            </h2>
                            <button type="button" 
                                    wire:click="$set('showAddressModal', true)"
                                    class="text-xs font-bold text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 border border-brand-200 px-3.5 py-1.5 rounded-xl transition-all flex items-center gap-1.5 shadow-xs cursor-pointer">
                                <i class="fa-solid fa-repeat text-[10px]"></i>
                                <span>Thay đổi</span>
                            </button>
                        </div>

                        <!-- LỰA CHỌN 1: Nếu ĐÃ chọn địa chỉ -->
                        @if ($address)
                            <div class="p-4 sm:p-5 rounded-2xl border border-gray-200 bg-gray-50/60">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <span class="text-sm sm:text-base font-bold text-gray-900">
                                        {{ $address->recipient_name ?? $address->receiver_name }}
                                    </span>
                                    <span class="text-xs sm:text-sm text-gray-600 font-mono">
                                        {{ $address->recipient_phone ?? $address->receiver_phone }}
                                    </span>
                                    @if ($address->is_default)
                                        <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2.5 py-0.5 rounded-full border border-brand-200">Mặc định</span>
                                    @endif
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 mt-2 leading-relaxed">
                                    {{ $address->address_detail }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}
                                </p>
                                <div class="mt-3 flex items-center gap-2 text-xs text-gray-400">
                                    <i class="fa-solid fa-route text-brand-600"></i>
                                    <span>Khoảng cách đến nông trại/hub: <b class="text-gray-700 font-mono">{{ $distance }} km</b></span>
                                </div>
                            </div>
                        <!-- LỰA CHỌN 2: Nếu CHƯA chọn/chưa có địa chỉ -->
                        @else
                            <div class="p-4 rounded-2xl border border-dashed border-red-300 bg-red-50 text-red-700 text-xs flex items-center justify-between">
                                <span>Chưa chọn địa chỉ nhận hàng. Vui lòng bấm Thay đổi để chọn hoặc thêm địa chỉ!</span>
                                <button type="button" wire:click="$set('showAddressModal', true)" class="font-bold underline cursor-pointer">Chọn ngay</button>
                            </div>
                        @endif

                        <!-- Ghi chú cho shipper -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Ghi chú cho shipper (tùy chọn)</label>
                            <input type="text" 
                                   wire:model.defer="orderNote" 
                                   placeholder="Ví dụ: Giao trước 11h trưa, gọi chuông cửa tầng 2..." 
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-brand-500 transition-all">
                        </div>
                    </div>

                    <!-- 2. VẬN CHUYỂN: CHỈ DUY NHẤT HỎA TỐC -->
                    <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <i class="fa-solid fa-truck-fast text-brand-600"></i> Phương Thức Vận Chuyển
                                </h2>
                                <p class="text-xs text-gray-400 mt-0.5">Khoảng cách thực tế: <b class="text-gray-700 font-mono">{{ $distance }} km</b></p>
                            </div>
                            <span class="text-[11px] font-bold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-full border border-brand-200">
                                Hỏa tốc
                            </span>
                        </div>

                        <!-- Thẻ Giao hỏa tốc cố định duy nhất -->
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
                            <!-- PayOS (Mặc định cố định) -->
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

                <!-- CỘT PHẢI (5 CỘT): TÓM TẮT ĐƠN HÀNG & TÍNH ĐIỂM CỘNG -->
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
                                                {{ number_format($unitPrice) }}đ × {{ $item->quantity }}
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
                        <div class="pt-4 border-t border-gray-100 space-y-2.5 text-xs sm:text-sm text-gray-600">
                            <div class="flex justify-between">
                                <span>Tạm tính tiền hàng:</span>
                                <span class="font-bold text-gray-900">{{ number_format($subtotal) }}đ</span>
                            </div>
                            @if ($discount > 0)
                                <div class="flex justify-between text-brand-700">
                                    <span>Khấu trừ điểm thưởng:</span>
                                    <span class="font-bold">-{{ number_format($discount) }}đ</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span>Phí giao hỏa tốc (<span>{{ $distance }} km</span>):</span>
                                <span class="font-bold text-gray-900">{{ number_format($shippingFee) }}đ</span>
                            </div>
                        </div>

                        <!-- KHỐI ĐIỂM THƯỞNG ĐƯỢC CỘNG (100k = 1.000 điểm) -->
                        <div class="p-3.5 bg-brand-50 border border-brand-200 rounded-2xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs shrink-0 shadow-xs">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-brand-900 leading-tight">Điểm tích lũy khi mua:</span>
                                    <span class="block text-[11px] text-brand-700">Tỷ lệ: 100k nhận 1.000 điểm</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-sm font-extrabold text-brand-700 block">+{{ number_format($earnedPoints) }} điểm</span>
                                <span class="text-[10px] text-gray-500 font-medium">(= {{ number_format($earnedPoints) }}đ)</span>
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
                                class="w-full bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white py-3.5 rounded-2xl font-bold text-sm sm:text-base flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-98 cursor-pointer">
                            <i class="fa-solid fa-lock text-xs" wire:loading.remove></i>
                            <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading></i>
                            <span wire:loading.remove>Xác nhận & Thanh toán PayOS</span>
                            <span wire:loading>Đang kết nối PayOS...</span>
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
    @if ($showAddressModal)
        <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-gray-100 space-y-5 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">Chọn Địa Chỉ Nhận Hàng</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Chọn một địa chỉ để giao nông sản hỏa tốc</p>
                    </div>
                    <button type="button" wire:click="$set('showAddressModal', false)" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Danh sách địa chỉ -->
                <div class="space-y-3">
                    @forelse ($addresses as $addr)
                        <div wire:click="selectAddress({{ $addr->id }})" 
                             class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-start gap-3 {{ $selectedAddressId == $addr->id ? 'border-brand-500 bg-brand-50/20' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                            <input type="radio" name="modal_address_radio" {{ $selectedAddressId == $addr->id ? 'checked' : '' }} class="mt-1 text-brand-600 focus:ring-brand-500">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-bold text-gray-900">{{ $addr->recipient_name ?? $addr->receiver_name }}</span>
                                    <span class="text-xs text-gray-500 font-mono">{{ $addr->recipient_phone ?? $addr->receiver_phone }}</span>
                                    @if ($addr->is_default)
                                        <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full border border-brand-200">Mặc định</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                    {{ $addr->address_detail }}, {{ $addr->ward }}, {{ $addr->district }}, {{ $addr->province }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 text-center py-4">Bạn chưa lưu địa chỉ nào trong sổ địa chỉ.</p>
                    @endforelse
                </div>

                <!-- Footer Modal -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-3">
                    <a href="{{ route('customer.profile') }}#addresses" target="_blank" class="text-xs font-semibold text-gray-500 hover:text-brand-600 flex items-center gap-1">
                        <i class="fa-solid fa-pen-to-square"></i> Quản lý trong hồ sơ
                    </a>
                    <button type="button" wire:click="$set('showAddressModal', false)" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition-all shadow-sm active:scale-95">
                        Xác nhận chọn
                    </button>
                </div>
            </div>
        </div>
    @endif

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
                    class="bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white px-6 py-3 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 shadow-md active:scale-95 transition-all cursor-pointer">
                <i class="fa-solid fa-lock text-[10px]" wire:loading.remove></i>
                <i class="fa-solid fa-spinner fa-spin text-[10px]" wire:loading></i>
                <span>Thanh toán PayOS</span>
            </button>
        </div>
    </div>
</div>