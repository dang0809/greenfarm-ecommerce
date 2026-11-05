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

        <button onclick="openCreateModal()"
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
                <input type="text" id="search-input" oninput="renderCards()"
                    placeholder="Tìm theo tên bài test, mã code..."
                    class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
            </div>

            <div class="flex flex-wrap items-center gap-3.5 sm:gap-6 text-xs sm:text-sm font-bold text-gray-600">
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-brand-700 transition">
                    <input type="radio" name="filter_status" value="all" checked onchange="renderCards()"
                        class="accent-brand-700">
                    <span>Tất cả</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-brand-700 transition">
                    <input type="radio" name="filter_status" value="active" onchange="renderCards()"
                        class="accent-brand-700">
                    <span class="text-brand-800">Còn hiệu lực</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-red-600 transition">
                    <input type="radio" name="filter_status" value="expired" onchange="renderCards()"
                        class="accent-red-600">
                    <span class="text-red-700">Hết hiệu lực</span>
                </label>
            </div>
        </div>

        <!-- DANH SÁCH CHỨNG CHỈ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 pb-12" id="cert-cards-container">

        </div>

    </div>

    <!-- MODAL 1: FORM ĐĂNG / SỬA GIẤY KIỂM ĐỊNH -->
    <div id="modal-form"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl p-5 sm:p-7 max-h-[90vh] overflow-y-auto no-scrollbar border border-gray-100">
            <div
                class="flex justify-between items-center mb-4 sm:mb-5 border-b border-gray-100 pb-3 sticky top-0 bg-white z-10">
                <div class="min-w-0">
                    <h3 class="text-base sm:text-xl font-extrabold text-gray-900 truncate" id="modal-form-title">Đăng
                        Giấy Kiểm Định Mới</h3>
                    <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5 truncate">Lưu dữ liệu vào bảng
                        `certifications`</p>
                </div>
                <button type="button" onclick="closeModal('modal-form')"
                    class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition-colors shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form onsubmit="handleSaveCert(event)" class="space-y-3.5 sm:space-y-4 text-xs sm:text-sm">
                <input type="hidden" id="cert-id">

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Tên chuẩn / Bài test (`name`) <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="cert-name" required placeholder="VD: VietGAP Trồng Trọt, Test mẫu đất T10..."
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-semibold text-xs sm:text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Mã quản lý (`code`) <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="cert-code" required placeholder="VD: vietgap, soil-test-2026..."
                            class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-mono text-xs uppercase">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Trạng thái (`status`) <span
                                class="text-red-500">*</span></label>
                        <select id="cert-status" required
                            class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-semibold text-xs cursor-pointer">
                            <option value="active">Còn hiệu lực</option>
                            <option value="expired">Hết hiệu lực</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Ngày cấp / test (`issued_date`) <span
                                class="text-red-500">*</span></label>
                        <input type="date" id="cert-issued" required
                            class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-mono text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Ngày hết hạn (`expiry_date`)</label>
                        <input type="date" id="cert-expiry"
                            class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl px-3.5 py-2 sm:py-2.5 outline-none focus:border-brand-500 font-mono text-xs">
                        <span class="text-[10px] text-gray-400 mt-0.5 block italic">Bỏ trống nếu vô thời hạn</span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Chi tiết / Tóm tắt kết quả (`description`) <span
                            class="text-red-500">*</span></label>
                    <textarea id="cert-desc" rows="3" required
                        placeholder="Ghi nhận tóm tắt chỉ số kiểm định, hàm lượng kim loại nặng..."
                        class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl sm:rounded-2xl p-3 outline-none focus:border-brand-500 text-xs font-medium resize-none"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Bản scan JPG/PDF kết quả (`image`) <span
                            class="text-red-500">*</span></label>
                    <input type="file" id="cert-file" accept="image/*" onchange="previewUploadImage(this)"
                        class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 sm:file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                    <div id="upload-preview-box" class="mt-2.5 hidden">
                        <img id="upload-preview-img" src=""
                            class="w-full h-32 sm:h-40 object-cover rounded-xl border border-gray-200 shadow-xs">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl shadow-md transition active:scale-95 text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Lưu & Công Bố Giấy Kiểm Định</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: XEM BẢN SCAN PHÓNG TO -->
    <div id="modal-scan"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-xs hidden p-3 sm:p-4">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-2xl shadow-2xl p-4 sm:p-6 border border-gray-100 max-h-[92vh] flex flex-col">
            <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-3 sm:mb-4 shrink-0">
                <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900 truncate" id="scan-title">Tên Giấy Tờ
                    </h3>
                    <p class="text-xs text-brand-700 font-mono font-bold truncate" id="scan-code">MÃ QUẢN LÝ: ---</p>
                </div>
                <button type="button" onclick="closeModal('modal-scan')"
                    class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition-colors shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div
                class="flex-1 overflow-y-auto rounded-xl sm:rounded-2xl border border-gray-200 bg-gray-50 p-2 text-center custom-scrollbar">
                <img id="scan-img" src="" class="max-w-full h-auto mx-auto rounded-lg sm:rounded-xl shadow-xs">
            </div>

            <div
                class="pt-3 sm:pt-4 border-t border-gray-100 mt-3 sm:mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs text-gray-500 shrink-0">
                <p id="scan-dates" class="font-mono">Ngày cấp: -- | Hết hạn: --</p>
                <button onclick="closeModal('modal-scan')"
                    class="w-full sm:w-auto px-5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold transition cursor-pointer">Đóng</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const DB = {
        certifications: [{
                id: 1,
                name: 'Chứng Nhận VietGAP Trồng Trọt',
                code: 'vietgap',
                image: 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&q=80&w=800',
                description: 'Quy trình sản xuất nông nghiệp tốt kiểm soát an toàn từ đất, nước và phân bón. Không dư lượng thuốc bảo vệ thực vật.',
                issued_date: '2026-01-15',
                expiry_date: '2026-12-31',
                status: 'active'
            },
            {
                id: 2,
                name: 'Kiểm Định Nguồn Nước Ngầm Quý 3',
                code: 'water-test-q3',
                image: 'https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&q=80&w=800',
                description: 'Phân tích Eurofins: không tồn dư kim loại nặng (Chì, Asen, Cadmium), đạt chuẩn nước tưới nông nghiệp công nghệ cao.',
                issued_date: '2026-07-20',
                expiry_date: '2026-10-20',
                status: 'active'
            },
            {
                id: 3,
                name: 'Test Mẫu Đất Canh Tác T10',
                code: 'soil-test-2026',
                image: 'https://images.unsplash.com/photo-1606326608606-aa0b62935f2b?auto=format&fit=crop&q=80&w=800',
                description: 'Độ pH 6.5, độ phì hữu cơ 4.2%, vi sinh vật cố định đạm phát triển mạnh. Không nhiễm clo hữu cơ.',
                issued_date: '2026-03-10',
                expiry_date: '2026-09-10',
                status: 'expired'
            },
            {
                id: 4,
                name: 'Tiêu Chuẩn Organic 100% Hữu Cơ',
                code: 'usda-organic',
                image: 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&q=80&w=800',
                description: 'Chứng nhận tuyệt đối không phân bón hóa học tổng hợp, không sử dụng giống biến đổi gen (Non-GMO).',
                issued_date: '2026-02-01',
                expiry_date: null,
                status: 'active'
            }
        ]
    };

    function renderCards() {
        const container = document.getElementById('cert-cards-container');
        const searchInput = document.getElementById('search-input');
        if (!container || !searchInput) return;

        const search = searchInput.value.toLowerCase().trim();
        const checkedFilter = document.querySelector('input[name="filter_status"]:checked');
        const filterStatus = checkedFilter ? checkedFilter.value : 'all';

        let list = [...DB.certifications].sort((a, b) => b.id - a.id);

        if (search) {
            list = list.filter(c => c.name.toLowerCase().includes(search) || c.code.toLowerCase().includes(search));
        }
        if (filterStatus !== 'all') {
            list = list.filter(c => c.status === filterStatus);
        }

        if (list.length === 0) {
            container.innerHTML = `
                    <div class="col-span-full py-12 sm:py-16 text-center bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft">
                        <i class="fa-regular fa-folder-open text-3xl sm:text-4xl text-gray-300 mb-3"></i>
                        <p class="text-sm sm:text-base font-bold text-gray-700">Không tìm thấy giấy tờ kiểm định nào</p>
                        <p class="text-xs text-gray-400 mt-1">Vui lòng thay đổi bộ lọc hoặc bấm nút đăng tài liệu mới.</p>
                    </div>
                `;
            return;
        }

        container.innerHTML = list.map(c => {
            const isActive = c.status === 'active';
            return `
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft hover:shadow-lg transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                        <div class="relative h-44 sm:h-52 bg-gray-100 overflow-hidden cursor-pointer" onclick="openScanPreview(${c.id})">
                            <img src="${c.image}" alt="${c.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/25 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                                <span class="bg-white/95 text-gray-900 font-bold px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl sm:rounded-2xl text-xs shadow-md flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
                                    <i class="fa-solid fa-magnifying-glass-plus text-brand-700"></i> Phóng to bản scan
                                </span>
                            </div>
                            <div class="absolute top-3 right-3">
                                <span class="text-[10px] sm:text-xs font-bold px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full shadow-sm border ${isActive ? 'bg-brand-100 text-brand-800 border-brand-200' : 'bg-red-100 text-red-700 border-red-200'}">
                                    ${isActive ? '<i class="fa-solid fa-circle-check text-[10px] mr-1"></i>Còn hiệu lực' : '<i class="fa-solid fa-clock-rotate-left text-[10px] mr-1"></i>Hết hiệu lực'}
                                </span>
                            </div>
                        </div>
                        <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-3">
                            <div>
                                <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-lg inline-block mb-1.5">
                                    ${c.code}
                                </span>
                                <h3 class="font-extrabold text-sm sm:text-base text-gray-900 group-hover:text-brand-700 transition-colors leading-snug">
                                    ${c.name}
                                </h3>
                                <p class="text-xs text-gray-600 leading-relaxed mt-2 line-clamp-3">
                                    ${c.description}
                                </p>
                            </div>
                            <div class="pt-3 border-t border-gray-100 text-xs font-mono text-gray-500 space-y-1">
                                <div class="flex justify-between">
                                    <span>Ngày cấp:</span>
                                    <b class="text-gray-800">${c.issued_date}</b>
                                </div>
                                <div class="flex justify-between">
                                    <span>Hết hạn:</span>
                                    <b class="${isActive ? 'text-gray-800' : 'text-red-600'}">${c.expiry_date || 'Vô thời hạn'}</b>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 pb-4 sm:px-5 sm:pb-5 pt-1 flex gap-2">
                            <button onclick="openEditModal(${c.id})" class="flex-1 bg-brand-50 hover:bg-brand-100 text-brand-800 font-bold py-2 sm:py-2.5 rounded-xl sm:rounded-2xl text-xs transition flex items-center justify-center gap-1.5 border border-brand-200 active:scale-95 cursor-pointer">
                                <i class="fa-regular fa-pen-to-square"></i> Cập nhật
                            </button>
                            <button onclick="deleteCert(${c.id})" class="p-2 sm:p-2.5 bg-gray-50 hover:bg-red-50 hover:text-red-600 text-gray-400 rounded-xl sm:rounded-2xl text-xs transition border border-gray-200 active:scale-95 cursor-pointer" title="Xóa">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                `;
        }).join('');
    }

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function openCreateModal() {
        document.getElementById('modal-form-title').textContent = 'Đăng Giấy Kiểm Định Mới';
        document.getElementById('cert-id').value = '';
        document.getElementById('cert-name').value = '';
        document.getElementById('cert-code').value = '';
        document.getElementById('cert-issued').value = new Date().toISOString().slice(0, 10);
        document.getElementById('cert-expiry').value = '';
        document.getElementById('cert-desc').value = '';
        document.getElementById('cert-status').value = 'active';
        document.getElementById('cert-file').value = '';
        document.getElementById('cert-file').required = true;
        document.getElementById('upload-preview-box').classList.add('hidden');
        openModal('modal-form');
    }

    function openEditModal(id) {
        const cert = DB.certifications.find(c => c.id === id);
        if (!cert) return;

        document.getElementById('modal-form-title').textContent = 'Cập Nhật Giấy Kiểm Định';
        document.getElementById('cert-id').value = cert.id;
        document.getElementById('cert-name').value = cert.name;
        document.getElementById('cert-code').value = cert.code;
        document.getElementById('cert-issued').value = cert.issued_date;
        document.getElementById('cert-expiry').value = cert.expiry_date || '';
        document.getElementById('cert-desc').value = cert.description;
        document.getElementById('cert-status').value = cert.status;

        document.getElementById('cert-file').required = false;
        document.getElementById('upload-preview-img').src = cert.image;
        document.getElementById('upload-preview-box').classList.remove('hidden');

        openModal('modal-form');
    }

    function previewUploadImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('upload-preview-img').src = e.target.result;
                document.getElementById('upload-preview-box').classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openScanPreview(id) {
        const cert = DB.certifications.find(c => c.id === id);
        if (!cert) return;

        document.getElementById('scan-title').textContent = cert.name;
        document.getElementById('scan-code').textContent = `MÃ QUẢN LÝ: ${cert.code}`;
        document.getElementById('scan-img').src = cert.image;
        document.getElementById('scan-dates').textContent =
            `Ngày cấp: ${cert.issued_date} | Hạn hiệu lực: ${cert.expiry_date || 'Vô thời hạn'}`;
        openModal('modal-scan');
    }

    function handleSaveCert(e) {
        e.preventDefault();
        const idVal = document.getElementById('cert-id').value;
        const name = document.getElementById('cert-name').value.trim();
        const code = document.getElementById('cert-code').value.trim();
        const issued = document.getElementById('cert-issued').value;
        const expiry = document.getElementById('cert-expiry').value || null;
        const desc = document.getElementById('cert-desc').value.trim();
        const status = document.getElementById('cert-status').value;
        const previewImgEl = document.getElementById('upload-preview-img');

        if (idVal) {
            const cert = DB.certifications.find(c => c.id === parseInt(idVal));
            cert.name = name;
            cert.code = code;
            cert.issued_date = issued;
            cert.expiry_date = expiry;
            cert.description = desc;
            cert.status = status;
            if (!document.getElementById('upload-preview-box').classList.contains('hidden')) {
                cert.image = previewImgEl.src;
            }
        } else {
            const newId = DB.certifications.length > 0 ? Math.max(...DB.certifications.map(c => c.id)) + 1 : 1;
            DB.certifications.unshift({
                id: newId,
                name: name,
                code: code,
                image: previewImgEl.src ||
                    'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&q=80&w=800',
                description: desc,
                issued_date: issued,
                expiry_date: expiry,
                status: status
            });
        }

        closeModal('modal-form');
        renderCards();
        Swal.fire({
            icon: 'success',
            title: 'Đã lưu giấy kiểm định!',
            confirmButtonColor: '#15803d',
            timer: 1500,
            showConfirmButton: false
        });
    }

    function deleteCert(id) {
        const cert = DB.certifications.find(c => c.id === id);
        Swal.fire({
            title: 'Gỡ bỏ giấy kiểm định?',
            text: `Bạn có chắc muốn xóa bản ghi "${cert.code} - ${cert.name}" khỏi hệ thống?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Đồng ý xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                DB.certifications = DB.certifications.filter(c => c.id !== id);
                renderCards();
                Swal.fire({
                    icon: 'success',
                    title: 'Đã xóa thành công!',
                    confirmButtonColor: '#15803d',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderCards();
    });
    document.addEventListener('livewire:navigated', () => {
        renderCards();
    });
    </script>
    @endpush
</div>