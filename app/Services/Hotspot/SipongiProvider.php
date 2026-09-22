<?php

namespace App\Services\Hotspot;

use App\Enums\HotspotConfidence;
use App\Models\DataSyncLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Adapter untuk laman peta SiPongi+ (KLHK/Kemenhut).
 *
 * SiPongi+ adalah SPA tanpa API publik terdokumentasi (lihat CLAUDE.md §8.1).
 * URL endpoint & parameter WAJIB diisi lewat .env setelah diverifikasi manual
 * via DevTools -> Network -> Fetch/XHR pada https://sipongi.gakkum.kehutanan.go.id/peta.
 *
 * Parser di bawah ini toleran terhadap beberapa kemungkinan nama field karena
 * format endpoint internal bisa berubah sewaktu-waktu tanpa pemberitahuan.
 */
class SipongiProvider implements HotspotProvider
{
    /** Kemungkinan nama field, diurutkan dari yang paling mungkin. */
    private const FIELD_MAP = [
        'lat' => ['lat', 'latitude', 'y', 'Lat', 'LATITUDE'],
        'lng' => ['lng', 'lon', 'long', 'longitude', 'x', 'Lon', 'LONGITUDE'],
        'confidence' => ['conf', 'confidence', 'kepercayaan', 'Conf', 'CONFIDENCE'],
        'satellite' => ['sat', 'satellite', 'satelit', 'Sat', 'SATELLITE'],
        'frp' => ['frp', 'FRP', 'fire_radiative_power'],
        'detected_at' => ['tanggal', 'date', 'tgl', 'acq_date', 'waktu', 'datetime', 'hotspot_date'],
        'time' => ['jam', 'time', 'acq_time'],
        'id' => ['id', 'ID', 'objectid', 'OBJECTID', 'gid'],
    ];

    public function key(): string
    {
        return 'sipongi';
    }

    public function fetch(BBox $bbox, int $hoursBack = 24): Collection
    {
        $url = config('karsa.hotspot.sipongi.url');

        if (blank($url)) {
            throw new HotspotProviderException('SIPONGI_HOTSPOT_URL belum diatur di .env.');
        }

        $params = $this->parseExtraParams(config('karsa.hotspot.sipongi.extra_params'));

        try {
            $response = Http::withHeaders([
                'User-Agent' => config('karsa.hotspot.sipongi.user_agent'),
                'Accept' => 'application/json',
            ])
                ->timeout(config('karsa.hotspot.timeout_seconds', 10))
                ->retry(config('karsa.hotspot.retry_times', 2), 500)
                ->get($url, $params);

            if (! $response->successful()) {
                throw new HotspotProviderException("SiPongi+ HTTP {$response->status()}");
            }

            $rows = $this->extractRows($response->json());
        } catch (Throwable $e) {
            DataSyncLog::log('sipongi', 'gagal', 0, $e->getMessage());
            throw new HotspotProviderException('Gagal mengambil data SiPongi+: '.$e->getMessage(), previous: $e);
        }

        $items = collect($rows)
            ->map(fn (array $row) => $this->mapRow($row))
            ->filter()
            ->filter(fn (HotspotDTO $dto) => $bbox->contains($dto->lat, $dto->lng))
            ->values();

        DataSyncLog::log('sipongi', 'sukses', $items->count());

        return $items;
    }

    private function parseExtraParams(?string $raw): array
    {
        if (blank($raw)) {
            return [];
        }

        parse_str($raw, $params);

        return $params;
    }

    /**
     * Endpoint bisa mengembalikan array langsung, atau dibungkus dalam
     * {data: [...]}, {features: [...]} (GeoJSON), atau {result: {data: [...]}}.
     */
    private function extractRows(mixed $json): array
    {
        if (is_array($json) && array_is_list($json)) {
            return $json;
        }

        if (! is_array($json)) {
            return [];
        }

        if (isset($json['features']) && is_array($json['features'])) {
            return array_map(function (array $feature) {
                $props = $feature['properties'] ?? [];
                $coords = $feature['geometry']['coordinates'] ?? null;
                if (is_array($coords) && count($coords) >= 2) {
                    $props['lng'] ??= $coords[0];
                    $props['lat'] ??= $coords[1];
                }

                return $props;
            }, $json['features']);
        }

        foreach (['data', 'result', 'rows', 'items', 'hotspots'] as $key) {
            if (isset($json[$key]) && is_array($json[$key])) {
                return array_is_list($json[$key]) ? $json[$key] : $this->extractRows($json[$key]);
            }
        }

        return [];
    }

    private function mapRow(array $row): ?HotspotDTO
    {
        $lat = $this->pick($row, 'lat');
        $lng = $this->pick($row, 'lng');

        if ($lat === null || $lng === null || ! is_numeric($lat) || ! is_numeric($lng)) {
            Log::warning('SipongiProvider: baris dilewati, lat/lng tidak ditemukan atau tidak valid', ['row' => $row]);

            return null;
        }

        $dateRaw = $this->pick($row, 'detected_at');
        $timeRaw = $this->pick($row, 'time');

        try {
            $detectedAt = $this->parseDateTime($dateRaw, $timeRaw);
        } catch (Throwable) {
            $detectedAt = Carbon::now('UTC');
        }

        $confRaw = $this->pick($row, 'confidence');

        return new HotspotDTO(
            source: 'sipongi',
            satellite: $this->pick($row, 'satellite') !== null ? (string) $this->pick($row, 'satellite') : null,
            lat: (float) $lat,
            lng: (float) $lng,
            confidence: HotspotConfidence::fromRaw($confRaw),
            confidenceRaw: $confRaw,
            frp: is_numeric($this->pick($row, 'frp')) ? (float) $this->pick($row, 'frp') : null,
            detectedAt: $detectedAt,
            externalId: (string) ($this->pick($row, 'id') ?? md5(json_encode($row))),
            raw: $row,
        );
    }

    private function pick(array $row, string $field): mixed
    {
        foreach (self::FIELD_MAP[$field] as $name) {
            if (array_key_exists($name, $row) && $row[$name] !== null && $row[$name] !== '') {
                return $row[$name];
            }
        }

        return null;
    }

    private function parseDateTime(mixed $date, mixed $time): Carbon
    {
        if ($date === null) {
            return Carbon::now('UTC');
        }

        $dateStr = trim((string) $date);

        if ($time !== null && ! str_contains($dateStr, ':')) {
            $dateStr .= ' '.trim((string) $time);
        }

        return Carbon::parse($dateStr, 'Asia/Jakarta')->utc();
    }
}
