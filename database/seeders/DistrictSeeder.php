<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Regency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seluruh kecamatan Kabupaten Serang.
 *
 * PERLU DIVERIFIKASI: daftar 28 kecamatan di bawah disusun dari pengetahuan
 * umum penulis (bukan hasil pengambilan langsung dari BPS/Kemendagri), dan
 * titik centroid_lat/centroid_lng adalah PERKIRAAN KASAR posisi kecamatan
 * (bukan hasil hitung dari poligon resmi). Sebelum dipakai untuk KTI:
 * 1) cocokkan nama & jumlah kecamatan dengan data BPS Kabupaten Serang
 *    (Kabupaten Serang Dalam Angka, https://serangkab.bps.go.id) atau
 *    Kepmendagri kode wilayah administrasi terbaru;
 * 2) impor batas & titik pusat resmi lewat `php artisan karsa:import-geojson`
 *    (lihat CLAUDE.md §6 & §16) begitu file GeoJSON kecamatan tersedia --
 *    lihat instruksi sumber data di akhir Tahap 2.
 */
class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $regency = Regency::where('code', '3604')->firstOrFail(); // Kabupaten Serang

        $districts = [
            ['code' => '3604010', 'name' => 'Anyar', 'lat' => -6.3382, 'lng' => 105.9036],
            ['code' => '3604020', 'name' => 'Cinangka', 'lat' => -6.2853, 'lng' => 105.8814],
            ['code' => '3604030', 'name' => 'Mancak', 'lat' => -6.2378, 'lng' => 105.9928],
            ['code' => '3604040', 'name' => 'Padarincang', 'lat' => -6.2038, 'lng' => 106.0561],
            ['code' => '3604050', 'name' => 'Ciomas', 'lat' => -6.2634, 'lng' => 106.0819],
            ['code' => '3604060', 'name' => 'Baros', 'lat' => -6.2246, 'lng' => 106.1789],
            ['code' => '3604070', 'name' => 'Pabuaran', 'lat' => -6.1745, 'lng' => 106.1523],
            ['code' => '3604080', 'name' => 'Petir', 'lat' => -6.1974, 'lng' => 106.2036],
            ['code' => '3604090', 'name' => 'Gunungsari', 'lat' => -6.2356, 'lng' => 106.1453],
            ['code' => '3604100', 'name' => 'Bandung', 'lat' => -6.2105, 'lng' => 106.2115],
            ['code' => '3604110', 'name' => 'Waringinkurung', 'lat' => -6.0169, 'lng' => 106.0575],
            ['code' => '3604120', 'name' => 'Bojonegara', 'lat' => -5.9427, 'lng' => 106.0578],
            ['code' => '3604130', 'name' => 'Pulo Ampel', 'lat' => -5.9466, 'lng' => 106.1136],
            ['code' => '3604140', 'name' => 'Kramatwatu', 'lat' => -6.0163, 'lng' => 106.1058],
            ['code' => '3604150', 'name' => 'Pontang', 'lat' => -6.0186, 'lng' => 106.2126],
            ['code' => '3604160', 'name' => 'Tirtayasa', 'lat' => -5.9862, 'lng' => 106.2456],
            ['code' => '3604170', 'name' => 'Tanara', 'lat' => -6.0236, 'lng' => 106.2839],
            ['code' => '3604180', 'name' => 'Carenang', 'lat' => -6.0517, 'lng' => 106.2879],
            ['code' => '3604190', 'name' => 'Ciruas', 'lat' => -6.0812, 'lng' => 106.1572],
            ['code' => '3604200', 'name' => 'Kragilan', 'lat' => -6.0611, 'lng' => 106.2192],
            ['code' => '3604210', 'name' => 'Kibin', 'lat' => -6.1069, 'lng' => 106.2352],
            ['code' => '3604220', 'name' => 'Binuang', 'lat' => -6.1527, 'lng' => 106.2427],
            ['code' => '3604230', 'name' => 'Kopo', 'lat' => -6.1057, 'lng' => 106.2688],
            ['code' => '3604240', 'name' => 'Cikande', 'lat' => -6.1402, 'lng' => 106.2896],
            ['code' => '3604250', 'name' => 'Jawilan', 'lat' => -6.1663, 'lng' => 106.2669],
            ['code' => '3604260', 'name' => 'Pamarayan', 'lat' => -6.1478, 'lng' => 106.3151],
            ['code' => '3604270', 'name' => 'Tunjung Teja', 'lat' => -6.1758, 'lng' => 106.2381],
            ['code' => '3604280', 'name' => 'Cikeusal', 'lat' => -6.1949, 'lng' => 106.2807],
        ];

        foreach ($districts as $d) {
            District::updateOrCreate(
                ['code' => $d['code']],
                [
                    'regency_id' => $regency->id,
                    'name' => 'Kec. '.$d['name'],
                    'slug' => Str::slug($d['name']),
                    'centroid_lat' => $d['lat'],
                    'centroid_lng' => $d['lng'],
                    'vulnerability_score' => 30,
                    'vulnerability_factors' => [
                        'tutupan_lahan' => 30,
                        'lahan_gambut' => 10,
                        'riwayat_kebakaran' => 20,
                        'jarak_permukiman' => 40,
                        'akses_pemadam' => 40,
                    ],
                    'is_monitored' => true,
                ]
            );
        }
    }
}
