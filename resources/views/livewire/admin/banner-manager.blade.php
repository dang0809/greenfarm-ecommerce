<div x-data="{ tab: 'banners', modal: null }" x-on:keydown.escape.window="modal = null"
    class="flex flex-1 min-h-0 flex-col overflow-hidden">

    <!-- Header Topbar Responsive: ĐÃ BỎ TIÊU ĐỀ DÀI VÀ BỎ NÚT TRÊN HEADER XUỐNG -->
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
                    Nội Dung & Banner
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span
                class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Truyền thông Website
            </span>
        </div>
    </header>

    <!-- Workspace Viewport -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12">

        <!-- ================= 3. THANH TAB CHUYỂN ĐỔI 3 PHÂN HỆ NỘI DUNG (ĐÃ BỎ CÁC CHỮ TRONG NGOẶC) ================= -->
        <div class="w-full bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div
                class="flex overflow-x-auto border-b border-slate-100 px-4 sm:px-6 pt-3 gap-6 sm:gap-8 text-sm no-scrollbar bg-slate-50/50 w-full">
                <button type="button" id="tab-btn-banners" x-on:click="tab = 'banners'"
                    x-bind:class="tab === 'banners'
                        ?
                        'tab-btn-active' :
                        'tab-btn-inactive'"
                    class="pb-3 transition whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-images"></i>
                    <span>Banner Quảng Cáo Trang Chủ</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full"
                        id="badge-banners-count">0</span>
                </button>
                <button type="button" id="tab-btn-pages" x-on:click="tab = 'pages'"
                    x-bind:class="tab === 'pages'
                        ?
                        'tab-btn-active' :
                        'tab-btn-inactive'"
                    class="pb-3 transition whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Trang Tĩnh & Câu Chuyện Thương Hiệu</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full"
                        id="badge-pages-count">0</span>
                </button>
                <button type="button" id="tab-btn-farm_images" x-on:click="tab = 'farm_images'"
                    x-bind:class="tab === 'farm_images'
                        ?
                        'tab-btn-active' :
                        'tab-btn-inactive'"
                    class="pb-3 transition whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-photo-film"></i>
                    <span>Bộ Sưu Tập Ảnh Nông Trại</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full"
                        id="badge-farm-images-count">0</span>
                </button>
            </div>
        </div>

        <!-- ================= TAB 1: BANNER QUẢNG CÁO TRANG CHỦ ================= -->
        <div id="tab-pane-banners" x-show="tab === 'banners'" class="space-y-4">

            <!-- BẢNG DANH SÁCH BANNERS KÈM THANH TÁC VỤ (NÚT THÊM MỚI ĐƯA XUỐNG ĐÂY) -->
            <div
                class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden space-y-4">
                <div
                    class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900">Danh Sách Banner Quảng Cáo</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Sắp xếp theo thứ tự ưu tiên và kiểm soát bật/tắt hiển
                            thị trực tiếp</p>
                    </div>

                    <!-- Nút Thêm Mới Banner đưa xuống đây -->
                    <div class="flex items-center gap-2.5">
                        <span
                            class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-3 py-2 rounded-xl hidden md:inline font-mono">
                            Chuẩn ảnh: 1920 &times; 600 px
                        </span>
                        <button type="button" x-on:click="modal = 'banner'"
                            class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-2 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-plus"></i>
                            <span>Thêm Banner Mới</span>
                        </button>
                    </div>
                </div>

                <div class="w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                        <thead>
                            <tr
                                class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                                <th class="py-3 px-4 pl-5">Hình Ảnh</th>
                                <th class="py-3 px-4">Tiêu Đề Banner</th>
                                <th class="py-3 px-4">Mô Tả Phụ</th>
                                <th class="py-3 px-4">Nút Bấm</th>
                                <th class="py-3 px-4 font-mono">Đường Dẫn Liên Kết</th>
                                <th class="py-3 px-4 text-center">Thứ Tự</th>
                                <th class="py-3 px-4 text-center">Trạng Thái</th>
                                <th class="py-3 px-4 pr-6 text-right">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium" id="banners-tbody">
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    Chưa có banner.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 2: TRANG TĨNH & CÂU CHUYỆN THƯƠNG HIỆU ================= -->
        <div id="tab-pane-pages" x-cloak x-show="tab === 'pages'" class="space-y-4">
            <div
                class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden space-y-4">
                <div
                    class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900">Các Trang Giới Thiệu & Chính Sách
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Quản lý nội dung bài viết "Câu chuyện thương hiệu",
                            "Chính sách đổi trả", "Quy trình canh tác"</p>
                    </div>

                    <!-- Nút Thêm Trang Mới đưa xuống đây -->
                    <button type="button" x-on:click="modal = 'page'"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-2 active:scale-95 cursor-pointer self-start sm:self-auto">
                        <i class="fa-solid fa-plus"></i>
                        <span>+ Thêm Trang Mới</span>
                    </button>
                </div>

                <div class="w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                        <thead>
                            <tr
                                class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                                <th class="py-3.5 px-4 pl-5">Tiêu Đề Trang</th>
                                <th class="py-3.5 px-4 font-mono">Đường Dẫn URL</th>
                                <th class="py-3.5 px-4">Nội Dung Tóm Tắt</th>
                                <th class="py-3.5 px-4 font-mono">Ngày Cập Nhật</th>
                                <th class="py-3.5 px-4 pr-6 text-right">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium" id="pages-tbody">
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    Chưa có trang nội dung.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 3: BỘ SƯU TẬP ẢNH NÔNG TRẠI ================= -->
        <div id="tab-pane-farm_images" x-cloak x-show="tab === 'farm_images'" class="space-y-4">
            <div
                class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft p-5 sm:p-6 space-y-4">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-camera text-brand-700"></i>
                            <span>Thư Viện Ảnh Thực Tế Nông Trại</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Hình ảnh thực địa xuất hiện ở mục "Về Chúng Tôi" trên
                            website</p>
                    </div>

                    <!-- Nút Tải Ảnh Vườn -->
                    <button type="button" x-on:click="modal = 'farm-image'"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5 self-start sm:self-auto active:scale-95 cursor-pointer">
                        <i class="fa-solid fa-upload"></i>
                        <span>+ Tải Thêm Ảnh Vườn</span>
                    </button>
                </div>

                <!-- Lưới Ảnh Gallery -->
                <div id="farm-gallery-grid"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                    <p class="col-span-full py-12 text-center text-sm text-slate-400">
                        Chưa có ảnh nông trại.
                    </p>
                </div>
            </div>
        </div>

    </div>
    <!-- MODAL 1: THÊM / SỬA BANNER TRANG CHỦ -->
    <div id="modal-banner-form" x-cloak x-show="modal === 'banner'" x-on:click.self="modal = null"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-7 max-h-[92vh] overflow-y-auto custom-scrollbar border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center text-sm font-bold border border-brand-200">
                        <i class="fa-solid fa-images"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-base" id="modal-banner-title">Thêm Banner Quảng
                            Cáo Mới</h4>
                        <p class="text-[11px] text-slate-400">Cập nhật vào hệ thống banner trang chủ</p>
                    </div>
                </div>
                <button type="button" x-on:click="modal = null" class="text-slate-400 hover:text-slate-600"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>

            <form x-on:submit.prevent="" class="space-y-3.5 text-xs">
                <input type="hidden" id="bn-id">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tiêu đề banner <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="bn-title" required
                        placeholder="VD: Nông Sản Sạch Minh Bạch Nguồn Gốc Blockchain"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-brand-600 font-bold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Đoạn mô tả ngắn</label>
                    <textarea id="bn-desc" rows="2"
                        placeholder="VD: Canh tác tự nhiên tại thung lũng Đạ Ròn, cam kết không hóa chất độc hại..."
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-2.5 outline-none focus:border-brand-600 resize-none font-medium"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chữ trên nút bấm</label>
                        <input type="text" id="bn-btn-text" placeholder="VD: Khám Phá Ngay, Mua Ngay"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-brand-600 font-semibold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Đường dẫn liên kết</label>
                        <input type="text" id="bn-link" placeholder="/shop, /subscription"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono outline-none focus:border-brand-600">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thứ tự hiển thị</label>
                        <input type="number" id="bn-sort" min="1" value="1" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold outline-none focus:border-brand-600">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Trạng thái phát hành</label>
                        <select id="bn-status"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-semibold outline-none focus:border-brand-600">
                            <option value="active">Hiển thị</option>
                            <option value="inactive">Tạm ẩn</option>
                        </select>
                    </div>
                </div>

                <!-- Tải ảnh Banner -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Hình ảnh banner (Khuyên dùng: 1920x600
                        px)</label>
                    <input type="file" id="bn-file" accept="image/*"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" x-on:click="modal = null"
                        class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">Hủy</button>
                    <button type="submit" disabled
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95">Lưu
                        Banner</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: THÊM / SỬA TRANG TĨNH -->
    <div id="modal-page-form" x-cloak x-show="modal === 'page'" x-on:click.self="modal = null"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-2xl shadow-2xl p-5 sm:p-7 max-h-[92vh] overflow-y-auto custom-scrollbar border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-sm font-bold border border-purple-200">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-base" id="modal-page-title">Chỉnh Sửa Trang Tĩnh
                        </h4>
                        <p class="text-[11px] text-slate-400">Quản trị các trang giới thiệu và chính sách</p>
                    </div>
                </div>
                <button type="button" x-on:click="modal = null" class="text-slate-400 hover:text-slate-600"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>

            <form x-on:submit.prevent="" class="space-y-3.5 text-xs">
                <input type="hidden" id="pg-id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tiêu đề trang <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="pg-title" required placeholder="VD: Câu Chuyện Thương Hiệu"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-brand-600 font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Đường dẫn URL <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="pg-slug" required placeholder="cau-chuyen-thuong-hieu"
                            class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2 font-mono outline-none text-slate-600">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nội dung chi tiết bài viết <span
                            class="text-red-500">*</span></label>
                    <textarea id="pg-content" rows="8" required
                        placeholder="Nhập văn bản giới thiệu, cam kết chất lượng, quy định chính sách..."
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-3 font-mono text-xs outline-none focus:border-brand-600 focus:bg-white resize-none"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" x-on:click="modal = null"
                        class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">Hủy</button>
                    <button type="submit" disabled
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95">Lưu
                        Nội Dung Trang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: THÊM / SỬA ẢNH NÔNG TRẠI -->
    <div id="modal-farm-image-form" x-cloak x-show="modal === 'farm-image'" x-on:click.self="modal = null"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-camera text-brand-700 text-lg"></i>
                    <h4 class="font-extrabold text-slate-900 text-sm" id="modal-farm-image-title">Tải Ảnh Nông Trại
                        Mới</h4>
                </div>
                <button type="button" x-on:click="modal = null" class="text-slate-400 hover:text-slate-600"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>

            <form x-on:submit.prevent="" class="space-y-3.5 text-xs">
                <input type="hidden" id="fi-id">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Chú thích bức ảnh <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="fi-caption" required
                        placeholder="VD: Hệ thống tưới nhỏ giọt công nghệ Israel"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:border-brand-600">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Thứ tự sắp xếp</label>
                    <input type="number" id="fi-sort" min="1" value="1" required
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold outline-none focus:border-brand-600">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Chọn ảnh thực tế hiện trường</label>
                    <input type="file" id="fi-file" accept="image/*"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" x-on:click="modal = null"
                        class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">Hủy</button>
                    <button type="submit" disabled
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95">Lưu
                        Ảnh Vườn</button>
                </div>
            </form>
        </div>
    </div>
</div>
