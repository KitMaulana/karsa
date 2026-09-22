<?php

namespace Database\Seeders;

use App\Models\Recommendation;
use Illuminate\Database\Seeder;

class RecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Rendah
            ['level' => 'rendah', 'audience' => 'warga_umum', 'icon' => 'leaf', 'title' => 'Pantau kondisi cuaca harian', 'body' => 'Cek prakiraan cuaca dan tingkat risiko di aplikasi setiap pagi, terutama saat musim kemarau.'],
            ['level' => 'rendah', 'audience' => 'warga_umum', 'icon' => 'flame', 'title' => 'Jangan bakar sampah sembarangan', 'body' => 'Buang sampah pada tempatnya, hindari membakar sampah atau sisa tanaman di lahan terbuka.'],
            ['level' => 'rendah', 'audience' => 'petani_pekebun', 'icon' => 'sprout', 'title' => 'Siapkan lahan tanpa bakar', 'body' => 'Gunakan metode pembukaan lahan tanpa bakar (PLTB) untuk mengurangi risiko kebakaran meluas.'],
            ['level' => 'rendah', 'audience' => 'sekolah', 'icon' => 'book-open', 'title' => 'Kenalkan bahaya karhutla ke siswa', 'body' => 'Sisipkan materi kesadaran lingkungan tentang dampak kebakaran hutan dan lahan dalam kegiatan belajar.'],

            // Sedang
            ['level' => 'sedang', 'audience' => 'warga_umum', 'icon' => 'droplets', 'title' => 'Siapkan sumber air di sekitar rumah', 'body' => 'Pastikan ember, tandon, atau sumber air lain mudah diakses untuk pemadaman awal bila diperlukan.'],
            ['level' => 'sedang', 'audience' => 'warga_umum', 'icon' => 'phone', 'title' => 'Simpan nomor darurat', 'body' => 'Simpan nomor 112, BPBD, dan Manggala Agni setempat di ponsel Anda.'],
            ['level' => 'sedang', 'audience' => 'petani_pekebun', 'icon' => 'shield', 'title' => 'Buat sekat bakar di batas lahan', 'body' => 'Bersihkan vegetasi kering di sekitar batas lahan untuk mencegah api menjalar dari lahan tetangga.'],
            ['level' => 'sedang', 'audience' => 'sekolah', 'icon' => 'wind', 'title' => 'Siapkan masker cadangan', 'body' => 'Sediakan masker di sekolah untuk mengantisipasi kabut asap yang mulai muncul.'],

            // Tinggi
            ['level' => 'tinggi', 'audience' => 'warga_umum', 'icon' => 'alert-triangle', 'title' => 'Waspada penuh, laporkan titik api', 'body' => 'Segera laporkan lewat aplikasi KARSA bila melihat asap atau api, sertakan foto dan lokasi.'],
            ['level' => 'tinggi', 'audience' => 'warga_umum', 'icon' => 'wind', 'title' => 'Kurangi aktivitas luar ruangan', 'body' => 'Batasi aktivitas di luar rumah terutama bagi anak-anak, lansia, dan penderita gangguan pernapasan.'],
            ['level' => 'tinggi', 'audience' => 'petani_pekebun', 'icon' => 'flame', 'title' => 'Hentikan sementara pembakaran apa pun', 'body' => 'Tunda seluruh kegiatan yang melibatkan api di lahan hingga risiko menurun.'],
            ['level' => 'tinggi', 'audience' => 'sekolah', 'icon' => 'school', 'title' => 'Siapkan rencana kegiatan dalam ruangan', 'body' => 'Siapkan opsi memindahkan kegiatan luar ruangan ke dalam ruangan bila kabut asap memburuk.'],

            // Sangat tinggi
            ['level' => 'sangat_tinggi', 'audience' => 'warga_umum', 'icon' => 'siren', 'title' => 'Ikuti arahan evakuasi petugas', 'body' => 'Patuhi instruksi BPBD/petugas setempat, siapkan tas siaga berisi dokumen penting dan kebutuhan darurat.'],
            ['level' => 'sangat_tinggi', 'audience' => 'warga_umum', 'icon' => 'phone-call', 'title' => 'Hubungi 112 untuk keadaan darurat', 'body' => 'Segera hubungi 112 atau BPBD setempat bila api mendekati permukiman.'],
            ['level' => 'sangat_tinggi', 'audience' => 'petani_pekebun', 'icon' => 'shield-alert', 'title' => 'Amankan ternak dan alat pertanian', 'body' => 'Pindahkan ternak dan peralatan penting ke lokasi yang lebih aman dari jangkauan api.'],
            ['level' => 'sangat_tinggi', 'audience' => 'sekolah', 'icon' => 'school', 'title' => 'Pertimbangkan meliburkan kegiatan luar ruangan', 'body' => 'Koordinasikan dengan dinas pendidikan setempat terkait kemungkinan penyesuaian jadwal sekolah.'],
        ];

        foreach ($items as $i => $item) {
            Recommendation::updateOrCreate(
                ['level' => $item['level'], 'audience' => $item['audience'], 'title' => $item['title']],
                [...$item, 'order' => $i]
            );
        }
    }
}
