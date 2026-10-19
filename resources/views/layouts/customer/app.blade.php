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
    
<!-- Toast thông báo Livewire + Alpine -->


<div
    x-data="{
        toasts: [],
        nextId: 1,

        init() {
            const success = @js(session('success'));
            const error = @js(session('error'));

            if (success) {
                this.addToast('success', success);
            }

            if (error) {
                this.addToast('error', error);
            }
        },

        notify(event) {
            const detail = event.detail ?? {};

            this.addToast(
                detail.type ?? 'success',
                detail.message ?? ''
            );
        },

        addToast(type, message) {
            if (!message) return;

            const id = this.nextId++;

            this.toasts.push({
                id,
                type,
                message
            });

            setTimeout(() => {
                this.removeToast(id);
            }, 4000);
        },

        removeToast(id) {
            this.toasts = this.toasts.filter(
                toast => toast.id !== id
            );
        }
    }"
    x-on:show-toast.window="notify($event)"
    class="fixed top-24 right-5 z-[110] w-[calc(100%-2.5rem)] max-w-sm flex flex-col gap-3"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-5"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-5"
            class="w-full rounded-2xl shadow-xl p-4 flex items-center justify-between gap-3 border"
            :class="toast.type === 'success'
                ? 'border-emerald-200 bg-emerald-50'
                : 'border-red-200 bg-red-50'"
        >
            <div class="flex items-center gap-3 min-w-0">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                    :class="toast.type === 'success'
                        ? 'bg-emerald-500 text-white'
                        : 'bg-red-500 text-white'"
                >
                    <i
                        class="fa-solid text-sm"
                        :class="toast.type === 'success'
                            ? 'fa-check'
                            : 'fa-triangle-exclamation'"
                    ></i>
                </div>

                <p
                    class="text-xs font-bold break-words flex-1"
                    :class="toast.type === 'success'
                        ? 'text-emerald-900'
                        : 'text-red-900'"
                    x-text="toast.message"
                ></p>
            </div>

            <button
                type="button"
                @click="removeToast(toast.id)"
                class="text-gray-400 hover:text-gray-600 p-1 shrink-0"
                aria-label="Đóng thông báo"
            >
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    </template>
</div>

</body>
</html>