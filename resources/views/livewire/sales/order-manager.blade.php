<div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">
    <!-- ================= 1. HEADER ================= -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5 truncate">
                    <span class="text-brand-700">Nông Trại Đất Lành</span>
                </div>
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">Quản Lý Đơn Hàng</h1>
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

    <!-- ================= 2. NỘI DUNG CUỘN ================= -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12 h-[calc(100dvh-5rem)]">

        <!-- ================= TOOLBAR: TÌM KIẾM, BỘ LỌC & TABS ================= -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden space-y-4">
            
            <!-- Hàng Tab Trạng Thái Đơn Hàng Chuẩn CSDL & Đồng Bộ Với Order -->
            <div class="flex overflow-x-auto border-b border-gray-100 px-4 sm:px-6 pt-3 gap-6 text-sm no-scrollbar bg-gray-50/40">
                <button onclick="setFilterStatus('all')" id="tab-all" class="pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Tất cả</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full" id="count-all">0</span>
                </button>
                <button onclick="setFilterStatus('pending')" id="tab-pending" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Chờ xác nhận</span>
                    <span class="text-xs font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full" id="count-pending">0</span>
                </button>
                <button onclick="setFilterStatus('processing')" id="tab-processing" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer" title="Đã chuẩn bị xong hàng, chờ shipper tới lấy">
                    <span>Chờ lấy hàng</span>
                    <span class="text-xs font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full" id="count-processing">0</span>
                </button>
                <button onclick="setFilterStatus('shipped')" id="tab-shipped" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đang giao</span>
                    <span class="text-xs font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full" id="count-shipped">0</span>
                </button>
                <button onclick="setFilterStatus('delivered')" id="tab-delivered" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đã giao</span>
                    <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full" id="count-delivered">0</span>
                </button>
                <button onclick="setFilterStatus('cancelled')" id="tab-cancelled" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đã hủy</span>
                    <span class="text-xs font-bold bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full" id="count-cancelled">0</span>
                </button>
            </div>

            <!-- Thanh Tìm Kiếm, Bộ Lọc & Nút Tạo Đơn Mới -->
            <div class="p-3.5 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 flex-1">
                    <!-- Ô tìm kiếm -->
                    <div class="relative w-full sm:w-72 md:w-80">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                        <input type="text" id="search-input" oninput="applyFilters()" placeholder="Tìm mã đơn, tên khách, số điện thoại..." class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                    </div>

                    <!-- Lọc theo loại đơn hàng CSDL -->
                    <select id="filter-type" onchange="applyFilters()" class="w-full sm:w-auto bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 transition-all cursor-pointer">
                        <option value="">-- Tất cả loại đơn hàng --</option>
                        <option value="retail">Đơn mua lẻ thông thường</option>
                        <option value="subscription">Gói rau giao định kỳ</option>
                        <option value="compensation">Đơn bù hàng khiếu nại</option>
                    </select>

                    <!-- Lọc theo thanh toán -->
                    <select id="filter-payment" onchange="applyFilters()" class="w-full sm:w-auto bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 transition-all cursor-pointer">
                        <option value="">-- Tình trạng thanh toán --</option>
                        <option value="pending">Chưa thu (Chờ COD / Chuyển khoản)</option>
                        <option value="paid">Đã thanh toán</option>
                        <option value="failed">Thanh toán thất bại</option>
                    </select>

                    <button onclick="resetFilters()" class="self-end sm:self-auto p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 transition shadow-xs" title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>

                <!-- Nút Tạo Đơn Hàng Mới -->
                <button onclick="openCreateOrderModal()" class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95 shrink-0 whitespace-nowrap self-end lg:self-auto cursor-pointer">
                    <i class="fa-solid fa-plus-circle text-sm"></i>
                    <span>+ Tạo Đơn Hàng Mới</span>
                </button>
            </div>
        </div>

        <!-- ================= BẢNG DANH SÁCH ĐƠN HÀNG ================= -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden">
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-gray-100">
                            <th class="py-3.5 sm:py-4 px-3 sm:px-4 pl-4 sm:pl-6 w-10 text-center">
                                <input type="checkbox" id="check-all" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded text-brand-600 border-gray-300 focus:ring-brand-500 cursor-pointer">
                            </th>
                            <th class="py-3.5 sm:py-4 px-3 sm:px-4">Mã Đơn Hàng</th>
                            <th class="py-3.5 sm:py-4 px-3 sm:px-4">Loại Đơn</th>
                            <th class="py-3.5 sm:py-4 px-3 sm:px-4">Người Nhận & Địa Chỉ</th>
                            <th class="py-3.5 sm:py-4 px-3 sm:px-4">Thời Gian Đặt</th>
                            <th class="py-3.5 sm:py-4 px-3 sm:px-4 text-right">Tổng Tiền</th>
                            <th class="py-3.5 sm:py-4 px-3 sm:px-4">Vận Chuyển & Shipper</th>
                            <th class="py-3.5 sm:py-4 px-3 sm:px-4 text-center">Trạng Thái</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium" id="order-table-body">
                        <!-- Render bằng JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Trạng thái trống -->
            <div id="empty-state" class="hidden py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <p class="text-base font-bold text-gray-700">Không tìm thấy đơn hàng nào phù hợp</p>
                <p class="text-xs text-gray-400 mt-1">Vui lòng thay đổi từ khóa tìm kiếm hoặc điều chỉnh bộ lọc trạng thái.</p>
            </div>
        </div>
    </div>

    <!-- ================= 3. THANH THAO TÁC HÀNG LOẠT ================= -->
    <div id="bulk-actions-bar" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900 text-white px-4 sm:px-6 py-2.5 sm:py-3.5 rounded-2xl sm:rounded-full shadow-floating border border-slate-700 flex items-center gap-2.5 sm:gap-4 transition-all duration-300 transform translate-y-24 opacity-0 pointer-events-none max-w-[95vw] overflow-x-auto no-scrollbar">
        <span class="text-xs font-semibold whitespace-nowrap text-slate-300">
            Đã chọn <b class="text-white font-mono text-sm" id="bulk-selected-count">0</b> đơn:
        </span>
        
        <div class="h-4 w-px bg-slate-700 shrink-0"></div>

        <button onclick="bulkApproveOrders()" class="bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl sm:rounded-full transition flex items-center gap-1.5 shadow-sm active:scale-95 whitespace-nowrap shrink-0 cursor-pointer" title="Xác nhận & chuẩn bị xong hàng loạt (Chuyển sang Chờ lấy hàng)">
            <i class="fa-solid fa-check"></i> Xác nhận loạt (Chuẩn bị xong)
        </button>

        <button onclick="bulkDispatchOrders()" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl sm:rounded-full transition flex items-center gap-1.5 shadow-sm active:scale-95 whitespace-nowrap shrink-0 cursor-pointer" title="Bàn giao hàng cho Shipper đi giao (Chuyển sang Đang giao)">
            <i class="fa-solid fa-motorcycle"></i> Bàn giao Shipper loạt
        </button>

        <button onclick="bulkCompleteOrders()" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl sm:rounded-full transition flex items-center gap-1.5 shadow-sm active:scale-95 whitespace-nowrap shrink-0 cursor-pointer" title="Xác nhận khách đã nhận hàng thành công (Chuyển sang Đã giao)">
            <i class="fa-solid fa-circle-check"></i> Đã giao loạt
        </button>

        <button onclick="bulkPrintPackingSlips()" class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl sm:rounded-full transition flex items-center gap-1.5 border border-slate-600 active:scale-95 whitespace-nowrap shrink-0">
            <i class="fa-solid fa-print"></i> In phiếu
        </button>

        <button onclick="bulkCancelOrders()" class="bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl sm:rounded-full transition flex items-center gap-1.5 active:scale-95 whitespace-nowrap shrink-0">
            <i class="fa-solid fa-ban"></i> Hủy đơn
        </button>

        <button onclick="deselectAll()" class="text-slate-400 hover:text-white p-1 text-xs shrink-0" title="Bỏ chọn">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- ================= 4. MODAL TẠO ĐƠN HÀNG MỚI (TỰ GÁN LÔ FEFO, KHÔNG ĐỔI LÔ) ================= -->
    <div id="modal-create-order" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-4xl shadow-2xl p-5 sm:p-8 max-h-[90vh] overflow-y-auto custom-scrollbar border border-gray-100 space-y-4 sm:space-y-6">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 sm:pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-lg border border-brand-200 shrink-0">
                        <i class="fa-solid fa-cart-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 leading-tight">Tạo Đơn Hàng Mới Cho Khách</h3>
                        <p class="text-xs text-gray-400">Dành cho Sales lên đơn qua Hotline, Zalo & Fanpage (Tự gán Lô xuất kho theo FEFO)</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-create-order')" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form onsubmit="handleCreateOrderSubmit(event)" class="space-y-4 sm:space-y-6">
                <!-- KHỐI 1: THÔNG TIN KHÁCH HÀNG & GIAO HÀNG -->
                <div class="bg-[#FAFAF8] rounded-xl sm:rounded-2xl border border-gray-200/80 p-4 sm:p-5 space-y-3 sm:space-y-4">
                    <h4 class="font-extrabold text-gray-900 text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-user-tag text-brand-700"></i> 1. Thông Tin Người Nhận & Vận Chuyển
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tên người nhận <span class="text-red-500">*</span></label>
                            <input type="text" id="co-name" required placeholder="VD: Chị Minh Anh" class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                            <input type="text" id="co-phone" required placeholder="VD: 0901234567" class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm outline-none focus:border-brand-500 font-mono">
                        </div>
                        <div class="sm:col-span-2 md:col-span-1">
                            <label class="block font-bold text-gray-700 mb-1">Loại đơn hàng</label>
                            <select id="co-order-type" onchange="toggleOrderTypeFee()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 sm:py-2.5 text-xs font-semibold outline-none focus:border-brand-500">
                                <option value="retail">Đơn mua lẻ thông thường</option>
                                <option value="subscription">Gói rau định kỳ (Giao tuần)</option>
                                <option value="compensation">Đơn bù dập nát (Miễn phí 0đ)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 text-xs">
                        <div class="md:col-span-2">
                            <label class="block font-bold text-gray-700 mb-1">Địa chỉ giao hàng chi tiết <span class="text-red-500">*</span></label>
                            <input type="text" id="co-address" required placeholder="Số nhà, tên đường, phường, quận..." class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Khoảng cách từ Farm (km)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="co-km" min="0.5" step="0.5" value="5" oninput="calculateShippingFee()" class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm font-mono font-bold text-brand-800 outline-none focus:border-brand-500">
                                <span class="text-xs text-gray-500 font-mono shrink-0">km</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Hình thức thanh toán</label>
                            <select id="co-payment-method" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 sm:py-2.5 text-xs font-semibold outline-none focus:border-brand-500">
                                <option value="cod">Thu tiền tận nơi khi nhận hàng (COD)</option>
                                <option value="payos">Chuyển khoản QR PayOS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Ghi chú giao hàng</label>
                            <input type="text" id="co-note" placeholder="Dặn dò shipper hoặc yêu cầu của khách..." class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2 sm:py-2.5 text-xs outline-none focus:border-brand-500">
                        </div>
                    </div>
                </div>

                <!-- KHỐI 2: CHỌN SẢN PHẨM & TỰ ĐỘNG GÁN LÔ FEFO -->
                <div class="space-y-3 text-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <h4 class="font-extrabold text-gray-900 text-xs uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-carrot text-brand-700"></i> 2. Chọn Nông Sản & Tự Động Gán Lô FEFO
                        </h4>
                        <span class="text-[11px] font-mono text-brand-700 font-bold bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-lg w-max">
                            Hệ thống tự động chọn lô cận hạn trước
                        </span>
                    </div>

                    <!-- Thanh chọn thêm món (HIỂN THỊ LÔ FEFO ĐƯỢC GÁN, KHÔNG ĐỔI LÔ) -->
                    <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-white border border-gray-200 flex flex-col md:flex-row md:items-end gap-3 shadow-2xs">
                        <div class="flex-1">
                            <label class="block font-bold text-gray-700 mb-1">Chọn loại rau củ quả:</label>
                            <select id="co-select-product" onchange="onProductSelectChange()" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold outline-none focus:border-brand-500">
                                <option value="1">Cà Chua Cherry Đỏ Đà Lạt (65.000đ/kg)</option>
                                <option value="2">Cải Bó Xôi Hữu Cơ Spinach (46.000đ/kg)</option>
                                <option value="3">Dưa Leo Baby Nhật Bản (55.000đ/kg)</option>
                            </select>
                        </div>

                        <!-- THÔNG TIN LÔ TỰ ĐỘNG GÁN THEO FEFO (CHỈ ĐỌC) -->
                        <div class="w-full md:w-64">
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-gray-700">Lô xuất kho (FEFO):</label>
                                <span class="text-[10px] text-brand-700 font-bold bg-brand-50 border border-brand-200 px-1.5 rounded">Tự động</span>
                            </div>
                            <div id="co-assigned-batch-badge" class="w-full bg-amber-50/60 border border-amber-300 font-mono text-xs text-amber-950 font-bold rounded-xl px-3 py-2">
                                <!-- Render lô tối ưu FEFO bằng JS -->
                            </div>
                        </div>

                        <div class="w-full sm:w-28">
                            <label class="block font-bold text-gray-700 mb-1">Số lượng (Kg):</label>
                            <input type="number" id="co-input-qty" min="0.5" step="0.5" value="1" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl p-2 text-sm font-mono font-bold text-center outline-none focus:border-brand-500">
                        </div>

                        <button type="button" onclick="addItemToNewOrder()" class="w-full md:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 px-4 rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs shrink-0 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Thêm món
                        </button>
                    </div>

                    <!-- Bảng các món đã chọn trong đơn (KHÔNG CÓ NÚT ĐỔI LÔ) -->
                    <div class="overflow-x-auto rounded-xl sm:rounded-2xl border border-gray-200 bg-white custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-200">
                                    <th class="py-2.5 px-4">Tên Nông Sản</th>
                                    <th class="py-2.5 px-4 text-center">Số Lượng</th>
                                    <th class="py-2.5 px-4 text-right">Đơn Giá</th>
                                    <th class="py-2.5 px-4">Lô Xuất Kho Được Gán (FEFO)</th>
                                    <th class="py-2.5 px-4 text-right">Thành Tiền</th>
                                    <th class="py-2.5 px-4 text-center w-20">Xóa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium" id="co-items-tbody">
                                <!-- Render JS các món đã chọn -->
                            </tbody>
                        </table>
                    </div>

                    <!-- BẢNG TỔNG KẾT TIỀN ĐƠN MỚI -->
                    <div class="p-4 rounded-xl sm:rounded-2xl bg-gray-50 border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="text-xs text-gray-600 font-mono space-y-1">
                            <p>Tiền hàng: <b class="text-gray-900" id="co-subtotal-text">0đ</b></p>
                            <p>Phí ship hỏa tốc (<span id="co-km-text">5</span> km): <b class="text-gray-900" id="co-shipfee-text">25.000đ</b></p>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-xs text-gray-400 font-bold uppercase block">Tổng Tiền Cần Thanh Toán</span>
                            <span class="text-2xl font-black text-brand-700 font-mono" id="co-total-text">25.000đ</span>
                        </div>
                    </div>
                </div>

                <!-- Nút Submit -->
                <div class="pt-2 flex flex-col sm:flex-row justify-end gap-2.5 sm:gap-3 border-t border-gray-100">
                    <button type="button" onclick="closeModal('modal-create-order')" class="w-full sm:w-auto px-5 py-2.5 rounded-xl sm:rounded-2xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 transition">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-7 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl transition shadow-md flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>Xác Nhận Tạo Đơn Hàng</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= 5. MODAL CHI TIẾT ĐƠN HÀNG (GIẢ LẬP GIAO HÀNG ĐỔI TRẠNG THÁI) ================= -->
    <div id="modal-order-detail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-4xl shadow-2xl p-5 sm:p-8 max-h-[90vh] overflow-y-auto custom-scrollbar border border-gray-100 space-y-4 sm:space-y-6">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 sm:pb-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 truncate" id="mod-order-code">#ORD-...</h3>
                        <span id="mod-type-badge" class="text-[10px] sm:text-xs font-bold px-2 sm:px-2.5 py-0.5 rounded-full border">Đơn lẻ</span>
                        <span id="mod-status-badge" class="text-[10px] sm:text-xs font-bold px-2 sm:px-2.5 py-0.5 rounded-full border">Chờ xác nhận</span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-gray-400 mt-1 font-mono">Thời gian đặt: <span id="mod-created-at">--</span></p>
                </div>
                <button onclick="closeModal('modal-order-detail')" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0">
                    <i class="fa-solid fa-xmark text-base sm:text-lg"></i>
                </button>
            </div>

            <!-- THÔNG TIN NGƯỜI NHẬN & VẬN CHUYỂN -->
            <div class="bg-[#FAFAF8] rounded-xl sm:rounded-2xl border border-gray-200/80 p-4 sm:p-5 space-y-3 text-xs">
                <div class="flex items-center justify-between border-b border-gray-200/60 pb-2">
                    <h4 class="font-extrabold text-gray-900 uppercase tracking-wider text-[11px] flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-brand-700"></i> Thông Tin Người Nhận & Vận Chuyển Hỏa Tốc
                    </h4>
                    <button onclick="openEditCustomerModal()" class="text-brand-700 font-bold hover:underline cursor-pointer">
                        <i class="fa-regular fa-pen-to-square"></i> Sửa thông tin
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4">
                    <div>
                        <span class="text-gray-400 block mb-0.5">Người nhận hàng:</span>
                        <p class="font-bold text-gray-900 text-sm" id="mod-cust-name">--</p>
                        <p class="font-mono text-gray-600 mt-0.5" id="mod-cust-phone">--</p>
                    </div>
                    <div>
                        <span class="text-gray-400 block mb-0.5">Địa chỉ nhận hàng:</span>
                        <p class="font-semibold text-gray-800 leading-snug" id="mod-cust-address">--</p>
                    </div>
                    <div>
                        <span class="text-gray-400 block mb-0.5">Khoảng cách & Cước phí:</span>
                        <p class="font-bold text-emerald-800 font-mono text-sm" id="mod-shipping-calc">-- km (Phí ship: --đ)</p>
                        <span class="inline-block mt-1 text-[10px] text-gray-500 bg-white px-2 py-0.5 rounded border border-gray-200 font-mono" id="mod-payment-badge">COD</span>
                    </div>
                </div>

                <!-- KHỐI THÔNG TIN SHIPPER NỘI BỘ NÔNG TRẠI ĐẤT LÀNH -->
                <div id="mod-courier-assigned-box" class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-gradient-to-r from-blue-50/90 to-indigo-50/80 border border-blue-200 text-blue-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg shrink-0 shadow-2xs">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-xs sm:text-sm text-blue-950" id="mod-courier-driver-name">Shipper: Nguyễn Văn Bình</span>
                                <span class="text-[10px] font-bold bg-white text-blue-700 border border-blue-200 px-2 py-0.2 rounded-full font-mono">Đội xe nội bộ Đất Lành</span>
                            </div>
                            <p class="text-[11px] text-blue-800 font-mono mt-0.5" id="mod-courier-driver-phone">SĐT: 0938.445.566 • Xe máy thùng lạnh 59-X1 889.24</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right border-t sm:border-t-0 border-blue-200/60 pt-2 sm:pt-0">
                        <span class="text-[10px] text-blue-600 font-bold block uppercase tracking-wider" id="mod-courier-status-title">Trạng thái giao hàng</span>
                        <p class="font-extrabold text-xs text-blue-900" id="mod-courier-status-desc">Đang trên đường giao tận nhà</p>
                    </div>
                </div>

                <div class="bg-amber-50/70 p-3 rounded-xl border border-amber-200/80 text-amber-900 text-xs flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-amber-600 shrink-0"></i>
                    <div>
                        <span class="font-bold">Ghi chú của khách:</span>
                        <span id="mod-cust-note" class="italic">"Không có ghi chú thêm"</span>
                    </div>
                </div>
            </div>

            <!-- BẢNG NÔNG SẢN & LÔ FEFO (KHÔNG CÓ QUYỀN ĐỔI LÔ) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-gray-900 text-xs sm:text-sm flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-brand-700"></i> Danh Sách Nông Sản & Lô Xuất Kho FEFO
                        </h4>
                        <p class="text-[11px] text-gray-400">Lô nông sản được gán tự động từ kho theo nguyên tắc xuất trước (FEFO).</p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl sm:rounded-2xl border border-gray-200/80 bg-white custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4">Sản Phẩm Đặt Mua</th>
                                <th class="py-3 px-4 text-center">Số Lượng</th>
                                <th class="py-3 px-4 text-right">Đơn Giá & Thành Tiền</th>
                                <th class="py-3 px-4">Lô Hàng Xuất Kho (FEFO)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium" id="mod-fefo-tbody">
                            <!-- Render bằng JS (Không có cột đổi lô) -->
                        </tbody>
                    </table>
                </div>

                <div class="p-4 rounded-xl sm:rounded-2xl bg-gray-50 border border-gray-200/80 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                    <div class="space-y-0.5 text-gray-500 font-mono">
                        <p>Tiền hàng: <b class="text-gray-800" id="mod-subtotal">--đ</b></p>
                        <p>Phí vận chuyển: <b class="text-gray-800" id="mod-ship-fee">--đ</b></p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-xs text-gray-400 font-bold uppercase tracking-wider block">Tổng Thanh Toán Cuối</span>
                        <span class="text-xl font-black text-brand-700 font-mono" id="mod-total-final">--đ</span>
                    </div>
                </div>
            </div>

            <!-- DÒNG THỜI GIAN LỊCH SỬ TRẠNG THÁI -->
            <div class="space-y-3 pt-3 border-t border-gray-100">
                <h4 class="font-extrabold text-gray-900 text-xs uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-timeline text-brand-700"></i> Lịch Sử Trạng Thái Đơn Hàng
                </h4>
                <div class="p-4 rounded-xl sm:rounded-2xl bg-[#FAFAF8] border border-gray-200/60" id="mod-timeline-container">
                    <!-- Render Timeline bằng JS -->
                </div>
            </div>

            <!-- KHU VỰC CÁC NÚT GIẢ LẬP GIAO HÀNG THEO TRẠNG THÁI (KHÔNG CẦN GỌI NGOÀI ĐỜI) -->
            <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button id="btn-cancel-order" onclick="cancelCurrentOrder()" class="flex-1 sm:flex-none bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold px-4 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-ban"></i> Hủy Đơn Hàng
                    </button>
                    <button onclick="printCurrentPackingSlip()" class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-4 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-print"></i> In Phiếu Đóng Gói
                    </button>
                </div>

                <!-- CÁC NÚT ĐỔI TRẠNG THÁI THEO TIẾN TRÌNH CÁC TAB ĐƠN HÀNG -->
                <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                    <!-- 1. Pending -> Processing (Xác nhận đơn & Chuẩn bị xong -> Chuyển sang Chờ lấy hàng) -->
                    <button id="btn-to-pack" onclick="approveCurrentOrder()" class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 shadow-sm cursor-pointer" title="Đã chuẩn bị xong hàng, chuyển sang Chờ lấy hàng">
                        <i class="fa-solid fa-check"></i> Xác Nhận Đơn (Chuẩn Bị Xong)
                    </button>
                    <!-- 2. Processing -> Shipped (Bàn giao Shipper -> Chuyển sang Đang giao) -->
                    <button id="btn-to-dispatch" onclick="dispatchCurrentOrder()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 shadow-sm cursor-pointer" title="Shipper nhận hàng và bắt đầu đi giao">
                        <i class="fa-solid fa-motorcycle"></i> Bàn Giao Cho Shipper (Bắt Đầu Giao)
                    </button>
                    <!-- 3. Shipped -> Delivered (Giao hàng thành công -> Chuyển sang Đã giao) -->
                    <button id="btn-to-delivered" onclick="completeCurrentDelivery()" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl sm:rounded-2xl text-xs transition active:scale-95 flex items-center justify-center gap-1.5 shadow-sm cursor-pointer" title="Khách đã nhận hàng thành công">
                        <i class="fa-solid fa-circle-check"></i> Xác Nhận Đã Giao Thành Công
                    </button>
                    <!-- Nút Đóng -->
                    <button onclick="closeModal('modal-order-detail')" class="w-full sm:w-auto px-5 py-2.5 rounded-xl sm:rounded-2xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- ================= 7. MODAL CHỈNH SỬA THÔNG TIN NGƯỜI NHẬN ================= -->
    <div id="modal-edit-cust" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-gray-100 space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-extrabold text-gray-900 text-base">Chỉnh Sửa Thông Tin Người Nhận</h3>
                <button onclick="closeModal('modal-edit-cust')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form onsubmit="handleSaveCustInfo(event)" class="space-y-3 text-xs">
                <div><label class="block font-bold text-gray-700 mb-1">Tên người nhận</label><input type="text" id="edit-name" required class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3.5 py-2 text-sm"></div>
                <div><label class="block font-bold text-gray-700 mb-1">Số điện thoại nhận</label><input type="text" id="edit-phone" required class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3.5 py-2 text-sm font-mono"></div>
                <div><label class="block font-bold text-gray-700 mb-1">Địa chỉ chi tiết</label><textarea id="edit-address" rows="2" required class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl p-3 text-xs resize-none"></textarea></div>
                <div><label class="block font-bold text-gray-700 mb-1">Ghi chú</label><textarea id="edit-note" rows="2" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl p-3 text-xs resize-none"></textarea></div>
                <div class="pt-2"><button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 rounded-xl shadow-sm cursor-pointer">Lưu Thay Đổi</button></div>
            </form>
        </div>
    </div>

    <!-- ================= 8. PHIẾU ĐÓNG GÓI IN NHIỆT / A5 ================= -->
    <div id="printable-packing-slip" class="hidden">
        <div class="max-w-xl mx-auto p-6 font-sans text-xs text-gray-900 border border-gray-300">
            <div class="flex justify-between items-start border-b pb-4 mb-4">
                <div>
                    <h2 class="text-lg font-black text-emerald-800">NÔNG TRẠI ĐẤT LÀNH</h2>
                    <p class="text-[10px] text-gray-500 font-mono">Thung lũng Đạ Ròn, Đơn Dương, Lâm Đồng</p>
                    <p class="text-[10px] text-gray-500">Hotline: 0987.654.321</p>
                </div>
                <div class="text-right">
                    <h3 class="text-base font-black" id="ps-order-code">#ORD-...</h3>
                    <p class="text-[10px] text-gray-500 font-mono" id="ps-date">11/10/2026</p>
                    <span class="inline-block text-[10px] font-bold uppercase border px-1.5 py-0.5 rounded" id="ps-pay-method">COD</span>
                </div>
            </div>
            <div class="mb-4 space-y-1">
                <p><b>Người nhận:</b> <span id="ps-cust-name">--</span> (<span id="ps-cust-phone">--</span>)</p>
                <p><b>Địa chỉ:</b> <span id="ps-cust-address">--</span></p>
                <p><b>Ghi chú:</b> <i id="ps-cust-note">--</i></p>
            </div>
            <table class="w-full text-left border-collapse border border-gray-300 mb-4">
                <thead><tr class="bg-gray-100 text-[10px] font-bold"><th class="border border-gray-300 p-2">Sản phẩm</th><th class="border border-gray-300 p-2 text-center">SL</th><th class="border border-gray-300 p-2">Mã Lô FEFO</th><th class="border border-gray-300 p-2 text-center">Kiểm Tra</th></tr></thead>
                <tbody id="ps-items-tbody"></tbody>
            </table>
            <div class="flex justify-between items-end border-t pt-3">
                <div class="text-[10px] text-gray-400"><p>Quét mã QR trên tem nhãn để đối chiếu nhật ký nông trại.</p></div>
                <div class="text-center font-mono"><p class="font-bold">Ký tên đóng gói</p><div class="h-10"></div><p class="text-[10px] text-gray-400">KS. Hoàng Nam</p></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // KHO NÔNG SẢN THỰC TẾ (TỰ ĐỘNG CHỌN LÔ CẬN HẠN NHẤT THEO FEFO, KHÔNG CHO ĐỔI LÔ)
    const INVENTORY = {
        1: {
            id: 1,
            name: 'Cà Chua Cherry Đỏ Đà Lạt',
            price: 65000,
            batches: [
                { batch_id: 1, batch_code: 'TH-20261001-001', days_left: 2, stock: 20, plot: 'Luống 1 (Khu A)', harvest_date: '01/10/2026' },
                { batch_id: 2, batch_code: 'TH-20261008-002', days_left: 14, stock: 35, plot: 'Luống 1 (Khu A)', harvest_date: '08/10/2026' }
            ]
        },
        2: {
            id: 2,
            name: 'Cải Bó Xôi Hữu Cơ Spinach',
            price: 46000,
            batches: [
                { batch_id: 3, batch_code: 'TH-20261005-003', days_left: 11, stock: 38, plot: 'Luống 3 (Khu B)', harvest_date: '05/10/2026' },
                { batch_id: 5, batch_code: 'TH-20261009-001', days_left: 15, stock: 25, plot: 'Luống 3 (Khu B)', harvest_date: '09/10/2026' }
            ]
        },
        3: {
            id: 3,
            name: 'Dưa Leo Baby Nhật Bản',
            price: 55000,
            batches: [
                { batch_id: 4, batch_code: 'TH-20261007-004', days_left: 8, stock: 15, plot: 'Luống 5 (Khu C)', harvest_date: '07/10/2026' },
                { batch_id: 7, batch_code: 'TH-20261010-002', days_left: 16, stock: 45, plot: 'Luống 5 (Khu C)', harvest_date: '10/10/2026' }
            ]
        }
    };

    let newOrderCart = [];

    // DỮ LIỆU ĐƠN HÀNG MẪU CHUẨN ĐÚNG CSDL HIỆN TẠI (5 TRẠNG THÁI: pending, processing, shipped, delivered, cancelled)
    const DB = {
        orders: [
            {
                id: 1,
                user_id: 5,
                order_code: '#ORD-20261011-001',
                order_type: 'retail',
                recipient_name: 'Chị Mai Anh',
                recipient_phone: '0908.123.456',
                recipient_address: 'Tòa Landmark 4, Vinhomes Central Park, Bình Thạnh, TP.HCM',
                shipping_distance_km: 4.2,
                shipping_fee: 22000,
                points_used: 0,
                points_earned: 26,
                subtotal: 245000,
                total: 267000,
                payment_method: 'payos',
                payment_status: 'paid',
                shipping_provider: 'Đất Lành Express',
                tracking_code: null,
                status: 'pending',
                note: 'Giao trước 11h trưa giúp tôi nhé!',
                created_at: '2026-10-11 08:15:00',
                driver_name: null,
                driver_phone: null,
                driver_vehicle: null,
                items: [
                    { id: 101, order_id: 1, product_id: 1, batch_id: 1, product_name: 'Cà Chua Cherry Đỏ Đà Lạt', image: 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=200', price: 65000, quantity: 2, subtotal: 130000, batch_code: 'TH-20261001-001', harvest_date: '01/10/2026', days_left: 2, plot: 'Luống 1' },
                    { id: 102, order_id: 1, product_id: 2, batch_id: 3, product_name: 'Cải Bó Xôi Hữu Cơ Spinach', image: 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=200', price: 46000, quantity: 2.5, subtotal: 115000, batch_code: 'TH-20261005-003', harvest_date: '05/10/2026', days_left: 11, plot: 'Luống 3' }
                ],
                status_histories: [
                    { id: 1, order_id: 1, old_status: null, new_status: 'pending', changed_by: 5, note: 'Khách đặt đơn và thanh toán trực tuyến qua PayOS thành công', created_at: '2026-10-11 08:15:00' }
                ]
            },
            {
                id: 2,
                user_id: 8,
                order_code: '#ORD-20261011-002',
                order_type: 'subscription',
                recipient_name: 'Anh Quốc Huy',
                recipient_phone: '0919.987.654',
                recipient_address: '142 Nguyễn Thị Minh Khai, Phường 6, Quận 3, TP.HCM',
                shipping_distance_km: 6.8,
                shipping_fee: 0,
                points_used: 0,
                points_earned: 35,
                subtotal: 350000,
                total: 350000,
                payment_method: 'payos',
                payment_status: 'paid',
                shipping_provider: 'Đất Lành Express',
                tracking_code: null,
                status: 'processing',
                note: 'Gói rau tuần đợt 2: Giao buổi sáng giúp anh.',
                created_at: '2026-10-11 08:30:00',
                driver_name: null,
                driver_phone: null,
                driver_vehicle: null,
                items: [
                    { id: 201, order_id: 2, product_id: 1, batch_id: 2, product_name: 'Combo Rau Hữu Cơ Gia Đình (5kg)', image: 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=200', price: 350000, quantity: 1, subtotal: 350000, batch_code: 'TH-20261008-002', harvest_date: '08/10/2026', days_left: 14, plot: 'Luống 1' }
                ],
                status_histories: [
                    { id: 2, order_id: 2, old_status: null, new_status: 'pending', changed_by: 1, note: 'Hệ thống tự động tạo đơn từ Gói VIP định kỳ', created_at: '2026-10-11 08:30:00' },
                    { id: 3, order_id: 2, old_status: 'pending', new_status: 'processing', changed_by: 2, note: 'Nhân viên bán hàng đã xác nhận đơn & đóng gói xong. Đang chờ shipper tới lấy.', created_at: '2026-10-11 08:40:00' }
                ]
            },
            {
                id: 3,
                user_id: 12,
                order_code: '#ORD-20261011-003',
                order_type: 'retail',
                recipient_name: 'Cô Bích Liên',
                recipient_phone: '0933.456.789',
                recipient_address: 'Số 88 Đường 41, Phường Tân Quy, Quận 7, TP.HCM',
                shipping_distance_km: 8.5,
                shipping_fee: 35000,
                points_used: 0,
                points_earned: 55,
                subtotal: 520000,
                total: 555000,
                payment_method: 'cod',
                payment_status: 'pending',
                shipping_provider: 'Đất Lành Express',
                tracking_code: 'DL-SHIP-89234',
                status: 'shipped',
                note: 'Bác lớn tuổi, giao gọi trước 15 phút bác xuống sảnh lấy.',
                created_at: '2026-10-11 08:45:00',
                driver_name: 'Nguyễn Văn Bình',
                driver_phone: '0938.445.566',
                driver_vehicle: 'Yamaha Sirius • 59-X1 889.24',
                items: [
                    { id: 301, order_id: 3, product_id: 3, batch_id: 4, product_name: 'Dưa Leo Baby Nhật Bản', image: 'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?w=200', price: 55000, quantity: 3, subtotal: 165000, batch_code: 'TH-20261007-004', harvest_date: '07/10/2026', days_left: 8, plot: 'Luống 5' }
                ],
                status_histories: [
                    { id: 4, order_id: 3, old_status: null, new_status: 'pending', changed_by: 12, note: 'Khách đặt đơn giao tận nơi (COD)', created_at: '2026-10-11 08:45:00' },
                    { id: 5, order_id: 3, old_status: 'pending', new_status: 'processing', changed_by: 2, note: 'Nhân viên đã xác nhận đơn & đóng gói xong nông sản', created_at: '2026-10-11 09:00:00' },
                    { id: 6, order_id: 3, old_status: 'processing', new_status: 'shipped', changed_by: 2, note: 'Shipper đã lấy hàng đi giao (Mã vận đơn: DL-SHIP-89234)', created_at: '2026-10-11 09:20:00' }
                ]
            }
        ]
    };

    const LABEL = {
        order_type: {
            retail: { label: 'Đơn lẻ', class: 'bg-gray-100 text-gray-700 border-gray-200' },
            subscription: { label: 'Gói định kỳ', class: 'bg-brand-50 text-brand-800 border-brand-200' },
            compensation: { label: 'Đơn bù khiếu nại', class: 'bg-amber-50 text-amber-800 border-amber-200' }
        },
        status: {
            pending: { label: 'Chờ xác nhận', class: 'bg-amber-100 text-amber-800 border-amber-300' },
            processing: { label: 'Chờ lấy hàng', class: 'bg-amber-100 text-amber-800 border-amber-300' },
            shipped: { label: 'Đang giao', class: 'bg-blue-100 text-blue-800 border-blue-300' },
            delivered: { label: 'Đã giao', class: 'bg-emerald-100 text-emerald-800 border-emerald-300' },
            cancelled: { label: 'Đã hủy', class: 'bg-rose-100 text-rose-800 border-rose-300' }
        },
        payment_status: {
            paid: { label: 'Đã thanh toán', class: 'text-emerald-700 bg-emerald-50 border-emerald-200' },
            pending: { label: 'Chưa thu', class: 'text-slate-600 bg-slate-100 border-slate-200' },
            failed: { label: 'Thất bại', class: 'text-rose-700 bg-rose-50 border-rose-200' }
        }
    };

    let currentFilterStatus = 'all';
    let selectedOrderIds = new Set();
    let viewingOrderId = null;

    function openCreateOrderModal() {
        newOrderCart = [];
        document.getElementById('co-name').value = '';
        document.getElementById('co-phone').value = '';
        document.getElementById('co-address').value = '';
        document.getElementById('co-km').value = '5';
        document.getElementById('co-note').value = '';
        document.getElementById('co-order-type').value = 'retail';
        document.getElementById('co-payment-method').value = 'cod';
        
        onProductSelectChange();
        renderNewOrderCart();
        openModal('modal-create-order');
    }

    // TỰ ĐỘNG CHỌN LÔ CẬN HẠN NHẤT (FEFO) CHO SẢN PHẨM, KHÔNG CHO PHÉP ĐỔI
    function onProductSelectChange() {
        const prodId = document.getElementById('co-select-product').value;
        const p = INVENTORY[prodId];
        const badgeDiv = document.getElementById('co-assigned-batch-badge');

        if (p && p.batches && p.batches.length > 0) {
            // Sắp xếp các lô theo số ngày còn lại tăng dần (FEFO)
            const fefoBatch = [...p.batches].sort((a, b) => a.days_left - b.days_left)[0];
            badgeDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${fefoBatch.batch_code} (${fefoBatch.plot})</span>
                    <span class="text-[10px] text-rose-700">Còn ${fefoBatch.days_left}N (Tồn ${fefoBatch.stock}kg)</span>
                </div>
            `;
            badgeDiv.setAttribute('data-batch-id', fefoBatch.batch_id);
        }
    }

    function addItemToNewOrder() {
        const prodId = parseInt(document.getElementById('co-select-product').value);
        const p = INVENTORY[prodId];
        const badgeDiv = document.getElementById('co-assigned-batch-badge');
        const batchId = parseInt(badgeDiv.getAttribute('data-batch-id'));
        const qty = parseFloat(document.getElementById('co-input-qty').value);
        const chosenBatch = p.batches.find(b => b.batch_id === batchId);

        if (!qty || qty <= 0) {
            Swal.fire('Lỗi', 'Vui lòng nhập số lượng hợp lệ!', 'warning');
            return;
        }

        if (qty > chosenBatch.stock) {
            Swal.fire('Lỗi', `Số lượng tồn kho lô ${chosenBatch.batch_code} chỉ còn ${chosenBatch.stock}kg!`, 'error');
            return;
        }

        const exist = newOrderCart.find(it => it.product_id === p.id && it.batch_id === chosenBatch.batch_id);
        if (exist) {
            exist.quantity += qty;
            exist.subtotal = exist.quantity * exist.price;
        } else {
            newOrderCart.push({
                product_id: p.id,
                product_name: p.name,
                batch_id: chosenBatch.batch_id,
                batch_code: chosenBatch.batch_code,
                days_left: chosenBatch.days_left,
                plot: chosenBatch.plot,
                harvest_date: chosenBatch.harvest_date,
                price: p.price,
                quantity: qty,
                subtotal: qty * p.price,
                image: 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=200'
            });
        }

        renderNewOrderCart();
    }

    function removeItemFromNewOrder(idx) {
        newOrderCart.splice(idx, 1);
        renderNewOrderCart();
    }

    function calculateShippingFee() {
        const km = parseFloat(document.getElementById('co-km').value) || 0;
        const type = document.getElementById('co-order-type').value;
        document.getElementById('co-km-text').textContent = km;

        let fee = 0;
        if (type !== 'compensation') {
            fee = Math.round(km * 5000);
            if (fee < 15000) fee = 15000;
        }
        return fee;
    }

    function toggleOrderTypeFee() {
        renderNewOrderCart();
    }

    function renderNewOrderCart() {
        const tbody = document.getElementById('co-items-tbody');
        const type = document.getElementById('co-order-type').value;

        if (newOrderCart.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-6 text-center text-gray-400 italic">Chưa có nông sản nào được thêm vào đơn hàng.</td></tr>`;
        } else {
            tbody.innerHTML = newOrderCart.map((it, idx) => {
                const priceDisplay = type === 'compensation' ? 0 : it.price;
                const subDisplay = type === 'compensation' ? 0 : (it.quantity * priceDisplay);
                return `
                    <tr class="hover:bg-gray-50 bg-white">
                        <td class="py-2.5 px-4 font-bold text-gray-900">${it.product_name}</td>
                        <td class="py-2.5 px-4 text-center font-mono font-bold">${it.quantity} Kg</td>
                        <td class="py-2.5 px-4 text-right font-mono">${priceDisplay.toLocaleString('vi-VN')}đ</td>
                        <td class="py-2.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-brand-800 font-bold bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-lg text-xs">
                                    ${it.batch_code} (${it.plot})
                                </span>
                                <span class="text-[10px] font-mono text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">Còn ${it.days_left}N</span>
                            </div>
                        </td>
                        <td class="py-2.5 px-4 text-right font-mono font-bold text-gray-900">${subDisplay.toLocaleString('vi-VN')}đ</td>
                        <td class="py-2.5 px-4 text-center">
                            <button type="button" onclick="removeItemFromNewOrder(${idx})" class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition cursor-pointer" title="Xóa món">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        let subtotal = newOrderCart.reduce((acc, it) => acc + (type === 'compensation' ? 0 : it.subtotal), 0);
        let shipFee = calculateShippingFee();
        let total = subtotal + shipFee;

        document.getElementById('co-subtotal-text').textContent = `${subtotal.toLocaleString('vi-VN')}đ`;
        document.getElementById('co-shipfee-text').textContent = `${shipFee.toLocaleString('vi-VN')}đ`;
        document.getElementById('co-total-text').textContent = `${total.toLocaleString('vi-VN')}đ`;
    }

    function handleCreateOrderSubmit(e) {
        e.preventDefault();

        if (newOrderCart.length === 0) {
            Swal.fire('Lỗi', 'Vui lòng chọn ít nhất 1 loại nông sản cho đơn hàng!', 'warning');
            return;
        }

        const name = document.getElementById('co-name').value.trim();
        const phone = document.getElementById('co-phone').value.trim();
        const address = document.getElementById('co-address').value.trim();
        const km = parseFloat(document.getElementById('co-km').value) || 5;
        const orderType = document.getElementById('co-order-type').value;
        const paymentMethod = document.getElementById('co-payment-method').value;
        const note = document.getElementById('co-note').value.trim();

        const subtotal = newOrderCart.reduce((acc, it) => acc + (orderType === 'compensation' ? 0 : it.subtotal), 0);
        const shipFee = calculateShippingFee();
        const total = subtotal + shipFee;

        const newId = DB.orders.length > 0 ? Math.max(...DB.orders.map(o => o.id)) + 1 : 1;
        const now = new Date();
        const dateStr = now.toISOString().slice(0, 10).replace(/-/g, '');
        const newCode = `#ORD-${dateStr}-${String(newId).padStart(3, '0')}`;
        const nowTimeStr = now.toISOString().slice(0, 19).replace('T', ' ');

        const newOrder = {
            id: newId,
            user_id: 1,
            order_code: newCode,
            order_type: orderType,
            recipient_name: name,
            recipient_phone: phone,
            recipient_address: address,
            shipping_distance_km: km,
            shipping_fee: shipFee,
            points_used: 0,
            points_earned: Math.round(total / 10000),
            subtotal: subtotal,
            total: total,
            payment_method: paymentMethod,
            payment_status: paymentMethod === 'cod' ? 'pending' : 'paid',
            shipping_provider: 'Đất Lành Express',
            tracking_code: null,
            status: 'pending',
            note: note,
            created_at: nowTimeStr,
            driver_name: null,
            driver_phone: null,
            driver_vehicle: null,
            items: newOrderCart.map((it, idx) => ({
                id: 1000 + idx,
                order_id: newId,
                product_id: it.product_id,
                batch_id: it.batch_id,
                product_name: it.product_name,
                image: it.image,
                price: orderType === 'compensation' ? 0 : it.price,
                quantity: it.quantity,
                subtotal: orderType === 'compensation' ? 0 : it.subtotal,
                batch_code: it.batch_code,
                harvest_date: it.harvest_date || '01/10/2026',
                days_left: it.days_left,
                plot: it.plot
            })),
            status_histories: [
                {
                    id: 1,
                    order_id: newId,
                    old_status: null,
                    new_status: 'pending',
                    changed_by: 2,
                    note: `Nhân viên bán hàng tạo đơn qua Hotline (${orderType})`,
                    created_at: nowTimeStr
                }
            ]
        };

        // Trừ tồn kho lô đã chọn
        newOrderCart.forEach(it => {
            const prod = INVENTORY[it.product_id];
            if (prod) {
                const batch = prod.batches.find(b => b.batch_id === it.batch_id);
                if (batch) batch.stock -= it.quantity;
            }
        });

        DB.orders.unshift(newOrder);
        closeModal('modal-create-order');
        applyFilters();

        Swal.fire({
            icon: 'success',
            title: 'Tạo đơn hàng thành công!',
            html: `Mã đơn hàng: <b class="font-mono text-emerald-800">${newCode}</b><br>Đã gán Lô xuất kho theo FEFO và lưu vào hệ thống.`,
            confirmButtonColor: '#15803d'
        });
    }

    function applyFilters() {
        const search = document.getElementById('search-input').value.toLowerCase().trim();
        const filterType = document.getElementById('filter-type').value;
        const filterPayment = document.getElementById('filter-payment').value;

        updateTabCounts();

        let list = DB.orders.filter(order => {
            if (currentFilterStatus !== 'all' && order.status !== currentFilterStatus) return false;
            if (search) {
                const matchCode = order.order_code.toLowerCase().includes(search);
                const matchName = order.recipient_name.toLowerCase().includes(search);
                const matchPhone = order.recipient_phone.includes(search);
                const matchDriver = order.driver_name && order.driver_name.toLowerCase().includes(search);
                if (!matchCode && !matchName && !matchPhone && !matchDriver) return false;
            }
            if (filterType && order.order_type !== filterType) return false;
            if (filterPayment && order.payment_status !== filterPayment) return false;
            return true;
        });

        renderTable(list);
        updateBulkActionBar();
    }

    function updateTabCounts() {
        const counts = {
            all: DB.orders.length,
            pending: DB.orders.filter(o => o.status === 'pending').length,
            processing: DB.orders.filter(o => o.status === 'processing').length,
            shipped: DB.orders.filter(o => o.status === 'shipped').length,
            delivered: DB.orders.filter(o => o.status === 'delivered').length,
            cancelled: DB.orders.filter(o => o.status === 'cancelled').length
        };

        document.getElementById('count-all').textContent = counts.all;
        document.getElementById('count-pending').textContent = counts.pending;
        document.getElementById('count-processing').textContent = counts.processing;
        document.getElementById('count-shipped').textContent = counts.shipped;
        document.getElementById('count-delivered').textContent = counts.delivered;
        document.getElementById('count-cancelled').textContent = counts.cancelled;
    }

    function renderTable(list) {
        const tbody = document.getElementById('order-table-body');
        const emptyState = document.getElementById('empty-state');

        if (list.length === 0) {
            tbody.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }
        emptyState.classList.add('hidden');

        tbody.innerHTML = list.map(o => {
            const isChecked = selectedOrderIds.has(o.id);
            
            const typeCfg = LABEL.order_type[o.order_type] || { label: o.order_type, class: 'bg-gray-100 text-gray-700 border-gray-200' };
            const typeBadge = `<span class="text-[11px] font-bold border px-2 py-0.5 rounded-md ${typeCfg.class}">${typeCfg.label}</span>`;

            const statusCfg = LABEL.status[o.status] || { label: o.status, class: 'bg-gray-100 text-gray-700 border-gray-200' };
            const statusBadge = `<span class="text-[10px] sm:text-xs font-bold border px-2.5 py-1 rounded-full ${statusCfg.class}">${statusCfg.label}</span>`;

            const payCfg = LABEL.payment_status[o.payment_status] || { label: o.payment_status, class: 'text-gray-700 bg-gray-100 border-gray-200' };

            let shippingInfo = '';
            if (o.status === 'pending') {
                shippingInfo = `<span class="text-xs text-gray-400 italic">Chưa điều phối shipper</span>`;
            } else if (o.status === 'processing') {
                shippingInfo = `
                    <div>
                        <span class="text-xs font-semibold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200 inline-flex items-center gap-1">
                            <i class="fa-solid fa-boxes-packing text-[10px]"></i> Chờ shipper lấy
                        </span>
                        <p class="text-[11px] text-gray-500 font-mono mt-0.5">${o.shipping_distance_km}km • ${o.shipping_fee.toLocaleString('vi-VN')}đ</p>
                    </div>
                `;
            } else if (o.status === 'shipped') {
                shippingInfo = `
                    <div>
                        <span class="text-xs font-bold text-blue-700 flex items-center gap-1">
                            <i class="fa-solid fa-motorcycle text-blue-600"></i> ${o.driver_name || 'Nguyễn Văn Bình'}
                        </span>
                        <p class="text-[11px] text-gray-500 font-mono">${o.driver_phone || '0938.445.566'} • ${o.shipping_distance_km}km</p>
                    </div>
                `;
            } else if (o.status === 'delivered') {
                shippingInfo = `
                    <div>
                        <span class="text-xs font-bold text-emerald-700 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Đã giao (${o.shipping_distance_km}km)
                        </span>
                        <p class="text-[11px] text-gray-500 font-mono">Shipper ${o.driver_name || 'Nguyễn Văn Bình'}</p>
                    </div>
                `;
            } else {
                shippingInfo = `<span class="text-xs text-rose-500 italic">Đơn đã hủy</span>`;
            }

            let actionButtons = `
                <button onclick="openOrderDetailModal(${o.id})" class="p-1.5 sm:p-2 rounded-xl bg-gray-50 text-gray-600 hover:text-brand-700 hover:bg-brand-50 transition border border-gray-200 cursor-pointer" title="Xem chi tiết đơn">
                    <i class="fa-regular fa-eye text-xs sm:text-sm"></i>
                </button>
                <button onclick="printOrderPackingSlip(${o.id})" class="p-1.5 sm:p-2 rounded-xl bg-gray-50 text-gray-600 hover:text-brand-700 hover:bg-brand-50 transition border border-gray-200 cursor-pointer" title="In phiếu đóng gói">
                    <i class="fa-solid fa-print text-xs sm:text-sm"></i>
                </button>
            `;

            // CÁC NÚT THAO TÁC THEO ĐÚNG TIẾN TRÌNH CÁC TAB
            if (o.status === 'pending') {
                actionButtons = `
                    <button onclick="confirmSingleOrder(${o.id})" class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs px-2.5 sm:px-3 py-1.5 rounded-xl transition shadow-xs active:scale-95 inline-flex items-center gap-1.5 cursor-pointer" title="Xác nhận & Nông sản đã chuẩn bị xong (Chuyển sang Chờ lấy hàng)">
                        <i class="fa-solid fa-check"></i> Xác nhận
                    </button>
                ` + actionButtons;
            } else if (o.status === 'processing') {
                actionButtons = `
                    <button onclick="dispatchSingleOrder(${o.id})" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-2.5 sm:px-3 py-1.5 rounded-xl transition shadow-xs active:scale-95 inline-flex items-center gap-1.5 cursor-pointer" title="Shipper đến lấy hàng và bắt đầu đi giao (Chuyển sang Đang giao)">
                        <i class="fa-solid fa-motorcycle"></i> Bàn giao Shipper
                    </button>
                ` + actionButtons;
            } else if (o.status === 'shipped') {
                actionButtons = `
                    <button onclick="completeSingleOrder(${o.id})" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-2.5 sm:px-3 py-1.5 rounded-xl transition shadow-xs active:scale-95 inline-flex items-center gap-1.5 cursor-pointer" title="Khách đã nhận hàng thành công (Chuyển sang Đã giao)">
                        <i class="fa-solid fa-circle-check"></i> Đã giao
                    </button>
                ` + actionButtons;
            }

            return `
                <tr class="hover:bg-brand-50/40 transition-colors bg-white border-b border-gray-100">
                    <td class="py-3.5 sm:py-4 px-3 sm:px-4 pl-4 sm:pl-6 text-center">
                        <input type="checkbox" value="${o.id}" ${isChecked ? 'checked' : ''} onchange="toggleSelectOrder(${o.id}, this.checked)" class="w-4 h-4 rounded text-brand-600 border-gray-300 focus:ring-brand-500 cursor-pointer">
                    </td>
                    <td class="py-3.5 sm:py-4 px-3 sm:px-4">
                        <button onclick="openOrderDetailModal(${o.id})" class="font-mono font-bold text-brand-800 hover:underline cursor-pointer text-xs sm:text-sm">
                            ${o.order_code}
                        </button>
                    </td>
                    <td class="py-3.5 sm:py-4 px-3 sm:px-4">${typeBadge}</td>
                    <td class="py-3.5 sm:py-4 px-3 sm:px-4">
                        <p class="font-bold text-gray-900 text-xs sm:text-sm">${o.recipient_name}</p>
                        <p class="text-xs text-gray-500 font-mono">${o.recipient_phone}</p>
                        <p class="text-xs text-gray-400 truncate max-w-xs" title="${o.recipient_address}">
                            <i class="fa-solid fa-location-dot text-brand-600 text-[10px] mr-1"></i>${o.recipient_address}
                        </p>
                    </td>
                    <td class="py-3.5 sm:py-4 px-3 sm:px-4 font-mono text-xs text-gray-600">${o.created_at}</td>
                    <td class="py-3.5 sm:py-4 px-3 sm:px-4 text-right">
                        <p class="font-mono font-extrabold text-gray-900 text-xs sm:text-sm">${o.total.toLocaleString('vi-VN')}đ</p>
                        <span class="text-[10px] font-bold px-2 py-0.2 rounded border uppercase font-mono ${payCfg.class}">
                            ${o.payment_method.toUpperCase()} • ${payCfg.label}
                        </span>
                    </td>
                    <td class="py-3.5 sm:py-4 px-3 sm:px-4">${shippingInfo}</td>
                    <td class="py-3.5 sm:py-4 px-3 sm:px-4 text-center">${statusBadge}</td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-6 text-right space-x-1">${actionButtons}</td>
                </tr>
            `;
        }).join('');
    }

    function setFilterStatus(status) {
        currentFilterStatus = status;
        ['all', 'pending', 'processing', 'shipped', 'delivered', 'cancelled'].forEach(s => {
            const btn = document.getElementById(`tab-${s}`);
            if (s === status) btn.className = "pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2";
            else btn.className = "pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2";
        });
        applyFilters();
    }

    function resetFilters() {
        document.getElementById('search-input').value = '';
        document.getElementById('filter-type').value = '';
        document.getElementById('filter-payment').value = '';
        setFilterStatus('all');
    }

    function toggleSelectOrder(id, isChecked) {
        if (isChecked) selectedOrderIds.add(id);
        else selectedOrderIds.delete(id);
        updateBulkActionBar();
    }

    function toggleSelectAll(masterCheckbox) {
        const list = DB.orders.filter(order => currentFilterStatus === 'all' || order.status === currentFilterStatus);
        if (masterCheckbox.checked) list.forEach(o => selectedOrderIds.add(o.id));
        else selectedOrderIds.clear();
        applyFilters();
    }

    function deselectAll() {
        selectedOrderIds.clear();
        document.getElementById('check-all').checked = false;
        applyFilters();
    }

    function updateBulkActionBar() {
        const bar = document.getElementById('bulk-actions-bar');
        const count = selectedOrderIds.size;
        document.getElementById('bulk-selected-count').textContent = count;
        if (count > 0) bar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
        else {
            bar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
            document.getElementById('check-all').checked = false;
        }
    }

    // 1. XÁC NHẬN ĐƠN (Pending -> Processing: Đã chuẩn bị xong hàng, chuyển sang Chờ lấy hàng)
    function confirmSingleOrder(id) {
        const o = DB.orders.find(x => x.id === id);
        if (!o || o.status !== 'pending') return;

        Swal.fire({
            title: 'Xác nhận đơn hàng?',
            html: `Xác nhận đơn <b class="font-mono text-brand-800">${o.order_code}</b>:<br><span class="text-xs text-gray-600 mt-1 block">Nông sản đã được chuẩn bị xong, đóng gói hoàn tất và chuyển sang trạng thái <b>Chờ lấy hàng</b> (chờ shipper tới lấy)?</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#15803d',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Đồng ý, Đã chuẩn bị xong',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                o.status = 'processing';
                o.status_histories.push({
                    id: o.status_histories.length + 1,
                    order_id: o.id,
                    old_status: 'pending',
                    new_status: 'processing',
                    changed_by: 2,
                    note: 'Đã xác nhận đơn. Nông sản đã chuẩn bị xong, đóng gói hoàn tất. Chờ shipper tới lấy.',
                    created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
                });

                applyFilters();
                if (viewingOrderId === id) openOrderDetailModal(id);

                Swal.fire({
                    icon: 'success',
                    title: 'Đã chuẩn bị xong hàng!',
                    html: `Đơn <b class="font-mono text-brand-800">${o.order_code}</b> đã chuyển sang tab <b>Chờ lấy hàng</b>.<br><span class="text-xs text-amber-700 font-semibold">(Đang chờ shipper tới lấy hàng)</span>`,
                    confirmButtonColor: '#15803d',
                    timer: 1600,
                    showConfirmButton: false
                });
            }
        });
    }

    // 2. BÀN GIAO SHIPPER (Processing -> Shipped: Shipper tới lấy hàng đi giao -> Đang giao)
    function dispatchSingleOrder(id) {
        const o = DB.orders.find(x => x.id === id);
        if (!o || o.status !== 'processing') return;

        Swal.fire({
            title: 'Bàn giao cho Shipper?',
            html: `Bàn giao đơn <b class="font-mono text-brand-800">${o.order_code}</b> cho Shipper nhận hàng và bắt đầu đi giao?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Bàn giao ngay',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                o.status = 'shipped';
                o.driver_name = 'Nguyễn Văn Bình';
                o.driver_phone = '0938.445.566';
                o.driver_vehicle = 'Xe máy thùng lạnh Đất Lành • 59-X1 889.24';

                o.status_histories.push({
                    id: o.status_histories.length + 1,
                    order_id: o.id,
                    old_status: 'processing',
                    new_status: 'shipped',
                    changed_by: 2,
                    note: `Shipper Nguyễn Văn Bình (0938.445.566) đã nhận hàng từ kho, đang đi giao đến địa chỉ khách hàng.`,
                    created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
                });

                applyFilters();
                if (viewingOrderId === id) openOrderDetailModal(id);

                Swal.fire({
                    icon: 'success',
                    title: 'Bàn giao Shipper thành công!',
                    html: `Tài xế <b>Nguyễn Văn Bình</b> (0938.445.566) đã nhận hàng và đang trên đường đi giao.<br><span class="text-xs text-blue-600 font-semibold">(Đơn đã chuyển sang tab Đang giao)</span>`,
                    confirmButtonColor: '#15803d'
                });
            }
        });
    }

    // 3. GIAO THÀNH CÔNG (Shipped -> Delivered: Khách đã nhận hàng thành công -> Đã giao)
    function completeSingleOrder(id) {
        const o = DB.orders.find(x => x.id === id);
        if (!o || o.status !== 'shipped') return;

        Swal.fire({
            title: 'Xác nhận giao thành công?',
            html: `Khách hàng đã nhận được đầy đủ hàng đơn <b class="font-mono text-brand-800">${o.order_code}</b>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Đã giao thành công',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                o.status = 'delivered';
                // Nếu là COD thì thu tiền thành công
                if (o.payment_method === 'cod') {
                    o.payment_status = 'paid';
                }

                o.status_histories.push({
                    id: o.status_histories.length + 1,
                    order_id: o.id,
                    old_status: 'shipped',
                    new_status: 'delivered',
                    changed_by: 2,
                    note: 'Giao hàng thành công. Khách hàng đã nhận nông sản tươi sạch.',
                    created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
                });

                applyFilters();
                if (viewingOrderId === id) openOrderDetailModal(id);

                Swal.fire({
                    icon: 'success',
                    title: 'Đã giao hàng thành công!',
                    html: `Đơn <b class="font-mono text-brand-800">${o.order_code}</b> đã chuyển sang tab <b>Đã giao</b>.<br><span class="text-xs text-emerald-600 font-semibold">(Đã tự động quyết toán tiền COD)</span>`,
                    confirmButtonColor: '#15803d',
                    timer: 1800,
                    showConfirmButton: false
                });
            }
        });
    }

    // CÁC THAO TÁC HÀNG LOẠT
    function bulkApproveOrders() {
        const targetIds = Array.from(selectedOrderIds);
        let count = 0;
        targetIds.forEach(id => {
            const o = DB.orders.find(x => x.id === id);
            if (o && o.status === 'pending') {
                o.status = 'processing';
                o.status_histories.push({ id: o.status_histories.length + 1, order_id: o.id, old_status: 'pending', new_status: 'processing', changed_by: 2, note: 'Xác nhận đơn hàng loạt: Nông sản chuẩn bị xong, chờ shipper lấy.', created_at: new Date().toISOString().slice(0, 19).replace('T', ' ') });
                count++;
            }
        });
        deselectAll();
        applyFilters();
        Swal.fire({ icon: 'success', title: 'Đã xác nhận hàng loạt!', html: `Đã chuẩn bị xong và chuyển <b>${count}</b> đơn sang tab <b>Chờ lấy hàng</b>.`, confirmButtonColor: '#15803d' });
    }

    function bulkDispatchOrders() {
        const targetIds = Array.from(selectedOrderIds);
        let count = 0;
        targetIds.forEach(id => {
            const o = DB.orders.find(x => x.id === id);
            if (o && o.status === 'processing') {
                o.status = 'shipped';
                o.driver_name = 'Nguyễn Văn Bình';
                o.driver_phone = '0938.445.566';
                o.driver_vehicle = 'Xe máy thùng lạnh Đất Lành • 59-X1 889.24';
                o.status_histories.push({ id: o.status_histories.length + 1, order_id: o.id, old_status: 'processing', new_status: 'shipped', changed_by: 2, note: `Shipper Nguyễn Văn Bình đã nhận hàng từ kho Đất Lành và đang đi giao`, created_at: new Date().toISOString().slice(0, 19).replace('T', ' ') });
                count++;
            }
        });
        deselectAll();
        applyFilters();
        Swal.fire({ icon: 'success', title: 'Đã bàn giao shipper loạt!', html: `Đã bàn giao cho Shipper <b>${count}</b> đơn hàng (Chuyển sang tab <b>Đang giao</b>).`, confirmButtonColor: '#15803d' });
    }

    function bulkCompleteOrders() {
        const targetIds = Array.from(selectedOrderIds);
        let count = 0;
        targetIds.forEach(id => {
            const o = DB.orders.find(x => x.id === id);
            if (o && o.status === 'shipped') {
                o.status = 'delivered';
                if (o.payment_method === 'cod') o.payment_status = 'paid';
                o.status_histories.push({ id: o.status_histories.length + 1, order_id: o.id, old_status: 'shipped', new_status: 'delivered', changed_by: 2, note: 'Khách đã nhận hàng thành công.', created_at: new Date().toISOString().slice(0, 19).replace('T', ' ') });
                count++;
            }
        });
        deselectAll();
        applyFilters();
        Swal.fire({ icon: 'success', title: 'Đã giao loạt!', html: `Đã đánh dấu giao thành công cho <b>${count}</b> đơn hàng (Chuyển sang tab <b>Đã giao</b>).`, confirmButtonColor: '#15803d' });
    }

    function bulkPrintPackingSlips() {
        const targetIds = Array.from(selectedOrderIds);
        if (targetIds.length === 0) return;
        printOrderPackingSlip(targetIds[0]);
    }

    function bulkCancelOrders() {
        const targetIds = Array.from(selectedOrderIds);
        Swal.fire({
            title: `Hủy ${targetIds.length} đơn hàng đã chọn?`,
            input: 'textarea',
            inputPlaceholder: 'Nhập lý do hủy đơn...',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận hủy',
            cancelButtonText: 'Bỏ qua',
            confirmButtonColor: '#dc2626',
            preConfirm: (val) => { if (!val) Swal.showValidationMessage('Vui lòng nhập lý do!'); return val; }
        }).then(res => {
            if (res.isConfirmed) {
                targetIds.forEach(id => {
                    const o = DB.orders.find(x => x.id === id);
                    if (o && o.status !== 'delivered' && o.status !== 'cancelled') {
                        o.status = 'cancelled';
                        o.cancel_reason = res.value;
                    }
                });
                deselectAll();
                applyFilters();
                Swal.fire({ icon: 'success', title: 'Đã hủy các đơn đã chọn', confirmButtonColor: '#15803d' });
            }
        });
    }

    function openOrderDetailModal(orderId) {
        viewingOrderId = orderId;
        const o = DB.orders.find(x => x.id === orderId);
        if (!o) return;

        document.getElementById('mod-order-code').textContent = o.order_code;
        document.getElementById('mod-created-at').textContent = o.created_at;

        const typeCfg = LABEL.order_type[o.order_type] || { label: o.order_type, class: 'bg-gray-100 text-gray-700' };
        const typeBadge = document.getElementById('mod-type-badge');
        typeBadge.className = `text-xs font-bold px-2.5 py-0.5 rounded-full border ${typeCfg.class}`;
        typeBadge.textContent = typeCfg.label;

        const statusCfg = LABEL.status[o.status] || { label: o.status, class: 'bg-gray-100 text-gray-700' };
        const statusBadge = document.getElementById('mod-status-badge');
        statusBadge.className = `text-xs font-bold px-2.5 py-0.5 rounded-full border ${statusCfg.class}`;
        statusBadge.textContent = statusCfg.label;

        document.getElementById('mod-cust-name').textContent = o.recipient_name;
        document.getElementById('mod-cust-phone').textContent = o.recipient_phone;
        document.getElementById('mod-cust-address').textContent = o.recipient_address;
        document.getElementById('mod-shipping-calc').textContent = `${o.shipping_distance_km} km (Phí ship: ${o.shipping_fee.toLocaleString('vi-VN')}đ)`;
        
        const payCfg = LABEL.payment_status[o.payment_status] || { label: o.payment_status };
        document.getElementById('mod-payment-badge').textContent = `${o.payment_method.toUpperCase()} • ${payCfg.label.toUpperCase()}`;
        document.getElementById('mod-cust-note').textContent = o.note ? `"${o.note}"` : "Không có ghi chú thêm";

        const courierBox = document.getElementById('mod-courier-assigned-box');
        if (o.status === 'shipped') {
            courierBox.classList.remove('hidden');
            document.getElementById('mod-courier-driver-name').textContent = 'Shipper: Nguyễn Văn Bình';
            document.getElementById('mod-courier-driver-phone').textContent = 'SĐT: 0938.445.566 • Xe máy thùng lạnh 59-X1 889.24';
            document.getElementById('mod-courier-status-title').textContent = 'Trạng thái giao hàng';
            document.getElementById('mod-courier-status-desc').textContent = `Đang trên đường giao (${o.shipping_distance_km} km)`;
        } else if (o.status === 'delivered') {
            courierBox.classList.remove('hidden');
            document.getElementById('mod-courier-driver-name').textContent = 'Shipper: Nguyễn Văn Bình';
            document.getElementById('mod-courier-driver-phone').textContent = 'SĐT: 0938.445.566 • Xe máy thùng lạnh 59-X1 889.24';
            document.getElementById('mod-courier-status-title').textContent = 'Trạng thái giao hàng';
            document.getElementById('mod-courier-status-desc').textContent = 'Đã giao thành công tận tay người nhận';
        } else if (o.status === 'processing') {
            courierBox.classList.remove('hidden');
            document.getElementById('mod-courier-driver-name').textContent = 'Shipper phụ trách: Nguyễn Văn Bình';
            document.getElementById('mod-courier-driver-phone').textContent = 'SĐT: 0938.445.566 • Đội xe nội bộ Đất Lành';
            document.getElementById('mod-courier-status-title').textContent = 'Tiến độ kho';
            document.getElementById('mod-courier-status-desc').textContent = 'Đang đóng gói, sẵn sàng bàn giao cho Shipper';
        } else {
            courierBox.classList.add('hidden');
        }

        // BẢNG NÔNG SẢN VÀ LÔ FEFO (KHÔNG CÓ CỘT ĐỔI LÔ)
        const fefoTbody = document.getElementById('mod-fefo-tbody');
        fefoTbody.innerHTML = (o.items || []).map(item => `
            <tr class="hover:bg-gray-50/80 transition bg-white border-b border-gray-100">
                <td class="py-3.5 px-4 flex items-center gap-3">
                    <img src="${item.image}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 shadow-2xs shrink-0">
                    <div>
                        <p class="font-bold text-gray-900">${item.product_name}</p>
                        <p class="text-[11px] text-gray-400">Nông sản sạch</p>
                    </div>
                </td>
                <td class="py-3.5 px-4 text-center font-mono font-extrabold text-sm text-gray-900">${item.quantity} Kg</td>
                <td class="py-3.5 px-4 text-right font-mono">
                    <span class="text-gray-400 text-[11px] block">${item.price.toLocaleString('vi-VN')}đ</span>
                    <b class="text-gray-900 text-sm">${item.subtotal.toLocaleString('vi-VN')}đ</b>
                </td>
                <td class="py-3.5 px-4">
                    <div class="space-y-0.5">
                        <span class="inline-flex items-center gap-1.5 font-mono font-bold text-brand-800 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-lg text-xs">
                            <i class="fa-solid fa-barcode text-brand-600"></i> Lô: ${item.batch_code}
                        </span>
                        <p class="text-[11px] text-gray-500">Thu hái: ${item.harvest_date} • ${item.plot}</p>
                        <span class="inline-block text-[10px] font-bold font-mono px-2 py-0.2 rounded border ${item.days_left <= 2 ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-200'}">
                            Hạn dùng còn ${item.days_left} ngày
                        </span>
                    </div>
                </td>
            </tr>
        `).join('');

        document.getElementById('mod-subtotal').textContent = `${o.subtotal.toLocaleString('vi-VN')}đ`;
        document.getElementById('mod-ship-fee').textContent = `${o.shipping_fee.toLocaleString('vi-VN')}đ`;
        document.getElementById('mod-total-final').textContent = `${o.total.toLocaleString('vi-VN')}đ`;

        const timelineContainer = document.getElementById('mod-timeline-container');
        timelineContainer.innerHTML = o.status_histories.map((t, idx) => {
            const oldText = t.old_status ? (LABEL.status[t.old_status]?.label || t.old_status) : null;
            const newText = LABEL.status[t.new_status]?.label || t.new_status;
            return `
                <div class="relative pl-6 pb-3 text-xs ${idx === o.status_histories.length - 1 ? '' : 'border-l-2 border-brand-200 ml-2'}">
                    <div class="absolute -left-[5px] top-0 w-3 h-3 rounded-full bg-brand-600 border-2 border-white shadow-2xs"></div>
                    <p class="font-bold text-gray-900">${oldText ? `${oldText} &rarr; ${newText}` : newText}</p>
                    <p class="text-gray-500 font-mono text-[11px]">${t.created_at}</p>
                    ${t.note ? `<p class="text-gray-600 italic mt-0.5">${t.note}</p>` : ''}
                </div>
            `;
        }).join('');

        // HIỂN THỊ NÚT GIẢ LẬP GIAO HÀNG TƯƠNG ỨNG THEO TRẠNG THÁI HIỆN TẠI
        const btnPack = document.getElementById('btn-to-pack');
        const btnDispatch = document.getElementById('btn-to-dispatch');
        const btnDelivered = document.getElementById('btn-to-delivered');
        const btnCancel = document.getElementById('btn-cancel-order');

        btnPack.classList.add('hidden');
        btnDispatch.classList.add('hidden');
        btnDelivered.classList.add('hidden');
        btnCancel.classList.add('hidden');

        if (o.status === 'pending') {
            btnPack.classList.remove('hidden');
            btnCancel.classList.remove('hidden');
        } else if (o.status === 'processing') {
            btnDispatch.classList.remove('hidden');
            btnCancel.classList.remove('hidden');
        } else if (o.status === 'shipped') {
            btnDelivered.classList.remove('hidden');
        }

        openModal('modal-order-detail');
    }

    function approveCurrentOrder() {
        if (!viewingOrderId) return;
        confirmSingleOrder(viewingOrderId);
    }

    function dispatchCurrentOrder() {
        if (!viewingOrderId) return;
        dispatchSingleOrder(viewingOrderId);
    }

    function completeCurrentDelivery() {
        if (!viewingOrderId) return;
        completeSingleOrder(viewingOrderId);
    }

    function cancelCurrentOrder() {
        if (!viewingOrderId) return;
        const o = DB.orders.find(x => x.id === viewingOrderId);

        Swal.fire({
            title: `Hủy đơn ${o.order_code}?`,
            input: 'textarea',
            inputPlaceholder: 'Nhập lý do hủy đơn...',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận hủy',
            cancelButtonText: 'Đóng',
            confirmButtonColor: '#dc2626',
            preConfirm: (reason) => { if (!reason) Swal.showValidationMessage('Vui lòng nhập lý do!'); return reason; }
        }).then(res => {
            if (res.isConfirmed) {
                o.status = 'cancelled';
                o.cancel_reason = res.value;
                o.status_histories.push({
                    id: o.status_histories.length + 1,
                    order_id: o.id,
                    old_status: o.status,
                    new_status: 'cancelled',
                    changed_by: 2,
                    note: `Nhân viên hủy đơn: ${res.value}`,
                    created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
                });

                applyFilters();
                closeModal('modal-order-detail');
                Swal.fire({ icon: 'success', title: 'Đã hủy đơn hàng', text: 'Số lượng sản phẩm đã hoàn lại vào các Lô FEFO tương ứng.', confirmButtonColor: '#15803d' });
            }
        });
    }

    function printCurrentPackingSlip() {
        if (!viewingOrderId) return;
        printOrderPackingSlip(viewingOrderId);
    }

    function printOrderPackingSlip(orderId) {
        const o = DB.orders.find(x => x.id === orderId);
        if (!o) return;

        document.getElementById('ps-order-code').textContent = o.order_code;
        document.getElementById('ps-date').textContent = o.created_at;
        document.getElementById('ps-pay-method').textContent = `${o.payment_method.toUpperCase()} (${LABEL.payment_status[o.payment_status]?.label.toUpperCase() || ''})`;
        document.getElementById('ps-cust-name').textContent = o.recipient_name;
        document.getElementById('ps-cust-phone').textContent = o.recipient_phone;
        document.getElementById('ps-cust-address').textContent = o.recipient_address;
        document.getElementById('ps-cust-note').textContent = o.note || 'Không có';

        const tbody = document.getElementById('ps-items-tbody');
        tbody.innerHTML = (o.items || []).map(item => `
            <tr>
                <td class="border border-gray-300 p-2 font-bold">${item.product_name}</td>
                <td class="border border-gray-300 p-2 text-center font-mono font-bold">${item.quantity} Kg</td>
                <td class="border border-gray-300 p-2 font-mono"><b>${item.batch_code}</b></td>
                <td class="border border-gray-300 p-2 text-center font-mono text-gray-400">[  ] ĐỦ</td>
            </tr>
        `).join('');

        window.print();
    }

    function openEditCustomerModal() {
        if (!viewingOrderId) return;
        const o = DB.orders.find(x => x.id === viewingOrderId);
        if (!o) return;

        document.getElementById('edit-name').value = o.recipient_name;
        document.getElementById('edit-phone').value = o.recipient_phone;
        document.getElementById('edit-address').value = o.recipient_address;
        document.getElementById('edit-note').value = o.note || '';

        openModal('modal-edit-cust');
    }

    function handleSaveCustInfo(e) {
        e.preventDefault();
        if (!viewingOrderId) return;
        const o = DB.orders.find(x => x.id === viewingOrderId);

        o.recipient_name = document.getElementById('edit-name').value.trim();
        o.recipient_phone = document.getElementById('edit-phone').value.trim();
        o.recipient_address = document.getElementById('edit-address').value.trim();
        o.note = document.getElementById('edit-note').value.trim();

        closeModal('modal-edit-cust');
        openOrderDetailModal(viewingOrderId);
        applyFilters();

        Swal.fire({ icon: 'success', title: 'Đã cập nhật thông tin!', confirmButtonColor: '#15803d', timer: 1500, showConfirmButton: false });
    }

    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

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
