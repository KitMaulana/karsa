<?php

namespace App\Services\Risk;

final readonly class AhpResult
{
    /**
     * @param  float[]  $weights  bobot ternormalisasi (jumlah = 1), urutan sesuai baris matriks
     * @param  float[]  $weightsGeometricMean  bobot pembanding dari rata-rata geometrik baris
     */
    public function __construct(
        public array $weights,
        public array $weightsGeometricMean,
        public float $lambdaMax,
        public float $ci,
        public float $cr,
        public float $ri,
        public int $n,
    ) {}

    public function isConsistent(?float $maxCr = null): bool
    {
        $maxCr ??= (float) config('karsa.risk.cr_max', 0.10);

        return $this->n <= 2 || $this->cr <= $maxCr;
    }

    public function toArray(): array
    {
        return [
            'weights' => $this->weights,
            'weights_geometric_mean' => $this->weightsGeometricMean,
            'lambda_max' => $this->lambdaMax,
            'ci' => $this->ci,
            'cr' => $this->cr,
            'ri' => $this->ri,
            'n' => $this->n,
            'consistent' => $this->isConsistent(),
        ];
    }
}
