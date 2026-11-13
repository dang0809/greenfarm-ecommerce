<div>
    <!-- MAIN CONTENT -->
    <main class="pt-28 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="pb-6 border-b border-gray-200/80 mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Thanh ToÃ¡n ÄÆ¡n HÃ ng</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-1">XÃ¡c nháº­n thÃ´ng tin giao nháº­n nÃ´ng sáº£n tÆ°Æ¡i tá»« NÃ´ng tráº¡i
                {{ $farmName }}</p>
        </div>

        <!-- FORM THANH TOÃN -->
        <form wire:submit.prevent="placeOrder">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Cá»˜T TRÃI (7 Cá»˜T): Äá»ŠA CHá»ˆ, SHIP Há»ŽA Tá»C, THANH TOÃN PAYOS -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- 1. KHá»I Äá»ŠA CHá»ˆ NHáº¬N HÃ€NG -->
                    <x-customer.address-card :address="$address" :distance="$distance" :max-distance="$maxDistance" :is-exceeded="$isDistanceExceeded"
                        :is-unknown="$isDistanceUnknown">
                        <!-- Ghi chÃº cho nÃ´ng tráº¡i -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Ghi chÃº cho nÃ´ng tráº¡i (tÃ¹y
                                chá»n)</label>
                            <input type="text" wire:model="orderNote"
                                placeholder="VÃ­ dá»¥: Giao trÆ°á»›c 11h trÆ°a, gá»i chuÃ´ng cá»­a táº§ng 2..."
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-brand-500 transition-all">
                        </div>
                    </x-customer.address-card>

                    <!-- 2. Váº¬N CHUYá»‚N -->
                    <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <i class="fa-solid fa-truck-fast text-brand-600"></i> PhÆ°Æ¡ng Thá»©c Váº­n Chuyá»ƒn
                                </h2>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    @if ($address)
                                        Khoáº£ng cÃ¡ch thá»±c táº¿: <b class="text-gray-700 font-mono">{{ $distance }}
                                            km</b>
                                    @else
                                        ChÆ°a chá»n Ä‘á»‹a chá»‰ nháº­n hÃ ng
                                    @endif
                                </p>
                            </div>
                            <span
                                class="text-[11px] font-bold {{ !$address ? 'text-gray-600 bg-gray-100 border-gray-200' : ($isDistanceExceeded ? 'text-red-700 bg-red-50 border-red-200' : 'text-brand-700 bg-brand-50 border-brand-200') }} px-2.5 py-1 rounded-full border">
                                {{ !$address ? 'ChÆ°a chá»n Ä‘á»‹a chá»‰' : ($isDistanceExceeded ? 'VÆ°á»£t bÃ¡n kÃ­nh' : 'Há»a tá»‘c') }}
                            </span>
                        </div>

                        @if (!$address)
                            <!-- Cáº¢NH BÃO CHÆ¯A CHá»ŒN Äá»ŠA CHá»ˆ -->
                            <div
                                class="p-4 rounded-2xl border border-amber-200 bg-amber-50 text-amber-800 text-xs space-y-1">
                                <p class="font-bold flex items-center gap-1.5 text-sm">
                                    <i class="fa-solid fa-circle-info text-amber-600"></i>
                                    <span>ChÆ°a cÃ³ thÃ´ng tin khoáº£ng cÃ¡ch</span>
                                </p>
                                <p class="leading-relaxed">
                                    Vui lÃ²ng chá»n hoáº·c thÃªm Ä‘á»‹a chá»‰ nháº­n hÃ ng á»Ÿ bÆ°á»›c trÃªn Ä‘á»ƒ há»‡ thá»‘ng tá»± Ä‘á»™ng tÃ­nh
                                    khoáº£ng cÃ¡ch vÃ  phÃ­ giao há»a tá»‘c.
                                </p>
                            </div>
                        @elseif ($isDistanceExceeded)
                            <!-- Cáº¢NH BÃO VÆ¯á»¢T BÃN KÃNH -->
                            <div class="p-4 rounded-2xl border border-red-200 bg-red-50 text-red-700 text-xs space-y-1">
                                <p class="font-bold flex items-center gap-1.5 text-sm">
                                    <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                                    <span>Ráº¥t tiáº¿c, nÃ´ng tráº¡i chÆ°a thá»ƒ giao Ä‘áº¿n Ä‘á»‹a chá»‰ nÃ y!</span>
                                </p>
                                <p class="leading-relaxed">
                                    Khoáº£ng cÃ¡ch tá»« nÃ´ng tráº¡i Ä‘áº¿n Ä‘á»‹a chá»‰ nháº­n hÃ ng lÃ  <b>{{ $distance }} km</b>,
                                    vÆ°á»£t quÃ¡ bÃ¡n kÃ­nh giao hÃ ng há»a tá»‘c tá»‘i Ä‘a (tá»‘i Ä‘a <b>{{ $maxDistance }} km</b>).
                                    Vui lÃ²ng chá»n Ä‘á»‹a chá»‰ nháº­n hÃ ng khÃ¡c gáº§n hÆ¡n.
                                </p>
                            </div>
                        @else
                            <!-- THáºº GIAO Há»ŽA Tá»C BÃŒNH THÆ¯á»œNG -->
                            <div
                                class="flex items-start justify-between p-4 rounded-2xl border-2 border-brand-500 bg-brand-50/40">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                        <i class="fa-solid fa-bolt-lightning text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-900">Giao Há»a Tá»‘c (2H Giá»¯ Trá»n Äá»™
                                                TÆ°Æ¡i)</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                            ÄÆ¡n hÃ ng Ä‘Æ°á»£c Ä‘Ã³ng thÃ¹ng xá»‘p thoÃ¡ng khÃ­ vÃ  Ä‘iá»u phá»‘i shipper giao ngay, giá»¯
                                            trá»n Ä‘á»™ tÆ°Æ¡i giÃ²n tá»± nhiÃªn vÃ  chá»‘ng dáº­p nÃ¡t.
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-3">
                                    <span
                                        class="text-sm font-extrabold text-brand-700 block">{{ number_format($shippingFee) }}Ä‘</span>
                                    <span class="text-[10px] text-gray-400 font-medium">{{ $distance }} km</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- 3. HÃŒNH THá»¨C THANH TOÃN: PAYOS -->
                    <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                        <div class="pb-3 border-b border-gray-100">
                            <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                <i class="fa-solid fa-credit-card text-brand-600"></i> PhÆ°Æ¡ng Thá»©c Thanh ToÃ¡n
                            </h2>
                            <p class="text-xs text-gray-400 mt-0.5">Thanh toÃ¡n trá»±c tuyáº¿n an toÃ n qua cá»•ng PayOS</p>
                        </div>

                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between p-4 rounded-2xl border-2 border-brand-500 bg-brand-50/30">
                                <div class="flex items-center gap-3">
                                    <input type="radio" checked disabled class="text-brand-600 focus:ring-brand-500">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm shrink-0">
                                            <i class="fa-solid fa-qrcode"></i>
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold text-gray-900 block leading-tight">Thanh toÃ¡n
                                                trá»±c tuyáº¿n qua PayOS</span>
                                            <span class="text-[11px] text-gray-500">QuÃ©t mÃ£ VietQR trÃªn á»©ng dá»¥ng ngÃ¢n
                                                hÃ ng hoáº·c tháº» ATM / Visa</span>
                                        </div>
                                    </div>
                                </div>
                                <span
                                    class="text-[10px] font-bold bg-brand-100 text-brand-800 px-2 py-0.5 rounded-full border border-brand-200">Máº·c
                                    Ä‘á»‹nh</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Cá»˜T PHáº¢I (5 Cá»˜T): TÃ“M Táº®T ÄÆ N HÃ€NG -->
                <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-28">

                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="font-bold text-gray-900 text-base">ÄÆ¡n HÃ ng Cá»§a Báº¡n</h3>
                            @if (!$isBuyNow)
                                <a href="{{ route('customer.cart') }}"
                                    class="text-xs font-semibold text-brand-600 hover:underline">Sá»­a giá» hÃ ng</a>
                            @endif
                        </div>

                        <!-- Danh sÃ¡ch máº·t hÃ ng -->
                        <div class="space-y-3.5 max-h-64 overflow-y-auto pr-1 no-scrollbar">
                            @forelse ($items as $item)
                                @php
                                    $product = $item->product;
                                    $primaryImage =
                                        $product->cover_image_url ??
                                        'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=300';

                                    $unitPrice = $product->sale_price ?? $product->price;
                                    $priceData = $product->getPriceBreakdownForQuantity((int) $item->quantity);
                                    $itemSubtotal = $priceData['total'];
                                    $hasSplit = count($priceData['breakdown']) > 1;
                                @endphp

                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="{{ $primaryImage }}" alt="{{ $product->name }}"
                                            class="w-12 h-12 rounded-xl object-cover border border-gray-100 shrink-0">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gray-900 truncate">{{ $product->name }}
                                            </p>
                                            @if ($hasSplit)
                                                <div class="text-[11px] text-gray-500 font-medium space-y-0.5 mt-0.5">
                                                    @foreach ($priceData['breakdown'] as $b)
                                                        <div class="flex items-center gap-1">
                                                            <span class="$b['is_sale'] ? 'text-rose-600 font-bold' : 'text-gray-600' }}">
                                                                {{ $b['quantity'] }}{{ $product->unit ?? 'kg' }} {{ $b['is_sale'] ? '(-' . $b['discount_percent'] . '%)' : '(Gốc)' }}
                                                            </span>
                                                            <span class="text-gray-400">Ã—
                                                                {{ number_format($b['unit_price']) }}Ä‘</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-[11px] text-gray-400">
{{ number_format($priceData['breakdown'][0]['unit_price'] ?? $unitPrice) }}Ä‘ Ã— {{ $item->quantity }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-800 shrink-0">
                                        {{ number_format($itemSubtotal) }}Ä‘
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 text-center py-4">KhÃ´ng cÃ³ sáº£n pháº©m nÃ o cáº§n thanh toÃ¡n.
                                </p>
                            @endforelse
                        </div>

                        <!-- Chi tiáº¿t thanh toÃ¡n -->
                        <div class="pt-4 border-t border-gray-100 space-y-3 text-xs sm:text-sm text-gray-600">
                            <!-- Táº¡m tÃ­nh tiá»n hÃ ng -->
                            <div class="flex justify-between">
                                <span>Táº¡m tÃ­nh tiá»n hÃ ng:</span>
                                <span class="font-bold text-gray-900">{{ number_format($subtotal) }}Ä‘</span>
                            </div>

                            <!-- DÃ™NG ÄIá»‚M THÆ¯á»žNG -->
                            <div class="p-3 bg-gray-50 border border-gray-200/80 rounded-2xl">
                                <label class="flex items-start justify-between gap-3 cursor-pointer">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" wire:model.live="usePoints" @disabled(!$canUsePoints)
                                            class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500 cursor-pointer disabled:opacity-50">
                                        <div>
                                            <span class="block text-xs font-bold text-gray-800">
                                                @if ($usePoints && $canUsePoints)
                                                    DÃ¹ng {{ number_format($pointsUsed) }} Ä‘iá»ƒm (1p =
                                                    {{ number_format($valuePerPoint) }}Ä‘)
                                                @else
                                                    DÃ¹ng Ä‘iá»ƒm thÆ°á»Ÿng tÃ­ch lÅ©y
                                                @endif
                                            </span>
                                            <span class="block text-[11px] text-gray-400">
                                                VÃ­ báº¡n Ä‘ang cÃ³: <b
                                                    class="text-brand-700">{{ number_format($userPoints) }}</b> Ä‘iá»ƒm
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-brand-700">
                                        -{{ number_format($discount, 0, ',', '.') }}Ä‘
                                    </span>
                                </label>

                                @if ($canUsePoints && $maxDiscountPercent > 0)
                                    <p class="text-[10px] text-gray-400 mt-1 italic">
                                        * Trá»« tá»‘i Ä‘a {{ $maxDiscountPercent }}% giÃ¡ trá»‹ tiá»n hÃ ng.
                                    </p>
                                @elseif($userPoints == 0)
                                    <p class="text-[10px] text-amber-600 mt-1 italic">
                                        TÃ i khoáº£n chÆ°a cÃ³ Ä‘iá»ƒm tÃ­ch lÅ©y.
                                    </p>
                                @endif
                            </div>

                            <!-- PhÃ­ váº­n chuyá»ƒn há»a tá»‘c -->
                            <div class="flex justify-between items-center">
                                <span>PhÃ­ giao há»a tá»‘c:</span>
                                @if ($address)
                                    <span class="font-bold text-gray-900">{{ number_format($shippingFee) }}Ä‘ <span
                                            class="text-xs font-normal text-gray-400">({{ $distance }}
                                            km)</span></span>
                                @else
                                    <span class="font-semibold text-amber-600">ChÆ°a chá»n Ä‘á»‹a chá»‰</span>
                                @endif
                            </div>
                        </div>

                        <!-- KHá»I ÄIá»‚M THÆ¯á»žNG TÃCH LÅ¨Y -->
                        <div
                            class="p-3.5 bg-brand-50 border border-brand-200 rounded-2xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs shrink-0 shadow-xs">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-brand-900 leading-tight">Äiá»ƒm tÃ­ch lÅ©y khi
                                        mua:</span>
                                    <span class="block text-[11px] text-brand-700">Tá»· lá»‡: 100k nháº­n
                                        {{ number_format($pointsPer100k) }} Ä‘iá»ƒm</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span
                                    class="text-sm font-extrabold text-brand-700 block">+{{ number_format($earnedPoints) }}
                                    Ä‘iá»ƒm</span>
                                <span class="text-[10px] text-gray-500 font-medium">(=
                                    {{ number_format($earnedPointsValue) }}Ä‘)</span>
                            </div>
                        </div>

                        <!-- Tá»•ng thanh toÃ¡n cuá»‘i cÃ¹ng -->
                        <div class="pt-4 border-t border-gray-100 flex items-baseline justify-between">
                            <div>
                                <span class="block text-xs text-gray-400">Tá»•ng thanh toÃ¡n:</span>
                                <span class="text-[11px] text-gray-400">(ÄÃ£ gá»“m phÃ­ ship há»a tá»‘c)</span>
                            </div>
                            <span class="text-2xl font-black text-brand-700">{{ number_format($totalAmount) }}Ä‘</span>
                        </div>

                        <!-- NÃºt XÃ¡c Nháº­n Äáº·t HÃ ng PayOS Desktop -->
                        <button type="submit" wire:loading.attr="disabled" @disabled(!$address || $isDistanceExceeded || $isDistanceUnknown)
                            class="w-full bg-brand-600 hover:bg-brand-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed text-white py-3.5 rounded-2xl font-bold text-sm sm:text-base flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-98 cursor-pointer">
                            @if (!$address)
                                <i class="fa-solid fa-location-dot text-xs"></i>
                                <span>Vui lÃ²ng chá»n Ä‘á»‹a chá»‰ nháº­n hÃ ng</span>
                            @elseif ($isDistanceUnknown)
                                <i class="fa-solid fa-triangle-exclamation text-xs text-amber-500"></i>
                                <span>Äá»‹a chá»‰ chÆ°a xÃ¡c Ä‘á»‹nh Ä‘Æ°á»£c vá»‹ trÃ­</span>
                            @elseif ($isDistanceExceeded)
                                <i class="fa-solid fa-ban text-xs"></i>
                                <span>VÆ°á»£t BÃ¡n KÃ­nh Giao HÃ ng (Tá»‘i Ä‘a {{ $maxDistance }}km)</span>
                            @else
                                <i class="fa-solid fa-lock text-xs" wire:loading.remove></i>
                                <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading></i>
                                <span wire:loading.remove>XÃ¡c nháº­n & Thanh toÃ¡n PayOS</span>
                                <span wire:loading>Äang káº¿t ná»‘i PayOS...</span>
                            @endif
                        </button>

                        <p class="text-[11px] text-gray-400 text-center leading-relaxed">
                            Báº±ng viá»‡c nháº¥n Ä‘áº·t hÃ ng, báº¡n Ä‘á»“ng Ã½ vá»›i <a href="#"
                                class="text-brand-600 hover:underline">ChÃ­nh sÃ¡ch kiá»ƒm hÃ ng & bá»“i thÆ°á»ng 24H</a> cá»§a
                            {{ $farmName }}.
                        </p>
                    </div>

                    <!-- Cam káº¿t nhanh -->
                    <div
                        class="bg-brand-50/50 border border-brand-100 rounded-2xl p-4 flex items-center gap-3 text-xs text-brand-900">
                        <i class="fa-solid fa-shield-halved text-brand-600 text-lg shrink-0"></i>
                        <p>Kiá»ƒm tra hÃ ng trÆ°á»›c khi thanh toÃ¡n. Äá»•i tráº£ / bÃ¹ hÃ ng 24H náº¿u nÃ´ng sáº£n bá»‹ dáº­p nÃ¡t.</p>
                    </div>

                </div>
            </div>
        </form>
    </main>

    <!-- MODAL CHá»ŒN Äá»ŠA CHá»ˆ NHáº¬N HÃ€NG -->
    <x-customer.address-modal :show="$showAddressModal" :addresses="$addresses" :selected-id="$selectedAddressId" :max-distance="$maxDistance" />
</div>
