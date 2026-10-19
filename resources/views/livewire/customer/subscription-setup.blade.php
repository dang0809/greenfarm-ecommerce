<div>
    <!-- MAIN CONTAINER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-12 space-y-8 flex-1 w-full">

        <!-- TIÊU ĐỀ TRANG VÀ BADGE TRẠNG THÁI -->
        <div class="border-b border-gray-200/80 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Thiết Lập Địa Chỉ & Lịch Giao 8 Đợt</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Ghim địa chỉ nhận hàng, chọn 2 ngày cố định trong tuần và nhặt rau tươi cho 8 đợt giao.</p>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-mono font-bold bg-brand-50 text-brand-800 border border-brand-200 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-brand-600"></i>
                    <span>Hợp đồng VIP: #{{ $contractCode }}</span>
                </span>

                @if($isLocked)
                    <div class="bg-amber-50 border border-amber-300 text-amber-900 px-4 py-1.5 rounded-2xl text-xs font-extrabold flex items-center gap-2 shrink-0">
                        <i class="fa-solid fa-lock text-amber-600 text-sm"></i>
                        <span>Đã chốt lịch & địa chỉ • Không thể chỉnh sửa</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- BƯỚC 1: ĐỊA CHỈ NHẬN HÀNG -->
        <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-brand-600"></i> Địa Chỉ Nhận Hàng Cố Định
                </h2>
                
                @if(!$isLocked)
                    <button type="button" 
                            wire:click="$set('showAddressModal', true)" 
                            class="text-xs font-bold text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 border border-brand-200 px-3.5 py-1.5 rounded-xl transition-all flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <i class="fa-solid fa-repeat text-[10px]"></i>
                        <span>Thay đổi</span>
                    </button>
                @endif
            </div>

            <!-- Display Current Address -->
            @if($currentAddress)
                <div class="p-4 sm:p-5 rounded-2xl border border-gray-200 bg-gray-50/60">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-sm sm:text-base font-bold text-gray-900">{{ $currentAddress['name'] }}</span>
                        <span class="text-xs sm:text-sm text-gray-600 font-mono">{{ $currentAddress['phone'] }}</span>
                        @if($currentAddress['is_default'])
                            <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2.5 py-0.5 rounded-full border border-brand-200">Mặc định</span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-gray-600 mt-2 leading-relaxed">
                        {{ $currentAddress['address'] }}
                    </p>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-400">
                        <i class="fa-solid fa-route text-brand-600"></i>
                        <span>Khoảng cách từ Farm: <b class="text-gray-700 font-mono">{{ $currentAddress['distance'] }} km</b> (Miễn phí ship hỏa tốc 8 đợt)</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- BƯỚC 2: CHỌN 2 NGÀY BẤT KỲ TRONG TUẦN -->
        <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-brand-600"></i> Chọn 2 Ngày Nhận Hàng Cố Định Trong Tuần
                    </h2>
                    <p class="text-xs text-gray-400 mt-0.5">Bấm chọn <b>đúng 2 ngày bất kỳ</b> bạn muốn nhận rau (Ví dụ: Thứ 2 & Thứ 5, Thứ 3 & Thứ 6,...)</p>
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
                            @disabled($isLocked)
                            class="p-3 rounded-2xl border-2 text-center transition flex flex-col items-center justify-center gap-1 {{ $isLocked ? 'cursor-not-allowed opacity-75' : 'cursor-pointer' }} {{ $isSelected ? 'border-brand-500 bg-brand-50/50 text-brand-900 font-extrabold shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300 text-gray-600 font-semibold' }}">
                        <span class="text-sm">{{ $w['label'] }}</span>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded {{ $isSelected ? 'bg-brand-600 text-white font-bold' : 'bg-gray-100 text-gray-500' }}">
                            {{ $isSelected ? 'Đã chọn' : 'Chọn' }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- BƯỚC 3: CHỌN RAU CHO TỪNG NGÀY GIAO -->
        <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="font-extrabold text-lg text-gray-900">Chọn Món Cho Từng Ngày Giao Cụ Thể</h2>
                    <p class="text-xs text-gray-400">Chuyển qua từng Tab đợt để nhặt đúng <b>3.5 kg</b> rau củ cho ngày đó.</p>
                </div>

                <div class="bg-brand-50 border border-brand-200 px-4 py-2 rounded-2xl flex items-center gap-3 shrink-0">
                    <i class="fa-solid fa-scale-balanced text-brand-700 text-xl"></i>
                    <div class="text-xs">
                        <span class="text-gray-500 font-bold block">Đợt đang chọn: <b class="text-brand-800">Đợt {{ $activeTab }}</b></span>
                        <span class="font-mono font-black text-brand-800 text-sm">{{ number_format($currentWeight, 1) }} / {{ number_format($maxWeightTarget, 1) }} kg</span>
                    </div>
                </div>
            </div>

            <!-- THANH TABS 8 ĐỢT GIAO -->
            <div class="flex gap-2 overflow-x-auto no-scrollbar py-1">
                @foreach($schedulePlan as $plan)
                    @php
                        $isActive = $plan['delivery_order'] === $activeTab;
                        $weight = $this->calculateDeliveryWeight($plan['items']);
                        $isFull = $weight === $maxWeightTarget;
                    @endphp
                    <button type="button" 
                            wire:click="switchTab({{ $plan['delivery_order'] }})" 
                            class="px-3.5 py-2 rounded-2xl border text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer {{ $isActive ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                        <span>Đợt {{ $plan['delivery_order'] }} ({{ $plan['date_label'] }})</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.5 rounded {{ $isFull ? ($isActive ? 'bg-brand-800 text-white' : 'bg-brand-100 text-brand-800') : 'bg-amber-100 text-amber-800' }}">
                            {{ number_format($weight, 1) }}kg
                        </span>
                    </button>
                @endforeach
            </div>

            <!-- CATALOG RAU CỦ VÀ SIDEBAR TÓM TẮT -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Cột trái: Catalog rau củ -->
                <div class="lg:col-span-8 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($veggieCatalog as $v)
                            @php
                                $qty = $currentDelivery['items'][$v['id']] ?? 0;
                            @endphp
                            <div class="bg-white rounded-2xl p-3.5 border {{ $qty > 0 ? 'border-brand-500 bg-brand-50/10' : 'border-gray-200' }} flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="{{ $v['image'] }}" class="w-12 h-12 rounded-xl object-cover border shrink-0">
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-xs text-gray-900 truncate">{{ $v['name'] }}</h4>
                                        <span class="text-[10px] font-mono text-gray-400 block">{{ $v['unit_label'] }} (~{{ $v['weight'] }}kg)</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl shrink-0">
                                    <button type="button" 
                                            wire:click="updateQty({{ $v['id'] }}, -1)" 
                                            @disabled($isLocked) 
                                            class="w-6 h-6 rounded-lg bg-white text-gray-700 font-bold flex items-center justify-center text-xs shadow-xs disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">-</button>
                                    
                                    <span class="w-5 text-center font-mono font-bold text-xs">{{ $qty }}</span>
                                    
                                    <button type="button" 
                                            wire:click="updateQty({{ $v['id'] }}, 1)" 
                                            @disabled($isLocked) 
                                            class="w-6 h-6 rounded-lg bg-brand-600 text-white font-bold flex items-center justify-center text-xs shadow-xs disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Cột phải: Sidebar tóm tắt đợt này -->
                <div class="lg:col-span-4 sticky top-28 space-y-4">
                    <div class="bg-gray-50/70 rounded-3xl border border-gray-200/80 p-5 space-y-4">
                        <div class="border-b border-gray-200 pb-3 flex items-center justify-between">
                            <h3 class="font-extrabold text-sm text-gray-900">
                                Món Cho Ngày {{ $currentDelivery['date_label'] }}
                            </h3>
                            <span class="text-xs font-mono font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded">
                                {{ number_format($currentWeight, 1) }} / {{ number_format($maxWeightTarget, 1) }} kg
                            </span>
                        </div>

                        <!-- Selected items list -->
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                            @forelse($currentDelivery['items'] as $id => $qty)
                                @php
                                    $item = collect($veggieCatalog)->firstWhere('id', $id);
                                @endphp
                                @if($item)
                                    <div class="flex items-center justify-between text-xs p-2 rounded-xl bg-white border border-gray-200">
                                        <span class="font-semibold text-gray-800 truncate">{{ $item['name'] }}</span>
                                        <span class="font-mono text-brand-700 font-bold">{{ $qty }} x {{ $item['unit_label'] }}</span>
                                    </div>
                                @endif
                            @empty
                                <p class="text-xs text-gray-400 italic py-4 text-center">Chưa chọn món cho {{ $currentDelivery['date_label'] }}</p>
                            @endforelse
                        </div>

                        <div class="pt-2 space-y-2">
                            @if(!$isLocked)
                                <button type="button" 
                                        wire:click="copyMenuToAllDeliveries" 
                                        class="w-full bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 font-bold py-2.5 rounded-xl transition text-xs flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-copy"></i>
                                    <span>Sao chép đợt này cho cả 8 đợt</span>
                                </button>

                                <button type="button" 
                                        x-data 
                                        @click="
                                            Swal.fire({
                                                title: 'Xác Nhận & Khóa Cố Định Hợp Đồng?',
                                                html: '<div class=\'text-left text-xs space-y-2 pt-2\'><p class=\'text-slate-600\'>Sau khi bấm <b>Xác nhận</b>, Địa chỉ giao hàng, Lịch 2 ngày cố định và Thực đơn 8 đợt sẽ bị <b>KHÓA VĨNH VIỄN</b>.</p><div class=\'p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 font-bold\'>⚠️ Bạn đã kiểm tra kỹ địa chỉ và danh sách món cho 8 đợt chưa?</div></div>',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#15803d',
                                                cancelButtonColor: '#64748b',
                                                confirmButtonText: '<i class=\'fa-solid fa-lock mr-1\'></i> Đồng Ý & Khóa Lịch',
                                                cancelButtonText: 'Kiểm tra lại'
                                            }).then((res) => {
                                                if (res.isConfirmed) {
                                                    $wire.confirmAndLockSchedule();
                                                }
                                            })
                                        " 
                                        class="w-full bg-brand-600 hover:bg-brand-700 text-white font-extrabold py-3.5 rounded-2xl transition shadow-md flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer">
                                    <i class="fa-solid fa-lock"></i>
                                    <span>Xác Nhận & Chốt Lịch Cố Định</span>
                                </button>
                            @else
                                <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-center space-y-1">
                                    <span class="text-emerald-800 font-extrabold text-xs flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-circle-check text-base"></i> Đã Chốt Cố Định Lịch Giao
                                    </span>
                                    <span class="text-[11px] text-slate-500 block">Nông trại Đất Lành sẽ thu hoạch và giao đúng lịch đã đăng ký.</span>
                                </div>
                                <a href="#" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-bold py-3 rounded-2xl transition text-xs flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-user"></i> Xem Quản Lý Gói VIP
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- MODAL QUẢN LÝ / CHỌN ĐỊA CHỈ NHẬN HÀNG -->
    @if($showAddressModal)
        <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-gray-100 space-y-5 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">Chọn Địa Chỉ Nhận Hàng VIP</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Địa chỉ này sẽ được ghim cho tất cả 8 đợt giao trong tháng</p>
                    </div>
                    <button type="button" 
                            wire:click="$set('showAddressModal', false)" 
                            class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach($addresses as $addr)
                        @php
                            $isSelectedAddr = $addr['id'] === $selectedAddressId;
                        @endphp
                        <div wire:click="selectAddress({{ $addr['id'] }})"
                             class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-start gap-3 {{ $isSelectedAddr ? 'border-brand-500 bg-brand-50/20' : 'border-gray-200 hover:border-gray-300 bg-white' }}">
                            <input type="radio" name="modal_address_radio" @checked($isSelectedAddr) class="mt-1 text-brand-600 focus:ring-brand-500">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-bold text-gray-900">{{ $addr['name'] }}</span>
                                    <span class="text-xs text-gray-500 font-mono">{{ $addr['phone'] }}</span>
                                    @if($addr['is_default'])
                                        <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full border border-brand-200">Mặc định</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                    {{ $addr['address'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-3">
                    <a href="#" target="_blank" class="text-xs font-semibold text-gray-500 hover:text-brand-600 flex items-center gap-1">
                        <i class="fa-solid fa-pen-to-square"></i> Quản lý trong hồ sơ
                    </a>
                    <button type="button" 
                            wire:click="$set('showAddressModal', false)" 
                            class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition-all shadow-sm cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- SWEETALERT2 EVENTS -->
@script
<script>
    $wire.on('swal-warning', (data) => {
        const info = data[0];
        Swal.fire({
            icon: 'warning',
            title: info.title,
            text: info.text,
            confirmButtonColor: '#15803d'
        });
    });

    $wire.on('swal-success', (data) => {
        const info = data[0];
        Swal.fire({
            icon: 'success',
            title: info.title,
            text: info.text,
            confirmButtonColor: '#15803d'
        });
    });
</script>
@endscript