<div>
    <x-forest-header :title="$program->title" :back="true" />

    <div class="space-y-4 px-5 py-6">
        @if(session('status'))
            <x-card class="bg-leaf-100/60 text-sm text-forest-950">{{ session('status') }}</x-card>
        @endif

        @if($program->cover)
            <img src="{{ $program->cover }}" alt="" class="h-40 w-full rounded-2xl object-cover">
        @endif

        <x-card>
            <p class="text-sm leading-relaxed text-ink-500">{{ $program->description }}</p>
            <div class="mt-3 space-y-1 text-sm text-ink-500">
                <p><strong class="text-forest-950">Tanggal:</strong> {{ $program->starts_at?->translatedFormat('d M Y, H.i') }} WIB</p>
                <p><strong class="text-forest-950">Lokasi:</strong> {{ $program->location }}</p>
                @if($program->volunteer_quota)
                    <p><strong class="text-forest-950">Kuota relawan:</strong> {{ $program->participants()->where('status', '!=', 'batal')->count() }}/{{ $program->volunteer_quota }}</p>
                @endif
            </div>
        </x-card>

        @if($program->donation_enabled && $donasiEnabled)
            <x-card>
                <h2 class="font-display font-bold text-forest-950">Donasi</h2>
                <div class="mt-2 h-2 rounded-full bg-leaf-100">
                    <div class="h-2 rounded-full bg-forest-600" style="width: {{ $program->donationProgressPercent() }}%"></div>
                </div>
                <p class="mt-1 text-sm text-ink-500">Rp{{ number_format($program->donation_collected, 0, ',', '.') }} dari target Rp{{ number_format($program->donation_target, 0, ',', '.') }}</p>

                @if($program->usage_report)
                    <div class="mt-3 border-t border-leaf-100 pt-3">
                        <p class="text-xs font-semibold text-forest-950">Laporan penggunaan dana</p>
                        <p class="mt-1 text-xs text-ink-500">{{ $program->usage_report }}</p>
                    </div>
                @endif
            </x-card>
        @endif

        @if($isJoined)
            <div class="karsa-btn-primary flex items-center justify-center gap-2 !bg-leaf-100 !text-forest-800">
                <x-icon name="check" class="h-5 w-5" /> Anda sudah terdaftar
            </div>
        @else
            <button type="button" wire:click="join" class="karsa-btn-primary">Ikut sebagai relawan</button>
        @endif
    </div>
</div>
