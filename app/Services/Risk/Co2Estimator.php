<?php

namespace App\Services\Risk;

/**
 * Estimasi emisi CO2 indikatif, rumus IPCC 2006 Vol.4 Pers. 2.27:
 * L = A x MB x Cf x Gef x 10^-3 (ton CO2). CLAUDE.md §10.
 *
 * SELALU tampilkan sebagai "Estimasi indikatif" di UI -- bukan angka resmi.
 * Admin wajib mencocokkan MB/Cf/Gef dengan Tabel 2.4-2.5 IPCC 2006 Vol.4.
 */
class Co2Estimator
{
    public function estimateFromHotspotCount(int $hotspotCount, ?float $luasPerHotspotHa = null, ?float $mb = null, ?float $cf = null, ?float $gef = null): float
    {
        $luasPerHotspotHa ??= (float) config('karsa.co2.luas_per_hotspot_ha', 1.0);
        $area = $hotspotCount * $luasPerHotspotHa;

        return $this->estimate($area, $mb, $cf, $gef);
    }

    public function estimate(float $areaHa, ?float $mb = null, ?float $cf = null, ?float $gef = null): float
    {
        $mb ??= (float) config('karsa.co2.default_mb', 30.0);
        $cf ??= (float) config('karsa.co2.default_cf', 0.5);
        $gef ??= (float) config('karsa.co2.default_gef', 1580);

        return round($areaHa * $mb * $cf * $gef * 0.001, 2);
    }
}
