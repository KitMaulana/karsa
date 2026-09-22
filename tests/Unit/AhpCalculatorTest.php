<?php

use App\Services\Risk\AhpCalculator;

// Contoh matriks klasik dari literatur AHP (Saaty): kriteria mobil dengan
// hasil bobot & CR yang bisa diverifikasi manual (lih. Saaty, 1980).
// Matriks 3x3: Harga vs Model vs Konsumsi BBM.
it('menghitung bobot & CR untuk matriks 3x3 yang konsisten', function () {
    $calculator = new AhpCalculator;

    // Matriks konsisten sempurna: A > B (2x), A > C (4x), B > C (2x).
    $matrix = [
        [1, 2, 4],
        [1 / 2, 1, 2],
        [1 / 4, 1 / 2, 1],
    ];

    $result = $calculator->calculate($matrix);

    expect($result->n)->toBe(3)
        ->and($result->lambdaMax)->toBeGreaterThanOrEqual(3.0)
        ->and(round(array_sum($result->weights), 4))->toBe(1.0)
        ->and($result->cr)->toBeLessThan(0.01) // matriks konsisten sempurna -> CR ~ 0
        ->and($result->isConsistent())->toBeTrue();

    // Bobot harus turun berurutan sesuai preferensi (A > B > C).
    expect($result->weights[0])->toBeGreaterThan($result->weights[1])
        ->and($result->weights[1])->toBeGreaterThan($result->weights[2]);
});

it('menandai matriks tidak konsisten saat CR melebihi 0.10', function () {
    $calculator = new AhpCalculator;

    // Matriks acak tidak konsisten (melanggar transitivitas secara sengaja).
    $matrix = [
        [1, 9, 1 / 9],
        [1 / 9, 1, 9],
        [9, 1 / 9, 1],
    ];

    $result = $calculator->calculate($matrix);

    expect($result->cr)->toBeGreaterThan(0.10)
        ->and($result->isConsistent())->toBeFalse();
});

it('menghasilkan bobot sama besar untuk matriks identitas (semua faktor setara)', function () {
    $calculator = new AhpCalculator;

    $matrix = [
        [1, 1, 1],
        [1, 1, 1],
        [1, 1, 1],
    ];

    $result = $calculator->calculate($matrix);

    foreach ($result->weights as $w) {
        expect(round($w, 4))->toBe(round(1 / 3, 4));
    }

    expect($result->cr)->toBe(0.0);
});

it('menghitung model default KARSA (hotspot=2x cuaca, 3x kerentanan) sesuai CLAUDE.md', function () {
    $calculator = new AhpCalculator;

    $matrix = [
        [1, 2, 3],
        [1 / 2, 1, 2],
        [1 / 3, 1 / 2, 1],
    ];

    $result = $calculator->calculate($matrix);

    // CLAUDE.md §9.2: bobot perkiraan ~ 0.54 / 0.30 / 0.16
    expect(round($result->weights[0], 2))->toBe(0.54)
        ->and(round($result->weights[1], 2))->toBe(0.30)
        ->and(round($result->weights[2], 2))->toBe(0.16)
        ->and($result->isConsistent())->toBeTrue();
});

it('menolak matriks yang tidak persegi', function () {
    $calculator = new AhpCalculator;

    $calculator->calculate([
        [1, 2],
        [1 / 2, 1, 3],
    ]);
})->throws(InvalidArgumentException::class);
