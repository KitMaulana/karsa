<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Hotspot;
use App\Services\Geo\PointInPolygon;
use App\Services\Hotspot\BBox;
use App\Services\Hotspot\FirmsProvider;
use App\Services\Hotspot\HotspotAggregator;
use App\Services\Hotspot\HotspotProvider;
use App\Services\Hotspot\HotspotProviderException;
use App\Services\Hotspot\SipongiProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sinkronkan hotspot dari semua sumber yang terkonfigurasi (SiPongi+, FIRMS),
 * gabungkan & dedup, lalu tentukan district_id. CLAUDE.md §8, §17.
 *
 * Kegagalan satu sumber TIDAK menghentikan proses -- sumber lain tetap
 * dicoba (fusi multi-sumber §8.3), dan bila semua gagal, data hotspot lama
 * tetap dipertahankan (tidak dihapus) sehingga halaman publik tetap
 * menampilkan data terakhir + label "Data terakhir diperbarui...".
 */
class SyncHotspots extends Command
{
    protected $signature = 'karsa:sync-hotspots {--hours=24}';

    protected $description = 'Sinkronkan data hotspot dari SiPongi+ dan/atau NASA FIRMS';

    public function handle(): int
    {
        $hoursBack = (int) $this->option('hours');

        $districts = District::where('is_monitored', true)->get();

        if ($districts->isEmpty()) {
            $this->warn('Tidak ada kecamatan yang dipantau (is_monitored=true).');

            return self::SUCCESS;
        }

        $points = $districts->map(fn (District $d) => [$d->centroid_lat, $d->centroid_lng])->filter(fn ($p) => $p[0] && $p[1])->values()->all();

        if ($points === []) {
            $this->warn('Tidak ada kecamatan dengan centroid yang valid.');

            return self::SUCCESS;
        }

        $bufferDegrees = ((float) config('karsa.risk.buffer_km', 5)) / 111; // ~perkiraan derajat per km
        $bbox = BBox::fromCenters($points, paddingDegrees: max(0.2, $bufferDegrees));

        $priority = config('karsa.hotspot.provider_priority', ['sipongi', 'firms']);
        $providers = [
            'sipongi' => fn () => new SipongiProvider,
            'firms' => fn () => new FirmsProvider,
        ];

        $bySource = [];

        foreach ($priority as $key) {
            if (! isset($providers[$key])) {
                continue;
            }

            /** @var HotspotProvider $provider */
            $provider = $providers[$key]();

            try {
                $items = $provider->fetch($bbox, $hoursBack);
                $bySource[$key] = $items;
                $this->info("{$key}: {$items->count()} hotspot diterima.");
            } catch (HotspotProviderException $e) {
                $this->warn("{$key} gagal: {$e->getMessage()}");
                Log::warning("SyncHotspots: {$key} gagal", ['error' => $e->getMessage()]);
            }
        }

        if ($bySource === []) {
            $this->error('Semua sumber hotspot gagal. Data lama dipertahankan.');

            return self::FAILURE;
        }

        $merged = HotspotAggregator::fromConfig()->merge($bySource);

        $districtsWithGeometry = $districts->filter(fn (District $d) => ! empty($d->geometry));
        $saved = 0;

        foreach ($merged as $entry) {
            $dto = $entry['dto'];
            $districtId = $this->resolveDistrict($dto->lat, $dto->lng, $districts, $districtsWithGeometry);

            Hotspot::updateOrCreate(
                ['external_id' => $dto->externalId, 'detected_at' => $dto->detectedAt],
                [
                    'sources' => $entry['sources'],
                    'satellite' => $dto->satellite,
                    'lat' => $dto->lat,
                    'lng' => $dto->lng,
                    'confidence' => $dto->confidence->value,
                    'confidence_raw' => is_scalar($dto->confidenceRaw) ? (string) $dto->confidenceRaw : null,
                    'frp' => $dto->frp,
                    'district_id' => $districtId,
                    'corroborated' => $entry['corroborated'],
                    'raw' => $dto->raw,
                ]
            );
            $saved++;
        }

        $this->info("Selesai. {$saved} hotspot tersimpan/diperbarui (setelah dedup dari ".array_sum(array_map(fn ($c) => $c->count(), $bySource))." mentah).");

        return self::SUCCESS;
    }

    /**
     * Tentukan district_id: uji poligon dulu bila geometry tersedia (via
     * bbox lalu ray-casting), fallback ke kecamatan terdekat dalam radius
     * buffer bila tidak ada poligon (data belum diimpor, lihat karsa:import-geojson).
     */
    private function resolveDistrict(float $lat, float $lng, $allDistricts, $withGeometry): ?int
    {
        foreach ($withGeometry as $district) {
            if (PointInPolygon::test($lat, $lng, $district->geometry)) {
                return $district->id;
            }
        }

        $bufferKm = (float) config('karsa.risk.buffer_km', 5);
        $nearest = null;
        $nearestDistance = null;

        foreach ($allDistricts as $district) {
            if (! $district->centroid_lat || ! $district->centroid_lng) {
                continue;
            }

            $distance = $this->haversineKm($lat, $lng, (float) $district->centroid_lat, (float) $district->centroid_lng);

            if ($distance <= $bufferKm && ($nearestDistance === null || $distance < $nearestDistance)) {
                $nearest = $district;
                $nearestDistance = $distance;
            }
        }

        return $nearest?->id;
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $h = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($h)));
    }
}
