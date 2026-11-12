<div x-data="{
    tab: 'all',
    modal: null,
    role: 'farm_staff',
    search: '',
    roleFilter: '',
    statusFilter: ''
}" x-on:keydown.escape.window="modal = null"
    class="flex flex-1 min-h-0 flex-col overflow-hidden">

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
                    Quản Lý Tài Khoản
                </h1>
            </div>
        </div>

        <!-- Chỉ báo tình trạng hệ thống -->
        <div class="flex items-center gap-2 shrink-0">
            <span
                class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Hệ thống phân quyền 4 cấp
            </span>
        </div>
    </header>

    <!-- Workspace Viewport -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12">

        <!-- ================= 3. HÀNG 4 THẺ THỐNG KÊ SỐ LƯỢNG TÀI KHOẢN (STAT CARDS) ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5">

            <!-- Thẻ 1: Khách hàng -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider block">Khách
                        Hàng (Customer)</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-purple-700 tracking-tight font-mono"
                            id="stat-cust-count">0</span>
                        <span
                            class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">VIP
                            Points</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">Tài khoản mua nông sản sạch
                    </p>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <!-- Thẻ 2: Nhân viên Nông trại (Farm Staff) -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider block">Kỹ Sư
                        Vườn (Staff)</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-brand-700 tracking-tight font-mono"
                            id="stat-staff-count">05</span>
                        <span
                            class="text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full border border-brand-200">Thực
                            địa</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">Quản lý luống đất, lô & QR
                        code</p>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-leaf"></i>
                </div>
            </div>

            <!-- Thẻ 3: Nhân viên Bán hàng (Sales) -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider block">Bán
                        Hàng & CSKH (Sales)</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-blue-700 tracking-tight font-mono"
                            id="stat-sales-count">04</span>
                        <span
                            class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">Đơn
                            hàng</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">Xử lý đơn, gọi xe, khiếu nại
                    </p>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-headset"></i>
                </div>
            </div>

            <!-- Thẻ 4: Tài khoản tạm khóa -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-rose-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-rose-600 uppercase tracking-wider block">Tài
                        Khoản Tạm Khóa</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-rose-600 tracking-tight font-mono"
                            id="stat-blocked-count">02</span>
                        <span
                            class="text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">Blocked</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-rose-500 font-medium mt-1">Đã chặn quyền đăng nhập</p>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-user-lock"></i>
                </div>
            </div>

        </div>

        <!-- ================= 4. BẢNG DANH SÁCH TÀI KHOẢN VÀ BỘ LỌC ================= -->
        <div
            class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden space-y-4">

            <!-- 4 Tab Chuyển Đổi Nhóm Đối Tượng -->
            <div
                class="flex overflow-x-auto border-b border-slate-100 px-4 sm:px-6 pt-3 gap-6 text-sm no-scrollbar bg-slate-50/50">
                <button type="button" x-on:click="tab = 'all'" id="tab-all"
                    class="pb-3 tab-btn-active transition whitespace-nowrap flex items-center gap-2">
                    <span>Tất Cả Tài Khoản</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full"
                        id="count-all">0</span>
                </button>
                <button type="button" x-on:click="tab = 'internal'" id="tab-internal"
                    class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-id-card-clip text-xs"></i>
                    <span>Đội Ngũ Nhân Sự Nội Bộ</span>
                    <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full"
                        id="count-internal">0</span>
                </button>
                <button type="button" x-on:click="tab = 'customer'" id="tab-customer"
                    class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-basket-shopping text-xs"></i>
                    <span>Khách Hàng (Customer)</span>
                    <span class="text-xs font-bold bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full"
                        id="count-customer">0</span>
                </button>
                <button type="button" x-on:click="tab = 'blocked'" id="tab-blocked"
                    class="pb-3 tab-btn-inactive transition whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-user-slash text-xs"></i>
                    <span>Tài Khoản Bị Khóa (Blocked)</span>
                    <span class="text-xs font-bold bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full"
                        id="count-blocked">0</span>
                </button>
            </div>

            <!-- THANH TÌM KIẾM, BỘ LỌC & NÚT CẤP TÀI KHOẢN -->
            <div
                class="p-3.5 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4 bg-slate-50/30 border-b border-slate-100">
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 flex-1">
                    <!-- Ô tìm kiếm -->
                    <div class="relative w-full sm:w-72 md:w-80">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
                        <input type="text" id="user-search-input" x-model="search"
                            placeholder="Tìm theo Họ tên, Email, Số điện thoại..."
                            class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-slate-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 bg-white font-medium transition-all">
                    </div>

                    <!-- Lọc theo Vai trò (Role) -->
                    <select id="user-filter-role" x-model="roleFilter"
                        class="w-full sm:w-auto bg-white border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-slate-700 outline-none focus:border-brand-600 transition cursor-pointer">
                        <option value="">-- Tất cả vai trò --</option>
                        <option value="admin">Admin (Chủ & Quản trị)</option>
                        <option value="farm_staff">Farm Staff (Kỹ sư vườn/kho)</option>
                        <option value="sales">Sales / CSKH (Bán hàng)</option>
                        <option value="customer">Customer (Khách hàng)</option>
                    </select>

                    <!-- Lọc theo Trạng thái -->
                    <select id="user-filter-status" x-model="statusFilter"
                        class="w-full sm:w-auto bg-white border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-slate-700 outline-none focus:border-brand-600 transition cursor-pointer">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="active">Đang hoạt động (active)</option>
                        <option value="blocked">Tạm khóa (blocked)</option>
                    </select>

                    <button type="button" x-on:click="tab = 'all'; search = ''; roleFilter = ''; statusFilter = ''"
                        class="p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 transition shadow-xs self-end sm:self-auto"
                        title="Đặt lại bộ lọc">

                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>

                <!-- Nút cấp tài khoản mới -->
                <button type="button" x-on:click="modal = 'user'"
                    class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95 shrink-0 whitespace-nowrap">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>+ Cấp Tài Khoản Mới</span>
                </button>
            </div>

            <!-- Bảng Danh Sách Tài Khoản Chính -->
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr
                            class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-slate-100">
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Thành Viên (Họ Tên & ID)</th>
                            <th class="py-3.5 sm:py-4 px-4">Email Đăng Nhập</th>
                            <th class="py-3.5 sm:py-4 px-4">Số Điện Thoại</th>
                            <th class="py-3.5 sm:py-4 px-4">Vai Trò (`role`)</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Điểm Tích Lũy (`reward_points`)</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Trạng Thái (`status`)</th>
                            <th class="py-3.5 sm:py-4 px-4">Ngày Tạo</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium" id="users-tbody">
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                Chưa có dữ liệu tài khoản.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="users-empty-state" class="hidden py-16 text-center">
                <div
                    class="w-16 h-16 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <p class="text-base font-bold text-slate-700">Không tìm thấy tài khoản người dùng nào</p>
                <p class="text-xs text-slate-400 mt-1">Vui lòng thay đổi từ khóa tìm kiếm hoặc điều chỉnh bộ lọc.</p>
            </div>
        </div>

    </div>

    <!-- ================= 5. MODAL: CẤP TÀI KHOẢN / SỬA PHÂN QUYỀN (CÓ CHỈNH ĐIỂM REWARD_POINTS) ================= -->
    <div id="modal-user-form" x-cloak x-show="modal === 'user'" x-on:click.self="modal = null"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-7 max-h-[92vh] overflow-y-auto custom-scrollbar border border-slate-100 space-y-4">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-lg border border-brand-200 shrink-0">
                        <i class="fa-solid fa-id-badge"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 leading-tight"
                            id="modal-user-title">Cấp Tài Khoản Người Dùng Mới</h3>
                        <p class="text-xs text-slate-400">Phân quyền 4 vai trò & thiết lập điểm thưởng</p>
                    </div>
                </div>
                <button x-on:click="modal = null"
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form x-on:submit.prevent="" class="space-y-3.5 text-xs sm:text-sm">
                <input type="hidden" id="uf-id">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Họ và tên <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="uf-name" required placeholder="VD: KS. Nguyễn Văn Bình"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-xs sm:text-sm outline-none focus:border-brand-600 focus:bg-white font-semibold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Email đăng nhập <span
                                class="text-red-500">*</span></label>
                        <input type="email" id="uf-email" required placeholder="vanbinh.farm@greenfarm.vn"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono outline-none focus:border-brand-600 focus:bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số điện thoại <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="uf-phone" required placeholder="0918.223.344"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono outline-none focus:border-brand-600 focus:bg-white font-bold">
                    </div>
                </div>

                <!-- Mật khẩu khởi tạo (chỉ hiện khi tạo mới) -->
                <div id="uf-password-wrapper">
                    <div class="flex justify-between items-center mb-1">
                        <label class="font-bold text-slate-700">Mật khẩu khởi tạo <span
                                class="text-red-500">*</span></label>
                        <button type="button" disabled class="text-[11px] text-brand-700 hover:underline font-bold">
                            <i class="fa-solid fa-wand-magic-sparkles mr-0.5"></i> Tạo ngẫu nhiên
                        </button>
                    </div>
                    <input type="text" id="uf-password" placeholder="Nhập ít nhất 8 ký tự..."
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono outline-none focus:border-brand-600 focus:bg-white">
                </div>

                <!-- Phân quyền Vai trò -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Vai trò phân quyền (`role`) <span
                            class="text-red-500">*</span></label>
                    <select id="uf-role" required x-model="role"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold outline-none focus:border-brand-600 cursor-pointer">
                        <option value="farm_staff">Farm Staff (Kỹ sư vườn - Luống đất & Kho FEFO)</option>
                        <option value="sales">Sales / CSKH (Nhân viên Bán hàng - Đơn hàng & Khiếu nại)</option>
                        <option value="admin">Admin Master (Chủ Nông Trại - Toàn quyền hệ thống)</option>
                        <option value="customer">Customer (Khách hàng mua sắm)</option>
                    </select>
                </div>

                <!-- Ô CHỈNH SỬA ĐIỂM TÍCH LŨY DÀNH CHO CUSTOMER -->
                <div id="uf-points-wrapper" x-cloak x-show="role === 'customer'">
                    <label class="block font-bold text-purple-900 mb-1">
                        Điểm thưởng tích lũy (`reward_points`) của Khách Hàng:
                    </label>
                    <div class="relative">
                        <input type="number" id="uf-reward-points" min="0" step="1" value="0"
                            class="w-full bg-purple-50/50 border border-purple-200 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-purple-900 outline-none focus:border-purple-600 focus:bg-white">
                        <span
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono text-purple-600 font-bold">pts</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-0.5">Admin có thể điều chỉnh cộng/trừ điểm thưởng trực tiếp
                        cho khách hàng</p>
                </div>

                <!-- Gợi ý phạm vi phân quyền -->
                <div id="uf-role-permission-hint"
                    class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl text-emerald-950 text-xs flex items-start gap-2">
                    <i class="fa-solid fa-shield-halved text-brand-700 mt-0.5 shrink-0"></i>
                    <span id="uf-permission-text" class="leading-relaxed"
                        x-text="{
        farm_staff: 'Quản lý luống đất, nhật ký canh tác và kho nông sản.',
        sales: 'Quản lý đơn hàng, khiếu nại và chăm sóc khách hàng.',
        admin: 'Quản trị tài khoản và cấu hình hệ thống.',
        customer: 'Mua sắm và tích lũy điểm thưởng.'
    }[role]">
                    </span>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" x-on:click="modal = null"
                        class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">Hủy</button>
                    <button type="submit" disabled
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95 flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Lưu Tài Khoản
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= 6. MODAL: ĐẶT LẠI MẬT KHẨU NHANH ================= -->
    <div id="modal-reset-password" x-cloak x-show="modal === 'password'" x-on:click.self="modal = null"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-sm">Đặt Lại Mật Khẩu Nhanh</h4>
                        <p class="text-[11px] text-slate-400" id="rp-user-name">Người dùng: ---</p>
                    </div>
                </div>
                <button x-on:click="modal = null" class="text-slate-400 hover:text-slate-600"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>

            <form x-on:submit.prevent="" class="space-y-3.5 text-xs">
                <input type="hidden" id="rp-user-id">

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="font-bold text-slate-700">Mật khẩu mới <span
                                class="text-red-500">*</span></label>
                        <button type="button" disabled class="text-[11px] text-brand-700 hover:underline font-bold">
                            Tạo pass mẫu: 123456@GreenFarm
                        </button>
                    </div>
                    <input type="text" id="rp-new-password" required placeholder="Nhập mật khẩu mới..."
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-900 outline-none focus:border-brand-600 focus:bg-white">
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="rp-require-change" checked
                            class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500">
                        <span class="text-slate-700 font-semibold text-xs">Bắt buộc người dùng đổi mật khẩu ở lần đăng
                            nhập tiếp theo</span>
                    </label>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" x-on:click="modal = null"
                        class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">Hủy</button>
                    <button type="submit" disabled
                        class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-5 py-2 rounded-xl shadow-sm transition active:scale-95">Xác
                        Nhận Đổi Mật Khẩu</button>
                </div>
            </form>
        </div>
    </div>
</div>
