<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <div class="karsa-card p-4">
            <p class="text-xs text-ink-500">Hotspot 24 jam</p>
            <p class="mt-1 font-display text-2xl font-bold text-forest-950">{{ $hotspot24h }}</p>
        </div>
        <div class="karsa-card p-4">
            <p class="text-xs text-ink-500">Laporan menunggu</p>
            <p class="mt-1 font-display text-2xl font-bold text-forest-950">{{ $pendingReports }}</p>
        </div>
        <div class="karsa-card p-4">
            <p class="text-xs text-ink-500">Peringatan 24 jam</p>
            <p class="mt-1 font-display text-2xl font-bold text-forest-950">{{ $activeAlerts }}</p>
        </div>
        <div class="karsa-card p-4">
            <p class="text-xs text-ink-500">Pengguna</p>
            <p class="mt-1 font-display text-2xl font-bold text-forest-950">{{ $userCount }}</p>
        </div>
        <div class="karsa-card p-4">
            <p class="text-xs text-ink-500">Kecamatan sangat tinggi</p>
            <p class="mt-1 font-display text-2xl font-bold text-risk-extreme">{{ $districtsByLevel['sangat_tinggi'] ?? 0 }}</p>
        </div>
    </div>

    <div class="karsa-card p-4">
        <h2 class="font-display font-bold text-forest-950">Kecamatan per level risiko</h2>
        <div class="mt-3 grid grid-cols-4 gap-3 text-center">
            @foreach(['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi', 'sangat_tinggi' => 'Sangat tinggi'] as $key => $label)
                <div>
                    <p class="font-display text-xl font-bold text-forest-950">{{ $districtsByLevel[$key] ?? 0 }}</p>
                    <p class="text-xs text-ink-500">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="karsa-card p-4">
        <h2 class="font-display font-bold text-forest-950">Tren skor & hotspot 30 hari</h2>
        <canvas id="trendChart" height="80" class="mt-3"></canvas>
    </div>

    <div class="karsa-card overflow-hidden">
        <h2 class="p-4 pb-0 font-display font-bold text-forest-950">Status sumber data</h2>
        <table class="mt-3 w-full text-sm">
            <thead class="border-y border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr><th class="px-4 py-2">Sumber</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Sinkron terakhir</th></tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @forelse($sourceStatus as $s)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-forest-950">{{ strtoupper($s['source']) }}</td>
                        <td class="px-4 py-2.5">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $s['status'] === 'sukses' ? 'bg-risk-low' : ($s['status'] === 'sebagian' ? 'bg-risk-mid' : 'bg-risk-extreme') }}"></span>
                            <span class="ml-1.5">{{ ucfirst($s['status']) }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-ink-500">{{ $s['last_sync']->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-4 text-center text-ink-500">Belum ada sinkronisasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('livewire:navigated', renderTrendChart);
    renderTrendChart();
    function renderTrendChart() {
        const el = document.getElementById('trendChart');
        if (!el || !window.Chart) return;
        new Chart(el, {
            type: 'line',
            data: {
                labels: @json($trend30d->pluck('date')),
                datasets: [{
                    label: 'Rata-rata skor',
                    data: @json($trend30d->pluck('avg_score')),
                    borderColor: '#2A6E43',
                    backgroundColor: 'rgba(42,110,67,0.08)',
                    fill: true, tension: 0.3,
                }],
            },
            options: { plugins: { legend: { display: false } } },
        });
    }
</script>
