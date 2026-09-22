<div>
    <x-forest-header title="Laporan Saya" :back="true" />

    <div class="space-y-3 px-5 py-6">
        @forelse($reports as $report)
            <x-card>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-forest-950">{{ ucfirst(str_replace('_', ' ', $report->type)) }}</span>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $report->status->badgeColor() }}">{{ $report->status->label() }}</span>
                </div>
                <p class="mt-1 text-xs text-ink-500">{{ $report->created_at->translatedFormat('d M Y, H.i') }} WIB</p>
                <p class="mt-2 text-sm text-ink-500">{{ Str::limit($report->description, 100) }}</p>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-ink-500">
                    <span>Nilai kepercayaan:</span>
                    <span class="font-semibold text-forest-800">{{ number_format($report->trust_score, 0) }}/100</span>
                </div>
            </x-card>
        @empty
            <div class="py-16 text-center">
                <x-icon name="camera" class="mx-auto h-10 w-10 text-ink-500" />
                <p class="mt-3 text-sm text-ink-500">Anda belum pernah mengirim laporan.</p>
                <a href="{{ route('lapor') }}" class="karsa-btn-primary mx-auto mt-4 max-w-[200px]">Kirim laporan</a>
            </div>
        @endforelse
    </div>
</div>
