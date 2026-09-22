<div>
    <x-forest-header title="Penggalangan Dana" :back="true" />

    <div class="space-y-4 px-5 py-6">
        @if(!$donasiEnabled)
            <x-card class="bg-leaf-100/50 text-xs text-ink-500">
                Mode demonstrasi: penggalangan dana untuk umum di Indonesia memerlukan izin resmi pengumpulan uang/barang.
                Fitur donasi pada tahap ini dibatasi untuk lingkungan sekolah.
            </x-card>
        @endif

        @forelse($programs as $program)
            <a href="{{ route('program.show', $program) }}" class="karsa-card block overflow-hidden">
                <div class="flex h-32 items-center justify-center bg-leaf-100">
                    @if($program->cover)
                        <img src="{{ $program->cover }}" alt="" class="h-full w-full object-cover">
                    @else
                        <x-icon name="sprout" class="h-10 w-10 text-forest-600" />
                    @endif
                </div>
                <div class="p-4">
                    <p class="font-display font-bold text-forest-950">{{ $program->title }}</p>
                    <p class="mt-1 text-xs text-ink-500">{{ $program->starts_at?->translatedFormat('d M Y') }} · {{ $program->location }}</p>

                    @if($program->donation_enabled && $donasiEnabled)
                        <div class="mt-3">
                            <div class="h-2 rounded-full bg-leaf-100">
                                <div class="h-2 rounded-full bg-forest-600" style="width: {{ $program->donationProgressPercent() }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-ink-500">{{ $program->donationProgressPercent() }}% dari target</p>
                        </div>
                    @endif
                </div>
            </a>
        @empty
            <p class="text-center text-sm text-ink-500">Belum ada program aksi.</p>
        @endforelse
    </div>
</div>
