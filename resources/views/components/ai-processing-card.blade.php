@props([
    'title' => 'AI GEMINI SEDANG MEMPROSES DATA NASA FIRMS DAN BMKG',
    'subtitle' => 'Menganalisis sebaran titik panas satelit NASA & indikator cuaca BMKG secara real-time...',
    'audience' => null,
])

<div role="status" aria-live="polite" aria-busy="true"
     class="relative overflow-hidden rounded-3xl border border-emerald-300/80 bg-gradient-to-br from-emerald-50 via-white to-amber-50/70 p-5 shadow-sm transition-all">
    
    {{-- Animated Scanning Bar (Glass Beam) --}}
    <div class="pointer-events-none absolute -inset-full top-0 z-0 bg-gradient-to-r from-transparent via-emerald-200/30 to-transparent animate-[pulse_2.5s_cubic-bezier(0.4,0,0.6,1)_infinite]"></div>

    <div class="relative z-10">
        {{-- Header Status AI --}}
        <div class="flex items-center justify-between gap-2 border-b border-emerald-100/80 pb-3">
            <div class="flex items-center gap-2">
                {{-- Pulsing Orb Icon --}}
                <div class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-forest-800 text-white shadow-xs">
                    <svg class="h-4 w-4 animate-spin text-emerald-300" style="animation-duration: 3s;" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
                    </span>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider text-forest-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Google Gemini 2.5 Flash
                    </span>
                    <h3 class="font-display text-xs font-bold text-forest-950">
                        Kecerdasan Buatan KARSA
                    </h3>
                </div>
            </div>

            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100/80 px-2.5 py-1 text-[10px] font-bold text-forest-900 border border-emerald-200">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                Memproses
            </span>
        </div>

        {{-- Headline & Penjelasan Interaktif --}}
        <div class="mt-3.5">
            <h4 class="font-display text-xs font-extrabold tracking-wide text-forest-900 uppercase flex items-center gap-1.5">
                <span class="text-sm">✨</span>
                {{ $title }}
            </h4>
            <p class="mt-1 text-[11px] leading-relaxed text-ink-500">
                {{ $subtitle }}
            </p>
        </div>

        {{-- Multi-step Progress Indicator --}}
        <div class="mt-3.5 space-y-2 rounded-2xl bg-white/80 p-3 border border-emerald-100/60 shadow-2xs">
            <div class="flex items-center gap-2.5 text-xs text-forest-900">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-[11px] text-emerald-800 animate-pulse">
                    🛰️
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold truncate text-forest-950">Membaca sebaran titik panas satelit NASA FIRMS</p>
                </div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Sinkron</span>
            </div>

            <div class="flex items-center gap-2.5 text-xs text-forest-900">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[11px] text-amber-800 animate-pulse">
                    🌦️
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold truncate text-forest-950">Menghubungkan data cuaca & kekeringan BMKG Serang</p>
                </div>
                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">Analisis</span>
            </div>

            <div class="flex items-center gap-2.5 text-xs text-forest-900">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-forest-100 text-[11px] text-forest-800 animate-pulse">
                    🧠
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold truncate text-forest-950">Gemini memformulasikan prediksi risiko 72 jam</p>
                </div>
                <div class="flex items-center gap-1">
                    <span class="h-1.5 w-1.5 rounded-full bg-forest-700 animate-bounce"></span>
                    <span class="h-1.5 w-1.5 rounded-full bg-forest-700 animate-bounce" style="animation-delay: 0.15s"></span>
                    <span class="h-1.5 w-1.5 rounded-full bg-forest-700 animate-bounce" style="animation-delay: 0.3s"></span>
                </div>
            </div>
        </div>

        {{-- Shimmering Skeleton Lines --}}
        <div class="mt-3.5 space-y-1.5 animate-pulse">
            <div class="h-2.5 w-full rounded-full bg-emerald-100/70"></div>
            <div class="h-2.5 w-4/5 rounded-full bg-emerald-100/60"></div>
            <div class="h-2.5 w-2/3 rounded-full bg-emerald-100/50"></div>
        </div>
    </div>
</div>
