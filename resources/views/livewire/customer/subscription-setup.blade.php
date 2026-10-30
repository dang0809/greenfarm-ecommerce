<div>
    <!-- MAIN CONTAINER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-16 space-y-8 flex-1 w-full">

        <!-- TIÊU ĐỀ TRANG -->
        <div class="border-b border-gray-200/80 pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-amber-500 text-lg">👑</span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ $isLocked ? 'Lịch Trình & Tiến Độ Giao Rau VIP' : 'Thiết Lập Địa Chỉ & Lịch Giao 8 Đợt' }}
                    </h1>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ $isLocked ? 'Theo dõi trạng thái từng đợt giao và danh sách thực đơn rau tươi hữu cơ của bạn.' : 'Ghim địa chỉ nhận hàng, chọn 2 ngày cố định trong tuần và phối rau tươi cho 8 đợt giao.' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if($isLocked)
                    <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-2 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Đã chốt lịch cố định ({{ $deliveredCount }}/8 đợt đã giao)</span>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-300 text-amber-900 px-4 py-2 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
                        <i class="fa-solid fa-pen-to-square text-amber-600"></i>
                        <span>Đang thiết lập • Chưa chốt lịch</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- BANNER THÔNG BÁO HOÀN THÀNH 8 ĐỢT GIAO RAU VIP -->
        @if($deliveredCount >= 8)
            <div class="bg-gradient-to-r from-emerald-600 via-brand-700 to-emerald-800 text-white rounded-3xl p-5 sm:p-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-2 border-emerald-400/40">
                <div class="flex items-start gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-amber-400/30 border border-amber-300/40 flex items-center justify-center text-amber-300 text-xl shrink-0 shadow-inner">
                        👑
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm sm:text-base text-white flex items-center gap-2">
                            <span>Chúc mừng! Bạn đã hoàn thành trọn vẹn 8 đợt giao rau VIP.</span>
                            <span class="text-xs bg-amber-400 text-gray-900 font-black px-2 py-0.5 rounded-full">8/8 Đợt</span>
                        </h4>
                        <p class="text-xs text-brand-100/90 mt-1 leading-relaxed">
                            Cảm ơn bạn đã đồng hành cùng Nông Trại Đất Lành. Hãy đăng ký gói mới cho tháng tiếp theo để tiếp tục nhận rau sạch hữu cơ tươi ngon định kỳ mỗi tuần!
                        </p>
                    </div>
                </div>
                <a href="{{ route('customer.vip.checkout') }}" wire:navigate
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-amber-400 hover:bg-amber-300 text-gray-900 text-xs sm:text-sm font-extrabold transition shadow-lg shrink-0 hover:scale-105 active:scale-95">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Đăng Ký Gói Mới</span>
                </a>
            </div>
        @endif

        @if(!$isLocked)
            <!-- ========================================== -->
            <!-- CHẾ ĐỘ 1: THIẾT LẬP LỊCH & CHỌN RAU 8 ĐỢT -->
            <!-- ========================================== -->

            <!-- BƯỚC 1: ĐỊA CHỈ NHẬN HÀNG -->
            <x-customer.address-card 
                :address="$address" 
                :distance="$distance" 
                :max-distance="$maxDistance" 
                :is-exceeded="$isCurrentAddressExceeded"
                :is-unknown="$isDistanceUnknown" />

            <!-- BƯỚC 2: CHỌN 2 NGÀY CỐ ĐỊNH TRONG TUẦN -->
            <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-brand-600"></i> Chọn 2 Ngày Nhận Hàng Cố Định Trong Tuần
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">Bấm chọn <b>đúng 2 ngày bất kỳ</b> bạn muốn nhận rau (Ví dụ: Thứ 3 & Thứ 6, Thứ 2 & Thứ 5...)</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-brand-800 bg-brand-50 border border-brand-200 px-3 py-1 rounded-xl">
                        Đã chọn: {{ count($selectedDayIndexes) }}/2 ngày
                    </span>
                </div>

                <!-- LƯỚI 7 NGÀY -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 text-xs">
                    @foreach($weekdays as $w)
                        @php
                            $isSelected = in_array($w['dayIndex'], $selectedDayIndexes);
                        @endphp
                        <button type="button" 
                                wire:click="toggleDaySelection({{ $w['dayIndex'] }})"
                                class="p-3.5 rounded-2xl border-2 text-center transition flex flex-col items-center justify-center gap-1.5 cursor-pointer {{ $isSelected ? 'border-brand-500 bg-brand-50/60 text-brand-900 font-extrabold shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300 text-gray-600 font-semibold' }}">
                            <span class="text-sm">{{ $w['label'] }}</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $isSelected ? 'bg-brand-600 text-white font-bold' : 'bg-gray-100 text-gray-500' }}">
                                {{ $isSelected ? '✓ Đã chọn' : 'Chọn' }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- BƯỚC 3: CHỌN RAU CHO TỪNG ĐỢT GIAO -->
            <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <h2 class="font-extrabold text-lg text-gray-900">Chọn Thực Đơn Cho Từng Đợt Giao Cụ Thể</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Chuyển qua từng Tab đợt để nhặt đủ <b>{{ number_format($maxWeightTarget, 1) }} kg</b> rau củ cho ngày đó.</p>
                    </div>

                    <div class="bg-brand-50 border border-brand-200 px-4 py-2 rounded-2xl flex items-center gap-3 shrink-0">
                        <i class="fa-solid fa-scale-balanced text-brand-700 text-xl"></i>
                        <div class="text-xs">
                            <span class="text-gray-500 font-bold block">Đợt đang chọn: <b class="text-brand-800">Đợt {{ $activeTab }}</b></span>
                            <span class="font-mono font-black text-sm {{ $currentWeight >= $maxWeightTarget ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ number_format($currentWeight, 1) }} / {{ number_format($maxWeightTarget, 1) }} kg
                            </span>
                        </div>
                    </div>
                </div>

                <!-- THANH TABS 8 ĐỢT GIAO -->
                <div class="flex gap-2 overflow-x-auto no-scrollbar py-1">
                    @foreach($schedulePlan as $plan)
                        @php
                            $isActive = $plan['delivery_order'] === $activeTab;
                            $weight = $plan['weight'] ?? 0.0;
                            $isFull = $weight >= $maxWeightTarget;
                        @endphp
                        <button type="button" 
                                wire:click="switchTab({{ $plan['delivery_order'] }})" 
                                class="px-4 py-2.5 rounded-2xl border text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer {{ $isActive ? 'bg-brand-600 text-white border-brand-600 shadow-xs' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                            <span>Đợt {{ $plan['delivery_order'] }} ({{ $plan['date_label'] }})</span>
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded-full {{ $isFull ? ($isActive ? 'bg-brand-800 text-white' : 'bg-emerald-100 text-emerald-800') : 'bg-amber-100 text-amber-800' }}">
                                {{ number_format($weight, 1) }}/{{ number_format($maxWeightTarget, 1) }}kg
                            </span>
                        </button>
                    @endforeach
                </div>

                <!-- BỘ LỌC DANH MỤC & TÌM KIẾM RAU CỦ -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1 text-xs">
                        <button type="button" 
                                wire:click="$set('selectedCategory', null)" 
                                class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap cursor-pointer {{ is_null($selectedCategory) ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Tất cả rau củ
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" 
                                    wire:click="$set('selectedCategory', {{ $cat->id }})" 
                                    class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap cursor-pointer {{ $selectedCategory === $cat->id ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    <div class="relative sm:w-64">
                        <input type="text" 
                               wire:model.live.debounce.300ms="searchVeggie" 
                               placeholder="Tìm tên rau củ..." 
                               class="w-full pl-9 pr-4 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-hidden">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                </div>

                <!-- CATALOG RAU CỦ VÀ SIDEBAR TÓM TẮT -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- Cột trái: Catalog rau củ còn hàng -->
                    <div class="lg:col-span-8 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @forelse($veggieCatalog as $v)
                                @php
                                    $qty = $currentDelivery['items'][$v['id']] ?? 0;
                                    $isMaxStock = $qty >= $v['stock'];
                                    $isOverWeight = ($currentWeight + $v['weight']) > ($maxWeightTarget + 0.001);
                                @endphp
                                <div class="bg-white rounded-2xl p-4 border transition-all hover:shadow-xs {{ $qty > 0 ? 'border-brand-500 bg-brand-50/15' : 'border-gray-200' }} flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <a href="{{ route('customer.product.detail', $v['slug']) }}" target="_blank" class="shrink-0 group block" title="Xem chi tiết sản phẩm">
                                            <img src="{{ $v['image'] }}" class="w-14 h-14 rounded-xl object-cover border border-gray-100 group-hover:opacity-90 transition">
                                        </a>

                                        <div class="min-w-0">
                                            <a href="{{ route('customer.product.detail', $v['slug']) }}" target="_blank" class="font-bold text-xs text-gray-900 hover:text-brand-600 transition flex items-center gap-1.5 truncate" title="Xem chi tiết">
                                                <span class="truncate">{{ $v['name'] }}</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-gray-400 shrink-0"></i>
                                            </a>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-mono text-gray-500 block">{{ $v['unit_label'] }} ({{ $v['weight'] }}kg)</span>
                                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                                    Còn {{ $v['stock'] }} {{ $v['unit'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bộ nút tăng giảm -->
                                    <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl shrink-0">
                                        <button type="button" 
                                                wire:click="updateQty({{ $v['id'] }}, -1)" 
                                                @disabled($qty <= 0)
                                                class="w-7 h-7 rounded-lg bg-white text-gray-700 font-bold flex items-center justify-center text-xs shadow-2xs disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">-</button>
                                        
                                        <span class="w-6 text-center font-mono font-bold text-xs text-gray-900">{{ $qty }}</span>
                                        
                                        <button type="button" 
                                                wire:click="updateQty({{ $v['id'] }}, 1)" 
                                                @disabled($isMaxStock || $isOverWeight)
                                                class="w-7 h-7 rounded-lg bg-brand-600 text-white font-bold flex items-center justify-center text-xs shadow-2xs disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                                title="{{ $isOverWeight ? 'Đã đạt định mức tối đa ' . $maxWeightTarget . 'kg' : ($isMaxStock ? 'Đã hết hàng khả dụng' : 'Thêm món') }}">+</button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-2 text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                    <i class="fa-solid fa-seedling text-3xl text-gray-300 mb-2"></i>
                                    <p class="text-xs text-gray-500 font-medium">Không tìm thấy rau củ nào phù hợp.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Cột phải: Sidebar tóm tắt đợt này -->
                    <div class="lg:col-span-4 sticky top-28 space-y-4">
                        <div class="bg-gray-50/80 rounded-3xl border border-gray-200/80 p-5 space-y-4 shadow-xs">
                            <div class="border-b border-gray-200 pb-3 flex items-center justify-between">
                                <h3 class="font-extrabold text-sm text-gray-900">
                                    Thực Đơn Ngày {{ $currentDelivery['date_label'] }}
                                </h3>
                                <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg {{ $currentWeight >= $maxWeightTarget ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ number_format($currentWeight, 1) }} / {{ number_format($maxWeightTarget, 1) }} kg
                                </span>
                            </div>

                            <!-- Thanh tiến trình cân nặng -->
                            <div class="space-y-1">
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    @php
                                        $percent = min(100, round(($currentWeight / max(0.1, $maxWeightTarget)) * 100));
                                    @endphp
                                    <div class="h-2 rounded-full transition-all duration-300 {{ $currentWeight >= $maxWeightTarget ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $percent }}%"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-400">
                                    <span>0 kg</span>
                                    <span>Định lượng: {{ number_format($maxWeightTarget, 1) }} kg</span>
                                </div>
                            </div>

                            <!-- Selected items list -->
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                @forelse($currentDelivery['items'] as $id => $qty)
                                    @php
                                        $item = collect($veggieCatalog)->firstWhere('id', $id);
                                    @endphp
                                    @if($item)
                                        <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-white border border-gray-200 shadow-2xs">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <img src="{{ $item['image'] }}" class="w-7 h-7 rounded-lg object-cover shrink-0">
                                                <span class="font-semibold text-gray-800 truncate">{{ $item['name'] }}</span>
                                            </div>
                                            <span class="font-mono text-brand-700 font-bold shrink-0 ml-2">
                                                @if(strtolower(trim($item['unit_label'])) === 'kg')
                                                    {{ $qty }} kg
                                                @else
                                                    {{ $qty }} x {{ $item['unit_label'] }} ({{ round($qty * ($item['weight'] ?? 0.5), 1) }}kg)
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                @empty
                                    <p class="text-xs text-gray-400 italic py-6 text-center">Chưa chọn món cho {{ $currentDelivery['date_label'] }}</p>
                                @endforelse
                            </div>

                            <div class="pt-2 space-y-2">
                                <button type="button" 
                                        wire:click="copyMenuToAllDeliveries" 
                                        class="w-full bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 font-bold py-2.5 rounded-xl transition text-xs flex items-center justify-center gap-2 cursor-pointer shadow-2xs">
                                    <i class="fa-solid fa-copy text-brand-600"></i>
                                    <span>Sao chép đợt này cho cả 8 đợt</span>
                                </button>

                                @if (!$address)
                                    <button type="button" disabled
                                            class="w-full bg-gray-200 border-2 border-gray-300 text-gray-500 font-bold py-3.5 rounded-2xl transition flex items-center justify-center gap-2 text-xs sm:text-sm cursor-not-allowed shadow-xs">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <span>Vui Lòng Chọn Địa Chỉ Nhận Hàng</span>
                                    </button>
                                @elseif ($isDistanceUnknown)
                                    <button type="button" disabled
                                            class="w-full bg-amber-100 border-2 border-amber-300 text-amber-800 font-bold py-3.5 rounded-2xl transition flex items-center justify-center gap-2 text-xs sm:text-sm cursor-not-allowed shadow-xs"
                                            title="Địa chỉ chưa xác định được vị trí">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span>Địa Chỉ Chưa Xác Định Được Vị Trí</span>
                                    </button>
                                @elseif ($isCurrentAddressExceeded)
                                    <button type="button" disabled
                                            class="w-full bg-red-100 border-2 border-red-300 text-red-600 font-bold py-3.5 rounded-2xl transition flex items-center justify-center gap-2 text-xs sm:text-sm cursor-not-allowed shadow-xs"
                                            title="Địa chỉ vượt quá bán kính giao hàng {{ $maxDistance }}km">
                                        <i class="fa-solid fa-ban"></i>
                                        <span>Địa Chỉ Vượt Bán Kính (Tối Đa {{ $maxDistance }}km)</span>
                                    </button>
                                @else
                                    <button type="button" 
                                            wire:confirm="Bạn có chắc chắn muốn xác nhận và khóa cố định lịch giao 8 đợt không? Lịch trình sẽ được lưu chính thức vào hệ thống."
                                            wire:click="confirmAndLockSchedule" 
                                            class="w-full bg-brand-600 hover:bg-brand-700 text-white font-extrabold py-3.5 rounded-2xl transition shadow-md flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer">
                                        <i class="fa-solid fa-lock"></i>
                                        <span>Xác Nhận & Chốt Lịch Cố Định</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- ========================================== -->
            <!-- CHẾ ĐỘ 2: TIẾN ĐỘ 8 ĐỢT GIAO RAU VIP (LOCKED) -->
            <!-- ========================================== -->

            <!-- THẺ TỔNG QUAN TIẾN ĐỘ HỢP ĐỒNG -->
            <div class="bg-gradient-to-br from-brand-900 via-brand-800 to-emerald-950 text-white p-6 sm:p-8 rounded-3xl shadow-xl space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-5">
                    <div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-black tracking-wider uppercase bg-amber-400/20 text-amber-300 border border-amber-300/30 px-3 py-1 rounded-full mb-2">
                            👑 Hội Viên VIP Đất Lành Farm
                        </span>
                        <h2 class="text-xl sm:text-2xl font-black">Lịch Trình Giao Định Kỳ 8 Đợt</h2>
                        <p class="text-xs text-brand-100/80 mt-1">Hợp đồng giao tận nhà đều đặn 2 đợt mỗi tuần. Rau tươi thu hoạch sáng sớm và giao hỏa tốc.</p>
                    </div>

                    <div class="text-left sm:text-right bg-white/10 px-5 py-3 rounded-2xl border border-white/15 backdrop-blur-xs">
                        <span class="text-[11px] text-brand-200 block">Tiến độ hoàn thành:</span>
                        <span class="text-xl sm:text-2xl font-black font-mono text-amber-300">{{ $deliveredCount }} / 8 Đợt</span>
                    </div>
                </div>

                <!-- Thông tin người nhận & Lịch cố định -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl space-y-1.5">
                        <span class="text-brand-200 font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-amber-400"></i> Địa chỉ nhận hàng cố định:
                        </span>
                        <p class="font-bold text-sm text-white">{{ $this->subscription?->recipient_name }} • {{ $this->subscription?->recipient_phone }}</p>
                        <p class="text-brand-100/90 leading-relaxed">{{ $this->subscription?->recipient_address }}</p>
                    </div>

                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl space-y-1.5">
                        <span class="text-brand-200 font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-check text-amber-400"></i> Lịch giao cố định trong tuần:
                        </span>
                        <p class="font-bold text-sm text-white">2 ngày cố định mỗi tuần (Định lượng: {{ number_format($maxWeightTarget, 1) }} kg/đợt)</p>
                        <p class="text-brand-100/90 leading-relaxed">
                            Cần dời ngày hoặc hỗ trợ khẩn cấp? Vui lòng liên hệ Hotline CSKH VIP độc quyền: <b class="text-amber-300 font-mono">0987.654.321</b>
                        </p>
                    </div>
                </div>
            </div>

            <!-- LƯỚI 8 CARD THEO DÕI TỪNG ĐỢT GIAO -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-brand-600"></i> Trạng Thái Chi Tiết 8 Đợt Giao
                    </h3>
                    <span class="text-xs text-gray-500 font-medium">8 đợt trong 4 tuần</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($lockedSchedules as $sch)
                        @php
                            $status = $sch['status'];
                            // Xác định màu sắc và badge chuẩn 4 trạng thái
                            if ($status === 'delivered') {
                                $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                $badgeText = '✓ Đã giao thành công';
                                $badgeIcon = 'fa-circle-check';
                                $cardBorder = 'border-emerald-300 bg-emerald-50/20';
                            } elseif ($status === 'shipped') {
                                $badgeClass = 'bg-sky-100 text-sky-800 border-sky-200 animate-pulse';
                                $badgeText = '🚚 Đang giao hàng';
                                $badgeIcon = 'fa-truck-fast';
                                $cardBorder = 'border-sky-300 bg-sky-50/20';
                            } elseif ($status === 'processing') {
                                $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200 animate-pulse';
                                $badgeText = '📦 Đang chuẩn bị';
                                $badgeIcon = 'fa-boxes-packing';
                                $cardBorder = 'border-amber-300 bg-amber-50/20';
                            } else {
                                // pending: Chờ đến ngày giao
                                $badgeClass = 'bg-gray-100 text-gray-700 border-gray-200';
                                $badgeText = '⏳ Chờ đến ngày giao';
                                $badgeIcon = 'fa-clock';
                                $cardBorder = 'border-gray-200 bg-white';
                            }
                        @endphp
                        
                        <div class="rounded-3xl border p-5 space-y-4 flex flex-col justify-between transition-all hover:shadow-xs {{ $cardBorder }}">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black text-brand-900 uppercase tracking-wider bg-brand-50 px-2.5 py-1 rounded-lg border border-brand-200">
                                        Đợt {{ $sch['order'] }}
                                    </span>

                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border {{ $badgeClass }}">
                                        {{ $badgeText }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-xs text-gray-400 block font-medium">Ngày giao dự kiến:</span>
                                    <span class="text-sm font-extrabold text-gray-900 font-mono">{{ $sch['date'] }}</span>
                                </div>

                                <!-- Danh sách món rau -->
                                <div class="border-t border-gray-100 pt-3 space-y-2">
                                    <span class="text-[11px] font-bold text-gray-600 block">Thực đơn đợt này ({{ $sch['total_weight'] }} kg):</span>
                                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                                        @forelse($sch['items'] as $item)
                                            <div class="flex items-center justify-between text-xs py-1 border-b border-gray-50 last:border-none">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <img src="{{ $item['image'] }}" class="w-6 h-6 rounded-md object-cover border border-gray-100 shrink-0">
                                                    <a href="{{ !empty($item['slug']) ? route('customer.product.detail', $item['slug']) : '#' }}" target="_blank" class="truncate font-medium text-gray-800 hover:text-brand-600">
                                                        {{ $item['name'] }}
                                                    </a>
                                                </div>
                                                <span class="font-mono font-bold text-brand-800 text-[11px] shrink-0 ml-1">{{ $item['quantity'] }}x @if(isset($item['weight']))({{ $item['weight'] }}kg)@endif</span>
                                            </div>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">Rau hữu cơ tổng hợp theo mùa</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 text-[11px] text-gray-400 text-center border-t border-gray-100">
                                @if($status === 'delivered')
                                    <span class="text-emerald-700 font-bold">Giao thành công</span>
                                @elseif($status === 'shipped')
                                    <span class="text-sky-700 font-bold">Shipper đang trên đường tới</span>
                                @elseif($status === 'processing')
                                    <span class="text-amber-700 font-bold">Đang đóng gói nông sản</span>
                                @else
                                    <span>Nông trại thu hoạch & giao đúng hẹn</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        @endif

    </main>

    <!-- MODAL CHỌN ĐỊA CHỈ NHẬN HÀNG -->
    <x-customer.address-modal 
        :show="$showAddressModal" 
        :addresses="$addresses" 
        :selected-id="$selectedAddressId" 
        :max-distance="$maxDistance" />
</div>