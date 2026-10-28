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
    @php
        $activeVipSub = \App\Models\Subscription::where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest()
            ->first();
    @endphp

    @if ($activeVipSub)
        <!-- 3. NÚT NỔI DÀNH RIÊNG CHO THÀNH VIÊN VIP (ĐÃ MUA GÓI) -->
        <a href="{{ route('customer.vip.setup') }}" wire:navigate 
           class="fixed bottom-6 right-4 sm:right-6 z-40 bg-gradient-to-r from-emerald-800 via-brand-700 to-amber-700 hover:from-emerald-900 hover:to-amber-800 text-white px-4 py-3 rounded-full shadow-2xl flex items-center gap-3 transition-all hover:scale-105 active:scale-95 border-2 border-amber-300/80 group ring-4 ring-amber-400/20">
            <div class="w-9 h-9 rounded-full bg-amber-400/30 border border-amber-200/50 flex items-center justify-center text-amber-300 text-base shadow-inner">
                <i class="fa-solid fa-crown group-hover:rotate-12 transition-transform text-amber-300"></i>
            </div>
            <div class="text-left pr-1">
                <span class="block text-[10px] text-amber-200 uppercase font-black tracking-wider leading-tight flex items-center gap-1.5">
                    <span>Thành Viên VIP</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                </span>
                <span class="block text-xs sm:text-sm font-black leading-tight text-white">Lịch Giao Rau VIP</span>
            </div>
            <span class="bg-amber-400 text-gray-900 text-[10px] font-black px-2 py-0.5 rounded-full shadow-xs">
                {{ $activeVipSub->deliverySchedules()->where('status', 'delivered')->count() }}/8 Đợt
            </span>
        </a>
    @elseif(filter_var(\App\Models\SystemSetting::where('group', 'subscription')->where('key', 'vip_subscription_enabled')->value('value') ?? 'true', FILTER_VALIDATE_BOOLEAN))
        <!-- 3. NÚT NỔI ĐĂNG KÝ GÓI RAU (KHI CHƯA MUA GÓI & ĐANG MỞ BÁN) -->
        <a href="{{ route('customer.vip.checkout') }}" wire:navigate 
           class="fixed bottom-6 right-4 sm:right-6 z-40 bg-gradient-to-r from-emerald-700 to-brand-600 hover:from-emerald-800 hover:to-brand-700 text-white px-4 py-3 rounded-full shadow-2xl flex items-center gap-3 transition-all hover:scale-105 active:scale-95 border-2 border-white/70 group">
            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-amber-300 text-base shadow-inner">
                <i class="fa-solid fa-box-open group-hover:rotate-12 transition-transform"></i>
            </div>
            <div class="text-left pr-1">
                <span class="block text-[10px] text-brand-100 uppercase font-extrabold tracking-wider leading-tight">Combo tươi mỗi tuần</span>
                <span class="block text-xs sm:text-sm font-black leading-tight">Đăng Ký Gói Rau Tuần</span>
            </div>
            <span class="bg-amber-400 text-gray-900 text-[10px] font-black px-2 py-0.5 rounded-full shadow-xs">-10%</span>
        </a>
    @endif
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
            const warning = @js(session('warning'));
            const info = @js(session('info'));

            if (success) this.addToast('success', success);
            if (error) this.addToast('error', error);
            if (warning) this.addToast('warning', warning);
            if (info) this.addToast('info', info);
        },

        notify(event) {
            const raw = event.detail ?? {};
            const detail = Array.isArray(raw) ? (raw[0] ?? {}) : raw;

            this.addToast(
                detail.type ?? 'success',
                detail.message ?? (typeof detail === 'string' ? detail : '')
            );
        },

        addToast(type, message) {
            if (!message) return;

            const id = this.nextId++;

            this.toasts.push({
                id,
                type: type || 'success',
                message
            });

            setTimeout(() => {
                this.removeToast(id);
            }, 4000);
        },

        removeToast(id) {
            this.toasts = this.toasts.filter(toast => toast.id !== id);
        },

        getConfig(type) {
            switch (type) {
                case 'success':
                    return {
                        box: 'border-emerald-200 bg-emerald-50 text-emerald-900',
                        iconBg: 'bg-emerald-500 text-white',
                        icon: 'fa-check'
                    };
                case 'warning':
                    return {
                        box: 'border-amber-200 bg-amber-50 text-amber-900',
                        iconBg: 'bg-amber-500 text-white',
                        icon: 'fa-triangle-exclamation'
                    };
                case 'info':
                    return {
                        box: 'border-sky-200 bg-sky-50 text-sky-900',
                        iconBg: 'bg-sky-500 text-white',
                        icon: 'fa-circle-info'
                    };
                case 'error':
                default:
                    return {
                        box: 'border-rose-200 bg-rose-50 text-rose-900',
                        iconBg: 'bg-rose-500 text-white',
                        icon: 'fa-circle-xmark'
                    };
            }
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
            class="w-full rounded-2xl shadow-xl p-4 flex items-center justify-between gap-3 border backdrop-blur-xs"
            :class="getConfig(toast.type).box"
        >
            <div class="flex items-center gap-3 min-w-0">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 shadow-xs"
                    :class="getConfig(toast.type).iconBg"
                >
                    <i class="fa-solid text-sm" :class="getConfig(toast.type).icon"></i>
                </div>

                <p class="text-xs font-bold break-words flex-1 leading-snug" x-text="toast.message"></p>
            </div>

            <button
                type="button"
                @click="removeToast(toast.id)"
                class="text-gray-400 hover:text-gray-600 p-1 shrink-0 transition-colors"
                aria-label="Đóng thông báo"
            >
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    </template>
</div>

</body>
</html>