<?php

namespace App\Services\Weather;

use App\Models\DataSyncLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Open-Meteo (utama, tanpa kunci API). Mendukung banyak koordinat sekaligus
 * dalam satu request (parameter latitude/longitude dipisah koma), dipakai
 * untuk menghemat jumlah permintaan (CLAUDE.md §8.5 & §17).
 */
class OpenMeteoProvider implements WeatherProvider
{
    public function key(): string
    {
        return 'open_meteo';
    }

    public function fetchMany(array $points): Collection
    {
        if ($points === []) {
            return collect();
        }

        $baseUrl = config('karsa.weather.open_meteo.base_url');
        $pastDays = config('karsa.weather.past_days', 30);
        $forecastDays = config('karsa.weather.forecast_days', 3);

        $lats = implode(',', array_column($points, 'lat'));
        $lngs = implode(',', array_column($points, 'lng'));

        try {
            $response = Http::timeout(config('karsa.weather.timeout_seconds', 10))
                ->retry(config('karsa.weather.retry_times', 2), 500)
                ->get($baseUrl, [
                    'latitude' => $lats,
                    'longitude' => $lngs,
                    'daily' => 'temperature_2m_max,relative_humidity_2m_min,precipitation_sum,wind_speed_10m_max',
                    'past_days' => $pastDays,
                    'forecast_days' => $forecastDays,
                    'timezone' => 'Asia/Jakarta',
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException("Open-Meteo HTTP {$response->status()}");
            }

            $json = $response->json();
        } catch (Throwable $e) {
            DataSyncLog::log('open_meteo', 'gagal', 0, $e->getMessage());
            throw $e;
        }

        // Respons multi-titik: array top-level bila banyak koordinat, objek tunggal bila satu.
        $entries = array_is_list($json) ? $json : [$json];

        $result = collect();

        foreach ($entries as $index => $entry) {
            $point = $points[$index] ?? null;

            if ($point === null || ! isset($entry['daily'])) {
                continue;
            }

            $daily = $entry['daily'];
            $dates = $daily['time'] ?? [];
            $observations = collect();

            foreach ($dates as $i => $date) {
                $carbonDate = Carbon::parse($date, 'Asia/Jakarta');
                $isForecast = $carbonDate->isAfter(Carbon::today('Asia/Jakarta'));

                $observations->push(new WeatherObservationDTO(
                    observedAt: $carbonDate,
                    tempMax: $daily['temperature_2m_max'][$i] ?? null,
                    rhMin: $daily['relative_humidity_2m_min'][$i] ?? null,
                    windMax: $daily['wind_speed_10m_max'][$i] ?? null,
                    rainMm: $daily['precipitation_sum'][$i] ?? null,
                    source: 'open_meteo',
                    isForecast: $isForecast,
                ));
            }

            $result->push([
                'district_id' => $point['id'],
                'observations' => $observations,
            ]);
        }

        DataSyncLog::log('open_meteo', 'sukses', $result->count());

        return $result;
    }
}
