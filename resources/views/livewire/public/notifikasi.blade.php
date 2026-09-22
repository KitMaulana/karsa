<div x-data="{ showInstall: false, isIos: /iPad|iPhone|iPod/.test(navigator.userAgent) }"
     x-init="window.addEventListener('karsa-install-available', () => showInstall = true)">
    <x-forest-header title="Notifikasi" :back="true" />

    <div class="px-5 py-6">
        <x-card class="mb-4" x-show="showInstall || isIos" x-cloak>
            <p class="text-sm text-ink-500">Pasang KARSA ke layar utama agar lebih cepat diakses dan notifikasi bekerja optimal.</p>
            <button type="button" x-show="showInstall" @click="window.karsaPromptInstall()" class="karsa-btn-primary mt-3">Pasang aplikasi</button>
            <p class="mt-2 text-xs text-ink-500" x-show="isIos">
                Di iPhone/iPad: ketuk tombol Bagikan <span aria-hidden="true">⬆️</span> di Safari, lalu pilih "Tambah ke Layar Utama".
            </p>
        </x-card>

        @if(!Auth::user()->pushSubscriptions()->exists())
            <x-card class="mb-4">
                <p class="text-sm text-ink-500">Aktifkan notifikasi push agar Anda mendapat peringatan segera saat risiko di wilayah pantauan naik.</p>
                <button type="button" onclick="window.karsaEnablePush && window.karsaEnablePush()" class="karsa-btn-primary mt-3">Aktifkan notifikasi</button>
                <p class="mt-2 text-xs text-ink-500">Pengguna iPhone: pasang KARSA ke layar utama terlebih dahulu (Bagikan → Tambah ke Layar Utama) agar notifikasi bisa aktif (iOS 16.4+).</p>
            </x-card>
        @endif

        <div class="space-y-2.5">
            @forelse($notifications as $n)
                <div class="flex items-start gap-3 rounded-2xl border border-leaf-100 bg-white px-4 py-3 {{ $n->read_at ? 'opacity-60' : '' }}">
                    <x-icon name="bell" class="mt-0.5 h-5 w-5 text-forest-800" />
                    <div class="flex-1">
                        <p class="text-sm text-forest-950">{{ $n->data['message'] ?? 'Notifikasi' }}</p>
                        <p class="mt-0.5 text-xs text-ink-500">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-ink-500">Belum ada notifikasi.</p>
            @endforelse
        </div>
    </div>
</div>
