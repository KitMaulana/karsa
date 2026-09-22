<?php

use App\Enums\HotspotConfidence;
use App\Services\Hotspot\HotspotAggregator;
use App\Services\Hotspot\HotspotDTO;
use Carbon\Carbon;

function makeHotspot(string $source, float $lat, float $lng, HotspotConfidence $conf, Carbon $at): HotspotDTO
{
    return new HotspotDTO(
        source: $source,
        satellite: 'TEST',
        lat: $lat,
        lng: $lng,
        confidence: $conf,
        confidenceRaw: $conf->value,
        frp: null,
        detectedAt: $at,
        externalId: uniqid(),
    );
}

it('menggabungkan dua titik dari sumber berbeda dalam jarak <=1km & waktu <=3jam sebagai terkonfirmasi', function () {
    $aggregator = new HotspotAggregator(distanceKm: 1, timeHours: 3);
    $now = Carbon::parse('2026-09-20 12:00:00');

    $sipongi = collect([makeHotspot('sipongi', -6.0800, 106.1500, HotspotConfidence::Medium, $now)]);
    $firms = collect([makeHotspot('firms', -6.0805, 106.1505, HotspotConfidence::High, $now->copy()->addHour())]);

    $merged = $aggregator->merge(['sipongi' => $sipongi, 'firms' => $firms]);

    expect($merged)->toHaveCount(1)
        ->and($merged->first()['corroborated'])->toBeTrue()
        ->and($merged->first()['sources'])->toEqualCanonicalizing(['sipongi', 'firms'])
        ->and($merged->first()['dto']->confidence)->toBe(HotspotConfidence::High); // representatif confidence tertinggi
});

it('tidak menggabungkan titik yang berjarak lebih dari 1km', function () {
    $aggregator = new HotspotAggregator(distanceKm: 1, timeHours: 3);
    $now = Carbon::parse('2026-09-20 12:00:00');

    $a = collect([makeHotspot('sipongi', -6.0800, 106.1500, HotspotConfidence::Medium, $now)]);
    $b = collect([makeHotspot('firms', -6.1200, 106.2000, HotspotConfidence::High, $now)]); // ~6km jauh

    $merged = $aggregator->merge(['sipongi' => $a, 'firms' => $b]);

    expect($merged)->toHaveCount(2)
        ->and($merged->every(fn ($m) => $m['corroborated'] === false))->toBeTrue();
});

it('tidak menggabungkan titik yang selisih waktunya lebih dari 3 jam', function () {
    $aggregator = new HotspotAggregator(distanceKm: 1, timeHours: 3);
    $now = Carbon::parse('2026-09-20 12:00:00');

    $a = collect([makeHotspot('sipongi', -6.0800, 106.1500, HotspotConfidence::Medium, $now)]);
    $b = collect([makeHotspot('firms', -6.0800, 106.1500, HotspotConfidence::High, $now->copy()->addHours(5))]);

    $merged = $aggregator->merge(['sipongi' => $a, 'firms' => $b]);

    expect($merged)->toHaveCount(2);
});
