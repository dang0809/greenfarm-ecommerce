<x-farm-owner.page :title="$product->name" :back="route('farm-owner.products')">
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
</x-farm-owner.page>
