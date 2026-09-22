<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RegencyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'province' => 'Banten',
            'code' => fake()->unique()->numerify('36##'),
            'name' => $name,
            'slug' => Str::slug($name),
            'centroid_lat' => fake()->randomFloat(6, -6.4, -5.8),
            'centroid_lng' => fake()->randomFloat(6, 105.9, 106.4),
        ];
    }
}
