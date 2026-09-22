@props(['title' => '', 'back' => false, 'backHref' => null])

<header {{ $attributes->class(['relative overflow-hidden pt-6 pb-12 px-5']) }}
    style="background: linear-gradient(180deg, var(--color-forest-950) 0%, var(--color-forest-600) 100%);">

    {{-- Lingkaran "bulan" samar di kanan atas --}}
    <div class="pointer-events-none absolute -right-6 -top-10 h-32 w-32 rounded-full bg-white/10"></div>

    <div class="relative z-10 flex items-center gap-3">
        @if($back)
            <a href="{{ $backHref ?? url()->previous() }}"
               aria-label="Kembali"
               class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur-sm transition hover:bg-white/25">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </a>
        @endif

        <div class="flex-1">
            @if($title)
                <h1 class="font-display text-lg font-bold text-white">{{ $title }}</h1>
            @endif
            {{ $slot }}
        </div>
    </div>

    {{-- Deretan siluet pohon pinus, tepi bawah bergelombang ke cream-50 --}}
    <div class="absolute inset-x-0 bottom-0 h-16 text-cream-50">
        <div class="absolute inset-0 h-[70px] overflow-hidden opacity-90">
            {!! file_get_contents(resource_path('svg/forest-strip.svg')) !!}
        </div>
        <svg viewBox="0 0 390 40" preserveAspectRatio="none" class="absolute inset-x-0 bottom-0 h-10 w-full" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path fill="currentColor" d="M0 24C48 8 97 8 145 20C193 32 242 32 290 18C315 11 340 11 390 20V40H0V24Z" />
        </svg>
    </div>
</header>
