<!DOCTYPE html>
<html lang="vi" class="h-full bg-cream">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Bảng Điều Khiển Quản Trị | Nông Trại Đất Lành' }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                50: '#f0fdf4',
                                100: '#dcfce7',
                                200: '#bbf7d0',
                                500: '#22c55e',
                                600: '#16a34a',
                                700: '#15803d',
                                800: '#166534',
                                900: '#14532d',
                            },
                            cream: '#FAFAF7',
                        },
                        boxShadow: {
                            soft: '0 4px 20px -2px rgba(15, 23, 42, 0.05)',
                            modal: '0 25px 50px -12px rgba(15, 23, 42, 0.25)',
                        },
                    },
                },
            };
        </script>

        <style>
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
