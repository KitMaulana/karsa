<div class="space-y-4">
    @if(session('status'))<div class="karsa-card bg-leaf-100/40 p-3 text-sm text-forest-950">{{ session('status') }}</div>@endif

    <button wire:click="edit" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">+ Tulis artikel</button>

    @if($editingId !== null)
        <div class="karsa-card space-y-3 p-4">
            <div class="grid gap-3 sm:grid-cols-2">
                <select wire:model="category" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                    <option value="berita">Berita</option><option value="edukasi">Edukasi</option>
                </select>
                <select wire:model="status" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                    <option value="draf">Draf</option><option value="terbit">Terbit</option>
                </select>
            </div>
            <input type="text" wire:model="title" placeholder="Judul" class="w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <input type="text" wire:model="excerpt" placeholder="Ringkasan singkat" class="w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <x-trix-editor model="body" :value="$body" />
            <div class="flex gap-2">
                <button wire:click="save" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">Simpan</button>
                <button wire:click="$set('editingId', null)" class="rounded-xl border border-leaf-100 px-4 py-2.5 text-sm">Batal</button>
            </div>
        </div>
    @endif

    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr><th class="px-4 py-2.5">Judul</th><th class="px-4 py-2.5">Kategori</th><th class="px-4 py-2.5">Status</th><th class="px-4 py-2.5"></th></tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($posts as $p)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-forest-950">{{ $p->title }}</td>
                        <td class="px-4 py-2.5">{{ ucfirst($p->category) }}</td>
                        <td class="px-4 py-2.5">{{ ucfirst($p->status) }}</td>
                        <td class="px-4 py-2.5 space-x-2">
                            <button wire:click="edit({{ $p->id }})" class="font-medium text-forest-800">Ubah</button>
                            <button wire:click="delete({{ $p->id }})" wire:confirm="Hapus artikel ini?" class="font-medium text-risk-extreme">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
