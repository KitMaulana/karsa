<?php

namespace App\Services\Hotspot;

use Illuminate\Support\Collection;

interface HotspotProvider
{
    /**
     * Nama sumber, dipakai sebagai label & kunci log (mis. "sipongi", "firms").
     */
    public function key(): string;

    /**
     * Ambil hotspot dalam kotak batas (bbox) untuk rentang jam ke belakang.
     *
     * @return Collection<int, HotspotDTO>
     *
     * @throws \App\Services\Hotspot\HotspotProviderException bila gagal & tak bisa dipulihkan
     */
    public function fetch(BBox $bbox, int $hoursBack = 24): Collection;
}
