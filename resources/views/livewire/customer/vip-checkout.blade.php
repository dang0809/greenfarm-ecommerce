<div>
    <main class="pt-28 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 w-full space-y-6">
        
        <!-- TIÊU ĐỀ TRANG -->
        <div class="pb-3 border-b border-gray-200/80">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                <span>Thanh Toán Gói Rau VIP</span>
                <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full font-bold">
                    <i class="fa-solid fa-crown text-amber-600 text-[11px]"></i> Premium
                </span>
            </h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-1">
                Đăng ký dịch vụ giao rau tươi chuẩn hữu cơ định kỳ tận nhà hàng tuần
            </p>
        </div>

        <!-- THÔNG BÁO NẾU CÓ HỢP ĐỒNG CHỜ THIẾT LẬP LỊCH -->
        @if ($pendingSubscriptionId)
            <div class="bg-blue-50 border-2 border-blue-200 rounded-3xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-calendar-check text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-blue-900">Bạn đã thanh toán Gói Rau VIP!</h4>
                        <p class="text-xs text-blue-700 mt-0.5">Hợp đồng của bạn đã kích hoạt nhưng chưa chọn 2 ngày nhận rau và thực đơn trong tuần.</p>
                    </div>
                </div>
                <a href="{{ route('customer.vip.setup') }}" wire:navigate
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm shrink-0">
                    <span>Thiết Lập Lịch Ngay</span>
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </a>
            </div>
        @endif

        <!-- BANNER CẢNH BÁO NẾU ADMIN TẠM TẮT MỞ BÁN GÓI -->
        @if (!$isEnabled)
            <div class="bg-amber-50 border-2 border-amber-300 rounded-3xl p-5 sm:p-6 text-amber-900 flex items-start gap-4 shadow-sm">
                <div class="w-10 h-10 rounded-2xl bg-amber-200 text-amber-800 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="font-bold text-sm sm:text-base text-amber-950">Thông Báo: Tạm Ngưng Nhận Đăng Ký Mới</h3>
                    <p class="text-xs sm:text-sm text-amber-800 leading-relaxed">
                        Dịch vụ <b>{{ $packageName }}</b> hiện đang tạm ngừng nhận đăng ký mới để bảo đảm sản lượng thu hoạch và độ tươi ngon tốt nhất cho các hợp đồng hiện hành. Quý khách vui lòng quay lại sau khi hệ thống mở đăng ký đợt mới.
                    </p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- CỘT TRÁI (8 CỘT): BANNER VÀ CHỌN THANH TOÁN -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- BANNER MÔ TẢ GÓI RAU -->
                <div class="bg-gradient-to-br from-brand-900 via-brand-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl space-y-5 relative overflow-hidden">
                    <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="bg-amber-400/20 border border-amber-400/40 text-amber-300 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-crown text-amber-400"></i> Dịch Vụ Đăng Ký Định Kỳ
                        </span>
                        <span class="text-xs text-brand-200/80 font-mono">
                            <i class="fa-solid fa-shield-halved mr-1 text-emerald-400"></i> Chuẩn Hữu Cơ 100%
                        </span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-snug">
                        {{ $packageName }}
                    </h2>

                    <p class="text-xs sm:text-sm text-brand-100/90 leading-relaxed font-medium">
                        Cung cấp rau củ quả tươi hái tận luống giao định kỳ <b>2 lần / tuần</b> (Tổng cộng <b>{{ $totalDeliveries }} đợt giao / tháng</b>). 
                        Mỗi đợt nhận định lượng <b>{{ $deliveryMaxWeight }} kg</b> tươi giòn. Miễn phí vận chuyển 100%.
                    </p>

                    <!-- 4 THẺ QUYỀN LỢI ĐỘNG -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs">
                        <div class="bg-white/10 hover:bg-white/15 p-3.5 rounded-2xl border border-white/10 flex items-center gap-2.5 transition">
                            <i class="fa-solid fa-truck-fast text-amber-300 text-sm"></i>
                            <span class="font-bold">Free Ship {{ $totalDeliveries }} đợt</span>
                        </div>
                        <div class="bg-white/10 hover:bg-white/15 p-3.5 rounded-2xl border border-white/10 flex items-center gap-2.5 transition">
                            <i class="fa-solid fa-weight-scale text-emerald-300 text-sm"></i>
                            <span class="font-bold">Tổng {{ $totalWeightMonthly }} kg / tháng</span>
                        </div>
                        <div class="bg-white/10 hover:bg-white/15 p-3.5 rounded-2xl border border-white/10 flex items-center gap-2.5 transition">
                            <i class="fa-solid fa-arrows-rotate text-cyan-300 text-sm"></i>
                            <span class="font-bold">Tự chọn rau từng đợt</span>
                        </div>
                        <div class="bg-white/10 hover:bg-white/15 p-3.5 rounded-2xl border border-white/10 flex items-center gap-2.5 transition">
                            <i class="fa-solid fa-shield-check text-rose-300 text-sm"></i>
                            <span class="font-bold">Bồi thường 1-đổi-1</span>
                        </div>
                    </div>
                </div>

                <!-- KHỐI PHƯƠNG THỨC THANH TOÁN (GIỐNG NGUYÊN BẢN TRANG CHECKOUT BÁN LẺ) -->
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

            <!-- CỘT PHẢI (4 CỘT): TÓM TẮT ĐƠN HÀNG STICKY -->
            <div class="lg:col-span-4 lg:sticky lg:top-28 space-y-6">
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                    <div class="pb-3 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900 text-base">Tóm Tắt Đăng Ký</h3>
                    </div>

                    <div class="space-y-3.5 text-xs sm:text-sm text-gray-600">
                        <div class="flex justify-between items-start gap-2">
                            <span class="text-gray-500 shrink-0">Dịch vụ:</span>
                            <span class="font-bold text-gray-900 text-right">{{ $packageName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Số đợt giao:</span>
                            <span class="font-bold text-gray-900">{{ $totalDeliveries }} Đợt (2 đợt / tuần)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Định lượng rau:</span>
                            <span class="font-bold text-gray-900">{{ $deliveryMaxWeight }} kg / đợt ({{ $totalWeightMonthly }} kg/tháng)</span>
                        </div>
                        <div class="flex justify-between text-brand-700">
                            <span>Phí vận chuyển {{ $totalDeliveries }} đợt:</span>
                            <span class="font-bold">0đ (Miễn phí 100%)</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Hình thức:</span>
                            <span class="font-medium text-gray-900">PayOS (VietQR)</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-baseline justify-between">
                        <div>
                            <span class="block text-xs text-gray-400">Tổng thanh toán:</span>
                            <span class="text-[11px] text-gray-400 font-medium">(Trọn gói 1 tháng)</span>
                        </div>
                        <span class="text-2xl font-black text-brand-700 font-mono">
                            {{ number_format($price, 0, ',', '.') }}đ
                        </span>
                    </div>

                    <!-- NÚT BẤM THANH TOÁN -->
                    @if ($isEnabled)
                        <button type="button" 
                                wire:click="processPayment"
                                wire:loading.attr="disabled"
                                class="w-full bg-brand-600 hover:bg-brand-700 text-white py-4 rounded-2xl font-bold text-sm sm:text-base flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-98 cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove wire:target="processPayment" class="flex items-center gap-2">
                                <i class="fa-solid fa-lock text-xs"></i>
                                <span>Thanh Toán & Cấu Hình Lịch</span>
                            </span>
                            <span wire:loading wire:target="processPayment" class="flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                                <span>Đang kết nối PayOS...</span>
                            </span>
                        </button>
                    @else
                        <button type="button" disabled
                                class="w-full bg-gray-300 text-gray-500 py-4 rounded-2xl font-bold text-sm sm:text-base flex items-center justify-center gap-2 cursor-not-allowed">
                            <i class="fa-solid fa-ban text-xs"></i>
                            <span>Tạm Ngưng Nhận Đăng Ký Mới</span>
                        </button>
                    @endif

                    <p class="text-[11px] text-gray-400 text-center leading-relaxed">
                        Sau khi thanh toán thành công, hệ thống sẽ chuyển bạn sang trang chọn Địa chỉ & Lịch giao rau cho {{ $totalDeliveries }} đợt.
                    </p>
                </div>
            </div>

        </div>
    </main>

    
</div>