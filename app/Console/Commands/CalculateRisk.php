<?php

namespace App\Console\Commands;

use App\Enums\RiskLevel;
use App\Models\Alert;
use App\Models\District;
use App\Models\Hotspot;
use App\Models\Report;
use App\Models\RiskModel;
use App\Models\RiskScore;
use App\Models\WeatherObservation;
use App\Services\Notify\AlertDispatcher;
use App\Services\Risk\Co2Estimator;
use App\Services\Risk\DataConfidence;
use App\Services\Risk\RiskScorer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Hitung skor risiko untuk semua kecamatan yang dipantau, deteksi kenaikan
 * level, dan buat peringatan otomatis. CLAUDE.md §9, §13, §17.
 */
class CalculateRisk extends Command
{
    protected $signature = 'karsa:calculate-risk';

    protected $description = 'Hitung skor risiko karhutla untuk seluruh kecamatan yang dipantau';

    public function handle(RiskScorer $scorer, DataConfidence $dataConfidence, Co2Estimator $co2Estimator): int
    {
        $model = RiskModel::active();

        if (! $model) {
            $this->error('Tidak ada risk_models aktif. Jalankan seeder atau aktifkan satu model di panel admin.');

            return self::FAILURE;
        }

        $districts = District::where('is_monitored', true)->get();
        $now = Carbon::now();

        foreach ($districts as $district) {
            $this->calculateForDistrict($district, $model, $now, $scorer, $dataConfidence, $co2Estimator);
        }

        $this->info("Selesai menghitung risiko untuk {$districts->count()} kecamatan.");

        return self::SUCCESS;
    }

    private function calculateForDistrict(
        District $district,
        RiskModel $model,
        Carbon $now,
        RiskScorer $scorer,
        DataConfidence $dataConfidence,
        Co2Estimator $co2Estimator,
    ): void {
        $hotspots24h = Hotspot::where('district_id', $district->id)
            ->where('detected_at', '>=', $now->copy()->subDay())
            ->get();

        $verifiedReports24h = Report::where('district_id', $district->id)
            ->where('status', \App\Enums\ReportStatus::Terverifikasi->value)
            ->where('created_at', '>=', $now->copy()->subDay())
            ->count();

        $weightedInputs = $hotspots24h->map(fn (Hotspot $h) => [
            'weight' => $h->confidence->weight(),
            'corroborated' => $h->corroborated,
        ])->values()->all();

        // Laporan warga terverifikasi dihitung sebagai hotspot bernilai 1.0 (CLAUDE.md §9.1).
        for ($i = 0; $i < $verifiedReports24h; $i++) {
            $weightedInputs[] = ['weight' => 1.0, 'corroborated' => false];
        }

        $weightedCount = $scorer->weightedHotspotCount($weightedInputs);
        $sHotspot = $scorer->normalizeHotspot($weightedCount, (float) \App\Models\Setting::get('hotspot_max', config('karsa.risk.hotspot_max', 10)));

        $weatherToday = WeatherObservation::where('district_id', $district->id)
            ->where('is_forecast', false)
            ->orderByDesc('observed_at')
            ->first();

        $bounds = config('karsa.risk.weather_bounds');
        $sWeather = $weatherToday
            ? $scorer->scoreWeather([
                'temp_max' => (float) ($weatherToday->temp_max ?? $bounds['temp_max']['min']),
                'rh_min' => (float) ($weatherToday->rh_min ?? $bounds['rh_min']['min']),
                'wind_max' => (float) ($weatherToday->wind_max ?? $bounds['wind_max']['min']),
                'dry_days' => (float) ($weatherToday->dry_days ?? 0),
            ], $model->sub_weights, $bounds)
            : 0.0;

        $sVulnerability = (float) $district->vulnerability_score;

        $score = $scorer->finalScore($sHotspot, $sWeather, $sVulnerability, $model->weights);
        $level = RiskLevel::fromScore($score, $model->thresholds);

        $confidence = $dataConfidence->calculate($district, $hotspots24h, $weatherToday);
        $luasPerHotspot = (float) \App\Models\Setting::get('luas_per_hotspot_ha', config('karsa.co2.luas_per_hotspot_ha', 1.0));
        $co2 = $co2Estimator->estimateFromHotspotCount($hotspots24h->count(), $luasPerHotspot);

        $previous = RiskScore::where('district_id', $district->id)
            ->where('calculated_at', '<=', $now->copy()->subHours(23))
            ->orderByDesc('calculated_at')
            ->first();

        $causes = $previous
            ? $scorer->mainCauses(
                ['s_hotspot' => $sHotspot, 's_weather' => $sWeather, 's_vulnerability' => $sVulnerability],
                ['s_hotspot' => (float) $previous->s_hotspot, 's_weather' => (float) $previous->s_weather, 's_vulnerability' => (float) $previous->s_vulnerability],
            )
            : [];

        $projection = $this->projectSeventyTwoHours($district, $model, $scorer, $sHotspot, $sVulnerability, $bounds);

        $riskScore = RiskScore::create([
            'district_id' => $district->id,
            'risk_model_id' => $model->id,
            'calculated_at' => $now,
            'score' => $score,
            'level' => $level->value,
            's_hotspot' => $sHotspot,
            's_weather' => $sWeather,
            's_vulnerability' => $sVulnerability,
            'data_confidence' => $confidence->score,
            'co2_estimate_t' => $co2,
            'detail' => [
                'confidence_components' => $confidence->components,
                'causes' => $causes,
                'projection_72h' => $projection,
                'hotspot_count_24h' => $hotspots24h->count(),
                'verified_reports_24h' => $verifiedReports24h,
            ],
        ]);

        $this->maybeCreateAlert($district, $previous, $riskScore, $causes, $now);
    }

