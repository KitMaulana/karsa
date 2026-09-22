<div class="space-y-4">
    @if(session('status'))
        <div class="karsa-card bg-leaf-100/40 p-3 text-sm text-forest-950">{{ session('status') }}</div>
    @endif

    <div class="karsa-card p-4">
        <h2 class="font-display font-bold text-forest-950">Impor batas wilayah (GeoJSON)</h2>
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <input type="file" wire:model="geojsonFile" accept=".json,.geojson" class="text-sm">
            <select wire:model="importLevel" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <option value="district">Kecamatan</option>
                <option value="regency">Kabupaten/kota</option>
            </select>
            <button wire:click="importGeojson" class="rounded-xl bg-forest-800 px-4 py-2 text-sm font-semibold text-white">Impor</button>
        </div>
        @if($importMessage)
            <pre class="mt-3 whitespace-pre-wrap rounded-xl bg-leaf-100/30 p-3 text-xs text-ink-500">{{ $importMessage }}</pre>
        @endif
    </div>

    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[800px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr>
                    <th class="px-4 py-2.5">Kecamatan</th>
                    <th class="px-4 py-2.5">Kabupaten</th>
                    <th class="px-4 py-2.5">Kerentanan</th>
                    <th class="px-4 py-2.5">Poligon</th>
                    <th class="px-4 py-2.5">Dipantau</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($districts as $d)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-forest-950">{{ $d->name }}</td>
                        <td class="px-4 py-2.5 text-ink-500">{{ $d->regency->name }}</td>
                        <td class="px-4 py-2.5">{{ $d->vulnerability_score }}</td>
                        <td class="px-4 py-2.5">{{ $d->geometry ? '✓' : '–' }}</td>
                        <td class="px-4 py-2.5">{{ $d->is_monitored ? 'Ya' : 'Tidak' }}</td>
                        <td class="px-4 py-2.5"><button wire:click="edit({{ $d->id }})" class="font-medium text-forest-800">Ubah</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($editingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md rounded-3xl bg-white p-6">
                <h3 class="font-display font-bold text-forest-950">Ubah kerentanan wilayah</h3>
                <div class="mt-4 space-y-3">
                    @foreach(['tutupan_lahan' => 'Tutupan lahan/vegetasi', 'lahan_gambut' => 'Lahan gambut', 'riwayat_kebakaran' => 'Riwayat kebakaran', 'jarak_permukiman' => 'Jarak ke permukiman', 'akses_pemadam' => 'Akses pemadam'] as $key => $label)
                        <div>
                            <div class="flex justify-between text-sm"><span>{{ $label }}</span><span>{{ $vulnerabilityFactors[$key] }}</span></div>
                            <input type="range" min="0" max="100" wire:model="vulnerabilityFactors.{{ $key }}" class="w-full">
                        </div>
                    @endforeach
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="isMonitored"> Aktifkan pemantauan</label>
                </div>
                <div class="mt-5 flex gap-2">
                    <button wire:click="save" class="karsa-btn-primary !h-11">Simpan</button>
                    <button wire:click="$set('editingId', null)" class="rounded-xl border border-leaf-100 px-4 py-2.5 text-sm">Batal</button>
                </div>
            </div>
        </div>
    @endif
</div>
