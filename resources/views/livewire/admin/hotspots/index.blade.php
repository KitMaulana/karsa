<div class="space-y-4">
    <div class="flex flex-wrap items-center gap-3">
        <button wire:click="sync" wire:loading.attr="disabled" class="karsa-btn-primary !h-11 !w-auto px-5">
            <span wire:loading.remove wire:target="sync">Sinkronkan sekarang</span>
            <span wire:loading wire:target="sync">Menyinkronkan…</span>
        </button>
        <button wire:click="exportCsv" class="rounded-xl border border-leaf-100 px-4 py-2.5 text-sm font-medium text-forest-950">Ekspor CSV</button>
        <button wire:click="exportGeojson" class="rounded-xl border border-leaf-100 px-4 py-2.5 text-sm font-medium text-forest-950">Ekspor GeoJSON</button>
        @if($syncMessage)
            <span class="text-sm text-ink-500">{{ $syncMessage }}</span>
        @endif
    </div>

    <div class="flex flex-wrap gap-3">
        <select wire:model.live="source" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <option value="">Semua sumber</option>
            <option value="sipongi">SiPongi+</option>
            <option value="firms">FIRMS</option>
        </select>
        <select wire:model.live="confidence" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <option value="">Semua confidence</option>
            <option value="high">Tinggi</option>
            <option value="medium">Sedang</option>
            <option value="low">Rendah</option>
        </select>
        <select wire:model.live="districtId" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <option value="">Semua kecamatan</option>
            @foreach($districts as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr>
                    <th class="px-4 py-2.5">Terdeteksi</th>
                    <th class="px-4 py-2.5">Sumber</th>
                    <th class="px-4 py-2.5">Confidence</th>
                    <th class="px-4 py-2.5">Kecamatan</th>
                    <th class="px-4 py-2.5">Terkonfirmasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($hotspots as $h)
                    <tr>
                        <td class="px-4 py-2.5 text-ink-500">{{ $h->detected_at->translatedFormat('d M Y, H.i') }}</td>
                        <td class="px-4 py-2.5">{{ implode(', ', $h->sources ?? []) }}</td>
                        <td class="px-4 py-2.5">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium" style="background:{{ $h->confidence->color() }}22; color:{{ $h->confidence->color() }}">{{ $h->confidence->label() }}</span>
                        </td>
                        <td class="px-4 py-2.5">{{ $h->district?->name ?? '–' }}</td>
                        <td class="px-4 py-2.5">{{ $h->corroborated ? 'Ya' : 'Tidak' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $hotspots->links() }}

    <div class="karsa-card p-4">
        <h2 class="font-display font-bold text-forest-950">Log sinkronisasi terbaru</h2>
        <div class="mt-2 space-y-1.5 text-sm">
            @foreach($logs as $log)
                <div class="flex justify-between border-b border-leaf-100 py-1.5 last:border-0">
                    <span>{{ strtoupper($log->source) }} · {{ $log->status }} · {{ $log->records }} data</span>
                    <span class="text-ink-500">{{ $log->created_at->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
