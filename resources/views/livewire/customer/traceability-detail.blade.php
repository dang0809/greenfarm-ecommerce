<div>
    <main class="pt-28 pb-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs -->
        <nav class="flex text-xs sm:text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-2">
                <li><a href="{{ route('home') }}" class="hover:text-brand-600">Trang chủ</a></li>
                <li><i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i></li>
                <li><span class="text-gray-500">Truy xuất</span></li>
                <li><i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i></li>
                <li class="text-gray-900 font-semibold font-mono">{{ $batchCode }}</li>
            </ol>
        </nav>

        @php
            $product = $plantingBatch?->product;
            $farmPlot = $plantingBatch?->farmPlot;
            $productImage = $product?->productImages?->firstWhere('is_primary', true)?->image 
                ?? $product?->productImages?->first()?->image;
            $imageUrl = $productImage 
                ? (str_starts_with($productImage, 'http') ? $productImage : asset('storage/' . $productImage))
                : 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?auto=format&fit=crop&q=80&w=300';
            $events = $plantingBatch?->traceabilityEvents;
        @endphp

        <!-- 1. KHỐI THÔNG TIN LÔ HÀNG & MẢNH ĐẤT CANH TÁC -->
        <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 shadow-sm mb-10">
            <div class="flex items-center gap-4 sm:gap-5">
                <img src="{{ $imageUrl }}" alt="{{ $product?->name ?? 'Nông sản hữu cơ' }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border border-gray-100 shrink-0">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-mono font-bold text-brand-800 bg-brand-50 border border-brand-200 px-3 py-1 rounded-xl">
                            Mã Lô SP: {{ $batchCode }}
                        </span>
                        @if ($plantingBatch?->planting_code)
                            <span class="text-xs font-mono font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-xl">
                                Đợt trồng: {{ $plantingBatch->planting_code }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-tight">
                        {{ $product?->name ?? 'Cải Bó Xôi Hữu Cơ (Spinach)' }}
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-500 mt-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-brand-600"></i>
                        <span>
                            {{ $farmPlot?->name ?? 'Mảnh đất canh tác hữu cơ' }} - Nông trại Đất Lành (Đơn Dương, Lâm Đồng)
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. KHỐI DÒNG THỜI GIAN NHẬT KÝ CANH TÁC -->
        <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-10 shadow-sm mb-12">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100">
                <div>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-timeline text-brand-600"></i> Hành Trình Canh Tác Thực Tế
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Dữ liệu từng công đoạn được ký số minh bạch và băm SHA-256 chống chỉnh sửa</p>
                </div>
                <span class="hidden sm:inline-block text-xs font-bold text-gray-400 font-mono">
                    {{ $events?->count() ?? 0 }} Sự kiện ghi nhận
                </span>
            </div>

            <!-- Timeline Dọc -->
            <div class="relative pl-6 sm:pl-8 space-y-8 sm:space-y-10 before:absolute before:left-2 sm:before:left-3 before:top-3 before:bottom-3 before:w-0.5 before:bg-brand-100">
                
                @if ($events && $events->count() > 0)
                    @foreach ($events as $event)
                        <div class="relative group">
                            <div class="absolute -left-[27px] sm:-left-[35px] top-1 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs shadow-md border-2 border-white">
                                <i class="fa-solid fa-leaf text-[10px] sm:text-xs"></i>
                            </div>

                            <div class="bg-brand-50/40 border border-brand-200/80 rounded-2xl p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-2">
                                    <h3 class="text-base sm:text-lg font-bold text-gray-900">
                                        {{ $event->event_name }}
                                    </h3>
                                    <time class="text-xs font-semibold text-brand-700 font-mono">
                                        {{ $event->recorded_at ? $event->recorded_at->format('H:i • d/m/Y') : '' }}
                                    </time>
                                </div>

                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-3">
                                    {{ $event->description }}
                                </p>

                                @if ($event->image)
                                    <div class="flex gap-2 sm:gap-3 mb-4">
                                        <img src="{{ str_starts_with($event->image, 'http') ? $event->image : asset('storage/' . $event->image) }}" 
                                             alt="{{ $event->event_name }}" 
                                             class="w-24 h-24 sm:w-32 sm:h-32 rounded-xl object-cover border border-gray-200">
                                    </div>
                                @endif

                                <!-- KHỐI CHỨNG THỰC BLOCKCHAIN -->
                                <div class="bg-white border border-brand-200/90 rounded-2xl p-3.5 sm:p-4 text-xs font-mono space-y-2 mb-3 shadow-xs">
                                    @if ($event->data_hash)
                                        <div>
                                            <span class="text-gray-400 font-semibold block text-[11px] mb-1 flex items-center gap-1.5">
                                                <i class="fa-solid fa-hashtag text-brand-600"></i> Data Hash (SHA-256):
                                            </span>
                                            <p class="text-gray-800 break-all bg-gray-50 p-2 sm:p-2.5 rounded-xl border border-gray-100 text-[11px] leading-relaxed">
                                                {{ $event->data_hash }}
                                            </p>
                                        </div>
                                    @endif

                                    @if ($event->blockchain_tx_hash)
                                        <div class="pt-2 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                                            <span class="text-gray-400 font-semibold text-[11px] flex items-center gap-1.5">
                                                <i class="fa-solid fa-cube text-blue-600"></i> TxHash:
                                            </span>
                                            <a href="https://polygonscan.com/tx/{{ $event->blockchain_tx_hash }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1 text-[11px] break-all">
                                                <span>{{ $event->blockchain_tx_hash }}</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px] shrink-0"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 text-xs text-gray-400 pt-2 border-t border-brand-100">
                                    <i class="fa-solid fa-user-check text-brand-600"></i>
                                    <span>Phụ trách ghi nhận: <b>{{ $event->recorded_by ?? 'Kỹ sư canh tác' }}</b></span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="py-12 text-center text-gray-400 text-xs">
                        Chưa có dữ liệu nhật ký canh tác cho đợt trồng này.
                    </div>
                @endif

            </div>
        </div>
    </main>
</div>