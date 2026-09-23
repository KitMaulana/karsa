<?php

use App\Enums\HotspotConfidence;
use App\Enums\RiskLevel;
use App\Models\District;
use App\Models\Hotspot;
use App\Models\RiskScore;
use App\Models\WeatherObservation;
use App\Services\Ai\GeminiRiskAnalyzer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('bisa menyusun prompt dan menganalisis risiko tanpa error konversi enum HotspotConfidence', function () {
    Config::set('karsa.gemini.api_key', 'test-api-key');

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'ringkasan_situasi' => 'Situasi aman.',
                                    'tingkat_ancaman' => 'Rendah',
                                    'faktor_kritis' => ['Cuaca'],
                                    'prediksi_72_jam' => 'Stabil',
                                    'rekomendasi_utama' => 'Tetap waspada',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $district = new District(['name' => 'Ciruas']);
    $district->id = 1;

    $hotspot = new Hotspot([
        'satellite' => 'NOAA-20',
        'confidence' => HotspotConfidence::High,
        'frp' => 12.5,
    ]);

    $score = new RiskScore([
        'score' => 45.0,
        'level' => RiskLevel::Sedang,
    ]);

    $weather = new WeatherObservation([
        'temp_max' => 33.0,
        'rh_min' => 45.0,
        'wind_max' => 12.0,
        'dry_days' => 4,
    ]);

    $analyzer = new GeminiRiskAnalyzer();
    $result = $analyzer->analyzeRiskAndPredict($district, $score, collect([$hotspot]), $weather);

    expect($result)->toBeArray()
        ->and($result['ringkasan_situasi'])->toBe('Situasi aman.')
        ->and($result['tingkat_ancaman'])->toBe('Rendah');

    Http::assertSent(function ($request) {
        return str_contains($request->body(), 'Kepercayaan: Tinggi')
            && str_contains($request->body(), 'NOAA-20')
            && str_contains($request->body(), 'FRP: 12.5 MW');
    });
});
