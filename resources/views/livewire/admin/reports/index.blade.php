<div class="space-y-4">
    <div class="flex gap-2">
        @foreach(['baru' => 'Baru', 'ditinjau' => 'Ditinjau', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak', 'diteruskan' => 'Diteruskan', 'selesai' => 'Selesai'] as $key => $label)
            <button wire:click="$set('status', '{{ $key }}')" class="rounded-full px-3 py-1.5 text-sm font-medium {{ $status === $key ? 'bg-forest-800 text-white' : 'bg-leaf-100 text-forest-950' }}">{{ $label }}</button>
        @endforeach
    </div>

    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr>
                    <th class="px-4 py-2.5">Waktu</th>
                    <th class="px-4 py-2.5">Pelapor</th>
                    <th class="px-4 py-2.5">Kecamatan</th>
                    <th class="px-4 py-2.5">Jenis</th>
                    <th class="px-4 py-2.5">Kepercayaan</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @forelse($reports as $r)
                    <tr>
                        <td class="px-4 py-2.5 text-ink-500">{{ $r->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-2.5">{{ $r->user->name }}</td>
                        <td class="px-4 py-2.5">{{ $r->district?->name ?? '–' }}</td>
                        <td class="px-4 py-2.5">{{ ucfirst(str_replace('_', ' ', $r->type)) }}</td>
                        <td class="px-4 py-2.5">
                            <span class="font-semibold {{ $r->trust_score >= 70 ? 'text-forest-800' : 'text-ink-500' }}">{{ number_format($r->trust_score, 0) }}</span>
                            @if(!empty($r->flags))
                                <span class="ml-1 text-risk-extreme" title="Ada bendera merah">⚑</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5"><a href="{{ route('admin.reports.show', $r) }}" class="font-medium text-forest-800">Lihat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-ink-500">Tidak ada laporan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $reports->links() }}
</div>
