<div class="flex flex-1 min-h-0 flex-col overflow-hidden">
    {{-- Header --}}
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
                    Cài Đặt Hệ Thống
                </h1>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12 w-full">
        {{-- Thông tin của nông trại --}}
        <div id="tab-pane-farm" class="w-full space-y-4 sm:space-y-6">
            <div
                class="w-full bg-white p-5 sm:p-8 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft space-y-6">
                <div class="border-b border-slate-100 pb-3.5">
                    <h3 class="font-extrabold text-slate-900 text-sm sm:text-base flex items-center gap-2">
                        <i class="fa-solid fa-store text-brand-700"></i>
                        <span>Thông Tin Thương Hiệu Nông Trại</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Hiển thị đồng bộ trên website, hóa đơn xuất kho và tem nhãn
                        truy xuất nguồn gốc</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 text-xs sm:text-sm">
                    {{-- Tên thương hiệu --}}
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Tên thương hiệu nông trại
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="cfg-farm-name" wire:model="farm_name"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 font-semibold text-slate-900 outline-none focus:border-brand-600 focus:bg-white">

                        @error('farm_name')
                            <p class="text-red-500" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Hotline: khối Email --}}
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Hotline CSKH / Đặt hàng
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="cfg-farm-hotline" wire:model="hotline"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono font-bold text-slate-900 outline-none focus:border-brand-600 focus:bg-white">

                        @error('hotline')
                            <p class="text-red-500" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email  --}}
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Email liên hệ chính thức
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="email" id="cfg-farm-email" wire:model="email"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono text-slate-800 outline-none focus:border-brand-600 focus:bg-white">

                        @error('email')
                            <p class="text-red-500" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Địa chỉ chiếm toàn bộ chiều ngang --}}
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Địa chỉ nông trại thực tế
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="cfg-farm-address" wire:model="farm_address"
                            class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 outline-none focus:border-brand-600 focus:bg-white">

                        @error('farm_address')
                            <p class="text-red-500" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>


                @error('save')
                    <p class="text-red-500" role="alert">{{ $message }}</p>
                @enderror

                {{-- Khối nút Lưu nằm ngoài grid --}}
                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="button" wire:click="requestSave" wire:loading.attr="disabled"
                        wire:target="requestSave,saveConfirmed,cancelSave"
                        class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-7 py-3 rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Lưu Thông Tin Nông Trại</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
    @script
        <script>
            let confirmationOpen = false;

            $wire.on('confirm-setting-save', async () => {
                if (confirmationOpen) return;

                confirmationOpen = true;

                try {
                    const result = await Swal.fire({
                        icon: 'question',
                        title: 'Xác nhận lưu thông tin nông trại?',
                        text: 'Chọn Hủy để tiếp tục chỉnh sửa dữ liệu đang nhập.',
                        showCancelButton: true,
                        confirmButtonText: 'Xác nhận',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#15803d',
                        cancelButtonColor: '#64748b',
                        reverseButtons: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });

                    if (result.isConfirmed) {
                        // Server lưu database rồi phát event swal thành công.
                        await $wire.saveConfirmed();
                    } else {
                        // Giữ nguyên các trường đang nhập.
                        await $wire.cancelSave();
                    }
                } catch (error) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Chưa thể xác nhận kết quả',
                        text: 'Vui lòng kiểm tra kết nối. Nếu đã nhấn Xác nhận, hãy tải lại trang để kiểm tra dữ liệu trước khi thử lại.',
                        confirmButtonColor: '#15803d',
                    });
                } finally {
                    confirmationOpen = false;
                }
            });
        </script>
    @endscript
</div>
