<div class="space-y-5">
    @if(session('status'))
        <div class="karsa-card bg-leaf-100/40 p-3 text-sm text-forest-950">{{ session('status') }}</div>
    @endif

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="karsa-card p-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-display font-bold text-forest-950">{{ ucfirst(str_replace('_', ' ', $report->type)) }}</h2>
                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $report->status->badgeColor() }}">{{ $report->status->label() }}</span>
                </div>
                <p class="mt-1 text-xs text-ink-500">{{ $report->created_at->translatedFormat('d M Y, H.i') }} WIB · {{ $report->district?->name }}</p>
                <p class="mt-3 text-sm text-ink-500">{{ $report->description }}</p>

                @if(!empty($report->media))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($report->media as $m)
                            @if($m['type'] === 'image' && $m['public_path'])
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($m['public_path']) }}" class="h-24 w-24 rounded-xl object-cover">
                            @else
                                <span class="flex h-24 w-24 items-center justify-center rounded-xl bg-leaf-100 text-xs text-ink-500">Video</span>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="mt-3 rounded-xl bg-leaf-100/30 p-3 text-xs text-ink-500">
                    Lokasi: {{ $report->lat }}, {{ $report->lng }}
                    @if($nearestHotspot)
                        · Hotspot terdekat: {{ number_format($nearestHotspot['distance'], 2) }} km
                    @endif
                </div>
            </div>

            @if(!empty($report->flags))
                <div class="karsa-card border border-risk-extreme/30 bg-risk-extreme/5 p-4">
                    <h3 class="font-semibold text-risk-extreme">Bendera kecurigaan</h3>
                    <ul class="mt-1 list-inside list-disc text-sm text-risk-extreme">
                        @foreach($report->flags as $flag)
                            <li>{{ str_replace('_', ' ', $flag) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="karsa-card p-4">
                <h3 class="font-semibold text-forest-950">Laporan sekitar (≤2km, 6 jam)</h3>
                @forelse($nearbyReports as $nr)
                    <p class="mt-1 text-sm text-ink-500">{{ $nr->user->name }} · {{ $nr->created_at->diffForHumans() }}</p>
                @empty
                    <p class="mt-1 text-sm text-ink-500">Tidak ada laporan lain di sekitar.</p>
                @endforelse
            </div>

            <div class="karsa-card p-4">
                <h3 class="font-semibold text-forest-950">Riwayat pelapor</h3>
                @forelse($reporterHistory as $rh)
                    <div class="mt-1 flex justify-between text-sm text-ink-500">
                        <span>{{ $rh->created_at->translatedFormat('d M Y') }}</span>
                        <span class="{{ $rh->status->badgeColor() }} rounded-full px-2 text-xs">{{ $rh->status->label() }}</span>
                    </div>
                @empty
                    <p class="mt-1 text-sm text-ink-500">Belum ada laporan lain dari pengguna ini.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-4">
            <div class="karsa-card p-4 text-center">
                <p class="text-xs text-ink-500">Nilai kepercayaan</p>
                <p class="font-display text-3xl font-bold text-forest-950">{{ number_format($report->trust_score, 0) }}</p>
            </div>

            @if(in_array($report->status->value, ['baru', 'ditinjau']))
                <div class="karsa-card space-y-2 p-4">
                    <button wire:click="verify" class="karsa-btn-primary !h-11">Verifikasi</button>
                    <textarea wire:model="rejectionReason" placeholder="Alasan penolakan..." class="w-full rounded-xl border border-leaf-100 px-3 py-2 text-sm" rows="2"></textarea>
                    <button wire:click="reject" class="w-full rounded-xl border border-risk-extreme/40 py-2.5 text-sm font-semibold text-risk-extreme">Tolak</button>
                </div>
            @endif

            @if($report->status->value === 'terverifikasi')
                <button wire:click="forward" class="karsa-btn-primary !h-11">Teruskan ke instansi</button>
            @endif

            @if($report->status->value === 'diteruskan')
                <button wire:click="complete" class="karsa-btn-primary !h-11">Tandai selesai</button>
            @endif
        </div>
    </div>
</div>
