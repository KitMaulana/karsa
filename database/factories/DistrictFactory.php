<?php

namespace Database\Factories;

use App\Models\Regency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DistrictFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Kec. '.fake()->unique()->citySuffix().fake()->unique()->lastName();

        return [
            'regency_id' => Regency::factory(),
            'code' => fake()->unique()->numerify('36.04.##'),
            'name' => $name,
            'slug' => Str::slug($name),
            'centroid_lat' => fake()->randomFloat(6, -6.4, -5.8),
            'centroid_lng' => fake()->randomFloat(6, 105.9, 106.4),
            'area_ha' => fake()->randomFloat(2, 1000, 8000),
            'vulnerability_score' => fake()->numberBetween(10, 80),
            'is_monitored' => true,
        ];
    }
}
