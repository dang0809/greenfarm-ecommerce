<x-farm-owner.page title="Danh sách sản phẩm">
    <div class="flex flex-col sm:flex-row gap-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Tìm tên hoặc SKU..."
            class="flex-1 bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-brand-600">

        <select wire:model.live="status" class="bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm">
            <option value="">Tất cả trạng thái</option>
            <option value="active">Đang bán</option>
            <option value="hidden">Đang ẩn</option>
        </select>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="p-4">Sản phẩm</th>
                        <th class="p-4">Danh mục</th>
                        <th class="p-4">Giá gốc</th>
                        <th class="p-4">Tồn khả dụng</th>
                        <th class="p-4">Đánh giá</th>
                        <th class="p-4">Trạng thái</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr wire:key="owner-product-{{ $product->id }}" class="hover:bg-slate-50">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    @if ($product->cover_image_url)
                                        <img src="{{ $product->cover_image_url }}" alt="{{ $product->name }}"
                                            loading="lazy" class="w-12 h-12 rounded-xl object-cover">
                                    @endif
                                    <div>
                                        <b>{{ $product->name }}</b>
                                        <p class="text-xs text-slate-500">{{ $product->sku }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">{{ $product->category?->name ?? '—' }}</td>
                            <td class="p-4">
                                {{ number_format((float) $product->price, 0, ',', '.') }}đ
                                / {{ $product->unit }}
                            </td>
                            <td class="p-4">{{ $product->total_stock }} {{ $product->unit }}</td>
                            <td class="p-4">
                                {{ $product->reviews_avg_rating !== null
                                    ? number_format((float) $product->reviews_avg_rating, 1) . '/5'
                                    : 'Chưa có' }}
                                <p class="text-xs text-slate-500">{{ $product->reviews_count }} đánh giá</p>
                            </td>
                            <td class="p-4">
                                {{ $product->status === 'active' ? 'Đang bán' : 'Đang ẩn' }}
                            </td>
                            <td class="p-4">
                                <a href="{{ route('farm-owner.products.show', $product) }}" wire:navigate
                                    class="inline-flex bg-brand-50 text-brand-700 px-3 py-2 rounded-xl font-bold text-xs">
                                    Xem chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">
                                Không tìm thấy sản phẩm.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $products->links() }}</div>
    </div>
</x-farm-owner.page>
