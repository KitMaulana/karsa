<x-layouts.public title="Kebijakan Privasi">
    <x-forest-header title="Kebijakan privasi" :back="true" />

    <div class="space-y-4 px-5 py-6 text-sm leading-relaxed text-ink-500">
        <x-card>
            <h2 class="font-display font-bold text-forest-950">Data yang kami kumpulkan</h2>
            <p class="mt-2">Nama, email atau nomor HP, kabupaten/kota tempat tinggal, lokasi saat mengirim laporan
            (GPS), foto/video laporan, dan riwayat aktivitas dalam aplikasi.</p>
        </x-card>
        <x-card>
            <h2 class="font-display font-bold text-forest-950">Tujuan penggunaan data</h2>
            <p class="mt-2">Data dipakai untuk memverifikasi laporan karhutla, menghitung tingkat risiko wilayah,
            mengirim peringatan dini, dan menyusun statistik untuk keperluan penelitian (tanpa identitas pribadi).</p>
        </x-card>
        <x-card>
            <h2 class="font-display font-bold text-forest-950">Perlindungan data pribadi</h2>
            <p class="mt-2">Identitas pelapor tidak pernah ditampilkan di peta publik. Foto yang dipublikasikan
            sudah dihapus metadata EXIF-nya. Berkas asli disimpan di penyimpanan privat dengan akses terbatas.</p>
        </x-card>
        <x-card>
            <h2 class="font-display font-bold text-forest-950">Hak pengguna</h2>
            <p class="mt-2">Anda dapat meminta penghapusan akun dan data pribadi dengan menghubungi
            {{ config('karsa.contact_email') }}.</p>
        </x-card>
    </div>
</x-layouts.public>
