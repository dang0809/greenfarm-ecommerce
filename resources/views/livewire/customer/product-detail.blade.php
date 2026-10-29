<main class="pt-28 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ previewImage: null }">
    
    <!-- Thông báo Alert -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-2xl text-xs sm:text-sm font-bold flex items-center justify-between">
            <span><i class="fa-solid fa-circle-check mr-2 text-green-600"></i> {{ session('success') }}</span>
            <button type="button" class="text-green-600" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl text-xs sm:text-sm font-bold flex items-center justify-between">
            <span><i class="fa-solid fa-circle-exclamation mr-2 text-red-600"></i> {{ session('error') }}</span>
            <button type="button" class="text-red-600" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif

    <!-- KHỐI THÔNG TIN SẢN PHẨM CHÍNH -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 mb-16">
        
        <!-- CỘT TRÁI: GALLERY ẢNH -->
        <div class="lg:col-span-6 flex flex-col gap-4">
            <div class="relative w-full aspect-square bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100">
                @if ($product->sale_price && $product->sale_price < $product->price)
                    <span class="absolute top-4 left-4 z-10 bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-sm">
                        -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}% Flash Sale
                    </span>
                @endif
                <img src="{{ $selectedImage }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
            </div>

            <!-- Thư viện ảnh thu nhỏ -->
            @php
                $productImages = $product->productImages;
            @endphp
            @if ($productImages && $productImages->count() > 1)
                <div class="grid grid-cols-4 gap-3 sm:gap-4">
                    @foreach ($productImages as $imgObj)
                        @php
                            $imgUrl = str_starts_with($imgObj->image, 'http') 
                                ? $imgObj->image 
                                : asset('storage/' . $imgObj->image);
                        @endphp
                        <button type="button" 
                                wire:click="selectImage('{{ $imgUrl }}')" 
                                class="thumb-btn border-2 {{ $selectedImage === $imgUrl ? 'border-brand-600' : 'border-transparent' }} rounded-2xl overflow-hidden aspect-square bg-white shadow-sm p-0.5 hover:border-brand-300 transition-colors">
                            <img src="{{ $imgUrl }}" class="w-full h-full object-cover rounded-xl">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- CỘT PHẢI: THÔNG TIN CHI TIẾT & MUA HÀNG -->
        <div class="lg:col-span-6 flex flex-col">

            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight mb-3">
                {{ $product->name }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs sm:text-sm text-gray-500 mb-6">
                <div class="flex items-center text-amber-400">
                    <i class="fa-solid fa-star"></i>
                    <span class="text-gray-800 font-bold ml-1.5">{{ number_format($product->reviews->avg('rating') ?? 5, 1) }}</span>
                    <button type="button" wire:click="switchTab('reviews')" class="text-gray-400 font-medium ml-1 hover:text-brand-600">({{ $product->reviews->count() }} nhận xét)</button>
                </div>
                <span>•</span>
                <span>Đã bán: <b class="text-gray-800">{{ $product->sold_count ?? 0 }}</b></span>
                <span>•</span>
                
                @if ($totalStock > 0)
                    <span class="text-brand-700 bg-brand-50 border border-brand-200 px-2.5 py-0.5 rounded-md font-bold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span> Còn hàng (Kho: {{ $totalStock }} {{ $product->unit ?? 'kg' }})
                    </span>
                @else
                    <span class="text-red-700 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-md font-bold">
                        Hết hàng
                    </span>
                @endif
            </div>

            <!-- Khung giá bán -->
            <div class="bg-white border border-gray-100 rounded-3xl p-5 sm:p-6 mb-6 flex items-end gap-3 shadow-sm">
                @if ($product->sale_price && $product->sale_price < $product->price)
                    <span class="text-3xl sm:text-4xl font-black text-brand-700 leading-none">{{ number_format($product->sale_price, 0, ',', '.') }}đ</span>
                    <span class="text-gray-400 text-base sm:text-lg line-through mb-0.5">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                @else
                    <span class="text-3xl sm:text-4xl font-black text-brand-700 leading-none">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                @endif
                <span class="text-gray-500 text-sm font-medium mb-0.5">/ {{ $product->unit ?? 'kg' }}</span>
            </div>

            <!-- LÔ HÀNG VÀ TRUY XUẤT NGUỒN GỐC (FEFO) -->
            @if ($currentBatch)
                <div class="bg-white border border-brand-200/80 rounded-2xl p-4 sm:p-5 mb-8 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <i class="fa-solid fa-qrcode text-brand-600 text-base"></i>
                            <span class="text-xs font-bold text-gray-900 uppercase tracking-wide">Lô xuất kho hiện tại</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-mono font-bold text-brand-700 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded">
                                {{ $currentBatch->batch_code }}
                            </span>
                            @if($currentBatch->manufactured_date)
                                <span class="text-xs text-gray-500 border-l border-gray-200 pl-2">Ngày SX: {{ \Carbon\Carbon::parse($currentBatch->manufactured_date)->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('customer.traceability.detail', ['batch' => $currentBatch->batch_code]) }}" class="shrink-0 inline-flex items-center justify-center gap-1.5 text-xs font-bold text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 px-4 py-2.5 rounded-xl transition-colors border border-brand-100 w-full sm:w-auto">
                        <span>Xem nhật ký canh tác</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            @endif

            <!-- FORM ĐẶT HÀNG -->
            <div class="mt-auto space-y-5">
                <div class="flex items-center gap-5">
                    <span class="text-sm font-bold text-gray-800">Số lượng:</span>
                    <div class="flex items-center border border-gray-200 rounded-2xl bg-white shadow-sm overflow-hidden h-11 w-32">
                        <button type="button" 
                                wire:click="decrementQuantity" 
                                @disabled($quantity <= 1 || $totalStock <= 0)
                                class="w-10 h-full text-gray-500 hover:bg-gray-50 hover:text-brand-600 transition-colors disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-gray-500 cursor-pointer">
                            <i class="fa-solid fa-minus text-xs"></i>
                        </button>
                        
                        <input type="number" 
                               wire:model.live="quantity" 
                               min="1" 
                               max="{{ $totalStock }}" 
                               readonly
                               class="w-full h-full text-center font-bold text-gray-900 bg-transparent outline-none text-sm select-none cursor-default [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        
                        <button type="button" 
                                wire:click="incrementQuantity" 
                                @disabled($quantity >= $totalStock || $totalStock <= 0)
                                class="w-10 h-full text-gray-500 hover:bg-gray-50 hover:text-brand-600 transition-colors disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-gray-500 cursor-pointer">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </button>
                    </div>
                    <span class="text-xs text-gray-400">Tối đa {{ $totalStock }} {{ $product->unit ?? 'kg' }}</span>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" 
                            wire:click="addToCart" 
                            {{ $totalStock <= 0 ? 'disabled' : '' }}
                            class="flex-1 bg-white border-2 border-brand-600 text-brand-700 py-3.5 rounded-2xl font-bold text-sm sm:text-base hover:bg-brand-50 transition-colors shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                    </button>
                    
                    <button type="button" 
                            wire:click="buyNow" 
                            {{ $totalStock <= 0 ? 'disabled' : '' }}
                            class="flex-1 bg-brand-600 border-2 border-brand-600 text-white py-3.5 rounded-2xl font-bold text-sm sm:text-base hover:bg-brand-700 transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        Mua ngay
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-6 pt-6 border-t border-gray-200/60 text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-brand-600 text-sm"></i>
                    <span>Giao hỏa tốc tính phí theo km</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-brand-600 text-sm"></i>
                    <span>Đổi trả 24h nếu dập nát</span>
                </div>
            </div>

        </div>
    </div>

    <!-- TABS: MÔ TẢ & ĐÁNH GIÁ -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-16" id="reviews">
        <div class="flex border-b border-gray-100">
            <button type="button" 
                    wire:click="switchTab('desc')" 
                    class="flex-1 py-4 sm:py-5 text-center font-bold text-xs sm:text-sm {{ $activeTab === 'desc' ? 'text-brand-600 border-b-2 border-brand-600 bg-gray-50/40' : 'text-gray-500 border-b-2 border-transparent hover:text-brand-600' }}">
                Mô Tả Sản Phẩm
            </button>
            <button type="button" 
                    wire:click="switchTab('reviews')" 
                    class="flex-1 py-4 sm:py-5 text-center font-bold text-xs sm:text-sm {{ $activeTab === 'reviews' ? 'text-brand-600 border-b-2 border-brand-600 bg-gray-50/40' : 'text-gray-500 border-b-2 border-transparent hover:text-brand-600' }}">
                Đánh Giá Khách Hàng ({{ $product->reviews->count() }})
            </button>
        </div>

        @if ($activeTab === 'desc')
            <div class="p-6 sm:p-10 text-gray-600 leading-relaxed text-sm sm:text-base space-y-4">
                {!! $product->description !!}
            </div>
        @endif

        @if ($activeTab === 'reviews')
            <div class="p-6 sm:p-10">
                <div class="flex flex-col sm:flex-row items-center gap-6 pb-8 border-b border-gray-100 mb-8">
                    <div class="text-center sm:text-left shrink-0">
                        <div class="text-5xl font-black text-brand-600">{{ number_format($product->reviews->avg('rating') ?? 5, 1) }}<span class="text-2xl text-gray-400">/5</span></div>
                        <div class="flex items-center text-amber-400 text-sm mt-1 justify-center sm:justify-start">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Dựa trên {{ $product->reviews->count() }} đánh giá thực tế</p>
                    </div>
                </div>

                @auth
                    @if ($this->hasPurchased)
                        <div class="bg-gray-50/70 border border-gray-200/80 rounded-2xl p-5 sm:p-6 mb-8">
                            <h4 class="text-sm sm:text-base font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <i class="fa-regular fa-pen-to-square text-brand-600"></i> Viết Đánh Giá Của Bạn
                            </h4>

                            <form wire:submit.prevent="submitReview" class="space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-xs font-bold text-gray-700">Mức độ hài lòng:</span>
                                    <div class="flex items-center gap-1.5 text-lg text-amber-400 cursor-pointer">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <button type="button" wire:click="$set('rating', {{ $i }})" class="transition-colors">
                                                <i class="fa-solid fa-star {{ $i <= $rating ? 'text-amber-400' : 'text-gray-300' }}"></i>
                                            </button>
                                        @endfor
                                    </div>
                                    <span class="text-xs font-bold text-amber-600">{{ $rating }} sao</span>
                                </div>

                                <div>
                                    <textarea wire:model="comment" rows="3" placeholder="Chia sẻ cảm nhận về độ tươi giòn, quy cách đóng gói và thời gian giao nhận..." class="w-full bg-white border border-gray-200 rounded-2xl p-3.5 text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all resize-none shadow-xs"></textarea>
                                    @error('comment') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-600 hover:text-brand-600 transition-colors">
                                        <i class="fa-solid fa-camera text-brand-600 text-sm"></i>
                                        <span>Thêm ảnh thực tế nhận hàng</span>
                                        <input type="file" wire:model="reviewPhoto" accept="image/*" class="hidden">
                                    </label>

                                    <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-6 py-2.5 rounded-xl text-xs sm:text-sm shadow-sm transition-all flex items-center justify-center gap-1.5 active:scale-95">
                                        <i class="fa-regular fa-paper-plane text-xs"></i>
                                        <span>Gửi đánh giá</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-5 mb-8 text-center">
                            <i class="fa-solid fa-bag-shopping text-amber-600 text-2xl mb-2"></i>
                            <p class="text-xs sm:text-sm font-bold text-amber-900">Chỉ khách hàng đã mua sản phẩm này mới có thể viết đánh giá.</p>
                        </div>
                    @endif
                @endauth

                <div class="space-y-6">
                    @forelse ($product->reviews as $review)
                        <div class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                            <div class="flex items-start gap-3.5 mb-2.5">
                                <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center shrink-0 text-sm">
                                    {{ mb_substr($review->user->name ?? 'K', 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h5 class="font-bold text-gray-900 text-sm">{{ $review->user->name ?? 'Khách hàng' }}</h5>
                                        <span class="text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-amber-400 text-xs mt-0.5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa-solid fa-star {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                                        @endfor
                                        <span class="text-[10px] text-brand-600 bg-brand-50 px-2 py-0.5 rounded font-bold ml-1">Đã mua hàng</span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-3">
                                {{ $review->content ?? $review->comment }}
                            </p>

                            @if ($review->images && $review->images->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($review->images as $revImg)
                                        @php
                                            $revImgUrl = str_starts_with($revImg->image, 'http') 
                                                ? $revImg->image 
                                                : asset('storage/' . $revImg->image);
                                        @endphp
                                        <img src="{{ $revImgUrl }}" 
                                             @click="previewImage = '{{ $revImgUrl }}'"
                                             title="Nhấn để xem ảnh phóng to"
                                             class="w-16 h-16 rounded-xl object-cover border border-gray-200 cursor-pointer hover:opacity-80 transition-opacity hover:scale-105 transform duration-200 shadow-xs">
                                    @endforeach
                                </div>
                            @elseif ($review->image)
                                @php
                                    $singleRevImgUrl = str_starts_with($review->image, 'http') ? $review->image : asset('storage/' . $review->image);
                                @endphp
                                <div class="flex gap-2">
                                    <img src="{{ $singleRevImgUrl }}" 
                                         @click="previewImage = '{{ $singleRevImgUrl }}'"
                                         title="Nhấn để xem ảnh phóng to"
                                         class="w-16 h-16 rounded-xl object-cover border border-gray-200 cursor-pointer hover:opacity-80 transition-opacity hover:scale-105 transform duration-200 shadow-xs">
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-6">Chưa có đánh giá nào cho sản phẩm này.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    <!-- SẢN PHẨM CÙNG DANH MỤC (NÔNG SẢN TƯƠNG TỰ) -->
    <div>
        <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-200/80">
            <h3 class="text-lg sm:text-2xl font-bold text-gray-900">Nông Sản Tương Tự</h3>
            <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="text-xs sm:text-sm font-semibold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                Xem thêm <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-6">
            @foreach ($relatedProducts as $related)
                @php
                    $relRawImg = $related->productImages->firstWhere('is_primary', true)?->image 
                        ?? $related->productImages->first()?->image;
                    
                    $relImgUrl = $relRawImg 
                        ? (str_starts_with($relRawImg, 'http') ? $relRawImg : asset('storage/' . $relRawImg))
                        : 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=600';

                    // Tính tồn kho thực tế của sản phẩm tương tự
                    $relStock = $related->total_stock !== null 
                        ? (int)$related->total_stock 
                        : ($related->batches ? $related->batches->where('status', 'active')->where('current_quantity', '>', 0)->where('expiry_date', '>=', now()->format('Y-m-d'))->sum('current_quantity') : 0);
                    $isRelOutOfStock = $relStock <= 0;
                @endphp

                <div class="bg-white rounded-3xl border border-gray-100 hover:border-brand-200 hover:shadow-lg transition-all duration-300 group flex flex-col relative overflow-hidden">
                    
                    <!-- Nhãn Tạm Hết Hàng hoặc Flash Sale -->
                    @if($isRelOutOfStock)
                        <span class="absolute top-3 left-3 z-20 bg-gray-900/80 backdrop-blur-xs text-white text-[10px] sm:text-[11px] font-bold px-2.5 py-1 rounded-lg shadow-sm">
                            Tạm Hết Hàng
                        </span>
                    @elseif($related->sale_price && $related->sale_price < $related->price)
                        <span class="absolute top-3 left-3 z-10 bg-red-500 text-white text-[10px] sm:text-[11px] font-bold px-2 sm:px-2.5 py-1 rounded-lg shadow-sm">
                            -{{ round((($related->price - $related->sale_price) / $related->price) * 100) }}% Flash Sale
                        </span>
                    @endif

                    <!-- Ảnh sản phẩm: vẫn cho click vào xem chi tiết -->
                    <a href="{{ route('customer.product.detail', ['slug' => $related->slug]) }}" class="relative aspect-square overflow-hidden block bg-gray-50">
                        <img src="{{ $relImgUrl }}" alt="{{ $related->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 {{ $isRelOutOfStock ? 'grayscale opacity-75' : '' }}">
                        
                        @if($isRelOutOfStock)
                            <div class="absolute inset-0 bg-gray-900/10 flex items-center justify-center">
                                <span class="bg-white/90 backdrop-blur-xs text-gray-800 text-xs font-extrabold px-3 py-1.5 rounded-xl shadow-xs border border-gray-200">
                                    Hết hàng
                                </span>
                            </div>
                        @endif
                    </a>
                    
                    <div class="p-3.5 sm:p-5 flex flex-col flex-grow">
                        <span class="text-[10px] sm:text-[11px] font-semibold text-brand-600 uppercase tracking-wide mb-1">{{ $related->category->name ?? 'Nông sản' }}</span>
                        <h4 class="text-sm sm:text-base font-bold text-gray-800 mb-1 line-clamp-2 group-hover:text-brand-600 transition">
                            <a href="{{ route('customer.product.detail', ['slug' => $related->slug]) }}">{{ $related->name }}</a>
                        </h4>
                        <div class="flex items-center gap-1 text-amber-400 text-xs mb-3">
                            <i class="fa-solid fa-star"></i><span class="text-gray-500 font-medium ml-1">{{ number_format($related->reviews->avg('rating') ?? 5, 1) }} | Đã bán {{ $related->sold_count ?? 0 }}</span>
                        </div>
                        <div class="mt-auto pt-3 border-t border-gray-50 flex items-end justify-between">
                            <div>
                                <span class="text-base sm:text-lg font-extrabold text-brand-700 leading-none block">{{ number_format($related->price, 0, ',', '.') }}đ</span>
                                <span class="text-[11px] text-gray-500 font-medium">/ {{ $related->unit ?? 'kg' }}</span>
                            </div>

                            <!-- Nút Thêm Giỏ Hàng: Vô hiệu hóa nếu tạm hết hàng -->
                            @auth
                                @if($isRelOutOfStock)
                                    <button type="button" 
                                            disabled 
                                            class="bg-gray-200 text-gray-400 w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center cursor-not-allowed" 
                                            title="Sản phẩm tạm hết hàng">
                                        <i class="fa-solid fa-ban text-xs sm:text-sm"></i>
                                    </button>
                                @else
                                    <button type="button" 
                                            wire:click="addToCart({{ $related->id }}, 1)" 
                                            class="bg-gray-900 hover:bg-brand-600 text-white w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition-colors shadow-md active:scale-95"
                                            title="Thêm vào giỏ">
                                        <i class="fa-solid fa-plus text-xs sm:text-sm"></i>
                                    </button>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- MODAL POPUP XEM ẢNH ĐÁNH GIÁ PHÓNG TO -->
    <div x-show="previewImage" 
         x-cloak 
         @click="previewImage = null"
         @keydown.escape.window="previewImage = null"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs cursor-pointer select-none"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="relative max-w-4xl max-h-[90vh] flex items-center justify-center">
            <img :src="previewImage" 
                 alt="Ảnh đánh giá" 
                 class="max-w-full max-h-[85vh] rounded-2xl object-contain shadow-2xl border border-white/10">
        </div>
    </div>

</main>