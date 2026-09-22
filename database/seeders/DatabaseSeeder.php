<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RegencySeeder::class,
            DistrictSeeder::class,
            RiskModelSeeder::class,
            RecommendationSeeder::class,
            PostSeeder::class,
            ProgramSeeder::class,
            SuperadminSeeder::class,
        ]);
    }
}
