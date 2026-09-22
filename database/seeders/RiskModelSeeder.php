<?php

namespace Database\Seeders;

use App\Models\RiskModel;
use App\Services\Risk\AhpCalculator;
use Illuminate\Database\Seeder;

/**
 * Model AHP default (CLAUDE.md §9.2): hotspot vs cuaca = 2, hotspot vs
 * kerentanan = 3, cuaca vs kerentanan = 2 -> bobot ~0,54/0,30/0,16.
 */
class RiskModelSeeder extends Seeder
{
    public function run(): void
    {
        $matrix = [
            [1, 2, 3],
            [1 / 2, 1, 2],
            [1 / 3, 1 / 2, 1],
        ];

        // Sub-matriks cuaca: suhu maks paling berpengaruh, lalu hari tanpa hujan,
        // lalu kelembapan, angin paling kecil pengaruhnya (nilai contoh, bisa diubah admin).
        $subMatrix = [
            [1, 2, 3, 2],
            [1 / 2, 1, 2, 1],
            [1 / 3, 1 / 2, 1, 1 / 2],
            [1 / 2, 1, 2, 1],
        ];

        $calculator = new AhpCalculator;
        $main = $calculator->calculate($matrix);
        $sub = $calculator->calculate($subMatrix);

        RiskModel::query()->update(['is_active' => false]);

        RiskModel::updateOrCreate(
            ['name' => 'Model AHP default'],
            [
                'matrix' => $matrix,
                'sub_matrix' => $subMatrix,
                'weights' => [
                    'hotspot' => round($main->weights[0], 4),
                    'cuaca' => round($main->weights[1], 4),
                    'kerentanan' => round($main->weights[2], 4),
                ],
                'sub_weights' => [
                    'temp_max' => round($sub->weights[0], 4),
                    'rh_min' => round($sub->weights[1], 4),
                    'wind_max' => round($sub->weights[2], 4),
                    'dry_days' => round($sub->weights[3], 4),
                ],
                'cr' => round($main->cr, 4),
                'sub_cr' => round($sub->cr, 4),
                'thresholds' => config('karsa.risk.thresholds'),
                'params' => null,
                'is_active' => true,
            ]
        );
    }
}
