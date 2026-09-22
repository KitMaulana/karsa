<?php

namespace App\Console\Commands;

use App\Enums\HotspotConfidence;
use App\Enums\ReportStatus;
use App\Models\Alert;
use App\Models\District;
use App\Models\Hotspot;
use App\Models\Report;
use App\Models\RiskModel;
use App\Models\RiskScore;
use App\Models\User;
use App\Models\WeatherObservation;
use App\Services\Risk\Co2Estimator;
use App\Services\Risk\DataConfidence;
use App\Services\Risk\RiskScorer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Isi data fiktif-realistis 14 hari terakhir untuk demo lomba (semua ditandai
 * is_demo=true agar mudah dihapus lewat karsa:demo-clear). CLAUDE.md, bagian
 * "Prompt perbaikan -- Demo lomba".
 */
class DemoSeed extends Command
{
    protected $signature = 'karsa:demo-seed';

    protected $description = 'Isi data demo (hotspot, cuaca, laporan, peringatan) 14 hari terakhir untuk Kabupaten Serang';

    public function handle(RiskScorer $scorer, DataConfidence $dataConfidence, Co2Estimator $co2Estimator): int
    {
        $districts = District::where('is_monitored', true)->get();
        $model = RiskModel::active();

        if (! $model || $districts->isEmpty()) {
            $this->error('Butuh minimal 1 risk_models aktif dan kecamatan yang dipantau. Jalankan db:seed dahulu.');

            return self::FAILURE;
        }

        $demoUser = User::firstOrCreate(
            ['email' => 'demo@karsa.local'],
            ['name' => 'Warga Demo', 'password' => bcrypt('demo12345'), 'role' => 'warga', 'email_verified_at' => now()]
        );

        $bar = $this->output->createProgressBar($districts->count() * 14);

        foreach ($districts as $district) {
            for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
                $day = Carbon::now()->subDays($daysAgo);
                $intensity = max(0, sin($daysAgo / 3) * 0.5 + 0.5 + (random_int(-20, 20) / 100));

                $hotspotCount = (int) round($intensity * random_int(2, 8));
                $hotspots = [];

                for ($i = 0; $i < $hotspotCount; $i++) {
                    $conf = $intensity > 0.7 ? HotspotConfidence::High : ($intensity > 0.4 ? HotspotConfidence::Medium : HotspotConfidence::Low);

                    $hotspots[] = Hotspot::create([
                        'external_id' => 'demo-'.$district->id.'-'.$daysAgo.'-'.$i,
                        'sources' => ['sipongi'],
                        'satellite' => 'DEMO',
                        'lat' => $district->centroid_lat + (random_int(-50, 50) / 10000),
                        'lng' => $district->centroid_lng + (random_int(-50, 50) / 10000),
                        'confidence' => $conf->value,
                        'confidence_raw' => $conf->value,
                        'frp' => random_int(1, 30),
                        'detected_at' => $day->copy()->addHours(random_int(0, 23)),
                        'district_id' => $district->id,
                        'corroborated' => $intensity > 0.6,
                        'raw' => ['demo' => true],
                        'is_demo' => true,
                    ]);
                }

                $tempMax = 28 + $intensity * 10 + random_int(-2, 2);
                $rhMin = 70 - $intensity * 35 + random_int(-5, 5);
                $windMax = 10 + $intensity * 15;
                $dryDays = (int) round($intensity * 15);

                WeatherObservation::updateOrCreate(
                    ['district_id' => $district->id, 'observed_at' => $day->toDateString(), 'source' => 'open_meteo'],
                    [
                        'temp_max' => $tempMax, 'rh_min' => max(20, $rhMin), 'wind_max' => $windMax,
                        'rain_mm' => $intensity > 0.6 ? 0 : random_int(0, 10), 'dry_days' => $dryDays,
                        'is_forecast' => false, 'is_demo' => true,
                    ]
                );

                $bounds = config('karsa.risk.weather_bounds');
                $weightedInputs = collect($hotspots)->map(fn (Hotspot $h) => ['weight' => $h->confidence->weight(), 'corroborated' => $h->corroborated])->all();
                $sHotspot = $scorer->normalizeHotspot($scorer->weightedHotspotCount($weightedInputs));
                $sWeather = $scorer->scoreWeather(['temp_max' => $tempMax, 'rh_min' => max(20, $rhMin), 'wind_max' => $windMax, 'dry_days' => $dryDays], $model->sub_weights, $bounds);
                $sVulnerability = (float) $district->vulnerability_score;
                $score = $scorer->finalScore($sHotspot, $sWeather, $sVulnerability, $model->weights);
                $level = \App\Enums\RiskLevel::fromScore($score, $model->thresholds);

                RiskScore::create([
                    'district_id' => $district->id, 'risk_model_id' => $model->id, 'calculated_at' => $day->copy()->setTime(15, 0),
                    'score' => $score, 'level' => $level->value, 's_hotspot' => $sHotspot, 's_weather' => $sWeather, 's_vulnerability' => $sVulnerability,
                    'data_confidence' => 70, 'co2_estimate_t' => $co2Estimator->estimateFromHotspotCount($hotspotCount),
                    'detail' => ['hotspot_count_24h' => $hotspotCount, 'is_demo' => true], 'is_demo' => true,
                ]);

                if ($intensity > 0.75 && random_int(0, 100) < 40) {
                    Report::create([
                        'user_id' => $demoUser->id, 'district_id' => $district->id,
                        'lat' => $district->centroid_lat, 'lng' => $district->centroid_lng,
                        'accuracy_m' => 20, 'type' => 'asap', 'description' => '[DEMO] Terlihat asap tebal di dekat lahan kosong.',
                        'trust_score' => random_int(60, 95), 'status' => ReportStatus::Terverifikasi->value,
                        'verified_at' => $day, 'is_demo' => true,
                    ]);
                }

                if ($level->rank() >= 2 && $daysAgo % 4 === 0) {
                    Alert::create([
                        'district_id' => $district->id, 'from_level' => 'sedang', 'to_level' => $level->value,
                        'score' => $score, 'causes' => [['factor' => 's_hotspot', 'delta' => 20, 'label' => 'Peningkatan hotspot']],
                        'is_manual' => false, 'message' => "[DEMO] Risiko karhutla di {$district->name} naik ke {$level->label()}.",
                        'sent_at' => $day, 'is_demo' => true,
                    ]);
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Data demo 14 hari untuk '.$districts->count().' kecamatan berhasil dibuat.');

        return self::SUCCESS;
    }
}
