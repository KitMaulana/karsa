<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class RiskController extends Controller
{
    /**
     * GET /api/v1/risk/{kecamatan} -- skor, level, sub-skor, keyakinan data, cuaca, tren 7 hari.
     */
    public function show(District $district): JsonResponse
    {
        $cacheKey = "api:risk:{$district->id}";

        $data = Cache::remember($cacheKey, 300, function () use ($district) {
            $latest = $district->riskScores()->latest('calculated_at')->first();

            $trend = $district->riskScores()
                ->where('calculated_at', '>=', now()->subDays(7))
                ->orderBy('calculated_at')
                ->get(['calculated_at', 'score', 'level'])
                ->groupBy(fn ($s) => $s->calculated_at->toDateString())
                ->map(fn ($day) => $day->last()->score)
                ->values();

            $weather = \App\Models\WeatherObservation::where('district_id', $district->id)
                ->where('is_forecast', false)
                ->latest('observed_at')
                ->first();

            if (! $latest) {
                return [
                    'district' => ['id' => $district->id, 'name' => $district->name, 'slug' => $district->slug],
                    'available' => false,
                ];
            }

            return [
                'district' => ['id' => $district->id, 'name' => $district->name, 'slug' => $district->slug],
                'available' => true,
                'score' => $latest->score,
                'level' => $latest->level->value,
                'level_label' => $latest->level->label(),
                's_hotspot' => $latest->s_hotspot,
                's_weather' => $latest->s_weather,
                's_vulnerability' => $latest->s_vulnerability,
                'data_confidence' => $latest->data_confidence,
                'co2_estimate_t' => $latest->co2_estimate_t,
                'calculated_at' => $latest->calculated_at->toIso8601String(),
                'weather' => $weather ? [
                    'temp_max' => $weather->temp_max,
                    'rh_min' => $weather->rh_min,
                    'wind_max' => $weather->wind_max,
                    'dry_days' => $weather->dry_days,
                    'observed_at' => $weather->observed_at->toDateString(),
                ] : null,
                'detail' => $latest->detail,
                'trend_7d' => $trend,
            ];
        });

        return response()->json($data);
    }
}
