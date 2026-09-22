<?php

namespace App\Services\Reports;

final readonly class TrustScoreResult
{
    /**
     * @param  array<int, string>  $flags  bendera merah, tidak memengaruhi skor
     */
    public function __construct(
        public float $score,
        public array $breakdown,
        public array $flags,
    ) {}
}
