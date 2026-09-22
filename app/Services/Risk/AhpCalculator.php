<?php

namespace App\Services\Risk;

/**
 * Kalkulator AHP (Analytic Hierarchy Process): bobot lewat power method
 * (eigenvector), dibandingkan dengan rata-rata geometrik baris, plus
 * Consistency Index (CI) & Consistency Ratio (CR). CLAUDE.md §9.2.
 *
 * Matriks input harus reciprocal: matrix[i][j] = 1 / matrix[j][i], diagonal = 1.
 */
class AhpCalculator
{
    private const MAX_ITERATIONS = 1000;

    private const CONVERGENCE_EPSILON = 1e-10;

    public function calculate(array $matrix): AhpResult
    {
        $n = count($matrix);

        if ($n === 0) {
            throw new \InvalidArgumentException('Matriks tidak boleh kosong.');
        }

        foreach ($matrix as $row) {
            if (count($row) !== $n) {
                throw new \InvalidArgumentException('Matriks harus persegi (n x n).');
            }
        }

        $weightsGeometricMean = $this->geometricMeanWeights($matrix, $n);
        [$weights, $lambdaMax] = $this->powerMethod($matrix, $n);

        $ci = $n > 2 ? ($lambdaMax - $n) / ($n - 1) : 0.0;
        $ri = $this->randomIndex($n);
        $cr = $ri > 0 ? $ci / $ri : 0.0;

        return new AhpResult(
            weights: $weights,
            weightsGeometricMean: $weightsGeometricMean,
            lambdaMax: $lambdaMax,
            ci: $ci,
            cr: $cr,
            ri: $ri,
            n: $n,
        );
    }

    /**
     * @return float[]
     */
    private function geometricMeanWeights(array $matrix, int $n): array
    {
        $products = array_map(
            fn (array $row) => array_product($row) ** (1 / $n),
            $matrix
        );

        $sum = array_sum($products);

        return array_map(fn (float $v) => $sum > 0 ? $v / $sum : 1 / $n, $products);
    }

    /**
     * @return array{0: float[], 1: float} [bobot, lambdaMax]
     */
    private function powerMethod(array $matrix, int $n): array
    {
        $vector = array_fill(0, $n, 1 / $n);

        for ($iter = 0; $iter < self::MAX_ITERATIONS; $iter++) {
            $next = array_fill(0, $n, 0.0);

            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $next[$i] += $matrix[$i][$j] * $vector[$j];
                }
            }

            $sum = array_sum($next);

            if ($sum <= 0) {
                break;
            }

            $next = array_map(fn (float $v) => $v / $sum, $next);

            $delta = array_sum(array_map(fn ($a, $b) => abs($a - $b), $next, $vector));
            $vector = $next;

            if ($delta < self::CONVERGENCE_EPSILON) {
                break;
            }
        }

        // lambdaMax = rata-rata (A.w)_i / w_i
        $lambdaMax = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $rowSum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $rowSum += $matrix[$i][$j] * $vector[$j];
            }
            $lambdaMax += $vector[$i] > 0 ? $rowSum / $vector[$i] : 0;
        }
        $lambdaMax /= $n;

        return [$vector, $lambdaMax];
    }

    private function randomIndex(int $n): float
    {
        $table = config('karsa.risk.ri_table', [3 => 0.58, 4 => 0.90, 5 => 1.12]);

        if ($n <= 2) {
            return 0.0;
        }

        return (float) ($table[$n] ?? 1.12 + 0.1 * ($n - 5));
    }
}
