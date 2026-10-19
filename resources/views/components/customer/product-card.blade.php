@props(['product'])

@php
    $rawImage = $product->productImages?->firstWhere('is_primary', true)?->image 
        ?? $product->productImages?->first()?->image;

    $primaryImage = $rawImage 
        ? (str_starts_with($rawImage, 'http') ? $rawImage : asset('storage/' . $rawImage))
        : 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=600';

    $stock = $product->total_stock !== null ? (int)$product->total_stock : 0;
    $isOutOfStock = $stock <= 0;
@endphp

<div class="bg-white rounded-3xl border border-gray-100 hover:border-brand-200 hover:shadow-lg transition-all duration-300 group flex flex-col relative overflow-hidden">
    
    <!-- Nhãn Tạm Hết Hàng / Flash Sale -->
    @if($isOutOfStock)
        <span class="absolute top-3 left-3 z-20 bg-gray-900/80 backdrop-blur-xs text-white text-[10px] sm:text-[11px] font-bold px-2.5 py-1 rounded-lg shadow-sm">
            Tạm Hết Hàng
        </span>
    @elseif($product->sale_price && $product->sale_price < $product->price)
        <span class="absolute top-3 left-3 z-10 bg-red-500 text-white text-[10px] sm:text-[11px] font-bold px-2 sm:px-2.5 py-1 rounded-lg shadow-sm">
            -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}% Flash Sale
        </span>
    @endif

    <!-- Ảnh sản phẩm -->
    <a href="{{ route('customer.product.detail', ['slug' => $product->slug]) }}" class="relative aspect-square overflow-hidden block bg-gray-50">
        <img src="{{ $primaryImage }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 {{ $isOutOfStock ? 'grayscale opacity-75' : '' }}">
        
        @if($isOutOfStock)
            <div class="absolute inset-0 bg-gray-900/10 flex items-center justify-center">
                <span class="bg-white/90 backdrop-blur-xs text-gray-800 text-xs font-extrabold px-3 py-1.5 rounded-xl shadow-xs border border-gray-200">
                    Hết hàng
                </span>
            </div>
        @endif
    </a>
    
    <div class="p-3.5 sm:p-5 flex flex-col flex-grow">
        <div class="mb-1">
            <span class="text-[10px] sm:text-[11px] font-semibold text-brand-600 uppercase tracking-wide">
                {{ $product->category?->name ?? 'Nông Sản' }}
            </span>
        </div>

        <h3 class="text-sm sm:text-base font-bold text-gray-800 mb-1 line-clamp-2 group-hover:text-brand-600 transition">
            <a href="{{ route('customer.product.detail', ['slug' => $product->slug]) }}">{{ $product->name }}</a>
        </h3>

        <div class="flex items-center gap-1 text-amber-400 text-xs mb-3">
            <i class="fa-solid fa-star"></i>
            <span class="text-gray-500 font-medium ml-1">
                {{ number_format($product->avg_rating ?? 5.0, 1) }} | Đã bán {{ $product->sold_count ?? 0 }}
            </span>
        </div>
        
        <div class="mt-auto pt-3 border-t border-gray-50 flex items-end justify-between">
            <div>
                @if($product->sale_price)
                    <span class="text-gray-400 text-[11px] sm:text-xs line-through block mb-0.5">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                    <span class="text-base sm:text-lg font-extrabold text-brand-700 leading-none">{{ number_format($product->sale_price, 0, ',', '.') }}đ</span>
                @else
                    <span class="text-base sm:text-lg font-extrabold text-brand-700 leading-none block">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                @endif
                <span class="text-[11px] text-gray-500 font-medium">/{{ $product->unit }}</span>
            </div>

            @auth
                @if($isOutOfStock)
                    <button type="button" disabled class="bg-gray-200 text-gray-400 w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center cursor-not-allowed">
                        <i class="fa-solid fa-ban text-xs sm:text-sm"></i>
                    </button>
                @else
                    <button type="button" wire:click="addToCart({{ $product->id }}, 1)" class="bg-gray-900 hover:bg-brand-600 text-white w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition-colors shadow-md active:scale-95">
                        <i class="fa-solid fa-plus text-xs sm:text-sm"></i>
                    </button>
                @endif
            @endauth
        </div>
    </div>
</div>