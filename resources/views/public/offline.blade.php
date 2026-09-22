<x-layouts.public title="Offline">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 text-center">
        <x-icon name="cloud" class="h-16 w-16 text-ink-500" />
        <h1 class="mt-4 font-display text-lg font-bold text-forest-950">Anda sedang offline</h1>
        <p class="mt-2 text-sm text-ink-500">Halaman ini belum tersimpan untuk dibuka tanpa koneksi internet. Data
        terakhir tetap tersedia di halaman yang sudah pernah dibuka.</p>
        <button onclick="window.location.reload()" class="karsa-btn-primary mt-6 max-w-[200px]">Coba lagi</button>
    </div>
</x-layouts.public>
