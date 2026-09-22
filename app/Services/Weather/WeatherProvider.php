<?php

namespace App\Services\Weather;

use Illuminate\Support\Collection;

interface WeatherProvider
{
    public function key(): string;

    /**
     * @param  array<int, array{id:int, lat:float, lng:float}>  $points  titik pusat kecamatan
     * @return Collection<int, array{district_id:int, observations: Collection<int, WeatherObservationDTO>}>
     */
    public function fetchMany(array $points): Collection;
}
