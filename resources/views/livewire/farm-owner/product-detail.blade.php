<div class="flex flex-1 min-h-0 min-w-0 flex-col overflow-hidden">
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center gap-3 px-4 sm:px-8 shrink-0">
        <button type="button" onclick="toggleSidebar()" aria-label="Mở menu"
            class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <a href="{{ route('farm-owner.products') }}" wire:navigate class="text-brand-700 text-sm font-bold shrink-0">
            <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i>
            Quay lại
        </a>

        <h1 class="text-lg sm:text-2xl font-black text-slate-900 truncate">
            {{ $product->name }}
        </h1>
    </header>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-5 custom-scrollbar">
        <section class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <p>SKU: <b>{{ $product->sku }}</b></p>
                <p>Danh mục: <b>{{ $product->category?->name ?? '—' }}</b></p>
                <p>Giá gốc:
                    <b>{{ number_format((float) $product->price, 0, ',', '.') }}đ / {{ $product->unit }}</b>
                </p>
                <p>Giá ưu đãi:
                    <b>{{ $product->sale_price !== null ? number_format((float) $product->sale_price, 0, ',', '.') . 'đ' : 'Không có' }}</b>
                </p>
                <p>Tồn khả dụng: <b>{{ $product->total_stock }} {{ $product->unit }}</b></p>
                <p>Trạng thái: <b>{{ $product->status === 'active' ? 'Đang bán' : 'Đang ẩn' }}</b></p>
            </div>

            <div class="flex flex-wrap gap-3">
                @foreach ($product->productImages as $image)
                    @if ($image->url)
                        <a href="{{ $image->url }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ $image->url }}" alt="{{ $product->name }}" loading="lazy"
                                class="h-28 w-28 rounded-2xl object-cover">
                        </a>
                    @endif
                @endforeach
            </div>

            <p class="whitespace-pre-line text-sm text-slate-600">{{ $product->description ?: 'Chưa có mô tả.' }}</p>
        </section>

        <section id="product-reviews" class="space-y-4">
            <h2 class="font-extrabold text-lg">
                Tất cả đánh giá ({{ $reviews->total() }})
            </h2>

            @forelse ($reviews as $review)
                <article wire:key="owner-review-{{ $review->id }}"
                    class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft">
                    <div class="flex items-center justify-between gap-3">
                        <strong>{{ $review->user?->name ?? 'Khách hàng' }}</strong>
                        <time class="text-xs text-slate-400">
                            {{ $review->created_at?->format('d/m/Y H:i') }}
                        </time>
                    </div>

                    <p class="mt-2 font-bold text-amber-600">{{ $review->rating }}/5 sao</p>
                    <p class="mt-2 whitespace-pre-line text-sm text-slate-700">
                        {{ $review->content ?: 'Không có nội dung.' }}</p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($review->images as $image)
                            @if ($image->url)
                                <a href="{{ $image->url }}" target="_blank" rel="noopener noreferrer">
                                    <img src="{{ $image->url }}" alt="Ảnh đánh giá" loading="lazy"
                                        class="h-20 w-20 rounded-xl object-cover">
                                </a>
                            @endif
                        @endforeach
                    </div>
                </article>
            @empty
                <p class="text-sm text-slate-500">Sản phẩm chưa có đánh giá.</p>
            @endforelse

            {{ $reviews->links(data: ['scrollTo' => '#product-reviews']) }}
        </section>
    </div>
</div>
