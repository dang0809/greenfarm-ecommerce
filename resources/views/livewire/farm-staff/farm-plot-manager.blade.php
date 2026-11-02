<div>
    <!-- Header -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 relative z-10 shrink-0 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <div id="breadcrumb" class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5 truncate">
                    <span class="text-brand-700">Nông Trại Đất Lành</span>
                </div>
                <h1 id="page-title" class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">Sơ Đồ Luống Canh Tác</h1>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-700"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>11/10/2026</span>
            </div>
        </div>
    </header>

    <!-- Main -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative z-10 space-y-5 sm:space-y-6 custom-scrollbar h-[calc(100dvh-5rem)]">
        
        <!-- VIEW DANH SÁCH LUỐNG -->
        <div id="view-plot-list" class="space-y-4 sm:space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3.5 bg-white p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                <div class="relative w-full md:w-80">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                    <input type="text" id="search-plot" oninput="renderPlots()" placeholder="Tìm tên luống (VD: Luống 1)..." class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                </div>
                <div class="flex flex-wrap items-center gap-3 sm:gap-5 text-xs sm:text-sm font-bold text-gray-600">
                    <label class="flex items-center gap-1.5 cursor-pointer hover:text-brand-700 transition">
                        <input type="radio" name="filter_plot" value="all" checked onchange="renderPlots()" class="accent-brand-700"> Tất cả luống
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer hover:text-brand-700 transition">
                        <input type="radio" name="filter_plot" value="active" onchange="renderPlots()" class="accent-brand-700"> Đang canh tác
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer hover:text-gray-900 transition">
                        <input type="radio" name="filter_plot" value="empty" onchange="renderPlots()" class="accent-gray-600"> Đất trống
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 pb-12" id="plots-grid">
                
            </div>
        </div>

        <!-- VIEW: CHI TIẾT LUỐNG & ĐIỀU HÀNH -->
        <div id="view-plot-detail" class="hidden space-y-4 sm:space-y-6 pb-12">
            <div class="flex items-center justify-between">
                <button type="button" onclick="showView('plots')" class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-gray-600 hover:text-brand-700 bg-white border border-gray-200 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl transition shadow-xs hover:bg-gray-50 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Quay lại Danh sách Luống
                </button>
            </div>

            <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 p-4 sm:p-6 shadow-soft flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative overflow-hidden">
                <div class="flex items-center gap-3.5 sm:gap-5 relative z-10 min-w-0">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-brand-50 border border-brand-100 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-seedling text-xl sm:text-2xl text-brand-700"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 sm:gap-3 mb-1">
                            <h2 id="detail-plot-name" class="font-extrabold text-xl sm:text-2xl text-gray-900 truncate">Luống 1</h2>
                            <span id="detail-plot-badge" class="text-[10px] sm:text-xs font-bold px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full border bg-brand-100 text-brand-800 border-brand-200 shrink-0">Đang canh tác</span>
                        </div>
                        <h3 id="detail-crop-name" class="text-sm sm:text-base font-extrabold text-brand-700 truncate">Cà Chua Cherry Đỏ</h3>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-[11px] sm:text-xs text-gray-500 font-mono mt-1">
                            <span>Mã: <b id="detail-batch-code" class="text-gray-800">CT-2026-CACHUA-L1</b></span>
                            <span class="hidden sm:inline">•</span>
                            <span>Gieo hạt: <b id="detail-planted-date" class="text-gray-800">01/08/2026</b></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABS -->
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden">
                <div class="flex overflow-x-auto border-b border-gray-100 px-4 sm:px-6 pt-3 sm:pt-4 gap-6 sm:gap-8 text-xs sm:text-sm no-scrollbar whitespace-nowrap">
                    <button onclick="switchPlotTab('timeline')" id="tab-btn-timeline" class="pb-3 tab-btn-active transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-timeline"></i> Nhật Ký Canh Tác
                    </button>
                    <button onclick="switchPlotTab('harvests')" id="tab-btn-harvests" class="pb-3 tab-btn-inactive transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-boxes-stacked"></i> Lô Hàng Thu Hoạch Đã Xuất Bán
                    </button>
                </div>

                <!-- Tab Nhật ký -->
                <div id="tab-content-timeline" class="p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-gray-900">Dòng Thời Gian Sinh Trưởng & Chăm Sóc</h3>
                            <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">Lưu trữ minh chứng & băm mã SHA-256 đối chiếu</p>
                        </div>
                        <button type="button" onclick="openModal('modal-ghi-nhat-ky')" class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs px-4 py-2.5 rounded-xl sm:rounded-2xl transition flex items-center justify-center gap-2 shadow-sm active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square text-brand-200"></i>
                            <span>Ghi Nhật Ký</span>
                        </button>
                    </div>
                    <div class="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-gray-100" id="events-timeline-list"></div>
                </div>

                <!-- Tab Các lô thu hoạch đã nhập kho -->
                <div id="tab-content-harvests" class="p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-blue-700">Các Lô Hàng Thương Mại Đã Nhập Kho</h3>
                            <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">Mỗi lần thu hoạch sẽ sinh ra 1 Lô hàng mới riêng biệt nhập kho FEFO</p>
                        </div>
                        <button type="button" onclick="openHarvestModal()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl sm:rounded-2xl transition flex items-center justify-center gap-2 shadow-sm active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-basket-shopping"></i>
                            <span>Thu Hoạch (Tạo Lô Mới)</span>
                        </button>
                    </div>
                    <div class="w-full overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-y border-gray-100">
                                    <th class="p-3 sm:p-3.5">Mã Lô Hàng Thu Hoạch</th>
                                    <th class="p-3 sm:p-3.5">Ngày Thu Hái</th>
                                    <th class="p-3 sm:p-3.5 text-center">Sản Lượng (Kg)</th>
                                    <th class="p-3 sm:p-3.5">Hạn Dùng (FEFO)</th>
                                    <th class="p-3 sm:p-3.5 text-right">In Tem QR</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium" id="harvests-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS LUỐNG ĐẤT -->
    <!-- Modal Xuống Giống -->
    <div id="modal-xuong-giong" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-7 border border-gray-100 max-h-[90vh] overflow-y-auto no-scrollbar">
            <div class="flex justify-between items-center mb-4 sm:mb-5 border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Xuống Giống Trồng Mới</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Thao tác tại: <b id="m-xg-plot-name" class="text-brand-700"></b></p>
                </div>
                <button type="button" onclick="closeModal('modal-xuong-giong')" class="text-gray-400 hover:text-red-500 p-1 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form onsubmit="handleXuongGiong(event)" class="space-y-3.5 sm:space-y-4 text-xs sm:text-sm">
                <input type="hidden" id="xg-plot-id">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Mã Lô Canh Tác (Tự sinh)</label>
                    <input type="text" id="xg-code" readonly class="w-full bg-gray-100 border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 font-mono font-bold text-brand-800 text-xs outline-none">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Chọn giống cây trồng <span class="text-red-500">*</span></label>
                    <select id="xg-product" required class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-medium cursor-pointer"></select>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ngày xuống giống <span class="text-red-500">*</span></label>
                    <input type="date" id="xg-date" required class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-mono text-xs">
                </div>
                <button type="submit" class="w-full mt-2 bg-brand-700 hover:bg-brand-800 text-white font-bold py-3 rounded-xl sm:rounded-2xl shadow-sm transition active:scale-95 text-xs sm:text-sm cursor-pointer">
                    Khởi Tạo Lô Canh Tác
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Ghi Nhật Ký -->
    <div id="modal-ghi-nhat-ky" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-7 border border-gray-100 max-h-[90vh] overflow-y-auto no-scrollbar">
            <div class="flex justify-between items-center mb-4 sm:mb-5 border-b border-gray-100 pb-3">
                <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Viết Nhật Ký Chăm Sóc</h3>
                <button type="button" onclick="closeModal('modal-ghi-nhat-ky')" class="text-gray-400 hover:text-red-500 p-1 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form onsubmit="handleGhiNhatKy(event)" class="space-y-3.5 sm:space-y-4 text-xs sm:text-sm">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Loại hoạt động <span class="text-red-500">*</span></label>
                    <input list="task-list" id="nk-name" required placeholder="Tưới nước, Bón phân, Bắt sâu..." class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-medium">
                    <datalist id="task-list">
                        <option value="Tưới nước ngầm công nghệ Israel">
                        <option value="Bón phân hữu cơ trùn quế vi sinh">
                        <option value="Cắt tỉa cành lá, làm cỏ">
                        <option value="Phun chế phẩm sinh học tỏi ớt">
                    </datalist>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Mô tả thông số chi tiết <span class="text-red-500">*</span></label>
                    <textarea id="nk-desc" rows="3" required placeholder="Ghi nhận độ ẩm đất, liều lượng phân bón..." class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl p-3 outline-none focus:border-brand-500 resize-none text-xs font-medium"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ảnh thực địa minh chứng</label>
                    <input type="file" id="nk-file" accept="image/*" onchange="previewImg(this, 'nk-preview-box')" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">
                    <div id="nk-preview-box" class="mt-2 hidden"><img src="" class="w-16 h-16 object-cover rounded-xl border border-gray-200"></div>
                </div>
                <button type="submit" class="w-full mt-2 bg-brand-700 hover:bg-brand-800 text-white font-bold py-3 rounded-xl sm:rounded-2xl shadow-sm transition active:scale-95 text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-fingerprint"></i> Lưu Tức Thì & Băm Hash
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Thu Hoạch -->
    <div id="modal-thu-hoach" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-7 max-h-[90vh] overflow-y-auto no-scrollbar border border-gray-100">
            <div class="flex justify-between items-center mb-4 sm:mb-5 border-b border-gray-100 pb-3 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-blue-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-basket-shopping"></i> Thu Hoạch & Tạo Lô Mới
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Tạo Lô Hàng Mới & ghi nhận Blockchain</p>
                </div>
                <button type="button" onclick="closeModal('modal-thu-hoach')" class="text-gray-400 hover:text-red-500 p-1 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form onsubmit="handleThuHoach(event)" class="space-y-3.5 sm:space-y-4 text-xs sm:text-sm">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Mã Lô Hàng Mới (Tự sinh)</label>
                    <input type="text" id="th-code" readonly class="w-full bg-gray-100 border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 font-mono font-bold text-blue-800 text-xs outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Ngày thu hái <span class="text-red-500">*</span></label>
                        <input type="date" id="th-date" required class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-blue-600 font-mono text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Hạn dùng (FEFO) <span class="text-red-500">*</span></label>
                        <input type="date" id="th-exp" required class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-blue-600 font-mono text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Sản lượng nhập kho (Kg) <span class="text-red-500">*</span></label>
                    <input type="number" id="th-qty" step="0.01" min="0.01" required placeholder="Nhập số kg..." class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-blue-600 font-bold font-mono text-blue-700 text-base">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ảnh thu hoạch thực tế <span class="text-red-500">*</span></label>
                    <input type="file" id="th-file" accept="image/*" required onchange="previewImg(this, 'th-preview-box')" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 cursor-pointer">
                    <div id="th-preview-box" class="mt-2 hidden"><img src="" class="w-20 h-20 object-cover rounded-xl border border-blue-200 shadow-xs"></div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ghi chú nhật ký thu hoạch</label>
                    <textarea id="th-note" rows="2" placeholder="Tình trạng quả, độ ngọt, chất lượng đợt thu..." class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl p-3 outline-none focus:border-blue-600 resize-none text-xs font-medium"></textarea>
                </div>

                <div class="bg-gray-50 rounded-xl sm:rounded-2xl border border-gray-200 p-3.5 sm:p-4 space-y-3">
                    <p class="font-bold text-gray-800 text-xs">Sau khi thu hoạch xong đợt này:</p>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="radio" name="harvest_mode" value="inherit" checked class="w-4 h-4 text-brand-700 accent-brand-700 mt-0.5">
                        <div class="text-xs">
                            <span class="font-bold text-brand-800 block">Tiếp tục nuôi cây (Kế thừa lô cũ)</span>
                            <span class="text-[11px] text-gray-500 block mt-0.5 leading-relaxed">Cây vẫn ở trên luống. Lô hàng mới kế thừa toàn bộ nhật ký trước đó.</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="radio" name="harvest_mode" value="finish" class="w-4 h-4 text-red-600 accent-red-600 mt-0.5">
                        <div class="text-xs">
                            <span class="font-bold text-red-800 block">Dọn luống kết thúc vụ</span>
                            <span class="text-[11px] text-gray-500 block mt-0.5 leading-relaxed">Luống đất sẽ chuyển thành Đất trống để chuẩn bị xuống giống mới.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl sm:rounded-2xl shadow-sm transition active:scale-95 text-xs sm:text-sm flex justify-center items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-box"></i> Lưu Thu Hoạch & Tạo Lô Kho
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal In Tem Nhiệt QR -->
    <div id="modal-in-qr" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-[320px] shadow-2xl p-5 sm:p-6 text-center border border-gray-100">
            <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tem Nhiệt Dán Thùng 70x80</span>
                <button type="button" onclick="closeModal('modal-in-qr')" class="text-gray-400 hover:text-red-500 p-1 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div id="printable-thermal-label" class="p-4 border-2 border-dashed border-brand-500 rounded-2xl bg-brand-50/20 mb-5">
                <div class="flex items-center justify-center gap-1.5 text-brand-900 border-b border-brand-200 pb-1.5 mb-2">
                    <i class="fa-solid fa-leaf text-brand-600"></i>
                    <span class="font-extrabold text-[11px] tracking-wider uppercase">ĐẤT LÀNH FARM</span>
                </div>
                <p id="print-name" class="font-bold text-sm text-gray-900 mb-2 leading-tight">Cà Chua Cherry</p>
                <div class="w-32 h-32 mx-auto bg-white border border-gray-200 rounded-xl p-1.5 mb-2 shadow-xs">
                    <img id="print-img" src="" class="w-full h-full object-contain">
                </div>
                <div class="text-[10px] text-gray-600 font-mono leading-tight space-y-0.5 border-t border-brand-200 pt-2">
                    <p id="print-code" class="font-bold text-gray-900">MÃ LÔ: TH-20261001-001</p>
                    <p id="print-date">Thu: 01/10/2026 • HSD: 20/10/2026</p>
                </div>
                <p class="text-[8px] text-gray-400 italic mt-1.5">Quét mã QR để xem nhật ký nguồn gốc</p>
            </div>
            <button onclick="window.print()" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 sm:py-3 rounded-xl sm:rounded-2xl transition active:scale-95 flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer">
                <i class="fa-solid fa-print"></i> In Tem Dán Bao Bì
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        const DBPlot = {
            products: {
                1: { id: 1, name: 'Cà Chua Cherry Đỏ Đà Lạt', unit: 'kg' },
                2: { id: 2, name: 'Cải Bó Xôi Hữu Cơ (Spinach)', unit: 'kg' },
                3: { id: 3, name: 'Dưa Leo Baby Nhật Bản', unit: 'kg' }
            },
            plots: [
                { id: 1, name: 'Luống 1', area: '100m2', status: 'active' },
                { id: 2, name: 'Luống 2', area: '120m2', status: 'empty' },
                { id: 3, name: 'Luống 3', area: '150m2', status: 'active' },
                { id: 4, name: 'Luống 4', area: '100m2', status: 'empty' },
                { id: 5, name: 'Luống 5', area: '200m2', status: 'active' },
                { id: 6, name: 'Luống 6', area: '100m2', status: 'maintenance' }
            ],
            batches: [
                { id: 1, farm_plot_id: 1, product_id: 1, parent_batch_id: null, batch_code: 'CT-2026-CACHUA-L1', crop_name: 'Cà Chua Cherry Đỏ Đà Lạt', planted_date: '2026-08-01', initial_quantity: 50, current_quantity: 45, spoiled_quantity: 5, status: 'growing' },
                { id: 2, farm_plot_id: 3, product_id: 2, parent_batch_id: null, batch_code: 'CT-2026-CBX-L3', crop_name: 'Cải Bó Xôi Hữu Cơ (Spinach)', planted_date: '2026-10-01', initial_quantity: 0, current_quantity: 0, spoiled_quantity: 0, status: 'growing' },
                { id: 3, farm_plot_id: 5, product_id: 3, parent_batch_id: null, batch_code: 'CT-2026-DUALEO-L5', crop_name: 'Dưa Leo Baby Nhật Bản', planted_date: '2026-08-15', initial_quantity: 80, current_quantity: 70, spoiled_quantity: 10, status: 'growing' }
            ],
            harvest_batches: [
                { id: 101, batch_id: 1, batch_code: 'TH-20261001-001', harvest_date: '2026-10-01', expiry_date: '2026-10-20', quantity: 50, image: 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=400', note: 'Thu quả chín bói đợt 1, quả đỏ mọng đều.' },
                { id: 102, batch_id: 3, batch_code: 'TH-20260925-001', harvest_date: '2026-09-25', expiry_date: '2026-10-15', quantity: 80, image: 'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?w=400', note: 'Dưa giòn ngọt, quả thẳng đạt loại 1.' }
            ],
            events: [
                { id: 1, batch_id: 1, event_name: 'Gieo hạt giống F1 Cà Chua', description: 'Khử trùng hạt giống nước ấm 54 độ C, ươm bầu xơ dừa.', image: 'https://images.unsplash.com/photo-1596199050105-6d9d0600a068?w=300', recorded_by: 'KS. Hoàng Nam', recorded_at: '2026-08-01 06:30', data_hash: '0x3d5e89a1c4b7...' },
                { id: 2, batch_id: 1, event_name: 'Bón phân hữu cơ vi sinh trùn quế', description: 'Bón thúc rễ bằng phân trùn quế vi sinh ủ hoai mục 45 ngày.', image: 'https://images.unsplash.com/photo-1592417817098-8f3d6910985b?w=300', recorded_by: 'KS. Hoàng Nam', recorded_at: '2026-08-20 08:00', data_hash: '0x8f4e2b1c9a7d...' }
            ],
            currentPlotId: null
        };

        function getPlot(id) { return DBPlot.plots.find(p => p.id === id); }
        function getBatchByPlot(plotId) { return DBPlot.batches.find(b => b.farm_plot_id === plotId && b.status === 'growing'); }

        function renderPlots() {
            const container = document.getElementById('plots-grid');
            const searchInput = document.getElementById('search-plot');
            if (!container || !searchInput) return;

            const search = searchInput.value.toLowerCase();
            const checkedFilter = document.querySelector('input[name="filter_plot"]:checked');
            const filter = checkedFilter ? checkedFilter.value : 'all';

            let filtered = DBPlot.plots;
            if (search) filtered = filtered.filter(p => p.name.toLowerCase().includes(search));

            container.innerHTML = filtered.map(plot => {
                const batch = getBatchByPlot(plot.id);
                if (filter === 'active' && !batch) return '';
                if (filter === 'empty' && batch) return '';

                if (!batch) {
                    return `
                        <div class="bg-white rounded-2xl sm:rounded-3xl border border-dashed border-gray-300 p-4 sm:p-5 flex flex-col justify-between min-h-[190px] sm:min-h-[200px] hover:bg-gray-50 transition shadow-sm">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-extrabold text-gray-800 text-sm sm:text-base">${plot.name}</h3>
                                    <span class="bg-gray-100 text-gray-500 px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold">Đất trống</span>
                                </div>
                                <p class="text-xs text-gray-400">Diện tích: ${plot.area}</p>
                                <p class="text-xs text-gray-500 font-medium mt-2.5 bg-gray-50 p-2 sm:p-2.5 rounded-xl border border-gray-100">Chưa có cây trồng</p>
                            </div>
                            <button onclick="openXuongGiongModal(${plot.id})" class="w-full mt-3 bg-white hover:bg-brand-50 text-brand-700 border border-brand-200 font-bold py-2 rounded-xl text-xs transition shadow-2xs flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                                <i class="fa-solid fa-seedling text-xs"></i> + Xuống Giống Mới
                            </button>
                        </div>
                    `;
                }

                return `
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-brand-100 p-4 sm:p-5 shadow-soft hover:shadow-card hover:border-brand-300 transition-all flex flex-col justify-between min-h-[190px] sm:min-h-[200px]">
                        <div>
                            <div class="flex items-center justify-between mb-2 sm:mb-3">
                                <h3 class="font-extrabold text-gray-900 text-sm sm:text-base">${plot.name}</h3>
                                <span class="bg-brand-100 text-brand-800 px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold">Đang canh tác</span>
                            </div>
                            <div class="flex items-start gap-3 mb-2">
                                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100">
                                    <i class="fa-solid fa-leaf text-brand-700"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs sm:text-sm font-extrabold text-brand-900 truncate leading-tight">${batch.crop_name}</h4>
                                    <p class="text-[11px] text-gray-400 font-mono mt-0.5 truncate">Mã: ${batch.batch_code}</p>
                                    <p class="text-[11px] text-gray-400 font-mono">Gieo: ${batch.planted_date}</p>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2.5 border-t border-gray-100 flex items-center justify-end">
                            <button onclick="showPlotDetail(${plot.id})" class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold py-1.5 sm:py-2 px-3.5 rounded-xl text-xs transition shadow-2xs flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                                <span>Quản Lý Luống</span> <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function showPlotDetail(plotId) {
            DBPlot.currentPlotId = plotId;
            const plot = getPlot(plotId);
            const batch = getBatchByPlot(plotId);
            if (!plot || !batch) return;

            document.getElementById('view-plot-list').classList.add('hidden');
            document.getElementById('view-plot-detail').classList.remove('hidden');

            document.getElementById('breadcrumb').innerHTML = `<span class="text-gray-400">Nông Trại / </span><span class="text-brand-700">${plot.name}</span>`;
            document.getElementById('page-title').textContent = `Điều Khiển: ${plot.name}`;

            document.getElementById('detail-plot-name').textContent = plot.name;
            document.getElementById('detail-crop-name').textContent = batch.crop_name;
            document.getElementById('detail-batch-code').textContent = batch.batch_code;
            document.getElementById('detail-planted-date').textContent = batch.planted_date;

            renderEventsTimeline(batch.id);
            renderHarvestBatches(batch.id);
            switchPlotTab('timeline');
        }

        function renderEventsTimeline(batchId) {
            const list = document.getElementById('events-timeline-list');
            if (!list) return;
            const events = DBPlot.events.filter(e => e.batch_id === batchId).sort((a,b) => b.id - a.id);

            if (events.length === 0) {
                list.innerHTML = '<p class="text-xs text-gray-400 italic pl-5">Chưa có nhật ký canh tác nào.</p>';
                return;
            }

            list.innerHTML = events.map(e => `
                <div class="relative pl-6 sm:pl-7">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full ${e.event_name.includes('Thu hoạch') ? 'bg-blue-600' : 'bg-brand-700'} border-2 border-white shadow-xs"></div>
                    <div class="bg-gray-50/60 p-3.5 sm:p-4 rounded-2xl border ${e.event_name.includes('Thu hoạch') ? 'border-blue-200 bg-blue-50/30' : 'border-gray-200/80'} space-y-1.5 hover:bg-white transition-all">
                        <div class="flex justify-between items-start gap-2">
                            <h4 class="font-extrabold text-xs sm:text-sm ${e.event_name.includes('Thu hoạch') ? 'text-blue-800' : 'text-gray-900'} leading-tight">${e.event_name}</h4>
                            <span class="text-[9px] sm:text-[10px] font-mono text-gray-400 shrink-0">Hash: ${e.data_hash.substring(0, 8)}...</span>
                        </div>
                        <p class="text-[11px] text-gray-500 font-mono"><i class="fa-regular fa-clock mr-1 text-gray-400"></i> ${e.recorded_at} • ${e.recorded_by}</p>
                        <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">${e.description}</p>
                        ${e.image ? `<img src="${e.image}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl object-cover border mt-2 shadow-xs cursor-pointer hover:scale-105 transition" onclick="previewImage('${e.image}')">` : ''}
                    </div>
                </div>
            `).join('');
        }

        function renderHarvestBatches(batchId) {
            const tbody = document.getElementById('harvests-table-body');
            if (!tbody) return;
            const harvests = DBPlot.harvest_batches.filter(h => h.batch_id === batchId).sort((a,b) => b.id - a.id);

            if (harvests.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-xs text-gray-400">Chưa khai báo đợt thu hoạch nào.</td></tr>';
                return;
            }

            tbody.innerHTML = harvests.map(h => `
                <tr class="hover:bg-blue-50/30 transition whitespace-nowrap">
                    <td class="p-3 sm:p-4 font-mono font-bold text-blue-700 text-xs">${h.batch_code}</td>
                    <td class="p-3 sm:p-4 font-mono text-gray-600 text-xs">${h.harvest_date}</td>
                    <td class="p-3 sm:p-4 font-black text-brand-800 text-sm text-center">${h.quantity}</td>
                    <td class="p-3 sm:p-4 font-mono text-gray-600 text-xs">${h.expiry_date}</td>
                    <td class="p-3 sm:p-4 text-right">
                        <button onclick="triggerPrintQR('${h.batch_code}', '${h.harvest_date}', '${h.expiry_date}')" class="bg-white border border-gray-200 text-gray-700 hover:text-brand-700 font-bold px-3 py-1.5 rounded-xl text-xs transition shadow-2xs cursor-pointer">
                            In Tem 70x80
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function switchPlotTab(tabId) {
            ['timeline', 'harvests'].forEach(t => {
                const content = document.getElementById(`tab-content-${t}`);
                const btn = document.getElementById(`tab-btn-${t}`);
                if (content) content.classList.add('hidden');
                if (btn) btn.className = "pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer";
            });
            const activeContent = document.getElementById(`tab-content-${tabId}`);
            const activeBtn = document.getElementById(`tab-btn-${tabId}`);
            if (activeContent) activeContent.classList.remove('hidden');
            if (activeBtn) activeBtn.className = "pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2 cursor-pointer";
        }

        function showView(view) {
            if (view === 'plots') {
                const detail = document.getElementById('view-plot-detail');
                const list = document.getElementById('view-plot-list');
                const title = document.getElementById('page-title');
                const breadcrumb = document.getElementById('breadcrumb');

                if (detail) detail.classList.add('hidden');
                if (list) list.classList.remove('hidden');
                if (title) title.textContent = "Sơ Đồ Luống Canh Tác";
                if (breadcrumb) breadcrumb.innerHTML = `<span class="text-brand-700">Nông Trại Đất Lành</span>`;
                renderPlots();
            }
        }

        function openXuongGiongModal(plotId) {
            const plot = getPlot(plotId);
            document.getElementById('xg-plot-id').value = plotId;
            document.getElementById('m-xg-plot-name').textContent = plot.name;
            const now = new Date().toISOString().slice(0,10).replace(/-/g,'');
            document.getElementById('xg-code').value = `CT-${now}-${plot.name.replace(/[^0-9]/g, '')}`;
            document.getElementById('xg-date').value = new Date().toISOString().slice(0,10);
            
            document.getElementById('xg-product').innerHTML = Object.keys(DBPlot.products).map(id => `<option value="${id}">${DBPlot.products[id].name}</option>`).join('');
            openModal('modal-xuong-giong');
        }

        function handleXuongGiong(e) {
            e.preventDefault();
            const plotId = parseInt(document.getElementById('xg-plot-id').value);
            const plot = getPlot(plotId);
            const prod = DBPlot.products[document.getElementById('xg-product').value];

            plot.status = 'active';
            DBPlot.batches.push({
                id: DBPlot.batches.length + 1, farm_plot_id: plotId, product_id: prod.id, parent_batch_id: null,
                batch_code: document.getElementById('xg-code').value, crop_name: prod.name,
                planted_date: document.getElementById('xg-date').value, initial_quantity: 0, current_quantity: 0, spoiled_quantity: 0, status: 'growing'
            });

            closeModal('modal-xuong-giong');
            Swal.fire({ icon: 'success', title: 'Xuống giống thành công!', text: `Đã mở Lô Canh Tác tại ${plot.name}`, confirmButtonColor: '#15803d' });
            renderPlots();
        }

        function handleGhiNhatKy(e) {
            e.preventDefault();
            const batch = getBatchByPlot(DBPlot.currentPlotId);
            const box = document.getElementById('nk-preview-box');
            const hasImg = !box.classList.contains('hidden');
            const now = new Date();

            DBPlot.events.unshift({
                id: DBPlot.events.length + 1,
                batch_id: batch.id,
                event_name: document.getElementById('nk-name').value,
                description: document.getElementById('nk-desc').value,
                image: hasImg ? box.querySelector('img').src : null,
                recorded_by: 'KS. Hoàng Nam',
                recorded_at: now.toLocaleDateString('vi-VN') + ' ' + now.toLocaleTimeString('vi-VN', {hour:'2-digit', minute:'2-digit'}),
                data_hash: '0x' + Math.random().toString(16).substring(2, 12)
            });

            document.getElementById('nk-name').value = '';
            document.getElementById('nk-desc').value = '';
            box.classList.add('hidden');

            closeModal('modal-ghi-nhat-ky');
            renderEventsTimeline(batch.id);
            Swal.fire({ icon: 'success', title: 'Đã lưu nhật ký!', timer: 1500, showConfirmButton: false });
        }

        function openHarvestModal() {
            const now = new Date().toISOString().slice(0,10);
            const nowClean = now.replace(/-/g,'');
            document.getElementById('th-code').value = `TH-${nowClean}-${Math.random().toString(36).substring(2,5).toUpperCase()}`;
            document.getElementById('th-date').value = now;
            
            const exp = new Date();
            exp.setDate(exp.getDate() + 15);
            document.getElementById('th-exp').value = exp.toISOString().slice(0,10);
            document.getElementById('th-qty').value = '';
            document.getElementById('th-file').value = '';
            document.getElementById('th-preview-box').classList.add('hidden');
            document.getElementById('th-note').value = '';

            openModal('modal-thu-hoach');
        }

        function handleThuHoach(e) {
            e.preventDefault();
            const batch = getBatchByPlot(DBPlot.currentPlotId);
            const qty = parseFloat(document.getElementById('th-qty').value);
            const thCode = document.getElementById('th-code').value;
            const thDate = document.getElementById('th-date').value;
            const thExp = document.getElementById('th-exp').value;
            const thNote = document.getElementById('th-note').value;
            const box = document.getElementById('th-preview-box');
            const harvestImg = box.querySelector('img').src;
            const mode = document.querySelector('input[name="harvest_mode"]:checked').value;

            DBPlot.harvest_batches.unshift({
                id: DBPlot.harvest_batches.length + 1, batch_id: batch.id, batch_code: thCode,
                harvest_date: thDate, expiry_date: thExp, quantity: qty, image: harvestImg, note: thNote
            });

            DBPlot.events.unshift({
                id: DBPlot.events.length + 1, batch_id: batch.id,
                event_name: `Thu hoạch Đợt (Mã: ${thCode})`,
                description: `Thu hái ${qty} kg nông sản đạt chuẩn. ${thNote}`,
                image: harvestImg, recorded_by: 'KS. Hoàng Nam',
                recorded_at: thDate + ' ' + new Date().toLocaleTimeString('vi-VN', {hour:'2-digit', minute:'2-digit'}),
                data_hash: '0x' + Math.random().toString(16).substring(2, 12)
            });

            batch.initial_quantity += qty;
            batch.current_quantity += qty;

            if (mode === 'finish') {
                batch.status = 'completed';
                const plot = getPlot(DBPlot.currentPlotId);
                plot.status = 'empty';
            }

            closeModal('modal-thu-hoach');

            if (mode === 'finish') {
                Swal.fire({ icon: 'success', title: 'Thu hoạch & Dọn luống thành công!', text: 'Đã tạo Lô hàng mới. Luống đã sẵn sàng cho vụ sau.', confirmButtonColor: '#15803d' }).then(() => {
                    showView('plots');
                });
            } else {
                Swal.fire({ icon: 'success', title: 'Thu hoạch thành công!', text: `Đã tạo Lô hàng mới ${thCode}. Cây vẫn tiếp tục được nuôi trên luống.`, confirmButtonColor: '#15803d' });
                showPlotDetail(DBPlot.currentPlotId);
                switchPlotTab('harvests');
            }
        }

        function triggerPrintQR(code, mfg, exp) {
            const batch = getBatchByPlot(DBPlot.currentPlotId);
            document.getElementById('print-name').textContent = batch.crop_name;
            document.getElementById('print-code').textContent = `MÃ LÔ: ${code}`;
            document.getElementById('print-date').textContent = `Thu: ${mfg} • HSD: ${exp}`;
            document.getElementById('print-img').src = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${code}`;
            openModal('modal-in-qr');
        }

        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

        function previewImg(input, boxId) {
            const box = document.getElementById(boxId);
            if (input.files && input.files[0]) {
                const r = new FileReader();
                r.onload = e => { box.querySelector('img').src = e.target.result; box.classList.remove('hidden'); }
                r.readAsDataURL(input.files[0]);
            }
        }
        function previewImage(url) { Swal.fire({ imageUrl: url, imageAlt: 'Ảnh phóng to', showConfirmButton: false, width: 'auto', background: 'transparent' }); }

        document.addEventListener('DOMContentLoaded', () => { renderPlots(); });
        document.addEventListener('livewire:navigated', () => { renderPlots(); });
    </script>
    @endpush
</div>