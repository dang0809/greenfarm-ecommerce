<div x-data="{ confirmingProduct: false }" x-on:product-confirm-state="confirmingProduct = $event.detail.busy">
    <!-- Header-->
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

        <div class="flex items-center gap-2 shrink-0">
            <span
                class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Tổng số: {{ isset($products) ? $products->total() : 0 }} sản phẩm
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

                    @if(isset($categories))
                    @foreach($categories as $cat)
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

            <!-- Nút Thêm Sản Phẩm Mới -->
            <button type="button" wire:click="createProduct" wire:loading.attr="disabled"
                class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95 shrink-0 whitespace-nowrap cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Thêm Sản Phẩm Mới</span>
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
                            <th class="py-3.5 sm:py-4 px-4 sm:px-5">Slug</th>
                            <th class="py-3.5 sm:py-4 px-4">Danh Mục</th>
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
                        @if(isset($products) && count($products) > 0)
                        @foreach($products as $product)
                        @php
                        $totalStock = $productStocks[$product->id] ?? 0;
                        @endphp

                        <tr wire:key="product-{{ $product->id }}" class="hover:bg-slate-50 transition bg-white">

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
                                {{ $product->slug}}
                            </td>

                            <td class="py-3.5 px-4 text-slate-700">
                                {{ $product->category->name ?? 'Chưa phân nhóm' }}
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

                            <td class="py-3.5 px-4 text-center font-mono">
                                {{ $product->sold_count ?? 0 }} {{ $product->unit }}
                            </td>

                            <td class="py-3.5 px-4 text-center font-mono text-amber-500 font-bold">
                                {{ $product->avg_rating ?? '5.0' }} ★
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div x-data="{ enabled: @js($product->status === 'active') }"
                                    x-on:product-status-updated.window="if (Number($event.detail.id) === {{ $product->id }}) {enabled = $event.detail.status === 'active';}"
                                    class="inline-flex items-center">

                                    <button type="button" role="switch"
                                        wire:click="requestToggleStatus({{ $product->id }})"
                                        wire:loading.attr="disabled" wire:target="requestToggleStatus,toggleStatus"
                                        x-bind:disabled="confirmingProduct" x-bind:aria-checked="enabled.toString()"
                                        x-bind:aria-label="enabled
                                        ? 'Tắt hiển thị sản phẩm'
                                        : 'Bật hiển thị sản phẩm'" x-bind:class="enabled
                                        ? 'bg-brand-600'
                                        : 'bg-slate-300'" class="relative inline-flex h-6 w-11 items-center rounded-full
                                        transition-colors duration-300
                                        focus:outline-none focus:ring-2 focus:ring-brand-500
                                        focus:ring-offset-2 disabled:opacity-60
                                        disabled:cursor-wait cursor-pointer">

                                        <span x-bind:class="enabled
                                            ? 'translate-x-5'
                                            : 'translate-x-1'" class="inline-block h-5 w-5 rounded-full bg-white shadow
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
                            <td colspan="9" class="py-10 text-center text-slate-400 italic">
                                Không tìm thấy sản phẩm nông sản nào phù hợp.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            @if(isset($products) && method_exists($products, 'hasPages') && $products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- MODAL THÊM / SỬA SẢN PHẨM -->
    <div id="modal-product" wire:key="product-modal-{{ $editingId ?? 'new' }}" x-data="{ uploadingImage: false }"
        x-on:livewire-upload-start="
        if ($event.detail.property === 'newImage') uploadingImage = true
    " x-on:livewire-upload-finish="
        if ($event.detail.property === 'newImage') uploadingImage = false
    " x-on:livewire-upload-error="
        if ($event.detail.property === 'newImage') uploadingImage = false
    " x-on:livewire-upload-cancel="
        if ($event.detail.property === 'newImage') uploadingImage = false
    "
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs {{ $showModal ? '' : 'hidden' }} p-3 sm:p-4">

        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-2xl shadow-2xl p-5 sm:p-8 max-h-[92vh] overflow-y-auto custom-scrollbar border border-slate-100 space-y-4">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base sm:text-xl font-extrabold text-slate-900" id="modal-product-title">
                    {{ $editingId ? 'Sửa Sản Phẩm: '.$name : 'Thêm Sản Phẩm Nông Sản Mới' }}
                </h3>

                <button type="button" wire:click="closeModal" x-bind:disabled="uploadingImage"
                    wire:loading.attr="disabled" wire:target="requestSaveProduct,saveProduct,cancelNewImage,closeModal"
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="$js.submitProduct" x-on:submit.capture="
                if (uploadingImage) {
                    $event.preventDefault();
                    $event.stopImmediatePropagation();
                }
            " class="space-y-3.5 text-xs sm:text-sm">
                @if ($errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700">

                    @foreach ($errors->all() as $message)
                    <p>{{ $message }}</p>
                    @endforeach
                </div>
                @endif

                <input type="hidden" id="p-id" value="{{ $editingId }}">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Tên sản phẩm <span class="text-red-500">*</span>
                    </label>

                    <input type="text" id="p-name" wire:model.live.debounce.300ms="name" maxlength="255" required
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-brand-600">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Mã SKU <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="p-sku" wire:model="sku" maxlength="100" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono uppercase outline-none focus:border-brand-600">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Danh mục <span class="text-red-500">*</span>
                        </label>

                        <select id="p-category" wire:model.live="category_id" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-brand-600 cursor-pointer">
                            @if(isset($categories))
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">
                                {{ $cat->name }}
                            </option>
                            @endforeach
                            @endif
                        </select>
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
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Giá bán gốc (đ) <span class="text-red-500">*</span>
                        </label>

                        <input type="number" id="p-price" wire:model="price" min="0.01" max="9999999999.99" step="0.01"
                            required
                            class="w-full min-w-0 bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono outline-none focus:border-brand-600">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Mô tả đặc tính
                    </label>

                    <textarea id="p-desc" wire:model="description" maxlength="10000" rows="2"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-2.5 outline-none focus:border-brand-600 resize-none"></textarea>
                </div>

                {{-- Ảnh chính của sản phẩm --}}
                <div class="space-y-3">
                    <label for="p-image" class="block font-bold text-slate-700 mb-1">
                        Ảnh sản phẩm
                        @if ($editingId === null || ! $currentImageUrl)
                        <span class="text-red-500">*</span>
                        @endif
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if ($editingId)
                        <div class="border border-slate-200 rounded-xl p-3">
                            <p class="font-bold text-slate-600 mb-2">
                                Ảnh đang lưu
                            </p>

                            @if ($currentImageUrl)
                            <img src="{{ $currentImageUrl }}" alt="Ảnh sản phẩm đang lưu"
                                class="w-full h-36 object-contain rounded-lg bg-slate-50">
                            @else
                            <p class="text-[11px] text-slate-400">
                                Sản phẩm chưa có ảnh phải tải ảnh lên trước khi lưu.
                                Nếu đã có ảnh, có thể giữ ảnh hiện tại.
                                JPG, JPEG, PNG hoặc WEBP; tối đa 2 MB.
                            </p>
                            @endif
                        </div>
                        @endif

                        {{-- Preview ảnh vừa chọn --}}
                        <div class="border border-slate-200 rounded-xl p-3">
                            <p class="font-bold text-slate-600 mb-2">
                                {{ $editingId ? 'Ảnh mới đang chọn' : 'Xem trước ảnh' }}
                            </p>

                            @if ($newImage && ! $errors->has('newImage'))
                            <img src="{{ $newImage->temporaryUrl() }}" alt="Xem trước ảnh mới"
                                class="w-full h-36 object-contain rounded-lg bg-slate-50">
                            @else
                            <p class="text-slate-400">
                                Chưa chọn ảnh mới.
                            </p>
                            @endif
                        </div>
                    </div>

                    <input type="file" id="p-image" wire:key="product-image-{{ $imageInputKey }}" wire:model="newImage"
                        accept=".jpg,.jpeg,.png,.webp" x-bind:disabled="uploadingImage" wire:loading.attr="disabled"
                        wire:target="requestSaveProduct,saveProduct,cancelNewImage,closeModal" x-on:change.capture="
            const input = $event.target;
            const file = input.files?.[0];

            if (!file) return;

            const extension = file.name.split('.').pop().toLowerCase();
            const mime = (file.type || '').toLowerCase();

            let message = '';

            if (
                !['jpg', 'jpeg', 'png', 'webp'].includes(extension)
                || (
                    mime !== ''
                    && !['image/jpeg', 'image/png', 'image/webp'].includes(mime)
                )
            ) {
                message = 'Tệp được chọn không phải ảnh hợp lệ. Chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP.';
            } else if (file.size > 2 * 1024 * 1024) {
                message = 'Ảnh sản phẩm không được vượt quá 2 MB.';
            }

            if (message !== '') {
                $event.preventDefault();
                $event.stopImmediatePropagation();

                input.value = '';

                Swal.fire({
                    icon: 'error',
                    title: 'Ảnh không hợp lệ',
                    text: message,
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: '#15803d'
                });
            }
        " class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-brand-600">

                    <p class="text-[11px] text-slate-400">
                        JPG, JPEG, PNG hoặc WEBP. Tối đa 2 MB.
                    </p>

                    <p x-show="uploadingImage" x-cloak class="text-xs text-brand-700">
                        Đang tải ảnh lên…
                    </p>

                    @error('newImage')
                    <p role="alert" class="text-xs text-red-600">
                        {{ $message }}
                    </p>
                    @enderror

                    @if ($newImage)
                    <button type="button" wire:click="cancelNewImage" x-bind:disabled="uploadingImage"
                        wire:loading.attr="disabled"
                        wire:target="requestSaveProduct,saveProduct,cancelNewImage,closeModal"
                        class="text-xs font-bold text-red-600 hover:text-red-700">
                        Hủy ảnh đang chọn
                    </button>
                    @endif
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" wire:click="closeModal" x-bind:disabled="uploadingImage"
                        wire:loading.attr="disabled"
                        wire:target="requestSaveProduct,saveProduct,cancelNewImage,closeModal"
                        class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>

                    <button type="submit" x-bind:disabled="uploadingImage" wire:loading.attr="disabled"
                        wire:target="requestSaveProduct,saveProduct,cancelNewImage,closeModal"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2 rounded-xl transition cursor-pointer">
                        Lưu Sản Phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>

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
            icon: 'error',
            title: 'Không thể lưu sản phẩm',
            text: messages.length ?
                messages.join('\n') : 'Yêu cầu chưa hoàn tất. Vui lòng kiểm tra kết nối và thử lại.',
            confirmButtonText: 'Đã hiểu',
            confirmButtonColor: '#15803d'
        });
    }

    async function runProductAction(action) {
        try {
            await action();
            await showProductErrors();
        } catch (error) {
            await showProductErrors(error);
        }
    }

    $js('submitProduct', async () => {
        if (submitting || confirming) return;

        submitting = true;

        try {
            // Kiểm tra dữ liệu trước khi mở xác nhận.
            await runProductAction(
                () => $wire.requestSaveProduct()
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
                icon: 'question',

                title: $wire.editingId !== null ?
                    'Xác nhận cập nhật sản phẩm?' : 'Xác nhận thêm sản phẩm?',

                text: 'Bạn có muốn lưu thông tin và ảnh sản phẩm?',
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
                    () => $wire.saveProduct()
                );
            }
        } catch (error) {
            await showProductErrors(error);
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