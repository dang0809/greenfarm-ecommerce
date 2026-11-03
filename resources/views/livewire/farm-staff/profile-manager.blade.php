<div>
    <!-- Header  -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 relative z-10 shrink-0 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5 truncate">
                    <span class="text-brand-700">Tài Khoản Nhân Viên</span>
                    <span>/</span>
                    <span class="truncate">Hồ Sơ Kỹ Sư Thực Địa</span>
                </div>
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">Hồ Sơ Kỹ Sư Canh Tác</h1>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-700"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>11/10/2026</span>
            </div>
        </div>
    </header>

    <!-- Main  -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 pb-28 sm:pb-12 relative z-10 custom-scrollbar h-[calc(100dvh-5rem)]">
        <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">
            
            <!-- Avatar & Thẻ định danh -->
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 p-5 sm:p-6 shadow-soft flex flex-col items-center text-center space-y-4">
                <div class="relative group">
                    <img id="preview-avatar" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=250" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-4 border-brand-100 shadow-md">
                    <label for="avatar-input" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-brand-700 hover:bg-brand-800 text-white flex items-center justify-center cursor-pointer shadow transition active:scale-95" title="Đổi ảnh đại diện">
                        <i class="fa-solid fa-camera text-xs"></i>
                    </label>
                    <input type="file" id="avatar-input" accept="image/*" class="hidden" onchange="changeAvatar(event)">
                </div>

                <div class="space-y-1.5">
                    <h2 class="text-lg sm:text-xl font-extrabold text-gray-900" id="card-name">KS. Hoàng Nam</h2>
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-brand-100 text-brand-800 border border-brand-200">
                        <i class="fa-solid fa-circle-check text-[10px]"></i> Đang hoạt động
                    </span>
                </div>

                <div class="w-full border-t border-gray-100 pt-4 space-y-2.5 text-xs text-left">
                    <div class="flex justify-between items-center text-gray-600">
                        <span>Vai trò:</span>
                        <span class="font-bold text-gray-800">Nhân viên Nông trại</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-600">
                        <span>Ngày gia nhập:</span>
                        <span class="font-bold text-gray-800 font-mono">01/01/2026</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-600">
                        <span>Phân khu:</span>
                        <span class="font-bold text-brand-700">Luống 1 - 6 (Đạ Ròn)</span>
                    </div>
                </div>
            </div>

            <!-- Form cập nhật thông tin & Đổi mật khẩu -->
            <div class="lg:col-span-2 bg-white rounded-2xl sm:rounded-3xl border border-gray-100 p-5 sm:p-8 shadow-soft space-y-5 sm:space-y-6">
                <form onsubmit="handleSaveProfile(event)" class="space-y-4 sm:space-y-5 text-xs sm:text-sm">
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <i class="fa-regular fa-user text-brand-700"></i> Thông Tin Tài Khoản
                        </h3>
                    </div>
                    
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                        <input type="text" id="user-name" value="KS. Hoàng Nam" required class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 sm:px-4 py-2.5 outline-none focus:border-brand-500 focus:bg-white font-semibold transition text-xs sm:text-sm">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                        <div>
                            <label class="block font-bold text-gray-500 mb-1">Email đăng nhập</label>
                            <input type="email" value="hoangnam.farm@datlanh.vn" disabled class="w-full bg-gray-100 border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 sm:px-4 py-2.5 text-gray-500 font-mono text-xs cursor-not-allowed">
                            <p class="text-[11px] text-gray-400 mt-1 italic">Liên hệ Quản trị viên để đổi email</p>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Số điện thoại liên hệ <span class="text-red-500">*</span></label>
                            <input type="text" id="user-phone" value="0918889999" required class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 sm:px-4 py-2.5 outline-none focus:border-brand-500 focus:bg-white font-mono font-semibold text-xs sm:text-sm transition">
                        </div>
                    </div>

                    <div class="pt-2">
                        <h3 class="text-sm sm:text-base font-extrabold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-brand-700"></i> Thay Đổi Mật Khẩu
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Mật khẩu mới</label>
                            <input type="password" id="user-new-password" placeholder="Bỏ trống nếu không đổi" minlength="8" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 sm:px-4 py-2.5 outline-none focus:border-brand-500 focus:bg-white text-xs sm:text-sm transition">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                            <input type="password" id="user-confirm-password" placeholder="Nhập lại mật khẩu mới" minlength="8" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 sm:px-4 py-2.5 outline-none focus:border-brand-500 focus:bg-white text-xs sm:text-sm transition">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <button type="submit" class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-8 py-3.5 rounded-xl sm:rounded-2xl text-sm transition shadow-md active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-regular fa-floppy-disk text-base"></i>
                            <span>Lưu Thông Tin</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function changeAvatar(event) {
            const file = event.target.files[0];
            if (file) {
                if (!file.type.startsWith('image/')) {
                    Swal.fire('Lỗi', 'Vui lòng chọn file hình ảnh hợp lệ (PNG, JPG, WEBP)!', 'error');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgUrl = e.target.result;
                    const previewAvatar = document.getElementById('preview-avatar');
                    const sidebarAvatar = document.getElementById('sidebar-avatar');
                    if (previewAvatar) previewAvatar.src = imgUrl;
                    if (sidebarAvatar) sidebarAvatar.src = imgUrl;
                };
                reader.readAsDataURL(file);
            }
        }

        function handleSaveProfile(event) {
            event.preventDefault();
            const nameEl = document.getElementById('user-name');
            const newPassEl = document.getElementById('user-new-password');
            const confirmPassEl = document.getElementById('user-confirm-password');
            if (!nameEl || !newPassEl || !confirmPassEl) return;

            const name = nameEl.value.trim();
            const newPass = newPassEl.value;
            const confirmPass = confirmPassEl.value;

            if (newPass && newPass !== confirmPass) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Xác nhận mật khẩu mới không khớp!',
                    confirmButtonColor: '#15803d'
                });
                return;
            }

            const cardName = document.getElementById('card-name');
            const sidebarName = document.getElementById('sidebar-name');
            if (cardName) cardName.textContent = name;
            if (sidebarName) sidebarName.textContent = name;

            newPassEl.value = '';
            confirmPassEl.value = '';

            Swal.fire({
                icon: 'success',
                title: 'Đã lưu thay đổi!',
                text: 'Hồ sơ tài khoản của bạn đã được cập nhật thành công.',
                confirmButtonColor: '#15803d',
                timer: 1500,
                showConfirmButton: false
            });
        }
    </script>
    @endpush
</div>