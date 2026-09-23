<div wire:init="loadAiAnalysis">
    <x-forest-header title="Peringatan & Status" :back="true" />

    <div class="space-y-5 px-5 py-6">

        {{-- Kartu Analisis Cerdas Google Gemini AI --}}
        @if($aiLoading)
            <x-ai-processing-card 
                title="AI GEMINI SEDANG MEMPROSES DATA NASA FIRMS DAN BMKG"
                subtitle="Menganalisis anomali cuaca & sebaran titik panas satelit NASA FIRMS untuk prediksi karhutla..."
            />
        @elseif(!empty($aiAnalysis) && !empty($aiAnalysis['data']))
            <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-amber-50 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-forest-800 text-white shadow-sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </span>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-forest-800">Analisis AI Google Gemini</span>
                            <h3 class="font-display text-sm font-bold text-forest-950">Kec. {{ $aiAnalysis['district_name'] ?? ($aiAnalysis['district']->name ?? '') }}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        @if(!empty($aiAnalysis['data']['tingkat_ancaman']))
                            <span class="rounded-full bg-forest-800/10 px-2.5 py-1 text-xs font-semibold text-forest-900">
                                {{ $aiAnalysis['data']['tingkat_ancaman'] }}
                            </span>
                        @endif
                        <button type="button" wire:click="refreshAiAnalysis" wire:loading.attr="disabled"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-emerald-200 bg-white/80 text-forest-800 hover:bg-emerald-100 transition shadow-2xs"
                                title="Perbarui analisis AI">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Hotspot & Cuaca Info Tag --}}
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center gap-1 rounded-lg bg-white/80 px-2 py-1 text-[11px] font-medium text-ink-500 shadow-xs">
                        <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                        {{ $aiAnalysis['hotspot_count'] }} Titik Panas NASA FIRMS
                    </span>
                    @if(isset($aiAnalysis['score']) && $aiAnalysis['score'] !== null)
                        <span class="inline-flex items-center gap-1 rounded-lg bg-white/80 px-2 py-1 text-[11px] font-medium text-ink-500 shadow-xs">
                            Skor Risiko: {{ is_numeric($aiAnalysis['score']) ? $aiAnalysis['score'] : round($aiAnalysis['score']->score, 0) }}/100
                        </span>
                    @endif
                </div>

                {{-- Ringkasan Situasi --}}
                <div class="mt-3 text-xs leading-relaxed text-forest-950">
                    <p>{{ $aiAnalysis['data']['ringkasan_situasi'] }}</p>
                </div>

                {{-- Prediksi 72 Jam --}}
                @if(!empty($aiAnalysis['data']['prediksi_72_jam']))
                    <div class="mt-3 rounded-2xl bg-white/90 p-3 text-xs border border-amber-200/60">
                        <p class="font-bold text-amber-900 flex items-center gap-1">
                            <span>⏱️</span> Prediksi 72 Jam ke Depan:
                        </p>
                        <p class="mt-1 text-ink-500">{{ $aiAnalysis['data']['prediksi_72_jam'] }}</p>
                    </div>
                @endif

                {{-- Rekomendasi Utama --}}
                @if(!empty($aiAnalysis['data']['rekomendasi_utama']))
                    <div class="mt-3 flex items-start gap-2 text-xs">
                        <span class="font-bold text-forest-800 shrink-0">👉 Aksi Prioritas:</span>
                        <span class="text-ink-500">{{ $aiAnalysis['data']['rekomendasi_utama'] }}</span>
                    </div>
                @endif

                <div class="mt-3 pt-3 border-t border-emerald-100 flex items-center justify-between">
                    <span class="text-[10px] text-ink-500">Sumber: Satelit NASA FIRMS + Cuaca BMKG</span>
                    <a href="{{ route('aksi', ['kecamatan' => $aiAnalysis['district_slug'] ?? ($aiAnalysis['district']->slug ?? '')]) }}" class="text-xs font-bold text-forest-800 hover:underline">
                        Buka Rencana Aksi →
                    </a>
                </div>
            </div>
        @endif

        {{-- Peringatan Aktif --}}
        <div>
            <h2 class="font-display font-bold text-forest-950">Peringatan aktif</h2>
            <div class="mt-3 space-y-3">
                @forelse($active as $alert)
                    <x-card class="border-l-4" style="border-left-color: {{ $alert->to_level->color() }}">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-forest-950">{{ $alert->district->name }}</p>
                            <x-risk-pill :level="$alert->to_level" />
                        </div>
                        <p class="mt-1 text-sm text-ink-500">
                            @if($alert->from_level)
                                Naik dari <strong>{{ $alert->from_level->label() }}</strong> ke <strong>{{ $alert->to_level->label() }}</strong>
                            @else
                                Level: <strong>{{ $alert->to_level->label() }}</strong>
                            @endif
                        </p>
                        @if(!empty($alert->causes))
                            <p class="mt-2 text-xs text-ink-500">
                                <strong>Penyebab utama:</strong> {{ collect($alert->causes)->pluck('label')->implode(', ') }}
                            </p>
                        @endif
                        <a href="{{ route('peta') }}" class="mt-2 inline-block text-sm font-semibold text-forest-800">Lihat peta risiko →</a>
                    </x-card>
                @empty
                    <x-card class="text-center text-sm text-ink-500">Tidak ada eskalasi darurat aktif saat ini.</x-card>
                @endforelse
            </div>
        </div>

        {{-- Riwayat Peringatan --}}
        <div>
            <h2 class="font-display font-bold text-forest-950">Riwayat peringatan</h2>
            <div class="mt-3 space-y-2">
                @forelse($history as $alert)
                    <div class="flex items-center justify-between rounded-2xl border border-leaf-100 bg-white px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-forest-950">{{ $alert->district->name }}</p>
                            <p class="text-xs text-ink-500">{{ $alert->created_at->translatedFormat('d M Y, H.i') }} WIB</p>
                        </div>
                        <x-risk-pill :level="$alert->to_level" />
                    </div>
                @empty
                    <p class="text-sm text-ink-500">Belum ada riwayat peringatan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
