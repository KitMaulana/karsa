<div class="space-y-4">
    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr><th class="px-4 py-2.5">Waktu</th><th class="px-4 py-2.5">Pengguna</th><th class="px-4 py-2.5">Aksi</th><th class="px-4 py-2.5">Subjek</th></tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($logs as $log)
                    <tr>
                        <td class="px-4 py-2.5 text-ink-500">{{ $log->created_at->translatedFormat('d M Y, H.i') }}</td>
                        <td class="px-4 py-2.5">{{ $log->user?->name ?? 'Sistem' }}</td>
                        <td class="px-4 py-2.5">{{ str_replace('_', ' ', $log->action) }}</td>
                        <td class="px-4 py-2.5 text-ink-500">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '–' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
