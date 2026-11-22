<div>
    <!-- MAIN CONTAINER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-16 space-y-8 flex-1 w-full">

        <!-- TIÊU ĐỀ TRANG -->
        <div class="border-b border-gray-200/80 pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-amber-500 text-lg">👑</span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        Lịch Trình & Tiến Độ Giao Rau VIP
                    </h1>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Theo dõi trạng thái từng đợt giao và danh sách thực đơn rau tươi hữu cơ của bạn.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-2 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Đã chốt lịch cố định ({{ $deliveredCount }}/8 đợt đã giao)</span>
                </div>
            </div>
        </div>

        <!-- BANNER THÔNG BÁO HOÀN THÀNH 8 ĐỢT GIAO RAU VIP -->
        @if ($deliveredCount >= 8)
            <div
                class="bg-gradient-to-r from-emerald-600 via-brand-700 to-emerald-800 text-white rounded-3xl p-5 sm:p-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-2 border-emerald-400/40">
                <div class="flex items-start gap-3.5">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-400/30 border border-amber-300/40 flex items-center justify-center text-amber-300 text-xl shrink-0 shadow-inner">
                        👑
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm sm:text-base text-white flex items-center gap-2">
                            <span>Chúc mừng! Bạn đã hoàn thành trọn vẹn 8 đợt giao rau VIP.</span>
                            <span class="text-xs bg-amber-400 text-gray-900 font-black px-2 py-0.5 rounded-full">8/8
                                Đợt</span>
                        </h4>
                        <p class="text-xs text-brand-100/90 mt-1 leading-relaxed">
                            Cảm ơn bạn đã đồng hành cùng Nông Trại {{ $farmName }}. Hãy đăng ký gói mới cho tháng
                            tiếp theo để tiếp tục nhận rau sạch hữu cơ tươi ngon định kỳ mỗi tuần!
                        </p>
                    </div>
                </div>
                <a href="{{ route('customer.vip.checkout') }}" wire:navigate
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-amber-400 hover:bg-amber-300 text-gray-900 text-xs sm:text-sm font-extrabold transition shadow-lg shrink-0 hover:scale-105 active:scale-95">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Đăng Ký Gói Mới</span>
                </a>
            </div>
        @endif

        <!-- THẺ TỔNG QUAN TIẾN ĐỘ HỢP ĐỒNG -->
        <div
            class="bg-gradient-to-br from-brand-900 via-brand-800 to-emerald-950 text-white p-6 sm:p-8 rounded-3xl shadow-xl space-y-6">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-5">
                <div>
                    <span
                        class="inline-flex items-center gap-1.5 text-xs font-black tracking-wider uppercase bg-amber-400/20 text-amber-300 border border-amber-300/30 px-3 py-1 rounded-full mb-2">
                        👑 Hội Viên VIP {{ $farmName }}
                    </span>
                    <h2 class="text-xl sm:text-2xl font-black">Lịch Trình Giao Định Kỳ 8 Đợt</h2>
                    <p class="text-xs text-brand-100/80 mt-1">Hợp đồng giao tận nhà đều đặn 2 đợt mỗi tuần. Rau
                        tươi thu hoạch sáng sớm và giao hỏa tốc.</p>
                </div>

                <div
                    class="text-left sm:text-right bg-white/10 px-5 py-3 rounded-2xl border border-white/15 backdrop-blur-xs">
                    <span class="text-[11px] text-brand-200 block">Tiến độ hoàn thành:</span>
                    <span class="text-xl sm:text-2xl font-black font-mono text-amber-300">{{ $deliveredCount }} /
                        8</span>
                    <span class="text-xs text-brand-200"> đợt</span>
                </div>
            </div>

            <!-- Thanh tiến trình (Progress Bar) -->
            <div class="space-y-2">
                <div class="flex justify-between text-xs text-brand-200 font-bold">
                    <span>Tiến độ nhận rau</span>
                    <span>{{ round(($deliveredCount / 8) * 100) }}%</span>
                </div>
                <div class="w-full bg-white/10 h-3 rounded-full overflow-hidden p-0.5">
                    <div class="bg-gradient-to-r from-amber-400 to-emerald-400 h-full rounded-full transition-all duration-500 shadow-sm"
                        style="width: {{ ($deliveredCount / 8) * 100 }}%"></div>
                </div>
            </div>

            <!-- THÔNG TIN GIAO HÀNG ĐÃ KHÓA -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex items-start gap-3">
                    <i class="fa-solid fa-location-dot text-amber-400 text-base mt-1"></i>
                    <div class="text-xs space-y-1">
                        <span class="text-brand-200 font-bold block">Địa chỉ nhận rau cố định:</span>
                        <p class="font-medium text-white">{{ $subscription->recipient_name }} - {{ $subscription->recipient_phone }}</p>
                        <p class="text-brand-100/70">{{ $subscription->recipient_address }}</p>
                    </div>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex items-start gap-3">
                    <i class="fa-solid fa-calendar-check text-amber-400 text-base mt-1"></i>
                    <div class="text-xs space-y-1">
                        <span class="text-brand-200 font-bold block">Ngày giao cố định hàng tuần:</span>
                        <p class="font-medium text-white">Thứ: {{ $subscription->delivery_days ?? '---' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-white/10 text-xs text-brand-200">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-amber-300"></i>
                    <span>Cần tạm hoãn đợt giao hoặc đổi địa chỉ khẩn cấp? Vui lòng liên hệ Hotline Nông Trại: <strong class="text-white">{{ $hotline }}</strong></span>
                </div>
            </div>
        </div>

        <!-- DANH SÁCH 8 ĐỢT GIAO RAU (GRID CARDS) -->
        <div class="space-y-4">
            <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-boxes-packing text-brand-600"></i>
                <span>Chi Tiết Thực Đơn 8 Đợt Nhận Rau</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach ($lockedSchedules as $idx => $sch)
                    @php
                        $status = $sch['status'];
                    @endphp
                    <div
                        class="bg-white rounded-3xl p-5 border transition-all duration-300 flex flex-col justify-between shadow-sm relative overflow-hidden {{ $status === 'delivered' ? 'border-emerald-200 bg-emerald-50/10' : ( $status === 'processing' ? 'border-amber-400 ring-2 ring-amber-300/40 shadow-md bg-amber-50/15' : 'border-gray-200/80 hover:border-brand-200') }}">

                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
                                <span class="font-black text-sm text-gray-900 flex items-center gap-1.5">
                                    <span
                                        class="w-6 h-6 rounded-full {{ $status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : ($status === 'processing' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700') }} flex items-center justify-center text-xs font-mono">
                                        {{ $sch['order'] }}
                                    </span>
                                    <span>Đợt {{ $sch['order'] }}</span>
                                </span>

                                @if ($status === 'delivered')
                                    <span
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                        <i class="fa-solid fa-check text-[10px]"></i> Đã giao thành công
                                    </span>
                                @elseif($status === 'processing')
                                    <span
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-300 px-2.5 py-0.5 rounded-full animate-pulse">
                                        <i class="fa-solid fa-boxes-packing text-[10px]"></i> Đang chuẩn bị
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-gray-500 bg-gray-50 border border-gray-200 px-2.5 py-0.5 rounded-full">
                                        <i class="fa-solid fa-clock text-[10px]"></i> Chờ đến ngày giao
                                    </span>
                                @endif
                            </div>

                            <div class="text-xs text-gray-500 flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar text-gray-400"></i>
                                <span class="font-semibold text-gray-800">{{ $sch['date'] }}</span>
                            </div>

                            <!-- Danh sách rau củ trong đợt này -->
                            <div class="space-y-1.5 pt-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wide block">
                                    Thực đơn ({{ $sch['total_weight'] }}kg):
                                </span>
                                <div class="space-y-1 max-h-48 overflow-y-auto pr-1">
                                    @forelse ($sch['items'] as $it)
                                        <div
                                            class="flex items-center justify-between text-xs py-1 px-2 rounded-xl bg-gray-50/70 border border-gray-100">
                                            <div class="flex items-center gap-2 truncate">
                                                <img src="{{ $it['image'] }}"
                                                    class="w-6 h-6 rounded-lg object-cover shrink-0 border border-gray-200">
                                                <span class="truncate font-medium text-gray-800">{{ $it['name'] }}</span>
                                            </div>
                                            <span class="font-bold text-brand-700 shrink-0 ml-2">
                                                x{{ $it['quantity'] }}
                                            </span>
                                        </div>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">Rau hữu cơ tổng hợp theo mùa</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 text-[11px] text-gray-400 text-center border-t border-gray-100 mt-4">
                            @if ($status === 'delivered')
                                <span class="text-emerald-700 font-bold">✓ Giao thành công</span>
                            @elseif($status === 'processing')
                                <span class="text-amber-700 font-bold">🌱 Đã tới ngày hẹn, đang chuẩn bị đóng gói</span>
                            @else
                                <span>Chờ đến ngày hẹn giao rau</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </main>
</div>
