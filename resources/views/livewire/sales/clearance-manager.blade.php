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
                    Xả Hàng Cận Date & Flash Sale
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

        <!-- ================= HÀNG 3 THẺ THỐNG KÊ (ĐÃ TINH GỌN THEO YÊU CẦU) ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-5">
            
            <!-- Thẻ 1: Lô nguy cấp dưới 24h -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-rose-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-rose-600 block uppercase tracking-wider">Hạn Dưới 24H (Hôm nay)</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-rose-600 tracking-tight font-mono" id="kpi-urgent-count">0</span>
                        <span class="text-[10px] font-extrabold bg-rose-50 text-rose-700 px-2 py-0.5 rounded-full border border-rose-200 animate-pulse">Xả gấp</span>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Lô hết hạn trong ngày hôm nay</p>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>

            <!-- Thẻ 2: Lô cận hạn dưới 48h (còn 2 ngày) -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-amber-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-amber-700 block uppercase tracking-wider">Hạn Còn &le; 2 Ngày (&le; 48H)</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-amber-700 tracking-tight font-mono" id="kpi-near-count">0</span>
                        <span class="text-[10px] font-bold bg-amber-50 text-amber-800 px-2 py-0.5 rounded-full border border-amber-200">Cận date</span>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Tổng các lô trong diện Flash Sale</p>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>

            <!-- Thẻ 3: Đang kích hoạt Flash Sale xả hàng -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-emerald-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-emerald-700 block uppercase tracking-wider">Đang Xả Kho Flash Sale</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-emerald-800 tracking-tight font-mono" id="kpi-active-sale-count">0</span>
                        <span class="text-xs text-emerald-700 font-bold" id="kpi-active-sale-qty">0 kg</span>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Đang hiển thị giá giảm trên Website</p>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-bolt"></i>
                </div>
            </div>

        </div>

        <!-- ================= BẢNG DANH SÁCH: CÁC LÔ NÔNG SẢN CẬN DATE & XẢ KHO FEFO ================= -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden space-y-4">
            
            <!-- Toolbar: Tiêu đề -->
            <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/60">
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-fire text-amber-500"></i>
                        <span>Danh Sách Lô Cận Date Cần Xả Kho (Hạn Còn &le; 2 Ngày)</span>
                    </h2>
                </div>
            </div>

            <!-- Thanh Tabs & Tìm Kiếm -->
            <div class="px-4 sm:px-6 pt-1 flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-gray-100">
                <!-- Tabs Lọc -->
                <div class="flex overflow-x-auto gap-6 text-sm no-scrollbar">
                    <button onclick="applyBatchFilter('all')" id="tab-all" class="pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                        <span>Tất cả lô cận date</span>
                        <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full" id="count-all">0</span>
                    </button>
                    <button onclick="applyBatchFilter('on_sale')" id="tab-on_sale" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                        <span>Đang chạy Flash Sale</span>
                        <span class="text-xs font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full" id="count-sale">0</span>
                    </button>
                    <button onclick="applyBatchFilter('not_sale')" id="tab-not_sale" class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                        <span>Chưa áp dụng giảm giá</span>
                        <span class="text-xs font-bold bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full" id="count-not-sale">0</span>
                    </button>
                </div>

                <!-- Tìm Kiếm -->
                <div class="relative w-full md:w-80 pb-3 md:pb-0">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="search-batch-input" oninput="renderAllData()" placeholder="Tìm mã lô, tên nông sản..." class="w-full pl-9 pr-3.5 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-brand-500 bg-[#FAFAF8] font-medium transition-all">
                </div>
            </div>

            <!-- Bảng Dữ Liệu Cuộn Ngang -->
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-gray-100">
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Mã Lô Hàng</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Tên Nông Sản</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Ngày Thu Hoạch</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Hạn Dùng & Thời Gian Còn Lại</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5 text-center">Tồn Kho (Kg)</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5 text-right">Giá Gốc</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5 text-center">Trạng Thái Xả Kho</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium" id="near-expiry-tbody">
                        <!-- Render bằng JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Trạng thái trống -->
            <div id="empty-state" class="hidden py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <p class="text-base font-bold text-gray-700">Kho hàng an toàn! Không có lô nào cận hạn dưới 2 ngày</p>
                <p class="text-xs text-gray-400 mt-1">Tất cả nông sản trong kho đều tươi mới và còn hạn sử dụng an toàn.</p>
            </div>
        </div>

    </div>

    <!-- ================= 3. MODAL CẤU HÌNH GIẢM GIÁ & KÍCH HOẠT FLASH SALE ================= -->
    <div id="modal-flash-sale" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-7 max-h-[92vh] overflow-y-auto custom-scrollbar border border-gray-100 space-y-4 sm:space-y-5">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 sm:pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-gray-900 leading-tight">Cấu Hình Flash Sale Xả Kho Cận Date</h3>
                        <p class="text-xs text-gray-400 font-mono" id="mod-batch-code-title">Lô: ---</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-flash-sale')" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form onsubmit="handleConfirmFlashSale(event)" class="space-y-4 text-xs">
                <input type="hidden" id="fs-batch-id">

                <!-- Thẻ tóm tắt nông sản & hạn dùng -->
                <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200/80 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nông sản áp dụng xả kho:</span>
                            <h4 class="font-extrabold text-gray-900 text-sm sm:text-base" id="fs-product-name">Rau muống thủy canh Đà Lạt</h4>
                        </div>
                        <span class="font-mono text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full" id="fs-hours-badge">
                            Còn 14 giờ
                        </span>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-amber-200/50 font-mono text-xs text-gray-600">
                        <span>Tồn kho hiện tại: <b class="text-gray-900" id="fs-stock-text">35 kg</b></span>
                        <span>Giá gốc: <b class="text-gray-900" id="fs-price-original">30.000đ/kg</b></span>
                    </div>
                </div>

                <!-- Cấu hình % Giảm giá -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1.5">
                        Chọn mức giảm giá Flash Sale (%): <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="grid grid-cols-4 gap-2 mb-2">
                        <button type="button" onclick="setDiscountPreset(20)" class="py-2 rounded-xl border border-gray-200 font-bold text-gray-700 bg-white hover:border-amber-500 hover:text-amber-800 transition active:scale-95 cursor-pointer">20%</button>
                        <button type="button" onclick="setDiscountPreset(30)" class="py-2 rounded-xl border border-gray-200 font-bold text-gray-700 bg-white hover:border-amber-500 hover:text-amber-800 transition active:scale-95 cursor-pointer">30%</button>
                        <button type="button" onclick="setDiscountPreset(50)" class="py-2 rounded-xl border border-gray-200 font-bold text-gray-700 bg-white hover:border-amber-500 hover:text-amber-800 transition active:scale-95 cursor-pointer">50%</button>
                        <button type="button" onclick="setDiscountPreset(70)" class="py-2 rounded-xl border border-gray-200 font-bold text-gray-700 bg-white hover:border-amber-500 hover:text-amber-800 transition active:scale-95 cursor-pointer">70%</button>
                    </div>

                    <div class="relative">
                        <input type="number" id="fs-discount-input" min="5" max="90" required value="40" oninput="calculateSalePrice()" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-2xl px-4 py-2.5 font-mono font-extrabold text-base text-amber-700 outline-none focus:border-amber-500 focus:bg-white">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 font-black text-gray-400">%</span>
                    </div>
                </div>

                <!-- Bảng tính tự động giá sau khi giảm -->
                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <div>
                        <span class="text-gray-400 block text-[11px]">Giá bán hiển thị cho khách sau khi giảm:</span>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <span class="text-lg sm:text-xl font-black text-brand-700 font-mono" id="fs-calc-saleprice">18.000đ</span>
                            <span class="text-xs text-gray-400 line-through font-mono" id="fs-calc-oldprice">30.000đ</span>
                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200 font-mono" id="fs-calc-discount-badge">-40%</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-tags text-brand-600 text-xl"></i>
                </div>

                <!-- Cấu hình thời gian áp dụng -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1.5">Thời hạn áp dụng mức giảm:</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-amber-400">
                            <input type="radio" name="fs_duration" value="today" checked class="text-amber-600 focus:ring-amber-500">
                            <span class="font-bold text-gray-800">Đến hết ngày hôm nay (Ưu tiên dọn kho trong ngày)</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-amber-400">
                            <input type="radio" name="fs_duration" value="until_sold" class="text-amber-600 focus:ring-amber-500">
                            <span class="font-bold text-gray-800">Đến khi bán hết số lượng tồn kho của Lô</span>
                        </label>
                    </div>
                </div>

                <!-- Nút Submit -->
                <div class="pt-2 flex flex-col sm:flex-row justify-end gap-2.5 sm:gap-3">
                    <button type="button" onclick="closeModal('modal-flash-sale')" class="w-full sm:w-auto px-5 py-2.5 rounded-xl sm:rounded-2xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="w-full sm:w-auto bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Kích Hoạt Flash Sale Ngay</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // DỮ LIỆU CÁC LÔ CẬN DATE TRONG VÒNG 2 NGÀY (<= 48H) CHUẨN CSDL (product_batches.discount_percent)
    const DB = {
        batches: [
            {
                id: 1,
                batch_code: 'TH-20261009-001',
                product_name: 'Cà Chua Cherry Đỏ Ngọt Đà Lạt',
                image: 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=400',
                harvest_date: '07/10/2026',
                expiry_date: '12/10/2026',
                hours_left: 14, // < 24h
                stock_qty: 35,
                original_price: 65000,
                discount_percent: 40,
                duration_type: 'today'
            },
            {
                id: 2,
                batch_code: 'TH-20261008-002',
                product_name: 'Rau Muống Thủy Canh Nông Trại',
                image: 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=400',
                harvest_date: '08/10/2026',
                expiry_date: '12/10/2026',
                hours_left: 18, // < 24h
                stock_qty: 48,
                original_price: 32000,
                discount_percent: 50,
                duration_type: 'today'
            },
            {
                id: 3,
                batch_code: 'TH-20261009-003',
                product_name: 'Dâu Tây Giống Nhật Mộc Châu',
                image: 'https://images.unsplash.com/photo-1543528176-61b239494933?w=400',
                harvest_date: '09/10/2026',
                expiry_date: '12/10/2026',
                hours_left: 22, // < 24h
                stock_qty: 18,
                original_price: 150000,
                discount_percent: 0,
                duration_type: null
            },
            {
                id: 4,
                batch_code: 'TH-20261009-004',
                product_name: 'Dưa Leo Baby Nhật Bản Giòn Ngọt',
                image: 'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?w=400',
                harvest_date: '09/10/2026',
                expiry_date: '13/10/2026',
                hours_left: 36, // <= 48h (còn 1.5 ngày)
                stock_qty: 62,
                original_price: 55000,
                discount_percent: 30,
                duration_type: 'until_sold'
            },
            {
                id: 5,
                batch_code: 'TH-20261009-005',
                product_name: 'Cải Bó Xôi Hữu Cơ (Spinach)',
                image: 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400',
                harvest_date: '09/10/2026',
                expiry_date: '13/10/2026',
                hours_left: 42, // <= 48h (còn 1.7 ngày)
                stock_qty: 25,
                original_price: 46000,
                discount_percent: 0,
                duration_type: null
            }
        ]
    };

    let currentBatchFilter = 'all';
    let configuringBatchId = null;

    function applyBatchFilter(type) {
        currentBatchFilter = type;
        ['all', 'on_sale', 'not_sale'].forEach(t => {
            const btn = document.getElementById(`tab-${t}`);
            if (btn) {
                if (t === type) {
                    btn.className = 'pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2 cursor-pointer';
                } else {
                    btn.className = 'pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2 cursor-pointer';
                }
            }
        });
        renderAllData();
    }

    function renderAllData() {
        updateMetrics();
        renderNearExpiryTable();
    }

    function updateMetrics() {
        // Hạn <= 24h
        const urgentCount = DB.batches.filter(b => b.hours_left <= 24).length;
        // Hạn <= 48h (toàn bộ danh sách này)
        const totalNearCount = DB.batches.length;

        // Đang Flash Sale
        const activeSaleBatches = DB.batches.filter(b => b.discount_percent > 0);
        const activeSaleCount = activeSaleBatches.length;
        const activeSaleQty = activeSaleBatches.reduce((sum, b) => sum + b.stock_qty, 0);

        const elUrgent = document.getElementById('kpi-urgent-count');
        const elNear = document.getElementById('kpi-near-count');
        const elActiveCount = document.getElementById('kpi-active-sale-count');
        const elActiveQty = document.getElementById('kpi-active-sale-qty');

        if (elUrgent) elUrgent.textContent = String(urgentCount).padStart(2, '0');
        if (elNear) elNear.textContent = String(totalNearCount).padStart(2, '0');
        if (elActiveCount) elActiveCount.textContent = `${activeSaleCount} lô hàng`;
        if (elActiveQty) elActiveQty.textContent = `${activeSaleQty} kg`;

        // Cập nhật số liệu trên các tab
        const notSaleCount = DB.batches.filter(b => b.discount_percent === 0).length;
        const elCountAll = document.getElementById('count-all');
        const elCountSale = document.getElementById('count-sale');
        const elCountNotSale = document.getElementById('count-not-sale');

        if (elCountAll) elCountAll.textContent = DB.batches.length;
        if (elCountSale) elCountSale.textContent = activeSaleCount;
        if (elCountNotSale) elCountNotSale.textContent = notSaleCount;
    }

    function renderNearExpiryTable() {
        const tbody = document.getElementById('near-expiry-tbody');
        const emptyState = document.getElementById('empty-state');
        if (!tbody) return;

        const searchInput = document.getElementById('search-batch-input');
        const search = searchInput ? searchInput.value.toLowerCase().trim() : '';

        let list = DB.batches;

        if (currentBatchFilter === 'on_sale') {
            list = list.filter(b => b.discount_percent > 0);
        } else if (currentBatchFilter === 'not_sale') {
            list = list.filter(b => b.discount_percent === 0);
        }

        if (search) {
            list = list.filter(b => b.batch_code.toLowerCase().includes(search) || b.product_name.toLowerCase().includes(search));
        }

        if (list.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }
        if (emptyState) emptyState.classList.add('hidden');

        tbody.innerHTML = list.map(b => {
            const isUrgent = b.hours_left <= 24;
            const hoursBadge = isUrgent
                ? `<span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full text-xs font-mono"><i class="fa-solid fa-clock"></i> Còn ${b.hours_left} giờ</span>`
                : `<span class="inline-flex items-center gap-1 font-bold text-amber-800 bg-amber-50 border border-amber-300 px-2.5 py-0.5 rounded-full text-xs font-mono"><i class="fa-regular fa-clock"></i> Còn ${Math.round(b.hours_left / 24 * 10) / 10} ngày</span>`;

            let saleStatusBadge = '';
            const isOnSale = b.discount_percent > 0;

            if (isOnSale) {
                const salePrice = Math.round(b.original_price * (100 - b.discount_percent) / 100);
                saleStatusBadge = `
                    <div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-amber-900 bg-amber-100 border border-amber-300 px-2.5 py-0.5 rounded-full animate-pulse">
                            <i class="fa-solid fa-bolt"></i> Giảm ${b.discount_percent}%
                        </span>
                        <span class="block text-brand-800 font-mono font-bold text-xs mt-0.5">${salePrice.toLocaleString('vi-VN')}đ/kg</span>
                    </div>
                `;
            } else {
                saleStatusBadge = `<span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2.5 py-0.5 rounded-full">Chưa áp dụng</span>`;
            }

            let actionBtns = '';
            if (!isOnSale) {
                actionBtns = `
                    <button onclick="openConfigModal(${b.id})" class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-3 py-1.5 rounded-xl transition shadow-xs active:scale-95 inline-flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-bolt"></i> Kích hoạt Flash Sale
                    </button>
                `;
            } else {
                actionBtns = `
                    <button onclick="openConfigModal(${b.id})" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs px-2.5 py-1.5 rounded-xl transition inline-flex items-center gap-1 cursor-pointer" title="Chỉnh sửa % giảm giá">
                        <i class="fa-solid fa-pen text-[10px]"></i> Sửa %
                    </button>
                    <button onclick="stopCampaign(${b.id})" class="p-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Dừng Flash Sale, về giá gốc">
                        <i class="fa-solid fa-stop text-xs"></i>
                    </button>
                `;
            }

            actionBtns += `
                <button onclick="discardSpoilage(${b.id})" class="p-1.5 rounded-xl border border-gray-200 text-gray-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer" title="Xuất hủy nếu hàng đã hỏng">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            `;

            return `
                <tr class="hover:bg-brand-50/40 transition-colors bg-white border-b border-gray-100">
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5 font-mono font-bold text-brand-800 text-xs sm:text-sm">
                        ${b.batch_code}
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <div class="flex items-center gap-3">
                            <img src="${b.image}" class="w-10 h-10 rounded-xl object-cover border border-gray-200 shadow-2xs shrink-0">
                            <div>
                                <span class="font-bold text-gray-900 text-xs sm:text-sm block">${b.product_name}</span>
                                <span class="text-[10px] text-gray-400">Nông sản sạch</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5 font-mono text-gray-600 text-xs">${b.harvest_date}</td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5">
                        <div class="space-y-0.5">
                            <span class="font-mono text-xs text-gray-800 font-semibold block">${b.expiry_date}</span>
                            ${hoursBadge}
                        </div>
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5 text-center font-mono font-extrabold text-gray-900 text-sm">
                        ${b.stock_qty} kg
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5 text-right font-mono font-bold text-gray-700 text-xs sm:text-sm">
                        ${b.original_price.toLocaleString('vi-VN')}đ
                    </td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-5 text-center">${saleStatusBadge}</td>
                    <td class="py-3.5 sm:py-4 px-4 sm:px-6 text-right space-x-1">${actionBtns}</td>
                </tr>
            `;
        }).join('');
    }

    function openConfigModal(batchId) {
        configuringBatchId = batchId;
        const b = DB.batches.find(x => x.id === batchId);
        if (!b) return;

        document.getElementById('fs-batch-id').value = b.id;
        document.getElementById('mod-batch-code-title').textContent = `Lô: ${b.batch_code}`;
        document.getElementById('fs-product-name').textContent = b.product_name;
        document.getElementById('fs-hours-badge').textContent = b.hours_left <= 24 ? `Còn ${b.hours_left} giờ` : `Còn ${Math.round(b.hours_left / 24 * 10) / 10} ngày`;
        document.getElementById('fs-stock-text').textContent = `${b.stock_qty} kg`;
        document.getElementById('fs-price-original').textContent = `${b.original_price.toLocaleString('vi-VN')}đ/kg`;

        document.getElementById('fs-discount-input').value = b.discount_percent > 0 ? b.discount_percent : 40;
        calculateSalePrice();

        openModal('modal-flash-sale');
    }



    function setDiscountPreset(val) {
        document.getElementById('fs-discount-input').value = val;
        calculateSalePrice();
    }

    function calculateSalePrice() {
        if (!configuringBatchId) return;
        const b = DB.batches.find(x => x.id === configuringBatchId);
        if (!b) return;

        const discount = parseInt(document.getElementById('fs-discount-input').value) || 0;
        const salePrice = Math.round(b.original_price * (100 - discount) / 100);

        document.getElementById('fs-calc-saleprice').textContent = `${salePrice.toLocaleString('vi-VN')}đ`;
        document.getElementById('fs-calc-oldprice').textContent = `${b.original_price.toLocaleString('vi-VN')}đ`;
        document.getElementById('fs-calc-discount-badge').textContent = `-${discount}%`;
    }

    function handleConfirmFlashSale(e) {
        e.preventDefault();
        if (!configuringBatchId) return;
        const b = DB.batches.find(x => x.id === configuringBatchId);
        if (!b) return;

        const discount = parseInt(document.getElementById('fs-discount-input').value) || 0;
        const duration = document.querySelector('input[name="fs_duration"]:checked').value;

        b.discount_percent = discount;
        b.duration_type = duration;

        closeModal('modal-flash-sale');
        renderAllData();

        const salePrice = Math.round(b.original_price * (100 - discount) / 100);

        Swal.fire({
            icon: 'success',
            title: 'Kích hoạt Flash Sale thành công!',
            html: `Lô <b class="font-mono text-brand-800">${b.batch_code}</b> (${b.product_name}) đã được áp dụng mức giảm <b>${discount}%</b>.<br>Giá sale hiển thị trên web: <b class="text-brand-800 font-mono">${salePrice.toLocaleString('vi-VN')}đ/kg</b>.`,
            confirmButtonColor: '#15803d'
        });
    }

    function stopCampaign(batchId) {
        const b = DB.batches.find(x => x.id === batchId);
        if (!b) return;

        Swal.fire({
            title: `Dừng Flash Sale lô này?`,
            text: `Lô ${b.batch_code} sẽ quay về bán giá gốc ${b.original_price.toLocaleString('vi-VN')}đ/kg và ngừng áp dụng giảm giá.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý dừng',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#dc2626'
        }).then(res => {
            if (res.isConfirmed) {
                b.discount_percent = 0;
                b.duration_type = null;
                renderAllData();
                Swal.fire({ icon: 'success', title: 'Đã dừng Flash Sale!', timer: 1500, showConfirmButton: false });
            }
        });
    }

    function discardSpoilage(batchId) {
        const b = DB.batches.find(x => x.id === batchId);
        if (!b) return;

        Swal.fire({
            title: `Lập biên bản xuất hủy lô ${b.batch_code}?`,
            text: `Số lượng tồn: ${b.stock_qty} kg (${b.product_name})`,
            input: 'textarea',
            inputPlaceholder: 'Nhập lý do xuất hủy (VD: Nông sản quá hạn bị hỏng úng thực tế, chuyển làm phân bón hữu cơ)...',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận xuất hủy',
            cancelButtonText: 'Đóng',
            confirmButtonColor: '#dc2626',
            preConfirm: (reason) => {
                if (!reason) Swal.showValidationMessage('Vui lòng nhập lý do xuất hủy kho!');
                return reason;
            }
        }).then(res => {
            if (res.isConfirmed) {
                const idx = DB.batches.findIndex(x => x.id === batchId);
                if (idx !== -1) DB.batches.splice(idx, 1);
                renderAllData();

                Swal.fire({
                    icon: 'success',
                    title: 'Đã lập phiếu xuất hủy kho!',
                    text: `Đã ghi nhận ${b.stock_qty} kg vào nhật ký hao hụt (spoiled_quantity) của nông trại.`,
                    confirmButtonColor: '#15803d'
                });
            }
        });
    }

    function openModal(id) { 
        const m = document.getElementById(id);
        if (m) m.classList.remove('hidden'); 
    }

    function closeModal(id) { 
        const m = document.getElementById(id);
        if (m) m.classList.add('hidden'); 
    }

    // Tương thích cả khi tải trực tiếp lẫn khi chuyển trang wire:navigate
    document.addEventListener('livewire:navigated', () => {
        renderAllData();
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => renderAllData());
    } else {
        renderAllData();
    }
</script>
@endpush
