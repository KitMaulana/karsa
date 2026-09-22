<?php

namespace Database\Factories;

use App\Services\Risk\AhpCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;

class RiskModelFactory extends Factory
{
    public function definition(): array
    {
        // Matriks utama default CLAUDE.md §9.2: hotspot vs cuaca = 2, hotspot vs kerentanan = 3, cuaca vs kerentanan = 2.
        $matrix = [
            [1, 2, 3],
            [1 / 2, 1, 2],
            [1 / 3, 1 / 2, 1],
        ];

        $subMatrix = [
            [1, 2, 3, 2],
            [1 / 2, 1, 2, 1],
            [1 / 3, 1 / 2, 1, 1 / 2],
            [1 / 2, 1, 2, 1],
        ];

        $calculator = new AhpCalculator;
        $main = $calculator->calculate($matrix);
        $sub = $calculator->calculate($subMatrix);

        return [
            'name' => 'Model AHP default',
            'matrix' => $matrix,
            'sub_matrix' => $subMatrix,
            'weights' => [
                'hotspot' => $main->weights[0],
                'cuaca' => $main->weights[1],
                'kerentanan' => $main->weights[2],
            ],
            'sub_weights' => [
                'temp_max' => $sub->weights[0],
                'rh_min' => $sub->weights[1],
                'wind_max' => $sub->weights[2],
                'dry_days' => $sub->weights[3],
            ],
            'cr' => $main->cr,
            'sub_cr' => $sub->cr,
            'thresholds' => config('karsa.risk.thresholds'),
            'params' => null,
            'is_active' => true,
        ];
    }
}
