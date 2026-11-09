<div>
    <!-- Header -->
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
                    Quản Lý Danh Mục
                </h1>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div
                class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-700"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12">

        <!-- Thanh Tìm Kiếm & Nút Thêm -->
        <div
            class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Tìm tên danh mục, đường dẫn slug..."
                    class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm outline-none focus:border-brand-600 bg-[#FAFAF8] font-medium transition-all">
            </div>

            <!-- Nút Thêm Danh Mục Mới -->
            <button type="button" wire:click="createCategory"
                class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95 shrink-0 whitespace-nowrap self-start sm:self-auto cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Thêm Danh Mục Mới</span>
            </button>
        </div>

        <!-- Bảng Dữ Liệu Danh Mục -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr
                            class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-slate-100">
                            <th class="py-3.5 px-5">Tên Danh Mục & Ảnh</th>
                            <th class="py-3.5 px-4 font-mono">Slug</th>
                            <th class="py-3.5 px-4 text-center">Số Sản Phẩm</th>
                            <th class="py-3.5 px-4 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($categories as $category)
                        <tr wire:key="category-{{ $category->id }}" class="hover:bg-slate-50 transition bg-white">
                            <td class="py-3.5 px-5 flex items-center gap-3">
                                <img src="{{ $categoryImageUrls[$category->id] ?? 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300' }}"
                                    class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0"
                                    alt="{{ $category->name }}">
                                <b class="text-slate-900 text-xs sm:text-sm">{{ $category->name }}</b>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 text-xs">/{{ $category->slug }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold">{{ $category->products_count ?? 0 }}
                                SP</td>
                            <td class="py-3.5 px-4 text-center">
                                <span
                                    class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border {{ $category->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $category->is_active ? 'Hiển thị' : 'Ẩn' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right space-x-1.5">
                                <button type="button" wire:click="editCategory({{ $category->id }})"
                                    class="p-1.5 rounded-lg bg-slate-100 hover:text-brand-700 hover:bg-slate-200 transition cursor-pointer"
                                    title="Sửa danh mục">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                <button type="button" wire:click="requestDeleteCategory({{ $category->id }})"
                                    wire:loading.attr="disabled" wire:target="requestDeleteCategory,deleteCategory"
                                    class="p-1.5 rounded-lg bg-slate-100 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                    title="Xóa danh mục">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400 italic">Chưa có danh mục nông sản
                                nào được khởi tạo.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            @if ($categories->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- MODAL THÊM / SỬA DANH MỤC -->
    <div wire:key="category-modal-{{ $editingId ?? 'new' }}-{{ $imageInputKey }}" x-data="{ uploadingImage: false }"
        x-on:livewire-upload-start="
        if ($event.detail.property === 'newImage') uploadingImage = true" x-on:livewire-upload-finish="
        if ($event.detail.property === 'newImage') uploadingImage = false" x-on:livewire-upload-error="
        if ($event.detail.property === 'newImage') uploadingImage = false" x-on:livewire-upload-cancel="
        if ($event.detail.property === 'newImage') uploadingImage = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs {{ $showModal ? '' : 'hidden' }} p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-7 max-h-[92vh] overflow-y-auto custom-scrollbar border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-base border border-amber-200 shrink-0">
                        <i class="fa-solid fa-folder-tree"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900">
                            {{ $editingId === null ? 'Thêm Danh Mục Mới' : 'Sửa Danh Mục: '.$name }}
                        </h3>
                    </div>
                </div>
                <button type="button" wire:click="closeModal" x-bind:disabled="uploadingImage"
                    wire:loading.attr="disabled" wire:target="requestSaveCategory,saveCategory,closeModal"
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="requestSaveCategory" x-on:submit.capture="
        if (uploadingImage) {
            $event.preventDefault();
            $event.stopImmediatePropagation();
        }" class="space-y-3.5 text-xs">

                @error('form')
                <p role="alert">{{ $message }}</p>
                @enderror

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Tên nhóm danh mục
                        <span class="text-red-500">*</span>
                    </label>

                    <input type="text" required maxlength="255" wire:model.live.debounce.300ms="name"
                        placeholder="VD: Rau Ăn Lá Thủy Canh"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-brand-600 focus:bg-white font-semibold">

                    @error('name')
                    <p role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Đường dẫn URL tĩnh (Slug)
                        <span class="text-red-500">*</span>
                    </label>

                    <input type="text" maxlength="255" wire:model="slug" placeholder="rau-an-la-thuy-canh"
                        class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2 font-mono text-xs text-slate-600 outline-none">

                    @error('slug')
                    <p role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="c-file" class="block font-bold text-slate-700 mb-1">
                        Ảnh đại diện danh mục

                        @if ($editingId === null || ! $currentImageUrl)
                        <span class="text-red-500">*</span>
                        @endif
                    </label>

                    @if ($editingId !== null && $currentImageUrl)
                    <p class="text-[10px] text-slate-400 mt-1">
                        Danh mục đã có ảnh. Không cần tải lại nếu không muốn thay ảnh.
                    </p>
                    @else
                    <p class="text-[10px] text-slate-400 mt-1">
                        Danh mục chưa có ảnh. Vui lòng tải ảnh JPG, JPEG hoặc WEBP,
                        tối đa 2 MB.
                    </p>
                    @endif

                    <input type="file" id="c-file" wire:key="category-image-{{ $imageInputKey }}" wire:model="newImage"
                        accept="image/jpeg,image/png,image/webp" x-bind:disabled="uploadingImage"
                        wire:loading.attr="disabled" wire:target="requestSaveCategory,saveCategory,closeModal"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-amber-50 file:text-amber-800 cursor-pointer">

                    <p class="text-[10px] text-slate-400 mt-1">
                        Khi thêm mới hoặc cập nhật có thay đổi, vui lòng chọn ảnh.
                        Chấp nhận JPG, JPEG, PNG, WEBP; tối đa 2 MB.
                    </p>

                    <template x-if="uploadingImage">
                        <p>Đang tải ảnh lên…</p>
                    </template>

                    @error('newImage')
                    <p role="alert">{{ $message }}</p>
                    @enderror

                    <div class="mt-2 {{ ($newImage || $currentImageUrl) ? '' : 'hidden' }}">

                        @if ($newImage && ! $errors->has('newImage'))
                        <img wire:key="category-preview-new-{{ $newImage->getFilename() }}"
                            src="{{ $newImage->temporaryUrl() }}"
                            class="w-16 h-16 object-cover rounded-xl border border-slate-200" alt="Ảnh mới đang chọn">

                        @elseif ($currentImageUrl)
                        <img wire:key="category-preview-current-{{ $editingId }}-{{ $imageInputKey }}"
                            src="{{ $currentImageUrl }}"
                            class="w-16 h-16 object-cover rounded-xl border border-slate-200"
                            alt="Ảnh danh mục hiện tại">
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Trạng thái:
                    </label>

                    <select wire:model="status"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold outline-none cursor-pointer">
                        <option value="active">Hiển thị trên Web</option>
                        <option value="hidden">Tạm ẩn</option>
                    </select>

                    @error('status')
                    <p role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" wire:click="closeModal" x-bind:disabled="uploadingImage"
                        wire:loading.attr="disabled" wire:target="requestSaveCategory,saveCategory,closeModal"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>

                    <button type="submit" x-bind:disabled="uploadingImage" wire:loading.attr="disabled"
                        wire:target="requestSaveCategory,saveCategory,closeModal"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2 rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                        Lưu Danh Mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@script
<script>
let confirming = false;

async function showCategoryRequestError() {
    await Swal.fire({
        icon: 'error',
        title: 'Yêu cầu chưa hoàn tất',
        text: 'Vui lòng kiểm tra dữ liệu, kết nối và thử lại.',
        confirmButtonText: 'Đã hiểu',
        confirmButtonColor: '#15803d'
    });
}

$wire.on('confirm-category-save', async ({
    editing
}) => {
    if (confirming) return;

    confirming = true;

    try {
        const result = await Swal.fire({
            icon: 'question',
            title: editing ?
                'Xác nhận cập nhật danh mục?' : 'Xác nhận thêm danh mục?',
            text: 'Bạn có muốn lưu thông tin và ảnh danh mục?',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#15803d',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            allowOutsideClick: false
        });

        if (result.isConfirmed) {
            await $wire.saveCategory();
        }

        // Hủy: không đóng modal, không reset dữ liệu hoặc ảnh.
    } catch (error) {
        await showCategoryRequestError();
    } finally {
        confirming = false;
    }
});

$wire.on('confirm-category-delete', async ({
    id,
    name
}) => {
    if (confirming) return;

    confirming = true;

    try {
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Xác nhận xóa danh mục?',
            text: `Bạn có chắc muốn xóa danh mục “${name}”?`,
            showCancelButton: true,
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            allowOutsideClick: false
        });

        if (result.isConfirmed) {
            await $wire.deleteCategory(id);
        }

        // Hủy: không gọi action xóa.
    } catch (error) {
        await showCategoryRequestError();
    } finally {
        confirming = false;
    }
});
</script>
@endscript