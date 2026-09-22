<?php

namespace App\Services\Reports;

use Carbon\CarbonImmutable;

final readonly class ExifData
{
    public function __construct(
        public ?float $lat = null,
        public ?float $lng = null,
        public ?CarbonImmutable $takenAt = null,
    ) {}

    public function hasGps(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }
}
