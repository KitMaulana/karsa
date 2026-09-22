<?php

namespace Database\Seeders;

use App\Models\Regency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seluruh kabupaten/kota Provinsi Banten (8 wilayah tingkat II).
 * Sumber: Kepmendagri 100.1.1-6117 Tahun 2022 (kode & nama wilayah administrasi Pemerintahan).
 * Kabupaten Serang mendapat data kecamatan lengkap (lihat DistrictSeeder);
 * wilayah lain hanya diisi centroid perkiraan sebagai referensi peta -- BELUM
 * dipantau (is_monitored di level kecamatan tidak berlaku di sini).
 */
class RegencySeeder extends Seeder
{
    public function run(): void
    {
        $regencies = [
            ['code' => '3601', 'name' => 'Kabupaten Pandeglang', 'lat' => -6.3084, 'lng' => 105.9694],
            ['code' => '3602', 'name' => 'Kabupaten Lebak', 'lat' => -6.5617, 'lng' => 106.2500],
            ['code' => '3603', 'name' => 'Kabupaten Tangerang', 'lat' => -6.1783, 'lng' => 106.6319],
            ['code' => '3604', 'name' => 'Kabupaten Serang', 'lat' => -6.1200, 'lng' => 106.1503],
            ['code' => '3671', 'name' => 'Kota Tangerang', 'lat' => -6.1783, 'lng' => 106.6319],
            ['code' => '3672', 'name' => 'Kota Cilegon', 'lat' => -6.0025, 'lng' => 106.0113],
            ['code' => '3673', 'name' => 'Kota Serang', 'lat' => -6.1149, 'lng' => 106.1503],
            ['code' => '3674', 'name' => 'Kota Tangerang Selatan', 'lat' => -6.2884, 'lng' => 106.7180],
        ];

        foreach ($regencies as $r) {
            Regency::updateOrCreate(
                ['code' => $r['code']],
                [
                    'province' => 'Banten',
                    'name' => $r['name'],
                    'slug' => Str::slug($r['name']),
                    'centroid_lat' => $r['lat'],
                    'centroid_lng' => $r['lng'],
                ]
            );
        }
    }
}
