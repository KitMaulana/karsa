<?php

use App\Services\Reports\TrustScoreInput;
use App\Services\Reports\TrustScorer;

it('memberi skor tinggi untuk laporan valid dekat hotspot dengan EXIF cocok', function () {
    $scorer = new TrustScorer;

    $result = $scorer->score(new TrustScoreInput(
        hasNearbyHotspot: true,        // +25
        exifGpsWithinRange: true,      // +20
        geolocationAccuracyM: 30,      // +15
        hasNeighborReport: true,       // +15
        exifTimeRecent: true,          // +10
        reporterVerifiedCount: 2,      // +6 (min 15)
        reporterRejectedCount: 0,
    ));

    expect($result->score)->toBe(91.0)
        ->and($result->flags)->toBeEmpty();
});

it('membatasi skor maksimum di 100 walau semua bonus terpenuhi', function () {
    $scorer = new TrustScorer;

    $result = $scorer->score(new TrustScoreInput(
        hasNearbyHotspot: true,
        exifGpsWithinRange: true,
        geolocationAccuracyM: 10,
        hasNeighborReport: true,
        exifTimeRecent: true,
        reporterVerifiedCount: 10,
        reporterRejectedCount: 0,
    ));

    expect($result->score)->toBe(100.0);
});

it('menandai bendera merah foto duplikat tanpa mengubah skor', function () {
    $scorer = new TrustScorer;

    $result = $scorer->score(new TrustScoreInput(
        hasNearbyHotspot: true,
        isDuplicatePhoto: true,
    ));

    expect($result->score)->toBe(25.0)
        ->and($result->flags)->toContain('foto_duplikat');
});

it('menandai lokasi di luar Indonesia', function () {
    $scorer = new TrustScorer;

    // Bangkok, jelas di luar bounding box Indonesia (lintang > 6.5).
    expect($scorer->isOutsideIndonesia(13.7563, 100.5018))->toBeTrue()
        ->and($scorer->isOutsideIndonesia(-6.0812, 106.1572))->toBeFalse(); // Ciruas
});

it('menandai spam laporan berlebih & akun baru tanpa mengubah skor', function () {
    $scorer = new TrustScorer;

    $result = $scorer->score(new TrustScoreInput(
        rateExceeded: true,
        isNewAccount: true,
    ));

    expect($result->score)->toBe(0.0)
        ->and($result->flags)->toContain('laporan_berlebih')
        ->and($result->flags)->toContain('akun_baru');
});

it('reputasi pelapor tidak pernah negatif walau banyak laporan ditolak', function () {
    $scorer = new TrustScorer;

    expect($scorer->reputationBonus(0, 10))->toBe(0.0)
        ->and($scorer->reputationBonus(5, 0))->toBe(15.0); // dipotong maksimum 15
});

it('menentukan prioritas otomatis saat skor >= 80', function () {
    $scorer = new TrustScorer;

    $high = $scorer->score(new TrustScoreInput(hasNearbyHotspot: true, exifGpsWithinRange: true, geolocationAccuracyM: 50, hasNeighborReport: true, exifTimeRecent: true));
    $low = $scorer->score(new TrustScoreInput(hasNearbyHotspot: true));

    expect($scorer->shouldAutoPrioritize($high))->toBeTrue()
        ->and($scorer->shouldAutoPrioritize($low))->toBeFalse();
});
