<!DOCTYPE html>
<html lang="vi" class="h-full bg-cream">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'GreenFarm Admin' }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] {
                display: none !important;
            }

            .menu-admin-active {
                background: linear-gradient(135deg, #15803d, #166534);
                color: #fff !important;
                box-shadow: 0 4px 15px rgba(21, 128, 61, .25);
            }

            .menu-admin-inactive {
                color: #475569;
            }

            .menu-admin-inactive:hover {
                background: #f1f5f9;
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
        heightAuto: false,
        scrollbarPadding: false,
        icon: $event.detail.icon ?? 'success',
        title: $event.detail.title ?? '',
        text: $event.detail.text ?? '',
        confirmButtonColor: '#15803d'
    })"
        class="font-sans text-slate-800 bg-cream antialiased flex h-[100dvh] min-h-[100dvh] overflow-hidden selection:bg-brand-200 selection:text-brand-900">

        <div id="admin-sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-900/60 z-40 hidden lg:hidden backdrop-blur-xs">
        </div>

        @livewire('admin.sidebar')

        <main class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden bg-cream">
            {{ $slot }}
        </main>

        <script>
            window.toggleSidebar = function() {
                const sidebar = document.getElementById('admin-sidebar');
                const overlay = document.getElementById('admin-sidebar-overlay');

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
