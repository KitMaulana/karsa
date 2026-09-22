<div>
    <x-forest-header title="Peringatan & Status" :back="true" />

    <div class="space-y-5 px-5 py-6">
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
                    <x-card class="text-center text-sm text-ink-500">Tidak ada peringatan aktif saat ini.</x-card>
                @endforelse
            </div>
        </div>

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
