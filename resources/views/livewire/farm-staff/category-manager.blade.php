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
                    Quản Lý Danh Mục Nhóm Nông Sản
                </h1>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span
                class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <i class="fa-solid fa-folder-tree"></i> Quản trị bảng `categories`
            </span>
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

            <button onclick="Livewire.dispatch('open-create-category-modal')"
                class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl sm:rounded-2xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95 shrink-0 whitespace-nowrap self-start sm:self-auto cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>+ Thêm Danh Mục Mới</span>
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
                            <th class="py-3.5 px-4">Cấp Phân Nhánh</th>
                            <th class="py-3.5 px-4 font-mono">Đường Dẫn URL (Slug)</th>
                            <th class="py-3.5 px-4 text-center">Số Sản Phẩm</th>
                            <th class="py-3.5 px-4 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($categories as $category)
                        <tr class="hover:bg-slate-50 transition bg-white">
                            <td class="py-3.5 px-5 flex items-center gap-3">
                                <img src="{{ $category->image ?? 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300' }}"
                                    class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                                <b
                                    class="text-slate-900 text-xs sm:text-sm">{{ $category->parent_id ? '↳ ' : '' }}{{ $category->name }}</b>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                @if($category->parent)
                                <span
                                    class="bg-brand-50 text-brand-800 border border-brand-200 px-2 py-0.5 rounded-lg">{{ $category->parent->name }}</span>
                                @else
                                <span class="text-slate-400 italic">Danh mục gốc (Cấp 1)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 text-xs">/{{ $category->slug }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold">{{ $category->products_count }} SP
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span
                                    class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border {{ $category->status === 'active' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $category->status === 'active' ? 'Hiển thị' : 'Ẩn' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right space-x-1.5">
                                <button wire:click="deleteCategory({{ $category->id }})"
                                    wire:confirm="Bạn có chắc chắn muốn xóa danh mục này?"
                                    class="p-1.5 rounded-lg bg-slate-100 hover:text-rose-600 cursor-pointer"
                                    title="Xóa danh mục">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 italic">Chưa có danh mục nông sản
                                nào được khởi tạo.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            @if($categories->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
            @endif
        </div>
    </div>
</div>