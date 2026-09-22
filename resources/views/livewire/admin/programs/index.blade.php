<div class="space-y-4">
    @if(session('status'))<div class="karsa-card bg-leaf-100/40 p-3 text-sm text-forest-950">{{ session('status') }}</div>@endif

    <div class="karsa-card flex items-center justify-between p-4">
        <div>
            <p class="font-semibold text-forest-950">Donasi diaktifkan</p>
            <p class="text-xs text-ink-500">Mode demonstrasi/lingkungan sekolah selama belum ada izin resmi.</p>
        </div>
        <button wire:click="toggleDonasi" class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $donasiEnabled ? 'bg-forest-800 text-white' : 'bg-leaf-100 text-forest-950' }}">
            {{ $donasiEnabled ? 'Aktif' : 'Nonaktif' }}
        </button>
    </div>

    <button wire:click="edit" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">+ Tambah program</button>

    @if($editingId !== null)
        <div class="karsa-card space-y-3 p-4">
            <input type="text" wire:model="title" placeholder="Judul program" class="w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <textarea wire:model="description" placeholder="Deskripsi" rows="3" class="w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm"></textarea>
            <div class="grid gap-3 sm:grid-cols-2">
                <input type="datetime-local" wire:model="startsAt" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <input type="text" wire:model="location" placeholder="Lokasi" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <input type="number" wire:model="volunteerQuota" placeholder="Kuota relawan" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <select wire:model="status" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                    <option value="draf">Draf</option><option value="terbit">Terbit</option><option value="selesai">Selesai</option>
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="donationEnabled"> Aktifkan donasi untuk program ini</label>
            @if($donationEnabled)
                <div class="grid gap-3 sm:grid-cols-2">
                    <input type="number" wire:model="donationTarget" placeholder="Target dana (Rp)" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                    <input type="number" wire:model="donationCollected" placeholder="Dana terkumpul (Rp)" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                </div>
                <textarea wire:model="usageReport" placeholder="Laporan penggunaan dana" rows="2" class="w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm"></textarea>
            @endif
            <div class="flex gap-2">
                <button wire:click="save" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">Simpan</button>
                <button wire:click="$set('editingId', null)" class="rounded-xl border border-leaf-100 px-4 py-2.5 text-sm">Batal</button>
            </div>
        </div>
    @endif

    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr><th class="px-4 py-2.5">Judul</th><th class="px-4 py-2.5">Relawan</th><th class="px-4 py-2.5">Status</th><th class="px-4 py-2.5"></th></tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($programs as $p)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-forest-950">{{ $p->title }}</td>
                        <td class="px-4 py-2.5">{{ $p->participants_count }}{{ $p->volunteer_quota ? '/'.$p->volunteer_quota : '' }}</td>
                        <td class="px-4 py-2.5">{{ ucfirst($p->status) }}</td>
                        <td class="px-4 py-2.5"><button wire:click="edit({{ $p->id }})" class="font-medium text-forest-800">Ubah</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
