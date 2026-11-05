<div>
    <!-- Header Topbar -->
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

    </header>

    <!-- Workspace Viewport -->
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
                        @if(isset($categories) && count($categories) > 0)
                        @foreach($categories as $category)
                        <tr wire:key="category-{{ $category->id }}" class="hover:bg-slate-50 transition bg-white">
                            <td class="py-3.5 px-5 flex items-center gap-3">
                                <img src="{{ $category->image ?? 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300' }}"
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
                                <!-- Nút Sửa Danh Mục với icon <i class="fa-regular fa-pen-to-square"></i> -->
                                <button type="button" wire:click="editCategory({{ $category->id }})"
                                    class="p-1.5 rounded-lg bg-slate-100 hover:text-brand-700 hover:bg-slate-200 transition cursor-pointer"
                                    title="Sửa danh mục">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                <button type="button" wire:click="deleteCategory({{ $category->id }})"
                                    wire:confirm="Bạn có chắc chắn muốn xóa danh mục này?" wire:loading.attr="disabled"
                                    wire:target="deleteCategory"
                                    class="p-1.5 rounded-lg bg-slate-100 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                    title="Xóa danh mục">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400 italic">Chưa có danh mục nông sản
                                nào được khởi tạo.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            @if(isset($categories) && method_exists($categories, 'hasPages') && $categories->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- MODAL THÊM / SỬA CÂY DANH MỤC -->
    <div id="modal-category"
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
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900" id="modal-category-title">
                            {{ $editingId === null ? 'Thêm Danh Mục Mới' : 'Sửa Danh Mục: '.$name }}
                        </h3>
                    </div>
                </div>
                <button type="button" wire:click="closeModal"
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form wire:submit="saveCategory" class="space-y-3.5 text-xs">
                {{-- Chỉ hiển thị ID; không bind vào editingId vì đây là Locked property. --}}
                <input type="hidden" id="c-id" value="{{ $editingId }}">

                @error('form')
                <p role="alert">{{ $message }}</p>
                @enderror

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Tên nhóm danh mục
                        <span class="text-red-500">*</span>
                    </label>

                    <input type="text" id="c-name" required maxlength="255" wire:model.live.debounce.300ms="name"
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

                    <input type="text" id="c-slug" maxlength="255" wire:model="slug" placeholder="rau-an-la-thuy-canh"
                        class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2 font-mono text-xs text-slate-600 outline-none">

                    @error('slug')
                    <p role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Ảnh đại diện danh mục
                    </label>

                    <input type="file" id="c-file" wire:key="category-image-{{ $imageInputKey }}" wire:model="newImage"
                        accept="image/jpeg,image/png,image/webp" wire:loading.attr="disabled" wire:target="saveCategory"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-amber-50 file:text-amber-800 cursor-pointer">

                    @error('newImage')
                    <p role="alert">{{ $message }}</p>
                    @enderror

                    @php
                    $previewUrl = $newImage && ! $errors->has('newImage')
                    ? $newImage->temporaryUrl()
                    : $currentImageUrl;
                    @endphp

                    <div id="c-preview-box" class="mt-2 {{ $previewUrl ? '' : 'hidden' }}">
                        @if ($previewUrl)
                        <img id="c-preview-img" src="{{ $previewUrl }}"
                            class="w-16 h-16 object-cover rounded-xl border border-slate-200" alt="Preview">
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Trạng thái:
                    </label>

                    <select id="c-status" wire:model="status"
                        class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold outline-none cursor-pointer">
                        <option value="active">Hiển thị trên Web (active)</option>
                        <option value="hidden">Tạm ẩn (hidden)</option>
                    </select>

                    @error('status')
                    <p role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="saveCategory,newImage"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>

                    <button type="submit" wire:loading.attr="disabled" wire:target="saveCategory,newImage"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-5 py-2 rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                        Lưu Danh Mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openCreateCategoryModal() {
    document.getElementById('modal-category-title').textContent = "Thêm Danh Mục Mới";
    document.getElementById('c-id').value = '';
    document.getElementById('c-name').value = '';
    document.getElementById('c-slug').value = '';
    document.getElementById('c-status').value = 'active';
    document.getElementById('c-preview-box').classList.add('hidden');
    openModal('modal-category');
}

function openEditCategoryModal(id, name, slug, status, image) {
    document.getElementById('modal-category-title').textContent = `Sửa Danh Mục: ${name}`;
    document.getElementById('c-id').value = id;
    document.getElementById('c-name').value = name;
    document.getElementById('c-slug').value = slug;
    document.getElementById('c-status').value = status;

    if (image) {
        document.getElementById('c-preview-img').src = image;
        document.getElementById('c-preview-box').classList.remove('hidden');
    } else {
        document.getElementById('c-preview-box').classList.add('hidden');
    }
    openModal('modal-category');
}

function generateSlug(val) {
    const slug = val.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim().replace(/\s+/g, '-');
    document.getElementById('c-slug').value = slug;
}

function previewCatImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('c-preview-img').src = e.target.result;
            document.getElementById('c-preview-box').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleCategorySubmit(e) {
    e.preventDefault();
    Swal.fire({
        icon: 'success',
        title: 'Đã lưu danh mục thành công!',
        timer: 1500,
        showConfirmButton: false
    });
    closeModal('modal-category');
}

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>
@endpush