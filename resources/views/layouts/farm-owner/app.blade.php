<!DOCTYPE html>
<html lang="vi" class="h-full bg-cream">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Bảng Điều Khiển Quản Trị' }} | {{ $farmName }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] {
                display: none !important;
            }

            .menu-admin-active {
                background: linear-gradient(135deg, #15803d 0%, #166534 100%);
                color: #ffffff !important;
                box-shadow: 0 4px 15px rgba(21, 128, 61, 0.25);
            }

            .menu-admin-inactive {
                color: #475569;
            }

            .menu-admin-inactive:hover {
                background-color: #f1f5f9;
                color: #0f172a;
            }

            .tab-btn-active {
                border-bottom: 2.5px solid #15803d;
                color: #15803d;
                font-weight: 700;
            }

            .tab-btn-inactive {
                color: #64748b;
                font-weight: 600;
            }

            .tab-btn-inactive:hover {
                color: #15803d;
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 5px;
                height: 5px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: #f8fafc;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 9999px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }

            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>

        @livewireStyles
        @stack('styles')
    </head>

    <body x-data="{}"
        x-on:swal.window="Swal.fire({
        icon: $event.detail.icon ?? 'info',
        title: $event.detail.title ?? '',
        text: $event.detail.text ?? '',
        confirmButtonColor: '#15803d'
    })"
        class="font-sans text-slate-800 bg-cream antialiased flex h-[100dvh] min-h-[100dvh] overflow-hidden selection:bg-brand-200 selection:text-brand-900">
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-900/60 z-40 hidden lg:hidden backdrop-blur-xs transition-opacity"></div>

        @livewire('farm-owner.sidebar')

        <main class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden bg-cream">
            {{ $slot }}
        </main>
        {{-- Hộp xem ảnh dùng chung cho các trang Farm Owner --}}
        <dialog x-data="{ imageSrc: '', imageTitle: '' }" x-ref="ownerImageDialog"
            x-on:owner-preview-image.window="
        imageSrc = $event.detail.src;
        imageTitle = $event.detail.title || 'Xem ảnh';
        $nextTick(() => {
            if (!$refs.ownerImageDialog.open) {
                $refs.ownerImageDialog.showModal();
            }
        });
    "
            x-on:click.self="$refs.ownerImageDialog.close()" x-on:close="imageSrc = ''; imageTitle = ''"
            aria-labelledby="owner-image-preview-title"
            class="m-auto w-[92vw] max-w-4xl max-h-[92dvh] overflow-hidden
        rounded-3xl border border-slate-200 bg-white p-0 text-slate-900
        shadow-2xl backdrop:bg-black/80 backdrop:backdrop-blur-sm">

            <div class="flex max-h-[90dvh] flex-col">
                <div
                    class="flex shrink-0 items-center justify-between gap-3
            border-b border-slate-100 p-4 sm:p-5">

                    <h2 id="owner-image-preview-title" x-text="imageTitle"
                        class="min-w-0 break-words text-base font-extrabold sm:text-lg">
                    </h2>

                    <button type="button" x-on:click="$refs.ownerImageDialog.close()" autofocus aria-label="Đóng ảnh"
                        class="flex h-9 w-9 shrink-0 cursor-pointer items-center
                    justify-center rounded-full bg-slate-100 text-slate-600
                    transition hover:bg-slate-200">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50
            p-3 sm:p-5 custom-scrollbar">
                    <template x-if="imageSrc">
                        <img x-bind:src="imageSrc" x-bind:alt="imageTitle"
                            class="mx-auto h-auto max-h-[64dvh] w-auto max-w-full
                        rounded-xl object-contain shadow-md">
                    </template>
                </div>

                <div class="shrink-0 border-t border-slate-100 p-4">
                    <button type="button" x-on:click="$refs.ownerImageDialog.close()"
                        class="w-full cursor-pointer rounded-xl bg-slate-100
                    px-5 py-2.5 text-sm font-bold text-slate-700
                    transition hover:bg-slate-200">
                        Đóng
                    </button>
                </div>
            </div>
        </dialog>
        <script>
            window.toggleSidebar = function() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');

                if (!sidebar || !overlay) return;

                const opening = sidebar.classList.contains('-translate-x-full');

                sidebar.classList.toggle('-translate-x-full', !opening);
                overlay.classList.toggle('hidden', !opening);
            };
        </script>

        @livewireScripts
        @stack('scripts')
    </body>

</html>
