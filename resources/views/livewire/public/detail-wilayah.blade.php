<div>
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

        <a href="{{ route('aksi') }}" class="karsa-btn-primary">Lihat rekomendasi</a>
    </div>
</div>
