<?php

use App\Enums\HotspotConfidence;
use App\Services\Hotspot\BBox;
use App\Services\Hotspot\HotspotProviderException;
use App\Services\Hotspot\SipongiProvider;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'karsa.hotspot.sipongi.url' => 'https://example-sipongi.test/api/hotspot',
        'karsa.hotspot.sipongi.extra_params' => null,
    ]);
});

it('menguraikan format respons SiPongi perkiraan dengan berbagai nama field', function () {
    $fixture = json_decode(file_get_contents(base_path('tests/Fixtures/sipongi_sample.json')), true);

    Http::fake([
        'example-sipongi.test/*' => Http::response($fixture, 200),
    ]);

    $provider = new SipongiProvider;
    $bbox = new BBox(west: 105.5, south: -6.5, east: 106.5, north: -5.5);

    $result = $provider->fetch($bbox);

    expect($result)->toHaveCount(3);

    $first = $result->first();
    expect($first->lat)->toBe(-6.081)
        ->and($first->lng)->toBe(106.157)
        ->and($first->confidence)->toBe(HotspotConfidence::High)
        ->and($first->satellite)->toBe('VIIRS');

    $second = $result->get(1);
    expect($second->lat)->toBe(-6.15)
        ->and($second->confidence)->toBe(HotspotConfidence::Medium);

    $third = $result->get(2);
    expect($third->lat)->toBe(-6.2)
        ->and($third->lng)->toBe(106.3)
        ->and($third->confidence)->toBe(HotspotConfidence::Low);
});

it('melempar exception terkendali bila URL belum diatur', function () {
    config(['karsa.hotspot.sipongi.url' => null]);

    $provider = new SipongiProvider;

    $provider->fetch(new BBox(105, -7, 107, -5));
})->throws(HotspotProviderException::class);

it('melewati baris tanpa koordinat valid tanpa menghentikan proses', function () {
    Http::fake([
        'example-sipongi.test/*' => Http::response([
            'data' => [
                ['id' => 1, 'lat' => -6.1, 'lng' => 106.2, 'confidence' => 'high'],
                ['id' => 2, 'catatan' => 'baris rusak tanpa koordinat'],
            ],
        ], 200),
    ]);

    $provider = new SipongiProvider;
    $result = $provider->fetch(new BBox(105, -7, 107, -5));

    expect($result)->toHaveCount(1);
});

it('menyaring hotspot di luar bbox', function () {
    Http::fake([
        'example-sipongi.test/*' => Http::response([
            'data' => [
                ['id' => 1, 'lat' => -6.1, 'lng' => 106.2, 'confidence' => 'high'], // dalam bbox
                ['id' => 2, 'lat' => 10.0, 'lng' => 120.0, 'confidence' => 'high'], // luar bbox
            ],
        ], 200),
    ]);

    $provider = new SipongiProvider;
    $result = $provider->fetch(new BBox(west: 105, south: -7, east: 107, north: -5));

    expect($result)->toHaveCount(1);
});
