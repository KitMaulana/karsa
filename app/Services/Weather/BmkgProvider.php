<?php

namespace App\Services\Weather;

use App\Models\DataSyncLog;
use App\Models\District;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * BMKG (pelengkap, prakiraan resmi). Butuh kode wilayah administrasi
 * tingkat IV (adm4) per kecamatan, kolom districts.bmkg_adm4.
 * Kecamatan tanpa bmkg_adm4 dilewati (Open-Meteo tetap jadi sumber utama).
 */
class BmkgProvider implements WeatherProvider
{
    public function key(): string
    {
        return 'bmkg';
    }

    public function fetchMany(array $points): Collection
    {
        $baseUrl = config('karsa.weather.bmkg.base_url');
        $result = collect();
        $failures = 0;

        foreach ($points as $point) {
            $adm4 = $point['bmkg_adm4'] ?? null;

            if (blank($adm4)) {
                continue;
            }

            try {
                $response = Http::timeout(config('karsa.weather.timeout_seconds', 10))
                    ->retry(config('karsa.weather.retry_times', 2), 500)
                    ->get($baseUrl, ['adm4' => $adm4]);

                if (! $response->successful()) {
                    $failures++;

                    continue;
                }

                $observations = $this->parseForecast($response->json());

                $result->push([
                    'district_id' => $point['id'],
                    'observations' => $observations,
                ]);

                // BMKG membatasi laju permintaan; beri jeda kecil antar kecamatan.
                usleep(300_000);
            } catch (Throwable) {
                $failures++;
            }
        }

        DataSyncLog::log('bmkg', $failures === 0 ? 'sukses' : 'sebagian', $result->count(), $failures > 0 ? "{$failures} kecamatan gagal" : null);

        return $result;
    }

    private function parseForecast(array $json): Collection
    {
        $entries = $json['data'][0]['cuaca'] ?? [];
        $byDate = [];

        foreach ($entries as $group) {
            foreach ($group as $item) {
                $date = Carbon::parse($item['local_datetime'] ?? $item['datetime'] ?? now())->toDateString();
                $byDate[$date]['temp'][] = $item['t'] ?? null;
                $byDate[$date]['humidity'][] = $item['hu'] ?? null;
                $byDate[$date]['wind'][] = $item['ws'] ?? null;
            }
        }

        return collect($byDate)->map(function (array $day, string $date) {
            $temps = array_filter($day['temp'] ?? []);
            $winds = array_filter($day['wind'] ?? []);

            return new WeatherObservationDTO(
                observedAt: Carbon::parse($date, 'Asia/Jakarta'),
                tempMax: $temps !== [] ? max($temps) : null,
                rhMin: null,
                windMax: $winds !== [] ? max($winds) : null,
                rainMm: null,
                source: 'bmkg',
                isForecast: true,
            );
        })->values();
    }
}
