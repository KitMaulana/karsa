<?php

namespace App\Services\Risk;

/**
 * Perhitungan murni skor risiko karhutla (CLAUDE.md §9). Sengaja tidak
 * bergantung pada Eloquent supaya mudah diuji unit -- orkestrasi DB
 * (ambil hotspot, cuaca, dsb) dilakukan oleh App\Console\Commands\CalculateRisk.
 *
 * R = w_h . S_hotspot + w_c . S_cuaca + w_k . S_kerentanan
 */
class RiskScorer
{
    /**
     * Hitung skor hotspot berbobot dari daftar {confidence_weight, corroborated}.
     *
     * @param  array<int, array{weight: float, corroborated: bool}>  $hotspots
     */
    public function weightedHotspotCount(array $hotspots): float
    {
        $bonus = (float) config('karsa.risk.corroborated_multiplier', 1.2);

        $sum = array_sum(array_map(
            fn (array $h) => $h['weight'] * ($h['corroborated'] ? $bonus : 1.0),
            $hotspots
        ));

        return round($sum, 6);
    }

    /**
     * Normalisasi linier weighted count -> 0-100 (0 -> 0, hotspotMax -> 100, dipotong di 100).
     */
    public function normalizeHotspot(float $weightedCount, ?float $hotspotMax = null): float
    {
        $hotspotMax ??= (float) config('karsa.risk.hotspot_max', 10);

        if ($hotspotMax <= 0) {
            return 0.0;
        }

        return round(min(100, max(0, ($weightedCount / $hotspotMax) * 100)), 2);
    }

    /**
     * Normalisasi satu metrik cuaca ke 0-100 secara linier: $min selalu
     * dipetakan ke 0 dan $max ke 100, walau $min > $max secara numerik.
     * Ini otomatis menangani arah terbalik untuk kelembapan minimum
     * (bounds disimpan {min:85, max:35} -> 85% jadi 0, 35% jadi 100).
     * Parameter $reversed dipertahankan untuk kejelasan pemanggilan saja.
     */
    public function normalizeMetric(float $value, float $min, float $max, bool $reversed = false): float
    {
        if ($max === $min) {
            return 0.0;
        }

        $normalized = ($value - $min) / ($max - $min) * 100;

        return round(min(100, max(0, $normalized)), 2);
    }

    /**
     * @param  array{temp_max:float, rh_min:float, wind_max:float, dry_days:float}  $metrics
     * @param  array{temp_max:float, rh_min:float, wind_max:float, dry_days:float}  $subWeights  bobot AHP sub-matriks cuaca (jumlah = 1)
     * @param  array<string, array{min:float,max:float}>  $bounds
     */
    public function scoreWeather(array $metrics, array $subWeights, array $bounds): float
    {
        $normalized = [
            'temp_max' => $this->normalizeMetric($metrics['temp_max'], $bounds['temp_max']['min'], $bounds['temp_max']['max']),
            'rh_min' => $this->normalizeMetric($metrics['rh_min'], $bounds['rh_min']['min'], $bounds['rh_min']['max'], reversed: true),
            'wind_max' => $this->normalizeMetric($metrics['wind_max'], $bounds['wind_max']['min'], $bounds['wind_max']['max']),
            'dry_days' => $this->normalizeMetric($metrics['dry_days'], $bounds['dry_days']['min'], $bounds['dry_days']['max']),
        ];

        $score = 0.0;
        foreach ($normalized as $key => $value) {
            $score += $value * ($subWeights[$key] ?? 0);
        }

        return round($score, 2);
    }

    /**
     * @param  array{hotspot: float, cuaca: float, kerentanan: float}  $mainWeights  bobot AHP utama (jumlah = 1)
     */
    public function finalScore(float $sHotspot, float $sCuaca, float $sKerentanan, array $mainWeights): float
    {
        $score = $sHotspot * ($mainWeights['hotspot'] ?? 0)
            + $sCuaca * ($mainWeights['cuaca'] ?? 0)
            + $sKerentanan * ($mainWeights['kerentanan'] ?? 0);

        return round(min(100, max(0, $score)), 2);
    }

    /**
     * Hari tanpa hujan berturut-turut dari deret harian precipitation_sum (mm),
     * diurutkan dari yang paling baru ke yang paling lama.
     *
     * @param  float[]  $dailyPrecipitationDescending
     */
    public function consecutiveDryDays(array $dailyPrecipitationDescending, float $thresholdMm = 1.0): int
    {
        $count = 0;

        foreach ($dailyPrecipitationDescending as $precipitation) {
            if ($precipitation >= $thresholdMm) {
                break;
            }
            $count++;
        }

        return $count;
    }

    /**
     * Bandingkan sub-skor sekarang vs 24 jam lalu, kembalikan 3 penyebab
     * utama kenaikan terbesar (CLAUDE.md §9.5).
     *
     * @param  array{s_hotspot: float, s_weather: float, s_vulnerability: float}  $current
     * @param  array{s_hotspot: float, s_weather: float, s_vulnerability: float}  $previous
     * @return array<int, array{factor: string, delta: float, label: string}>
     */
    public function mainCauses(array $current, array $previous, int $limit = 3): array
    {
        $labels = [
            's_hotspot' => 'Peningkatan hotspot',
            's_weather' => 'Kondisi cuaca memburuk',
            's_vulnerability' => 'Kerentanan wilayah',
        ];

        $deltas = [];
        foreach ($labels as $key => $label) {
            $delta = ($current[$key] ?? 0) - ($previous[$key] ?? 0);
            if ($delta > 0) {
                $deltas[] = ['factor' => $key, 'delta' => round($delta, 2), 'label' => $label];
            }
        }

        usort($deltas, fn ($a, $b) => $b['delta'] <=> $a['delta']);

        return array_slice($deltas, 0, $limit);
    }
}
