<div wire:init="loadAiMitigation" x-data="{
        checked: JSON.parse(localStorage.getItem('karsa_checklist') || '{}'),
        toggle(id) { this.checked[id] = !this.checked[id]; localStorage.setItem('karsa_checklist', JSON.stringify(this.checked)); }
    }">
    <div class="px-5 py-6" style="background: linear-gradient(180deg, var(--color-forest-950), var(--color-forest-600));">
        <div class="flex items-center justify-between">
            <a href="{{ route('menu') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white">
                <x-icon name="chevron-left" class="h-5 w-5" />
            </a>
            <x-risk-pill :level="$level" />
        </div>
        <h1 class="mt-4 font-display text-xl font-bold text-white">Aksi Pencegahan</h1>
        <p class="mt-1 text-sm text-white/80">
            @if($level->value === 'rendah')
                Kondisi terkendali. Tetap waspada dan jaga kebiasaan baik.
            @elseif($level->value === 'sedang')
                Risiko mulai meningkat. Siapkan langkah antisipasi lebih awal.
            @elseif($level->value === 'tinggi')
                Risiko tinggi. Segera terapkan langkah pencegahan di bawah.
            @else
                Risiko sangat tinggi. Waspada penuh dan ikuti arahan petugas.
            @endif
        </p>
    </div>

    <div class="space-y-5 px-5 py-5">
        <div class="flex gap-2 overflow-x-auto">
            @foreach(['warga_umum' => 'Warga umum', 'petani_pekebun' => 'Petani & pekebun', 'sekolah' => 'Sekolah'] as $key => $label)
                <button type="button" wire:click="setAudience('{{ $key }}')"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-medium {{ $audience === $key ? 'bg-forest-800 text-white' : 'bg-leaf-100 text-forest-950' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Rekomendasi Mitigasi Cerdas AI (Google Gemini) --}}
        @if($aiLoading)
            <x-ai-processing-card 
                title="AI GEMINI SEDANG MEMPROSES DATA NASA FIRMS DAN BMKG"
                subtitle="Menyusun panduan aksi mitigasi taktis khusus kelompok {{ match($audience) { 'petani_pekebun' => 'Petani & Pekebun', 'sekolah' => 'Sekolah', default => 'Warga Umum' } }}..."
            />
        @elseif(!empty($aiMitigation) && !empty($aiMitigation['langkah_aksi']))
            <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-amber-50/60 p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-forest-800 text-white shadow-xs">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </span>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-forest-800">Rekomendasi Cerdas Gemini AI</span>
                            <h3 class="font-display text-xs font-bold text-forest-950">{{ $aiMitigation['judul_fokus'] ?? 'Fokus Mitigasi Hari Ini' }}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        @if(!empty($aiMitigation['urgensi']))
                            <span class="rounded-full bg-forest-800/10 px-2 py-0.5 text-[10px] font-semibold text-forest-900">
                                {{ $aiMitigation['urgensi'] }}
                            </span>
                        @endif
                        <button type="button" wire:click="refreshAiMitigation" wire:loading.attr="disabled"
                                class="flex h-6 w-6 items-center justify-center rounded-lg border border-emerald-200 bg-white/80 text-forest-800 hover:bg-emerald-100 transition shadow-2xs"
                                title="Perbarui panduan mitigasi AI">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mt-3 space-y-2">
                    @foreach($aiMitigation['langkah_aksi'] as $aksi)
                        <div class="rounded-2xl border border-leaf-100 bg-white/90 p-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-forest-950">{{ $aksi['judul'] }}</span>
                                @if(!empty($aksi['prioritas']))
                                    <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">{{ $aksi['prioritas'] }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-ink-500 leading-relaxed">{{ $aksi['deskripsi'] }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-2.5 text-[10px] text-ink-500 text-right">Analisis real-time data satelit NASA & cuaca BMKG</p>
            </div>
        @endif

        <div>
            <h2 class="font-display font-bold text-forest-950">Rencana aksi 72 jam ke depan</h2>

            <div class="mt-3 space-y-2.5">
                @forelse($recommendations as $rec)
                    <div class="karsa-card flex items-start gap-3 p-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-leaf-100">
                            <x-icon :name="$rec->icon" class="h-4.5 w-4.5 text-forest-800" />
                        </span>
                        <div class="flex-1">
                            <p class="font-semibold text-forest-950">{{ $rec->title }}</p>
                            <p class="mt-0.5 text-sm text-ink-500">{{ $rec->body }}</p>
                        </div>
                        <button type="button" @click="toggle({{ $rec->id }})" class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2"
                                :class="checked[{{ $rec->id }}] ? 'border-forest-800 bg-forest-800 text-white' : 'border-leaf-100 text-transparent'">
                            <x-icon name="check" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-ink-500">Belum ada rekomendasi untuk kombinasi level & audiens ini.</p>
                @endforelse
            </div>
        </div>

        @if(count($projection) > 0)
            <x-card>
                <h2 class="font-display font-bold text-forest-950">Proyeksi 72 jam</h2>
                <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                    @foreach($projection as $p)
                        <div>
                            <p class="text-xs text-ink-500">{{ \Illuminate\Support\Carbon::parse($p['date'])->translatedFormat('d M') }}</p>
                            <p class="mt-1 font-display text-lg font-bold text-forest-950">{{ number_format($p['score'], 0) }}</p>
                            <x-risk-pill :level="$p['level']" class="mt-1 text-[10px]" />
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-ink-500">Proyeksi memakai prakiraan cuaca 3 hari, hotspot diasumsikan tetap seperti kondisi saat ini.</p>
            </x-card>
        @endif

        <a href="{{ route('aksi.panduan') }}" class="karsa-btn-primary">Panduan lengkap</a>
    </div>
</div>
