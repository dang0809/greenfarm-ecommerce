<div>
    <main class="pt-24 lg:pt-28 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14 pb-16">
        
        

        <!-- 1. BÀI VIẾT TRUY VẤN TỪ BẢNG PAGES -->
        <article class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-10 lg:p-12 shadow-sm">
            <header class="pb-6 border-b border-gray-100 mb-8">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-700 bg-brand-50 border border-brand-200 px-3 py-1 rounded-full uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-leaf text-xs"></i> Câu chuyện thương hiệu
                </span>
                
                <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-3">
                    {{ $page->title ?? 'Câu Chuyện Thương Hiệu Nông Trại Đất Lành' }}
                </h1>

                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <span><i class="fa-regular fa-clock mr-1"></i> Cập nhật: {{ isset($page->created_at) ? \Carbon\Carbon::parse($page->created_at)->format('d/m/Y') : 'Tháng 10/2026' }}</span>
                    <span>•</span>
                    <span><i class="fa-solid fa-location-dot mr-1"></i> Thôn 3, Xã Đạ Ròn, Huyện Đơn Dương, Tỉnh Lâm Đồng</span>
                </div>
            </header>

            <div class="content-body">
                @if ($page && $page->content)
                    {!! $page->content !!}
                @else
                    <blockquote>
                        "Chúng tôi là một nông trại nông nghiệp sạch, với sứ mệnh mang đến những sản phẩm tươi ngon, an toàn và minh bạch đến từng bữa ăn của gia đình Việt. Canh tác tử tế là khởi đầu của một cuộc sống khỏe mạnh."
                    </blockquote>

                    <p>
                        Nằm nép mình bên thung lũng xã Đạ Ròn, huyện Đơn Dương, tỉnh Lâm Đồng ở độ cao hơn 1.000m so với mực nước biển, <strong>Nông trại Đất Lành</strong> được thiên nhiên ban tặng khí hậu quanh năm mát mẻ, đất bazan màu mỡ cùng mạch suối ngầm tự nhiên chảy từ chân rừng thông nguyên sinh. Chúng tôi không xem nông nghiệp là ngành sản xuất công nghiệp đơn thuần, mà là sự tôn trọng đối với đất mẹ và trách nhiệm với sức khỏe thế hệ mai sau.
                    </p>

                    <h2 class="text-xl font-bold text-gray-900 mt-8 mb-3">1. Triết Lý Canh Tác Thuận Tự Nhiên</h2>
                    <p class="mb-4">
                        Canh tác bền vững - Kiến tạo giá trị lâu dài. Mọi luống rau, giàn quả tại Đất Lành đều được chăm sóc bằng sự tử tế và kiến thức khoa học thông qua các nguyên tắc:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 mb-6 text-gray-700">
                        <li><strong>Tôn trọng đất và nguồn nước:</strong> Giữ gìn độ phì nhiêu màu mỡ của đất bằng cách luân canh cây họ đậu và bổ sung mùn trùn quế định kỳ. Bảo vệ nguồn suối ngầm tầng sâu 80m không bị xâm nhiễm hóa chất.</li>
                        <li><strong>Ưu tiên phương pháp canh tác an toàn (Nguyên tắc 5 Không):</strong> Tuyệt đối không phân bón hóa học, không thuốc trừ sâu tổng hợp, không giống biến đổi gen (Non-GMO), không chất kích thích sinh trưởng và không chất bảo quản sau thu hoạch.</li>
                        <li><strong>Theo dõi sát sao quá trình sinh trưởng:</strong> Ứng dụng công nghệ nhà màng Israel và hệ thống tưới nhỏ giọt tự động nhằm kiểm soát nhiệt độ, độ ẩm và sức khỏe cây trồng suốt mùa vụ.</li>
                        <li><strong>Minh bạch nhật ký và nguồn gốc:</strong> Mọi giai đoạn bón mùn, chăm bón và thời gian thu hái thực tế đều được kỹ sư số hóa gán vào mã lô (Batch Code), cho phép người tiêu dùng tra cứu chi tiết qua mã QR.</li>
                    </ul>

                    <h2 class="text-xl font-bold text-gray-900 mt-8 mb-3">2. Hồ Sơ Năng Lực & Nền Tảng Vững Chắc</h2>
                    <p class="mb-4">
                        Đất Lành tự chủ 100% quy trình sản xuất và chuỗi cung ứng khép kín nhằm đảm bảo chất lượng nông sản tươi ngon nhất khi đến tay khách hàng:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 mb-6 text-gray-700">
                        <li><strong>Diện tích canh tác:</strong> 12 hecta đất trồng đạt chuẩn nông nghiệp sạch và nhà màng chống côn trùng công nghệ cao.</li>
                        <li><strong>Nhóm nông sản chính:</strong> Hơn 15 chủng loại nông sản tươi gồm rau ăn lá (cải bó xôi, xà lách mỡ, cải xoăn kale), củ quả theo mùa (cà chua cherry, khoai lang mật), nấm tươi tự nhiên và trái cây nhiệt đới.</li>
                        <li><strong>Quy trình sản xuất 3 giai đoạn:</strong> Kiểm soát chặt chẽ từ khâu Gieo trồng hạt giống hữu cơ &rarr; Thu hoạch thủ công lúc 5:00 sáng &rarr; Sơ chế, đóng gói túi Kraft thoáng khí và bảo quản lạnh.</li>
                        <li><strong>Đóng gói & Giao nhận hỏa tốc:</strong> 100% nông sản được giao nhanh bằng thùng xốp cách nhiệt trong 2H nội thành, giữ trọn độ tươi giòn tự nhiên và bảo vệ rau củ khỏi dập nát.</li>
                    </ul>
                @endif
            </div>
        </article>

        <!-- 2. THƯ VIỆN HÌNH ẢNH TRUY VẤN TỪ BẢNG FARM_IMAGES -->
        <section class="space-y-6">
            <div class="flex items-center justify-between pb-2">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fa-regular fa-image text-brand-600 text-lg"></i>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Thư viện hình ảnh thực tế</h2>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500">Những khoảnh khắc chân thật từ nông trại (Click vào ảnh để phóng to)</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @forelse ($farmImages as $item)
                    @php
                        $imgSrc = (!empty($item->image_url) && str_starts_with($item->image_url, 'http')) 
                            ? $item->image_url 
                            : asset('storage/' . ($item->image_url ?? ''));
                        
                        $fallbackImg = 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=800';
                    @endphp
                    <div class="relative rounded-2xl overflow-hidden shadow-sm aspect-[4/5] group bg-gray-900 cursor-pointer border border-gray-100 hover:shadow-xl transition-all"
                         onclick="openImageLightbox('{{ $imgSrc }}', '{{ addslashes($item->caption ?? 'Hình ảnh nông trại Đất Lành') }}')">
                        <img src="{{ $imgSrc }}" 
                             alt="{{ $item->caption ?? 'Hình ảnh nông trại' }}" 
                             onerror="this.onerror=null; this.src='{{ $fallbackImg }}';"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-90">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent flex flex-col justify-end p-4 text-white">
                            <h3 class="text-xs sm:text-sm font-bold leading-snug drop-shadow-sm line-clamp-2">
                                {{ $item->caption ?? 'Khu vực nông trại' }}
                            </h3>
                        </div>
                    </div>
                @empty
                    @php
                        $defaultAlbums = [
                            ['caption' => 'Hệ thống nhà màng A1 canh tác hữu cơ', 'url' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=800'],
                            ['caption' => 'Hệ thống tưới nhỏ giọt tự động Israel', 'url' => 'https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?w=800'],
                            ['caption' => 'Khu vực thu hoạch & phân loại rau quả', 'url' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800'],
                            ['caption' => 'Quy trình sơ chế & đóng gói túi Kraft', 'url' => 'https://images.unsplash.com/photo-1584467735871-8e85353a8413?w=800'],
                            ['caption' => 'Cánh đồng trồng trọt sinh thái Đất Lành', 'url' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=800'],
                        ];
                    @endphp
                    @foreach ($defaultAlbums as $album)
                        <div class="relative rounded-2xl overflow-hidden shadow-sm aspect-[4/5] group bg-gray-900 cursor-pointer border border-gray-100 hover:shadow-xl transition-all"
                             onclick="openImageLightbox('{{ $album['url'] }}', '{{ addslashes($album['caption']) }}')">
                            <img src="{{ $album['url'] }}" alt="{{ $album['caption'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-90">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent flex flex-col justify-end p-4 text-white">
                                <h3 class="text-xs sm:text-sm font-bold leading-snug line-clamp-2">{{ $album['caption'] }}</h3>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </section>

        <!-- 3. CHỨNG NHẬN TỪ BẢNG CERTIFICATIONS -->
        <section class="space-y-6" id="chung-nhan">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-circle-check text-brand-600 text-lg"></i>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Chứng nhận & kiểm định</h2>
                </div>
                <p class="text-xs sm:text-sm text-gray-500">Cam kết chất lượng bằng những con số và tài liệu thật</p>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-xs relative">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    @forelse ($certifications as $cert)
                        @php
                            $certImg = (!empty($cert->image) && str_starts_with($cert->image, 'http')) 
                                ? $cert->image 
                                : asset('storage/' . ($cert->image ?? ''));
                            
                            $certFallback = 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=600';
                            
                            $expiryFormatted = !empty($cert->expiry_date) 
                                ? 'Còn hiệu lực đến ' . \Carbon\Carbon::parse($cert->expiry_date)->format('d/m/Y')
                                : 'Hiệu lực vĩnh viễn';
                        @endphp
                        <div class="flex flex-col text-center group cursor-pointer" 
                             onclick="openCertModal('{{ addslashes($cert->name ?? 'Chứng nhận') }}', '{{ addslashes($cert->code ?? '') }}', '{{ addslashes($cert->description ?? '') }}', '{{ $certImg }}')">
                            <!-- Khung ảnh chứng nhận -->
                            <div class="p-2.5 rounded-2xl bg-gray-50 border border-gray-200 mb-3 aspect-[3/4] flex items-center justify-center overflow-hidden group-hover:border-brand-500 group-hover:shadow-md transition-all">
                                <img src="{{ $certImg }}" 
                                     alt="{{ $cert->name ?? 'Chứng nhận' }}" 
                                     onerror="this.onerror=null; this.src='{{ $certFallback }}';"
                                     class="w-full h-full object-cover rounded-xl group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <!-- Nhãn tên và hạn dùng dưới ảnh -->
                            <span class="text-xs sm:text-sm font-bold text-gray-900 block truncate group-hover:text-brand-600 transition-colors">
                                {{ $cert->name ?? 'Chứng nhận VietGAP' }}
                            </span>
                            <span class="text-[11px] text-gray-500 font-medium mt-1">
                                {{ $expiryFormatted }}
                            </span>
                        </div>
                    @empty
                        @php
                            $defaultCerts = [
                                ['name' => 'Chứng Nhận VietGAP', 'code' => 'VG-2026-LD-098', 'desc' => 'Quy trình thực hành sản xuất nông nghiệp tốt.', 'expiry' => 'Còn hiệu lực đến 01/2028', 'img' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=600'],
                                ['name' => 'Tiêu Chuẩn Organic 100%', 'code' => 'ORGANIC-VINACERT-2026', 'desc' => 'Tuyệt đối không phân bón hóa học & không GMO.', 'expiry' => 'Còn hiệu lực đến 02/2027', 'img' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=600'],
                                ['name' => 'Phiếu Kiểm Nghiệm Đất & Nước', 'code' => 'EUROFINS-Q3-2026', 'desc' => 'Không tồn dư kim loại nặng Chì, Cadmium, Asen.', 'expiry' => 'Còn hiệu lực đến 12/2026', 'img' => 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=600'],
                            ];
                        @endphp
                        @foreach ($defaultCerts as $cert)
                            <div class="flex flex-col text-center group cursor-pointer" 
                                 onclick="openCertModal('{{ addslashes($cert['name']) }}', '{{ addslashes($cert['code']) }}', '{{ addslashes($cert['desc']) }}', '{{ $cert['img'] }}')">
                                <div class="p-2.5 rounded-2xl bg-gray-50 border border-gray-200 mb-3 aspect-[3/4] flex items-center justify-center overflow-hidden group-hover:border-brand-500 group-hover:shadow-md transition-all">
                                    <img src="{{ $cert['img'] }}" alt="{{ $cert['name'] }}" class="w-full h-full object-cover rounded-xl group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <span class="text-xs sm:text-sm font-bold text-gray-900 block truncate group-hover:text-brand-600 transition-colors">
                                    {{ $cert['name'] }}
                                </span>
                                <span class="text-[11px] text-gray-500 font-medium mt-1">
                                    {{ $cert['expiry'] }}
                                </span>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

    </main>

    <!-- 4. MODAL PHÓNG TO CHỨNG NHẬN TOÀN MÀN HÌNH (ĐÃ PHÓNG TO KHUNG ẢNH TOÀN MÀN HÌNH) -->
    <div id="cert-modal" class="fixed inset-0 z-50 bg-black/95 backdrop-blur-md flex flex-col items-center justify-between p-4 sm:p-6 hidden">
        <div class="w-full flex items-center justify-between text-white max-w-6xl">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-brand-500 text-xl"></i>
                <div>
                    <h3 id="cert-modal-title" class="text-base sm:text-lg font-bold text-white">Bản Scan Chứng Nhận</h3>
                    <p id="cert-modal-code" class="text-xs text-brand-400 font-mono">Số hiệu</p>
                </div>
            </div>
            <button type="button" onclick="closeCertModal()" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Khung chứa ảnh scan được phóng to tối đa -->
        <div class="flex-1 my-2 flex items-center justify-center w-full max-w-6xl overflow-hidden relative">
            <img id="cert-modal-img" src="" alt="Chứng nhận scan" class="max-h-[85vh] sm:max-h-[88vh] w-auto max-w-full object-contain rounded-2xl shadow-2xl border border-white/10">
        </div>

        <div class="w-full max-w-4xl bg-white/10 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/10 text-center text-xs sm:text-sm text-gray-200">
            <p id="cert-modal-desc">Mô tả chứng nhận</p>
        </div>
    </div>

    <!-- 5. MODAL PHÓNG TO ẢNH NÔNG TRẠI TOÀN MÀN HÌNH -->
    <div id="image-lightbox-modal" class="fixed inset-0 z-50 bg-black/95 backdrop-blur-md flex flex-col items-center justify-between p-4 sm:p-8 hidden">
        <div class="w-full flex items-center justify-between text-white max-w-6xl">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-leaf text-brand-500 text-xl"></i>
                <div>
                    <h3 id="lightbox-title" class="text-sm sm:text-base font-bold text-white">Hình ảnh nông trại</h3>
                </div>
            </div>
            <button type="button" onclick="closeImageLightbox()" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="flex-1 my-4 flex items-center justify-center w-full max-w-6xl overflow-hidden relative">
            <img id="lightbox-img" src="" alt="Ảnh phóng to" class="max-h-[80vh] max-w-full object-contain rounded-2xl shadow-2xl border border-white/10">
        </div>
    </div>

    <!-- SCRIPT JS ĐIỀU KHIỂN LIGHTBOX -->
    <script>
        const certModal = document.getElementById('cert-modal');
        const certModalTitle = document.getElementById('cert-modal-title');
        const certModalCode = document.getElementById('cert-modal-code');
        const certModalDesc = document.getElementById('cert-modal-desc');
        const certModalImg = document.getElementById('cert-modal-img');

        function openCertModal(title, code, desc, imgUrl) {
            if (certModalTitle) certModalTitle.textContent = title;
            if (certModalCode) certModalCode.textContent = code ? 'Số hiệu: ' + code : '';
            if (certModalDesc) certModalDesc.textContent = desc;
            if (certModalImg) certModalImg.src = imgUrl;
            certModal?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCertModal() {
            certModal?.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        certModal?.addEventListener('click', (e) => {
            if (e.target === certModal) closeCertModal();
        });

        const lightboxModal = document.getElementById('image-lightbox-modal');
        const lightboxImg = document.getElementById('lightbox-img');
        const lightboxTitle = document.getElementById('lightbox-title');

        function openImageLightbox(imgUrl, title) {
            if (lightboxImg) lightboxImg.src = imgUrl;
            if (lightboxTitle) lightboxTitle.textContent = title;
            lightboxModal?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageLightbox() {
            lightboxModal?.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        lightboxModal?.addEventListener('click', (e) => {
            if (e.target === lightboxModal) closeImageLightbox();
        });
    </script>
</div>