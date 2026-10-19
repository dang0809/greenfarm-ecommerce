<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Nông Trại Đất Lành | Nông Sản Sạch Tự Trồng - Tự Bán' }}</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Thư viện Quét mã QR bằng Camera HTML5 -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

   @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html { scroll-behavior: smooth; }
        .glass-header { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .dot.active { background-color: #ffffff; width: 1.75rem; }
        #qr-reader video { border-radius: 1rem; object-fit: cover; }
    </style>

    @stack('styles')
</head>

<body class="font-sans text-gray-800 bg-white antialiased selection:bg-brand-200 selection:text-brand-900 min-h-screen flex flex-col justify-between">
    <livewire:customer.auth.auth-modal />
    <!-- 1. LIVEWIRE HEADER -->
    <livewire:customer.header />

    <!-- 2. NỘI DUNG TRANG ĐỘNG ($slot) -->
    <main class="flex-grow">
        {{ $slot }}
    </main>
        @auth
    <!-- 3. NÚT NỔI ĐĂNG KÝ GÓI RAU (FLOATING CTA) -->
    <a href="#" class="fixed bottom-6 right-4 sm:right-6 z-40 bg-gradient-to-r from-emerald-700 to-brand-600 hover:from-emerald-800 hover:to-brand-700 text-white px-4 py-3 rounded-full shadow-2xl flex items-center gap-3 transition-all hover:scale-105 active:scale-95 border-2 border-white/70 group">
        <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-amber-300 text-base shadow-inner">
            <i class="fa-solid fa-box-open group-hover:rotate-12 transition-transform"></i>
        </div>
        <div class="text-left pr-1">
            <span class="block text-[10px] text-brand-100 uppercase font-extrabold tracking-wider leading-tight">Combo tươi mỗi tuần</span>
            <span class="block text-xs sm:text-sm font-black leading-tight">Đăng Ký Gói Rau Tuần</span>
        </div>
        <span class="bg-amber-400 text-gray-900 text-[10px] font-black px-2 py-0.5 rounded-full shadow-xs">-10%</span>
    </a>
        @endauth
    <!-- 4. FOOTER BLADE COMPONENT -->
    <x-customer.footer />

    <!-- SCRIPT ĐIỀU KHIỂN HEADER MOBILE & USER MENU -->
    

    @stack('scripts')
    <!-- TOAST THÔNG BÁO TỰ ẨN SAU 4 GIÂY -->
@if (session()->has('success') || session()->has('error'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 4000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed top-24 right-5 z-[110] max-w-sm w-full bg-white border rounded-2xl shadow-xl p-4 flex items-center justify-between gap-3 {{ session()->has('success') ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }}">
        
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ session()->has('success') ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }}">
                <i class="fa-solid {{ session()->has('success') ? 'fa-check' : 'fa-triangle-exclamation' }} text-sm"></i>
            </div>
            <p class="text-xs font-bold {{ session()->has('success') ? 'text-emerald-900' : 'text-red-900' }}">
                {{ session('success') ?? session('error') }}
            </p>
        </div>

        <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-600 p-1">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>
@endif
</body>
</html>