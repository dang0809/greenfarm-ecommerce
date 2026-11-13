<div>
    <!-- Flash Message Thông báo -->
    @if (session()->has('success'))
        <div
            class="fixed top-24 right-5 z-50 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs sm:text-sm font-bold shadow-lg flex items-center justify-between gap-3">
            <span><i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> {{ session('success') }}</span>
            <button type="button" class="text-emerald-600" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif

    <!-- Header -->
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 relative z-10 shrink-0 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()"
                class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0"
                aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">

                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">Hồ
                    Sơ Kiểm Định & Chứng Nhận</h1>
            </div>
        </div>

        <button type="button" wire:click="openCreate"
            class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl transition shadow-md flex items-center gap-2 active:scale-95 shrink-0 whitespace-nowrap cursor-pointer">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span class="hidden sm:inline">Đăng Giấy Mới</span>
            <span class="sm:hidden">Đăng mới</span>
        </button>
    </header>

    <!-- Main -->
    <div
        class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative z-10 space-y-4 sm:space-y-6 custom-scrollbar h-[calc(100dvh-5rem)]">

        <!-- THANH TÌM KIẾM & BỘ LỌC TRẠNG THÁI -->
        <div
            class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
            <div class="relative w-full md:w-80">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                <input type="text" id="search-input" wire:model.live.debounce.300ms="search"
                    placeholder="Tìm theo tên chứng nhận, mã chứng nhận..."
                    class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
            </div>

            <div class="flex flex-wrap items-center gap-3.5 sm:gap-6 text-xs sm:text-sm font-bold text-gray-600">
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-brand-700 transition">
                    <input type="radio" name="filter_status" value="all" wire:model.live="statusFilter"
                        class="accent-brand-700">
                    <span>Tất cả</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-brand-700 transition">
                    <input type="radio" name="filter_status" value="active" wire:model.live="statusFilter"
                        class="accent-brand-700">
                    <span class="text-brand-800">Còn hiệu lực</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-red-600 transition">
                    <input type="radio" name="filter_status" value="expired" wire:model.live="statusFilter"
                        class="accent-red-600">
                    <span class="text-red-700">Hết hiệu lực</span>
                </label>
            </div>
        </div>

        <!-- DANH SÁCH CHỨNG CHỈ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 pb-12" id="cert-cards-container">

            @forelse ($certifications as $cert)
                @php
                    $isActive = $cert->effective_status === 'active';
                @endphp

                <div wire:key="cert-{{ $cert->id }}"
                    class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft hover:shadow-lg transition-all duration-300 flex flex-col justify-between overflow-hidden group">

                    <div wire:click="openScan({{ $cert->id }})"
                        class="relative h-44 sm:h-52 bg-gray-100 overflow-hidden cursor-pointer">

                        @if ($cert->is_pdf)
                            <div class="w-full h-full flex items-center justify-center">
                                <span>
                                    <i class="fa-solid fa-file-pdf"></i>
                                    Bản scan PDF
                                </span>
                            </div>
                        @elseif ($cert->image_url)
                            <img src="{{ $cert->image_url }}" alt="{{ $cert->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                Chưa có bản scan
                            </div>
                        @endif

                        <div
                            class="absolute inset-0 bg-black/25 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                            <span
                                class="bg-white/95 text-gray-900 font-bold px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl sm:rounded-2xl text-xs shadow-md flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
                                <i class="fa-solid fa-magnifying-glass-plus text-brand-700"></i>
                                Phóng to bản scan
                            </span>
                        </div>

                        <div class="absolute top-3 right-3">
                            <span
                                class="text-[10px] sm:text-xs font-bold px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full shadow-sm border {{ $isActive ? 'bg-brand-100 text-brand-800 border-brand-200' : 'bg-red-100 text-red-700 border-red-200' }}">

                                @if ($isActive)
                                    <i class="fa-solid fa-circle-check text-[10px] mr-1"></i>
                                    Còn hiệu lực
                                @else
                                    <i class="fa-solid fa-clock-rotate-left text-[10px] mr-1"></i>
                                    Hết hiệu lực
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <span
                                class="font-mono text-xs font-bold text-brand-700 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-lg inline-block mb-1.5">
                                {{ $cert->code }}
                            </span>

                            <h3
                                class="font-extrabold text-sm sm:text-base text-gray-900 group-hover:text-brand-700 transition-colors leading-snug">
                                {{ $cert->name }}
                            </h3>

                            <p class="text-xs text-gray-600 leading-relaxed mt-2 line-clamp-3">
                                {{ $cert->description }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-gray-100 text-xs font-mono text-gray-500 space-y-1">
                            <div class="flex justify-between">
                                <span>Ngày cấp:</span>

                                <b class="text-gray-800">
                                    {{ $cert->issued_date?->format('d/m/Y') ?? '—' }}
                                </b>
                            </div>

                            <div class="flex justify-between">
                                <span>Hết hạn:</span>

                                <b class="{{ $isActive ? 'text-gray-800' : 'text-red-600' }}">
                                    {{ $cert->expiry_date?->format('d/m/Y') ?? 'Vô thời hạn' }}
                                </b>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 pb-4 sm:px-5 sm:pb-5 pt-1 flex gap-2">
                        <button type="button" wire:click="openEdit({{ $cert->id }})" wire:loading.attr="disabled"
                            class="flex-1 bg-brand-50 hover:bg-brand-100 text-brand-800 font-bold py-2 sm:py-2.5 rounded-xl sm:rounded-2xl text-xs transition flex items-center justify-center gap-1.5 border border-brand-200 active:scale-95 cursor-pointer">

                            <i class="fa-regular fa-pen-to-square"></i>
                            Cập nhật
                        </button>

                        <button type="button" wire:click="requestDelete({{ $cert->id }})"
                            wire:loading.attr="disabled"
                            class="p-2 sm:p-2.5 bg-gray-50 hover:bg-red-50 hover:text-red-600 text-gray-400 rounded-xl sm:rounded-2xl text-xs transition border border-gray-200 active:scale-95 cursor-pointer"
                            title="Xóa">

                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full py-12 sm:py-16 text-center bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                    <i class="fa-regular fa-folder-open text-3xl sm:text-4xl text-gray-300 mb-3"></i>

                    <p class="text-sm sm:text-base font-bold text-gray-700">
                        Không tìm thấy giấy tờ kiểm định nào
                    </p>

                    <p class="text-xs text-gray-400 mt-1">
                        Vui lòng thay đổi bộ lọc hoặc bổ sung giấy kiểm định mới.
                    </p>
                </div>
            @endforelse
        </div>

    </div>

    <!-- MODAL 1: FORM ĐĂNG / SỬA GIẤY KIỂM ĐỊNH -->
    @if ($showForm)
        <div id="modal-form"
            wire:key="certification-form-{{ $editingId !== null ? 'Cập Nhật Giấy Kiểm Định' : 'Đăng Giấy Kiểm Định Mới' }}"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
            <div
                class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-7 max-h-[90vh] overflow-y-auto no-scrollbar border border-gray-100">
                <div
                    class="flex justify-between items-center mb-4 sm:mb-5 border-b border-gray-100 pb-3 sticky top-0 bg-white z-10">
                    <div class="min-w-0">
                        <h3 id="modal-form-title" class="text-base sm:text-xl font-extrabold text-gray-900 truncate">
                            {{ $editingId !== null ? 'Cập nhật giấy kiểm định' : 'Đăng giấy kiểm định mới' }}
                        </h3>
                    </div>
                    <button type="button" wire:click="closeForm" wire:loading.attr="disabled"
                        wire:target="requestSave,save,newFile"
                        class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition-colors shrink-0 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit="requestSave" novalidate class="space-y-3.5 sm:space-y-4 text-xs sm:text-sm">
                    {{-- Lỗi chung khi lưu file hoặc database --}}
                    @error('form')
                        <div role="alert"
                            class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-semibold text-red-700">
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- Tên chứng nhận --}}
                    <div>
                        <label for="cert-name" class="block font-bold text-gray-700 mb-1">
                            Tên chứng nhận chuẩn
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="cert-name" wire:model="name" maxlength="255" aria-required="true"
                            placeholder="VD: VietGAP Trồng Trọt, Test mẫu đất T10..."
                            class="w-full bg-[#FAFAF8] border @error('name') border-red-400 @else border-gray-200 @enderror rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-semibold text-xs sm:text-sm">

                        @error('name')
                            <p role="alert" class="mt-1 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Mã chứng nhận --}}
                        <div>
                            <label for="cert-code" class="block font-bold text-gray-700 mb-1">
                                Mã chứng nhận
                                <span class="text-red-500">*</span>
                            </label>

                            <input type="text" id="cert-code" wire:model="code" maxlength="50"
                                aria-required="true" placeholder="VD: vietgap, soil-test-2026..."
                                class="w-full bg-[#FAFAF8] border @error('code') border-red-400 @else border-gray-200 @enderror rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-mono text-xs uppercase">

                            @error('code')
                                <p role="alert" class="mt-1 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Trạng thái --}}
                        <div>
                            <label for="cert-status" class="block font-bold text-gray-700 mb-1">
                                Trạng thái
                                <span class="text-red-500">*</span>
                            </label>

                            <select id="cert-status" wire:model="status" aria-required="true"
                                class="w-full bg-[#FAFAF8] border @error('status') border-red-400 @else border-gray-200 @enderror rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-semibold text-xs cursor-pointer">
                                <option value="active">Còn hiệu lực</option>
                                <option value="expired">Hết hiệu lực</option>
                            </select>

                            @error('status')
                                <p role="alert" class="mt-1 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Ngày cấp --}}
                        <div>
                            <label for="cert-issued" class="block font-bold text-gray-700 mb-1">
                                Ngày cấp
                                <span class="text-red-500">*</span>
                            </label>

                            <input type="date" id="cert-issued" wire:model="issuedDate" aria-required="true"
                                class="w-full bg-[#FAFAF8] border @error('issuedDate') border-red-400 @else border-gray-200 @enderror rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-mono text-xs">

                            @error('issuedDate')
                                <p role="alert" class="mt-1 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Ngày hết hạn --}}
                        <div>
                            <label for="cert-expiry" class="block font-bold text-gray-700 mb-1">
                                Ngày hết hạn
                            </label>

                            <input type="date" id="cert-expiry" wire:model="expiryDate"
                                class="w-full bg-[#FAFAF8] border @error('expiryDate') border-red-400 @else border-gray-200 @enderror rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-mono text-xs">

                            <span class="text-[10px] text-gray-400 mt-0.5 block italic">
                                Bỏ trống nếu vô thời hạn
                            </span>

                            @error('expiryDate')
                                <p role="alert" class="mt-1 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Chi tiết --}}
                    <div>
                        <label for="cert-desc" class="block font-bold text-gray-700 mb-1">
                            Chi tiết
                            <span class="text-red-500">*</span>
                        </label>

                        <textarea id="cert-desc" wire:model="description" maxlength="10000" rows="3" aria-required="true"
                            placeholder="Ghi nhận tóm tắt chỉ số kiểm định, hàm lượng kim loại nặng..."
                            class="w-full bg-[#FAFAF8] border @error('description') border-red-400 @else border-gray-200 @enderror rounded-xl sm:rounded-2xl p-3 outline-none focus:border-brand-500 text-xs font-medium resize-none"></textarea>

                        @error('description')
                            <p role="alert" class="mt-1 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Bản scan --}}
                    <div>
                        <label for="cert-file" class="block font-bold text-gray-700 mb-1">
                            Bản scan JPG, JPEG, PNG, WEBP hoặc PDF

                            @if ($currentFileUrl === null)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <input type="file" id="cert-file" wire:model="newFile"
                            wire:key="certification-file-{{ $fileInputKey }}" accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 sm:file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">

                        @error('newFile')
                            <p role="alert" class="mt-1 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-1 text-[10px] text-gray-400">
                            Dung lượng tối đa 5 MB.
                            @if ($currentFileUrl !== null)
                                Không chọn tệp mới nếu muốn giữ bản scan hiện tại.
                            @endif
                        </p>

                        <p wire:loading wire:target="newFile" class="mt-1 text-xs text-brand-700">
                            Đang tải bản scan...
                        </p>

                        @if ($uploadFileName !== null)
                            <div class="mt-2.5">
                                @if ($uploadPreviewUrl !== null)
                                    <img src="{{ $uploadPreviewUrl }}" alt="Bản scan mới"
                                        class="w-full h-32 sm:h-40 object-cover rounded-xl border border-gray-200 shadow-xs">
                                @else
                                    <p class="text-xs text-gray-600">
                                        <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                                        Tệp đã chọn: {{ $uploadFileName }}
                                    </p>
                                @endif
                            </div>
                        @elseif ($currentFileUrl !== null)
                            <div class="mt-2.5">
                                @if ($currentIsPdf)
                                    <a href="{{ $currentFileUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="text-brand-700 font-semibold">
                                        <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                                        Xem bản PDF hiện tại
                                    </a>
                                @else
                                    <img src="{{ $currentFileUrl }}" alt="Bản scan hiện tại"
                                        class="w-full h-32 sm:h-40 object-cover rounded-xl border border-gray-200 shadow-xs">
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="pt-2">
                        <button type="submit" wire:loading.attr="disabled" wire:target="requestSave,save,newFile"
                            class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl shadow-md transition active:scale-95 text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-wait">
                            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>

                            <span wire:loading.remove wire:target="requestSave,save,newFile">
                                {{ $editingId !== null ? 'Lưu cập nhật giấy kiểm định' : 'Lưu & Công bố giấy kiểm định' }}
                            </span>

                            <span wire:loading wire:target="requestSave,save,newFile">
                                Đang xử lý...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: XEM BẢN SCAN PHÓNG TO -->
    @if ($scanCertification)
        <div id="modal-scan" wire:key="certification-scan-{{ $scanCertification->id }}"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/80 backdrop-blur-xs p-3 sm:p-4">

            <div
                class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-2xl
               max-h-[92dvh] overflow-hidden flex flex-col
               shadow-2xl border border-gray-100">

                <!-- Header của modal -->
                <div
                    class="shrink-0 flex items-start justify-between gap-3
                   border-b border-gray-100 p-4 sm:p-6">

                    <div class="min-w-0 flex-1">
                        <h3 id="scan-title" class="text-base sm:text-lg font-extrabold text-gray-900 break-words">

                            {{ $scanCertification->name }}
                        </h3>

                        <p id="scan-code" class="text-xs text-brand-700 font-mono font-bold mt-1 break-all">

                            MÃ QUẢN LÝ: {{ $scanCertification->code }}
                        </p>
                    </div>

                    <button type="button" wire:click="closeScan"
                        class="w-8 h-8 rounded-full bg-gray-100 text-gray-500
                       hover:bg-gray-200 flex items-center justify-center
                       transition-colors shrink-0 cursor-pointer"
                        aria-label="Đóng bản scan">

                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Hiển thị bản scan -->
                <div
                    class="flex-1 min-h-0 overflow-y-auto
                   rounded-xl sm:rounded-2xl bg-gray-50
                   p-3 sm:p-5 text-center custom-scrollbar">

                    @if ($scanCertification->image_url)
                        @if ($scanCertification->is_pdf)
                            <div class="flex flex-col items-center gap-3">
                                <object data="{{ $scanCertification->image_url }}" type="application/pdf"
                                    class="w-full h-[55vh] sm:h-[60vh] min-h-[300px]
                                   rounded-xl border border-gray-200 bg-white">

                                    <p class="text-xs sm:text-sm text-gray-500 p-4">
                                        Trình duyệt không thể hiển thị PDF trực tiếp.
                                    </p>
                                </object>

                                <a href="{{ $scanCertification->image_url }}" target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center gap-2
                                   px-4 py-2 rounded-xl bg-brand-50
                                   text-brand-700 hover:bg-brand-100
                                   text-xs font-bold">

                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    Mở PDF trong tab mới
                                </a>
                            </div>
                        @else
                            <div class="min-h-full flex items-center justify-center">
                                <img id="scan-img" src="{{ $scanCertification->image_url }}"
                                    alt="{{ $scanCertification->name }}"
                                    class="max-w-full w-auto h-auto
                                   max-h-[58vh] sm:max-h-[64vh]
                                   object-contain rounded-xl shadow-md">
                            </div>
                        @endif
                    @else
                        <div class="min-h-[240px] flex items-center justify-center">
                            <p class="text-xs sm:text-sm text-gray-500">
                                Giấy kiểm định chưa có bản scan.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Footer của modal -->
                <div
                    class="shrink-0 border-t border-gray-100
                   p-4 sm:p-5 flex flex-col items-stretch gap-3
                   text-xs text-gray-500">

                    <p id="scan-dates" class="font-mono text-center leading-relaxed">

                        Ngày cấp:
                        {{ $scanCertification->issued_date?->format('d/m/Y') ?? '—' }}

                        <span class="mx-1">|</span>

                        Hết hạn:
                        {{ $scanCertification->expiry_date?->format('d/m/Y') ?? 'Vô thời hạn' }}
                    </p>

                    <button type="button" wire:click="closeScan"
                        class="w-full px-5 py-2.5 rounded-xl
                       bg-gray-100 hover:bg-gray-200
                       text-gray-700 font-bold transition cursor-pointer">

                        Đóng
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@script
    <script>
        let confirming = false;

        async function runConfirmation(options, action) {
            if (confirming) return;

            confirming = true;

            try {
                const result = await Swal.fire({
                    heightAuto: false,
                    scrollbarPadding: false,
                    showCancelButton: true,
                    cancelButtonText: 'Hủy',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    ...options
                });

                if (result.isConfirmed) {
                    await action();
                }
            } catch (error) {
                // Livewire đã đưa lỗi validation về từng trường.
                if (error?.status === 422 || error?.errors) return;

                await Swal.fire({
                    heightAuto: false,
                    scrollbarPadding: false,
                    icon: 'error',
                    title: 'Yêu cầu chưa hoàn tất',
                    text: 'Vui lòng kiểm tra kết nối và thử lại.',
                    confirmButtonColor: '#15803d'
                });
            } finally {
                confirming = false;
            }
        }

        $wire.on('confirm-certification-save', ({
            editing
        }) => {
            return runConfirmation({
                icon: 'question',

                title: editing ?
                    'Xác nhận cập nhật giấy kiểm định?' : 'Xác nhận thêm giấy kiểm định?',

                text: 'Thông tin và bản scan sẽ được lưu sau khi xác nhận.',
                confirmButtonText: 'Xác nhận',
                confirmButtonColor: '#15803d'
            }, () => $wire.save());
        });

        $wire.on('confirm-certification-delete', ({
            id,
            name
        }) => {
            return runConfirmation({
                icon: 'warning',
                title: 'Gỡ bỏ giấy kiểm định?',
                text: 'Bạn có chắc muốn xóa “' + name + '”?',
                confirmButtonText: 'Đồng ý xóa',
                confirmButtonColor: '#dc2626'
            }, () => $wire.deleteCertification(id));
        });
    </script>
@endscript
