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
            ['code' => '3604010', 'name' => 'Anyar', 'lat' => -6.0537, 'lng' => 105.9288],
            ['code' => '3604020', 'name' => 'Cinangka', 'lat' => -6.1601, 'lng' => 105.8821],
            ['code' => '3604030', 'name' => 'Mancak', 'lat' => -6.0963, 'lng' => 106.0028],
            ['code' => '3604040', 'name' => 'Padarincang', 'lat' => -6.1835, 'lng' => 106.0093],
            ['code' => '3604050', 'name' => 'Ciomas', 'lat' => -6.2163, 'lng' => 106.0519],
            ['code' => '3604060', 'name' => 'Baros', 'lat' => -6.2201, 'lng' => 106.1415],
            ['code' => '3604070', 'name' => 'Pabuaran', 'lat' => -6.1722, 'lng' => 106.1018],
            ['code' => '3604080', 'name' => 'Petir', 'lat' => -6.2084, 'lng' => 106.2127],
            ['code' => '3604090', 'name' => 'Gunungsari', 'lat' => -6.1364, 'lng' => 106.0674],
            ['code' => '3604100', 'name' => 'Bandung', 'lat' => -6.2736, 'lng' => 106.2721],
            ['code' => '3604110', 'name' => 'Waringinkurung', 'lat' => -6.0683, 'lng' => 106.0652],
            ['code' => '3604120', 'name' => 'Bojonegara', 'lat' => -5.9863, 'lng' => 106.0961],
            ['code' => '3604130', 'name' => 'Pulo Ampel', 'lat' => -5.9082, 'lng' => 106.0824],
            ['code' => '3604140', 'name' => 'Kramatwatu', 'lat' => -6.0631, 'lng' => 106.1172],
            ['code' => '3604150', 'name' => 'Pontang', 'lat' => -6.0121, 'lng' => 106.2542],
            ['code' => '3604160', 'name' => 'Tirtayasa', 'lat' => -6.0215, 'lng' => 106.3024],
            ['code' => '3604170', 'name' => 'Tanara', 'lat' => -6.0232, 'lng' => 106.3615],
            ['code' => '3604180', 'name' => 'Carenang', 'lat' => -6.0754, 'lng' => 106.3451],
            ['code' => '3604190', 'name' => 'Ciruas', 'lat' => -6.1154, 'lng' => 106.2112],
            ['code' => '3604200', 'name' => 'Kragilan', 'lat' => -6.1412, 'lng' => 106.2554],
            ['code' => '3604210', 'name' => 'Kibin', 'lat' => -6.1738, 'lng' => 106.3021],
            ['code' => '3604220', 'name' => 'Binuang', 'lat' => -6.1143, 'lng' => 106.3582],
            ['code' => '3604230', 'name' => 'Kopo', 'lat' => -6.3125, 'lng' => 106.4012],
            ['code' => '3604240', 'name' => 'Cikande', 'lat' => -6.2023, 'lng' => 106.3621],
            ['code' => '3604250', 'name' => 'Jawilan', 'lat' => -6.2741, 'lng' => 106.3482],
            ['code' => '3604260', 'name' => 'Pamarayan', 'lat' => -6.2514, 'lng' => 106.2842],
            ['code' => '3604270', 'name' => 'Tunjung Teja', 'lat' => -6.2573, 'lng' => 106.2195],
            ['code' => '3604280', 'name' => 'Cikeusal', 'lat' => -6.2091, 'lng' => 106.2625],
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
