<div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">
    <!-- ================= 1. HEADER CHÍNH ================= -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
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

        <!-- ================= 3 THẺ THỐNG KÊ KPI ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-5">
            
            <!-- Thẻ 1: Lô nguy cấp <= 1 ngày -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-rose-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-rose-600 block uppercase tracking-wider">Hạn Còn &le; 1 Ngày</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-rose-600 tracking-tight font-mono">{{ str_pad($urgentCount, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[10px] font-extrabold bg-rose-50 text-rose-700 px-2 py-0.5 rounded-full border border-rose-200 animate-pulse">Giảm 50%</span>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Lô hết hạn hôm nay hoặc ngày mai</p>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>

            <!-- Thẻ 2: Lô cận hạn 2 ngày -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-amber-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-amber-700 block uppercase tracking-wider">Hạn Còn 2 Ngày</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-amber-700 tracking-tight font-mono">{{ str_pad($nearCount, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[10px] font-bold bg-amber-50 text-amber-800 px-2 py-0.5 rounded-full border border-amber-200">Giảm 30%</span>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Lô hết hạn vào ngày mốt</p>
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
                        <span class="text-2xl sm:text-3xl font-black text-emerald-800 tracking-tight font-mono">{{ $activeSaleCount }} lô</span>
                        <span class="text-xs text-emerald-700 font-bold font-mono">({{ number_format($activeSaleQty) }} kg)</span>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Đang hiển thị giá giảm ngoài Website</p>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-bolt"></i>
                </div>
            </div>

        </div>

        <!-- ================= BẢNG DANH SÁCH: CÁC LÔ NÔNG SẢN CẬN DATE ================= -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden space-y-3">
            
            <!-- Toolbar: Tiêu đề + Nút thao tác nhanh -->
            <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/60">
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-fire text-amber-500"></i>
                        <span>Danh Sách Lô Cận Date Cần Xả Kho (Hạn Còn &le; 2 Ngày)</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Tự động áp dụng giảm <b>30%</b> (còn 2 ngày) và <b>50%</b> (còn 1 ngày) khi bật Flash Sale.</p>
                </div>

                @if($notSaleCount > 0)
                    <button type="button" 
                            wire:click="enableAllNearExpiry"
                            wire:confirm="Bạn có chắc muốn kích hoạt Flash Sale cho tất cả các lô cận date chưa áp dụng?"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition shadow-sm active:scale-95 shrink-0 cursor-pointer disabled:opacity-50">
                        <i class="fa-solid fa-bolt" wire:loading.remove wire:target="enableAllNearExpiry"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="enableAllNearExpiry"></i>
                        <span>Bật Sale Tất Cả ({{ $notSaleCount }} lô)</span>
                    </button>
                @endif
            </div>

            <!-- Thanh Tabs & Tìm Kiếm -->
            <div class="px-4 sm:px-6 pt-1 flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-gray-100">
                <!-- Tabs Lọc -->
                <div class="flex overflow-x-auto gap-4 sm:gap-6 text-sm no-scrollbar">
                    <button type="button" 
                            wire:click="setTabFilter('all')" 
                            class="pb-3 transition whitespace-nowrap flex items-center gap-2 cursor-pointer font-bold {{ $tabFilter === 'all' ? 'text-brand-700 border-b-2 border-brand-700' : 'text-gray-500 hover:text-gray-700' }}">
                        <span>Tất cả lô cận date</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $tabFilter === 'all' ? 'bg-brand-50 text-brand-800' : 'bg-gray-100 text-gray-600' }}">{{ $totalCount }}</span>
                    </button>

                    <button type="button" 
                            wire:click="setTabFilter('on_sale')" 
                            class="pb-3 transition whitespace-nowrap flex items-center gap-2 cursor-pointer font-bold {{ $tabFilter === 'on_sale' ? 'text-amber-700 border-b-2 border-amber-600' : 'text-gray-500 hover:text-gray-700' }}">
                        <span>Đang chạy Flash Sale</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $tabFilter === 'on_sale' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">{{ $activeSaleCount }}</span>
                    </button>

                    <button type="button" 
                            wire:click="setTabFilter('not_sale')" 
                            class="pb-3 transition whitespace-nowrap flex items-center gap-2 cursor-pointer font-bold {{ $tabFilter === 'not_sale' ? 'text-rose-700 border-b-2 border-rose-600' : 'text-gray-500 hover:text-gray-700' }}">
                        <span>Chưa áp dụng</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $tabFilter === 'not_sale' ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-600' }}">{{ $notSaleCount }}</span>
                    </button>
                </div>

                <!-- Ô Tìm Kiếm -->
                <div class="relative w-full md:w-72 pb-3 md:pb-0">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Tìm mã lô, tên nông sản..." 
                           class="w-full pl-9 pr-3.5 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-brand-500 bg-[#FAFAF8] font-medium transition-all">
                </div>
            </div>

            <!-- ================= DANH SÁCH LÔ CẬN DATE: CARD CHO MOBILE (< md) ================= -->
            <div class="md:hidden divide-y divide-gray-100">
                @forelse($batches as $batch)
                    @php
                        $product = $batch->plantingBatch?->product;
                        $rawImg = $product?->productImages?->firstWhere('is_primary', true)?->image
                            ?? $product?->productImages?->first()?->image;
                        $imgUrl = $rawImg
                            ? (str_starts_with($rawImg, 'http') ? $rawImg : ($product?->cover_image_url ?? asset('storage/' . $rawImg)))
                            : 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=200';

                        $daysLeft = today()->diffInDays($batch->expiry_date, false);
                        $suggestedPercent = ($daysLeft <= 1) ? 50 : 30;
                        $isOnSale = $batch->discount_percent > 0;
                        $originalPrice = (float) ($product?->price ?? 0);
                        $salePrice = $isOnSale ? round($originalPrice * (100 - $batch->discount_percent) / 100) : $originalPrice;
                        $availQty = $batch->available_quantity;
                        $resQty = (int) ($batch->reserved_quantity ?? 0);
                    @endphp
                    <div wire:key="mobile-batch-{{ $batch->id }}" class="p-4 space-y-3 {{ $isOnSale ? 'bg-amber-50/15' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <img src="{{ $imgUrl }}" alt="{{ $product?->name ?? 'Nông sản' }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100 shrink-0 shadow-2xs">
                                <div class="min-w-0">
                                    <p class="font-extrabold text-gray-900 text-xs sm:text-sm leading-snug line-clamp-2">
                                        {{ $product?->name ?? 'Nông sản chưa rõ' }}
                                    </p>
                                    <span class="inline-flex items-center font-mono text-[10px] font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded border border-gray-200 mt-1">
                                        {{ $batch->batch_code }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Tồn kho</span>
                                <span class="font-black text-sm {{ $availQty <= 0 ? 'text-rose-600' : 'text-gray-900' }} font-mono">
                                    {{ number_format($availQty) }} <span class="text-[11px] font-medium text-gray-500">kg</span>
                                </span>
                                @if($resQty > 0)
                                    <span class="block text-[10px] text-amber-700 font-bold font-mono">(Giữ {{ number_format($resQty) }}kg)</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <span class="text-gray-500 font-medium">
                                HSD: <b class="text-gray-800 font-mono">{{ $batch->expiry_date->format('d/m/Y') }}</b>
                            </span>
                            @if($daysLeft <= 0)
                                <span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full text-[10px] font-mono">
                                    <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Hết hạn hôm nay (Sale 50%)
                                </span>
                            @elseif($daysLeft == 1)
                                <span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full text-[10px] font-mono">
                                    <i class="fa-solid fa-clock text-[9px]"></i> Còn 1 ngày (Sale 50%)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 font-bold text-amber-800 bg-amber-50 border border-amber-300 px-2.5 py-0.5 rounded-full text-[10px] font-mono">
                                    <i class="fa-regular fa-clock text-[9px]"></i> Còn 2 ngày (Sale 30%)
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100">
                            <div>
                                @if($isOnSale)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-black text-sm text-rose-600 font-mono">{{ number_format($salePrice, 0, ',', '.') }}đ</span>
                                        <span class="text-[10px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded font-mono">-{{ $batch->discount_percent }}%</span>
                                        <span class="text-[11px] text-gray-400 line-through font-mono">{{ number_format($originalPrice, 0, ',', '.') }}đ</span>
                                    </div>
                                @else
                                    <span class="font-bold text-sm text-gray-900 font-mono">{{ number_format($originalPrice, 0, ',', '.') }}đ <span class="text-[10px] text-gray-400 font-normal">/kg</span></span>
                                @endif
                            </div>

                            <div class="shrink-0">
                                @if(!$isOnSale)
                                    <button type="button"
                                            wire:click="enableFlashSale({{ $batch->id }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition shadow-2xs active:scale-95 cursor-pointer whitespace-nowrap disabled:opacity-50">
                                        <i class="fa-solid fa-bolt text-[10px]" wire:loading.remove wire:target="enableFlashSale({{ $batch->id }})"></i>
                                        <i class="fa-solid fa-spinner fa-spin text-[10px]" wire:loading wire:target="enableFlashSale({{ $batch->id }})"></i>
                                        <span>Bật Sale -{{ $suggestedPercent }}%</span>
                                    </button>
                                @else
                                    <button type="button"
                                            wire:click="disableFlashSale({{ $batch->id }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center gap-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs px-3.5 py-2 rounded-xl transition active:scale-95 cursor-pointer whitespace-nowrap disabled:opacity-50">
                                        <i class="fa-solid fa-power-off text-[10px]" wire:loading.remove wire:target="disableFlashSale({{ $batch->id }})"></i>
                                        <i class="fa-solid fa-spinner fa-spin text-[10px]" wire:loading wire:target="disableFlashSale({{ $batch->id }})"></i>
                                        <span>Tắt Sale</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 px-4 text-center">
                        <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-2.5 text-2xl">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <p class="text-sm font-bold text-gray-800">Kho hàng an toàn! Không có lô nào cận date &le; 2 ngày</p>
                        <p class="text-xs text-gray-400 mt-0.5">Tất cả nông sản trong kho đều còn hạn sử dụng an toàn và tươi mới.</p>
                    </div>
                @endforelse
            </div>

            <!-- ================= BẢNG 5 CỘT TINH GỌN CHO TABLET & DESKTOP (>= md) ================= -->
            <div class="hidden md:block w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[760px] text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-gray-100">
                            <th class="py-3 px-4 whitespace-nowrap">Nông Sản & Mã Lô</th>
                            <th class="py-3 px-3 whitespace-nowrap">Hạn Dùng & Cảnh Báo</th>
                            <th class="py-3 px-3 text-center whitespace-nowrap">Tồn Kho</th>
                            <th class="py-3 px-3 text-right whitespace-nowrap">Giá Bán</th>
                            <th class="py-3 px-4 text-center whitespace-nowrap">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @forelse($batches as $batch)
                            @php
                                $product = $batch->plantingBatch?->product;
                                $rawImg = $product?->productImages?->firstWhere('is_primary', true)?->image
                                    ?? $product?->productImages?->first()?->image;
                                $imgUrl = $rawImg
                                    ? (str_starts_with($rawImg, 'http') ? $rawImg : ($product?->cover_image_url ?? asset('storage/' . $rawImg)))
                                    : 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=200';
                                
                                $daysLeft = today()->diffInDays($batch->expiry_date, false);
                                $isUrgent = $daysLeft <= 1;
                                $suggestedPercent = $isUrgent ? 50 : 30;
                                
                                $isOnSale = $batch->discount_percent > 0;
                                $originalPrice = (float) ($product?->price ?? 0);
                                $salePrice = $isOnSale ? round($originalPrice * (100 - $batch->discount_percent) / 100) : $originalPrice;
                            @endphp
                            <tr wire:key="desktop-batch-{{ $batch->id }}" class="hover:bg-amber-50/30 transition-colors {{ $isOnSale ? 'bg-amber-50/15' : '' }}">
                                
                                <!-- Cột 1: Nông Sản & Mã Lô -->
                                <td class="py-3 px-4 align-middle">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <img src="{{ $imgUrl }}" alt="{{ $product?->name ?? 'Nông sản' }}" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl object-cover border border-gray-100 shrink-0 shadow-2xs">
                                        <div class="min-w-0">
                                            <p class="font-extrabold text-gray-900 text-xs sm:text-sm truncate max-w-[240px] leading-snug" title="{{ $product?->name }}">
                                                {{ $product?->name ?? 'Nông sản chưa rõ' }}
                                            </p>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="inline-flex items-center font-mono text-[10px] sm:text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.2 rounded border border-gray-200 whitespace-nowrap">
                                                    {{ $batch->batch_code }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Cột 2: Hạn Dùng & Cảnh Báo -->
                                <td class="py-3 px-3 align-middle whitespace-nowrap">
                                    <div class="leading-tight">
                                        <span class="text-xs text-gray-700 font-semibold block">
                                            {{ $batch->expiry_date->format('d/m/Y') }}
                                        </span>
                                        @if($daysLeft <= 0)
                                            <span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full text-[10px] mt-1 font-mono animate-pulse">
                                                <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Hết hạn hôm nay (Sale 50%)
                                            </span>
                                        @elseif($daysLeft == 1)
                                            <span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full text-[10px] mt-1 font-mono">
                                                <i class="fa-solid fa-clock text-[9px]"></i> Còn 1 ngày (Sale 50%)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 font-bold text-amber-800 bg-amber-50 border border-amber-300 px-2 py-0.5 rounded-full text-[10px] mt-1 font-mono">
                                                <i class="fa-regular fa-clock text-[9px]"></i> Còn 2 ngày (Sale 30%)
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Cột 3: Tồn Kho Khả Dụng -->
                                <td class="py-3 px-3 text-center align-middle whitespace-nowrap">
                                    @php
                                        $availQty = $batch->available_quantity;
                                        $resQty = (int) ($batch->reserved_quantity ?? 0);
                                    @endphp
                                    <div class="leading-tight">
                                        <span class="font-black text-xs sm:text-sm {{ $availQty <= 0 ? 'text-rose-600' : 'text-gray-900' }} font-mono">
                                            {{ number_format($availQty) }}
                                        </span>
                                        <span class="text-[11px] text-gray-500 font-medium">kg</span>
                                        @if($resQty > 0)
                                            <span class="block text-[10px] text-amber-700 font-bold font-mono mt-0.5">
                                                (Giữ chỗ {{ number_format($resQty) }}kg)
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Cột 4: Giá Bán -->
                                <td class="py-3 px-3 text-right align-middle whitespace-nowrap">
                                    @if($isOnSale)
                                        <div class="leading-tight">
                                            <span class="text-[10px] sm:text-xs text-gray-400 line-through block font-mono">
                                                {{ number_format($originalPrice, 0, ',', '.') }}đ
                                            </span>
                                            <div class="flex items-center justify-end gap-1 mt-0.5">
                                                <span class="font-black text-xs sm:text-sm text-rose-600 font-mono">
                                                    {{ number_format($salePrice, 0, ',', '.') }}đ
                                                </span>
                                                <span class="text-[9px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200 px-1 py-0.2 rounded font-mono">
                                                    -{{ $batch->discount_percent }}%
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="leading-tight">
                                            <span class="font-bold text-xs sm:text-sm text-gray-900 font-mono">
                                                {{ number_format($originalPrice, 0, ',', '.') }}đ
                                            </span>
                                            <span class="text-[10px] text-gray-400 block">/kg</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Cột 5: Thao Tác (Nút Bật / Tắt Flash Sale) -->
                                <td class="py-3 px-4 text-center align-middle whitespace-nowrap">
                                    @if(!$isOnSale)
                                        <button type="button" 
                                                wire:click="enableFlashSale({{ $batch->id }})" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center justify-center gap-1 bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] sm:text-xs px-3 py-1.5 rounded-xl transition shadow-2xs active:scale-95 cursor-pointer disabled:opacity-50">
                                            <i class="fa-solid fa-bolt text-[10px]" wire:loading.remove wire:target="enableFlashSale({{ $batch->id }})"></i>
                                            <i class="fa-solid fa-spinner fa-spin text-[10px]" wire:loading wire:target="enableFlashSale({{ $batch->id }})"></i>
                                            <span>Bật Sale -{{ $suggestedPercent }}%</span>
                                        </button>
                                    @else
                                        <button type="button" 
                                                wire:click="disableFlashSale({{ $batch->id }})" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center justify-center gap-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[11px] sm:text-xs px-3 py-1.5 rounded-xl transition active:scale-95 cursor-pointer disabled:opacity-50" 
                                                title="Tắt Flash Sale, quay về giá gốc">
                                            <i class="fa-solid fa-power-off text-[10px]" wire:loading.remove wire:target="disableFlashSale({{ $batch->id }})"></i>
                                            <i class="fa-solid fa-spinner fa-spin text-[10px]" wire:loading wire:target="disableFlashSale({{ $batch->id }})"></i>
                                            <span>Tắt Sale</span>
                                        </button>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-2.5 text-2xl">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-800">Kho hàng an toàn! Không có lô nào cận date &le; 2 ngày</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Tất cả nông sản trong kho đều còn hạn sử dụng an toàn và tươi mới.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân Trang -->
            @if($batches->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $batches->links() }}
                </div>
            @endif

        </div>

    </div>
</div>
