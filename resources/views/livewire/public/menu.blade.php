<div>
    <x-forest-header title="KARSA">
        <p class="text-sm text-white/80">Satu aplikasi untuk memantau &amp; melindungi hutan kita.</p>
    </x-forest-header>

    <div class="-mt-6 space-y-5 px-5 pb-6">
        @if($myDistrict && $myRiskScore)
            <x-card class="relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-ink-500">Risiko wilayah Anda hari ini</p>
                        <p class="font-display font-bold text-forest-950">{{ $myDistrict->name }}</p>
                    </div>
                    <x-risk-pill :level="$myRiskScore->level" />
                </div>
                <div class="mt-3 flex items-end gap-1">
                    <span class="font-display text-3xl font-extrabold text-forest-950">{{ number_format($myRiskScore->score, 0) }}</span>
                    <span class="mb-1 text-sm text-ink-500">/100</span>
                </div>
                <a href="{{ route('wilayah.show', $myDistrict) }}" class="mt-3 inline-block text-sm font-semibold text-forest-800">Lihat detail wilayah →</a>
            </x-card>
        @elseif(!auth()->check())
            <x-card class="relative z-10 text-center">
                <p class="text-sm text-ink-500">Masuk untuk memantau risiko wilayah Anda dan mengirim laporan.</p>
                <a href="{{ route('login') }}" class="mt-3 inline-block font-semibold text-forest-800">Masuk sekarang →</a>
            </x-card>
        @else
            <x-card class="relative z-10 text-center">
                <p class="text-sm text-ink-500">Pilih wilayah pantauan Anda di halaman Profil untuk melihat ringkasan risiko di sini.</p>
                <a href="{{ route('profil') }}" class="mt-3 inline-block font-semibold text-forest-800">Atur wilayah pantauan →</a>
            </x-card>
        @endif

        <div class="space-y-2.5">
            @foreach($items as $item)
                <x-menu-item :href="route($item['route'])" :icon="$item['icon']" :title="$item['title']" :subtitle="$item['subtitle']" />
            @endforeach
        </div>
    </div>
</div>
