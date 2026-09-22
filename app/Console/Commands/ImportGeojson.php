<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Regency;
use Illuminate\Console\Command;

/**
 * Impor batas wilayah dari file GeoJSON (FeatureCollection), cocokkan
 * berdasarkan kode atau nama. CLAUDE.md §6, Tahap 2.
 *
 * Properti yang dicari pada tiap feature (nama field toleran, mengikuti
 * konvensi umum data batas administrasi Indonesia/BIG):
 * - kode: kode, code, kdkab, kdkec, kode_kec, kode_kab
 * - nama: nama, name, nmkec, nmkab, wadmkc, wadmkk
 */
class ImportGeojson extends Command
{
    protected $signature = 'karsa:import-geojson {file : Path ke file GeoJSON} {--level=district : district atau regency}';

    protected $description = 'Impor batas wilayah (poligon) kecamatan/kabupaten dari file GeoJSON';

    private const CODE_FIELDS = ['kode', 'code', 'kdkab', 'kdkec', 'kode_kec', 'kode_kab', 'kode_wilayah'];

    private const NAME_FIELDS = ['nama', 'name', 'nmkec', 'nmkab', 'wadmkc', 'wadmkk', 'NAMOBJ'];

    public function handle(): int
    {
        $path = $this->argument('file');
        $level = $this->option('level');

        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        if (! in_array($level, ['district', 'regency'], true)) {
            $this->error('--level harus "district" atau "regency".');

            return self::FAILURE;
        }

        $geojson = json_decode(file_get_contents($path), true);

        if (! is_array($geojson) || ($geojson['type'] ?? null) !== 'FeatureCollection') {
            $this->error('File bukan GeoJSON FeatureCollection yang valid.');

            return self::FAILURE;
        }

        $matched = 0;
        $skipped = 0;

        foreach ($geojson['features'] as $feature) {
            $props = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? null;

            if ($geometry === null) {
                $skipped++;

                continue;
            }

            $code = $this->pick($props, self::CODE_FIELDS);
            $name = $this->pick($props, self::NAME_FIELDS);

            $model = $level === 'district'
                ? $this->findDistrict($code, $name)
                : $this->findRegency($code, $name);

            if ($model === null) {
                $this->warn('Tidak cocok: '.($name ?? $code ?? json_encode($props)));
                $skipped++;

                continue;
            }

            $centroid = $this->approximateCentroid($geometry);

            $model->update([
                'geometry' => $geometry,
                'centroid_lat' => $centroid[1] ?? $model->centroid_lat,
                'centroid_lng' => $centroid[0] ?? $model->centroid_lng,
            ]);

            $matched++;
        }

        $this->info("Selesai. Cocok & diperbarui: {$matched}. Dilewati: {$skipped}.");

        return self::SUCCESS;
    }

    private function pick(array $props, array $fields): ?string
    {
        foreach ($fields as $f) {
            if (isset($props[$f]) && $props[$f] !== '') {
                return (string) $props[$f];
            }
        }

        return null;
    }

    private function findDistrict(?string $code, ?string $name): ?District
    {
        if ($code && $district = District::where('code', $code)->first()) {
            return $district;
        }

        if ($name) {
            return District::whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($name).'%'])->first();
        }

        return null;
    }

    private function findRegency(?string $code, ?string $name): ?Regency
    {
        if ($code && $regency = Regency::where('code', $code)->first()) {
            return $regency;
        }

        if ($name) {
            return Regency::whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($name).'%'])->first();
        }

        return null;
    }

    /**
     * Hitung centroid kasar (rata-rata titik) dari geometry Polygon/MultiPolygon,
     * cukup untuk keperluan tampilan peta -- bukan centroid geodesik presisi.
     */
    private function approximateCentroid(array $geometry): ?array
    {
        $type = $geometry['type'] ?? null;
        $coords = $geometry['coordinates'] ?? [];

        $points = match ($type) {
            'Polygon' => $coords[0] ?? [],
            'MultiPolygon' => $coords[0][0] ?? [],
            default => [],
        };

        if ($points === []) {
            return null;
        }

        $lngSum = array_sum(array_column($points, 0));
        $latSum = array_sum(array_column($points, 1));
        $count = count($points);

        return [$lngSum / $count, $latSum / $count];
    }
}
