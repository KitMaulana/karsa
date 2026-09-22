<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'category' => 'edukasi',
                'title' => 'Mengenal titik panas (hotspot) dan bedanya dengan titik api',
                'excerpt' => 'Hotspot adalah indikasi awal, bukan kepastian adanya kebakaran. Kenali perbedaannya agar tidak salah paham.',
                'body' => "<p>Titik panas (hotspot) adalah area yang terdeteksi satelit memiliki suhu permukaan jauh lebih tinggi dari sekitarnya. Hotspot <strong>belum tentu</strong> menandakan adanya api aktif -- bisa juga berasal dari pantulan panas industri, lahan terbuka yang sangat panas, atau gangguan sensor.</p><p>KARSA menggabungkan data hotspot dari beberapa sumber (SiPongi+ dan NASA FIRMS) serta laporan warga terverifikasi untuk memberi gambaran risiko yang lebih akurat, bukan sekadar jumlah titik panas mentah.</p>",
            ],
            [
                'category' => 'edukasi',
                'title' => 'Kenapa membuka lahan tanpa membakar itu penting?',
                'excerpt' => 'Pembakaran lahan adalah penyebab utama karhutla di banyak wilayah. Ini alternatif yang lebih aman.',
                'body' => '<p>Pembukaan lahan tanpa bakar (PLTB) menggunakan metode mekanis atau bahan organik untuk mengolah sisa tanaman, tanpa risiko api menjalar ke area yang lebih luas. Metode ini lebih ramah lingkungan dan mengurangi emisi karbon dibanding pembakaran terbuka.</p>',
            ],
            [
                'category' => 'berita',
                'title' => 'KARSA resmi diperkenalkan sebagai purwarupa sistem peringatan dini karhutla',
                'excerpt' => 'Tim siswa SMA Negeri 1 Ciruas memperkenalkan KARSA sebagai model analisis risiko dan peringatan dini kebakaran hutan dan lahan.',
                'body' => '<p>KARSA (Kawasan Analisis Risiko dan Siaga) dikembangkan sebagai purwarupa karya tulis ilmiah yang menggabungkan data hotspot, cuaca, dan partisipasi masyarakat untuk membantu masyarakat memantau dan mencegah kebakaran hutan dan lahan di wilayah mereka.</p>',
            ],
            [
                'category' => 'berita',
                'title' => 'Wapala SMAN 1 Ciruas ajak warga tanam pohon bersama',
                'excerpt' => 'Kegiatan penanaman pohon digelar sebagai bagian dari program pelestarian lingkungan dan pencegahan karhutla.',
                'body' => '<p>Wadah Pecinta Alam (Wapala) SMA Negeri 1 Ciruas mengajak warga sekitar untuk ikut serta dalam kegiatan penanaman pohon di wilayah rawan karhutla. Kegiatan ini menjadi bagian dari program aksi berkelanjutan KARSA.</p>',
            ],
        ];

        foreach ($posts as $p) {
            Post::updateOrCreate(
                ['slug' => Str::slug($p['title'])],
                [
                    ...$p,
                    'slug' => Str::slug($p['title']),
                    'status' => 'terbit',
                    'published_at' => now()->subDays(random_int(1, 20)),
                ]
            );
        }
    }
}
