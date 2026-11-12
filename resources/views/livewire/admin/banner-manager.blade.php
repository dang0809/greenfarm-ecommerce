<div x-data="{ tab: 'banners', confirming: false }" x-on:content-confirm-state="confirming = $event.detail.busy"
    x-on:keydown.escape.window="
    if (!confirming && !Swal.isVisible() && $wire.modal) {
        $wire.closeModal();
    }
"
    class="flex flex-1 min-h-0 flex-col overflow-hidden">
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
                    Nội Dung & Banner
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span
                class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Truyền thông Website
            </span>
        </div>
    </header>

    <!-- Workspace Viewport -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12">

        <!-- ================= 3. THANH TAB CHUYỂN ĐỔI 3 PHÂN HỆ NỘI DUNG (ĐÃ BỎ CÁC CHỮ TRONG NGOẶC) ================= -->
        <div class="w-full bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div
                class="flex overflow-x-auto border-b border-slate-100 px-4 sm:px-6 pt-3 gap-6 sm:gap-8 text-sm no-scrollbar bg-slate-50/50 w-full">
                <button type="button" id="tab-btn-banners" x-on:click="tab = 'banners'"
                    x-bind:class="tab === 'banners'
                        ?
                        'tab-btn-active' :
                        'tab-btn-inactive'"
                    class="pb-3 transition whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-images"></i>
                    <span>Banner Quảng Cáo Trang Chủ</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full"
                        id="badge-banners-count">{{ $banners->count() }}</span>
                </button>
                <button type="button" id="tab-btn-pages" x-on:click="tab = 'pages'"
                    x-bind:class="tab === 'pages'
                        ?
                        'tab-btn-active' :
                        'tab-btn-inactive'"
                    class="pb-3 transition whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Trang Tĩnh & Câu Chuyện Thương Hiệu</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full"
                        id="badge-pages-count">{{ $pages->count() }}</span>
                </button>
                <button type="button" id="tab-btn-farm_images" x-on:click="tab = 'farm_images'"
                    x-bind:class="tab === 'farm_images'
                        ?
                        'tab-btn-active' :
                        'tab-btn-inactive'"
                    class="pb-3 transition whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-photo-film"></i>
                    <span>Bộ Sưu Tập Ảnh Nông Trại</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full"
                        id="badge-farm-images-count">{{ $farmImages->count() }}</span>
                </button>
            </div>
        </div>

        <!-- ================= TAB 1: BANNER QUẢNG CÁO TRANG CHỦ ================= -->
        <div id="tab-pane-banners" x-show="tab === 'banners'" class="space-y-4">

            <!-- BẢNG DANH SÁCH BANNERS KÈM THANH TÁC VỤ (NÚT THÊM MỚI ĐƯA XUỐNG ĐÂY) -->
            <div
                class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden space-y-4">
                <div
                    class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900">Danh Sách Banner Quảng Cáo</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Sắp xếp theo thứ tự ưu tiên và kiểm soát bật/tắt hiển
                            thị trực tiếp</p>
                    </div>

                    <!-- Nút Thêm Mới Banner đưa xuống đây -->
                    <div class="flex items-center gap-2.5">
                        <span
                            class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-3 py-2 rounded-xl hidden md:inline font-mono">
                            Chuẩn ảnh: 1920 &times; 600 px
                        </span>
                        <button type="button" wire:click="openForm('banner')"
                            class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-2 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-plus"></i>
                            <span>Thêm Banner Mới</span>
                        </button>
                    </div>
                </div>

                <div class="w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                        <thead>
                            <tr
                                class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                                <th class="py-3 px-4 pl-5">Hình Ảnh</th>
                                <th class="py-3 px-4">Tiêu Đề Banner</th>
                                <th class="py-3 px-4">Mô Tả Phụ</th>
                                <th class="py-3 px-4">Nút Bấm</th>
                                <th class="py-3 px-4 font-mono">Đường Dẫn Liên Kết</th>
                                <th class="py-3 px-4 text-center">Thứ Tự</th>
                                <th class="py-3 px-4 text-center">Trạng Thái</th>
                                <th class="py-3 px-4 pr-6 text-right">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium" id="banners-tbody">
                            @forelse ($banners as $item)
                                <tr wire:key="banner-row-{{ $item->id }}">
                                    <td class="px-4 py-3">
                                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}"
                                            class="h-14 w-36 rounded-lg object-cover">
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $item->title }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ \Illuminate\Support\Str::limit($item->description, 70) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $item->button_text }}
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $item->link }}
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        {{ $item->sort_order }}
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <div wire:key="banner-status-{{ $item->id }}" x-data="{ enabled: @js($item->status === 'active') }"
                                            x-on:banner-status-updated.window="
            if (Number($event.detail.id) === {{ $item->id }}) {
                enabled = $event.detail.status === 'active';
            }
        "
                                            class="inline-flex items-center">
                                            <button type="button" role="switch"
                                                wire:click="requestToggleStatus({{ $item->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="requestToggleStatus,saveAction,cancelAction"
                                                x-bind:disabled="confirming" x-bind:aria-checked="enabled.toString()"
                                                x-bind:title="enabled
                                                    ?
                                                    'Đang hiển thị - Gạt để tắt' :
                                                    'Đang ẩn - Gạt để bật'"
                                                x-bind:class="enabled ? 'bg-brand-600' : 'bg-slate-200'"
                                                aria-label="Trạng thái banner {{ $item->title }}"
                                                class="relative inline-flex h-5 w-9 items-center rounded-full
                transition-colors duration-300
                focus:outline-none focus:ring-2 focus:ring-brand-500
                focus:ring-offset-2 disabled:opacity-60
                disabled:cursor-not-allowed cursor-pointer">
                                                <span aria-hidden="true"
                                                    x-bind:class="enabled
                                                        ?
                                                        'translate-x-[18px] border-white' :
                                                        'translate-x-0.5 border-slate-300'"
                                                    class="inline-block h-4 w-4 rounded-full border bg-white
                    shadow transition-transform duration-300 ease-in-out"></span>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="py-3 px-4 pr-6 text-right space-x-1.5">
                                        <button type="button" wire:click="openEdit('banner', {{ $item->id }})"
                                            wire:loading.attr="disabled" x-bind:disabled="confirming"
                                            class="p-2 rounded-xl bg-slate-100 text-slate-700
            hover:text-brand-700 hover:bg-brand-50 transition"
                                            title="Chỉnh sửa banner">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button type="button"
                                            wire:click="requestDelete('banner', {{ $item->id }})"
                                            wire:loading.attr="disabled" x-bind:disabled="confirming"
                                            class="p-2 rounded-xl bg-slate-100 text-slate-400
            hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Xóa banner">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                        Chưa có banner.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 2: TRANG TĨNH & CÂU CHUYỆN THƯƠNG HIỆU ================= -->
        <div id="tab-pane-pages" x-cloak x-show="tab === 'pages'" class="space-y-4">
            <div
                class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden space-y-4">
                <div
                    class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900">Các Trang Giới Thiệu & Chính
                            Sách
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Quản lý nội dung bài viết "Câu chuyện thương hiệu",
                            "Chính sách đổi trả", "Quy trình canh tác"</p>
                    </div>

                    <!-- Nút Thêm Trang Mới đưa xuống đây -->
                    <button type="button" wire:click="openForm('page')"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-2 active:scale-95 cursor-pointer self-start sm:self-auto">
                        <i class="fa-solid fa-plus"></i>
                        <span>Thêm Trang Mới</span>
                    </button>
                </div>

                <div class="w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                        <thead>
                            <tr
                                class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                                <th class="py-3.5 px-4 pl-5">Tiêu Đề Trang</th>
                                <th class="py-3.5 px-4 font-mono">Đường Dẫn URL</th>
                                <th class="py-3.5 px-4">Nội Dung Tóm Tắt</th>
                                <th class="py-3.5 px-4 font-mono">Ngày Cập Nhật</th>
                                <th class="py-3.5 px-4 pr-6 text-right">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium" id="pages-tbody">
                            @forelse ($pages as $item)
                                <tr wire:key="page-row-{{ $item->id }}">
                                    <td class="px-4 py-3 font-bold">
                                        {{ $item->title }}
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $item->slug }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags($item->content), ENT_QUOTES, 'UTF-8'), 100) }}
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $item->updated_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>

                                    <td class="py-3.5 px-4 pr-6 text-right">
                                        <button type="button" wire:click="openEdit('page', {{ $item->id }})"
                                            wire:loading.attr="disabled" x-bind:disabled="confirming"
                                            class="p-2 rounded-xl bg-slate-100 text-slate-700
            hover:text-brand-700 hover:bg-brand-50 transition
            font-bold text-xs"
                                            title="Chỉnh sửa nội dung">
                                            <i class="fa-regular fa-pen-to-square mr-1"></i>
                                            Soạn thảo
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        Chưa có trang nội dung.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 3: BỘ SƯU TẬP ẢNH NÔNG TRẠI ================= -->
        <div id="tab-pane-farm_images" x-cloak x-show="tab === 'farm_images'" class="space-y-4">
            <div
                class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft p-5 sm:p-6 space-y-4">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-camera text-brand-700"></i>
                            <span>Thư Viện Ảnh Thực Tế Nông Trại</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Hình ảnh thực địa xuất hiện ở mục "Về Chúng Tôi" trên
                            website</p>
                    </div>

                    <!-- Nút Tải Ảnh Vườn -->
                    <button type="button" wire:click="openForm('farm-image')"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5 self-start sm:self-auto active:scale-95 cursor-pointer">
                        <i class="fa-solid fa-upload"></i>
                        <span>Tải Thêm Ảnh Vườn</span>
                    </button>
                </div>

                <!-- Lưới Ảnh Gallery -->
                <div id="farm-gallery-grid"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                    @forelse ($farmImages as $item)
                        <figure wire:key="farm-image-{{ $item->id }}"
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <img src="{{ $item->display_url }}" alt="{{ $item->caption }}"
                                class="h-44 w-full object-cover">

                            <figcaption class="space-y-1 p-3">
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ $item->caption }}
                                </p>

                                <p class="text-xs text-slate-400">
                                    Thứ tự: {{ $item->sort_order }}
                                </p>
                                <div
                                    class="pt-2 border-t border-slate-200/60
    flex items-center justify-between text-xs">
                                    <button type="button" wire:click="openEdit('farm-image', {{ $item->id }})"
                                        wire:loading.attr="disabled" x-bind:disabled="confirming"
                                        class="text-brand-700 hover:text-brand-800
            font-bold flex items-center gap-1">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        Sửa
                                    </button>

                                    <button type="button"
                                        wire:click="requestDelete('farm-image', {{ $item->id }})"
                                        wire:loading.attr="disabled" x-bind:disabled="confirming"
                                        class="text-slate-400 hover:text-rose-600 transition" title="Xóa ảnh">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </figcaption>
                        </figure>
                    @empty
                        <p class="col-span-full py-12 text-center text-sm text-slate-400">
                            Chưa có ảnh nông trại.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
    <!-- MODAL 1: THÊM / SỬA BANNER TRANG CHỦ -->
    <div id="modal-banner-form" x-cloak x-show="$wire.modal === 'banner'"
        x-on:click.self="if (!confirming) $wire.closeModal()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-7 max-h-[92vh] overflow-y-auto custom-scrollbar border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center text-sm font-bold border border-brand-200">
                        <i class="fa-solid fa-images"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-base" id="modal-banner-title">
                            {{ $editingId ? 'Chỉnh Sửa Banner' : 'Thêm Banner Quảng Cáo Mới' }}</h4>
                        <p class="text-[11px] text-slate-400">Cập nhật vào hệ thống banner trang chủ</p>
                    </div>
                </div>
                <button type="button" x-on:click="if (!confirming) $wire.closeModal()"
                    class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            {{-- Form banner --}}
            <form wire:submit="requestSave('banner')" novalidate class="space-y-3.5 text-xs">
                <fieldset :disabled="confirming" class="space-y-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tiêu đề banner <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="bn-title" required wire:model.live.debounce.300ms="banner.title"
                            placeholder="VD: Nông Sản Sạch Minh Bạch Nguồn Gốc Blockchain"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-brand-600 font-bold">
                        @error('banner.title')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Đoạn mô tả ngắn<span
                                class="text-red-500">*</span></label>
                        <textarea id="bn-desc" required rows="2" wire:model.live.debounce.300ms="banner.description"
                            placeholder="VD: Canh tác tự nhiên tại thung lũng Đạ Ròn, cam kết không hóa chất độc hại..."
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-2.5 outline-none focus:border-brand-600 resize-none font-medium"></textarea>
                        @error('banner.description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Chữ trên nút bấm
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="bn-btn-text" required
                                wire:model.live.debounce.300ms="banner.button_text"
                                placeholder="VD: Khám Phá Ngay, Mua Ngay"
                                class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-brand-600 font-semibold">
                            @error('banner.button_text')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Đường dẫn liên kết
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="bn-link" required
                                wire:model.live.debounce.300ms="banner.link" placeholder="/san-pham"
                                class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono outline-none focus:border-brand-600">
                            @error('banner.link')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Thứ tự hiển thị
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="bn-sort" wire:model.live.debounce.300ms="banner.sort_order"
                                min="1" required
                                class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold outline-none focus:border-brand-600">
                            @error('banner.sort_order')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Trạng thái phát hành
                                <span class="text-red-500">*</span>
                            </label>
                            <select id="bn-status" required wire:model.live="banner.status"
                                class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-semibold outline-none focus:border-brand-600">
                                <option value="active">Hiển thị</option>
                                <option value="inactive">Tạm ẩn</option>
                            </select>
                            @error('banner.status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tải ảnh Banner -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hình ảnh banner (Khuyên dùng: 1920x600
                            px) @if ($editingId === null || !$currentImagePath)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <input type="file" id="bn-file" wire:model="bannerFile"
                            wire:key="banner-file-{{ $fileInputKey }}" accept="image/jpeg,image/png,image/webp"
                            @required($editingId === null || !$currentImagePath)
                            class="w-full text-xs text-slate-500
    file:mr-3 file:py-1.5 file:px-3 file:rounded-xl
    file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">

                        @error('bannerFile')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($imagePreviewUrl && $modal === 'banner')
                            <div id="bn-preview-box" class="mt-2">
                                <img id="bn-preview-img" src="{{ $imagePreviewUrl }}" alt="Ảnh banner"
                                    class="w-full h-28 object-cover rounded-xl
                border border-slate-200 shadow-xs">
                            </div>
                        @endif

                        <span wire:loading wire:target="bannerFile" class="text-xs text-slate-500">
                            Đang tải ảnh tạm...
                        </span>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" x-on:click="if (!confirming) $wire.closeModal()"
                            class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">Hủy</button>
                        <button type="submit" wire:loading.attr="disabled"
                            wire:target="requestSave,saveConfirmed,bannerFile,farmFile"
                            class="bg-brand-700 hover:bg-brand-800 text-white font-bold
        px-5 py-2 rounded-xl shadow-sm transition
        disabled:opacity-50 disabled:cursor-not-allowed">
                            Lưu Banner
                        </button>
                    </div>
                    @error('save')
                        <p class="text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </fieldset>
            </form>
        </div>
    </div>

    <!-- MODAL 2: THÊM / SỬA TRANG TĨNH -->
    <div id="modal-page-form" x-cloak x-show="$wire.modal === 'page'"
        x-on:click.self="if (!confirming) $wire.closeModal()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-2xl shadow-2xl p-5 sm:p-7 max-h-[92vh] overflow-y-auto custom-scrollbar border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-sm font-bold border border-purple-200">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-base" id="modal-page-title">
                            {{ $editingId ? 'Chỉnh Sửa Trang Tĩnh' : 'Thêm Trang Mới' }}
                        </h4>
                        <p class="text-[11px] text-slate-400">Quản trị các trang giới thiệu và chính sách</p>
                    </div>
                </div>
                <button type="button" x-on:click="if (!confirming) $wire.closeModal()"
                    class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            {{-- Form trang --}}
            <form wire:submit="requestSave('page')" novalidate class="space-y-3.5 text-xs">
                <fieldset :disabled="confirming" class="space-y-3.5">
                    <input type="hidden" id="pg-id">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tiêu đề trang <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="pg-title" wire:model.live.debounce.300ms="page.title" required
                                placeholder="VD: Câu Chuyện Thương Hiệu"
                                class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-brand-600 font-bold">
                            @error('page.title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Đường dẫn URL <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="pg-slug" wire:model.live.debounce.300ms="page.slug" required
                                placeholder="cau-chuyen-thuong-hieu"
                                class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2 font-mono outline-none text-slate-600">
                            @error('page.slug')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nội dung chi tiết bài viết <span
                                class="text-red-500">*</span></label>
                        <textarea id="pg-content" wire:model.live.debounce.300ms="page.content" rows="8" required
                            placeholder="Nhập văn bản giới thiệu, cam kết chất lượng, quy định chính sách..."
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl p-3 font-mono text-xs outline-none focus:border-brand-600 focus:bg-white resize-none"></textarea>
                        @error('page.content')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" x-on:click="if (!confirming) $wire.closeModal()"
                            class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">Hủy</button>
                        <button type="submit" wire:loading.attr="disabled"
                            wire:target="requestSave,saveConfirmed,bannerFile,farmFile"
                            class="bg-brand-700 hover:bg-brand-800 text-white font-bold
        px-5 py-2 rounded-xl shadow-sm transition
        disabled:opacity-50 disabled:cursor-not-allowed">
                            Lưu Nội Dung Trang
                        </button>
                    </div>
                    @error('save')
                        <p class="text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </fieldset>
            </form>
        </div>
    </div>

    <!-- MODAL 3: THÊM / SỬA ẢNH NÔNG TRẠI -->
    <div id="modal-farm-image-form" x-cloak x-show="$wire.modal === 'farm-image'"
        x-on:click.self="if (!confirming) $wire.closeModal()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 sm:p-6 border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-camera text-brand-700 text-lg"></i>
                    <h4 class="font-extrabold text-slate-900 text-sm" id="modal-farm-image-title">
                        {{ $editingId ? 'Sửa Ảnh Nông Trại' : 'Tải Ảnh Nông Trại Mới' }}
                        Mới</h4>
                </div>
                <button type="button" x-on:click="if (!confirming) $wire.closeModal()"
                    class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            {{-- Form ảnh nông trại --}}
            <form wire:submit="requestSave('farm-image')" novalidate class="space-y-3.5 text-xs">
                <fieldset :disabled="confirming" class="space-y-3.5">
                    <input type="hidden" id="fi-id">

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chú thích bức ảnh <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="fi-caption" wire:model.live.debounce.300ms="farm.caption" required
                            placeholder="VD: Hệ thống tưới nhỏ giọt công nghệ Israel"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:border-brand-600">
                        @error('farm.caption')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thứ tự sắp xếp
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="fi-sort" wire:model.live.debounce.300ms="farm.sort_order"
                            min="1" required
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3 py-2 font-mono font-bold outline-none focus:border-brand-600">
                        @error('farm.sort_order')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chọn ảnh thực tế hiện trường
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="fi-file" wire:model="farmFile"
                            wire:key="farm-file-{{ $fileInputKey }}" accept="image/jpeg,image/png,image/webp"
                            @required($editingId === null || !$currentImagePath)
                            class="w-full text-xs text-slate-500
    file:mr-3 file:py-1.5 file:px-3 file:rounded-xl
    file:border-0 file:bg-brand-50 file:text-brand-700 cursor-pointer">

                        @error('farmFile')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($imagePreviewUrl && $modal === 'farm-image')
                            <div id="fi-preview-box" class="mt-2">
                                <img id="fi-preview-img" src="{{ $imagePreviewUrl }}" alt="Ảnh nông trại"
                                    class="w-full h-32 object-cover rounded-xl
                border border-slate-200 shadow-xs">
                            </div>
                        @endif

                        <span wire:loading wire:target="farmFile" class="text-xs text-slate-500">
                            Đang tải ảnh tạm...
                        </span>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" x-on:click="if (!confirming) $wire.closeModal()"
                            class="px-4 py-2 rounded-xl border text-slate-600 font-bold hover:bg-slate-50">Hủy</button>
                        <button type="submit" wire:loading.attr="disabled"
                            wire:target="requestSave,saveConfirmed,bannerFile,farmFile"
                            class="bg-brand-700 hover:bg-brand-800 text-white font-bold
        px-5 py-2 rounded-xl shadow-sm transition
        disabled:opacity-50 disabled:cursor-not-allowed">
                            Lưu Ảnh Vườn
                        </button>
                    </div>
                    @error('save')
                        <p class="text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </fieldset>
            </form>
        </div>
    </div>
</div>
@script
    <script>
        let confirming = false;

        function setConfirming(busy) {
            $wire.$el.dispatchEvent(
                new CustomEvent('content-confirm-state', {
                    detail: {
                        busy
                    }
                })
            );
        }

        async function confirmAction(options, save, cancel) {
            if (confirming) return;

            confirming = true;
            setConfirming(true);

            try {
                const result = await Swal.fire({
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#15803d',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    ...options
                });

                if (result.isConfirmed) {
                    await save();
                } else {
                    await cancel();
                }
            } catch (error) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Yêu cầu chưa hoàn tất',
                    text: 'Vui lòng kiểm tra kết nối và thử lại.'
                });
            } finally {
                confirming = false;
                setConfirming(false);
            }
        }

        $wire.on('confirm-content-save', ({
            type,
            editing
        }) => {
            const names = {
                banner: 'banner',
                page: 'trang nội dung',
                'farm-image': 'ảnh nông trại'
            };

            return confirmAction({
                    title: (
                        editing ?
                        'Xác nhận cập nhật ' :
                        'Xác nhận thêm '
                    ) + names[type] + '?'
                },
                () => $wire.saveConfirmed(),
                () => $wire.cancelConfirmation());
        });

        $wire.on('confirm-content-action', ({
            kind,
            name,
            enabling
        }) => {
            return confirmAction({
                    icon: kind === 'delete' ? 'warning' : 'question',

                    title: kind === 'delete' ?
                        'Xóa nội dung này?' : (
                            enabling ?
                            'Bật hiển thị banner?' :
                            'Tạm ẩn banner?'
                        ),

                    text: name,

                    confirmButtonText: kind === 'delete' ?
                        'Đồng ý xóa' : 'Xác nhận',

                    confirmButtonColor: kind === 'delete' ?
                        '#dc2626' : '#15803d'
                },
                () => $wire.saveAction(),
                () => $wire.cancelAction());
        });
    </script>
@endscript
