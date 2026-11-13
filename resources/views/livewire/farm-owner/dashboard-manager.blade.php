<div class="flex flex-1 min-h-0 min-w-0 flex-col overflow-hidden">
    <!-- Header Topbar Responsive -->
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()"
                class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition shrink-0"
                aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <h1 class="text-base sm:text-2xl font-black text-slate-900 tracking-tight truncate leading-tight">
                    Tổng Quan Quản Trị
                </h1>
            </div>
        </div>

        <!-- Chỉ báo trạng thái hệ thống -->
        <div class="flex items-center gap-2 shrink-0">
            <span
                class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Hệ thống vận hành ổn định
            </span>
        </div>
    </header>

    <!-- Workspace Viewport -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12">

        <!-- ================= THANH TÁC VỤ & BỘ LỌC THỜI GIAN ================= -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft">
            <div class="flex items-center gap-2.5">
                <span class="text-xs font-bold text-slate-600 whitespace-nowrap">
                    <i class="fa-regular fa-calendar-days text-brand-700 mr-1"></i>Kỳ báo cáo:
                </span>
                <div class="relative">
                    <select id="time-filter"
                        class="bg-[#FAFAF8] border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3 py-2 pr-7 outline-none focus:border-brand-600 focus:bg-white transition cursor-pointer appearance-none">
                        <option value="today">Hôm nay</option>
                        <option value="7days">7 ngày qua</option>
                        <option value="this_month" selected>Tháng này (10/2026)</option>
                        <option value="this_quarter">Quý này (Q4/2026)</option>
                        <option value="this_year">Cả năm 2026</option>
                    </select>
                    <i
                        class="fa-solid fa-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" data-export-report
                    class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm px-4 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center gap-1.5 active:scale-95 whitespace-nowrap cursor-pointer">
                    <i class="fa-solid fa-file-arrow-down text-brand-300"></i>
                    <span>Xuất Báo Cáo Doanh Thu (Excel/PDF)</span>
                </button>
            </div>
        </div>

        <!-- ================= 3. HÀNG 3 THẺ THỐNG KÊ DOANH THU ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-5">

            <!-- Thẻ 1: Tổng doanh thu -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex flex-col justify-between hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Doanh
                        Thu</span>
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-emerald-50 text-brand-700 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </div>
                <div>
                    <span
                        class="text-xl sm:text-2xl xl:text-3xl font-black text-slate-900 tracking-tight font-mono block truncate"
                        id="stat-revenue">148.500.000đ</span>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2 text-xs font-bold text-emerald-700">
                        <span
                            class="inline-flex items-center gap-1 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full text-[10px] sm:text-[11px]">
                            <i class="fa-solid fa-arrow-trend-up"></i> +12.4%
                        </span>
                        <span class="text-slate-400 font-medium text-[10px] sm:text-[11px]" id="stat-revenue-compare">so
                            với tháng trước</span>
                    </div>
                </div>
            </div>

            <!-- Thẻ 2: Đơn hàng hoàn thành -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex flex-col justify-between hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Đơn Hoàn
                        Thành</span>
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xl sm:text-2xl xl:text-3xl font-black text-slate-900 tracking-tight font-mono"
                            id="stat-orders">620</span>
                        <span class="text-xs text-slate-400 font-bold">đơn</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2 text-xs font-bold text-blue-700">
                        <span
                            class="inline-flex items-center gap-1 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full text-[10px] sm:text-[11px]">
                            <i class="fa-solid fa-truck-fast"></i> 98.2%
                        </span>
                        <span class="text-slate-400 font-medium text-[10px] sm:text-[11px]">giao đúng giờ</span>
                    </div>
                </div>
            </div>

            <!-- Thẻ 3: Doanh thu gói VIP -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex flex-col justify-between hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-amber-700 uppercase tracking-wider">Doanh Thu Gói
                        VIP</span>
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                </div>
                <div>
                    <span
                        class="text-xl sm:text-2xl xl:text-3xl font-black text-amber-700 tracking-tight font-mono block truncate"
                        id="stat-sub-revenue">45.000.000đ</span>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2 text-xs font-bold text-amber-800">
                        <span
                            class="inline-flex items-center gap-1 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full text-[10px] sm:text-[11px]">
                            <i class="fa-solid fa-chart-pie"></i> 30.3%
                        </span>
                        <span class="text-slate-400 font-medium text-[10px] sm:text-[11px]">doanh thu định kỳ</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= 4. HÀNG THỐNG KÊ: KIỂM SOÁT KHIẾU NẠI & ĐỀN BÙ ================= -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-5">

            <!-- Thẻ K1: Tổng đơn khiếu nại phát sinh -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-rose-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-rose-600 uppercase tracking-wider block">Tổng Đơn
                        Khiếu Nại</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-rose-600 font-mono"
                            id="stat-complaint-total">18</span>
                        <span class="text-xs text-slate-400 font-bold">trường hợp</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">Tổng số case phát sinh từ
                        khách hàng</p>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>

            <!-- Thẻ K2: Tổng đơn bồi thường (Hoàn tiền / Voucher) -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-amber-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-amber-700 uppercase tracking-wider block">Tổng
                        Đơn Bồi Thường</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-slate-900 font-mono"
                            id="stat-refund-count">11</span>
                        <span class="text-xs text-slate-400 font-bold">đơn duyệt</span>
                        <span class="text-xs font-black text-rose-600 font-mono ml-1"
                            id="stat-refund-amount">(-1.250.000đ)</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">Kiểm soát dòng tiền chi trả
                        đền bù</p>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>

            <!-- Thẻ K3: Tổng đơn giao bù (Reship / Replacement) -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-blue-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-blue-700 uppercase tracking-wider block">Tổng Đơn
                        Giao Bù (Reship)</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-blue-700 font-mono"
                            id="stat-reship-count">07</span>
                        <span class="text-xs text-slate-400 font-bold">đơn 0đ</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">Kho/Farm trừ tồn và xuất gửi
                        lại hàng mới</p>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
            </div>

        </div>

        <!-- ================= 5. KHU VỰC BIỂU ĐỒ (2 CỘT SO SÁNH SONG SONG) ================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">

            <!-- Biểu đồ A: Xu hướng doanh thu -->
            <div
                class="lg:col-span-8 min-w-0 bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="min-w-0">
                        <h3
                            class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2 truncate">
                            <i class="fa-solid fa-chart-column text-brand-700"></i>
                            <span>So Sánh Doanh Thu (Đơn Mua Lẻ vs Gói Định Kỳ VIP)</span>
                        </h3>
                        <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5 truncate">So sánh giá trị đóng góp theo
                            từng mốc thời gian (Đơn vị: Triệu VNĐ)</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-semibold shrink-0">
                        <span class="inline-flex items-center gap-1.5 text-brand-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span> Cột Đơn lẻ
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-amber-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Cột Gói VIP
                        </span>
                    </div>
                </div>

                <div class="relative h-60 sm:h-72 lg:h-80 w-full min-w-0" wire:ignore>
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>

            <!-- Biểu đồ B: Cơ cấu danh mục -->
            <div
                class="lg:col-span-4 min-w-0 bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft space-y-4 flex flex-col justify-between">
                <div class="min-w-0">
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2 truncate">
                        <i class="fa-solid fa-chart-pie text-amber-600"></i>
                        <span>Cơ Cấu Danh Mục Nông Sản</span>
                    </h3>
                    <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5 truncate">Tỷ trọng doanh số theo nhóm sản
                        phẩm</p>
                </div>

                <div class="relative h-52 sm:h-60 w-full min-w-0 flex items-center justify-center" wire:ignore>
                    <canvas id="categoryShareChart"></canvas>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                    <div class="flex items-center gap-2 truncate">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></span>
                        <span class="text-slate-600 truncate">Rau lá: <b>40%</b></span>
                    </div>
                    <div class="flex items-center gap-2 truncate">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                        <span class="text-slate-600 truncate">Rau củ: <b>30%</b></span>
                    </div>
                    <div class="flex items-center gap-2 truncate">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span class="text-slate-600 truncate">Trái cây: <b>20%</b></span>
                    </div>
                    <div class="flex items-center gap-2 truncate">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shrink-0"></span>
                        <span class="text-slate-600 truncate">Nấm: <b>10%</b></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= 6. BẢNG TOP SẢN PHẨM DOANH THU CAO ================= -->
        <div
            class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden space-y-3">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2 truncate">
                        <i class="fa-solid fa-medal text-amber-500"></i>
                        <span>Top Nông Sản & Combo Gói VIP Doanh Thu Cao Nhất</span>
                    </h3>
                    <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5 truncate">Thống kê sản lượng xuất bán thực
                        tế và mức độ hài lòng của khách</p>
                </div>
                <span
                    class="text-xs font-bold text-brand-700 bg-brand-50 border border-brand-200 px-3 py-1.5 rounded-xl shrink-0">
                    Chủ lực
                </span>
            </div>

            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-100">
                            <th class="py-3.5 px-4 pl-5">Tên Nông Sản / Gói Combo</th>
                            <th class="py-3.5 px-4">Nhóm Sản Phẩm</th>
                            <th class="py-3.5 px-4 text-center">Sản Lượng Bán</th>
                            <th class="py-3.5 px-4 text-right">Tổng Doanh Thu</th>
                            <th class="py-3.5 px-4 pr-5 text-center">Đánh Giá</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 pl-5 flex items-center gap-3">
                                <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?w=100"
                                    class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                                <b class="text-slate-900 text-xs sm:text-sm">Combo Bếp Xanh Đất Lành (5kg/tuần)</b>
                            </td>
                            <td class="py-3.5 px-4 text-amber-700 font-bold">Gói định kỳ VIP</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">85 hợp đồng</td>
                            <td class="py-3.5 px-4 text-right font-mono font-extrabold text-brand-800 text-sm">
                                29.750.000đ</td>
                            <td class="py-3.5 px-4 pr-5 text-center font-mono text-amber-500 font-bold">5.0 ★</td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 pl-5 flex items-center gap-3">
                                <img src="https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=100"
                                    class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                                <b class="text-slate-900 text-xs sm:text-sm">Cà Chua Cherry Đỏ Đà Lạt</b>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">Rau củ ăn trái</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">450 kg</td>
                            <td class="py-3.5 px-4 text-right font-mono font-extrabold text-brand-800 text-sm">
                                29.250.000đ</td>
                            <td class="py-3.5 px-4 pr-5 text-center font-mono text-amber-500 font-bold">4.9 ★</td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 pl-5 flex items-center gap-3">
                                <img src="https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?w=100"
                                    class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                                <b class="text-slate-900 text-xs sm:text-sm">Dưa Leo Baby Nhật Bản</b>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">Rau củ ăn trái</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">380 kg</td>
                            <td class="py-3.5 px-4 text-right font-mono font-extrabold text-brand-800 text-sm">
                                20.900.000đ</td>
                            <td class="py-3.5 px-4 pr-5 text-center font-mono text-amber-500 font-bold">4.8 ★</td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 pl-5 flex items-center gap-3">
                                <img src="https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=100"
                                    class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                                <b class="text-slate-900 text-xs sm:text-sm">Cải Bó Xôi Hữu Cơ Spinach</b>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">Rau ăn lá</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">410 kg</td>
                            <td class="py-3.5 px-4 text-right font-mono font-extrabold text-brand-800 text-sm">
                                18.860.000đ</td>
                            <td class="py-3.5 px-4 pr-5 text-center font-mono text-amber-500 font-bold">4.9 ★</td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 pl-5 flex items-center gap-3">
                                <img src="https://images.unsplash.com/photo-1543528176-61b239494933?w=100"
                                    class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                                <b class="text-slate-900 text-xs sm:text-sm">Dâu Tây Giống Nhật Mộc Châu</b>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">Trái cây hữu cơ</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">120 hộp</td>
                            <td class="py-3.5 px-4 text-right font-mono font-extrabold text-brand-800 text-sm">
                                17.400.000đ</td>
                            <td class="py-3.5 px-4 pr-5 text-center font-mono text-amber-500 font-bold">4.7 ★</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@script
    <script>
        const root = $wire.$el;

        let revenueChart = null;
        let categoryChart = null;

        const DATA_PERIODS = {
            today: {
                revenue: '4.850.000đ',
                compare: 'hôm qua (3.900.000đ)',
                orders: '24',
                subRevenue: '1.200.000đ',
                labels: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
                retail: [850, 1200, 600, 950, 800, 450],
                sub: [350, 200, 350, 100, 500, 150]
            },
            '7days': {
                revenue: '38.200.000đ',
                compare: '7 ngày trước (+8.5%)',
                orders: '165',
                subRevenue: '11.500.000đ',
                labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
                retail: [4200, 5100, 3900, 4800, 6200, 7500, 6500],
                sub: [1500, 2200, 1400, 1800, 2500, 1100, 1000]
            },
            this_month: {
                revenue: '148.500.000đ',
                compare: 'so với tháng trước (+12.4%)',
                orders: '620',
                subRevenue: '45.000.000đ',
                labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4', 'Tuần 5'],
                retail: [24500, 28200, 26000, 31000, 18800],
                sub: [9500, 11200, 10500, 12000, 1800]
            },
            this_quarter: {
                revenue: '412.000.000đ',
                compare: 'so với Quý 3 (+18.2%)',
                orders: '1.780',
                subRevenue: '128.000.000đ',
                labels: ['Tháng 8', 'Tháng 9', 'Tháng 10'],
                retail: [92000, 98000, 103500],
                sub: [38000, 41000, 45000]
            },
            this_year: {
                revenue: '1.650.000.000đ',
                compare: 'mục tiêu năm (+104%)',
                orders: '7.150',
                subRevenue: '495.000.000đ',
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10'],
                retail: [85000, 92000, 105000, 110000, 118000, 125000, 130000, 138000, 142000, 148500],
                sub: [28000, 31000, 35000, 39000, 42000, 44000, 46000, 48000, 49000, 45000]
            }
        };

        function initCharts() {
            // BIỂU ĐỒ DOANH THU: 2 CỘT SO SÁNH SONG SONG (GROUPED BAR CHART)
            const ctxRev = document.getElementById('revenueTrendChart').getContext('2d');
            revenueChart = new Chart(ctxRev, {
                type: 'bar',
                data: {
                    labels: DATA_PERIODS.this_month.labels,
                    datasets: [{
                            label: 'Đơn mua lẻ',
                            data: DATA_PERIODS.this_month.retail,
                            backgroundColor: 'rgba(22, 163, 74, 0.9)',
                            borderColor: '#15803d',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        {
                            label: 'Gói định kỳ VIP',
                            data: DATA_PERIODS.this_month.sub,
                            backgroundColor: 'rgba(245, 158, 11, 0.9)',
                            borderColor: '#d97706',
                            borderWidth: 1,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + (context.parsed.y * 1000)
                                        .toLocaleString('vi-VN') + ' đ';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000) + ' Tr';
                                }
                            }
                        }
                    }
                }
            });

            // BIỂU ĐỒ TRÒN DOUGHNUT: CƠ CẤU DANH MỤC
            const ctxCat = document.getElementById('categoryShareChart').getContext('2d');
            categoryChart = new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: ['Rau ăn lá', 'Rau củ quả', 'Trái cây', 'Nấm'],
                    datasets: [{
                        data: [40, 30, 20, 10],
                        backgroundColor: [
                            '#16a34a',
                            '#f59e0b',
                            '#f43f5e',
                            '#6366f1'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.parsed + '%';
                                }
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }

        function changeTimePeriod(periodKey) {
            const data = DATA_PERIODS[periodKey] || DATA_PERIODS.this_month;

            document.getElementById('stat-revenue').textContent = data.revenue;
            document.getElementById('stat-revenue-compare').textContent = data.compare;
            document.getElementById('stat-orders').textContent = data.orders;
            document.getElementById('stat-sub-revenue').textContent = data.subRevenue;

            if (revenueChart) {
                revenueChart.data.labels = data.labels;
                revenueChart.data.datasets[0].data = data.retail;
                revenueChart.data.datasets[1].data = data.sub;
                revenueChart.update();
            }

            Swal.fire({
                icon: 'success',
                title: 'Đã cập nhật số liệu báo cáo!',
                timer: 1000,
                showConfirmButton: false
            });
        }

        function exportReport() {
            Swal.fire({
                title: 'Xuất Báo Cáo Tài Chính',
                text: 'Chọn định dạng tệp muốn tải về:',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-file-excel mr-1"></i> Tải Excel',
                cancelButtonText: '<i class="fa-solid fa-file-pdf mr-1"></i> Tải PDF',
                showCloseButton: true,
                confirmButtonColor: '#15803d',
                cancelButtonColor: '#0f172a'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã xuất file Excel thành công!',
                        text: 'File Bao_Cao_Doanh_Thu_Farm.xlsx đã sẵn sàng.',
                        confirmButtonColor: '#15803d'
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã xuất file PDF thành công!',
                        text: 'File Bao_Cao_Tong_Quan_Admin.pdf đã sẵn sàng.',
                        confirmButtonColor: '#15803d'
                    });
                }
            });
        }

        root.querySelector('#time-filter')?.addEventListener('change', (event) => {
            changeTimePeriod(event.target.value);
        });

        root.querySelector('[data-export-report]')?.addEventListener('click', () => {
            exportReport();
        });

        initCharts();

        document.addEventListener('livewire:navigating', () => {
            revenueChart?.destroy();
            categoryChart?.destroy();
        }, {
            once: true
        });
    </script>
@endscript
