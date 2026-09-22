<?php

namespace App\Services\Hotspot;

use App\Enums\HotspotConfidence;
use Carbon\CarbonInterface;

final readonly class HotspotDTO
{
    public function __construct(
        public string $source,
        public ?string $satellite,
        public float $lat,
        public float $lng,
        public HotspotConfidence $confidence,
        public string|int|float|null $confidenceRaw,
        public ?float $frp,
        public CarbonInterface $detectedAt,
        public string $externalId,
        public array $raw = [],
    ) {}

    /**
     * Kunci dedup kasar berdasarkan koordinat yang dibulatkan + sumber + waktu,
     * dipakai untuk deteksi duplikat exact sebelum pencocokan jarak/waktu penuh.
     */
    public function roughKey(): string
    {
        return sprintf(
            '%s:%s:%s:%s',
            $this->source,
            round($this->lat, 3),
            round($this->lng, 3),
            $this->detectedAt->format('YmdH')
        );
    }
}
