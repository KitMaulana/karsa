<?php

namespace App\Services\Hotspot;

use Illuminate\Support\Collection;

/**
 * Gabungkan hasil beberapa provider, dedup titik yang sama, dan tandai
 * `corroborated` bila muncul dari lebih dari satu sumber.
 *
 * Dua titik dianggap sama bila jarak <= dedup.distance_km DAN selisih
 * waktu <= dedup.time_hours (CLAUDE.md §8.3).
 */
class HotspotAggregator
{
    public function __construct(
        private readonly float $distanceKm = 1.0,
        private readonly float $timeHours = 3.0,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            distanceKm: (float) config('karsa.hotspot.dedup.distance_km', 1),
            timeHours: (float) config('karsa.hotspot.dedup.time_hours', 3),
        );
    }

    /**
     * @param  array<string, Collection<int, HotspotDTO>>  $bySource  key = nama sumber
     * @return Collection<int, array{dto: HotspotDTO, sources: string[], corroborated: bool}>
     */
    public function merge(array $bySource): Collection
    {
        $all = collect($bySource)
            ->flatMap(fn (Collection $items, string $source) => $items->map(fn (HotspotDTO $dto) => [
                'dto' => $dto,
                'sources' => [$source],
            ]))
            ->values();

        $clusters = [];

        foreach ($all as $entry) {
            $matched = false;

            foreach ($clusters as &$cluster) {
                $rep = $cluster['dto'];

                if ($this->distanceKm($rep, $entry['dto']) <= $this->distanceKm
                    && abs($rep->detectedAt->diffInHours($entry['dto']->detectedAt, absolute: true)) <= $this->timeHours) {
                    $cluster['sources'] = array_values(array_unique(array_merge($cluster['sources'], $entry['sources'])));

                    // Simpan representatif dengan confidence tertinggi.
                    if ($entry['dto']->confidence->weight() > $rep->confidence->weight()) {
                        $cluster['dto'] = $entry['dto'];
                    }

                    $matched = true;
                    break;
                }
            }
            unset($cluster);

            if (! $matched) {
                $clusters[] = $entry;
            }
        }

        return collect($clusters)->map(fn (array $c) => [
            'dto' => $c['dto'],
            'sources' => $c['sources'],
            'corroborated' => count($c['sources']) > 1,
        ])->values();
    }

    private function distanceKm(HotspotDTO $a, HotspotDTO $b): float
    {
        $earthRadiusKm = 6371.0;

        $latDelta = deg2rad($b->lat - $a->lat);
        $lngDelta = deg2rad($b->lng - $a->lng);

        $h = sin($latDelta / 2) ** 2
            + cos(deg2rad($a->lat)) * cos(deg2rad($b->lat)) * sin($lngDelta / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($h)));
    }
}
