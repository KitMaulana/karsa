<div class="max-w-2xl space-y-5">
    @if(session('status'))<div class="karsa-card bg-leaf-100/40 p-3 text-sm text-forest-950">{{ session('status') }}</div>@endif

    <div class="karsa-card p-4">
        <h2 class="font-display font-bold text-forest-950">Sumber data hotspot</h2>
        <div class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-ink-500">SIPONGI_HOTSPOT_URL</span><span class="font-mono text-xs">{{ $sipongiUrl ?: 'belum diatur' }}</span></div>
            <div class="flex justify-between"><span class="text-ink-500">FIRMS_MAP_KEY</span><span class="font-mono text-xs">{{ $firmsMapKey ?: 'belum diatur' }}</span></div>
        </div>
        <p class="mt-2 text-xs text-ink-500">Kunci API diatur langsung di file .env server untuk keamanan (CLAUDE.md §3 poin 3), tidak lewat panel ini.</p>

        <label class="mt-3 block text-sm font-medium text-forest-950">Urutan prioritas sumber</label>
        <div class="mt-1 flex gap-2 text-sm">
            @foreach($providerPriority as $i => $p)
                <span class="rounded-full bg-leaf-100 px-3 py-1">{{ $i + 1 }}. {{ strtoupper($p) }}</span>
            @endforeach
        </div>
    </div>

    <div class="karsa-card p-4">
        <h2 class="font-display font-bold text-forest-950">Parameter model</h2>
        <div class="mt-3 space-y-3">
            <div>
                <label class="text-sm text-ink-500">Radius penyangga (km)</label>
                <input type="number" step="0.5" wire:model="bufferKm" class="mt-1 w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm text-ink-500">Luas per hotspot untuk estimasi CO₂ (ha)</label>
                <input type="number" step="0.1" wire:model="luasPerHotspotHa" class="mt-1 w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            </div>
        </div>
    </div>

    <div class="karsa-card space-y-3 p-4">
        <label class="flex items-center justify-between text-sm">
            <span>Aktifkan embed peta SiPongi+ (iframe)</span>
            <input type="checkbox" wire:model="sipongiEmbedEnabled">
        </label>
        <label class="flex items-center justify-between text-sm">
            <span>Aktifkan fitur donasi</span>
            <input type="checkbox" wire:model="donasiEnabled">
        </label>
    </div>

    <button wire:click="save" class="karsa-btn-primary !h-11 !w-auto px-6">Simpan pengaturan</button>
</div>
