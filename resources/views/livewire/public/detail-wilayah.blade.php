<div wire:init="loadAiAnalysis">
    <x-forest-header :title="$district->name" :back="true" />

    <div class="-mt-6 space-y-5 px-5 pb-6">
        <x-card class="relative z-10 flex flex-col items-center py-6">
            @if($score)
                <div x-data="{ pct: 0 }" x-init="setTimeout(() => pct = {{ $score->score }}, 150)" class="relative h-32 w-56">
                    <svg viewBox="0 0 200 110" class="h-full w-full">
                        <path d="M10 100 A90 90 0 0 1 190 100" fill="none" stroke="#DDEBD3" stroke-width="14" stroke-linecap="round" />
                        <path d="M10 100 A90 90 0 0 1 190 100" fill="none" :stroke="'{{ $score->level->color() }}' " stroke-width="14" stroke-linecap="round"
                              stroke-dasharray="283" :stroke-dashoffset="283 - (283 * pct / 100)" style="transition: stroke-dashoffset 1s ease-out;" />
                    </svg>
                    <div class="absolute inset-x-0 bottom-0 flex flex-col items-center">
                        <span class="font-display text-4xl font-extrabold text-forest-950">{{ number_format($score->score, 0) }}<span class="text-base font-medium text-ink-500">/100</span></span>
                    </div>
                </div>
                <x-risk-pill :level="$score->level" class="mt-2" />
                <p class="mt-2 text-xs text-ink-500">Diperbarui {{ $score->calculated_at->translatedFormat('d M Y, H.i') }} WIB</p>
            @else
                <p class="text-sm text-ink-500">Belum ada data risiko untuk wilayah ini.</p>
            @endif
        </x-card>

        <div class="grid grid-cols-3 gap-3">
            <x-card :padded="false" class="p-3 text-center">
                <x-icon name="thermometer" class="mx-auto h-5 w-5 text-risk-high" />
                <p class="mt-1 font-display text-lg font-bold text-forest-950">{{ $weather?->temp_max ? number_format($weather->temp_max, 0).'°' : '–' }}</p>
                <p class="text-[11px] text-ink-500">Suhu maks</p>
            </x-card>
            <x-card :padded="false" class="p-3 text-center">
                <x-icon name="droplets" class="mx-auto h-5 w-5 text-water" />
                <p class="mt-1 font-display text-lg font-bold text-forest-950">{{ $weather?->rh_min ? number_format($weather->rh_min, 0).'%' : '–' }}</p>
                <p class="text-[11px] text-ink-500">Kelembapan min</p>
            </x-card>
            <x-card :padded="false" class="p-3 text-center">
                <x-icon name="flame" class="mx-auto h-5 w-5 text-risk-extreme" />
                <p class="mt-1 font-display text-lg font-bold text-forest-950">{{ $score?->detail['hotspot_count_24h'] ?? 0 }}</p>
                <p class="text-[11px] text-ink-500">Hotspot 24 jam</p>
            </x-card>
        </div>

        {{-- Analisis Cerdas Gemini AI --}}
        @if($aiLoading)
            <x-ai-processing-card 
                title="AI GEMINI SEDANG MEMPROSES DATA NASA FIRMS DAN BMKG"
                subtitle="Menganalisis sebaran titik panas satelit NASA & indikator cuaca BMKG untuk Kecamatan {{ $district->name }}..."
            />
        @elseif(!empty($aiAnalysis))
            <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-amber-50/60 p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-forest-800 text-white shadow-xs">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </span>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-forest-800">Tinjauan Cerdas Gemini AI</span>
                            <h3 class="font-display text-xs font-bold text-forest-950">Prediksi & Mitigasi Cerdas</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        @if(!empty($aiAnalysis['tingkat_ancaman']))
                            <span class="rounded-full bg-forest-800/10 px-2 py-0.5 text-[10px] font-semibold text-forest-900">
                                {{ $aiAnalysis['tingkat_ancaman'] }}
                            </span>
                        @endif
                        <button type="button" wire:click="refreshAiAnalysis" wire:loading.attr="disabled"
                                class="flex h-6 w-6 items-center justify-center rounded-lg border border-emerald-200 bg-white/80 text-forest-800 hover:bg-emerald-100 transition shadow-2xs"
                                title="Perbarui analisis AI">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>

                <p class="mt-2.5 text-xs text-forest-950 leading-relaxed">{{ $aiAnalysis['ringkasan_situasi'] }}</p>

                @if(!empty($aiAnalysis['prediksi_72_jam']))
                    <div class="mt-2.5 rounded-xl bg-amber-50/80 p-2.5 text-xs border border-amber-200/60">
                        <span class="font-bold text-amber-900">⏱️ Proyeksi 72 Jam:</span>
                        <p class="mt-0.5 text-ink-500">{{ $aiAnalysis['prediksi_72_jam'] }}</p>
                    </div>
                @endif

                @if(!empty($aiAnalysis['rekomendasi_utama']))
                    <div class="mt-2 text-xs flex items-start gap-1.5">
                        <span class="font-bold text-forest-800 shrink-0">👉 Aksi:</span>
                        <span class="text-ink-500">{{ $aiAnalysis['rekomendasi_utama'] }}</span>
                    </div>
                @endif
            </div>
        @endif

        @if($score)
            <x-card>
                <h2 class="font-display font-bold text-forest-950">Faktor risiko</h2>
                <div class="mt-3 space-y-3">
                    @foreach([
                        ['label' => 'Hotspot', 'value' => $score->s_hotspot],
                        ['label' => 'Kondisi cuaca', 'value' => $score->s_weather],
                        ['label' => 'Kerentanan wilayah', 'value' => $score->s_vulnerability],
                    ] as $factor)
                        <div>
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-ink-500">{{ $factor['label'] }}</span>
                                <span class="font-semibold text-forest-950">{{ number_format($factor['value'], 0) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-leaf-100">
                                <div class="h-2 rounded-full bg-forest-600" style="width: {{ min(100, $factor['value']) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card>
                <div class="flex items-center justify-between">
                    <h2 class="font-display font-bold text-forest-950">Keyakinan data</h2>
                    <span class="text-sm font-semibold text-forest-800">
                        {{ $score->data_confidence >= 75 ? 'Tinggi' : ($score->data_confidence >= 45 ? 'Sedang' : 'Rendah') }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-ink-500">Berdasarkan kesegaran data, jumlah sumber aktif, dan kelengkapan data wilayah.</p>
            </x-card>

            <x-card>
                <h2 class="font-display font-bold text-forest-950">Estimasi emisi CO₂</h2>
                <p class="mt-1 font-display text-2xl font-bold text-forest-950">{{ number_format($score->co2_estimate_t ?? 0, 1) }} <span class="text-sm font-normal text-ink-500">ton CO₂</span></p>
                <p class="mt-1 text-xs text-ink-500">Estimasi indikatif berdasarkan jumlah hotspot 24 jam terakhir. <a href="{{ route('tentang') }}" class="underline">Lihat metodologi</a>.</p>
            </x-card>
        @endif

        <a href="{{ route('aksi', ['kecamatan' => $district->slug]) }}" class="karsa-btn-primary">Lihat rekomendasi</a>
    </div>
</div>
