<!DOCTYPE html>
<html lang="vi" class="h-full bg-cream">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Quản Trị Nông Trại | Nông Trại Đất Lành' }}</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 & TailwindCSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .menu-active { background-color: #15803d; color: #ffffff !important; box-shadow: 0 4px 12px rgba(21, 128, 61, 0.25); }
        .menu-inactive { color: #4B5563; } 
        .menu-inactive:hover { background-color: #dcfce7; color: #15803d; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    </style>
    @stack('styles')
</head>
<body class="font-sans text-gray-800 bg-cream antialiased flex h-[100dvh] min-h-[100dvh] overflow-hidden selection:bg-brand-200 selection:text-brand-900">

    <!-- LỚP PHỦ MOBILE KHI MỞ SIDEBAR -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 z-40 hidden lg:hidden backdrop-blur-xs transition-opacity"></div>

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