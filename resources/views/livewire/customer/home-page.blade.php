<div>
    <!-- 1. BANNER SLIDER -->
    <section class="pt-24 pb-8 lg:pt-32 lg:pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative w-full h-[360px] sm:h-[440px] md:h-[500px] rounded-3xl overflow-hidden shadow-sm group bg-gray-900" id="hero-slider">
            @if(count($banners) > 0)
                @foreach($banners as $index => $banner)
                    @php
                        $bannerImg = str_starts_with($banner->image, 'http') ? $banner->image : asset('storage/' . $banner->image);
                    @endphp
                    <div class="absolute inset-0 slide {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }} transition-opacity duration-700" data-index="{{ $index }}">
                        <img src="{{ $bannerImg }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/45"></div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 sm:px-12 z-10">
                            <div class="max-w-3xl text-white flex flex-col items-center">
                                <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold tracking-tight drop-shadow-md leading-tight mb-3">{{ $banner->title }}</h1>
                                <p class="text-xs sm:text-base md:text-lg text-white/90 drop-shadow font-medium max-w-2xl mb-6 line-clamp-2 sm:line-clamp-none">{{ $banner->description }}</p>
                                <a href="{{ $banner->link ?? route('products.index') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white text-xs sm:text-sm md:text-base font-bold px-7 sm:px-8 py-3 rounded-2xl shadow-lg transition-all hover:scale-105">
                                    <span>{{ $banner->button_text ?? 'Khám phá ngay' }}</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Fallback khi CSDL chưa có Banner -->
                <div class="absolute inset-0 slide opacity-100 z-10 transition-opacity duration-700" data-index="0">
                    <img src="https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?auto=format&fit=crop&q=80&w=1600" alt="Banner Nông Sản 1" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/45"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 sm:px-12 z-10">
                        <div class="max-w-3xl text-white flex flex-col items-center">
                            <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold tracking-tight drop-shadow-md leading-tight mb-3">Nông Sản Sạch Tươi Ngon Thu Hoạch Mỗi Sớm</h1>
                            <p class="text-xs sm:text-base md:text-lg text-white/90 drop-shadow font-medium max-w-2xl mb-6">Xem chi tiết nhật ký gieo trồng, kiểm định chất lượng và thời gian thu hoạch thực tế qua mã QR.</p>
                            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white text-xs sm:text-sm md:text-base font-bold px-7 sm:px-8 py-3 rounded-2xl shadow-lg transition-all hover:scale-105">
                                <span>Khám phá ngay</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="absolute inset-0 slide opacity-0 z-0 transition-opacity duration-700" data-index="1">
                    <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&q=80&w=1600" alt="Banner Nông Sản 2" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/45"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 sm:px-12 z-10">
                        <div class="max-w-3xl text-white flex flex-col items-center">
                            <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold tracking-tight drop-shadow-md leading-tight mb-3">Minh Bạch Nguồn Gốc - Giao Nhanh Tận Bếp</h1>
                            <p class="text-xs sm:text-base md:text-lg text-white/90 drop-shadow font-medium max-w-2xl mb-6">Nông sản xuất kho theo nguyên tắc FEFO, giữ trọn hàm lượng vitamin và độ tươi giòn tự nhiên.</p>
                            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white text-xs sm:text-sm md:text-base font-bold px-7 sm:px-8 py-3 rounded-2xl shadow-lg transition-all hover:scale-105">
                                <span>Xem thực đơn hôm nay</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Nút Prev/Next -->
            <button id="prev-slide" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all z-20"><i class="fa-solid fa-chevron-left text-sm"></i></button>
            <button id="next-slide" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all z-20"><i class="fa-solid fa-chevron-right text-sm"></i></button>

            <!-- Dots Slider -->
            <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex space-x-2 z-20" id="slider-dots">
                @if(count($banners) > 0)
                    @foreach($banners as $index => $banner)
                        <button class="dot w-2.5 h-2.5 rounded-full transition-all duration-300 bg-white/50 {{ $index === 0 ? 'active' : '' }}" data-target="{{ $index }}"></button>
                    @endforeach
                @else
                    <button class="dot w-2.5 h-2.5 rounded-full transition-all duration-300 bg-white/50 active" data-target="0"></button>
                    <button class="dot w-2.5 h-2.5 rounded-full transition-all duration-300 bg-white/50" data-target="1"></button>
                @endif
            </div>
        </div>
    </section>

    <!-- 2. DANH MỤC SẢN PHẨM -->
    <section class="py-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Danh Mục Sản Phẩm</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Khám phá các loại nông sản tươi sạch theo từng nhóm</p>
                </div>
                <div class="flex items-center gap-2">
                    <button id="cat-prev-btn" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-brand-50 hover:text-brand-600 transition-all"><i class="fa-solid fa-chevron-left text-xs"></i></button>
                    <button id="cat-next-btn" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-brand-50 hover:text-brand-600 transition-all"><i class="fa-solid fa-chevron-right text-xs"></i></button>
                </div>
            </div>
            
            <div id="category-scroll-container" class="flex gap-4 md:gap-6 overflow-x-auto no-scrollbar scroll-smooth py-2">
                @forelse($categories as $category)
                    @php
                        $catImage = $category->image 
                            ? (str_starts_with($category->image, 'http') ? $category->image : asset('storage/' . $category->image))
                            : 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=300';
                    @endphp
                    <a href="{{ route('products.index', ['category' => $category->id]) }}" class="group flex flex-col items-center text-center shrink-0 w-[44%] sm:w-[30%] lg:w-[calc((100%-4*1.5rem)/5)]">
                        <div class="w-24 h-24 sm:w-28 sm:h-28 lg:w-32 lg:h-32 rounded-full overflow-hidden mb-3 shadow-sm border border-gray-100 group-hover:border-brand-300 transition-all duration-300">
                            <img src="{{ $catImage }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <h3 class="text-sm md:text-base font-bold text-gray-800 group-hover:text-brand-600 transition-colors">{{ $category->name }}</h3>
                    </a>
                @empty
                    <a href="{{ route('products.index') }}" class="group flex flex-col items-center text-center shrink-0 w-[44%] sm:w-[30%] lg:w-[calc((100%-4*1.5rem)/5)]">
                        <div class="w-24 h-24 sm:w-28 sm:h-28 lg:w-32 lg:h-32 rounded-full overflow-hidden mb-3 shadow-sm border border-gray-100 group-hover:border-brand-300 transition-all duration-300">
                            <img src="https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=300" alt="Rau lá xanh" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <h3 class="text-sm md:text-base font-bold text-gray-800 group-hover:text-brand-600">Rau Lá Xanh</h3>
                    </a>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 3. NÔNG SẢN NỔI BẬT -->
    <section class="py-16 bg-cream">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Nông Sản Nổi Bật</h2>
                    <p class="text-gray-500 text-sm mt-1">Các sản phẩm tươi sạch được khách hàng đặt nhiều nhất</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-brand-600 font-medium transition flex items-center gap-2 text-sm">Xem tất cả <i class="fa-solid fa-arrow-right text-xs"></i></a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @forelse($products as $product)
                    {{-- Gọi Blade Component Card Sản Phẩm --}}
                    <x-customer.product-card :product="$product" />
                @empty
                    <div class="col-span-2 md:col-span-4 py-12 text-center bg-white rounded-3xl border border-dashed border-gray-200">
                        <i class="fa-solid fa-basket-shopping text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-gray-600 font-bold">Chưa có sản phẩm nổi bật nào.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 4. TRUY XUẤT NGUỒN GỐC & QUÉT QR CODE -->
    <section class="py-20 bg-stone-50 border-y border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 bg-brand-100 text-brand-800 px-4 py-1.5 rounded-full font-bold text-xs tracking-wide mb-5 uppercase border border-brand-200">
                    <i class="fa-solid fa-qrcode text-base"></i> Minh Bạch Nguồn Gốc Lô Hàng
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Tra Cứu Nhật Ký Canh Tác Trực Tuyến</h2>
                <p class="text-base text-gray-600 leading-relaxed mb-10 max-w-2xl mx-auto">
                    Mỗi bao bì sản phẩm của Đất Lành đều gắn liền với một mã lô duy nhất. Nhập mã hoặc quét QR để xem thời gian gieo trồng, bón phân và thu hoạch thực tế.
                </p>

                <form action="" method="GET" id="batch-search-form" class="max-w-2xl mx-auto flex flex-col sm:flex-row items-center gap-3">
                    <div class="flex-1 w-full bg-white px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl shadow-sm border border-gray-200 flex items-center focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-100 transition-all">
                        <i class="fa-solid fa-barcode text-gray-400 text-lg mr-3 shrink-0"></i>
                        <input type="text" id="batch-code-input" name="batch" placeholder="Nhập mã lô (Ví dụ: DL-20261015-K1)" class="w-full outline-none text-gray-800 font-mono text-sm uppercase" required>
                        
                        <button type="button" id="start-qr-scan-btn" class="ml-2 px-3 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs flex items-center gap-1.5 shrink-0 transition-colors border border-brand-200" title="Quét mã QR từ camera">
                            <i class="fa-solid fa-camera text-brand-600"></i>
                            <span class="hidden sm:inline">Quét QR</span>
                        </button>
                    </div>

                    <button type="submit" class="w-full sm:w-auto bg-gray-900 text-white px-8 py-3.5 rounded-2xl font-bold hover:bg-brand-600 transition-colors shadow-md whitespace-nowrap">
                        Xác thực ngay
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- MODAL QUÉT CAMERA QR CODE -->
    <div id="qr-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 relative">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-qrcode text-brand-600 text-lg"></i>
                    <h3 class="font-bold text-gray-900 text-base">Quét Mã QR Lô Nông Sản</h3>
                </div>
                <button type="button" id="close-qr-modal-btn" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <p class="text-xs text-gray-500 mb-4 text-center">Hướng camera về phía mã QR in trên tem nhãn gói nông sản</p>

            <div class="overflow-hidden rounded-2xl border-2 border-dashed border-brand-400 bg-black/5 aspect-square relative flex items-center justify-center">
                <div id="qr-reader" class="w-full h-full"></div>
            </div>

            <div id="qr-scan-status" class="mt-3 text-center text-xs font-semibold text-gray-500">
                Đang chuẩn bị camera...
            </div>
        </div>
    </div>

    <!-- 5. CAM KẾT NÔNG TRẠI -->
    <section class="py-8 bg-brand-50/40 border-b border-brand-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-4 p-2">
                    <div class="w-12 h-12 bg-white rounded-2xl border border-brand-200 text-brand-600 flex items-center justify-center text-xl shrink-0 shadow-sm">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm">Bồi Thường & Đổi Trả 24H</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Hỗ trợ hoàn tiền hoặc giao bù nhanh nếu nông sản bị dập nát khi nhận hàng</p>
                    </div>
                </div>

                <div class="flex items-center justify-center md:justify-start gap-4 p-2">
                    <div class="w-12 h-12 bg-white rounded-2xl border border-brand-200 text-brand-600 flex items-center justify-center text-xl shrink-0 shadow-sm">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm">Giao Hỏa Tốc Tính Theo Km</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Tối ưu chi phí vận chuyển trực tiếp từ nông trại đến tận cửa nhà bạn</p>
                    </div>
                </div>

                <div class="flex items-center justify-center md:justify-start gap-4 p-2">
                    <div class="w-12 h-12 bg-white rounded-2xl border border-brand-200 text-brand-600 flex items-center justify-center text-xl shrink-0 shadow-sm">
                        <i class="fa-solid fa-leaf"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm">100% Thu Hoạch Tự Nhiên</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Nông sản sạch cắt mới mỗi sáng sớm, không qua xử lý chất bảo quản</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SCRIPT SLIDER & QUÉT QR CODE -->
    <script>
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        let currentSlide = 0;
        let slideInterval;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.replace('opacity-100', 'opacity-0'));
            dots.forEach(dot => dot.classList.remove('active'));
            if (slides[index]) slides[index].classList.replace('opacity-0', 'opacity-100');
            if (dots[index]) dots[index].classList.add('active');
            currentSlide = index;
        }

        document.getElementById('next-slide')?.addEventListener('click', () => { 
            showSlide((currentSlide + 1) % slides.length); 
            resetInterval(); 
        });

        // FIX LỖI CÚ PHÁP SCRIPT Ở NÚT PREV SLIDE
        document.getElementById('prev-slide')?.addEventListener('click', () => { 
            showSlide((currentSlide - 1 + slides.length) % slides.length); 
            resetInterval(); 
        });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
                resetInterval();
            });
        });

        function resetInterval() {
            clearInterval(slideInterval);
            slideInterval = setInterval(() => { 
                if (slides.length > 0) showSlide((currentSlide + 1) % slides.length); 
            }, 5000);
        }
        resetInterval();

        // Cuộn danh mục
        const catContainer = document.getElementById('category-scroll-container');
        document.getElementById('cat-prev-btn')?.addEventListener('click', () => catContainer.scrollBy({ left: -catContainer.offsetWidth / 2, behavior: 'smooth' }));
        document.getElementById('cat-next-btn')?.addEventListener('click', () => catContainer.scrollBy({ left: catContainer.offsetWidth / 2, behavior: 'smooth' }));

        // HTML5 QR CODE CAMERA LOGIC
        let html5QrCode = null;
        const qrModal = document.getElementById('qr-modal');
        const startQrScanBtn = document.getElementById('start-qr-scan-btn');
        const closeQrModalBtn = document.getElementById('close-qr-modal-btn');
        const batchCodeInput = document.getElementById('batch-code-input');
        const qrStatus = document.getElementById('qr-scan-status');

        function stopQrScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                    html5QrCode = null;
                }).catch(err => console.error(err));
            }
            qrModal.classList.add('hidden');
        }

        startQrScanBtn?.addEventListener('click', () => {
            qrModal.classList.remove('hidden');
            qrStatus.textContent = 'Đang bật camera...';

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }

            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText) => {
                    stopQrScanner();
                    let code = decodedText.trim();
                    if (code.includes('batch=')) {
                        const urlParams = new URLSearchParams(code.split('?')[1]);
                        code = urlParams.get('batch') || code;
                    }
                    batchCodeInput.value = code;
                },
                (errorMessage) => {}
            ).catch(err => {
                qrStatus.textContent = 'Không thể truy cập camera. Vui lòng cấp quyền hoặc nhập mã tay!';
            });
        });

        closeQrModalBtn?.addEventListener('click', stopQrScanner);
        qrModal?.addEventListener('click', (e) => {
            if (e.target === qrModal) stopQrScanner();
        });
    </script>
</div>