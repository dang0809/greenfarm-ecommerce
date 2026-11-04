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
                    Xử Lý Khiếu Nại & Bồi Thường
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-700"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </header>

    <!-- ================= 2. NỘI DUNG CUỘN CHÍNH ================= -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12 h-[calc(100dvh-5rem)]">

        <!-- ================= 3 THẺ TỔNG QUAN KHIẾU NẠI (KPIS) ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-5">
            
            <!-- Thẻ 1: Chờ thẩm định -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-rose-100 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-rose-600 block uppercase tracking-wider">Chờ Thẩm Định</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-rose-600 tracking-tight" id="kpi-pending-count">0</span>
                        <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full animate-pulse border border-rose-200">Cần xử lý</span>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Khách đã gửi ảnh bằng chứng</p>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>

            <!-- Thẻ 2: Đã hoàn tiền -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-emerald-100 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-emerald-700 block uppercase tracking-wider">Đã Duyệt Hoàn Tiền</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-xl sm:text-2xl font-black text-emerald-800 tracking-tight font-mono" id="kpi-refund-amount">0đ</span>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-1"><span id="kpi-refund-count">0</span> ca đã duyệt hoàn khoản/điểm</p>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>

            <!-- Thẻ 3: Đã từ chối -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-gray-500 block uppercase tracking-wider">Đã Từ Chối Bồi Hoàn</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-gray-800 tracking-tight" id="kpi-rejected-count">0</span>
                        <span class="text-xs text-gray-400 font-semibold">yêu cầu</span>
                    </div>
                    <p class="text-[11px] text-gray-400 font-medium mt-1">Quá hạn 24H hoặc không đủ căn cứ</p>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-ban"></i>
                </div>
            </div>

        </div>

        <!-- ================= BẢNG DANH SÁCH KHIẾU NẠI CHÍNH ================= -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden space-y-4">
            
            <!-- Hàng Tab Lọc Theo Đúng 3 Trạng Thái Chuẩn CSDL (pending, resolved, rejected) -->
            <div class="flex overflow-x-auto border-b border-gray-100 px-4 sm:px-6 pt-3 gap-6 text-sm no-scrollbar bg-gray-50/40">
                <button onclick="setFilterStatus('all')" id="tab-all" class="pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Tất cả</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full" id="count-all">0</span>
                </button>
                <button onclick="setFilterStatus('pending')" id="tab-pending" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Chờ thẩm định</span>
                    <span class="text-xs font-bold bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full" id="count-tab-pending">0</span>
                </button>
                <button onclick="setFilterStatus('resolved')" id="tab-resolved" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đã duyệt hoàn tiền</span>
                    <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full" id="count-tab-resolved">0</span>
                </button>
                <button onclick="setFilterStatus('rejected')" id="tab-rejected" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đã từ chối</span>
                    <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full" id="count-tab-rejected">0</span>
                </button>
            </div>

            <!-- Thanh Tìm Kiếm -->
            <div class="p-3.5 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div class="relative w-full sm:w-96">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                    <input type="text" id="search-input" oninput="applyFilters()" placeholder="Tìm Mã khiếu nại (#COM...), Mã đơn (#ORD...), Khách, SĐT..." class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                </div>

                <button onclick="resetFilters()" class="self-end sm:self-auto p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 transition shadow-xs cursor-pointer" title="Đặt lại bộ lọc">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>

            <!-- Bảng Dữ Liệu -->
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-gray-100 whitespace-nowrap">
                            <th class="py-3.5 px-4 sm:px-5">Mã Khiếu Nại</th>
                            <th class="py-3.5 px-4 sm:px-5">Đơn Hàng Gốc</th>
                            <th class="py-3.5 px-4 sm:px-5">Khách Hàng & SĐT</th>
                            <th class="py-3.5 px-4 sm:px-5">Sản Phẩm & Lô FEFO</th>
                            <th class="py-3.5 px-4 sm:px-5">Lý Do Khiếu Nại</th>
                            <th class="py-3.5 px-4 sm:px-5 text-center">Bằng Chứng</th>
                            <th class="py-3.5 px-4 sm:px-5">Tài Khoản Hoàn Tiền</th>
                            <th class="py-3.5 px-4 sm:px-5 text-right">Số Tiền Hoàn</th>
                            <th class="py-3.5 px-4 sm:px-5 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium whitespace-nowrap" id="complaints-tbody">
                        <!-- Render bằng JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Trạng thái trống -->
            <div id="empty-state" class="hidden py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
                <p class="text-base font-bold text-gray-700">Không tìm thấy yêu cầu khiếu nại nào phù hợp</p>
                <p class="text-xs text-gray-400 mt-1">Tất cả phản ánh đã được tiếp nhận và xử lý hoàn tất.</p>
            </div>
        </div>

    </div>

    <!-- ================= 4. MODAL THẨM ĐỊNH & PHÊ DUYỆT HOÀN TIỀN ================= -->
    <div id="modal-appraise" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-4xl shadow-2xl p-5 sm:p-8 max-h-[92vh] overflow-y-auto custom-scrollbar border border-gray-100 space-y-5 sm:space-y-6">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 sm:pb-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <h3 class="text-base sm:text-xl font-extrabold text-gray-900 truncate" id="mod-comp-code">#COM-...</h3>
                        <span id="mod-comp-status-badge" class="text-[10px] sm:text-xs font-bold px-2.5 sm:px-3 py-0.5 rounded-full border">Chờ thẩm định</span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5 font-mono">Gửi yêu cầu lúc: <span id="mod-comp-created-at">--</span></p>
                </div>
                <button onclick="closeModal('modal-appraise')" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base sm:text-lg"></i>
                </button>
            </div>

            <!-- PHÂN KHU A: THÔNG TIN ĐƠN GỐC, KHÁCH HÀNG & TÀI KHOẢN NGÂN HÀNG -->
            <div class="bg-[#FAFAF8] rounded-xl sm:rounded-2xl border border-gray-200/80 p-4 sm:p-5 space-y-3.5 text-xs">
                <h4 class="font-extrabold text-gray-900 uppercase tracking-wider text-[11px] flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-brand-700"></i> Phân Khu A: Đơn Hàng Gốc & Tài Khoản Nhận Tiền Hoàn
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-1">
                    <div>
                        <span class="text-gray-400 block mb-0.5">Khách hàng phản ánh:</span>
                        <p class="font-bold text-gray-900 text-sm" id="mod-cust-info">--</p>
                        <p class="text-gray-500 font-mono text-[11px]" id="mod-cust-phone">--</p>
                        <p class="text-gray-500 text-[11px] truncate mt-0.5" id="mod-cust-address">--</p>
                    </div>

                    <div>
                        <span class="text-gray-400 block mb-0.5">Chi tiết đơn hàng gốc:</span>
                        <p class="font-bold text-brand-800 font-mono text-sm" id="mod-order-code">#ORD-...</p>
                        <p class="text-gray-600 text-[11px]">Tổng giá trị đơn: <b id="mod-order-total" class="font-mono text-gray-900">--</b></p>
                        <p class="text-gray-600 text-[11px]">Nhận hàng lúc: <b id="mod-order-delivered-time">--</b></p>
                    </div>

                    <div>
                        <span class="text-gray-400 block mb-0.5">Lô nông sản xuất kho gốc:</span>
                        <span class="inline-block font-mono font-bold text-brand-800 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded text-xs" id="mod-batch-code">
                            --
                        </span>
                        <p class="text-gray-500 text-[11px] mt-1" id="mod-batch-details">--</p>
                    </div>
                </div>

                <!-- Thẻ thông tin tài khoản ngân hàng của khách (Chuẩn theo CSDL) -->
                <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200 text-emerald-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Tài khoản nhận tiền hoàn trả</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <b class="font-extrabold text-sm text-gray-900 font-mono" id="mod-bank-account-number">---</b>
                                <button type="button" onclick="copyBankAccount()" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold underline cursor-pointer" title="Sao chép số tài khoản">
                                    <i class="fa-regular fa-copy"></i> Copy
                                </button>
                            </div>
                            <p class="text-[11px] text-gray-600">Ngân hàng: <b id="mod-bank-name" class="text-gray-900">---</b> • Chủ TK: <b id="mod-bank-account-name" class="text-gray-900 uppercase">---</b></p>
                        </div>
                    </div>
                    <div class="sm:text-right">
                        <span class="text-[10px] text-gray-400 block">Tiền khách yêu cầu hoàn:</span>
                        <b class="text-base font-black text-rose-700 font-mono" id="mod-req-amount">0đ</b>
                    </div>
                </div>

                <!-- Lời nhắn của khách -->
                <div class="bg-rose-50/70 p-3.5 rounded-xl border border-rose-200/80 text-rose-950 text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-comment-dots text-rose-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <span class="font-bold block text-rose-900 mb-0.5">Nội dung phản ánh của khách hàng:</span>
                        <p class="italic leading-relaxed text-rose-900" id="mod-comp-reason">"..."</p>
                    </div>
                </div>
            </div>

            <!-- PHÂN KHU B: THƯ VIỆN HÌNH ẢNH BẰNG CHỨNG (EVIDENCE GALLERY) -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="font-extrabold text-gray-900 text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-camera text-brand-700"></i> Phân Khu B: Ảnh Chụp Thực Tế Nông Sản Bị Hỏng Do Khách Tải Lên
                    </h4>
                    <span class="text-[11px] text-gray-400 italic">Nhấp vào ảnh để phóng to kiểm tra vết dập</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="mod-evidence-gallery">
                    <!-- Render thumbnails bằng JS -->
                </div>
            </div>

            <!-- PHÂN KHU C: FORM PHÊ DUYỆT (CHỈ CÓ HOÀN TIỀN HOẶC TỪ CHỐI) -->
            <div class="bg-[#FAFAF8] rounded-xl sm:rounded-2xl border border-gray-200/80 p-4 sm:p-5 space-y-4 text-xs">
                <h4 class="font-extrabold text-gray-900 uppercase tracking-wider text-[11px] flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-brand-700"></i> Phân Khu C: Phê Duyệt Phương Án Xử Lý (Nhân Viên Sales)
                </h4>

                <!-- Đúng 2 Lựa Chọn Giải Pháp: Hoàn Tiền Hoặc Từ Chối -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5" id="resolution-options">
                    
                    <!-- Lựa chọn 1: Duyệt Hoàn Tiền -->
                    <label id="opt-label-refund" class="relative flex flex-col p-4 rounded-xl sm:rounded-2xl border-2 border-emerald-500 bg-emerald-50/50 cursor-pointer transition shadow-2xs">
                        <input type="radio" name="resolution_type" value="refund" checked onchange="toggleResolutionSection('refund')" class="absolute top-3.5 right-3.5 text-brand-600 focus:ring-brand-500 cursor-pointer">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shadow-xs"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                            <div>
                                <b class="text-sm font-extrabold text-emerald-950 block">1. Duyệt Hoàn Tiền</b>
                                <span class="text-[10px] font-semibold text-emerald-800">Chuyển trả tiền lại cho khách</span>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-500 leading-relaxed mt-1">Chuyển khoản trực tiếp vào số tài khoản khách đã cung cấp hoặc hoàn điểm thưởng VIP.</p>
                    </label>

                    <!-- Lựa chọn 2: Từ Chối Khiếu Nại -->
                    <label id="opt-label-reject" class="relative flex flex-col p-4 rounded-xl sm:rounded-2xl border-2 border-gray-200 bg-white cursor-pointer transition shadow-2xs hover:border-rose-400">
                        <input type="radio" name="resolution_type" value="reject" onchange="toggleResolutionSection('reject')" class="absolute top-3.5 right-3.5 text-rose-600 focus:ring-rose-500 cursor-pointer">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center text-sm shadow-xs"><i class="fa-solid fa-ban"></i></span>
                            <div>
                                <b class="text-sm font-extrabold text-gray-900 block">2. Từ Chối Khiếu Nại</b>
                                <span class="text-[10px] font-semibold text-rose-700">Không chấp thuận bồi thường</span>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-500 leading-relaxed mt-1">Sản phẩm quá thời hạn 24H hoặc ảnh bằng chứng không thể hiện rõ vết dập nát.</p>
                    </label>

                </div>

                <!-- Chi tiết Phân Khu Hoàn Tiền -->
                <div id="section-refund" class="p-4 rounded-xl bg-white border border-emerald-200 space-y-3.5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex-1">
                            <label class="block font-bold text-gray-700 mb-1">Số tiền duyệt hoàn trả (VNĐ) <span class="text-red-500">*</span></label>
                            <input type="number" id="refund-amount-input" value="0" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3.5 py-2.5 text-base font-mono font-bold text-brand-800 outline-none focus:border-brand-500">
                        </div>
                        <div class="flex gap-2 self-end sm:self-auto pt-1 sm:pt-4">
                            <button type="button" onclick="setQuickRefund(100)" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Hoàn 100%</button>
                            <button type="button" onclick="setQuickRefund(50)" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Hoàn 50%</button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Phương thức chi trả hoàn tiền:</label>
                        <select id="refund-method-select" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:border-brand-500 cursor-pointer">
                            <option value="bank">Chuyển khoản lại qua Ngân hàng theo số tài khoản khách đã cung cấp</option>
                            <option value="points">Cộng điểm thưởng thân thiết (Reward Points) vào tài khoản khách</option>
                        </select>
                    </div>
                </div>

                <!-- Chi tiết Phân Khu Từ Chối -->
                <div id="section-reject" class="hidden p-4 rounded-xl bg-white border border-rose-200 space-y-2.5">
                    <label class="block font-bold text-rose-900 mb-1">Lý do từ chối gửi thông báo cho khách <span class="text-red-500">*</span></label>
                    <select id="reject-reason-preset" onchange="applyRejectPreset(this.value)" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold outline-none cursor-pointer">
                        <option value="">-- Chọn lý do mẫu chuẩn --</option>
                        <option value="expired_time">Sản phẩm đã nhận quá 24h theo chính sách bồi hoàn nông sản tươi sống</option>
                        <option value="improper_storage">Hư hỏng do bảo quản ở nhiệt độ thường quá lâu, không giữ mát tủ lạnh</option>
                        <option value="insufficient_evidence">Hình ảnh gửi không thể hiện rõ vết dập nát hoặc thiếu tem nhãn lô Đất Lành</option>
                    </select>
                    <textarea id="reject-reason-text" rows="2" placeholder="Nội dung gửi thông báo từ chối đến tài khoản khách hàng..." class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl p-3 text-xs outline-none focus:border-rose-500 resize-none"></textarea>
                </div>

                <!-- Ghi chú nội bộ Sales -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ghi chú lưu vết nội bộ của Sales (`resolution_note`):</label>
                    <input type="text" id="internal-note-input" placeholder="VD: Đã đối soát ảnh chụp dập nát, chuyển khoản hoàn tiền xong lúc 10h15..." class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- Nút Hành Động -->
            <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <button type="button" onclick="closeModal('modal-appraise')" class="px-5 py-2.5 rounded-xl sm:rounded-2xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 transition cursor-pointer">
                    Đóng
                </button>
                <button type="button" id="btn-submit-resolution" onclick="confirmResolutionSubmit()" class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-6 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Xác Nhận Xử Lý Khiếu Nại</span>
                </button>
            </div>

        </div>
    </div>

    <!-- ================= 5. MODAL PHÓNG TO HÌNH ẢNH BẰNG CHỨNG ================= -->
    <div id="modal-zoom-img" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-xs hidden p-4" onclick="closeModal('modal-zoom-img')">
        <div class="relative max-w-2xl w-full bg-white rounded-2xl sm:rounded-3xl p-3 sm:p-4 shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center pb-2 mb-2 border-b border-gray-100">
                <span class="text-xs font-bold text-gray-700">Ảnh Bằng Chứng Nông Sản Khách Tải Lên</span>
                <button onclick="closeModal('modal-zoom-img')" class="w-7 h-7 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <img id="zoom-img-target" src="" class="w-full max-h-[75vh] object-contain rounded-xl">
        </div>
    </div>
</div>

@push('scripts')
<script>
    // DỮ LIỆU KHIẾU NẠI CHUẨN CSDL (3 TRẠNG THÁI: pending, resolved, rejected & CHỈ HOÀN TIỀN)
    const DB = {
        complaints: [
            {
                id: 1,
                complaint_code: '#COM-20261011-01',
                order_id: 1085,
                order_code: '#ORD-20261010-085',
                order_total: 340000,
                delivered_at: '10/10/2026 16:30',
                customer_name: 'Chị Hoàng Yến',
                customer_phone: '0912.445.678',
                customer_address: '124 Khánh Hội, Phường 3, Quận 4, TP.HCM',
                faulty_product: 'Dâu Tây Giống Nhật (Hộp 500g)',
                item_price: 145000,
                batch_code: 'TH-20261008-DAU01',
                batch_info: 'Thu hái: 08/10 • Luống 2',
                reason_category: 'Dập nát khi vận chuyển',
                reason_text: 'Mở hộp dâu tây ra thấy bị dập nát góc đáy hộp, quả bị nhũn và chảy nước đỏ ra thùng. Tôi yêu cầu farm hoàn lại tiền hộp dâu giúp mình.',
                bank_name: 'Vietcombank',
                bank_account_number: '1029384756',
                bank_account_name: 'HOANG THI HOANG YEN',
                status: 'pending',
                created_at: '11/10/2026 08:20',
                images: [
                    'https://images.unsplash.com/photo-1543528176-61b239494933?w=800',
                    'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=800'
                ],
                resolution_note: null,
                refund_amount: 145000
            },
            {
                id: 2,
                complaint_code: '#COM-20261011-02',
                order_id: 1092,
                order_code: '#ORD-20261010-092',
                order_total: 215000,
                delivered_at: '10/10/2026 14:15',
                customer_name: 'Anh Minh Trí',
                customer_phone: '0938.990.112',
                customer_address: 'Tòa Landmark 2, Vinhomes Central Park, Bình Thạnh',
                faulty_product: 'Cà Chua Cherry Đỏ Đà Lạt (1.5 kg)',
                item_price: 97500,
                batch_code: 'TH-20261001-001',
                batch_info: 'Thu hái: 01/10 • Luống 1',
                reason_category: 'Nứt vỏ, úng nước',
                reason_text: 'Cà chua bị nứt vỏ nhiều quả, có dấu hiệu bị chèn ép thùng nặng lên trên. Tôi muốn hoàn tiền số kg bị hỏng qua tài khoản ngân hàng.',
                bank_name: 'Techcombank',
                bank_account_number: '190345678910',
                bank_account_name: 'NGUYEN MINH TRI',
                status: 'pending',
                created_at: '11/10/2026 09:10',
                images: [
                    'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=800'
                ],
                resolution_note: null,
                refund_amount: 97500
            },
            {
                id: 3,
                complaint_code: '#COM-20261011-03',
                order_id: 1104,
                order_code: '#ORD-20261011-014',
                order_total: 185000,
                delivered_at: '11/10/2026 09:45',
                customer_name: 'Cô Ngọc Diệp',
                customer_phone: '0903.556.778',
                customer_address: '54 Nguyễn Thị Thập, Tân Phú, Quận 7',
                faulty_product: 'Cải Bó Xôi Hữu Cơ (1.0 kg)',
                item_price: 46000,
                batch_code: 'TH-20261005-003',
                batch_info: 'Thu hái: 05/10 • Luống 3',
                reason_category: 'Dập gãy ngọn, dập nát lá',
                reason_text: 'Túi rau bị dập gãy ngọn dập nát lá do bó quá chặt. Bác muốn hoàn lại tiền bó rau này.',
                bank_name: 'MB Bank',
                bank_account_number: '0903556778',
                bank_account_name: 'TRAN THI NGOC DIEP',
                status: 'pending',
                created_at: '11/10/2026 10:30',
                images: [
                    'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=800'
                ],
                resolution_note: null,
                refund_amount: 46000
            },
            {
                id: 4,
                complaint_code: '#COM-20261010-01',
                order_id: 1044,
                order_code: '#ORD-20261009-044',
                order_total: 420000,
                delivered_at: '09/10/2026 15:30',
                customer_name: 'Chị Thanh Hương',
                customer_phone: '0977.112.334',
                customer_address: '15/2 Bis Lê Thánh Tôn, Bến Nghé, Quận 1',
                faulty_product: 'Nấm Đùi Gà Organic (500g)',
                item_price: 65000,
                batch_code: 'TH-20261007-NAM01',
                batch_info: 'Thu hái: 07/10',
                reason_category: 'Giao thiếu số lượng',
                reason_text: 'Đơn đặt 2 gói nấm mà shipper chỉ đưa 1 gói. Nhờ farm hoàn tiền gói còn lại.',
                bank_name: 'VietinBank',
                bank_account_number: '108877665544',
                bank_account_name: 'LE THANH HUONG',
                status: 'resolved',
                created_at: '10/10/2026 08:00',
                images: [
                    'https://images.unsplash.com/photo-1509358271058-acd22cc93898?w=800'
                ],
                resolution_note: 'Đã hoàn 65.000đ trực tiếp qua STK VietinBank cho khách. Lỗi kho sót hàng.',
                refund_amount: 65000
            },
            {
                id: 5,
                complaint_code: '#COM-20261009-02',
                order_id: 1012,
                order_code: '#ORD-20261008-012',
                order_total: 290000,
                delivered_at: '08/10/2026 11:20',
                customer_name: 'Bác Trần Khang',
                customer_phone: '0982.909.111',
                customer_address: 'Vinhomes Central Park, Bình Thạnh',
                faulty_product: 'Dưa Leo Baby Nhật Bản (2 kg)',
                item_price: 110000,
                batch_code: 'TH-20261005-003',
                batch_info: 'Thu hái: 05/10 • Luống 5',
                reason_category: 'Trầy xước dập vỏ',
                reason_text: 'Dưa leo bị cấn xước vỏ nhiều quả, quả mềm.',
                bank_name: 'ACB',
                bank_account_number: '234567891',
                bank_account_name: 'TRAN VAN KHANG',
                status: 'resolved',
                created_at: '09/10/2026 10:15',
                images: [
                    'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?w=800'
                ],
                resolution_note: 'Đã chuyển khoản bồi hoàn 110.000đ qua ACB.',
                refund_amount: 110000
            },
            {
                id: 6,
                complaint_code: '#COM-20261008-01',
                order_id: 994,
                order_code: '#ORD-20261005-019',
                order_total: 510000,
                delivered_at: '05/10/2026 10:00',
                customer_name: 'Anh Tuấn Kiệt',
                customer_phone: '0918.777.888',
                customer_address: 'Chung cư Masteri Thảo Điền, Quận 2',
                faulty_product: 'Bơ Sáp Đắk Lắk (2 kg)',
                item_price: 120000,
                batch_code: 'TH-20261003-BO01',
                batch_info: 'Thu hái: 03/10',
                reason_category: 'Bơ sượng sau 4 ngày mua',
                reason_text: 'Mua từ ngày 5/10 đến nay 8/10 bơ chín bị sượng đầu.',
                bank_name: 'Vietcombank',
                bank_account_number: '007100998877',
                bank_account_name: 'VO TUAN KIET',
                status: 'rejected',
                created_at: '08/10/2026 15:30',
                images: [
                    'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=800'
                ],
                resolution_note: 'Từ chối hỗ trợ: Đã nhận hàng quá 72h theo chính sách bồi hoàn nông sản trong 24h.',
                refund_amount: 0
            }
        ]
    };

    // ĐÚNG 3 TRẠNG THÁI CSDL CHUẨN
    const STATUS_CONFIG = {
        pending: { label: 'Chờ thẩm định', badge: 'bg-amber-100 text-amber-800 border-amber-300' },
        resolved: { label: 'Đã duyệt hoàn tiền', badge: 'bg-emerald-100 text-emerald-800 border-emerald-300' },
        rejected: { label: 'Đã từ chối', badge: 'bg-rose-100 text-rose-800 border-rose-300' }
    };

    let currentFilterStatus = 'all';
    let appraisingComplaintId = null;

    function applyFilters() {
        const searchInput = document.getElementById('search-input');
        const search = searchInput ? searchInput.value.toLowerCase().trim() : '';
        updateSummaryCards();

        let list = DB.complaints.filter(c => {
            if (currentFilterStatus !== 'all' && c.status !== currentFilterStatus) return false;
            if (search) {
                const matchComp = c.complaint_code.toLowerCase().includes(search);
                const matchOrder = c.order_code.toLowerCase().includes(search);
                const matchName = c.customer_name.toLowerCase().includes(search);
                const matchPhone = c.customer_phone.includes(search);
                if (!matchComp && !matchOrder && !matchName && !matchPhone) return false;
            }
            return true;
        });

        renderTable(list);
    }

    function resetFilters() {
        const searchInput = document.getElementById('search-input');
        if (searchInput) searchInput.value = '';
        setFilterStatus('all');
    }

    function setFilterStatus(status) {
        currentFilterStatus = status;
        ['all', 'pending', 'resolved', 'rejected'].forEach(s => {
            const btn = document.getElementById(`tab-${s}`);
            if (btn) {
                if (s === status) {
                    btn.className = 'pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2 cursor-pointer';
                } else {
                    btn.className = 'pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer';
                }
            }
        });
        applyFilters();
    }

    function updateSummaryCards() {
        const pendingCount = DB.complaints.filter(c => c.status === 'pending').length;
        const resolvedList = DB.complaints.filter(c => c.status === 'resolved');
        const resolvedCount = resolvedList.length;
        const rejectedCount = DB.complaints.filter(c => c.status === 'rejected').length;

        const totalRefunded = resolvedList.reduce((sum, c) => sum + (c.refund_amount || 0), 0);

        const elPending = document.getElementById('kpi-pending-count');
        const elRefundAmt = document.getElementById('kpi-refund-amount');
        const elRefundCnt = document.getElementById('kpi-refund-count');
        const elRejected = document.getElementById('kpi-rejected-count');

        if (elPending) elPending.textContent = String(pendingCount).padStart(2, '0');
        if (elRefundAmt) elRefundAmt.textContent = totalRefunded.toLocaleString('vi-VN') + 'đ';
        if (elRefundCnt) elRefundCnt.textContent = resolvedCount;
        if (elRejected) elRejected.textContent = String(rejectedCount).padStart(2, '0');

        const elCountAll = document.getElementById('count-all');
        const elTabPending = document.getElementById('count-tab-pending');
        const elTabResolved = document.getElementById('count-tab-resolved');
        const elTabRejected = document.getElementById('count-tab-rejected');

        if (elCountAll) elCountAll.textContent = DB.complaints.length;
        if (elTabPending) elTabPending.textContent = pendingCount;
        if (elTabResolved) elTabResolved.textContent = resolvedCount;
        if (elTabRejected) elTabRejected.textContent = rejectedCount;
    }

    function renderTable(list) {
        const tbody = document.getElementById('complaints-tbody');
        const emptyState = document.getElementById('empty-state');
        if (!tbody) return;

        if (list.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }
        if (emptyState) emptyState.classList.add('hidden');

        tbody.innerHTML = list.map(c => {
            const statusCfg = STATUS_CONFIG[c.status] || { label: c.status, badge: 'bg-gray-100 text-gray-700 border-gray-200' };
            const firstImg = c.images && c.images.length > 0 ? c.images[0] : '';

            const isPending = c.status === 'pending';
            const actionBtn = isPending ? `
                <button onclick="openAppraiseModal(${c.id})" class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition shadow-xs active:scale-95 inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>Thẩm định & Hoàn tiền</span>
                </button>
            ` : `
                <button onclick="openAppraiseModal(${c.id})" class="bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 font-bold text-xs px-3 py-2 rounded-xl transition active:scale-95 inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <i class="fa-regular fa-eye"></i>
                    <span>Xem kết quả</span>
                </button>
            `;

            return `
                <tr class="hover:bg-brand-50/40 transition-colors bg-white border-b border-gray-100">
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <span class="font-mono font-bold text-brand-800 text-xs sm:text-sm cursor-pointer hover:underline" onclick="openAppraiseModal(${c.id})">${c.complaint_code}</span>
                        <span class="block text-[10px] text-gray-400 font-mono">${c.created_at}</span>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <a href="{{ route('sales.orders') }}" wire:navigate class="font-mono font-bold text-blue-700 hover:underline text-xs sm:text-sm" title="Xem đơn hàng gốc">${c.order_code}</a>
                        <span class="block text-[10px] text-gray-400 font-mono">${c.order_total.toLocaleString('vi-VN')}đ</span>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <p class="font-bold text-gray-900 text-xs sm:text-sm">${c.customer_name}</p>
                        <p class="text-xs text-gray-500 font-mono">${c.customer_phone}</p>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <p class="font-bold text-gray-800 text-xs sm:text-sm">${c.faulty_product}</p>
                        <span class="text-[11px] text-brand-800 font-mono font-semibold">Lô: ${c.batch_code}</span>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <span class="text-xs font-bold text-rose-700 block max-w-xs truncate" title="${c.reason_text}">
                            ${c.reason_category}
                        </span>
                        <p class="text-[11px] text-gray-500 truncate max-w-xs italic">"${c.reason_text}"</p>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5 text-center">
                        ${firstImg ? `
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl overflow-hidden border border-gray-200 cursor-pointer hover:opacity-80 transition shadow-2xs inline-block align-middle" onclick="zoomImage('${firstImg}')" title="Bấm xem ảnh dập nát">
                                <img src="${firstImg}" class="w-full h-full object-cover">
                            </div>
                        ` : `<span class="text-gray-300 text-xs italic">Không có</span>`}
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <p class="font-mono font-bold text-gray-900 text-xs">${c.bank_account_number}</p>
                        <p class="text-[11px] text-gray-500 font-medium">${c.bank_name} • ${c.bank_account_name}</p>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5 text-right font-mono">
                        <b class="text-xs sm:text-sm text-emerald-700">${(c.refund_amount || c.item_price).toLocaleString('vi-VN')}đ</b>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5 text-center">
                        <span class="text-[10px] sm:text-xs font-bold px-2.5 py-1 rounded-full border ${statusCfg.badge}">${statusCfg.label}</span>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-6 text-right">
                        ${actionBtn}
                    </td>
                </tr>
            `;
        }).join('');
    }

    function openAppraiseModal(id) {
        appraisingComplaintId = id;
        const c = DB.complaints.find(x => x.id === id);
        if (!c) return;

        document.getElementById('mod-comp-code').textContent = c.complaint_code;
        document.getElementById('mod-comp-created-at').textContent = c.created_at;

        const statusCfg = STATUS_CONFIG[c.status] || { label: c.status, badge: 'bg-gray-100 text-gray-700' };
        const badge = document.getElementById('mod-comp-status-badge');
        badge.className = `text-[10px] sm:text-xs font-bold px-2.5 sm:px-3 py-0.5 rounded-full border ${statusCfg.badge}`;
        badge.textContent = statusCfg.label;

        document.getElementById('mod-cust-info').textContent = c.customer_name;
        document.getElementById('mod-cust-phone').textContent = c.customer_phone;
        document.getElementById('mod-cust-address').textContent = c.customer_address;
        document.getElementById('mod-order-code').textContent = c.order_code;
        document.getElementById('mod-order-total').textContent = `${c.order_total.toLocaleString('vi-VN')}đ`;
        document.getElementById('mod-order-delivered-time').textContent = c.delivered_at;
        document.getElementById('mod-batch-code').textContent = c.batch_code;
        document.getElementById('mod-batch-details').textContent = c.batch_info;
        document.getElementById('mod-comp-reason').textContent = `"${c.reason_text}"`;

        // Điền thông tin ngân hàng của khách
        document.getElementById('mod-bank-name').textContent = c.bank_name || 'Chưa cung cấp';
        document.getElementById('mod-bank-account-number').textContent = c.bank_account_number || '---';
        document.getElementById('mod-bank-account-name').textContent = c.bank_account_name || '---';
        document.getElementById('mod-req-amount').textContent = `${(c.refund_amount || c.item_price).toLocaleString('vi-VN')}đ`;

        const gallery = document.getElementById('mod-evidence-gallery');
        if (c.images && c.images.length > 0) {
            gallery.innerHTML = c.images.map(imgUrl => `
                <div class="relative group rounded-xl overflow-hidden border border-gray-200 aspect-square cursor-pointer shadow-2xs hover:border-brand-500 transition" onclick="zoomImage('${imgUrl}')">
                    <img src="${imgUrl}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1">
                        <i class="fa-solid fa-magnifying-glass-plus"></i> Phóng to
                    </div>
                </div>
            `).join('');
        } else {
            gallery.innerHTML = '<p class="col-span-full text-xs text-gray-400 italic">Khách hàng không đính kèm hình ảnh.</p>';
        }

        document.getElementById('refund-amount-input').value = c.refund_amount || c.item_price;
        document.getElementById('internal-note-input').value = c.resolution_note || '';

        // Tùy trạng thái để bật/tắt quyền thao tác
        const btnSubmit = document.getElementById('btn-submit-resolution');
        const radioRefund = document.querySelector('input[name="resolution_type"][value="refund"]');
        const radioReject = document.querySelector('input[name="resolution_type"][value="reject"]');

        if (c.status === 'resolved') {
            radioRefund.checked = true;
            toggleResolutionSection('refund');
            btnSubmit.classList.add('hidden');
        } else if (c.status === 'rejected') {
            radioReject.checked = true;
            toggleResolutionSection('reject');
            btnSubmit.classList.add('hidden');
        } else {
            // Pending: mặc định chọn Hoàn tiền
            radioRefund.checked = true;
            toggleResolutionSection('refund');
            btnSubmit.classList.remove('hidden');
        }

        openModal('modal-appraise');
    }

    function copyBankAccount() {
        if (!appraisingComplaintId) return;
        const c = DB.complaints.find(x => x.id === appraisingComplaintId);
        if (!c || !c.bank_account_number) return;
        navigator.clipboard.writeText(c.bank_account_number);
        Swal.fire({
            icon: 'success',
            title: 'Đã sao chép số tài khoản!',
            text: `${c.bank_account_number} (${c.bank_name})`,
            timer: 1500,
            showConfirmButton: false
        });
    }

    function toggleResolutionSection(type) {
        const secRefund = document.getElementById('section-refund');
        const secReject = document.getElementById('section-reject');
        const lblRefund = document.getElementById('opt-label-refund');
        const lblReject = document.getElementById('opt-label-reject');

        if (type === 'refund') {
            secRefund.classList.remove('hidden');
            secReject.classList.add('hidden');
            lblRefund.className = 'relative flex flex-col p-4 rounded-xl sm:rounded-2xl border-2 border-emerald-500 bg-emerald-50/50 cursor-pointer transition shadow-2xs';
            lblReject.className = 'relative flex flex-col p-4 rounded-xl sm:rounded-2xl border-2 border-gray-200 bg-white cursor-pointer transition shadow-2xs hover:border-rose-400';
        } else if (type === 'reject') {
            secRefund.classList.add('hidden');
            secReject.classList.remove('hidden');
            lblRefund.className = 'relative flex flex-col p-4 rounded-xl sm:rounded-2xl border-2 border-gray-200 bg-white cursor-pointer transition shadow-2xs hover:border-emerald-500';
            lblReject.className = 'relative flex flex-col p-4 rounded-xl sm:rounded-2xl border-2 border-rose-500 bg-rose-50/50 cursor-pointer transition shadow-2xs';
        }
    }

    function setQuickRefund(percent) {
        const c = DB.complaints.find(x => x.id === appraisingComplaintId);
        if (!c) return;
        const baseAmount = c.item_price || c.refund_amount || 0;
        const amount = Math.round((baseAmount * percent) / 100);
        document.getElementById('refund-amount-input').value = amount;
    }

    function applyRejectPreset(val) {
        const textarea = document.getElementById('reject-reason-text');
        if (val === 'expired_time') textarea.value = 'Rất tiếc, nông sản tươi sạch chỉ áp dụng chính sách hoàn tiền trong vòng 24H kể từ lúc nhận hàng theo quy định Nông trại Đất Lành.';
        else if (val === 'improper_storage') textarea.value = 'Nông sản bị hư hỏng do bảo quản ở nhiệt độ thường quá lâu sau khi nhận, không tuân thủ hướng dẫn giữ mát tủ lạnh.';
        else if (val === 'insufficient_evidence') textarea.value = 'Hình ảnh cung cấp chưa thể hiện rõ vết dập nát hoặc thiếu thông tin tem nhãn mã lô của Nông trại để đối soát.';
    }

    function confirmResolutionSubmit() {
        if (!appraisingComplaintId) return;
        const c = DB.complaints.find(x => x.id === appraisingComplaintId);
        if (!c) return;

        const selectedType = document.querySelector('input[name="resolution_type"]:checked').value;
        const internalNote = document.getElementById('internal-note-input').value.trim();

        if (selectedType === 'refund') {
            const amount = parseFloat(document.getElementById('refund-amount-input').value) || 0;
            const method = document.getElementById('refund-method-select').value;

            c.status = 'resolved';
            c.refund_amount = amount;
            c.resolution_note = `${internalNote ? internalNote + '. ' : ''}Đã hoàn ${amount.toLocaleString('vi-VN')}đ qua ${method === 'bank' ? `Ngân hàng (${c.bank_name} - ${c.bank_account_number})` : 'Điểm thưởng VIP'}.`;

            closeModal('modal-appraise');
            applyFilters();

            Swal.fire({
                icon: 'success',
                title: 'Đã Duyệt Hoàn Tiền Thành Công!',
                html: `Đã duyệt hoàn tiền: <b class="font-mono text-emerald-800 text-lg">${amount.toLocaleString('vi-VN')}đ</b><br>Khách hàng: <b>${c.customer_name}</b><br>STK nhận: <b>${c.bank_account_number} (${c.bank_name})</b><br><span class="text-xs text-gray-500 mt-2 block">Trạng thái khiếu nại đã chuyển sang "Đã duyệt hoàn tiền".</span>`,
                confirmButtonColor: '#15803d'
            });

        } else if (selectedType === 'reject') {
            const rejectText = document.getElementById('reject-reason-text').value.trim();
            if (!rejectText) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Thiếu lý do từ chối',
                    text: 'Vui lòng chọn hoặc nhập lý do từ chối để gửi phản hồi cho khách hàng!',
                    confirmButtonColor: '#dc2626'
                });
                return;
            }

            c.status = 'rejected';
            c.resolution_note = `Từ chối bồi hoàn: ${rejectText}. ${internalNote ? '(' + internalNote + ')' : ''}`;

            closeModal('modal-appraise');
            applyFilters();

            Swal.fire({
                icon: 'info',
                title: 'Đã Từ Chối Khiếu Nại',
                text: `Hệ thống đã ghi nhận lý do từ chối và gửi phản hồi đến tài khoản ${c.customer_name}.`,
                confirmButtonColor: '#dc2626'
            });
        }
    }

    function zoomImage(url) {
        const target = document.getElementById('zoom-img-target');
        if (target) target.src = url;
        openModal('modal-zoom-img');
    }

    function openModal(id) { 
        const m = document.getElementById(id);
        if (m) m.classList.remove('hidden'); 
    }
    
    function closeModal(id) { 
        const m = document.getElementById(id);
        if (m) m.classList.add('hidden'); 
    }

    // Tương thích cả khi tải mới lẫn khi chuyển tab wire:navigate
    document.addEventListener('livewire:navigated', () => {
        applyFilters();
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => applyFilters());
    } else {
        applyFilters();
    }
</script>
@endpush
