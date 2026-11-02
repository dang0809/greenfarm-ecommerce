<div>
    <!-- Header -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 relative z-10 shrink-0 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5 truncate">
                    <span class="text-brand-700">Kho & Điều Phối</span>
                    <span>/</span>
                    <span class="truncate">Lô Hàng Nông Sản (FEFO)</span>
                </div>
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">Quản Lý Lô Nông Sản Thương Mại</h1>
            </div>
        </div>
        
        <div class="flex items-center gap-2 shrink-0">
            <div class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-600"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>11/10/2026</span>
            </div>
        </div>
    </header>

    <!-- Main -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative z-10 space-y-5 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12 h-[calc(100dvh-5rem)]">
        
        <!-- THỐNG KÊ KHO -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-500">Tổng số lô hàng</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-gray-50 text-gray-700 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-gray-900" id="kpi-total">0</div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1">Lô đã nhập kho</p>
            </div>

            <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-brand-700">Lô đang bán (Tồn)</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-brand-700" id="kpi-active">0</div>
                <p class="text-[10px] sm:text-[11px] text-brand-600 font-medium mt-1">Sẵn sàng xuất bán</p>
            </div>

            <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-amber-600">Lô cận hạn (&le; 3N)</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-amber-700" id="kpi-near-expiry">0</div>
                <p class="text-[10px] sm:text-[11px] text-amber-700 font-medium mt-1">Ưu tiên đẩy bán FEFO</p>
            </div>

            <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-rose-100 shadow-soft">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-rose-600">Lô đã hết hạn</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-rose-600" id="kpi-expired">0</div>
                <p class="text-[10px] sm:text-[11px] text-rose-500 font-medium mt-1">Cần lập phiếu hủy kho</p>
            </div>

            <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-rose-700">Hao hụt xuất hủy</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-rose-700" id="kpi-spoiled">0 kg</div>
                <p class="text-[10px] sm:text-[11px] text-rose-500 font-medium mt-1">Ghi log xuất hủy</p>
            </div>
        </div>

        <!-- BẢNG LÔ NÔNG SẢN -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden space-y-4">
            <div class="p-3.5 sm:p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4 bg-gray-50/60">
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 flex-1">
                    <div class="relative w-full sm:w-72 md:w-80">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                        <input type="text" id="filter-search" oninput="renderTable()" placeholder="Tìm mã lô, tên nông sản, luống..." class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white font-medium transition-all">
                    </div>

                    <select id="filter-product" onchange="renderTable()" class="bg-white border border-gray-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold text-gray-700 outline-none focus:border-brand-500 transition-all cursor-pointer">
                        <option value="">-- Tất cả nông sản --</option>
                        <option value="1">Cà Chua Cherry Đỏ Đà Lạt</option>
                        <option value="2">Cải Bó Xôi Hữu Cơ (Spinach)</option>
                        <option value="3">Dưa Leo Baby Nhật Bản</option>
                    </select>

                    <select id="filter-status" onchange="renderTable()" class="bg-white border border-gray-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold text-gray-700 outline-none focus:border-brand-500 transition-all cursor-pointer">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="active">Đang bán tốt</option>
                        <option value="near_expiry">Cận hạn FEFO (&le; 3 ngày)</option>
                        <option value="expired">Đã hết hạn</option>
                        <option value="out_of_stock">Đã hết hàng</option>
                    </select>
                </div>

                <button onclick="resetFilters()" class="self-end md:self-auto p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 transition-colors shadow-xs cursor-pointer" title="Đặt lại bộ lọc">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>

            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100 whitespace-nowrap">
                            <th class="py-3.5 px-4 pl-5">Mã Lô Hàng</th>
                            <th class="py-3.5 px-4">Sản Phẩm</th>
                            <th class="py-3.5 px-4">Vị Trí Trồng</th>
                            <th class="py-3.5 px-4">Ngày Thu Hái</th>
                            <th class="py-3.5 px-4">Hạn Sử Dụng (FEFO)</th>
                            <th class="py-3.5 px-4 text-center">Tồn Kho</th>
                            <th class="py-3.5 px-4 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-4 pr-5 text-right min-w-[340px]">Tác Vụ Quản Trị</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium whitespace-nowrap" id="batches-table-body">
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODALS THAO TÁC KHO -->
    <!-- Modal Báo Hỏng -->
    <div id="modal-spoilage" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-extrabold text-rose-600 text-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-trash-can"></i> Lập Phiếu Báo Hỏng / Xuất Hủy
                </h4>
                <button onclick="closeModal('modal-spoilage')" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form onsubmit="handleSpoilageSubmit(event)" class="space-y-3 text-xs">
                <input type="hidden" id="sp-batch-id">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mã Lô xử lý:</label>
                    <input type="text" id="sp-batch-code" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold text-slate-700 outline-none">
                </div>
                <div>
                    <div class="flex justify-between mb-1">
                        <label class="font-bold text-slate-700">Số lượng hao hụt xuất hủy (Kg) <span class="text-red-500">*</span></label>
                        <span id="sp-max-notice" class="text-rose-600 font-mono font-bold"></span>
                    </div>
                    <input type="number" id="sp-qty" step="0.5" min="0.5" required class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold text-rose-600 text-sm outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lý do chi tiết (`note`) <span class="text-red-500">*</span></label>
                    <textarea id="sp-note" rows="2" required placeholder="Dập nát khi lưu kho, úng nhũn lá, chuột cắn..." class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-2.5 outline-none focus:border-rose-500 resize-none"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-spoilage')" class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95 cursor-pointer">Xác Nhận Xuất Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Cân Chỉnh Kho -->
    <div id="modal-adjust-stock" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-blue-600 text-base"></i>
                    <h4 class="font-extrabold text-slate-900 text-sm">Cân Chỉnh Tồn Kho Thực Tế</h4>
                </div>
                <button onclick="closeModal('modal-adjust-stock')" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form onsubmit="handleAdjustStockSubmit(event)" class="space-y-3 text-xs">
                <input type="hidden" id="as-batch-id">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mã Lô hàng:</label>
                    <input type="text" id="as-batch-code" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold text-slate-700 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Số lượng cân thực tế mới (Kg) <span class="text-red-500">*</span></label>
                    <input type="number" id="as-quantity" step="0.5" min="0" required class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 font-mono font-bold text-sm text-slate-900 outline-none focus:border-brand-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lý do điều chỉnh kiểm kê <span class="text-red-500">*</span></label>
                    <textarea id="as-note" rows="2" required placeholder="Cân lại định kỳ, kiểm kê sai lệch số liệu..." class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-2.5 outline-none focus:border-brand-600 resize-none"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-adjust-stock')" class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95 cursor-pointer">Lưu Tồn Kho Mới</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sửa HSD -->
    <div id="modal-adjust-expiry" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar-day text-purple-600 text-base"></i>
                    <h4 class="font-extrabold text-slate-900 text-sm">Sửa Hạn Sử Dụng (HSD)</h4>
                </div>
                <button onclick="closeModal('modal-adjust-expiry')" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form onsubmit="handleAdjustExpirySubmit(event)" class="space-y-3 text-xs">
                <input type="hidden" id="ae-batch-id">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mã Lô hàng:</label>
                    <input type="text" id="ae-batch-code" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold text-slate-700 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Hạn sử dụng mới (HSD) <span class="text-red-500">*</span></label>
                    <input type="date" id="ae-expiry" required class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono outline-none focus:border-brand-600 font-bold text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú gia hạn / rút ngắn</label>
                    <textarea id="ae-note" rows="2" placeholder="Bảo quản kho lạnh âm sâu, kéo dài thêm 5 ngày..." class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-2.5 outline-none focus:border-brand-600 resize-none"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-adjust-expiry')" class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95 cursor-pointer">Lưu Hạn Dùng Mới</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal In Tem QR -->
    <div id="modal-in-qr" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-[320px] shadow-2xl p-5 sm:p-6 text-center border border-slate-100">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tem Nhiệt Dán Hộp 70x80</span>
                <button type="button" onclick="closeModal('modal-in-qr')" class="text-slate-400 hover:text-red-500 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div id="printable-thermal-label" class="p-4 border-2 border-dashed border-brand-500 rounded-2xl bg-brand-50/30 mb-4">
                <div class="flex items-center justify-center gap-1.5 text-brand-900 border-b border-brand-200 pb-1.5 mb-2">
                    <i class="fa-solid fa-leaf text-brand-600"></i>
                    <span class="font-extrabold text-xs tracking-wider uppercase">ĐẤT LÀNH FARM</span>
                </div>
                <p id="print-name" class="font-bold text-sm text-slate-900 mb-2 leading-tight">Cà Chua Cherry</p>
                <div class="w-32 h-32 mx-auto bg-white border border-slate-200 rounded-xl p-1.5 mb-2 shadow-xs">
                    <img id="print-img" src="" class="w-full h-full object-contain">
                </div>
                <div class="text-[10px] text-slate-600 font-mono leading-tight space-y-0.5 border-t border-brand-200 pt-2">
                    <p id="print-code" class="font-bold text-slate-900">MÃ LÔ: ---</p>
                    <p id="print-date">Thu: 01/10/2026 • HSD: 20/10/2026</p>
                </div>
                <p class="text-[9px] text-slate-400 italic mt-1.5">Quét mã QR để xem nhật ký nguồn gốc</p>
            </div>
            <button onclick="window.print()" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 rounded-xl transition active:scale-95 flex items-center justify-center gap-2 shadow-md text-xs cursor-pointer">
                <i class="fa-solid fa-print"></i> In Tem Dán Bao Bì
            </button>
        </div>
    </div>

    <!-- Modal Chi Tiết -->
    <div id="modal-detail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-3xl shadow-2xl p-5 sm:p-7 max-h-[90vh] overflow-y-auto custom-scrollbar space-y-4 sm:space-y-6 border border-slate-100">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 sm:pb-4">
                <div class="min-w-0">
                    <h3 class="text-base sm:text-xl font-extrabold text-slate-900 truncate">Chi Tiết Lô Hàng Thương Mại & Nhật Ký Gốc</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 truncate">Mã truy xuất: <b id="dt-code" class="text-brand-700 font-mono text-xs sm:text-sm"></b></p>
                </div>
                <button onclick="closeModal('modal-detail')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition-colors shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="w-14 h-14 rounded-xl bg-white border border-brand-200 p-1 flex items-center justify-center shrink-0 shadow-sm">
                        <img id="dt-qr-img" src="" class="w-full h-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <h4 id="dt-crop" class="text-base sm:text-lg font-extrabold text-slate-900 truncate">Tên Nông Sản</h4>
                        <p class="text-[11px] sm:text-xs text-brand-700 font-mono font-bold mt-0.5 truncate" id="dt-planting-info">Lô Canh Tác Gốc: ---</p>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-[11px] sm:text-xs text-slate-500 font-mono mt-1">
                            <span>Thu: <b id="dt-mfg" class="text-slate-800">--</b></span>
                            <span>•</span>
                            <span>HSD: <b id="dt-exp" class="text-slate-800">--</b></span>
                        </div>
                    </div>
                </div>
                <div class="text-left sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-brand-100">
                    <span class="text-[10px] sm:text-xs text-slate-500 block">Tồn kho hiện tại</span>
                    <span id="dt-current-qty" class="text-lg sm:text-xl font-black text-brand-700">0 Kg</span>
                </div>
            </div>

            <div class="space-y-2.5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-trash-can text-red-500"></i> Lịch Sử Hao Hụt / Xuất Hủy (inventory_logs)
                </h4>
                <div class="space-y-2" id="dt-logs-list"></div>
            </div>

            <div class="space-y-2.5 pt-3 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-seedling text-brand-600"></i> Nhật Ký Canh Tác Gốc Cây Trồng (traceability_events)
                </h4>
                <div class="space-y-3 relative before:absolute before:inset-0 before:left-3 before:w-0.5 before:bg-slate-100" id="dt-events-list"></div>
            </div>

            <div class="pt-2 flex justify-end">
                <button onclick="closeModal('modal-detail')" class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors cursor-pointer">Đóng cửa sổ</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const DBBatch = {
            products: {
                1: { id: 1, name: 'Cà Chua Cherry Đỏ Đà Lạt', unit: 'Kg', sku: 'QUA-TMT-01', price: 65000 },
                2: { id: 2, name: 'Cải Bó Xôi Hữu Cơ (Spinach)', unit: 'Kg', sku: 'RAU-CBX-02', price: 46000 },
                3: { id: 3, name: 'Dưa Leo Baby Nhật Bản', unit: 'Kg', sku: 'QUA-DLB-03', price: 55000 }
            },
            farm_plots: {
                1: { id: 1, name: 'Luống 1', area: '100m2' },
                2: { id: 2, name: 'Luống 2', area: '120m2' },
                3: { id: 3, name: 'Luống 3', area: '150m2' },
                4: { id: 4, name: 'Luống 4', area: '100m2' },
                5: { id: 5, name: 'Luống 5', area: '200m2' }
            },
            planting_batches: {
                1: { id: 1, farm_plot_id: 1, product_id: 1, planting_code: 'TRONG-CACHUA-L1' },
                2: { id: 2, farm_plot_id: 3, product_id: 2, planting_code: 'TRONG-CBX-L3' },
                3: { id: 3, farm_plot_id: 5, product_id: 3, planting_code: 'TRONG-DUALEO-L5' }
            },
            product_batches: [
                { id: 1, planting_batch_id: 1, batch_code: 'TH-20261001-001', manufactured_date: '2026-10-01', expiry_date: '2026-10-13', initial_quantity: 50, current_quantity: 20, spoiled_quantity: 5, status: 'active' },
                { id: 2, planting_batch_id: 1, batch_code: 'TH-20261008-002', manufactured_date: '2026-10-08', expiry_date: '2026-10-25', initial_quantity: 35, current_quantity: 35, spoiled_quantity: 0, status: 'active' },
                { id: 3, planting_batch_id: 2, batch_code: 'TH-20261005-003', manufactured_date: '2026-10-05', expiry_date: '2026-10-22', initial_quantity: 40, current_quantity: 38, spoiled_quantity: 2, status: 'active' },
                { id: 4, planting_batch_id: 3, batch_code: 'TH-20260920-004', manufactured_date: '2026-09-20', expiry_date: '2026-10-05', initial_quantity: 80, current_quantity: 15, spoiled_quantity: 15, status: 'suspended' }
            ],
            inventory_logs: [
                { id: 1, batch_id: 1, type: 'import', quantity: 50, note: 'Nhập kho đợt thu hoạch 1', created_by: 'KS. Hoàng Nam', created_at: '2026-10-01 08:30:00' },
                { id: 2, batch_id: 1, type: 'export_spoilage', quantity: 5, note: 'Chuột cắn rách hộp lưu kho đêm', created_by: 'KS. Hoàng Nam', created_at: '2026-10-04 09:00:00' },
                { id: 3, batch_id: 2, type: 'import', quantity: 35, note: 'Nhập kho đợt thu hoạch 2', created_by: 'KS. Hoàng Nam', created_at: '2026-10-08 09:00:00' }
            ],
            traceability_events: [
                { id: 1, planting_batch_id: 1, event_name: 'Gieo hạt giống F1 Cà Chua', description: 'Ngâm ủ hạt nước ấm 54 độ C, ươm khay xơ dừa', recorded_by: 'KS. Hoàng Nam', recorded_at: '2026-08-01 07:30:00', hash: '0x3a9f...e1' }
            ]
        };

        function getBatch(id) { return DBBatch.product_batches.find(b => b.id === id); }
        function getPlantingBatch(id) { return DBBatch.planting_batches[id]; }
        function getProductByPlanting(pb) { return DBBatch.products[pb.product_id]; }
        function getPlotByPlanting(pb) { return DBBatch.farm_plots[pb.farm_plot_id]; }

        function evaluateStatus(batch) {
            if (batch.status === 'suspended') {
                return { key: 'suspended', label: 'Tạm ngưng', badge: 'bg-slate-100 text-slate-600 border-slate-300' };
            }
            const today = new Date('2026-10-11');
            const exp = new Date(batch.expiry_date);
            const diffDays = Math.ceil((exp - today) / (1000 * 60 * 60 * 24));

            if (exp < today) return { key: 'expired', label: 'Đã hết hạn', badge: 'bg-red-100 text-red-700 border-red-200' };
            if (batch.current_quantity <= 0) return { key: 'out_of_stock', label: 'Đã hết hàng', badge: 'bg-gray-100 text-gray-600 border-gray-200' };
            if (diffDays <= 3) return { key: 'near_expiry', label: `Cận Hạn (${diffDays}N) • FEFO`, badge: 'bg-amber-100 text-amber-800 border-amber-300 font-bold' };
            return { key: 'active', label: 'Đang bán tốt', badge: 'bg-brand-100 text-brand-800 border-brand-200' };
        }

        function renderTable() {
            const tbody = document.getElementById('batches-table-body');
            const searchInput = document.getElementById('filter-search');
            if (!tbody || !searchInput) return;

            const search = searchInput.value.toLowerCase();
            const prodFilter = document.getElementById('filter-product').value;
            const statusFilter = document.getElementById('filter-status').value;

            const kpiTotal = document.getElementById('kpi-total');
            const kpiActive = document.getElementById('kpi-active');
            const kpiNear = document.getElementById('kpi-near-expiry');
            const kpiExp = document.getElementById('kpi-expired');
            const kpiSpoiled = document.getElementById('kpi-spoiled');

            if (kpiTotal) kpiTotal.textContent = DBBatch.product_batches.length;
            if (kpiActive) kpiActive.textContent = DBBatch.product_batches.filter(b => b.current_quantity > 0 && evaluateStatus(b).key !== 'expired').length;
            if (kpiNear) kpiNear.textContent = DBBatch.product_batches.filter(b => evaluateStatus(b).key === 'near_expiry').length;
            if (kpiExp) kpiExp.textContent = DBBatch.product_batches.filter(b => evaluateStatus(b).key === 'expired').length;
            
            const totalSpoiled = DBBatch.product_batches.reduce((sum, b) => sum + b.spoiled_quantity, 0);
            if (kpiSpoiled) kpiSpoiled.textContent = `${totalSpoiled} kg`;

            let list = [...DBBatch.product_batches].sort((a, b) => new Date(a.expiry_date) - new Date(b.expiry_date));

            if (search) {
                list = list.filter(b => {
                    const pb = getPlantingBatch(b.planting_batch_id);
                    const p = getProductByPlanting(pb);
                    const plot = getPlotByPlanting(pb);
                    return b.batch_code.toLowerCase().includes(search) || p.name.toLowerCase().includes(search) || plot.name.toLowerCase().includes(search);
                });
            }
            if (prodFilter) {
                list = list.filter(b => {
                    const pb = getPlantingBatch(b.planting_batch_id);
                    return pb.product_id.toString() === prodFilter;
                });
            }
            if (statusFilter) {
                list = list.filter(b => evaluateStatus(b).key === statusFilter);
            }

            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="py-10 text-center text-xs text-gray-400">Không tìm thấy lô hàng nào phù hợp với bộ lọc.</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(b => {
                const pb = getPlantingBatch(b.planting_batch_id);
                const p = getProductByPlanting(pb);
                const plot = getPlotByPlanting(pb);
                const status = evaluateStatus(b);

                return `
                    <tr class="hover:bg-brand-50/40 transition-colors bg-white border-b border-gray-100 whitespace-nowrap">
                        <td class="py-3 px-4 pl-5">
                            <span onclick="openDetailModal(${b.id})" class="font-mono font-bold text-brand-800 hover:underline cursor-pointer text-xs block">${b.batch_code}</span>
                            <span class="text-[10px] text-gray-400 font-mono">ID: ${b.id}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-gray-900 text-xs">${p.name}</span>
                            <span class="text-[10px] text-gray-400 font-mono block">${p.sku}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-700 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded-lg">
                                <i class="fa-solid fa-map-pin text-brand-600 text-[10px]"></i> ${plot.name}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-gray-600 text-xs">${b.manufactured_date}</td>
                        <td class="py-3 px-4 font-mono text-xs">
                            <span class="${status.key === 'near_expiry' ? 'text-amber-800 font-bold' : (status.key === 'expired' ? 'text-red-600 font-bold' : 'text-gray-700')}">
                                ${b.expiry_date}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono">
                            <span class="font-extrabold ${b.current_quantity > 0 ? 'text-brand-700 text-sm' : 'text-gray-400 text-xs'}">${b.current_quantity}</span>
                            <span class="text-[11px] text-gray-400">/${b.initial_quantity} kg</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border ${status.badge}">${status.label}</span>
                        </td>
                        <td class="py-3 px-4 pr-5 text-right space-x-1">
                            <button onclick="openSpoilageModal(${b.id})" class="px-2 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition font-bold text-[11px] cursor-pointer" title="Báo hỏng / Xuất hủy">
                                <i class="fa-solid fa-trash-can mr-1"></i>Hủy
                            </button>
                            <button onclick="openAdjustExpiryModal(${b.id})" class="px-2 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 transition font-bold text-[11px] cursor-pointer" title="Sửa hạn dùng (FEFO)">
                                <i class="fa-solid fa-calendar-day mr-1"></i>Sửa HSD
                            </button>
                            <button onclick="openAdjustStockModal(${b.id})" class="px-2 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition font-bold text-[11px] cursor-pointer" title="Cân chỉnh tồn kho">
                                <i class="fa-solid fa-scale-balanced mr-1"></i>Cân chỉnh
                            </button>
                            <button onclick="triggerPrintQR(${b.id})" class="px-2 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition font-bold text-[11px] cursor-pointer" title="In mã QR tem nhiệt">
                                <i class="fa-solid fa-qrcode mr-1"></i>QR
                            </button>
                            <button onclick="openDetailModal(${b.id})" class="p-1.5 rounded-lg bg-emerald-50 text-brand-800 hover:bg-brand-100 border border-emerald-200 transition cursor-pointer" title="Xem chi tiết & nhật ký">
                                <i class="fa-solid fa-fingerprint"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function resetFilters() {
            document.getElementById('filter-search').value = '';
            document.getElementById('filter-product').value = '';
            document.getElementById('filter-status').value = '';
            renderTable();
        }

        function openSpoilageModal(batchId) {
            const b = getBatch(batchId);
            const pb = getPlantingBatch(b.planting_batch_id);
            const p = getProductByPlanting(pb);

            document.getElementById('sp-batch-id').value = b.id;
            document.getElementById('sp-batch-code').value = `${b.batch_code} (${p.name})`;
            document.getElementById('sp-max-notice').textContent = `Tồn tối đa: ${b.current_quantity} kg`;
            document.getElementById('sp-qty').max = b.current_quantity;
            document.getElementById('sp-qty').value = '';
            document.getElementById('sp-note').value = '';
            openModal('modal-spoilage');
        }

        function handleSpoilageSubmit(e) {
            e.preventDefault();
            const id = parseInt(document.getElementById('sp-batch-id').value);
            const b = getBatch(id);
            const qty = parseFloat(document.getElementById('sp-qty').value);
            const note = document.getElementById('sp-note').value;
            const now = new Date();
            const timeStr = now.toISOString().slice(0, 19).replace('T', ' ');

            if (qty > b.current_quantity) {
                Swal.fire('Lỗi', 'Số lượng hỏng không thể vượt quá tồn kho thực tế!', 'error');
                return;
            }

            b.current_quantity -= qty;
            b.spoiled_quantity += qty;

            DBBatch.inventory_logs.unshift({
                id: DBBatch.inventory_logs.length + 1,
                batch_id: b.id, type: 'export_spoilage', quantity: qty, note: note,
                created_by: 'KS. Hoàng Nam', created_at: timeStr
            });

            closeModal('modal-spoilage');
            renderTable();
            Swal.fire({ icon: 'success', title: 'Đã xuất hủy hao hụt!', text: `Đã trừ ${qty} kg khỏi lô ${b.batch_code}.`, confirmButtonColor: '#15803d', timer: 1800, showConfirmButton: false });
        }

        function openAdjustExpiryModal(batchId) {
            const b = getBatch(batchId);
            if (!b) return;
            document.getElementById('ae-batch-id').value = b.id;
            document.getElementById('ae-batch-code').value = b.batch_code;
            document.getElementById('ae-expiry').value = b.expiry_date;
            document.getElementById('ae-note').value = '';
            openModal('modal-adjust-expiry');
        }

        function handleAdjustExpirySubmit(e) {
            e.preventDefault();
            const id = parseInt(document.getElementById('ae-batch-id').value);
            const b = getBatch(id);
            const newExp = document.getElementById('ae-expiry').value;
            b.expiry_date = newExp;
            closeModal('modal-adjust-expiry');
            renderTable();
            Swal.fire({ icon: 'success', title: 'Đã cập nhật hạn dùng mới!', text: `Lô ${b.batch_code} có HSD mới: ${newExp}.`, confirmButtonColor: '#15803d' });
        }

        function openAdjustStockModal(batchId) {
            const b = getBatch(batchId);
            if (!b) return;
            document.getElementById('as-batch-id').value = b.id;
            document.getElementById('as-batch-code').value = b.batch_code;
            document.getElementById('as-quantity').value = b.current_quantity;
            document.getElementById('as-note').value = '';
            openModal('modal-adjust-stock');
        }

        function handleAdjustStockSubmit(e) {
            e.preventDefault();
            const id = parseInt(document.getElementById('as-batch-id').value);
            const b = getBatch(id);
            const newQty = parseFloat(document.getElementById('as-quantity').value);
            b.current_quantity = newQty;
            closeModal('modal-adjust-stock');
            renderTable();
            Swal.fire({ icon: 'success', title: 'Đã cân chỉnh tồn kho!', text: `Tồn kho mới: ${newQty} kg.`, confirmButtonColor: '#15803d' });
        }

        function triggerPrintQR(id) {
            const b = getBatch(id);
            const pb = getPlantingBatch(b.planting_batch_id);
            const p = getProductByPlanting(pb);

            document.getElementById('print-name').textContent = p.name;
            document.getElementById('print-code').textContent = `MÃ LÔ: ${b.batch_code}`;
            document.getElementById('print-date').textContent = `Thu: ${b.manufactured_date} • HSD: ${b.expiry_date}`;
            document.getElementById('print-img').src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${b.batch_code}`;
            openModal('modal-in-qr');
        }

        function openDetailModal(id) {
            const b = getBatch(id);
            const pb = getPlantingBatch(b.planting_batch_id);
            const p = getProductByPlanting(pb);
            const plot = getPlotByPlanting(pb);

            document.getElementById('dt-code').textContent = b.batch_code;
            document.getElementById('dt-crop').textContent = p.name;
            document.getElementById('dt-planting-info').textContent = `Lô Canh Tác Gốc: ${pb.planting_code} (${plot.name})`;
            document.getElementById('dt-mfg').textContent = b.manufactured_date;
            document.getElementById('dt-exp').textContent = b.expiry_date;
            document.getElementById('dt-current-qty').textContent = `${b.current_quantity} Kg`;
            document.getElementById('dt-qr-img').src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${b.batch_code}`;

            const logs = DBBatch.inventory_logs.filter(l => l.batch_id === id).reverse();
            const logContainer = document.getElementById('dt-logs-list');
            if (logs.length === 0) {
                logContainer.innerHTML = '<p class="text-xs text-slate-400 italic">Chưa có bản ghi biến động kho nào.</p>';
            } else {
                logContainer.innerHTML = logs.map(l => `
                    <div class="p-3 rounded-xl border border-slate-100 bg-white flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold ${l.type === 'export_spoilage' ? 'text-rose-600' : 'text-brand-700'}">
                                ${l.type === 'export_spoilage' ? 'Xuất hủy hao hụt' : 'Nhập kho thu hoạch'}
                            </span>
                            <p class="text-xs text-slate-700 mt-0.5 font-medium">${l.note}</p>
                            <span class="text-[10px] text-slate-400 font-mono">${l.created_at} • ${l.created_by}</span>
                        </div>
                        <span class="font-mono font-black ${l.type === 'export_spoilage' ? 'text-rose-600' : 'text-brand-700'} text-xs sm:text-sm">
                            ${l.type === 'export_spoilage' ? '-' : '+'}${l.quantity} kg
                        </span>
                    </div>
                `).join('');
            }

            const events = DBBatch.traceability_events.filter(e => e.planting_batch_id === pb.id);
            const evContainer = document.getElementById('dt-events-list');
            if (events.length === 0) {
                evContainer.innerHTML = '<p class="text-xs text-slate-400 italic pl-5">Không có sự kiện canh tác được ghi nhận.</p>';
            } else {
                evContainer.innerHTML = events.map(e => `
                    <div class="relative pl-6 text-xs">
                        <div class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-brand-600 border-2 border-white shadow-xs"></div>
                        <p class="font-bold text-slate-900">${e.event_name}</p>
                        <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">${e.description}</p>
                        <span class="text-[10px] text-slate-400 font-mono">${e.recorded_at} • ${e.recorded_by}</span>
                    </div>
                `).join('');
            }
            openModal('modal-detail');
        }

        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

        document.addEventListener('DOMContentLoaded', () => { renderTable(); });
        document.addEventListener('livewire:navigated', () => { renderTable(); });
    </script>
    @endpush
</div>