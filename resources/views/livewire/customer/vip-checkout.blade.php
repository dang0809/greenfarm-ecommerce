<div>
    <main class="pt-28 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 w-full space-y-6">
        
        <div class="pb-2 border-b border-gray-200/80">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Thanh Toán Gói Rau VIP</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-1">Xác nhận thanh toán gói dịch vụ giao rau tươi định kỳ 1 tháng</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- CỘT TRÁI (8 CỘT): BANNER VÀ CHỌN THANH TOÁN -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- BANNER MÔ TẢ GÓI RAU -->
                <div class="bg-gradient-to-br from-brand-900 via-brand-800 to-emerald-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl space-y-4">
                    <span class="bg-amber-400/20 border border-amber-400/40 text-amber-300 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider inline-block">
                        <i class="fa-solid fa-crown text-amber-400 mr-1"></i> Dịch Vụ Gói Rau VIP Đăng Ký Định Kỳ
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black">
                        {{ $packageName }}
                    </h2>
                    <p class="text-xs sm:text-sm text-brand-100/90 leading-relaxed font-medium">
                        Cung cấp rau củ quả tươi hái tận luống giao định kỳ <b>2 lần / tuần</b> (Tổng cộng <b>8 đợt giao / tháng</b>). Mỗi đợt nhận định lượng <b>3.5 kg</b> tươi giòn. Miễn phí vận chuyển 100%.
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs">
                        <div class="bg-white/10 p-3 rounded-2xl border border-white/10 flex items-center gap-2">
                            <i class="fa-solid fa-truck-fast text-amber-300"></i>
                            <span class="font-bold">Free Ship 8 đợt</span>
                        </div>
                        <div class="bg-white/10 p-3 rounded-2xl border border-white/10 flex items-center gap-2">
                            <i class="fa-solid fa-weight-scale text-emerald-300"></i>
                            <span class="font-bold">Tổng 28 kg / tháng</span>
                        </div>
                        <div class="bg-white/10 p-3 rounded-2xl border border-white/10 flex items-center gap-2">
                            <i class="fa-solid fa-arrows-rotate text-cyan-300"></i>
                            <span class="font-bold">Tự chọn rau từng đợt</span>
                        </div>
                        <div class="bg-white/10 p-3 rounded-2xl border border-white/10 flex items-center gap-2">
                            <i class="fa-solid fa-shield-check text-rose-300"></i>
                            <span class="font-bold">Bồi thường 1-đổi-1</span>
                        </div>
                    </div>
                </div>

                <!-- KHỐI CHỌN PHƯƠNG THỨC THANH TOÁN -->
                <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                    <div class="pb-3 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-credit-card text-brand-600"></i> Phương Thức Thanh Toán Trọn Gói
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">Thanh toán một lần duy nhất cho toàn bộ 8 đợt giao trong tháng</p>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center justify-between p-4 rounded-2xl border-2 transition cursor-pointer"
                               :class="$wire.paymentMethod === 'vnpay' ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <div class="flex items-center gap-3">
                                <input type="radio" wire:model.live="paymentMethod" value="vnpay" class="text-brand-600 focus:ring-brand-500">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm shrink-0 font-black">
                                        VNPAY
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-gray-900 block leading-tight">Thanh toán qua VNPAY / VietQR Bank App</span>
                                        <span class="text-[11px] text-gray-500">Quét mã QR bằng ứng dụng ngân hàng hoặc thẻ ATM</span>
                                    </div>
                                </div>
                            </div>
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded">Khuyên dùng</span>
                        </label>

                        <label class="flex items-center justify-between p-4 rounded-2xl border transition cursor-pointer"
                               :class="$wire.paymentMethod === 'momo' ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <div class="flex items-center gap-3">
                                <input type="radio" wire:model.live="paymentMethod" value="momo" class="text-brand-600 focus:ring-brand-500">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center text-sm shrink-0 font-bold">
                                        MoMo
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-gray-900 block leading-tight">Ví Điện Tử MoMo</span>
                                        <span class="text-[11px] text-gray-500">Thanh toán an toàn qua ứng dụng MoMo</span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- CỘT PHẢI (4 CỘT): TÓM TẮT ĐƠN HÀNG STICKY -->
            <div class="lg:col-span-4 lg:sticky lg:top-28 space-y-6">
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                    <div class="pb-3 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900 text-base">Tóm Tắt Đăng Ký</h3>
                    </div>

                    <div class="space-y-3 text-xs sm:text-sm text-gray-600">
                        <div class="flex justify-between">
                            <span>Dịch vụ:</span>
                            <span class="font-bold text-gray-900">{{ $packageName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Số đợt giao:</span>
                            <span class="font-bold text-gray-900">{{ $totalDeliveries }} Đợt (2 đợt / tuần)</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Định lượng:</span>
                            <span class="font-bold text-gray-900">3.5 kg / đợt (28 kg/tháng)</span>
                        </div>
                        <div class="flex justify-between text-brand-700">
                            <span>Phí vận chuyển 8 đợt:</span>
                            <span class="font-bold">0đ (Miễn phí 100%)</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-baseline justify-between">
                        <div>
                            <span class="block text-xs text-gray-400">Tổng thanh toán:</span>
                            <span class="text-[11px] text-gray-400">(Trọn gói 1 tháng)</span>
                        </div>
                        <span class="text-2xl font-black text-brand-700 font-mono">{{ number_format($price, 0, ',', '.') }}đ</span>
                    </div>

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
                            <span>Đang xử lý...</span>
                        </span>
                    </button>

                    <p class="text-[11px] text-gray-400 text-center leading-relaxed">
                        Sau khi thanh toán thành công, hệ thống sẽ chuyển bạn sang trang chọn Địa chỉ & Lịch giao rau cho 8 đợt.
                    </p>
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPT XỬ LÝ SWEETALERT2 VỚI LIVEWIRE EVENT -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('start-payment-process', (eventData) => {
                const data = eventData[0];
                Swal.fire({
                    title: 'Đang Kết Nối Cổng Thanh Toán...',
                    html: `Vui lòng hoàn tất thanh toán <b>${data.amount}</b> qua ${data.method}.`,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                }).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thanh Toán Thành Công!',
                        text: 'Gói VIP của bạn đã kích hoạt. Bây giờ hãy chọn Địa chỉ và Lịch giao rau cho từng ngày nhé!',
                        confirmButtonColor: '#15803d',
                        confirmButtonText: 'Đến Trang Chọn Địa Chỉ & Lịch Giao'
                    });
                });
            });
        });
    </script>
</div>