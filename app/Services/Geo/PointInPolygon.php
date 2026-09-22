<?php

namespace App\Services\Geo;

/**
 * Uji titik-dalam-poligon sederhana (ray casting) untuk geometri GeoJSON
 * (Polygon & MultiPolygon), dipakai untuk menentukan district_id hotspot
 * tanpa dependensi ekstensi spasial database.
 */
class PointInPolygon
{
    /**
     * @param  array  $geometry  GeoJSON geometry: {type: "Polygon"|"MultiPolygon", coordinates: [...]}
     */
    public static function test(float $lat, float $lng, array $geometry): bool
    {
        if (! self::withinBbox($lat, $lng, $geometry)) {
            return false;
        }

        $type = $geometry['type'] ?? null;
        $coordinates = $geometry['coordinates'] ?? [];

        return match ($type) {
            'Polygon' => self::testPolygon($lat, $lng, $coordinates),
            'MultiPolygon' => collect($coordinates)->some(fn ($polygon) => self::testPolygon($lat, $lng, $polygon)),
            default => false,
        };
    }

    /**
     * Poligon GeoJSON: array cincin [ [lng,lat], ... ], cincin pertama = luar,
     * berikutnya = lubang.
     */
    private static function testPolygon(float $lat, float $lng, array $rings): bool
    {
        if ($rings === []) {
            return false;
        }

        $outer = self::rayCast($lat, $lng, $rings[0]);

        if (! $outer) {
            return false;
        }

        for ($i = 1; $i < count($rings); $i++) {
            if (self::rayCast($lat, $lng, $rings[$i])) {
                return false; // titik jatuh di lubang
            }
        }

        return true;
    }

    /**
     * @param  array  $ring  array titik [lng, lat]
     */
    private static function rayCast(float $lat, float $lng, array $ring): bool
    {
        $inside = false;
        $count = count($ring);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = $ring[$i][0];
            $yi = $ring[$i][1];
            $xj = $ring[$j][0];
            $yj = $ring[$j][1];

            $intersects = (($yi > $lat) !== ($yj > $lat))
                && ($lng < ($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: PHP_FLOAT_EPSILON) + $xi);

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    private static function withinBbox(float $lat, float $lng, array $geometry): bool
    {
        $bbox = $geometry['bbox'] ?? null;

        if ($bbox === null) {
            return true; // tidak ada bbox tersimpan, lanjut uji penuh
        }

        [$west, $south, $east, $north] = $bbox;

        return $lng >= $west && $lng <= $east && $lat >= $south && $lat <= $north;
    }
}
