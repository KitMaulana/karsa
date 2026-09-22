<div class="space-y-6">
    <div class="karsa-card flex flex-wrap items-end gap-3 p-4">
        <div>
            <label class="text-xs text-ink-500">Dari</label>
            <input type="date" wire:model.live="startDate" class="block rounded-xl border border-leaf-100 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs text-ink-500">Sampai</label>
            <input type="date" wire:model.live="endDate" class="block rounded-xl border border-leaf-100 px-3 py-2 text-sm">
        </div>
        <button wire:click="exportCsv" class="rounded-xl border border-leaf-100 px-4 py-2.5 text-sm font-medium text-forest-950">Ekspor CSV</button>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="karsa-card p-4 text-center">
            <p class="text-xs text-ink-500">Akurasi</p>
            <p class="mt-1 font-display text-2xl font-bold text-forest-950">{{ $result['accuracy'] ?? '–' }}%</p>
        </div>
        <div class="karsa-card p-4 text-center">
            <p class="text-xs text-ink-500">Recall (sensitivitas)</p>
            <p class="mt-1 font-display text-2xl font-bold text-forest-950">{{ $result['recall'] ?? '–' }}%</p>
        </div>
        <div class="karsa-card p-4 text-center">
            <p class="text-xs text-ink-500">False alarm rate</p>
            <p class="mt-1 font-display text-2xl font-bold text-risk-extreme">{{ $result['false_alarm_rate'] ?? '–' }}%</p>
        </div>
    </div>

    <div class="karsa-card p-4">
        <h2 class="font-display font-bold text-forest-950">Tabel kontingensi</h2>
        <p class="mt-1 text-xs text-ink-500">Prediksi: level risiko harian ≥ Tinggi. Aktual: ada hotspot confidence tinggi atau laporan terverifikasi pada hari & kecamatan yang sama.</p>
        <table class="mt-3 w-full max-w-md text-center text-sm">
            <thead>
                <tr><th></th><th class="py-2 text-xs text-ink-500">Aktual: Ya</th><th class="py-2 text-xs text-ink-500">Aktual: Tidak</th></tr>
            </thead>
            <tbody>
                <tr>
                    <th class="py-2 text-left text-xs text-ink-500">Prediksi: Ya</th>
                    <td class="rounded-xl bg-risk-low/20 py-3 font-semibold">{{ $result['tp'] }}</td>
                    <td class="rounded-xl bg-risk-extreme/10 py-3 font-semibold">{{ $result['fp'] }}</td>
                </tr>
                <tr>
                    <th class="py-2 text-left text-xs text-ink-500">Prediksi: Tidak</th>
                    <td class="rounded-xl bg-risk-extreme/10 py-3 font-semibold">{{ $result['fn'] }}</td>
                    <td class="rounded-xl bg-risk-low/20 py-3 font-semibold">{{ $result['tn'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
