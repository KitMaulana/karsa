<div class="space-y-4">
    @if(session('status'))<div class="karsa-card bg-leaf-100/40 p-3 text-sm text-forest-950">{{ session('status') }}</div>@endif

    <button wire:click="edit" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">+ Tambah rekomendasi</button>

    @if($editingId !== null)
        <div class="karsa-card grid gap-3 p-4 sm:grid-cols-2">
            <select wire:model="level" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <option value="rendah">Rendah</option><option value="sedang">Sedang</option><option value="tinggi">Tinggi</option><option value="sangat_tinggi">Sangat tinggi</option>
            </select>
            <select wire:model="audience" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <option value="warga_umum">Warga umum</option><option value="petani_pekebun">Petani & pekebun</option><option value="sekolah">Sekolah</option>
            </select>
            <input type="text" wire:model="icon" placeholder="Nama ikon (mis. leaf, flame)" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <input type="number" wire:model="order" placeholder="Urutan" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <input type="text" wire:model="title" placeholder="Judul" class="sm:col-span-2 rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <textarea wire:model="body" placeholder="Penjelasan" rows="3" class="sm:col-span-2 rounded-xl border border-leaf-100 px-3 py-2 text-sm"></textarea>
            <div class="flex gap-2 sm:col-span-2">
                <button wire:click="save" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">Simpan</button>
                <button wire:click="$set('editingId', null)" class="rounded-xl border border-leaf-100 px-4 py-2.5 text-sm">Batal</button>
            </div>
        </div>
    @endif

    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr><th class="px-4 py-2.5">Level</th><th class="px-4 py-2.5">Audiens</th><th class="px-4 py-2.5">Judul</th><th class="px-4 py-2.5"></th></tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($recommendations as $r)
                    <tr>
                        <td class="px-4 py-2.5">{{ ucfirst(str_replace('_',' ',$r->level)) }}</td>
                        <td class="px-4 py-2.5">{{ ucfirst(str_replace('_',' ',$r->audience)) }}</td>
                        <td class="px-4 py-2.5 font-medium text-forest-950">{{ $r->title }}</td>
                        <td class="px-4 py-2.5 space-x-2">
                            <button wire:click="edit({{ $r->id }})" class="font-medium text-forest-800">Ubah</button>
                            <button wire:click="delete({{ $r->id }})" wire:confirm="Hapus rekomendasi ini?" class="font-medium text-risk-extreme">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
