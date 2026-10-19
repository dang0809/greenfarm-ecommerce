<div>
    <main class="pt-28 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Tiêu đề trang & Thanh tìm kiếm / Sắp xếp / Lọc -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-gray-200/80 gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Nông Sản Sạch</h1>
                <p class="text-gray-500 text-xs sm:text-sm mt-1">
                    @if(!empty($search))
                        Kết quả tìm kiếm cho: <span class="font-bold text-brand-700">"{{ $search }}"</span>
                    @else
                        Danh sách nông sản sạch tươi ngon thu hoạch trực tiếp tại nông trại
                    @endif
                </p>
            </div>

            <!-- Toolbar: Tìm kiếm + Sắp xếp + Nút lọc Mobile -->
            <div class="flex flex-wrap items-center justify-between sm:justify-end gap-3 w-full md:w-auto">
                
                <!-- Ô TÌM KIẾM -->
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Tìm theo tên sản phẩm..." 
                           class="w-full bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2.5 text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 shadow-sm transition-all">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                    
                    @if(!empty($search))
                        <button wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1" title="Xóa từ khóa">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    @endif
                </div>

                <!-- Nút bật Drawer lọc trên Mobile -->
                <button id="open-filter-btn" class="lg:hidden flex items-center gap-2 bg-white border border-gray-200 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 shadow-sm hover:border-brand-500 transition-all">
                    <i class="fa-solid fa-filter text-brand-600"></i>
                    <span>Bộ lọc</span>
                </button>

                <!-- Dropdown Sắp xếp -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 hidden sm:inline whitespace-nowrap">Sắp xếp:</span>
                    <select wire:model.live="sortBy" class="bg-white border border-gray-200 text-gray-700 text-xs sm:text-sm rounded-xl px-3 py-2.5 outline-none focus:border-brand-500 shadow-sm cursor-pointer">
                        <option value="newest">Mới thu hoạch nhất</option>
                        <option value="price_asc">Giá: Thấp đến Cao</option>
                        <option value="price_desc">Giá: Cao đến Thấp</option>
                        <option value="popular">Bán chạy nhất</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bố cục 2 cột: Sidebar Bộ lọc & Grid Sản Phẩm -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
            
            <!-- CỘT TRÁI: SIDEBAR BỘ LỌC (DESKTOP) -->
            <aside class="hidden lg:block lg:col-span-1 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm sticky top-28 space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h2 class="font-extrabold text-gray-900 text-base flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-brand-600"></i> Bộ Lọc Tìm Kiếm
                    </h2>
                    <button wire:click="resetFilters" class="text-xs font-semibold text-brand-600 hover:underline">Xóa lọc</button>
                </div>

                <!-- Lọc theo Danh mục -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Danh Mục</h3>
                    <div class="space-y-2.5">
                        <label class="flex items-center justify-between text-sm text-gray-700 hover:text-brand-600 cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <input type="radio" wire:model.live="selectedCategory" value="" name="desktop_cat" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
                                <span>Tất cả danh mục</span>
                            </span>
                        </label>

                        @foreach($categories as $category)
                            <label class="flex items-center justify-between text-sm text-gray-700 hover:text-brand-600 cursor-pointer">
                                <span class="flex items-center gap-2.5">
                                    <input type="radio" wire:model.live="selectedCategory" value="{{ $category->id }}" name="desktop_cat" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
                                    <span>{{ $category->name }}</span>
                                </span>
                                <span class="text-xs text-gray-400">{{ $category->products_count }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Lọc theo Khoảng giá -->
                <div class="pt-4 border-t border-gray-100">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Khoảng Giá</h3>
                    <div class="space-y-2 text-sm text-gray-700">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" wire:model.live="priceRange" value="" name="desktop_price" class="text-brand-600 focus:ring-brand-500">
                            <span>Tất cả mức giá</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" wire:model.live="priceRange" value="under_50" name="desktop_price" class="text-brand-600 focus:ring-brand-500">
                            <span>Dưới 50.000đ</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" wire:model.live="priceRange" value="50_100" name="desktop_price" class="text-brand-600 focus:ring-brand-500">
                            <span>50.000đ - 100.000đ</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" wire:model.live="priceRange" value="over_100" name="desktop_price" class="text-brand-600 focus:ring-brand-500">
                            <span>Trên 100.000đ</span>
                        </label>
                    </div>
                </div>
            </aside>

            <!-- CỘT PHẢI: LƯỚI SẢN PHẨM -->
            <div class="lg:col-span-3">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3.5 sm:gap-6">
                    @forelse($products as $product)
                    {{-- Gọi Blade Component Card Sản Phẩm --}}
                    <x-customer.product-card :product="$product" />
                    @empty
                        <div class="col-span-2 md:col-span-3 py-16 text-center bg-white rounded-3xl border border-dashed border-gray-200">
                            <i class="fa-solid fa-basket-shopping text-4xl text-gray-300 mb-3 block"></i>
                            <p class="text-gray-600 font-bold text-base">Không tìm thấy sản phẩm phù hợp</p>
                            <p class="text-gray-400 text-xs mt-1">Vui lòng thử tìm từ khóa khác hoặc bấm xóa bộ lọc.</p>
                            <button wire:click="resetFilters" class="mt-4 px-4 py-2 bg-brand-600 text-white text-xs font-bold rounded-xl hover:bg-brand-700 transition">
                                Xóa bộ lọc
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Phân trang Livewire -->
                <div class="mt-12">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </main>

    <!-- DRAWER BỘ LỌC CHO MOBILE -->
    <div id="filter-backdrop" class="fixed inset-0 bg-black/50 z-50 hidden transition-opacity"></div>
    <div id="filter-drawer" class="fixed inset-y-0 right-0 max-w-xs w-full bg-white z-50 shadow-2xl p-6 transform translate-x-full transition-transform duration-300 overflow-y-auto flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                    <i class="fa-solid fa-filter text-brand-600"></i> Bộ Lọc Sản Phẩm
                </h3>
                <button id="close-filter-btn" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-6">
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2.5">Danh Mục</h4>
                    <div class="space-y-2 text-sm text-gray-700">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" wire:model.live="selectedCategory" value="" name="mobile_cat" class="rounded text-brand-600"> Tất cả
                        </label>
                        @foreach($categories as $category)
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="radio" wire:model.live="selectedCategory" value="{{ $category->id }}" name="mobile_cat" class="rounded text-brand-600"> {{ $category->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2.5">Khoảng Giá</h4>
                    <div class="space-y-2 text-sm text-gray-700">
                        <label class="flex items-center gap-2.5 cursor-pointer"><input type="radio" wire:model.live="priceRange" value="" name="m_price" class="text-brand-600"> Tất cả</label>
                        <label class="flex items-center gap-2.5 cursor-pointer"><input type="radio" wire:model.live="priceRange" value="under_50" name="m_price" class="text-brand-600"> Dưới 50.000đ</label>
                        <label class="flex items-center gap-2.5 cursor-pointer"><input type="radio" wire:model.live="priceRange" value="50_100" name="m_price" class="text-brand-600"> 50.000đ - 100.000đ</label>
                        <label class="flex items-center gap-2.5 cursor-pointer"><input type="radio" wire:model.live="priceRange" value="over_100" name="m_price" class="text-brand-600"> Trên 100.000đ</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 mt-6 flex gap-3">
            <button wire:click="resetFilters" id="reset-filter-mobile" class="w-1/3 py-3 border border-gray-200 rounded-xl font-bold text-xs text-gray-600 hover:bg-gray-50">
                Đặt lại
            </button>
            <button id="apply-filter-mobile" class="w-2/3 py-3 bg-brand-600 text-white rounded-xl font-bold text-xs shadow-md hover:bg-brand-700">
                Xem kết quả
            </button>
        </div>
    </div>

    <!-- SCRIPT DRAWER BỘ LỌC MOBILE -->
    <script>
        const openFilterBtn = document.getElementById('open-filter-btn');
        const closeFilterBtn = document.getElementById('close-filter-btn');
        const filterBackdrop = document.getElementById('filter-backdrop');
        const filterDrawer = document.getElementById('filter-drawer');
        const applyFilterMobile = document.getElementById('apply-filter-mobile');

        function toggleFilter(open) {
            if (open) {
                filterBackdrop?.classList.remove('hidden');
                filterDrawer?.classList.remove('translate-x-full');
            } else {
                filterBackdrop?.classList.add('hidden');
                filterDrawer?.classList.add('translate-x-full');
            }
        }

        openFilterBtn?.addEventListener('click', () => toggleFilter(true));
        closeFilterBtn?.addEventListener('click', () => toggleFilter(false));
        filterBackdrop?.addEventListener('click', () => toggleFilter(false));
        applyFilterMobile?.addEventListener('click', () => toggleFilter(false));
    </script>
</div>