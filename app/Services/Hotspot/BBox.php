<?php

namespace App\Services\Hotspot;

final readonly class BBox
{
    public function __construct(
        public float $west,
        public float $south,
        public float $east,
        public float $north,
    ) {}

    public static function fromCenters(array $points, float $paddingDegrees = 0.2): self
    {
        $lats = array_column($points, 0);
        $lngs = array_column($points, 1);

        return new self(
            west: min($lngs) - $paddingDegrees,
            south: min($lats) - $paddingDegrees,
            east: max($lngs) + $paddingDegrees,
            north: max($lats) + $paddingDegrees,
        );
    }

    public function contains(float $lat, float $lng): bool
    {
        return $lat >= $this->south && $lat <= $this->north
            && $lng >= $this->west && $lng <= $this->east;
    }

    public function toFirmsPath(): string
    {
        return "{$this->west},{$this->south},{$this->east},{$this->north}";
    }
}
