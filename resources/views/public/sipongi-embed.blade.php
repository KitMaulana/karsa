<x-layouts.public title="Peta SiPongi+">
    <x-forest-header title="Peta SiPongi+" :back="true" backHref="{{ route('peta') }}" />

    <div class="px-5 py-5">
        <x-card class="mb-4 bg-leaf-100/40 text-xs text-ink-500">
            Ini adalah peta resmi <strong>SiPongi+ KLHK/Kemenhut</strong> yang ditampilkan apa adanya sebagai
            pelengkap referensi. Peta ini <strong>bukan sumber data</strong> KARSA -- skor risiko & hotspot di
            halaman Peta Risiko KARSA dihitung dari data NASA FIRMS (dan SiPongi+ bila endpointnya sudah
            dikonfigurasi admin).
        </x-card>

        @if($enabled)
            <div class="karsa-card overflow-hidden">
                <iframe
                    src="{{ $url }}"
                    title="Peta SiPongi+ KLHK/Kemenhut"
                    class="h-[70vh] w-full border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer"
                ></iframe>
            </div>
            <p class="mt-3 text-center text-xs text-ink-500">
                Peta tidak muncul? <a href="{{ $url }}" target="_blank" rel="noopener" class="font-semibold text-forest-800 underline">Buka di tab baru</a>
            </p>
        @else
            <x-card class="text-center">
                <p class="text-sm text-ink-500">Tampilan peta SiPongi+ di dalam aplikasi sedang dinonaktifkan oleh admin.</p>
                <a href="{{ $url }}" target="_blank" rel="noopener" class="karsa-btn-primary mt-4">Buka peta SiPongi+</a>
            </x-card>
        @endif
    </div>
</x-layouts.public>
