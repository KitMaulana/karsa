<?php

namespace App\Services\Hotspot;

use App\Enums\HotspotConfidence;
use App\Models\DataSyncLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Adapter NASA FIRMS (Fire Information for Resource Management System).
 * API resmi & terdokumentasi: https://firms.modaps.eosdis.nasa.gov/api/
 */
class FirmsProvider implements HotspotProvider
{
    public function key(): string
    {
        return 'firms';
    }

    public function fetch(BBox $bbox, int $hoursBack = 24): Collection
    {
        $mapKey = config('karsa.hotspot.firms.map_key');

        if (blank($mapKey)) {
            throw new HotspotProviderException('FIRMS_MAP_KEY belum diatur di .env.');
        }

        $sources = config('karsa.hotspot.firms.sources');
        $dayRange = config('karsa.hotspot.firms.day_range', 1);
        $baseUrl = config('karsa.hotspot.firms.base_url');

        $items = collect();
        $failures = [];

        foreach ($sources as $source) {
            $url = sprintf(
                '%s/%s/%s/%s/%d',
                $baseUrl,
                $mapKey,
                $source,
                $bbox->toFirmsPath(),
                $dayRange
            );

            try {
                $response = Http::timeout(config('karsa.hotspot.timeout_seconds', 10))
                    ->retry(config('karsa.hotspot.retry_times', 2), 500)
                    ->get($url);

                if (! $response->successful()) {
                    $failures[] = "{$source}: HTTP {$response->status()}";

                    continue;
                }

                $items = $items->merge($this->parseCsv($response->body(), $source));
            } catch (Throwable $e) {
                $failures[] = "{$source}: ".$e->getMessage();
            }
        }

        if ($items->isEmpty() && $failures !== []) {
            DataSyncLog::log('firms', 'gagal', 0, implode('; ', $failures));
            throw new HotspotProviderException('Semua sumber FIRMS gagal: '.implode('; ', $failures));
        }

        DataSyncLog::log('firms', $failures === [] ? 'sukses' : 'sebagian', $items->count(), implode('; ', $failures) ?: null);

        $filtered = $items->filter(fn (HotspotDTO $dto) => $bbox->contains($dto->lat, $dto->lng))->values();

        return $filtered;
    }

    private function parseCsv(string $csv, string $source): Collection
    {
        $lines = array_filter(array_map('trim', explode("\n", $csv)));

        if (count($lines) < 2) {
            return collect();
        }

        $header = str_getcsv(array_shift($lines));
        $header = array_map('strtolower', $header);

        $isModis = str_starts_with($source, 'MODIS');

        return collect($lines)->map(function (string $line) use ($header, $source, $isModis) {
            $cols = str_getcsv($line);

            if (count($cols) !== count($header)) {
                return null;
            }

            $row = array_combine($header, $cols);

            $lat = (float) ($row['latitude'] ?? 0);
            $lng = (float) ($row['longitude'] ?? 0);

            if ($lat === 0.0 && $lng === 0.0) {
                return null;
            }

            $confRaw = $row['confidence'] ?? null;
            $confidence = $isModis
                ? HotspotConfidence::fromRaw(is_numeric($confRaw) ? (float) $confRaw : null)
                : HotspotConfidence::fromRaw($confRaw);

            $acqDate = $row['acq_date'] ?? null;
            $acqTime = str_pad((string) ($row['acq_time'] ?? '0000'), 4, '0', STR_PAD_LEFT);

            try {
                $detectedAt = Carbon::createFromFormat(
                    'Y-m-d Hi',
                    "{$acqDate} {$acqTime}",
                    'UTC'
                );
            } catch (Throwable) {
                $detectedAt = Carbon::now('UTC');
            }

            return new HotspotDTO(
                source: 'firms',
                satellite: $row['satellite'] ?? $source,
                lat: $lat,
                lng: $lng,
                confidence: $confidence,
                confidenceRaw: $confRaw,
                frp: is_numeric($row['frp'] ?? null) ? (float) $row['frp'] : null,
                detectedAt: $detectedAt,
                externalId: md5($source.$lat.$lng.$acqDate.$acqTime),
                raw: $row,
            );
        })->filter()->values();
    }
}
