<div>
    <!-- Header Topbar -->
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()"
                class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition shrink-0">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <h1 class="text-base sm:text-2xl font-black text-slate-900 tracking-tight truncate leading-tight">
                    Quản Lý Danh Sách Sản Phẩm Nông Sản
                </h1>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span
                class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Tổng số: {{ $products->total() }} sản phẩm
            </span>
        </div>
    </header>

    <!-- Workspace Viewport -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12">

        <!-- Thanh Công Cụ & Bộ Lọc -->
        <div
            class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 flex-1">
                <div class="relative w-full sm:w-72 md:w-80">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm tên sản phẩm, mã SKU..."
                        class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-slate-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                </div>

                <select wire:model.live="selectedCategory"
                    class="w-full sm:w-auto bg-[#FAFAF8] border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-slate-700 outline-none focus:border-brand-600 transition cursor-pointer">
                    <option value="">-- Tất cả danh mục --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="selectedStatus"
                    class="w-full sm:w-auto bg-[#FAFAF8] border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-slate-700 outline-none focus:border-brand-600 transition cursor-pointer">
                    <option value="">-- Trạng thái bán trên web --</option>
                    <option value="active">Đang bán (active)</option>
                    <option value="hidden">Tạm ẩn (hidden)</option>
                </select>

                <button wire:click="$set('search', ''); $set('selectedCategory', ''); $set('selectedStatus', '');"
                    class="self-end sm:self-auto p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 transition shadow-xs cursor-pointer"
                    title="Đặt lại bộ lọc">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>

            <button onclick="Livewire.dispatch('open-create-product-modal')"
                class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95 shrink-0 whitespace-nowrap cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>+ Thêm Sản Phẩm Mới</span>
            </button>
        </div>

        <!-- Bảng Dữ Liệu Sản Phẩm -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr
                            class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-slate-100">
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Sản Phẩm & SKU</th>
                            <th class="py-3.5 sm:py-4 px-4">Danh Mục</th>
                            <th class="py-3.5 sm:py-4 px-4 text-right">Giá Bán Niêm Yết</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Đơn Vị</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Tồn Kho (Lô)</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Đã Bán</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Đánh Giá</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Hiển Thị Web</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($products as $product)
                        @php
                        $totalStock = $product->productBatches->where('status', 'active')->sum('current_quantity');
                        @endphp
                        <tr class="hover:bg-slate-50 transition bg-white">
                            <td class="py-3.5 px-4 pl-5 flex items-center gap-3">
                                <img src="{{ $product->image ?? 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300' }}"
                                    class="w-10 h-10 rounded-xl object-cover border border-slate-200 shrink-0">
                                <div class="min-w-0">
                                    <b
                                        class="text-slate-900 text-xs sm:text-sm block truncate hover:text-brand-700 cursor-pointer">{{ $product->name }}</b>
                                    <span
                                        class="font-mono text-[11px] text-brand-800 font-bold bg-brand-50 px-1.5 py-0.2 rounded border border-brand-200">{{ $product->sku }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">{{ $product->category->name ?? 'Chưa phân nhóm' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                {{ number_format($product->price, 0, ',', '.') }}đ</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold">{{ $product->unit }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <b
                                    class="font-mono text-sm {{ $totalStock > 0 ? 'text-brand-800' : 'text-slate-400' }}">{{ $totalStock }}
                                    kg</b>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">{{ $product->sold_count ?? 0 }} kg</td>
                            <td class="py-3.5 px-4 text-center font-mono text-amber-500 font-bold">
                                {{ $product->avg_rating ?? '5.0' }} ★</td>
                            <td class="py-3.5 px-4 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:click="toggleStatus({{ $product->id }})"
                                        {{ $product->status === 'active' ? 'checked' : '' }} class="sr-only peer">
                                    <div
                                        class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600">
                                    </div>
                                </label>
                            </td>
                            <td class="py-3.5 px-4 pr-6 text-right space-x-1.5">
                                <a href="{{ route('admin.product-batches', $product->id) }}"
                                    class="inline-flex items-center p-2 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 transition shadow-2xs font-bold text-xs"
                                    title="Quản lý Lô thu hoạch & FEFO">
                                    <i class="fa-solid fa-boxes-stacked mr-1"></i> Quản lý Lô
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400 italic">Không tìm thấy sản phẩm nông
                                sản nào phù hợp.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            @if($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </div>
</div>