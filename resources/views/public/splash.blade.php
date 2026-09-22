<x-layouts.public title="Beranda">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 text-center"
         style="background: linear-gradient(180deg, var(--color-forest-950) 0%, var(--color-forest-600) 100%);">
        <div class="flex h-24 w-24 items-center justify-center rounded-3xl bg-white/15">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5" class="h-14 w-14">
                <path d="M12 3l7 3v6c0 4.5-3 7.7-7 9-4-1.3-7-4.5-7-9V6Z" />
                <path d="M12 8v7M9 12l3 3 3-3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        <p class="karsa-wordmark mt-6 text-4xl text-white">KARSA</p>
        <p class="mt-2 text-sm text-white/80">Kawasan Analisis Risiko dan Siaga</p>
        <p class="mt-6 max-w-xs text-sm text-white/70">Satu aplikasi untuk memantau, menganalisis, dan melindungi hutan kita.</p>

        <p class="mt-10 animate-pulse text-xs text-white/60">Menyiapkan data risiko…</p>
    </div>

    <script>
        setTimeout(function () {
            var onboarded = localStorage.getItem('karsa_onboarded');
            window.location.href = onboarded ? '{{ route('menu') }}' : '{{ route('onboarding') }}';
        }, 1500);
    </script>
</x-layouts.public>
