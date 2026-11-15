<div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden bg-cream">
    <!-- ================= 1. HEADER CHÍNH ================= -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">
                    Quản Lý Hợp Đồng Gói VIP
                </h1>
            </div>
        </div>


    </header>

    <!-- ================= 2. KHU VỰC NỘI DUNG CHÍNH (VỪA VẶN MÀN HÌNH, KHÔNG TRÀN) ================= -->
    <div class="flex-1 flex flex-col min-h-0 p-3 sm:p-5 lg:p-6 overflow-hidden">
        
        <!-- KHỐI BẢNG DỮ LIỆU & TOOLBAR -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft flex-1 flex flex-col min-h-0 overflow-hidden">
            
            <!-- Hàng Tab Lọc Trạng Thái -->
            <div class="flex overflow-x-auto no-scrollbar border-b border-gray-100 px-4 sm:px-6 pt-3 gap-6 text-sm bg-gray-50/50 shrink-0">
                <button type="button" wire:click="setStatusFilter('all')" class="pb-3 {{ $statusFilter === 'all' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer font-bold">
                    <span>Tất cả</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusFilter === 'all' ? 'bg-brand-100 text-brand-800' : 'bg-slate-200 text-slate-700' }}">{{ $statusCounts['all'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('active')" class="pb-3 {{ $statusFilter === 'active' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer font-bold">
                    <span>Đang hoạt động</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusFilter === 'active' ? 'bg-emerald-200 text-emerald-900 font-extrabold' : 'bg-emerald-100 text-emerald-800' }}">{{ $statusCounts['active'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('today')" class="pb-3 {{ $statusFilter === 'today' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer font-bold" title="Các hợp đồng có đợt giao vào hôm nay">
                    <span>Lịch giao hôm nay</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusFilter === 'today' ? 'bg-amber-200 text-amber-900 font-extrabold' : 'bg-amber-100 text-amber-800' }}">{{ $statusCounts['today'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('completed')" class="pb-3 {{ $statusFilter === 'completed' ? 'tab-btn-active' : 'tab-btn-inactive' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer font-bold">
                    <span>Đã hoàn thành</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusFilter === 'completed' ? 'bg-slate-300 text-slate-800 font-extrabold' : 'bg-gray-100 text-gray-600' }}">{{ $statusCounts['completed'] }}</span>
                </button>
            </div>

            <!-- Thanh Tìm Kiếm -->
            <div class="p-3.5 sm:p-4 flex items-center justify-between gap-3 border-b border-gray-100 shrink-0">
                <div class="relative w-full max-w-md">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm theo mã đơn hàng (#1794...)" class="w-full pl-10 pr-9 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                    @if(!empty($search))
                        <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1" title="Xóa tìm kiếm">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Bảng Dữ Liệu Hợp Đồng (Scroll mượt, không tràn màn hình) -->
            <div class="flex-1 overflow-x-auto overflow-y-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-xs shadow-2xs">
                        <tr class="text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-gray-100">
                            <th class="py-3 px-3 sm:px-4 whitespace-nowrap">Mã Đơn VIP</th>
                            <th class="py-3 px-3 sm:px-4 min-w-[170px] max-w-[240px]">Khách Hàng & Nơi Nhận</th>
                            <th class="py-3 px-3 sm:px-4 whitespace-nowrap">Lịch Giao Cố Định</th>
                            <th class="py-3 px-3 sm:px-4 whitespace-nowrap">Tiến Độ Nhận Rau</th>
                            <th class="py-3 px-3 sm:px-4 whitespace-nowrap">Ngày Giao Kế Tiếp</th>
                            <th class="py-3 px-3 sm:px-4 text-center whitespace-nowrap">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @forelse($subscriptions as $sub)
                            @php
                                $deliveredCount = $sub->deliverySchedules->where('status', 'delivered')->count();
                                $totalDeliveries = 8;
                                $progressPercent = round(($deliveredCount / $totalDeliveries) * 100);
                                $nextSchedule = $sub->deliverySchedules->where('status', '!=', 'delivered')->sortBy('delivery_order')->first();
                                $orderCode = $vipOrderCodes[$sub->user_id] ?? $sub->id;
                            @endphp
                            <tr class="hover:bg-brand-50/30 transition-colors">
                                <!-- Mã Đơn VIP (Click để xem chi tiết) -->
                                <td class="py-3 px-3 sm:px-4 whitespace-nowrap">
                                    <button type="button" 
                                            wire:click="openDetail({{ $sub->id }})" 
                                            class="font-mono font-bold text-brand-700 hover:text-brand-900 hover:underline flex items-center gap-1.5 cursor-pointer text-xs sm:text-sm group text-left transition" 
                                            title="Bấm để xem chi tiết 8 đợt giao">
                                        <span>#{{ ltrim($orderCode, '#') }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-gray-400 group-hover:text-brand-700 transition-colors"></i>
                                    </button>
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                        {{ $sub->created_at?->format('d/m/Y') }}
                                    </div>
                                </td>

                                <!-- Khách Hàng & Nơi Nhận (Read-Only) -->
                                <td class="py-3 px-3 sm:px-4 min-w-[170px] max-w-[240px]">
                                    <div class="font-bold text-gray-900 text-xs sm:text-sm truncate" title="{{ $sub->recipient_name }}">
                                        {{ $sub->recipient_name }}
                                    </div>
                                    <div class="text-[11px] sm:text-xs font-mono text-gray-500 mt-0.5">
                                        {{ $sub->recipient_phone }}
                                    </div>
                                    <div class="text-[11px] text-gray-400 truncate mt-0.5 flex items-center gap-1" title="{{ $sub->recipient_address }}">
                                        <i class="fa-solid fa-location-dot text-[9px] text-gray-300 shrink-0"></i>
                                        <span class="truncate">{{ $sub->recipient_address }}</span>
                                    </div>
                                </td>

                                <!-- Lịch Giao Cố Định -->
                                <td class="py-3 px-3 sm:px-4 whitespace-nowrap">
                                    @if(!empty($sub->delivery_days))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            <i class="fa-solid fa-calendar-days text-[10px]"></i>
                                            <span>{{ $sub->delivery_days }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                            <i class="fa-regular fa-clock text-[10px]"></i>
                                            <span>Chưa đặt</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Tiến Độ Nhận Rau -->
                                <td class="py-3 px-3 sm:px-4 whitespace-nowrap">
                                    <div class="space-y-1 w-28 sm:w-36">
                                        <div class="flex items-center justify-between text-xs font-mono font-bold">
                                            <span class="text-brand-800">{{ $deliveredCount }}/{{ $totalDeliveries }} đợt</span>
                                            <span class="text-gray-400 text-[10px] sm:text-[11px]">{{ $progressPercent }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 h-1.5 sm:h-2 rounded-full overflow-hidden">
                                            <div class="bg-brand-600 h-full rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Ngày Giao Kế Tiếp (Sửa lỗi logic khi chưa đặt ngày) -->
                                <td class="py-3 px-3 sm:px-4 whitespace-nowrap">
                                    @if($deliveredCount >= $totalDeliveries)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i> Đã giao đủ 8 đợt
                                        </span>
                                    @elseif($nextSchedule)
                                        <div class="font-mono font-bold text-gray-900 text-xs sm:text-sm">
                                            {{ $nextSchedule->delivery_date?->format('d/m/Y') }}
                                        </div>
                                        <div class="text-[10px] text-amber-700 font-bold mt-0.5 flex items-center gap-1">
                                            <span>Đợt {{ $nextSchedule->delivery_order }}/8</span>
                                            @if($nextSchedule->status === 'processing')
                                                <span class="inline-flex items-center gap-0.5 text-[9px] bg-amber-100 text-amber-800 px-1 py-0.2 rounded font-semibold">
                                                    <span class="w-1 h-1 rounded-full bg-amber-500 animate-pulse"></span>
                                                    Hôm nay
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                                            <i class="fa-regular fa-clock text-[10px]"></i> Chờ khách chọn ngày
                                        </span>
                                    @endif
                                </td>

                                <!-- Trạng Thái Hợp Đồng -->
                                <td class="py-3 px-3 sm:px-4 text-center whitespace-nowrap">
                                    @if($sub->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Đang hoạt động
                                        </span>
                                    @elseif($sub->status === 'completed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <i class="fa-solid fa-flag-checkered text-[10px]"></i>
                                            Đã hoàn thành
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                            {{ ucfirst($sub->status) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center text-gray-400">
                                    <div class="w-14 h-14 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-2 text-xl">
                                        <i class="fa-solid fa-calendar-xmark"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700">Không tìm thấy hợp đồng gói VIP nào</p>
                                    <p class="text-xs text-gray-400 mt-1">Vui lòng thử tìm kiếm với từ khóa khác hoặc bấm tab khác.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân Trang (Nằm dưới cùng bảng, cố định) -->
            <div class="p-3 sm:p-4 border-t border-gray-100 bg-gray-50/50 shrink-0">
                {{ $subscriptions->links() }}
            </div>
        </div>

    </div>

    <!-- ================= 3. MODAL CHI TIẾT HỢP ĐỒNG & 8 ĐỢT GIAO (READ-ONLY) ================= -->
    @if($showDetailModal && $selectedSubscription)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-6" wire:keydown.escape="closeDetail">
            <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-5xl shadow-2xl max-h-[92vh] flex flex-col overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200">
                
                <!-- Header Modal -->
                <div class="flex items-center justify-between border-b border-gray-100 px-5 sm:px-8 py-4 shrink-0 bg-white">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 font-mono">
                                #{{ ltrim($selectedOrderCode ?? $selectedSubscription->id, '#') }}
                            </h3>
                            @if($selectedSubscription->status === 'active')
                                <span class="text-[10px] sm:text-xs font-bold px-2.5 py-0.5 rounded-full border bg-emerald-50 text-emerald-700 border-emerald-200">
                                    Đang hoạt động
                                </span>
                            @elseif($selectedSubscription->status === 'completed')
                                <span class="text-[10px] sm:text-xs font-bold px-2.5 py-0.5 rounded-full border bg-slate-100 text-slate-700 border-slate-200">
                                    Đã hoàn thành
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5 font-mono">
                            Ngày đăng ký: {{ $selectedSubscription->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <button type="button" wire:click="closeDetail" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0 cursor-pointer">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <!-- Thân Modal (Cuộn mượt với thanh scroll gọn gàng) -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-5 sm:p-8 space-y-6">
                    
                    <!-- 3 Khối Thông Tin Tóm Tắt (Read-Only) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-4">
                        <!-- Thông tin người nhận -->
                        <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-gray-200/80 space-y-1 text-xs">
                            <span class="text-gray-400 block font-semibold uppercase tracking-wider text-[10px]">Khách hàng nhận rau:</span>
                            <p class="font-bold text-gray-900 text-sm">{{ $selectedSubscription->recipient_name }}</p>
                            <p class="font-mono text-gray-600">{{ $selectedSubscription->recipient_phone }}</p>
                            <p class="text-gray-600 leading-snug pt-1" title="{{ $selectedSubscription->recipient_address }}">
                                {{ $selectedSubscription->recipient_address }}
                            </p>
                        </div>

                        <!-- Thông tin gói VIP -->
                        <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-gray-200/80 space-y-1 text-xs">
                            <span class="text-gray-400 block font-semibold uppercase tracking-wider text-[10px]">Gói rau VIP:</span>
                            <p class="font-bold text-brand-800 text-sm">Gói Rau Định Kỳ VIP</p>
                            <p class="text-gray-600">Định lượng: <b class="text-gray-900">3.5kg / đợt</b> (8 đợt / 4 tuần)</p>
                            <p class="text-gray-600">Lịch giao: <b class="text-gray-900 font-mono">{{ !empty($selectedSubscription->delivery_days) ? $selectedSubscription->delivery_days : 'Chưa đặt' }}</b></p>
                        </div>

                        <!-- Thông tin thanh toán & tiến độ -->
                        <div class="p-4 rounded-2xl bg-brand-50/50 border border-brand-200/80 space-y-1 text-xs">
                            <span class="text-brand-700 block font-semibold uppercase tracking-wider text-[10px]">Thanh toán & Tiến độ:</span>
                            <p class="font-bold text-brand-900 text-base font-mono">
                                {{ number_format($selectedSubscription->total_price, 0, ',', '.') }}đ
                            </p>
                            @php
                                $compCount = count(array_filter($enrichedSchedules, fn($s) => $s['status'] === 'delivered'));
                            @endphp
                            <p class="text-gray-600 pt-1">
                                Tiến độ: <b class="text-brand-800 font-mono">{{ $compCount }}/8 đợt</b> đã hoàn tất
                            </p>
                        </div>
                    </div>

                    <!-- Danh Sách 8 Đợt Giao Chi Tiết -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm sm:text-base font-extrabold text-gray-900 flex items-center gap-2">
                                <i class="fa-solid fa-boxes-stacked text-brand-700"></i>
                                <span>Chi Tiết Tiến Độ 8 Đợt Giao Rau</span>
                            </h4>
                            <span class="text-xs text-gray-400 font-mono">Tổng 8 đợt</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5">
                            @forelse($enrichedSchedules as $sch)
                                @php
                                    $st = $sch['status'];
                                    if ($st === 'delivered') {
                                        $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                        $badgeText = '✓ Đã giao';
                                        $borderClass = 'border-emerald-200 bg-emerald-50/20';
                                    } elseif ($st === 'processing') {
                                        $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
                                        $badgeText = '🌱 Hôm nay tới đợt';
                                        $borderClass = 'border-amber-200 bg-amber-50/20';
                                    } else {
                                        $badgeClass = 'bg-gray-100 text-gray-600 border-gray-200';
                                        $badgeText = '⏳ Chờ đến ngày';
                                        $borderClass = 'border-gray-200 bg-white';
                                    }
                                @endphp
                                <div class="rounded-2xl border p-4 space-y-3 flex flex-col justify-between {{ $borderClass }} shadow-2xs">
                                    <div class="space-y-2.5">
                                        <!-- Header đợt -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-black text-brand-900 uppercase bg-brand-50 px-2 py-0.5 rounded-lg border border-brand-200">
                                                Đợt {{ $sch['order'] }}
                                            </span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $badgeClass }}">
                                                {{ $badgeText }}
                                            </span>
                                        </div>

                                        <!-- Ngày giao & Mã đơn -->
                                        <div class="text-xs space-y-0.5">
                                            <div class="text-gray-400 text-[11px]">Ngày giao:</div>
                                            <div class="font-mono font-bold text-gray-900">{{ $sch['date'] }}</div>
                                            @if(!empty($sch['order_code']))
                                                <div class="text-[10px] text-brand-700 font-mono font-semibold pt-0.5">
                                                    Đơn: #{{ $sch['order_code'] }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Món rau -->
                                        <div class="border-t border-gray-100 pt-2 space-y-1.5">
                                            <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                                                Rau đã chọn ({{ $sch['total_weight'] }}kg):
                                            </div>
                                            <div class="space-y-1 max-h-28 overflow-y-auto pr-1.5 custom-scrollbar">
                                                @forelse($sch['items'] as $it)
                                                    <div class="flex items-center justify-between text-xs py-0.5">
                                                        <div class="flex items-center gap-1.5 min-w-0">
                                                            <img src="{{ $it['image'] }}" class="w-5 h-5 rounded object-cover border border-gray-100 shrink-0" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?w=100'">
                                                            <span class="truncate text-gray-700 text-[11px] font-medium" title="{{ $it['name'] }}">{{ $it['name'] }}</span>
                                                        </div>
                                                        <span class="font-mono font-bold text-brand-800 text-[11px] shrink-0 ml-1.5 whitespace-nowrap">
                                                            {{ $it['quantity'] }}x <span class="text-gray-500 font-medium text-[10px]">({{ $it['weight'] }}kg)</span>
                                                        </span>
                                                    </div>
                                                @empty
                                                    <span class="text-[11px] text-gray-400 italic">Rau hữu cơ theo mùa</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-8 text-center bg-gray-50/80 rounded-2xl border border-dashed border-gray-200">
                                    <i class="fa-regular fa-calendar-xmark text-gray-400 text-2xl mb-1.5 block"></i>
                                    <p class="text-xs sm:text-sm font-bold text-gray-700">Khách hàng chưa thiết lập lịch giao 8 đợt</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Lịch 8 đợt và danh sách nông sản sẽ hiển thị tại đây ngay khi khách chọn ngày trên website.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                <!-- Footer Modal -->
                <div class="border-t border-gray-100 px-5 sm:px-8 py-3.5 bg-gray-50 flex items-center justify-end shrink-0">
                    <button type="button" wire:click="closeDetail" class="px-5 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 font-bold text-xs sm:text-sm transition shadow-xs cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
