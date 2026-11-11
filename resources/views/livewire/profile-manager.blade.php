<div x-data="{ avatarReady: false, uploadingAvatar: false }" x-on:livewire-upload-start="uploadingAvatar = true; avatarReady = false"
    x-on:livewire-upload-finish="uploadingAvatar = false" x-on:livewire-upload-error="uploadingAvatar = false"
    x-on:livewire-upload-cancel="uploadingAvatar = false">
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600"
                aria-label="Mở menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 truncate">
                Hồ Sơ {{ $roleTitle ?? 'Nhân Viên' }}
            </h1>
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

    <div class="overflow-y-auto p-4 sm:p-6 lg:p-8 pb-28 custom-scrollbar h-[calc(100dvh-5rem)]">
        <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- Ảnh và thông tin tài khoản đã lưu -->
            <div
                class="bg-white rounded-3xl border border-gray-100 p-6 shadow-soft flex flex-col items-center text-center space-y-4">
                <div class="relative">
                    @if ($newAvatar && !$errors->has('newAvatar'))
                        <img wire:key="avatar-preview-{{ $newAvatar->getFilename() }}"
                            src="{{ $newAvatar->temporaryUrl() }}" alt="Ảnh đại diện đang chọn" x-init="avatarReady = $el.complete && $el.naturalWidth > 0"
                            x-on:load="avatarReady = true" x-on:error="avatarReady = false"
                            class="w-28 h-28 rounded-full object-cover border-4 border-brand-100">
                    @elseif ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="Ảnh đại diện"
                            class="w-28 h-28 rounded-full object-cover border-4 border-brand-100">
                    @else
                        <div
                            class="w-28 h-28 rounded-full bg-brand-100 text-brand-800 flex items-center justify-center text-3xl font-bold">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <label for="avatar-input" title="Đổi ảnh đại diện"
                        class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-brand-700 text-white flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-camera text-xs"></i>
                    </label>

                    <input type="file" id="avatar-input" x-data="{}"
                        wire:key="avatar-input-{{ $avatarInputKey }}" wire:model="newAvatar"
                        wire:loading.attr="disabled" x-bind:disabled="uploadingAvatar" accept=".jpg,.jpeg,.png,.webp"
                        x-on:change.capture="
                            const input = $event.target;
                            const file = input.files?.[0];

                            if (!file) return;

                            const extension = file.name.split('.').pop().toLowerCase();
                            const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                            const mime = (file.type || '').toLowerCase();

                            let message = '';

                            // Kiểm tra định dạng trước dung lượng.
                            if (
                                !allowedExtensions.includes(extension)
                                || (mime !== '' && !allowedTypes.includes(mime))
                            ) {
                                message = 'Tệp được chọn không phải định dạng ảnh hợp lệ. Chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP.';
                            } else if (file.size > 2 * 1024 * 1024) {
                                message = 'Ảnh đại diện không được vượt quá 2 MB.';
                            }

                            if (message !== '') {
                                // Chặn listener upload của Livewire.
                                $event.preventDefault();
                                $event.stopImmediatePropagation();

                                // Xóa lựa chọn để có thể chọn lại tệp.
                                input.value = '';

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Ảnh không hợp lệ',
                                    text: message,
                                    confirmButtonText: 'Đã hiểu',
                                    confirmButtonColor: '#15803d'
                                });
                            }
                        "
                        class="hidden">
                </div>

                <p class="text-xs text-gray-500">
                    JPG, PNG hoặc WEBP, tối đa 2 MB.
                </p>

                <p wire:loading wire:target="newAvatar" class="text-xs text-brand-700">
                    Đang tải ảnh lên…
                </p>

                @error('newAvatar')
                    <p class="text-xs text-red-600" role="alert">
                        {{ $message }}
                    </p>
                @enderror

                @if ($newAvatar && !$errors->has('newAvatar'))
                    <div wire:loading.remove wire:target="newAvatar">
                        <div class="flex flex-col items-center gap-3">
                            <button type="button" wire:click="cancelAvatar" wire:loading.attr="disabled"
                                x-bind:disabled="uploadingAvatar"
                                class="text-xs font-bold text-red-600 disabled:opacity-50">
                                Hủy ảnh đang chọn
                            </button>

                            <button type="button" wire:click="requestSaveAvatar" wire:loading.attr="disabled"
                                wire:target="requestSaveAvatar,saveAvatar" x-bind:disabled="uploadingAvatar"
                                x-show="avatarReady && !uploadingAvatar" style="display: none"
                                class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-bold px-6 py-2.5 rounded-xl disabled:opacity-50 disabled:cursor-wait">
                                <span wire:loading.remove wire:target="requestSaveAvatar,saveAvatar">
                                    Lưu ảnh
                                </span>

                                <span wire:loading wire:target="requestSaveAvatar,saveAvatar">
                                    <i class="fa-solid fa-spinner fa-spin mr-1"></i>
                                    Đang xử lý…
                                </span>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="space-y-2">
                    <h2 class="text-xl font-extrabold text-gray-900">
                        {{ $user->name }}
                    </h2>

                    <span class="inline-flex text-xs font-bold px-3 py-1 rounded-full bg-brand-100 text-brand-800">
                        {{ $user->status === 'active' ? 'Đang hoạt động' : 'Không hoạt động' }}
                    </span>
                </div>

                <div class="w-full border-t border-gray-100 pt-4 space-y-3 text-xs text-left">
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500">Vai trò:</span>
                        <span class="font-bold text-gray-800">
                            {{ $user->role === 'sales' ? 'Nhân viên Bán hàng' : ($user->role === 'farm_staff' ? 'Nhân viên Nông trại' : $user->role) }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500">Ngày tạo tài khoản:</span>
                        <span class="font-bold font-mono">
                            {{ $user->created_at?->format('d/m/Y') ?? 'Chưa có dữ liệu' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Form cập nhật -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 p-5 sm:p-8 shadow-soft">
                <form wire:submit="requestSaveProfile" novalidate class="space-y-5 text-sm">
                    @error('profile')
                        <div role="alert" class="rounded-xl bg-red-50 text-red-700 p-3">
                            {{ $message }}
                        </div>
                    @enderror

                    <h3 class="font-extrabold text-gray-900 border-b border-gray-100 pb-2">
                        Thông Tin Tài Khoản
                    </h3>

                    <div>
                        <label for="user-name" class="block font-bold text-gray-700 mb-1">
                            Họ và tên <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="user-name" wire:model="name" required maxlength="255"
                            autocomplete="name"
                            class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-brand-500">

                        @error('name')
                            <p class="text-xs text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="user-email" class="block font-bold text-gray-500 mb-1">
                                Email đăng nhập
                            </label>

                            <input type="email" id="user-email" value="{{ $user->email }}" disabled
                                class="w-full bg-gray-100 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-500 cursor-not-allowed">

                            <p class="text-xs text-gray-400 mt-1">
                                Liên hệ Quản trị viên để đổi email.
                            </p>
                        </div>

                        <div>
                            <label for="user-phone" class="block font-bold text-gray-700 mb-1">
                                Số điện thoại
                                <span class="text-red-500">*</span>
                            </label>

                            <input type="tel" id="user-phone" wire:model="phone" required maxlength="10"
                                inputmode="numeric" autocomplete="tel"
                                class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-brand-500">

                            @error('phone')
                                <p class="text-xs text-red-600 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                            wire:target="requestSaveProfile,saveProfile"
                            class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-8 py-3 rounded-xl disabled:opacity-50 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="requestSaveProfile,saveProfile">
                                Lưu thông tin
                            </span>

                            <span wire:loading wire:target="requestSaveProfile,saveProfile">
                                <i class="fa-solid fa-spinner fa-spin mr-1"></i>
                                Đang xử lý…
                            </span>
                        </button>
                    </div>
                </form>

                <form wire:submit="requestSavePassword" autocomplete="off" novalidate class="mt-8 space-y-5 text-sm">

                    <h3 class="font-extrabold text-gray-900 border-b border-gray-100 pb-2 pt-2">
                        Thay Đổi Mật Khẩu
                    </h3>

                    <p class="text-xs text-gray-500">
                        Nhập đầy đủ ba ô để đổi mật khẩu.
                        Mật khẩu mới có 8–72 ký tự,
                        gồm chữ hoa, chữ thường và chữ số.
                    </p>

                    <div>
                        <label for="user-current-password" class="block font-bold text-gray-700 mb-1">
                            Mật khẩu hiện tại
                        </label>

                        <input type="password" id="user-current-password" wire:model="current_password"
                            autocomplete="off" data-lpignore="true"
                            class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-brand-500">

                        @error('current_password')
                            <p class="text-xs text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="user-new-password" class="block font-bold text-gray-700 mb-1">
                                Mật khẩu mới
                            </label>

                            <input type="password" id="user-new-password" wire:model="new_password"
                                autocomplete="off" data-lpignore="true" minlength="8" maxlength="72"
                                class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-brand-500">

                            @error('new_password')
                                <p class="text-xs text-red-600 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="user-confirm-password" class="block font-bold text-gray-700 mb-1">
                                Xác nhận mật khẩu mới
                            </label>

                            <input type="password" id="user-confirm-password" wire:model="new_password_confirmation"
                                autocomplete="off" data-lpignore="true" minlength="8" maxlength="72"
                                class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-brand-500">

                            @error('new_password_confirmation')
                                <p class="text-xs text-red-600 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                            wire:target="requestSavePassword,savePassword"
                            class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-8 py-3 rounded-xl disabled:opacity-50 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="requestSavePassword,savePassword">
                                Cập nhật mật khẩu
                            </span>

                            <span wire:loading wire:target="requestSavePassword,savePassword">
                                <i class="fa-solid fa-spinner fa-spin mr-1"></i>
                                Đang xử lý…
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @script
        <script>
            let confirmationOpen = false;

            async function confirmSave(title, save) {
                if (confirmationOpen) {
                    return;
                }

                confirmationOpen = true;

                try {
                    const result = await Swal.fire({
                        icon: 'question',
                        title,
                        text: 'Chọn Hủy để hủy bỏ thao tác.',
                        showCancelButton: true,
                        confirmButtonText: 'Xác nhận',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#15803d',
                        cancelButtonColor: '#6b7280',
                        reverseButtons: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });

                    if (result.isConfirmed) {
                        await save();
                    } else {
                        await $wire.cancelProfileChanges();
                    }
                } finally {
                    confirmationOpen = false;
                }
            }

            $wire.on('confirm-profile-save', () => confirmSave(
                'Xác nhận lưu thông tin tài khoản?',
                () => $wire.saveProfile()
            ));

            $wire.on('confirm-password-save', () => confirmSave(
                'Xác nhận cập nhật mật khẩu?',
                () => $wire.savePassword()
            ));

            $wire.on('confirm-avatar-save', () => confirmSave(
                'Xác nhận lưu ảnh đại diện?',
                () => $wire.saveAvatar()
            ));
        </script>
    @endscript
</div>
<<<<<<< HEAD:resources/views/livewire/farm-staff/profile-manager.blade.php=======>>>>>>>
    909e51ba9809b76457a6d00dfc53b9da5f5bb86b:resources/views/livewire/profile-manager.blade.php
