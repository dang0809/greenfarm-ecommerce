<div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">
    <!-- ================= 1. HEADER CHÍNH ================= -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5 truncate">
                    <span class="text-brand-700">Nông Trại Đất Lành</span>
                </div>
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">
                    Quản Lý Gói Rau Định Kỳ VIP
                </h1>
            </div>
        </div>

        <!-- Chỉ báo loại hợp đồng & Ngày hôm nay -->
        <div class="flex items-center gap-2 shrink-0">
            <div class="hidden sm:inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Gói Rau Định Kỳ VIP
            </div>
            <div class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-700"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </header>

    <!-- ================= 2. KHU VỰC CUỘN NỘI DUNG ================= -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12 h-[calc(100dvh-5rem)]">

        <!-- ================= 3. HÀNG 4 THẺ THỐNG KÊ GÓI VIP ================= -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5">
            
            <!-- Thẻ 1: Tổng Hợp Đồng -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-gray-500 block uppercase tracking-wider">Tổng Hợp Đồng</span>
                    <span class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight mt-1 block font-mono" id="stat-total">0</span>
                    <p class="text-[10px] sm:text-[11px] text-gray-400 font-medium mt-1">Toàn bộ gói VIP đã ký</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-file-contract"></i>
                </div>
            </div>

            <!-- Thẻ 2: Đang Hoạt Động -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-brand-700 block uppercase tracking-wider">Đang Hoạt Động</span>
                    <span class="text-2xl sm:text-3xl font-black text-brand-700 tracking-tight mt-1 block font-mono" id="stat-active">0</span>
                    <p class="text-[10px] sm:text-[11px] text-brand-800 font-medium mt-1">Khách đang nhận rau định kỳ</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-repeat"></i>
                </div>
            </div>

            <!-- Thẻ 3: Lịch Giao Hôm Nay -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-amber-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-amber-700 block uppercase tracking-wider">Lịch Giao Hôm Nay</span>
                    <span class="text-2xl sm:text-3xl font-black text-amber-700 tracking-tight mt-1 block font-mono" id="stat-today">0</span>
                    <p class="text-[10px] sm:text-[11px] text-amber-800 font-bold mt-1">Hệ thống quét tự động sinh đơn</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>

            <!-- Thẻ 4: Đã Hoàn Thành Trọn Vẹn -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 block uppercase tracking-wider">Đã Hoàn Thành</span>
                    <span class="text-2xl sm:text-3xl font-black text-slate-700 tracking-tight mt-1 block font-mono" id="stat-completed">0</span>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">Đã nhận đủ 100% các đợt</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
            </div>

        </div>

        <!-- ================= 4. BẢNG DANH SÁCH HỢP ĐỒNG GÓI VIP ================= -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden space-y-4">
            
            <!-- Hàng Tab Lọc Trạng Thái -->
            <div class="flex overflow-x-auto border-b border-gray-100 px-4 sm:px-6 pt-3 gap-6 text-sm no-scrollbar bg-gray-50/40">
                <button onclick="setFilterStatus('all')" id="tab-all" class="pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2">
                    <span>Tất cả</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full" id="count-all">0</span>
                </button>
                <button onclick="setFilterStatus('active')" id="tab-active" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2">
                    <span>Đang hoạt động</span>
                    <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full" id="count-tab-active">0</span>
                </button>
                <button onclick="setFilterStatus('today')" id="tab-today" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2">
                    <span>Lịch giao hôm nay</span>
                    <span class="text-xs font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full" id="count-tab-today">0</span>
                </button>
                <button onclick="setFilterStatus('completed')" id="tab-completed" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2">
                    <span>Đã hoàn thành</span>
                    <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full" id="count-tab-completed">0</span>
                </button>
            </div>

            <!-- Thanh Tìm Kiếm & Nút Đăng Ký Gói Mới -->
            <div class="p-3.5 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-gray-100">
                <div class="flex items-center gap-2.5 flex-1 max-w-md">
                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                        <input type="text" id="search-input" oninput="applyFilters()" placeholder="Tìm theo Mã hợp đồng (#SUB...), Tên khách, SĐT..." class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                    </div>
                    <button onclick="resetFilters()" class="p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 transition shadow-xs shrink-0 cursor-pointer" title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>

                <!-- Nút Hành Động (Chỉ có Tạo Gói Mới) -->
                <div class="flex items-center gap-2">
                    <button onclick="openCreateSubModal()" class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95 whitespace-nowrap cursor-pointer">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>+ Đăng Ký Gói Mới</span>
                    </button>
                </div>
            </div>

            <!-- Bảng Dữ Liệu Hợp Đồng -->
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-gray-100">
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Mã Hợp Đồng</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Khách Hàng & Nơi Nhận</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Loại Gói VIP</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Lịch Giao Cố Định</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5 min-w-[170px]">Tiến Độ Nhận Rau</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Ngày Giao Kế Tiếp</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5 text-center">Trạng Thái</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-6 text-right">Chi Tiết</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium" id="sub-table-body">
                        <!-- Render động qua JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Trạng Thái Trống (Empty State) -->
            <div id="empty-state" class="hidden py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>
                <p class="text-base font-bold text-gray-700">Không tìm thấy hợp đồng gói định kỳ nào</p>
                <p class="text-xs text-gray-400 mt-1">Vui lòng thay đổi từ khóa tìm kiếm hoặc bấm tab trạng thái khác.</p>
            </div>
        </div>

    </div>

    <!-- ================= 5. MODAL CHI TIẾT HỢP ĐỒNG & LỊCH SỬ GIAO ================= -->
    <div id="modal-sub-detail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-5xl shadow-2xl p-5 sm:p-8 max-h-[90vh] overflow-y-auto custom-scrollbar border border-gray-100 space-y-4 sm:space-y-6">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 sm:pb-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 truncate font-mono" id="mod-sub-code">#SUB-2026-001</h3>
                        <span id="mod-sub-status-badge" class="text-[10px] sm:text-xs font-bold px-2.5 sm:px-3 py-0.5 rounded-full border">Đang hoạt động</span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5 font-mono">Bắt đầu từ: <span id="mod-sub-created-at">01/09/2026</span></p>
                </div>
                <button type="button" onclick="closeModal('modal-sub-detail')" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base sm:text-lg"></i>
                </button>
            </div>

            <!-- Tóm Tắt Hợp Đồng & Khách Hàng -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-4">
                <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFAF8] border border-gray-200/80 space-y-1 text-xs">
                    <span class="text-gray-400 block font-semibold">Khách hàng nhận rau:</span>
                    <p class="font-bold text-gray-900 text-sm" id="mod-cust-name">Chị Mai Anh</p>
                    <p class="font-mono text-gray-600" id="mod-cust-phone">0908.123.456</p>
                    <p class="text-gray-600 leading-snug pt-1 truncate" id="mod-cust-address">Landmark 4, Vinhomes Central Park, Bình Thạnh</p>
                </div>

                <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFAF8] border border-gray-200/80 space-y-1 text-xs">
                    <span class="text-gray-400 block font-semibold">Gói rau đăng ký:</span>
                    <p class="font-bold text-brand-800 text-sm truncate" id="mod-pkg-name">Gói Rau Định Kỳ VIP</p>
                    <p class="text-gray-600">Định lượng: <b class="text-gray-900">3.5kg - 5kg / đợt</b></p>
                    <p class="text-gray-600 font-mono">Lịch giao: <b class="text-gray-900" id="mod-delivery-schedule">Thứ 3 & Thứ 6</b></p>
                </div>

                <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-brand-50/60 border border-brand-200/80 space-y-1 text-xs">
                    <span class="text-brand-700 block font-semibold">Thanh toán trọn gói:</span>
                    <p class="font-black text-brand-800 text-base sm:text-lg font-mono" id="mod-prepaid-amount">1.840.000đ</p>
                    <span class="inline-block text-[10px] font-bold bg-white text-brand-700 px-2 py-0.5 rounded border border-brand-200">
                        <i class="fa-solid fa-circle-check mr-1"></i>Đã thanh toán trước 100%
                    </span>
                    <p class="text-[11px] text-gray-500 pt-0.5 font-mono">Tiến độ: <b id="mod-progress-text">6 / 8 đợt</b></p>
                </div>
            </div>

            <!-- Yêu Cầu Dinh Dưỡng / Kiêng Kỵ của Khách -->
            <div class="bg-amber-50/70 p-3.5 sm:p-4 rounded-xl sm:rounded-2xl border border-amber-200/80 text-amber-950 text-xs flex items-start gap-2.5 sm:gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-200 text-amber-800 flex items-center justify-center shrink-0 text-sm">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold text-xs sm:text-sm">Yêu cầu dinh dưỡng & Kiêng kỵ của khách:</span>
                        <span class="text-[10px] font-bold text-amber-800 bg-white px-2 py-0.5 rounded border border-amber-300 shrink-0">Lưu ý kho</span>
                    </div>
                    <p class="mt-1 text-amber-900 leading-relaxed font-medium" id="mod-preferences">
                        "Nhà có trẻ nhỏ, ưu tiên rau mồng tơi và cải ngọt baby. Không ăn mướp đắng, nếu tuần nào có mướp đắng vui lòng đổi sang bầu hoặc bí xanh."
                    </p>
                </div>
            </div>

            <!-- Bảng Lịch Sử Chi Tiết Từng Đợt Giao Hàng & THỰC ĐƠN MÓN -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-gray-900 text-xs sm:text-sm flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-brand-700"></i> Lịch Sử Chi Tiết & Thực Đơn Từng Đợt Giao
                        </h4>
                        <p class="text-[11px] text-gray-400">Nhấp vào danh sách rau củ để xem chi tiết từng món và định lượng chuẩn bị cho từng đợt.</p>
                    </div>
                    <span class="hidden sm:inline-flex text-[11px] text-brand-700 font-bold bg-brand-50 border border-brand-200 px-2.5 py-1 rounded-lg items-center gap-1">
                        <i class="fa-solid fa-circle-info"></i> Nhấn vào thực đơn để xem chi tiết
                    </span>
                </div>

                <div class="overflow-x-auto rounded-xl sm:rounded-2xl border border-gray-200 bg-white custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-200">
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4">Đợt Giao</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4">Ngày Giao Hàng</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4 min-w-[280px]">Rau Củ Trong Đợt (Thực Đơn)</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4">Mã Đơn Liên Kết</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4">Tài Xế Giao</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4 text-center">Trạng Thái Đợt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium" id="mod-deliveries-tbody">
                            <!-- Render qua JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Các Nút Hành Động Trong Modal -->
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-3">
                <button type="button" onclick="changeDeliveryAddressModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-4 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-regular fa-map"></i>
                    <span>Đổi Địa Chỉ Nhận Hàng</span>
                </button>

                <button type="button" onclick="closeModal('modal-sub-detail')" class="px-5 py-2.5 rounded-xl sm:rounded-2xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 cursor-pointer">
                    Đóng
                </button>
            </div>

        </div>
    </div>

    <!-- ================= 6. MODAL XEM CHI TIẾT THỰC ĐƠN RAU CỦ TRONG TỪNG ĐỢT ================= -->
    <div id="modal-round-items" class="fixed inset-0 z-60 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-6 max-h-[85vh] overflow-y-auto custom-scrollbar border border-gray-100 space-y-4">
            
            <!-- Header Modal Thực Đơn -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-lg border border-brand-200 shrink-0">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-gray-900 leading-tight" id="mri-title">Thực Đơn Đợt Giao #1</h3>
                        <p class="text-xs text-gray-400 font-mono" id="mri-subtitle">Ngày giao: 15/10/2026 • Gói VIP</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('modal-round-items')" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Banner tóm tắt định lượng đợt giao -->
            <div class="p-3 bg-brand-50/70 border border-brand-200 rounded-xl flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                    <span class="font-bold text-brand-900" id="mri-total-types">4 Loại Nông Sản Hữu Cơ</span>
                </div>
                <div class="font-mono font-extrabold text-brand-800" id="mri-total-weight">
                    Tổng: 3.5 kg
                </div>
            </div>

            <!-- Danh sách từng loại rau củ -->
            <div class="space-y-2.5" id="mri-items-list">
                <!-- Render bằng JavaScript -->
            </div>

            <!-- Ghi chú lưu ý khi đóng gói của đợt này -->
            <div class="p-3 rounded-xl bg-amber-50/80 border border-amber-200 text-xs space-y-1">
                <span class="font-bold text-amber-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-clipboard-check text-amber-700"></i>
                    <span>Lưu ý đóng gói cho kho:</span>
                </span>
                <p class="text-amber-800 text-[11px] leading-relaxed" id="mri-note">
                    Rau thu hoạch tươi sáng sớm, đóng túi kraft thoáng khí.
                </p>
            </div>

            <!-- Nút đóng sub-modal -->
            <div class="pt-2 flex justify-end">
                <button type="button" onclick="closeModal('modal-round-items')" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 cursor-pointer">
                    Đóng
                </button>
            </div>

        </div>
    </div>

    <!-- ================= 7. MODAL ĐĂNG KÝ GÓI MỚI (CHỈ 1 GÓI DUY NHẤT) ================= -->
    <div id="modal-create-sub" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-2xl shadow-2xl p-5 sm:p-8 max-h-[90vh] overflow-y-auto custom-scrollbar border border-gray-100 space-y-4 sm:space-y-6">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 sm:pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-lg border border-brand-200 shrink-0">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 leading-tight">Đăng Ký Hợp Đồng Gói Rau Mới</h3>
                        <p class="text-xs text-gray-400">Sales mở hợp đồng nhận rau định kỳ theo yêu cầu của khách hàng</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('modal-create-sub')" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form onsubmit="handleCreateSubSubmit(event)" class="space-y-4 text-xs">
                
                <!-- 1. Thông tin khách hàng -->
                <div class="bg-[#FAFAF8] p-3.5 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 space-y-3">
                    <span class="font-extrabold text-gray-900 uppercase tracking-wider text-[11px] block">
                        1. Thông Tin Khách Hàng
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Họ tên khách hàng <span class="text-red-500">*</span></label>
                            <input type="text" id="cs-name" required placeholder="VD: Chị Minh Anh" class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2 text-xs sm:text-sm outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                            <input type="text" id="cs-phone" required placeholder="VD: 0918.223.344" class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2 text-xs sm:text-sm outline-none focus:border-brand-500 font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Địa chỉ giao cố định <span class="text-red-500">*</span></label>
                        <input type="text" id="cs-address" required placeholder="Số nhà, tên đường, phường, quận..." class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2 text-xs sm:text-sm outline-none focus:border-brand-500">
                    </div>
                </div>

                <!-- 2. Gói rau VIP (Cố định 1 gói duy nhất) & Lịch trình -->
                <div class="bg-[#FAFAF8] p-3.5 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 space-y-3">
                    <span class="font-extrabold text-gray-900 uppercase tracking-wider text-[11px] block">
                        2. Gói Nông Sản & Lịch Trình Giao Hàng
                    </span>

                    <!-- Hiển thị thông tin gói cố định -->
                    <div class="p-3 bg-brand-50/70 border border-brand-200 rounded-xl flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-brand-700 text-white flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-leaf text-base"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-brand-900 text-xs sm:text-sm">Gói Rau Định Kỳ VIP</h4>
                                <p class="text-[11px] text-brand-700">Định lượng 3.5kg - 5kg rau củ sạch chuẩn VietGAP / đợt</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-brand-800 text-sm sm:text-base font-mono">230.000đ</span>
                            <span class="text-[10px] text-gray-500 block">/ đợt giao</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tần suất nhận rau</label>
                            <select id="cs-frequency" onchange="calculateNewSubPrice()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold outline-none focus:border-brand-500 cursor-pointer">
                                <option value="1">1 lần / tuần (4 đợt / tháng)</option>
                                <option value="2" selected>2 lần / tuần (8 đợt / tháng)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Thời hạn hợp đồng</label>
                            <select id="cs-duration" onchange="calculateNewSubPrice()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold outline-none focus:border-brand-500 cursor-pointer">
                                <option value="4" selected>4 tuần (1 tháng)</option>
                                <option value="8">8 tuần (2 tháng - Giảm 5%)</option>
                                <option value="12">12 tuần (3 tháng - Giảm 10%)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Chọn các ngày giao trong tuần -->
                    <div>
                        <label class="block font-bold text-gray-700 mb-1.5">Các thứ nhận rau cố định trong tuần:</label>
                        <div class="flex flex-wrap gap-2 text-xs font-bold" id="cs-days-checkboxes">
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-brand-500">
                                <input type="checkbox" name="delivery_day" value="T2" class="text-brand-600 rounded"> T2
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-brand-500 bg-brand-50 cursor-pointer">
                                <input type="checkbox" name="delivery_day" value="T3" checked class="text-brand-600 rounded"> T3
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-brand-500">
                                <input type="checkbox" name="delivery_day" value="T4" class="text-brand-600 rounded"> T4
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-brand-500">
                                <input type="checkbox" name="delivery_day" value="T5" class="text-brand-600 rounded"> T5
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-brand-500 bg-brand-50 cursor-pointer">
                                <input type="checkbox" name="delivery_day" value="T6" checked class="text-brand-600 rounded"> T6
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-brand-500">
                                <input type="checkbox" name="delivery_day" value="T7" class="text-brand-600 rounded"> T7
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-brand-500">
                                <input type="checkbox" name="delivery_day" value="CN" class="text-brand-600 rounded"> CN
                            </label>
                        </div>
                    </div>

                    <!-- Ghi chú kiêng cữ -->
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Ghi chú dinh dưỡng / Kiêng kỵ của khách</label>
                        <textarea id="cs-preferences" rows="2" placeholder="VD: Nhà có trẻ em không ăn cay, ưu tiên cải ngọt và mồng tơi..." class="w-full bg-white border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:border-brand-500 resize-none"></textarea>
                    </div>
                </div>

                <!-- Tổng kết tiền hợp đồng -->
                <div class="p-4 rounded-xl sm:rounded-2xl bg-brand-50 border border-brand-200 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                    <div>
                        <span class="text-gray-500 block">Tổng số đợt giao: <b class="text-gray-900 font-mono" id="cs-calc-deliveries">8 đợt</b></span>
                        <span class="text-[11px] text-brand-700 font-medium">Miễn phí 100% chi phí vận chuyển hỏa tốc tận nhà</span>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-xs text-gray-500 font-bold block uppercase">Thanh toán trọn gói</span>
                        <span class="text-xl font-black text-brand-800 font-mono" id="cs-calc-total">1.840.000đ</span>
                    </div>
                </div>

                <!-- Nút Submit -->
                <div class="pt-2 flex flex-col sm:flex-row justify-end gap-2.5 sm:gap-3">
                    <button type="button" onclick="closeModal('modal-create-sub')" class="w-full sm:w-auto px-5 py-2.5 rounded-xl sm:rounded-2xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-6 py-2.5 rounded-xl sm:rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-file-contract"></i> Tạo Hợp Đồng Gói VIP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Khởi tạo ngày hiện tại đồng bộ máy chủ
    const TODAY_DATE = '{{ now()->format("Y-m-d") }}';
    const PACKAGE_UNIT_PRICE = 230000; // Giá tiêu chuẩn 1 đợt giao gói VIP

    // CƠ SỞ DỮ LIỆU MẪU CHUẨN HOÁ KÈM THỰC ĐƠN RAU CỦ TỪNG ĐỢT
    const DB = {
        subscriptions: [
            {
                id: 1,
                sub_code: '#SUB-2026-001',
                customer_name: 'Chị Mai Anh',
                customer_phone: '0908.123.456',
                customer_address: 'Tòa Landmark 4, Vinhomes Central Park, Bình Thạnh',
                package_name: 'Gói Rau Định Kỳ VIP (Định lượng 3.5kg - 5kg/lần)',
                delivery_days: 'T3, T6',
                total_deliveries: 8,
                delivered_count: 6,
                status: 'active',
                next_delivery_date: TODAY_DATE,
                prepaid_amount: 1840000,
                created_at: '{{ now()->subDays(20)->format("d/m/Y") }}',
                preferences: 'Nhà có trẻ nhỏ, ưu tiên rau mồng tơi và cải ngọt baby. Không ăn mướp đắng, nếu có đổi sang bầu hoặc bí xanh.',
                deliveries: [
                    { 
                        round: 1, 
                        date: '{{ now()->subDays(18)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261015-01', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Cải Bó Xôi Hữu Cơ F1', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 01' },
                            { name: 'Cà Chua Cherry Đỏ Đà Lạt', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' },
                            { name: 'Bắp Cải Trái Tim Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
                            { name: 'Cà Rốt Hữu Cơ Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' }
                        ]
                    },
                    { 
                        round: 2, 
                        date: '{{ now()->subDays(15)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261018-04', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Rau Cải Ngọt Baby', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bí Xanh / Bí Đao Chanh', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Dưa Leo Baby Hữu Cơ', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' },
                            { name: 'Khoai Lang Mật Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Nông Trường 04' }
                        ]
                    },
                    { 
                        round: 3, 
                        date: '{{ now()->subDays(11)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261022-02', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Rau Mồng Tơi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Cà Chua Beef Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '4 quả', farm: 'Nhà Màng 01' },
                            { name: 'Đậu Cove Nhật Hữu Cơ', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 03' },
                            { name: 'Bầu Sao Non Ngọt', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' }
                        ]
                    },
                    { 
                        round: 4, 
                        date: '{{ now()->subDays(8)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261025-05', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Xà Lách Mỡ Đà Lạt Thủy Canh', weight: '1.0 kg', category: 'Rau ăn lá', qty: '3 cây', farm: 'Nhà Màng 01' },
                            { name: 'Củ Dền Hữu Cơ Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Cà Chua Cherry Vàng', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' },
                            { name: 'Bông Cải Xanh Baby (Broccoli)', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bông', farm: 'Nhà Màng 03' }
                        ]
                    },
                    { 
                        round: 5, 
                        date: '{{ now()->subDays(5)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261029-01', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Cải Thìa Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bí Đỏ Hồ Lô Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Nông Trường 04' },
                            { name: 'Ớt Chuông Ngọt Ba Màu', weight: '0.5 kg', category: 'Củ quả', qty: '3 quả', farm: 'Nhà Màng 01' },
                            { name: 'Cà Rốt Hữu Cơ Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' }
                        ]
                    },
                    { 
                        round: 6, 
                        date: '{{ now()->subDays(2)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261101-03', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Cải Bó Xôi Hữu Cơ F1', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 01' },
                            { name: 'Cà Chua Cherry Đỏ Đà Lạt', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' },
                            { name: 'Bắp Cải Trái Tim Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
                            { name: 'Bầu Sao Non Ngọt', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' }
                        ]
                    },
                    { 
                        round: 7, 
                        date: '{{ now()->format("d/m/Y") }}', 
                        order_code: 'Chờ sinh đơn', 
                        driver: 'Chưa điều phối', 
                        status: 'pending',
                        items: [
                            { name: 'Rau Mồng Tơi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Rau Cải Ngọt Baby', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Dưa Leo Baby Hữu Cơ', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' },
                            { name: 'Bí Xanh / Bí Đao Chanh', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' }
                        ]
                    },
                    { 
                        round: 8, 
                        date: '{{ now()->addDays(3)->format("d/m/Y") }}', 
                        order_code: 'Chờ sinh đơn', 
                        driver: 'Chưa điều phối', 
                        status: 'pending',
                        items: [
                            { name: 'Xà Lách Mỡ Đà Lạt Thủy Canh', weight: '1.0 kg', category: 'Rau ăn lá', qty: '3 cây', farm: 'Nhà Màng 01' },
                            { name: 'Cà Chua Beef Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '4 quả', farm: 'Nhà Màng 01' },
                            { name: 'Cà Rốt Hữu Cơ Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Cà Chua Cherry Vàng', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' }
                        ]
                    }
                ]
            },
            {
                id: 2,
                sub_code: '#SUB-2026-002',
                customer_name: 'Anh Quốc Huy',
                customer_phone: '0919.987.654',
                customer_address: '142 Nguyễn Thị Minh Khai, Phường 6, Quận 3',
                package_name: 'Gói Rau Định Kỳ VIP (Định lượng 3.5kg - 5kg/lần)',
                delivery_days: 'T6',
                total_deliveries: 4,
                delivered_count: 2,
                status: 'active',
                next_delivery_date: '{{ now()->addDays(2)->format("Y-m-d") }}',
                prepaid_amount: 920000,
                created_at: '{{ now()->subDays(14)->format("d/m/Y") }}',
                preferences: 'Thích các loại củ quả ngọt, đóng riêng trái cây ăn liền.',
                deliveries: [
                    { 
                        round: 1, 
                        date: '{{ now()->subDays(12)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261021-02', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Khoai Lang Mật Đà Lạt', weight: '1.5 kg', category: 'Củ quả', qty: '1 túi', farm: 'Nông Trường 04' },
                            { name: 'Bắp Ngọt Hữu Cơ (Ngô ngọt)', weight: '1.0 kg', category: 'Củ quả', qty: '3 bắp', farm: 'Vườn Đất Lành' },
                            { name: 'Cà Chua Cherry Đỏ', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' },
                            { name: 'Cà Rốt Baby Đà Lạt', weight: '0.5 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' }
                        ]
                    },
                    { 
                        round: 2, 
                        date: '{{ now()->subDays(5)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261028-01', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Bí Đỏ Hồ Lô Hữu Cơ', weight: '1.5 kg', category: 'Củ quả', qty: '1 quả', farm: 'Nông Trường 04' },
                            { name: 'Dưa Leo Baby Hữu Cơ', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' },
                            { name: 'Củ Dền Hữu Cơ Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Cà Chua Cherry Vàng', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' }
                        ]
                    },
                    { 
                        round: 3, 
                        date: '{{ now()->addDays(2)->format("d/m/Y") }}', 
                        order_code: 'Chờ sinh đơn', 
                        driver: 'Chưa điều phối', 
                        status: 'pending',
                        items: [
                            { name: 'Khoai Lang Mật Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Nông Trường 04' },
                            { name: 'Bí Xanh Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Bắp Cải Trái Tim', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
                            { name: 'Cà Chua Cherry Đỏ', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' }
                        ]
                    },
                    { 
                        round: 4, 
                        date: '{{ now()->addDays(9)->format("d/m/Y") }}', 
                        order_code: 'Chờ sinh đơn', 
                        driver: 'Chưa điều phối', 
                        status: 'pending',
                        items: [
                            { name: 'Bắp Ngọt Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '3 bắp', farm: 'Vườn Đất Lành' },
                            { name: 'Cà Rốt Hữu Cơ Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Củ Dền Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Dưa Leo Baby', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
                        ]
                    }
                ]
            },
            {
                id: 3,
                sub_code: '#SUB-2026-003',
                customer_name: 'Cô Bích Liên',
                customer_phone: '0933.456.789',
                customer_address: 'Số 88 Đường 41, Phường Tân Quy, Quận 7',
                package_name: 'Gói Rau Định Kỳ VIP (Định lượng 3.5kg - 5kg/lần)',
                delivery_days: 'T4',
                total_deliveries: 8,
                delivered_count: 7,
                status: 'active',
                next_delivery_date: '{{ now()->addDays(4)->format("Y-m-d") }}',
                prepaid_amount: 1840000,
                created_at: '{{ now()->subDays(25)->format("d/m/Y") }}',
                preferences: 'Người già ăn nhạt, nhiều xơ. Không lấy ớt chuông.',
                deliveries: [
                    { 
                        round: 1, 
                        date: '{{ now()->subDays(24)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261011-03', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Cải Bó Xôi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 01' },
                            { name: 'Bí Xanh Non Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Bắp Cải Trái Tim', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
                            { name: 'Dưa Leo Baby', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
                        ]
                    },
                    { 
                        round: 2, 
                        date: '{{ now()->subDays(20)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261015-02', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Rau Mồng Tơi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bầu Sao Non', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Cà Rốt Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Cà Chua Cherry Đỏ', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' }
                        ]
                    },
                    { 
                        round: 3, 
                        date: '{{ now()->subDays(17)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261018-04', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Cải Thìa Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Khoai Lang Mật', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Nông Trường 04' },
                            { name: 'Bông Cải Xanh Baby', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bông', farm: 'Nhà Màng 03' },
                            { name: 'Dưa Leo Baby', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
                        ]
                    },
                    { 
                        round: 4, 
                        date: '{{ now()->subDays(13)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261022-01', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Xà Lách Mỡ Thủy Canh', weight: '1.0 kg', category: 'Rau ăn lá', qty: '3 cây', farm: 'Nhà Màng 01' },
                            { name: 'Bí Đỏ Hồ Lô', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Nông Trường 04' },
                            { name: 'Củ Dền Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Cà Chua Cherry Vàng', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' }
                        ]
                    },
                    { 
                        round: 5, 
                        date: '{{ now()->subDays(10)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261025-03', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Cải Ngọt Baby', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bí Xanh Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Bắp Cải Trái Tim', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
                            { name: 'Cà Rốt Đà Lạt', weight: '0.5 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' }
                        ]
                    },
                    { 
                        round: 6, 
                        date: '{{ now()->subDays(7)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261028-02', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Rau Muống Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bầu Sao Non', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Cà Chua Beef Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '4 quả', farm: 'Nhà Màng 01' },
                            { name: 'Dưa Leo Baby', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
                        ]
                    },
                    { 
                        round: 7, 
                        date: '{{ now()->subDays(3)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261031-05', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Cải Bó Xôi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 01' },
                            { name: 'Khoai Lang Mật', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Nông Trường 04' },
                            { name: 'Cà Rốt Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Cà Chua Cherry Đỏ', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' }
                        ]
                    },
                    { 
                        round: 8, 
                        date: '{{ now()->addDays(4)->format("d/m/Y") }}', 
                        order_code: 'Chờ sinh đơn', 
                        driver: 'Chưa điều phối', 
                        status: 'pending',
                        items: [
                            { name: 'Rau Mồng Tơi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bắp Cải Trái Tim', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
                            { name: 'Bí Xanh Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Dưa Leo Baby', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
                        ]
                    }
                ]
            },
            {
                id: 4,
                sub_code: '#SUB-2026-004',
                customer_name: 'Chị Thu Thảo',
                customer_phone: '0909.554.433',
                customer_address: 'Chung cư Masteri Thảo Điền, Quận 2, TP. Thủ Đức',
                package_name: 'Gói Rau Định Kỳ VIP (Định lượng 3.5kg - 5kg/lần)',
                delivery_days: 'T2, T6',
                total_deliveries: 8,
                delivered_count: 2,
                status: 'active',
                next_delivery_date: TODAY_DATE,
                prepaid_amount: 1840000,
                created_at: '{{ now()->subDays(7)->format("d/m/Y") }}',
                preferences: 'Giao sáng sớm trước 9h để gia đình nấu ăn.',
                deliveries: [
                    { 
                        round: 1, 
                        date: '{{ now()->subDays(6)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261029-03', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Cải Bó Xôi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 01' },
                            { name: 'Bắp Cải Trái Tim', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
                            { name: 'Cà Chua Cherry Đỏ', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' },
                            { name: 'Cà Rốt Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' }
                        ]
                    },
                    { 
                        round: 2, 
                        date: '{{ now()->subDays(3)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261101-02', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Rau Cải Ngọt Baby', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bí Xanh Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Khoai Lang Mật', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Nông Trường 04' },
                            { name: 'Dưa Leo Baby', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
                        ]
                    },
                    { 
                        round: 3, 
                        date: '{{ now()->format("d/m/Y") }}', 
                        order_code: 'Chờ sinh đơn', 
                        driver: 'Chưa điều phối', 
                        status: 'pending',
                        items: [
                            { name: 'Rau Mồng Tơi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bầu Sao Non', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Cà Chua Beef Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '4 quả', farm: 'Nhà Màng 01' },
                            { name: 'Cà Chua Cherry Vàng', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' }
                        ]
                    },
                    { 
                        round: 4, 
                        date: '{{ now()->addDays(4)->format("d/m/Y") }}', 
                        order_code: 'Chờ sinh đơn', 
                        driver: 'Chưa điều phối', 
                        status: 'pending',
                        items: [
                            { name: 'Xà Lách Mỡ Thủy Canh', weight: '1.0 kg', category: 'Rau ăn lá', qty: '3 cây', farm: 'Nhà Màng 01' },
                            { name: 'Bông Cải Xanh Baby', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bông', farm: 'Nhà Màng 03' },
                            { name: 'Cà Rốt Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Dưa Leo Baby', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
                        ]
                    }
                ]
            },
            {
                id: 5,
                sub_code: '#SUB-2026-005',
                customer_name: 'Chị Đan Vy',
                customer_phone: '0988.665.544',
                customer_address: 'Khu biệt thự Chateau, Phú Mỹ Hưng, Quận 7',
                package_name: 'Gói Rau Định Kỳ VIP (Định lượng 3.5kg - 5kg/lần)',
                delivery_days: 'T7',
                total_deliveries: 4,
                delivered_count: 4,
                status: 'completed',
                next_delivery_date: '---',
                prepaid_amount: 920000,
                created_at: '{{ now()->subDays(35)->format("d/m/Y") }}',
                preferences: 'Hợp đồng 1 tháng đã hoàn thành trọn vẹn 4 đợt giao.',
                deliveries: [
                    { 
                        round: 1, 
                        date: '{{ now()->subDays(28)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261007-01', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Cải Bó Xôi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 01' },
                            { name: 'Bắp Cải Trái Tim', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
                            { name: 'Cà Chua Cherry Đỏ', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' },
                            { name: 'Cà Rốt Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' }
                        ]
                    },
                    { 
                        round: 2, 
                        date: '{{ now()->subDays(21)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261014-02', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Rau Cải Ngọt Baby', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bí Xanh Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Khoai Lang Mật', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Nông Trường 04' },
                            { name: 'Dưa Leo Baby', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
                        ]
                    },
                    { 
                        round: 3, 
                        date: '{{ now()->subDays(14)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261021-03', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Rau Mồng Tơi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                            { name: 'Bầu Sao Non', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                            { name: 'Cà Chua Beef Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '4 quả', farm: 'Nhà Màng 01' },
                            { name: 'Cà Chua Cherry Vàng', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' }
                        ]
                    },
                    { 
                        round: 4, 
                        date: '{{ now()->subDays(7)->format("d/m/Y") }}', 
                        order_code: '#ORD-20261028-01', 
                        driver: 'Nguyễn Văn Bình (Shipper Đất Lành)', 
                        status: 'delivered',
                        items: [
                            { name: 'Xà Lách Mỡ Thủy Canh', weight: '1.0 kg', category: 'Rau ăn lá', qty: '3 cây', farm: 'Nhà Màng 01' },
                            { name: 'Bông Cải Xanh Baby', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bông', farm: 'Nhà Màng 03' },
                            { name: 'Cà Rốt Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' },
                            { name: 'Dưa Leo Baby', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
                        ]
                    }
                ]
            }
        ]
    };

    let currentFilterStatus = 'all';
    let viewingSubId = null;

    function initSubscriptionManager() {
        applyFilters();
    }

    function applyFilters() {
        const searchInput = document.getElementById('search-input');
        const search = searchInput ? searchInput.value.toLowerCase().trim() : '';
        updateSummaryCards();

        let list = DB.subscriptions.filter(s => {
            if (currentFilterStatus === 'active' && s.status !== 'active') return false;
            if (currentFilterStatus === 'today' && (s.next_delivery_date !== TODAY_DATE || s.status !== 'active')) return false;
            if (currentFilterStatus === 'completed' && s.status !== 'completed') return false;

            if (search) {
                const matchCode = s.sub_code.toLowerCase().includes(search);
                const matchName = s.customer_name.toLowerCase().includes(search);
                const matchPhone = s.customer_phone.includes(search);
                if (!matchCode && !matchName && !matchPhone) return false;
            }
            return true;
        });

        renderTable(list);
    }

    function updateSummaryCards() {
        const totalCount = DB.subscriptions.length;
        const activeCount = DB.subscriptions.filter(s => s.status === 'active').length;
        const todayCount = DB.subscriptions.filter(s => s.status === 'active' && s.next_delivery_date === TODAY_DATE).length;
        const completedCount = DB.subscriptions.filter(s => s.status === 'completed').length;

        const statTotal = document.getElementById('stat-total');
        const statActive = document.getElementById('stat-active');
        const statToday = document.getElementById('stat-today');
        const statCompleted = document.getElementById('stat-completed');

        if (statTotal) statTotal.textContent = String(totalCount).padStart(2, '0');
        if (statActive) statActive.textContent = String(activeCount).padStart(2, '0');
        if (statToday) statToday.textContent = String(todayCount).padStart(2, '0');
        if (statCompleted) statCompleted.textContent = String(completedCount).padStart(2, '0');

        const cntAll = document.getElementById('count-all');
        const cntActive = document.getElementById('count-tab-active');
        const cntToday = document.getElementById('count-tab-today');
        const cntCompleted = document.getElementById('count-tab-completed');

        if (cntAll) cntAll.textContent = totalCount;
        if (cntActive) cntActive.textContent = activeCount;
        if (cntToday) cntToday.textContent = todayCount;
        if (cntCompleted) cntCompleted.textContent = completedCount;
    }

    function renderTable(list) {
        const tbody = document.getElementById('sub-table-body');
        const emptyState = document.getElementById('empty-state');
        if (!tbody) return;

        if (list.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }
        if (emptyState) emptyState.classList.add('hidden');

        tbody.innerHTML = list.map(s => {
            const percent = Math.min(100, Math.round((s.delivered_count / s.total_deliveries) * 100));
            const isToday = s.status === 'active' && s.next_delivery_date === TODAY_DATE;

            let statusBadge = '';
            if (s.status === 'active') {
                statusBadge = '<span class="text-xs font-bold text-emerald-800 bg-emerald-100 border border-emerald-300 px-2.5 py-1 rounded-full"><i class="fa-solid fa-circle-check mr-1"></i>Đang hoạt động</span>';
            } else {
                statusBadge = '<span class="text-xs font-bold text-gray-600 bg-gray-100 border border-gray-300 px-2.5 py-1 rounded-full"><i class="fa-solid fa-flag-checkered mr-1"></i>Đã hoàn thành</span>';
            }

            return `
                <tr class="hover:bg-brand-50/40 transition-colors bg-white border-b border-gray-100">
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <button type="button" onclick="openSubDetailModal(${s.id})" class="font-mono font-bold text-brand-800 hover:underline cursor-pointer text-xs sm:text-sm">
                            ${s.sub_code}
                        </button>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <p class="font-bold text-gray-900 text-xs sm:text-sm">${s.customer_name}</p>
                        <p class="text-xs text-gray-500 font-mono">${s.customer_phone}</p>
                        <p class="text-xs text-gray-400 truncate max-w-xs" title="${s.customer_address}">
                            <i class="fa-solid fa-location-dot text-brand-600 text-[10px] mr-1"></i>${s.customer_address}
                        </p>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <p class="font-bold text-gray-800 text-xs">${s.package_name}</p>
                        <span class="text-[11px] text-gray-500 font-mono">Định lượng: 3.5kg - 5kg / đợt</span>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <span class="font-bold text-xs text-brand-900 bg-brand-50 border border-brand-200 px-2.5 py-1 rounded-lg inline-flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-brand-700"></i>${s.delivery_days}
                        </span>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-mono font-bold">
                                <span class="text-gray-700">${s.delivered_count} / ${s.total_deliveries} đợt</span>
                                <span class="text-brand-700">${percent}%</span>
                            </div>
                            <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-brand-600 h-full rounded-full transition-all duration-300" style="width: ${percent}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <span class="font-mono text-xs font-bold ${isToday ? 'text-amber-700 bg-amber-50 border border-amber-300 px-2.5 py-1 rounded-lg animate-pulse inline-block' : 'text-gray-700'}">
                            ${s.next_delivery_date} ${isToday ? '(Hôm nay!)' : ''}
                        </span>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5 text-center">${statusBadge}</td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-6 text-right">
                        <button type="button" onclick="openSubDetailModal(${s.id})" class="p-2 rounded-xl bg-gray-50 text-gray-600 hover:text-brand-700 hover:bg-brand-50 transition border border-gray-200 cursor-pointer" title="Xem chi tiết & lịch sử đợt giao">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function setFilterStatus(status) {
        currentFilterStatus = status;
        ['all', 'active', 'today', 'completed'].forEach(s => {
            const btn = document.getElementById(`tab-${s}`);
            if (btn) {
                if (s === status) {
                    btn.className = "pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2";
                } else {
                    btn.className = "pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2";
                }
            }
        });
        applyFilters();
    }

    function resetFilters() {
        const input = document.getElementById('search-input');
        if (input) input.value = '';
        setFilterStatus('all');
    }

    // MODAL CHI TIẾT HỢP ĐỒNG
    function openSubDetailModal(id) {
        viewingSubId = id;
        const s = DB.subscriptions.find(x => x.id === id);
        if (!s) return;

        document.getElementById('mod-sub-code').textContent = s.sub_code;
        document.getElementById('mod-sub-created-at').textContent = s.created_at;
        document.getElementById('mod-cust-name').textContent = s.customer_name;
        document.getElementById('mod-cust-phone').textContent = s.customer_phone;
        document.getElementById('mod-cust-address').textContent = s.customer_address;
        document.getElementById('mod-pkg-name').textContent = s.package_name;
        document.getElementById('mod-delivery-schedule').textContent = `${s.delivery_days} hàng tuần`;
        document.getElementById('mod-prepaid-amount').textContent = `${s.prepaid_amount.toLocaleString('vi-VN')}đ`;
        document.getElementById('mod-progress-text').textContent = `${s.delivered_count} / ${s.total_deliveries} đợt`;
        document.getElementById('mod-preferences').textContent = `"${s.preferences}"`;

        const badge = document.getElementById('mod-sub-status-badge');
        if (s.status === 'active') {
            badge.className = "text-xs font-bold px-3 py-1 rounded-full border bg-emerald-100 text-emerald-800 border-emerald-300";
            badge.textContent = "Đang hoạt động";
        } else {
            badge.className = "text-xs font-bold px-3 py-1 rounded-full border bg-gray-100 text-gray-600 border-gray-300";
            badge.textContent = "Đã hoàn thành";
        }

        const tbody = document.getElementById('mod-deliveries-tbody');
        tbody.innerHTML = s.deliveries.map(d => {
            let roundBadge = '';
            if (d.status === 'delivered') {
                roundBadge = '<span class="text-xs font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full"><i class="fa-solid fa-check mr-1"></i>Đã giao thành công</span>';
            } else {
                roundBadge = '<span class="text-xs font-bold text-gray-600 bg-gray-100 px-2.5 py-0.5 rounded-full"><i class="fa-regular fa-clock mr-1"></i>Dự kiến giao</span>';
            }

            const items = d.items || [];
            const itemsCount = items.length;
            const totalKg = items.reduce((acc, it) => acc + (parseFloat(it.weight) || 0), 0).toFixed(1);
            const itemsSummary = items.map(it => it.name).join(', ');

            return `
                <tr class="hover:bg-gray-50 bg-white border-b border-gray-100">
                    <td class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold text-gray-900 font-mono">Đợt ${d.round}</td>
                    <td class="py-2.5 sm:py-3 px-3 sm:px-4 font-mono text-gray-700">${d.date}</td>
                    <td class="py-2.5 sm:py-3 px-3 sm:px-4">
                        <button type="button" onclick="openRoundItemsModal(${d.round})" class="text-left group cursor-pointer inline-flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-brand-50/80 hover:bg-brand-100 border border-brand-200 transition">
                            <div class="w-7 h-7 rounded-lg bg-brand-700 text-white flex items-center justify-center shrink-0 text-xs shadow-2xs">
                                <i class="fa-solid fa-basket-shopping"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-extrabold text-brand-900 text-xs flex items-center gap-1.5">
                                    <span>${itemsCount} loại rau củ</span>
                                    <span class="text-[10px] bg-white text-brand-800 font-mono font-bold px-1.5 py-0.2 rounded border border-brand-200">${totalKg} kg</span>
                                </div>
                                <p class="text-[11px] text-gray-600 truncate max-w-[210px] sm:max-w-[260px]" title="${itemsSummary}">${itemsSummary}</p>
                            </div>
                            <i class="fa-solid fa-chevron-right text-brand-600 text-[10px] ml-0.5 group-hover:translate-x-0.5 transition-transform"></i>
                        </button>
                    </td>
                    <td class="py-2.5 sm:py-3 px-3 sm:px-4 font-mono font-bold text-brand-800">${d.order_code}</td>
                    <td class="py-2.5 sm:py-3 px-3 sm:px-4 text-gray-600">${d.driver}</td>
                    <td class="py-2.5 sm:py-3 px-3 sm:px-4 text-center">${roundBadge}</td>
                </tr>
            `;
        }).join('');

        openModal('modal-sub-detail');
    }

    // MODAL XEM CHI TIẾT THỰC ĐƠN RAU CỦ TRONG 1 ĐỢT GIAO CỤ THỂ
    function openRoundItemsModal(roundNumber) {
        if (!viewingSubId) return;
        const s = DB.subscriptions.find(x => x.id === viewingSubId);
        if (!s) return;
        const round = s.deliveries.find(d => d.round === roundNumber);
        if (!round) return;

        document.getElementById('mri-title').textContent = `Thực Đơn Đợt Giao #${round.round}`;
        document.getElementById('mri-subtitle').textContent = `Ngày giao: ${round.date} • Hợp đồng: ${s.sub_code} (${s.customer_name})`;
        document.getElementById('mri-note').textContent = s.preferences || 'Rau tươi thu hoạch sáng sớm, đóng túi kraft thoáng khí.';

        const items = round.items && round.items.length > 0 ? round.items : [
            { name: 'Cải Bó Xôi Hữu Cơ F1', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 01' },
            { name: 'Cà Chua Cherry Đỏ Đà Lạt', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' },
            { name: 'Bắp Cải Trái Tim Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
            { name: 'Cà Rốt Hữu Cơ Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' }
        ];

        const totalWeight = items.reduce((acc, it) => acc + (parseFloat(it.weight) || 0), 0).toFixed(1);
        document.getElementById('mri-total-types').textContent = `${items.length} Loại Nông Sản Hữu Cơ`;
        document.getElementById('mri-total-weight').textContent = `Tổng định lượng: ${totalWeight} kg`;

        const listContainer = document.getElementById('mri-items-list');
        listContainer.innerHTML = items.map((it, idx) => {
            let iconClass = 'fa-leaf text-brand-600 bg-brand-50 border-brand-200';
            let iconTag = 'fa-leaf';

            if (it.category.includes('Củ') || it.category.includes('quả')) {
                iconClass = 'fa-carrot text-amber-600 bg-amber-50 border-amber-200';
                iconTag = 'fa-carrot';
            } else if (it.category.includes('Trái cây')) {
                iconClass = 'fa-apple-whole text-rose-600 bg-rose-50 border-rose-200';
                iconTag = 'fa-apple-whole';
            }

            return `
                <div class="p-3 rounded-xl sm:rounded-2xl bg-white border border-gray-200/90 hover:border-brand-300 transition flex items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 ${iconClass} text-base">
                            <i class="fa-solid ${iconTag}"></i>
                        </div>
                        <div class="min-w-0">
                            <h5 class="font-extrabold text-gray-900 text-xs sm:text-sm truncate">${it.name}</h5>
                            <div class="flex items-center gap-2 text-[10px] text-gray-500 font-medium mt-0.5">
                                <span class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded font-semibold">${it.category}</span>
                                <span>•</span>
                                <span class="text-brand-700 font-mono font-semibold">${it.farm || 'Nông Trại Đất Lành'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="font-black text-brand-800 text-xs sm:text-sm font-mono block">${it.weight}</span>
                        <span class="text-[10px] text-gray-400 font-mono font-semibold">${it.qty || '1 phần'}</span>
                    </div>
                </div>
            `;
        }).join('');

        openModal('modal-round-items');
    }

    // ĐỔI ĐỊA CHỈ GIAO HÀNG
    function changeDeliveryAddressModal() {
        if (!viewingSubId) return;
        const s = DB.subscriptions.find(x => x.id === viewingSubId);

        Swal.fire({
            title: 'Thay đổi địa chỉ nhận rau',
            input: 'textarea',
            inputValue: s.customer_address,
            inputPlaceholder: 'Nhập địa chỉ giao hàng mới cho khách...',
            showCancelButton: true,
            confirmButtonText: 'Lưu địa chỉ',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#15803d',
            preConfirm: (val) => { 
                if (!val || !val.trim()) {
                    Swal.showValidationMessage('Vui lòng nhập địa chỉ nhận hàng!');
                    return false;
                }
                return val.trim(); 
            }
        }).then(res => {
            if (res.isConfirmed) {
                s.customer_address = res.value;
                applyFilters();
                document.getElementById('mod-cust-address').textContent = s.customer_address;
                Swal.fire({ icon: 'success', title: 'Đã cập nhật địa chỉ giao!', timer: 1500, showConfirmButton: false });
            }
        });
    }

    // MỞ MODAL TẠO GÓI
    function openCreateSubModal() {
        document.getElementById('cs-name').value = '';
        document.getElementById('cs-phone').value = '';
        document.getElementById('cs-address').value = '';
        document.getElementById('cs-preferences').value = '';
        calculateNewSubPrice();
        openModal('modal-create-sub');
    }

    // TÍNH TOÁN GIÁ GÓI DUY NHẤT
    function calculateNewSubPrice() {
        const freq = parseInt(document.getElementById('cs-frequency').value) || 2;
        const weeks = parseInt(document.getElementById('cs-duration').value) || 4;

        const totalRounds = freq * weeks;
        let rawTotal = totalRounds * PACKAGE_UNIT_PRICE;

        if (weeks === 8) rawTotal = rawTotal * 0.95;
        if (weeks === 12) rawTotal = rawTotal * 0.90;

        const calcDeliveries = document.getElementById('cs-calc-deliveries');
        const calcTotal = document.getElementById('cs-calc-total');

        if (calcDeliveries) calcDeliveries.textContent = `${totalRounds} đợt giao`;
        if (calcTotal) calcTotal.textContent = `${Math.round(rawTotal).toLocaleString('vi-VN')}đ`;
    }

    // SUBMIT TẠO HỢP ĐỒNG GÓI VIP MỚI
    function handleCreateSubSubmit(e) {
        e.preventDefault();

        const name = document.getElementById('cs-name').value.trim();
        const phone = document.getElementById('cs-phone').value.trim();
        const address = document.getElementById('cs-address').value.trim();
        const freq = parseInt(document.getElementById('cs-frequency').value) || 2;
        const weeks = parseInt(document.getElementById('cs-duration').value) || 4;
        const preferences = document.getElementById('cs-preferences').value.trim() || 'Rau củ hữu cơ tươi ngon theo mùa.';

        const checkedDays = Array.from(document.querySelectorAll('input[name="delivery_day"]:checked')).map(cb => cb.value);
        if (checkedDays.length === 0) {
            Swal.fire('Lưu ý', 'Vui lòng chọn ít nhất một thứ giao trong tuần!', 'warning');
            return;
        }

        const totalRounds = freq * weeks;
        let rawTotal = totalRounds * PACKAGE_UNIT_PRICE;
        if (weeks === 8) rawTotal = rawTotal * 0.95;
        if (weeks === 12) rawTotal = rawTotal * 0.90;

        const newId = DB.subscriptions.length > 0 ? Math.max(...DB.subscriptions.map(s => s.id)) + 1 : 1;
        const newCode = `#SUB-2026-${String(newId).padStart(3, '0')}`;

        const sampleMenu = [
            [
                { name: 'Cải Bó Xôi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 01' },
                { name: 'Bắp Cải Trái Tim', weight: '1.0 kg', category: 'Rau ăn lá', qty: '1 bắp', farm: 'Nhà Màng 03' },
                { name: 'Cà Chua Cherry Đỏ', weight: '0.5 kg', category: 'Củ quả', qty: '1 hộp', farm: 'Vườn Thực Nghiệm' },
                { name: 'Cà Rốt Hữu Cơ Đà Lạt', weight: '1.0 kg', category: 'Củ quả', qty: '1 túi', farm: 'Lô Vườn Đất Lành' }
            ],
            [
                { name: 'Rau Mồng Tơi Hữu Cơ', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                { name: 'Rau Cải Ngọt Baby', weight: '1.0 kg', category: 'Rau ăn lá', qty: '2 bó', farm: 'Nhà Màng 02' },
                { name: 'Bí Xanh / Bí Đao Chanh', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                { name: 'Dưa Leo Baby Hữu Cơ', weight: '0.5 kg', category: 'Củ quả', qty: '1 khay', farm: 'Nhà Màng 01' }
            ],
            [
                { name: 'Xà Lách Mỡ Thủy Canh', weight: '1.0 kg', category: 'Rau ăn lá', qty: '3 cây', farm: 'Nhà Màng 01' },
                { name: 'Bầu Sao Non Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '1 quả', farm: 'Vườn Đất Lành' },
                { name: 'Cà Chua Beef Hữu Cơ', weight: '1.0 kg', category: 'Củ quả', qty: '4 quả', farm: 'Nhà Màng 01' },
                { name: 'Khoai Lang Mật Đà Lạt', weight: '0.5 kg', category: 'Củ quả', qty: '1 túi', farm: 'Nông Trường 04' }
            ]
        ];

        const deliveries = [];
        for (let i = 1; i <= totalRounds; i++) {
            const menuForRound = sampleMenu[(i - 1) % sampleMenu.length];
            deliveries.push({
                round: i,
                date: `{{ now()->addDays(3)->format("d/m/Y") }}`,
                order_code: 'Chờ sinh đơn',
                driver: 'Chưa điều phối',
                status: 'pending',
                items: menuForRound
            });
        }

        const newSub = {
            id: newId,
            sub_code: newCode,
            customer_name: name,
            customer_phone: phone,
            customer_address: address,
            package_name: 'Gói Rau Định Kỳ VIP (Định lượng 3.5kg - 5kg/lần)',
            delivery_days: checkedDays.join(', '),
            total_deliveries: totalRounds,
            delivered_count: 0,
            status: 'active',
            next_delivery_date: '{{ now()->addDays(3)->format("Y-m-d") }}',
            prepaid_amount: Math.round(rawTotal),
            created_at: '{{ now()->format("d/m/Y") }}',
            preferences: preferences,
            deliveries: deliveries
        };

        DB.subscriptions.unshift(newSub);
        closeModal('modal-create-sub');
        applyFilters();

        Swal.fire({
            icon: 'success',
            title: 'Tạo hợp đồng thành công!',
            html: `Đã mở hợp đồng <b class="font-mono text-emerald-800">${newCode}</b> cho khách hàng <b>${name}</b>.<br><span class="text-xs text-gray-500 font-mono">Gói VIP sẽ được vận chuyển định kỳ bởi Shipper Đất Lành.</span>`,
            confirmButtonColor: '#15803d'
        });
    }

    function openModal(id) { 
        const el = document.getElementById(id);
        if (el) el.classList.remove('hidden'); 
    }

    function closeModal(id) { 
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden'); 
    }

    // Đảm bảo chạy cả khi trang nạp trực tiếp và khi Livewire navigate
    document.addEventListener('DOMContentLoaded', initSubscriptionManager);
    document.addEventListener('livewire:navigated', initSubscriptionManager);
</script>
@endpush
