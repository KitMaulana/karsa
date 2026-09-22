<?php

namespace App\Services\Reports;

final readonly class TrustScoreInput
{
    public function __construct(
        public bool $hasNearbyHotspot = false,
        public ?bool $exifGpsWithinRange = null, // null = tidak ada data EXIF GPS
        public ?float $geolocationAccuracyM = null,
        public bool $hasNeighborReport = false,
        public ?bool $exifTimeRecent = null, // null = tidak ada data EXIF waktu
        public int $reporterVerifiedCount = 0,
        public int $reporterRejectedCount = 0,
        public bool $isDuplicatePhoto = false,
        public bool $isOutsideIndonesia = false,
        public bool $rateExceeded = false,
        public bool $isNewAccount = false,
    ) {}
}
