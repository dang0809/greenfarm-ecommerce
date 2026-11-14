<div x-data="{ confirmingProduct: false }" x-on:product-confirm-state="confirmingProduct = $event.detail.busy"
    class="flex flex-1 min-h-0 min-w-0 flex-col overflow-hidden">
    {{-- Hiển thị tiêu đề quản lý sản phẩm và nút mở menu di động --}}
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()"
                class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition shrink-0"
                aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>

            <div class="min-w-0">
                <h1 class="text-base sm:text-2xl font-black text-slate-900 tracking-tight truncate leading-tight">
                    Quản Lý Danh Sách Sản Phẩm Nông Sản
                </h1>
            </div>
        </div>


    </header>

    <div
        class="flex-1 min-h-0 min-w-0 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12">

        {{-- Tìm kiếm sản phẩm, lọc danh mục, trạng thái và đặt lại bộ lọc --}}
        <div
            class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">

            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 flex-1">
                <div class="relative w-full sm:w-72 md:w-80">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>

                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Tìm tên sản phẩm, mã SKU..."
                        class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-slate-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                </div>

                <select wire:model.live="selectedCategory"
                    class="w-full sm:w-auto bg-[#FAFAF8] border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-slate-700 outline-none focus:border-brand-600 transition cursor-pointer">
                    <option value="">-- Tất cả danh mục --</option>

                    @if (isset($categories))
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    @endif
                </select>

                <select wire:model.live="selectedStatus"
                    class="w-full sm:w-auto bg-[#FAFAF8] border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-semibold text-slate-700 outline-none focus:border-brand-600 transition cursor-pointer">
                    <option value="">-- Trạng thái bán trên web --</option>
                    <option value="active">Đang bán</option>
                    <option value="hidden">Tạm ẩn</option>
                </select>

                <button type="button" wire:click="resetFilters"
                    class="self-end sm:self-auto p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 transition shadow-xs cursor-pointer"
                    title="Đặt lại bộ lọc">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>

            {{-- Mở biểu mẫu tạo sản phẩm sau khi đặt lại dữ liệu cũ --}}
            <button type="button" wire:click="createProduct" wire:loading.attr="disabled"
                class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95 shrink-0 whitespace-nowrap cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Thêm Sản Phẩm Mới</span>
            </button>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span
                class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Tổng số: {{ isset($products) ? $products->total() : 0 }} sản phẩm
            </span>
        </div>

        {{-- Hiển thị thông tin sản phẩm, tồn kho và các thao tác quản lý --}}
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr
                            class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-slate-100">
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Sản Phẩm & SKU</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Slug</th>
                            <th class="py-3.5 sm:py-4 px-4">Danh Mục</th>
                            <th class="py-3.5 sm:py-4 px-4">Ngày thu hoạch tối thiểu</th>
                            <th class="py-3.5 sm:py-4 px-4 text-right">Giá Bán Niêm Yết</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Đơn Vị</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Tồn Kho (Lô FEFO)</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Đã Bán</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Đánh Giá</th>
                            <th class="py-3.5 sm:py-4 px-4 text-center">Hiển Thị Web</th>
                            <th class="py-3.5 sm:py-4 px-4 sm:px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 font-medium">
                        @if (isset($products) && count($products) > 0)
                            @foreach ($products as $product)
                                {{-- Lấy tổng tồn kho --}}
                                @php
                                    $totalStock = $productStocks[$product->id] ?? 0;
                                @endphp

                                <tr wire:key="product-{{ $product->id }}"
                                    class="hover:bg-slate-50 transition bg-white">

                                    <td class="py-3.5 px-4 pl-5 flex items-center gap-3">
                                        <img src="{{ $productImageUrls[$product->id] ?? 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300' }}"
                                            class="w-10 h-10 rounded-xl object-cover border border-slate-200 shrink-0"
                                            alt="{{ $product->name }}">

                                        <div class="min-w-0">
                                            <b
                                                class="text-slate-900 text-xs sm:text-sm block truncate hover:text-brand-700 cursor-pointer">
                                                {{ $product->name }}
                                            </b>

                                            <span
                                                class="font-mono text-[11px] text-brand-800 font-bold bg-brand-50 px-1.5 py-0.2 rounded border border-brand-200">
                                                {{ $product->sku }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-700">
                                        {{ $product->slug }}
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-700">
                                        {{ $product->category->name ?? 'Chưa phân nhóm' }}
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-700">
                                        {{ $product->min_growth_days }}
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                        {{ number_format($product->price, 0, ',', '.') }}đ
                                    </td>

                                    <td class="py-3.5 px-4 text-center font-mono font-bold">
                                        {{ $product->unit }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <b
                                            class="font-mono text-sm {{ $totalStock > 0 ? 'text-brand-800' : 'text-slate-400' }}">
                                            {{ $totalStock }} {{ $product->unit }}
                                        </b>
                                    </td>

                                    {{-- Hiển thị số lượng đã bán và điểm đánh giá trung bình của sản phẩm. --}}
                                    <td class="py-3.5 px-4 text-center font-mono text-slate-700 font-bold">
                                        {{ number_format($product->sold_count ?? 0, 0, ',', '.') }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center font-mono text-amber-500 font-bold">
                                        {{ number_format($product->avg_rating ?? 0, 1) }} ★
                                    </td>

                                    {{-- Đồng bộ nút hiển thị sau khi cập nhật trạng thái sản phẩm --}}
                                    <td class="py-3.5 px-4 text-center">
                                        <div x-data="{ enabled: @js($product->status === 'active') }"
                                            x-on:product-status-updated.window="if (Number($event.detail.id) === {{ $product->id }}) {enabled = $event.detail.status === 'active';}"
                                            class="inline-flex items-center">

                                            <button type="button" role="switch"
                                                wire:click="requestToggleStatus({{ $product->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="requestToggleStatus,toggleStatus"
                                                x-bind:disabled="confirmingProduct"
                                                x-bind:aria-checked="enabled.toString()"
                                                x-bind:aria-label="enabled
                                                    ?
                                                    'Tắt hiển thị sản phẩm' :
                                                    'Bật hiển thị sản phẩm'"
                                                x-bind:class="enabled
                                                    ?
                                                    'bg-brand-600' :
                                                    'bg-slate-300'"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full
                                        transition-colors duration-300
                                        focus:outline-none focus:ring-2 focus:ring-brand-500
                                        focus:ring-offset-2 disabled:opacity-60
                                        disabled:cursor-wait cursor-pointer">

                                                <span
                                                    x-bind:class="enabled
                                                        ?
                                                        'translate-x-5' :
                                                        'translate-x-1'"
                                                    class="inline-block h-5 w-5 rounded-full bg-white shadow
                                            ring-1 ring-slate-200
                                            transition-transform duration-300 ease-in-out">
                                                </span>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 pr-6 text-right space-x-1.5">
                                        <a href="{{ route('farm-staff.product-batch') }}"
                                            class="inline-flex items-center p-2 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 transition shadow-2xs font-bold text-xs"
                                            title="Quản lý Lô thu hoạch & FEFO">
                                            <i class="fa-solid fa-boxes-stacked mr-1"></i>
                                            Quản lý Lô
                                        </a>

                                        <button type="button" wire:click="editProduct({{ $product->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-2 rounded-xl bg-slate-100 text-slate-700 hover:text-brand-700 hover:bg-brand-50 transition cursor-pointer"
                                            title="Sửa sản phẩm">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="11" class="py-10 text-center text-slate-400 italic">
                                    Không tìm thấy sản phẩm nông sản nào phù hợp.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Hiển thị điều hướng khi danh sách sản phẩm có nhiều trang --}}
            @if ($products->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Hiển thị biểu mẫu thêm, sửa sản phẩm và khóa thao tác khi đang xử lý --}}
    <div id="modal-product" wire:key="product-modal-{{ $editingId ?? 'new' }}" x-data="{ uploadingImage: false }"
        x-on:product-images-ready="uploadingImage = false"
        x-on:livewire-upload-start="
        if ($event.detail.property === 'imageUploads') {
            uploadingImage = true
        }
    "
        x-on:livewire-upload-finish="
        if ($event.detail.property === 'imageUploads') {
            uploadingImage = false
        }
    "
        x-on:livewire-upload-error="
        if ($event.detail.property === 'imageUploads') {
            uploadingImage = false
        }
    "
        x-on:livewire-upload-cancel="
        if ($event.detail.property === 'imageUploads') {
            uploadingImage = false
        }
    "
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs {{ $showModal ? '' : 'hidden' }} p-3 sm:p-4">

        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-2xl shadow-2xl p-5 sm:p-8 max-h-[92vh] overflow-y-auto custom-scrollbar border border-slate-100 space-y-4">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base sm:text-xl font-extrabold text-slate-900" id="modal-product-title">
                    {{ $editingId ? 'Sửa Sản Phẩm: ' . $name : 'Thêm Sản Phẩm Nông Sản Mới' }}
                </h3>

                <button type="button" wire:click="closeModal" x-bind:disabled="uploadingImage || confirmingProduct"
                    wire:loading.attr="disabled"
                    wire:target="imageUploads,moveImage,removeImage,requestSaveProduct,saveProduct,closeModal"
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center cursor-pointer disabled:opacity-50">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            {{-- Kiểm tra dữ liệu trước khi mở hộp thoại xác nhận lưu sản phẩm --}}
            <form wire:submit="$js.submitProduct" novalidate
                x-on:submit.capture="
        if (confirmingProduct) {
            $event.preventDefault();
            $event.stopImmediatePropagation();
        }
    "
                class="space-y-3.5 text-xs sm:text-sm">
                <input type="hidden" id="p-id" value="{{ $editingId }}">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Tên sản phẩm <span class="text-red-500">*</span>
                    </label>

                    <input type="text" id="p-name" wire:model.live.debounce.300ms="name" maxlength="255"
                        required
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-brand-600">
                    @error('name')
                        <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Mã SKU <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="p-sku" wire:model="sku" maxlength="100" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono uppercase outline-none focus:border-brand-600">
                        @error('sku')
                            <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Danh mục <span class="text-red-500">*</span>
                        </label>

                        <select id="p-category" wire:model.live="category_id" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-brand-600 cursor-pointer">
                            @if (isset($categories))
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('category_id')
                            <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Đơn vị tính
                        </label>

                        <select id="p-unit" wire:model="unit" required
                            class="w-full min-w-0 bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-brand-600 cursor-pointer">

                            <option value="kg">Kg</option>
                            <option value="bó">Bó</option>
                            <option value="hộp 500g">Hộp 500g</option>

                            @if ($unit !== '' && !in_array($unit, ['kg', 'bó', 'hộp 500g'], true))
                                <option value="{{ $unit }}">
                                    {{ $unit }}
                                </option>
                            @endif
                        </select>
                        @error('unit')
                            <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Giá bán gốc (đ) <span class="text-red-500">*</span>
                        </label>

                        <input type="number" id="p-price" wire:model="price" min="0.01" max="9999999999.99"
                            step="0.01" required
                            class="w-full min-w-0 bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono outline-none focus:border-brand-600">
                        @error('price')
                            <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Số ngày thu hoạch tối thiểu <span class="text-red-500">*</span>
                        </label>

                        <input type="number" id="p-min-growth-days" wire:model="min_growth_days" min="1"
                            max="10000" step="1" required inputmode="numeric"
                            class="w-full min-w-0 bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono outline-none focus:border-brand-600">
                        @error('min_growth_days')
                            <p class="text-xs text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Mô tả đặc tính
                    </label>

                    <textarea id="p-desc" wire:model="description" maxlength="10000" rows="2"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-2.5 outline-none focus:border-brand-600 resize-none"></textarea>
                    @error('description')
                        <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tải nhiều ảnh, xem trước, sắp xếp ảnh bìa và loại bỏ ảnh --}}
                <div class="space-y-3">
                    <label for="p-images" class="block font-bold text-slate-700 mb-1">
                        Ảnh sản phẩm
                        <span class="text-red-500">*</span>
                    </label>

                    <input type="file" id="p-images" wire:model="imageUploads"
                        wire:key="product-images-input-{{ $imageInputKey }}" multiple accept=".jpg,.jpeg,.png,.webp"
                        x-bind:disabled="uploadingImage || confirmingProduct" wire:loading.attr="disabled"
                        wire:target="imageUploads,moveImage,removeImage,requestSaveProduct,saveProduct,closeModal"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-brand-600 disabled:opacity-60 disabled:cursor-wait">

                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Có thể chọn nhiều ảnh cùng lúc hoặc chọn thêm ảnh ở lần tiếp theo.
                        Mỗi ảnh tối đa 2 MB.
                    </p>

                    <p class="text-[11px] font-semibold text-brand-700">
                        Ảnh ở vị trí 1 sẽ là ảnh chính.
                        Dùng nút Lên/Xuống để đổi thứ tự.
                    </p>

                    @error('imageOrder')
                        <p role="alert" class="text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    @error('imageUploads')
                        <p role="alert" class="text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    {{-- Hiển thị lỗi của từng tệp trong lần tải nhiều ảnh. --}}
                    @foreach ($errors->get('imageUploads.*') as $uploadErrors)
                        @foreach ($uploadErrors as $message)
                            <p role="alert" class="text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @endforeach
                    @endforeach

                    {{-- Hiển thị lỗi kiểm tra lại nội dung ảnh trước khi lưu. --}}
                    @error('image')
                        <p role="alert" class="text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <p wire:loading wire:target="imageUploads" class="text-xs text-brand-700">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i>
                        Đang tải ảnh lên...
                    </p>

                    {{-- Xác định ảnh đã lưu hoặc ảnh mới để tạo bản xem trước. --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse ($imageOrder as $index => $token)
                            @php
                                $isOld = str_starts_with($token, 'old:');
                                $tokenId = $isOld ? substr($token, 4) : null;

                                $imageUrl = $isOld
                                    ? $existingImages[$tokenId]['url'] ?? null
                                    : (isset($newImages[$token])
                                        ? $newImages[$token]->temporaryUrl()
                                        : null);
                            @endphp

                            <div wire:key="product-image-{{ $token }}"
                                class="border border-slate-200 rounded-xl p-3 space-y-3">
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="Ảnh sản phẩm vị trí {{ $index + 1 }}"
                                        class="w-full h-36 object-contain rounded-lg bg-slate-50">
                                @else
                                    <div
                                        class="w-full h-36 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 text-xs">
                                        Không có ảnh xem trước
                                    </div>
                                @endif

                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-slate-700">
                                        Vị trí {{ $index + 1 }}
                                    </span>

                                    @if ($index === 0)
                                        <span
                                            class="text-[10px] font-bold text-brand-800 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-full">
                                            Ảnh chính
                                        </span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" wire:click="moveImage({{ $index }}, -1)"
                                        wire:loading.attr="disabled"
                                        wire:target="imageUploads,moveImage,removeImage,requestSaveProduct,saveProduct,closeModal"
                                        @disabled($index === 0)
                                        class="rounded-lg bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 text-xs font-bold text-slate-700 disabled:opacity-40">
                                        <i class="fa-solid fa-arrow-up"></i>
                                        Lên
                                    </button>

                                    <button type="button" wire:click="moveImage({{ $index }}, 1)"
                                        wire:loading.attr="disabled"
                                        wire:target="imageUploads,moveImage,removeImage,requestSaveProduct,saveProduct,closeModal"
                                        @disabled($index === count($imageOrder) - 1)
                                        class="rounded-lg bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 text-xs font-bold text-slate-700 disabled:opacity-40">
                                        <i class="fa-solid fa-arrow-down"></i>
                                        Xuống
                                    </button>

                                    <button type="button" wire:click="removeImage({{ $index }})"
                                        wire:loading.attr="disabled"
                                        wire:target="imageUploads,moveImage,removeImage,requestSaveProduct,saveProduct,closeModal"
                                        class="ml-auto rounded-lg bg-red-50 hover:bg-red-100 px-2.5 py-1.5 text-xs font-bold text-red-600">
                                        <i class="fa-solid fa-xmark"></i>
                                        Bỏ ảnh
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p
                                class="sm:col-span-2 rounded-xl border border-dashed border-slate-200 p-5 text-center text-xs text-slate-400">
                                Chưa có ảnh. Vui lòng chọn ít nhất một ảnh.
                            </p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" wire:click="closeModal" x-bind:disabled="uploadingImage"
                        wire:loading.attr="disabled" wire:target="requestSaveProduct,saveProduct,closeModal"
                        class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>

                    <button type="submit" x-bind:disabled="confirmingProduct" wire:loading.attr="disabled"
                        wire:target="imageUploads,moveImage,removeImage,requestSaveProduct,saveProduct,closeModal"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2 rounded-xl transition cursor-pointer disabled:opacity-50 disabled:cursor-wait">
                        <span wire:loading.remove wire:target="requestSaveProduct,saveProduct">
                            Lưu Sản phẩm
                        </span>

                        <span wire:loading wire:target="requestSaveProduct,saveProduct">
                            Đang lưu...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Kiểm tra dữ liệu, xác nhận lưu và thay đổi trạng thái sản phẩm. --}}
    @script
        <script>
            let submitting = false;
            let confirming = false;

            function setConfirmationState(busy) {
                $wire.$el.dispatchEvent(
                    new CustomEvent('product-confirm-state', {
                        detail: {
                            busy
                        }
                    })
                );
            }

            async function showProductErrors(error = null) {
                const messages = error?.errors ?
                    Object.values(error.errors).flat() :
                    $wire.$errors.all();

                if (!messages.length && !error) return;

                await Swal.fire({
                    heightAuto: false,
                    scrollbarPadding: false,
                    icon: 'error',
                    title: 'Không thể lưu sản phẩm',
                    text: messages.length ?
                        messages.join('\n') : 'Yêu cầu chưa hoàn tất. Vui lòng kiểm tra kết nối và thử lại.',
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: '#15803d'
                });
            }

            async function runProductAction(action, {
                inlineErrors = false
            } = {}) {
                try {
                    await action();

                    if (!inlineErrors) {
                        await showProductErrors();
                    }
                } catch (error) {
                    const isValidationError =
                        Number(error?.status) === 422 ||
                        (
                            error?.errors &&
                            Object.keys(error.errors).length > 0
                        );

                    if (inlineErrors && isValidationError) {
                        return;
                    }

                    await showProductErrors(error);
                }
            }

            $js('submitProduct', async () => {
                if (submitting || confirming) return;

                submitting = true;

                try {
                    // Kiểm tra dữ liệu trước khi mở xác nhận.
                    await runProductAction(
                        () => $wire.requestSaveProduct(), {
                            inlineErrors: true
                        }
                    );
                } finally {
                    submitting = false;
                }
            });

            $wire.on('confirm-product-save', async () => {
                if (confirming) return;

                confirming = true;
                setConfirmationState(true);

                try {
                    const result = await Swal.fire({
                        heightAuto: false,
                        scrollbarPadding: false,
                        icon: 'question',
                        title: $wire.editingId !== null ?
                            'Xác nhận cập nhật sản phẩm?' : 'Xác nhận thêm sản phẩm?',
                        text: 'Bạn có muốn lưu thông tin và thứ tự ảnh sản phẩm?',
                        showCancelButton: true,
                        confirmButtonText: 'Xác nhận',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#15803d',
                        cancelButtonColor: '#6b7280',
                        reverseButtons: true,
                        allowOutsideClick: false
                    });

                    if (result.isConfirmed) {
                        await runProductAction(
                            () => $wire.saveProduct(), {
                                inlineErrors: true
                            }
                        );
                    }
                } finally {
                    confirming = false;
                    setConfirmationState(false);
                }
            });

            $wire.on('confirm-product-status', async ({
                id,
                name,
                expectedStatus,
                targetStatus
            }) => {
                if (confirming) return;

                confirming = true;
                setConfirmationState(true);

                try {
                    const enabling = targetStatus === 'active';

                    const result = await Swal.fire({
                        heightAuto: false,
                        scrollbarPadding: false,
                        icon: enabling ? 'question' : 'warning',

                        title: enabling ?
                            'Bật hiển thị sản phẩm?' : 'Tắt hiển thị sản phẩm?',

                        text: enabling ?
                            'Hiển thị “' + name + '” trên cửa hàng?' : 'Ẩn “' + name + '” khỏi cửa hàng?',

                        showCancelButton: true,
                        confirmButtonText: 'Xác nhận',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: enabling ? '#15803d' : '#dc2626',
                        cancelButtonColor: '#6b7280',
                        reverseButtons: true,
                        allowOutsideClick: false
                    });

                    if (result.isConfirmed) {
                        await runProductAction(
                            () => $wire.toggleStatus(
                                id,
                                expectedStatus,
                                targetStatus
                            )
                        );
                    }
                } catch (error) {
                    await showProductErrors(error);
                } finally {
                    confirming = false;
                    setConfirmationState(false);
                }
            });
        </script>
    @endscript
</div>
