<?php

namespace App\Services\Weather;

use Carbon\CarbonInterface;

final readonly class WeatherObservationDTO
{
    public function __construct(
        public CarbonInterface $observedAt,
        public ?float $tempMax,
        public ?float $rhMin,
        public ?float $windMax,
        public ?float $rainMm,
        public string $source,
        public bool $isForecast = false,
    ) {}
}
