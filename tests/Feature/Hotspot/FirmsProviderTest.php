<?php

use App\Enums\HotspotConfidence;
use App\Services\Hotspot\BBox;
use App\Services\Hotspot\FirmsProvider;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'karsa.hotspot.firms.map_key' => 'TEST_MAP_KEY',
        'karsa.hotspot.firms.sources' => ['VIIRS_SNPP_NRT'],
    ]);
});

it('menguraikan CSV asli NASA FIRMS (VIIRS) dengan benar', function () {
    $csv = file_get_contents(base_path('tests/Fixtures/firms_viirs_sample.csv'));

    Http::fake([
        'firms.modaps.eosdis.nasa.gov/*' => Http::response($csv, 200),
    ]);

    $provider = new FirmsProvider;
    $bbox = new BBox(west: 105.5, south: -6.5, east: 106.5, north: -5.5);

    $result = $provider->fetch($bbox);

    expect($result)->toHaveCount(3);

    $first = $result->first();
    expect($first->lat)->toBe(-6.0812)
        ->and($first->lng)->toBe(106.1572)
        ->and($first->confidence)->toBe(HotspotConfidence::High)
        ->and($first->frp)->toBe(15.3)
        ->and($first->source)->toBe('firms');

    expect($result->get(1)->confidence)->toBe(HotspotConfidence::Medium);
    expect($result->get(2)->confidence)->toBe(HotspotConfidence::Low);
});

it('melempar exception bila FIRMS_MAP_KEY belum diatur', function () {
    config(['karsa.hotspot.firms.map_key' => null]);

    $provider = new FirmsProvider;

    $provider->fetch(new BBox(105, -7, 107, -5));
})->throws(\App\Services\Hotspot\HotspotProviderException::class);

it('menandai confidence MODIS berdasarkan angka 0-100', function () {
    config(['karsa.hotspot.firms.sources' => ['MODIS_NRT']]);

    $csv = "latitude,longitude,brightness,scan,track,acq_date,acq_time,satellite,instrument,confidence,version,bright_t31,frp,daynight\n"
        ."-6.08,106.15,320.5,1.0,1.0,2026-09-20,0530,Terra,MODIS,90,6.1NRT,290.1,15.3,D\n"
        ."-6.15,106.24,305.2,1.0,1.0,2026-09-20,0530,Terra,MODIS,50,6.1NRT,285.0,5.1,D\n"
        ."-6.20,106.30,298.1,1.0,1.0,2026-09-20,0530,Terra,MODIS,10,6.1NRT,280.0,2.0,D\n";

    Http::fake([
        'firms.modaps.eosdis.nasa.gov/*' => Http::response($csv, 200),
    ]);

    $provider = new FirmsProvider;
    $result = $provider->fetch(new BBox(105, -7, 107, -5));

    expect($result->get(0)->confidence)->toBe(HotspotConfidence::High)
        ->and($result->get(1)->confidence)->toBe(HotspotConfidence::Medium)
        ->and($result->get(2)->confidence)->toBe(HotspotConfidence::Low);
});
