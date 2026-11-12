<!DOCTYPE html>
<html lang="vi" class="h-full bg-cream">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Quản Trị Nông Trại' }} | {{ $farmName }}</title>

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- SweetAlert2 & TailwindCSS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .menu-active {
                background-color: #15803d;
                color: #ffffff !important;
                box-shadow: 0 4px 12px rgba(21, 128, 61, 0.25);
            }

            .menu-inactive {
                color: #4B5563;
            }

            .menu-inactive:hover {
                background-color: #dcfce7;
                color: #15803d;
            }

            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: #f1f5f9;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 9999px;
            }

            @media print {
                @page {
                    size: 70mm 80mm;
                    margin: 0;
                }

                html,
                body {
                    display: block !important;
                    width: 70mm !important;
                    height: 80mm !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    overflow: hidden !important;
                    background: white !important;
                }

                body>*:not(#farm-print-root) {
                    display: none !important;
                }

                #farm-print-root {
                    position: static !important;
                    display: block !important;
                    width: 70mm !important;
                    height: 80mm !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: white !important;
                }

                .farm-no-print {
                    display: none !important;
                }

                .farm-print-card {
                    box-sizing: border-box;
                    width: 70mm !important;
                    max-width: none !important;
                    height: 80mm !important;
                    padding: 0 !important;
                    border-radius: 0 !important;
                    box-shadow: none !important;
                }

                #farm-thermal-label {
                    box-sizing: border-box;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1mm;
                    width: 70mm;
                    height: 80mm;
                    padding: 4mm !important;
                    border: none !important;
                    border-radius: 0 !important;
                    color: black !important;
                }

                #farm-thermal-label>* {
                    margin: 0 !important;
                }

                #farm-thermal-label img {
                    width: 30mm !important;
                    height: 30mm !important;
                    flex-shrink: 0;
                }

                #farm-thermal-label .farm-label-name {
                    max-height: 10mm;
                    overflow: hidden;
                    font-size: 11px;
                    line-height: 1.2;
                }
            }
        </style>
        @stack('styles')
    </head>

    <body x-data="{}"
        x-on:swal.window="Swal.fire({
        icon: $event.detail.icon ?? 'success',
        title: $event.detail.title ?? '',
        text: $event.detail.text ?? '',
        confirmButtonColor: '#15803d'
    })"
        x-on:farm-preview-image.window="Swal.fire({
        imageUrl: $event.detail.src,
        imageAlt: 'Ảnh nhật ký canh tác',
        showConfirmButton: false,
        showCloseButton: true
    })"
        class="font-sans text-gray-800 bg-cream antialiased flex h-[100dvh] min-h-[100dvh] overflow-hidden selection:bg-brand-200 selection:text-brand-900">

        <!-- LỚP PHỦ MOBILE KHI MỞ SIDEBAR -->
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-black/60 z-40 hidden lg:hidden backdrop-blur-xs transition-opacity"></div>

        <!-- ================= 1. SIDEBAR LIVEWIRE COMPONENT ================= -->
        @livewire('farm-staff.sidebar')

        <!-- ================= 2. MAIN ================= -->
        <main class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden bg-cream">
            {{ $slot }}
        </main>

        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                const isClosed = sidebar.classList.contains('-translate-x-full');

                if (isClosed) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }
        </script>
        @stack('scripts')
    </body>

</html>
