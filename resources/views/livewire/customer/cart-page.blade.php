<div>
    <main class="pt-28 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Tiêu đề giỏ hàng -->
        <div class="pb-6 border-b border-gray-200/80 mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Giỏ Hàng</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-1">
                Đang chọn <span class="font-bold text-brand-700">{{ $selectedCount }}</span> / {{ $items->count() }} sản phẩm tươi thu hoạch
            </p>
        </div>

        @if($items->count() > 0)
            <!-- BỐ CỤC 2 CỘT -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- CỘT TRÁI (8 CỘT): DANH SÁCH SẢN PHẨM -->
                <div class="lg:col-span-8 space-y-4">
                    
                    <!-- Thanh chọn tất cả & Xóa giỏ -->
                    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer text-xs sm:text-sm font-bold text-gray-800">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500 cursor-pointer">
                            <span>Chọn tất cả ({{ $items->count() }} sản phẩm)</span>
                        </label>
                        
                        @if(count($selectedItems) > 0)
                            <button type="button" 
                                    wire:click="removeSelected" 
                                    wire:confirm="Bạn có chắc chắn muốn xóa các sản phẩm đã chọn khỏi giỏ hàng?"
                                    class="text-xs text-gray-400 hover:text-red-600 transition-colors flex items-center gap-1.5 font-medium cursor-pointer">
                                <i class="fa-regular fa-trash-can"></i> Xóa mục đã chọn ({{ count($selectedItems) }})
                            </button>
                        @endif
                    </div>

                    <!-- Danh sách Cart Item -->
                    <div class="space-y-4">
                        @foreach($items as $item)
                            @php
                                $product = $item->product;
                                $rawImage = $product->productImages?->firstWhere('is_primary', true)?->image 
                                    ?? $product->productImages?->first()?->image;

                                $primaryImage = $rawImage 
                                    ? (str_starts_with($rawImage, 'http') ? $rawImage : asset('storage/' . $rawImage))
                                    : 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=300';

                                $unitPrice = $product->sale_price ?? $product->price;
                                $itemSubtotal = $unitPrice * $item->quantity;
                            @endphp

                            <div class="bg-white p-4 sm:p-5 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3.5 sm:gap-4 w-full sm:w-auto">
                                    <!-- Checkbox chọn item -->
                                    <input type="checkbox" 
                                           wire:model.live="selectedItems" 
                                           value="{{ $item->id }}" 
                                           class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500 cursor-pointer shrink-0">
                                    
                                    <!-- Ảnh sản phẩm -->
                                    <a href="{{ route('customer.product.detail', ['slug' => $product->slug]) }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-gray-50 border border-gray-100 shrink-0">
                                        <img src="{{ $primaryImage }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    </a>

                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-semibold text-brand-600 uppercase tracking-wide">
                                            {{ $product->category?->name ?? 'Nông Sản' }}
                                        </span>
                                        <h3 class="text-sm sm:text-base font-bold text-gray-900 truncate">
                                            <a href="{{ route('customer.product.detail', ['slug' => $product->slug]) }}" class="hover:text-brand-600 transition-colors">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        <div class="flex items-baseline gap-2 mt-0.5">
                                            <span class="text-sm sm:text-base font-extrabold text-brand-700">
                                                {{ number_format($unitPrice, 0, ',', '.') }}đ
                                            </span>
                                            @if($product->sale_price)
                                                <span class="text-xs text-gray-400 line-through">
                                                    {{ number_format($product->price, 0, ',', '.') }}đ
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-gray-500 font-medium">/ {{ $product->unit }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bộ tăng giảm số lượng & Thành tiền -->
                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-50">
                                    <div class="flex items-center border border-gray-200 rounded-xl bg-gray-50/50 overflow-hidden h-9 w-28">
                                        <button type="button" 
                                                wire:click="decrementQuantity({{ $item->id }})" 
                                                @if($item->quantity <= 1) disabled @endif
                                                class="w-8 h-full text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors flex items-center justify-center cursor-pointer shrink-0">
                                            <i class="fa-solid fa-minus text-[10px]"></i>
                                        </button>
                                        
                                        <input type="number" 
                                               wire:key="cart-qty-{{ $item->id }}-{{ $item->quantity }}"
                                               value="{{ $item->quantity }}" 
                                               wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                                               min="1"
                                               onchange="if(this.value < 1) this.value = 1;" 
                                               class="w-full h-full text-center font-bold text-gray-900 bg-transparent outline-none text-xs">
                                        
                                        <button type="button" 
                                                wire:click="incrementQuantity({{ $item->id }})" 
                                                class="w-8 h-full text-gray-500 hover:bg-gray-100 transition-colors flex items-center justify-center cursor-pointer">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                        </button>
                                    </div>

                                    <div class="text-right min-w-[90px]">
                                        <span class="block text-xs text-gray-400 sm:hidden">Tổng:</span>
                                        <span class="text-sm sm:text-base font-extrabold text-gray-900">
                                            {{ number_format($itemSubtotal, 0, ',', '.') }}đ
                                        </span>
                                    </div>

                                    <button type="button" 
                                            wire:click="removeItem({{ $item->id }})" 
                                            wire:confirm="Xóa {{ $product->name }} khỏi giỏ hàng?"
                                            class="text-gray-400 hover:text-red-600 p-1 transition-colors cursor-pointer" 
                                            title="Xóa món này">
                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Cam kết bảo đảm nông sản -->
                    <div class="bg-brand-50/50 border border-brand-100 rounded-2xl p-4 flex items-center gap-3.5 text-xs text-brand-900">
                        <i class="fa-solid fa-shield-halved text-brand-600 text-lg shrink-0"></i>
                        <p>
                            <b>Cam kết Đất Lành:</b> Nông sản thu hoạch sớm theo đơn. Nếu phát hiện dập nát khi nhận, quý khách có thể gửi hình ảnh để được hoàn tiền hoặc giao bù trong 24H.
                        </p>
                    </div>
                </div>

                <!-- CỘT PHẢI (4 CỘT): TÓM TẮT ĐƠN HÀNG -->
                <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-28">
                    
                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                        <h3 class="font-bold text-gray-900 text-base pb-3 border-b border-gray-100">
                            Tóm Tắt Đơn Hàng
                        </h3>

                        <!-- Tính năng Dùng điểm thưởng -->
                        <div class="pb-3 border-b border-gray-100">
                            <label class="flex items-start justify-between gap-3 cursor-pointer">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           wire:model.live="usePoints" 
                                           @disabled(!$canUsePoints) 
                                           class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500 cursor-pointer disabled:opacity-50">
                                    <div>
                                        <span class="block text-xs font-bold text-gray-800">Dùng {{ $pointsRequired }} điểm thưởng</span>
                                        <span class="block text-[11px] text-gray-400">Bạn có {{ $userPoints }} điểm tích lũy</span>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-brand-700">-{{ number_format($pointsDiscount, 0, ',', '.') }}đ</span>
                            </label>
                            @if(!$canUsePoints)
                                <p class="text-[10px] text-amber-600 mt-1 italic">Cần tối thiểu {{ $pointsRequired }} điểm để sử dụng.</p>
                            @endif
                        </div>

                        <div class="space-y-3 text-xs sm:text-sm text-gray-600">
                            <div class="flex justify-between">
                                <span>Tạm tính (<b>{{ $selectedCount }}</b> sản phẩm):</span>
                                <span class="font-bold text-gray-900">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                            </div>
                            
                            @if($discount > 0)
                                <div class="flex justify-between text-brand-700">
                                    <span>Điểm thưởng thành viên:</span>
                                    <span class="font-bold">-{{ number_format($discount, 0, ',', '.') }}đ</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-gray-500">
                                <span>Phí vận chuyển:</span>
                                <span class="text-xs italic">Tính ở bước thanh toán</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-baseline justify-between">
                            <div>
                                <span class="block text-xs text-gray-400">Tổng thanh toán:</span>
                                <span class="text-[11px] text-gray-400">(Chưa tính phí ship)</span>
                            </div>
                            <span class="text-2xl font-black text-brand-700">
                                {{ number_format($total, 0, ',', '.') }}đ
                            </span>
                        </div>

                        <!-- Nút Tiến Hành Đặt Hàng -->
                        @if($selectedCount > 0)
                            <button type="button" 
                                    wire:click="proceedToCheckout"
                                    class="w-full bg-brand-600 hover:bg-brand-700 text-white py-3.5 rounded-2xl font-bold text-sm sm:text-base flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-98 cursor-pointer">
                                <span>Tiến hành đặt hàng</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        @else
                            <button type="button" disabled class="w-full bg-gray-200 text-gray-400 py-3.5 rounded-2xl font-bold text-sm sm:text-base flex items-center justify-center gap-2 cursor-not-allowed">
                                <span>Vui lòng chọn sản phẩm</span>
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        @else
            <!-- MÀN HÌNH GIỎ HÀNG TRỐNG -->
            <div class="py-16 text-center bg-white rounded-3xl border border-dashed border-gray-200 max-w-xl mx-auto my-8 p-8 space-y-4">
                <div class="w-20 h-20 bg-brand-50 text-brand-600 rounded-full flex items-center justify-center mx-auto text-3xl">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Giỏ hàng của bạn đang trống</h2>
                    <p class="text-xs text-gray-500 mt-1">Hãy khám phá các loại nông sản tươi ngon thu hoạch trực tiếp tại nông trại Đất Lành nhé!</p>
                </div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold px-6 py-3 rounded-2xl shadow-md transition">
                    <i class="fa-solid fa-leaf"></i>
                    <span>Khám phá nông sản ngay</span>
                </a>
            </div>
        @endif

    </main>

    <!-- STICKY BAR THANH TOÁN DÀNH CHO MOBILE (< lg) -->
    @if($items->count() > 0)
        <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-gray-200 p-4 shadow-xl">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                <div>
                    <span class="block text-[10px] text-gray-400">Tổng thanh toán ({{ $selectedCount }} món):</span>
                    <span class="text-lg font-black text-brand-700 leading-none">
                        {{ number_format($total, 0, ',', '.') }}đ
                    </span>
                </div>
                
                @if($selectedCount > 0)
                    <a href="#" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 shadow-md">
                        <span>Đặt hàng</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                @else
                    <button type="button" disabled class="bg-gray-200 text-gray-400 px-6 py-3 rounded-xl font-bold text-xs cursor-not-allowed">
                        <span>Chưa chọn món</span>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>