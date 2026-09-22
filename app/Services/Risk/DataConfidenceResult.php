<?php

namespace App\Services\Risk;

final readonly class DataConfidenceResult
{
    public function __construct(
        public float $score,
        public string $level, // tinggi | sedang | rendah
        public array $components,
    ) {}

    public function label(): string
    {
        return match ($this->level) {
            'tinggi' => 'Tinggi',
            'sedang' => 'Sedang',
            default => 'Rendah',
        };
    }
}
