<!DOCTYPE html>
<html lang="vi" class="h-full bg-cream">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Đăng Nhập Quản Trị | Nông Trại Đất Lành' }}</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 & TailwindCSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    </style>
    @stack('styles')
</head>
<body class="font-sans text-gray-800 bg-cream antialiased min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 selection:bg-brand-200 selection:text-brand-900">
    <!-- KHÔNG CÓ HEADER, KHÔNG CÓ SIDEBAR, CHỈ CHỨA DUY NHẤT KHUNG ĐĂNG NHẬP Ở GIỮA -->
    <main class="w-full max-w-md mx-auto">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>

