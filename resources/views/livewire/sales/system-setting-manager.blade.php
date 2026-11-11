<div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">
    <!-- ================= 1. HEADER TOPBAR ================= -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">
                    Cài Đặt Hệ Thống
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Tham số đang hoạt động
            </span>
        </div>
    </header>

    <!-- ================= 2. NỘI DUNG CUỘN ================= -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12 w-full">

        <!-- ================= THANH TAB CÁC NHÓM CẤU HÌNH ================= -->
        <div class="w-full bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden">
            <div class="flex overflow-x-auto border-b border-gray-100 px-4 sm:px-6 pt-3 gap-6 sm:gap-8 text-sm no-scrollbar bg-gray-50/40 w-full">
                <button type="button" wire:click="switchTab('shipping')"
                    class="pb-3 {{ $activeTab === 'shipping' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>Vận Chuyển</span>
                </button>
                <button type="button" wire:click="switchTab('loyalty')"
                    class="pb-3 {{ $activeTab === 'loyalty' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-coins"></i>
                    <span>Điểm Thưởng Thân Thiết</span>
                </button>
                <button type="button" wire:click="switchTab('subscription')"
                    class="pb-3 {{ $activeTab === 'subscription' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-crown"></i>
                    <span>Gói Rau Định Kỳ VIP</span>
                </button>
                <button type="button" wire:click="switchTab('contact')"
                    class="pb-3 {{ $activeTab === 'contact' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-store"></i>
                    <span>Thông Tin &amp; Tọa Độ Farm</span>
                </button>
            </div>
        </div>

        <!-- ================= TAB 1: VẬN CHUYỂN (shipping) ================= -->
        @if ($activeTab === 'shipping')
        <form wire:submit="saveGroup('shipping')" class="w-full space-y-4 sm:space-y-6">
            <div class="w-full bg-white p-5 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft space-y-6">
                <div class="border-b border-gray-100 pb-3.5">
                    <h3 class="font-extrabold text-gray-900 text-sm sm:text-base flex items-center gap-2">
                        <i class="fa-solid fa-route text-brand-700"></i>
                        <span>Phạm Vi Giao Hàng Hỏa Tốc Theo Khoảng Cách (km)</span>
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">Kiểm tra khoảng cách từ tọa độ nông trại đến địa chỉ giao hàng của khách</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs sm:text-sm">
                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-1.5">
                        <label class="block font-bold text-slate-700">
                            Bán kính giao tối đa từ Farm <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="max_shipping_distance_km" min="1" max="500" step="any"
                                class="w-full bg-white border border-slate-200 rounded-xl pl-3.5 pr-12 py-2.5 font-mono font-bold text-slate-900 outline-none focus:border-brand-600 text-sm">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono text-slate-400">km</span>
                        </div>
                        <span class="text-[11px] text-slate-500 block">Đơn vượt quá bán kính này sẽ bị từ chối giao hỏa tốc</span>
                        @error('max_shipping_distance_km')
                            <span class="text-xs font-semibold text-rose-600 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveGroup"
                        class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-7 py-3 rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer disabled:opacity-50">
                        <i wire:loading.remove wire:target="saveGroup" class="fa-solid fa-floppy-disk"></i>
                        <i wire:loading wire:target="saveGroup" class="fa-solid fa-spinner fa-spin"></i>
                        <span>Lưu Cấu Hình Vận Chuyển</span>
                    </button>
                </div>
            </div>
        </form>
        @endif

        <!-- ================= TAB 2: ĐIỂM THƯỞNG THÂN THIẾT (loyalty) ================= -->
        @if ($activeTab === 'loyalty')
        <form wire:submit="saveGroup('loyalty')" class="w-full space-y-4 sm:space-y-6">
            <div class="w-full bg-white p-5 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft space-y-6">
                <div class="border-b border-gray-100 pb-3.5">
                    <h3 class="font-extrabold text-gray-900 text-sm sm:text-base flex items-center gap-2">
                        <i class="fa-solid fa-gift text-purple-600"></i>
                        <span>Chính Sách Tích Điểm &amp; Đổi Thưởng Thành Viên (Loyalty)</span>
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">Áp dụng cho khách hàng khi tích điểm và dùng điểm trừ tiền lúc thanh toán</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-xs sm:text-sm">
                    <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-100 space-y-1.5">
                        <label class="block font-bold text-slate-700">
                            Mức chi tiêu tích 1 điểm <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="spending_per_point" min="1000" step="1"
                                class="w-full bg-white border border-slate-200 rounded-xl pl-3.5 pr-14 py-2.5 font-mono font-bold text-slate-900 outline-none focus:border-brand-600 text-sm">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono text-slate-400">VNĐ</span>
                        </div>
                        <span class="text-[11px] text-slate-500 block">VD: 10.000đ chi tiêu tích được 1 điểm</span>
                        @error('spending_per_point')
                            <span class="text-xs font-semibold text-rose-600 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-100 space-y-1.5">
                        <label class="block font-bold text-slate-700">
                            Giá trị quy đổi khi trừ tiền <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="value_per_point" min="1" step="1"
                                class="w-full bg-white border border-slate-200 rounded-xl pl-3.5 pr-20 py-2.5 font-mono font-bold text-purple-700 outline-none focus:border-brand-600 text-sm">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono text-slate-400">VNĐ / 1 pt</span>
                        </div>
                        <span class="text-[11px] text-slate-500 block">VD: 1 điểm quy đổi giảm 100 VNĐ</span>
                        @error('value_per_point')
                            <span class="text-xs font-semibold text-rose-600 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-100 space-y-1.5">
                        <label class="block font-bold text-slate-700">
                            Giới hạn trừ điểm tối đa mỗi đơn <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="max_point_discount_percent" min="1" max="100" step="any"
                                class="w-full bg-white border border-slate-200 rounded-xl pl-3.5 pr-10 py-2.5 font-mono font-bold text-slate-900 outline-none focus:border-brand-600 text-sm">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono text-slate-400">%</span>
                        </div>
                        <span class="text-[11px] text-slate-500 block">Trần % giảm giá tối đa trên tiền hàng</span>
                        @error('max_point_discount_percent')
                            <span class="text-xs font-semibold text-rose-600 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveGroup"
                        class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-7 py-3 rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer disabled:opacity-50">
                        <i wire:loading.remove wire:target="saveGroup" class="fa-solid fa-floppy-disk"></i>
                        <i wire:loading wire:target="saveGroup" class="fa-solid fa-spinner fa-spin"></i>
                        <span>Lưu Cấu Hình Điểm Thưởng</span>
                    </button>
                </div>
            </div>
        </form>
        @endif

        <!-- ================= TAB 3: GÓI RAU ĐỊNH KỲ VIP (subscription) ================= -->
        @if ($activeTab === 'subscription')
        <form wire:submit="saveGroup('subscription')" class="w-full space-y-4 sm:space-y-6">
            <div class="w-full bg-white p-5 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft space-y-6">
                <div class="border-b border-gray-100 pb-3.5">
                    <h3 class="font-extrabold text-gray-900 text-sm sm:text-base flex items-center gap-2">
                        <i class="fa-solid fa-crown text-amber-500"></i>
                        <span>Cấu Hình Gói Rau Định Kỳ VIP</span>
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">Điều chỉnh trạng thái mở bán, tên gói, giá gói và định lượng tối đa mỗi đợt giao</p>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200/80 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Mở bán Gói Rau VIP trên website</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Ẩn hoặc hiện mục đăng ký gói VIP đối với khách hàng</p>
                    </div>
                    <label class="inline-flex items-center gap-2.5 cursor-pointer shrink-0">
                        <input type="checkbox" wire:model="vip_subscription_enabled" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-brand-600 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                        <span class="text-xs font-bold {{ $vip_subscription_enabled ? 'text-brand-700' : 'text-slate-500' }}">
                            {{ $vip_subscription_enabled ? 'Đang bật' : 'Đang tắt' }}
                        </span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-xs sm:text-sm">
                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-1.5">
                        <label class="block font-bold text-slate-700">
                            Tên gói VIP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="vip_package_name"
                            class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 font-semibold text-slate-900 outline-none focus:border-brand-600 text-sm">
                        @error('vip_package_name')
                            <span class="text-xs font-semibold text-rose-600 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-1.5">
                        <label class="block font-bold text-slate-700">
                            Giá gói VIP <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="vip_package_price" min="1000" step="1"
                                class="w-full bg-white border border-slate-200 rounded-xl pl-3.5 pr-14 py-2.5 font-mono font-bold text-brand-700 outline-none focus:border-brand-600 text-sm">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono text-slate-400">VNĐ</span>
                        </div>
                        @error('vip_package_price')
                            <span class="text-xs font-semibold text-rose-600 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-1.5">
                        <label class="block font-bold text-slate-700">
                            Định lượng tối đa mỗi đợt giao <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="vip_delivery_max_weight" min="0.5" max="50" step="any"
                                class="w-full bg-white border border-slate-200 rounded-xl pl-3.5 pr-10 py-2.5 font-mono font-bold text-slate-900 outline-none focus:border-brand-600 text-sm">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono text-slate-400">kg</span>
                        </div>
                        <span class="text-[11px] text-slate-500 block">Tổng cân nặng tối đa khách chọn cho mỗi đợt giao</span>
                        @error('vip_delivery_max_weight')
                            <span class="text-xs font-semibold text-rose-600 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveGroup"
                        class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-7 py-3 rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer disabled:opacity-50">
                        <i wire:loading.remove wire:target="saveGroup" class="fa-solid fa-floppy-disk"></i>
                        <i wire:loading wire:target="saveGroup" class="fa-solid fa-spinner fa-spin"></i>
                        <span>Lưu Cấu Hình Gói VIP</span>
                    </button>
                </div>
            </div>
        </form>
        @endif

        <!-- ================= TAB 4: THÔNG TIN & TỌA ĐỘ FARM (contact) ================= -->
        @if ($activeTab === 'contact')
        <form wire:submit="saveGroup('contact')" class="w-full space-y-4 sm:space-y-6">
            <div class="w-full bg-white p-5 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft space-y-6">
                <div class="border-b border-gray-100 pb-3.5">
                    <h3 class="font-extrabold text-gray-900 text-sm sm:text-base flex items-center gap-2">
                        <i class="fa-solid fa-store text-brand-700"></i>
                        <span>Thông Tin Liên Hệ &amp; Tọa Độ Kho/Nông Trại</span>
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">Thông tin liên hệ và tọa độ gốc dùng để tính khoảng cách giao hàng</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 text-xs sm:text-sm">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Hotline CSKH / Đặt hàng <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="hotline"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono font-bold text-slate-900 outline-none focus:border-brand-600 focus:bg-white">
                        @error('hotline')
                            <span class="text-xs font-semibold text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Email liên hệ chính thức <span class="text-red-500">*</span>
                        </label>
                        <input type="email" wire:model="email"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono text-slate-800 outline-none focus:border-brand-600 focus:bg-white">
                        @error('email')
                            <span class="text-xs font-semibold text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Vĩ độ kho/nông trại (Latitude) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="farm_latitude" step="any"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono text-slate-800 outline-none focus:border-brand-600 focus:bg-white">
                        @error('farm_latitude')
                            <span class="text-xs font-semibold text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Kinh độ kho/nông trại (Longitude) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="farm_longitude" step="any"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono text-slate-800 outline-none focus:border-brand-600 focus:bg-white">
                        @error('farm_longitude')
                            <span class="text-xs font-semibold text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Địa chỉ kho / nông trại <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="farm_address"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 outline-none focus:border-brand-600 focus:bg-white">
                        @error('farm_address')
                            <span class="text-xs font-semibold text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveGroup"
                        class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-7 py-3 rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer disabled:opacity-50">
                        <i wire:loading.remove wire:target="saveGroup" class="fa-solid fa-floppy-disk"></i>
                        <i wire:loading wire:target="saveGroup" class="fa-solid fa-spinner fa-spin"></i>
                        <span>Lưu Thông Tin Nông Trại</span>
                    </button>
                </div>
            </div>
        </form>
        @endif

    </div>
</div>

