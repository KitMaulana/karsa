<?php

use App\Services\Risk\RiskScorer;

it('menormalisasi skor hotspot secara linier 0-100', function () {
    $scorer = new RiskScorer;

    expect($scorer->normalizeHotspot(0, 10))->toBe(0.0)
        ->and($scorer->normalizeHotspot(5, 10))->toBe(50.0)
        ->and($scorer->normalizeHotspot(10, 10))->toBe(100.0)
        ->and($scorer->normalizeHotspot(20, 10))->toBe(100.0); // dipotong di 100
});

it('menghitung weighted hotspot count dengan bonus terkonfirmasi', function () {
    $scorer = new RiskScorer;

    $hotspots = [
        ['weight' => 1.0, 'corroborated' => true],  // 1.0 * 1.2 = 1.2
        ['weight' => 0.6, 'corroborated' => false], // 0.6
        ['weight' => 0.3, 'corroborated' => false], // 0.3
    ];

    expect($scorer->weightedHotspotCount($hotspots))->toBe(2.1);
});

it('membalik arah normalisasi untuk kelembapan minimum (rh_min)', function () {
    $scorer = new RiskScorer;

    // 85% -> 0, 35% -> 100 (CLAUDE.md §9.1)
    expect($scorer->normalizeMetric(85, 85, 35, reversed: true))->toBe(0.0)
        ->and($scorer->normalizeMetric(35, 85, 35, reversed: true))->toBe(100.0)
        ->and($scorer->normalizeMetric(60, 85, 35, reversed: true))->toBe(50.0);
});

it('menghitung skor cuaca gabungan sesuai bobot sub-faktor', function () {
    $scorer = new RiskScorer;

    $metrics = ['temp_max' => 38, 'rh_min' => 35, 'wind_max' => 40, 'dry_days' => 21];
    $subWeights = ['temp_max' => 0.4, 'rh_min' => 0.3, 'wind_max' => 0.1, 'dry_days' => 0.2];
    $bounds = [
        'temp_max' => ['min' => 25, 'max' => 38],
        'rh_min' => ['min' => 85, 'max' => 35],
        'wind_max' => ['min' => 0, 'max' => 40],
        'dry_days' => ['min' => 0, 'max' => 21],
    ];

    // Semua metrik di titik ekstrem "risiko maksimum" -> skor harus 100.
    expect($scorer->scoreWeather($metrics, $subWeights, $bounds))->toBe(100.0);
});

it('menghitung skor akhir R = wh.Sh + wc.Sc + wk.Sk', function () {
    $scorer = new RiskScorer;

    $score = $scorer->finalScore(
        sHotspot: 80,
        sCuaca: 60,
        sKerentanan: 40,
        mainWeights: ['hotspot' => 0.54, 'cuaca' => 0.30, 'kerentanan' => 0.16]
    );

    // 80*0.54 + 60*0.30 + 40*0.16 = 43.2 + 18 + 6.4 = 67.6
    expect($score)->toBe(67.6);
});

it('menghitung hari tanpa hujan berturut-turut dari deret harian terbaru ke lama', function () {
    $scorer = new RiskScorer;

    // Hari ini (indeks 0) sampai 4 hari lalu tanpa hujan, lalu hujan.
    $daily = [0.0, 0.2, 0.0, 0.5, 0.0, 5.0, 0.0];

    expect($scorer->consecutiveDryDays($daily))->toBe(5);
});

it('menyusun 3 penyebab utama kenaikan skor terbesar', function () {
    $scorer = new RiskScorer;

    $current = ['s_hotspot' => 80, 's_weather' => 70, 's_vulnerability' => 40];
    $previous = ['s_hotspot' => 50, 's_weather' => 65, 's_vulnerability' => 40];

    $causes = $scorer->mainCauses($current, $previous);

    expect($causes)->toHaveCount(2) // kerentanan tidak berubah, tidak masuk daftar
        ->and($causes[0]['factor'])->toBe('s_hotspot') // delta terbesar (30) di urutan pertama
        ->and($causes[1]['factor'])->toBe('s_weather');
});
