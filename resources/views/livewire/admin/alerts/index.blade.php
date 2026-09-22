<div class="space-y-4">
    @if(session('status'))
        <div class="karsa-card bg-leaf-100/40 p-3 text-sm text-forest-950">{{ session('status') }}</div>
    @endif

    <button wire:click="$set('showForm', true)" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">+ Buat peringatan manual</button>

    @if($showForm)
        <div class="karsa-card space-y-3 p-4">
            <select wire:model="districtId" class="w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <option value="">Pilih kecamatan</option>
                @foreach($districts as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
            </select>
            <select wire:model="level" class="w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm">
                <option value="sedang">Sedang</option>
                <option value="tinggi">Tinggi</option>
                <option value="sangat_tinggi">Sangat tinggi</option>
            </select>
            <textarea wire:model="message" rows="3" placeholder="Isi pesan peringatan..." class="w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm"></textarea>
            <div class="flex gap-2">
                <button wire:click="broadcast" class="rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-semibold text-white">Kirim</button>
                <button wire:click="$set('showForm', false)" class="rounded-xl border border-leaf-100 px-4 py-2.5 text-sm">Batal</button>
            </div>
        </div>
    @endif

    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr><th class="px-4 py-2.5">Waktu</th><th class="px-4 py-2.5">Kecamatan</th><th class="px-4 py-2.5">Level</th><th class="px-4 py-2.5">Tipe</th><th class="px-4 py-2.5"></th></tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($alerts as $a)
                    <tr>
                        <td class="px-4 py-2.5 text-ink-500">{{ $a->created_at->translatedFormat('d M Y, H.i') }}</td>
                        <td class="px-4 py-2.5">{{ $a->district->name }}</td>
                        <td class="px-4 py-2.5"><x-risk-pill :level="$a->to_level" /></td>
                        <td class="px-4 py-2.5">{{ $a->is_manual ? 'Manual' : 'Otomatis' }}</td>
                        <td class="px-4 py-2.5">
                            @php($wa = $this->whatsappLink($a))
                            @if($wa)<a href="{{ $wa }}" target="_blank" class="font-medium text-forest-800">Kirim via WhatsApp</a>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $alerts->links() }}
</div>