    /**
     * Proyeksi risiko 72 jam: hotspot diasumsikan tetap sama dengan saat ini,
     * cuaca memakai prakiraan 3 hari ke depan (CLAUDE.md §8.5, §6 /aksi).
     */
    private function projectSeventyTwoHours(District $district, RiskModel $model, RiskScorer $scorer, float $sHotspot, float $sVulnerability, array $bounds): array
    {
        $forecasts = WeatherObservation::where('district_id', $district->id)
            ->where('is_forecast', true)
            ->orderBy('observed_at')
            ->limit(3)
            ->get();

        return $forecasts->map(function (WeatherObservation $f) use ($model, $scorer, $sHotspot, $sVulnerability, $bounds) {
            $sWeather = $scorer->scoreWeather([
                'temp_max' => (float) ($f->temp_max ?? $bounds['temp_max']['min']),
                'rh_min' => (float) ($f->rh_min ?? $bounds['rh_min']['min']),
                'wind_max' => (float) ($f->wind_max ?? $bounds['wind_max']['min']),
                'dry_days' => (float) ($f->dry_days ?? 0),
            ], $model->sub_weights, $bounds);

            $score = $scorer->finalScore($sHotspot, $sWeather, $sVulnerability, $model->weights);

            return [
                'date' => $f->observed_at->toDateString(),
                'score' => $score,
                'level' => RiskLevel::fromScore($score, $model->thresholds)->value,
            ];
        })->values()->all();
    }

    private function maybeCreateAlert(District $district, ?RiskScore $previous, RiskScore $current, array $causes, Carbon $now): void
    {
        $currentLevel = $current->level;
        $previousLevel = $previous?->level;

        $levelIncreased = $previousLevel !== null && $currentLevel->isHigherThan($previousLevel);
        $scoreJumped = $previous !== null && ($current->score - (float) $previous->score) >= (float) config('karsa.risk.alert_score_jump', 15);

        if (! $levelIncreased && ! $scoreJumped) {
            return;
        }

        $cooldownHours = (int) config('karsa.risk.alert_cooldown_hours', 12);
        $recentAlert = Alert::where('district_id', $district->id)
            ->where('to_level', $currentLevel->value)
            ->where('created_at', '>=', $now->copy()->subHours($cooldownHours))
            ->exists();

        if ($recentAlert) {
            return;
        }

        $causeLabels = collect($causes)->pluck('label')->implode(', ') ?: 'peningkatan skor risiko';

        $alert = Alert::create([
            'district_id' => $district->id,
            'from_level' => $previousLevel?->value,
            'to_level' => $currentLevel->value,
            'score' => $current->score,
            'causes' => $causes,
            'is_manual' => false,
            'message' => "Risiko karhutla di {$district->name} naik".($previousLevel ? " dari {$previousLevel->label()}" : '')." ke {$currentLevel->label()}. Penyebab utama: {$causeLabels}. Lihat langkah pencegahan 72 jam.",
            'sent_at' => now(),
        ]);

        app(AlertDispatcher::class)->dispatch($alert);
    }
}
