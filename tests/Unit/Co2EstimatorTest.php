<?php

use App\Services\Risk\Co2Estimator;

it('menghitung estimasi emisi CO2 dengan rumus IPCC 2006 Vol.4 Pers. 2.27', function () {
    $estimator = new Co2Estimator;

    // L = A x MB x Cf x Gef x 10^-3
    // 10 ha x 30 t/ha x 0.5 x 1580 g/kg x 0.001 = 237 ton CO2
    $result = $estimator->estimate(areaHa: 10, mb: 30, cf: 0.5, gef: 1580);

    expect($result)->toBe(237.0);
});

it('mengestimasi luas dari jumlah hotspot dikali luas per hotspot default', function () {
    $estimator = new Co2Estimator;

    $result = $estimator->estimateFromHotspotCount(hotspotCount: 5, luasPerHotspotHa: 1, mb: 30, cf: 0.5, gef: 1580);

    // 5 ha x 30 x 0.5 x 1580 x 0.001 = 118.5
    expect($result)->toBe(118.5);
});
