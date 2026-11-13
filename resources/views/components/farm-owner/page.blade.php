@props(['title', 'back' => null])

<div class="flex flex-1 min-h-0 min-w-0 flex-col overflow-hidden">
    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center gap-3 px-4 sm:px-8 shrink-0">
        <button type="button" onclick="toggleSidebar()" aria-label="Mở menu"
            class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
            <i class="fa-solid fa-bars"></i>
        </button>

        @if ($back)
            <a href="{{ $back }}" class="text-brand-700 text-sm font-bold shrink-0">
                <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
            </a>
        @endif

        <h1 class="text-lg sm:text-2xl font-black text-slate-900 truncate">
            {{ $title }}
        </h1>
    </header>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-5 custom-scrollbar">
        {{ $slot }}
    </div>
</div>
