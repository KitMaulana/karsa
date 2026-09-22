<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\WeatherObservation;
use App\Services\Weather\BmkgProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sinkronkan observasi cuaca (30 hari ke belakang + prakiraan 3 hari) untuk
 * seluruh kecamatan yang dipantau. Open-Meteo sebagai sumber utama (satu
 * request untuk banyak koordinat), BMKG sebagai pelengkap. CLAUDE.md §8.5, §17.
 */
class SyncWeather extends Command
{
    protected $signature = 'karsa:sync-weather';

    protected $description = 'Sinkronkan data cuaca (Open-Meteo utama, BMKG pelengkap)';

    public function handle(\App\Services\Weather\OpenMeteoProvider $openMeteo, BmkgProvider $bmkg): int
    {
        $districts = District::where('is_monitored', true)->whereNotNull('centroid_lat')->whereNotNull('centroid_lng')->get();

        if ($districts->isEmpty()) {
            $this->warn('Tidak ada kecamatan dengan centroid untuk disinkronkan.');

            return self::SUCCESS;
        }

        $points = $districts->map(fn (District $d) => [
            'id' => $d->id,
            'lat' => (float) $d->centroid_lat,
            'lng' => (float) $d->centroid_lng,
            'bmkg_adm4' => $d->bmkg_adm4,
        ])->all();

        try {
            $results = $openMeteo->fetchMany($points);
        } catch (\Throwable $e) {
            $this->error('Open-Meteo gagal total: '.$e->getMessage());
            Log::error('SyncWeather: Open-Meteo gagal', ['error' => $e->getMessage()]);
            $results = collect();
        }

        $saved = 0;

        foreach ($results as $result) {
            $districtId = $result['district_id'];
            $observations = $result['observations']->sortBy('observedAt')->values();

            // Hitung hari tanpa hujan berjalan (reset saat hujan >= 1mm), maju dari hari terlama.
            $runningDryDays = 0;

            foreach ($observations as $obs) {
                $rain = $obs->rainMm ?? 0;
                $runningDryDays = $rain < 1.0 ? $runningDryDays + 1 : 0;
                $dryDays = $obs->isForecast ? null : $runningDryDays;

                WeatherObservation::updateOrCreate(
                    ['district_id' => $districtId, 'observed_at' => $obs->observedAt->toDateString(), 'source' => $obs->source],
                    [
                        'temp_max' => $obs->tempMax,
                        'rh_min' => $obs->rhMin,
                        'wind_max' => $obs->windMax,
                        'rain_mm' => $obs->rainMm,
                        'dry_days' => $dryDays,
                        'is_forecast' => $obs->isForecast,
                    ]
                );
                $saved++;
            }
        }

        $this->info("Open-Meteo: {$saved} observasi tersimpan/diperbarui.");

        // BMKG pelengkap: hanya untuk kecamatan dengan bmkg_adm4 terisi.
        $pointsWithAdm4 = array_filter($points, fn ($p) => ! blank($p['bmkg_adm4']));

        if ($pointsWithAdm4 !== []) {
            try {
                $bmkgResults = $bmkg->fetchMany($pointsWithAdm4);
                $bmkgSaved = 0;

                foreach ($bmkgResults as $result) {
                    foreach ($result['observations'] as $obs) {
                        WeatherObservation::updateOrCreate(
                            ['district_id' => $result['district_id'], 'observed_at' => $obs->observedAt->toDateString(), 'source' => $obs->source],
                            [
                                'temp_max' => $obs->tempMax,
                                'rh_min' => $obs->rhMin,
                                'wind_max' => $obs->windMax,
                                'rain_mm' => $obs->rainMm,
                                'is_forecast' => $obs->isForecast,
                            ]
                        );
                        $bmkgSaved++;
                    }
                }

                $this->info("BMKG: {$bmkgSaved} observasi tersimpan/diperbarui.");
            } catch (\Throwable $e) {
                $this->warn('BMKG gagal (dilewati, Open-Meteo tetap dipakai): '.$e->getMessage());
                Log::warning('SyncWeather: BMKG gagal', ['error' => $e->getMessage()]);
            }
        }

        return self::SUCCESS;
    }
}
