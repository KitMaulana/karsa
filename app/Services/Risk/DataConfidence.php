<?php

namespace App\Services\Risk;

use App\Models\District;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Indikator keyakinan data per kecamatan (0-100 -> Tinggi/Sedang/Rendah).
 * CLAUDE.md §8.3: kesegaran data hotspot, jumlah sumber aktif, proporsi
 * hotspot high-confidence/terkonfirmasi, ketersediaan data cuaca,
 * kelengkapan data kerentanan.
 */
class DataConfidence
{
    public function calculate(District $district, Collection $hotspots, ?object $weatherObservation): DataConfidenceResult
    {
        $score = 0;
        $components = [];

        // Kesegaran hotspot (<= 3 jam = penuh), bobot 25.
        $latestHotspot = $hotspots->sortByDesc('detected_at')->first();
        $freshness = 0;
        if ($latestHotspot) {
            $hoursAgo = Carbon::parse($latestHotspot->detected_at)->diffInHours(now());
            $freshness = $hoursAgo <= 3 ? 25 : max(0, 25 - ($hoursAgo - 3) * 2);
        }
        $components['kesegaran_hotspot'] = round($freshness, 1);
        $score += $freshness;

        // Jumlah sumber aktif, bobot 20 (maks 2 sumber: sipongi + firms).
        $sourceCount = $hotspots->flatMap(fn ($h) => $h->sources ?? [$h->source ?? null])->filter()->unique()->count();
        $sourceScore = min(20, $sourceCount * 10);
        $components['jumlah_sumber'] = $sourceScore;
        $score += $sourceScore;

        // Proporsi hotspot high-confidence / terkonfirmasi, bobot 25.
        $total = $hotspots->count();
        $highOrCorroborated = $hotspots->filter(fn ($h) => ($h->confidence ?? null)?->value === 'high' || ($h->corroborated ?? false))->count();
        $proportionScore = $total > 0 ? round(($highOrCorroborated / $total) * 25, 1) : 12.5; // netral bila tak ada hotspot
        $components['proporsi_confidence_tinggi'] = $proportionScore;
        $score += $proportionScore;

        // Ketersediaan data cuaca, bobot 15.
        $weatherScore = $weatherObservation ? 15 : 0;
        $components['ketersediaan_cuaca'] = $weatherScore;
        $score += $weatherScore;

        // Kelengkapan data kerentanan, bobot 15.
        $vulnerabilityScore = ! empty($district->vulnerability_factors) ? 15 : ($district->vulnerability_score !== null ? 8 : 0);
        $components['kelengkapan_kerentanan'] = $vulnerabilityScore;
        $score += $vulnerabilityScore;

        $score = round(min(100, $score), 1);

        $level = match (true) {
            $score >= 75 => 'tinggi',
            $score >= 45 => 'sedang',
            default => 'rendah',
        };

        return new DataConfidenceResult($score, $level, $components);
    }
}
