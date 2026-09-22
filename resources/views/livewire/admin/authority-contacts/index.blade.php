<div class="space-y-4">
    @if(session('status'))<div class="karsa-card bg-leaf-100/40 p-3 text-sm text-forest-950">{{ session('status') }}</div>@endif

    <button wire:click="edit" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">+ Tambah kontak</button>

    @if($editingId !== null)
        <div class="karsa-card grid gap-3 p-4 sm:grid-cols-2">
            <input type="text" wire:model="name" placeholder="Nama" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <input type="text" wire:model="agency" placeholder="Instansi (mis. BPBD, Manggala Agni)" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <select wire:model="regencyId" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <option value="">Kabupaten/kota (opsional)</option>
                @foreach($regencies as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
            </select>
            <select wire:model="districtId" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <option value="">Kecamatan (opsional)</option>
                @foreach($districts as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
            </select>
            <input type="email" wire:model="email" placeholder="Email" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <input type="text" wire:model="whatsapp" placeholder="Nomor WhatsApp (08xx)" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <select wire:model="minLevel" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <option value="sedang">Sedang</option><option value="tinggi">Tinggi</option><option value="sangat_tinggi">Sangat tinggi</option>
            </select>
            <div class="flex gap-2 sm:col-span-2">
                <button wire:click="save" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">Simpan</button>
                <button wire:click="$set('editingId', null)" class="rounded-xl border border-leaf-100 px-4 py-2.5 text-sm">Batal</button>
            </div>
        </div>
    @endif

    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr><th class="px-4 py-2.5">Nama</th><th class="px-4 py-2.5">Instansi</th><th class="px-4 py-2.5">Wilayah</th><th class="px-4 py-2.5">Level minimal</th><th class="px-4 py-2.5"></th></tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($contacts as $c)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-forest-950">{{ $c->name }}</td>
                        <td class="px-4 py-2.5">{{ $c->agency }}</td>
                        <td class="px-4 py-2.5 text-ink-500">{{ $c->district?->name ?? $c->regency?->name ?? 'Semua wilayah' }}</td>
                        <td class="px-4 py-2.5">{{ $c->min_level->label() }}</td>
                        <td class="px-4 py-2.5 space-x-2">
                            <button wire:click="edit({{ $c->id }})" class="font-medium text-forest-800">Ubah</button>
                            <button wire:click="delete({{ $c->id }})" wire:confirm="Hapus kontak ini?" class="font-medium text-risk-extreme">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
