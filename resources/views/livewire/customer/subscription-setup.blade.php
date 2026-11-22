<div>
    <!-- MAIN CONTAINER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-16 space-y-8 flex-1 w-full">

        <!-- TIÊU ĐỀ TRANG -->
        <div class="border-b border-gray-200/80 pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-amber-500 text-lg">👑</span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        Thiết Lập Địa Chỉ & Lịch Giao 8 Đợt
                    </h1>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Ghim địa chỉ nhận hàng, chọn 2 ngày cố định trong tuần và phối rau tươi cho 8 đợt giao.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="bg-amber-50 border border-amber-300 text-amber-900 px-4 py-2 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
                    <i class="fa-solid fa-pen-to-square text-amber-600"></i>
                    <span>Đang thiết lập • Chưa chốt lịch</span>
                </div>
            </div>
        </div>
            <!-- ========================================== -->
            <!-- CHẾ ĐỘ 1: THIẾT LẬP LỊCH & CHỌN RAU 8 ĐỢT -->
            <!-- ========================================== -->

            <!-- BƯỚC 1: ĐỊA CHỈ NHẬN HÀNG -->
            <x-customer.address-card :address="$address" :distance="$distance" :max-distance="$maxDistance" :is-exceeded="$isCurrentAddressExceeded"
                :is-unknown="$isDistanceUnknown" />

            <!-- BƯỚC 2: CHỌN 2 NGÀY CỐ ĐỊNH TRONG TUẦN -->
            <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-brand-600"></i> Chọn 2 Ngày Nhận Hàng Cố Định Trong
                            Tuần
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">Bấm chọn <b>đúng 2 ngày bất kỳ</b> bạn muốn nhận rau (Ví
                            dụ: Thứ 3 & Thứ 6, Thứ 2 & Thứ 5...)</p>
                    </div>
                    <span
                        class="text-xs font-mono font-bold text-brand-800 bg-brand-50 border border-brand-200 px-3 py-1 rounded-xl">
                        Đã chọn: {{ count($selectedDayIndexes) }}/2 ngày
                    </span>
                </div>

                <!-- LƯỚI 7 NGÀY -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 text-xs">
                    @foreach ($weekdays as $w)
                        @php
                            $isSelected = in_array($w['dayIndex'], $selectedDayIndexes);
                        @endphp
                        <button type="button" wire:click="toggleDaySelection({{ $w['dayIndex'] }})"
                            class="p-3.5 rounded-2xl border-2 text-center transition flex flex-col items-center justify-center gap-1.5 cursor-pointer {{ $isSelected ? 'border-brand-500 bg-brand-50/60 text-brand-900 font-extrabold shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300 text-gray-600 font-semibold' }}">
                            <span class="text-sm">{{ $w['label'] }}</span>
                            <span
                                class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $isSelected ? 'bg-brand-600 text-white font-bold' : 'bg-gray-100 text-gray-500' }}">
                                {{ $isSelected ? '✓ Đã chọn' : 'Chọn' }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- BƯỚC 3: CHỌN RAU CHO TỪNG ĐỢT GIAO -->
            <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-xs space-y-6">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <h2 class="font-extrabold text-lg text-gray-900">Chọn Thực Đơn Cho Từng Đợt Giao Cụ Thể</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Chuyển qua từng Tab đợt để nhặt đủ
                            <b>{{ number_format($maxWeightTarget, 1) }} kg</b> rau củ cho ngày đó.
                        </p>
                    </div>

                    <div
                        class="bg-brand-50 border border-brand-200 px-4 py-2 rounded-2xl flex items-center gap-3 shrink-0">
                        <i class="fa-solid fa-scale-balanced text-brand-700 text-xl"></i>
                        <div class="text-xs">
                            <span class="text-gray-500 font-bold block">Đợt đang chọn: <b class="text-brand-800">Đợt
                                    {{ $activeTab }}</b></span>
                            <span
                                class="font-mono font-black text-sm {{ $currentWeight >= $maxWeightTarget ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ number_format($currentWeight, 1) }} / {{ number_format($maxWeightTarget, 1) }} kg
                            </span>
                        </div>
                    </div>
                </div>

                <!-- THANH TABS 8 ĐỢT GIAO -->
                <div class="flex gap-2 overflow-x-auto no-scrollbar py-1">
                    @foreach ($schedulePlan as $plan)
                        @php
                            $isActive = $plan['delivery_order'] === $activeTab;
                            $weight = $plan['weight'] ?? 0.0;
                            $isFull = $weight >= $maxWeightTarget;
                        @endphp
                        <button type="button" wire:click="switchTab({{ $plan['delivery_order'] }})"
                            class="px-4 py-2.5 rounded-2xl border text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer {{ $isActive ? 'bg-brand-600 text-white border-brand-600 shadow-xs' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                            <span>Đợt {{ $plan['delivery_order'] }} ({{ $plan['date_label'] }})</span>
                            <span
                                class="font-mono text-[10px] px-2 py-0.5 rounded-full {{ $isFull ? ($isActive ? 'bg-brand-800 text-white' : 'bg-emerald-100 text-emerald-800') : 'bg-amber-100 text-amber-800' }}">
                                {{ number_format($weight, 1) }}/{{ number_format($maxWeightTarget, 1) }}kg
                            </span>
                        </button>
                    @endforeach
                </div>

                <!-- BỘ LỌC DANH MỤC & TÌM KIẾM RAU CỦ -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1 text-xs">
                        <button type="button" wire:click="$set('selectedCategory', null)"
                            class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap cursor-pointer {{ is_null($selectedCategory) ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Tất cả rau củ
                        </button>
                        @foreach ($categories as $cat)
                            <button type="button" wire:click="$set('selectedCategory', {{ $cat->id }})"
                                class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap cursor-pointer {{ $selectedCategory === $cat->id ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    <div class="relative sm:w-64">
                        <input type="text" wire:model.live.debounce.300ms="searchVeggie"
                            placeholder="Tìm theo tên sản phẩm..."
                            class="w-full pl-9 pr-8 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-hidden">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        @if (!empty($searchVeggie))
                            <button type="button" wire:click="$set('searchVeggie', '')"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5 cursor-pointer"
                                title="Xóa từ khóa">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        @endif
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
                                    $isOverWeight = $currentWeight + $v['weight'] > $maxWeightTarget + 0.001;
                                @endphp
                                <div
                                    class="bg-white rounded-2xl p-4 border transition-all hover:shadow-xs {{ $qty > 0 ? 'border-brand-500 bg-brand-50/15' : 'border-gray-200' }} flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <a href="{{ route('customer.product.detail', $v['slug']) }}" target="_blank"
                                            class="shrink-0 group block" title="Xem chi tiết sản phẩm">
                                            <img src="{{ $v['image'] }}"
                                                class="w-14 h-14 rounded-xl object-cover border border-gray-100 group-hover:opacity-90 transition">
                                        </a>

                                        <div class="min-w-0">
                                            <a href="{{ route('customer.product.detail', $v['slug']) }}"
                                                target="_blank"
                                                class="font-bold text-xs text-gray-900 hover:text-brand-600 transition flex items-center gap-1.5 truncate"
                                                title="Xem chi tiết">
                                                <span class="truncate">{{ $v['name'] }}</span>
                                                <i
                                                    class="fa-solid fa-arrow-up-right-from-square text-[9px] text-gray-400 shrink-0"></i>
                                            </a>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span
                                                    class="text-[10px] font-mono text-gray-500 block">{{ $v['unit_label'] }}
                                                    ({{ $v['weight'] }}kg)
                                                </span>
                                                <span
                                                    class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                                    Còn {{ $v['stock'] }} {{ $v['unit'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bộ nút tăng giảm -->
                                    <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl shrink-0">
                                        <button type="button" wire:click="updateQty({{ $v['id'] }}, -1)"
                                            @disabled($qty <= 0)
                                            class="w-7 h-7 rounded-lg bg-white text-gray-700 font-bold flex items-center justify-center text-xs shadow-2xs disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">-</button>

                                        <span
                                            class="w-6 text-center font-mono font-bold text-xs text-gray-900">{{ $qty }}</span>

                                        <button type="button" wire:click="updateQty({{ $v['id'] }}, 1)"
                                            @disabled($isMaxStock || $isOverWeight)
                                            class="w-7 h-7 rounded-lg bg-brand-600 text-white font-bold flex items-center justify-center text-xs shadow-2xs disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                            title="{{ $isOverWeight ? 'Đã đạt định mức tối đa ' . $maxWeightTarget . 'kg' : ($isMaxStock ? 'Đã hết hàng khả dụng' : 'Thêm món') }}">+</button>
                                    </div>
                                </div>
                            @empty
                                <div
                                    class="col-span-2 text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
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
                                <span
                                    class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg {{ $currentWeight >= $maxWeightTarget ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ number_format($currentWeight, 1) }} / {{ number_format($maxWeightTarget, 1) }}
                                    kg
                                </span>
                            </div>

                            <!-- Thanh tiến trình cân nặng -->
                            <div class="space-y-1">
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    @php
                                        $percent = min(100, round(($currentWeight / max(0.1, $maxWeightTarget)) * 100));
                                    @endphp
                                    <div class="h-2 rounded-full transition-all duration-300 {{ $currentWeight >= $maxWeightTarget ? 'bg-emerald-500' : 'bg-amber-500' }}"
                                        style="width: {{ $percent }}%"></div>
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
                                    @if ($item)
                                        <div
                                            class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-white border border-gray-200 shadow-2xs">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <img src="{{ $item['image'] }}"
                                                    class="w-7 h-7 rounded-lg object-cover shrink-0">
                                                <span
                                                    class="font-semibold text-gray-800 truncate">{{ $item['name'] }}</span>
                                            </div>
                                            <span class="font-mono text-brand-700 font-bold shrink-0 ml-2">
                                                @if (strtolower(trim($item['unit_label'])) === 'kg')
                                                    {{ $qty }} kg
                                                @else
                                                    {{ $qty }} x {{ $item['unit_label'] }}
                                                    ({{ round($qty * ($item['weight'] ?? 0.5), 1) }}kg)
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                @empty
                                    <p class="text-xs text-gray-400 italic py-6 text-center">Chưa chọn món cho
                                        {{ $currentDelivery['date_label'] }}</p>
                                @endforelse
                            </div>

                            <div class="pt-2 space-y-2">
                                <button type="button" wire:click="copyMenuToAllDeliveries"
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


    </main>

    <!-- MODAL CHỌN ĐỊA CHỈ NHẬN HÀNG -->
    <x-customer.address-modal :show="$showAddressModal" :addresses="$addresses" :selected-id="$selectedAddressId" :max-distance="$maxDistance" />
</div>
