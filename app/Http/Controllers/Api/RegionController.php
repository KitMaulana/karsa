<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Regency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RegionController extends Controller
{
    /**
     * GET /api/v1/regions -- ringan, tanpa geometri.
     */
    public function index(): JsonResponse
    {
        $regencies = Cache::remember('api:regions:index', 600, function () {
            return Regency::with(['districts' => function ($q) {
                $q->select('id', 'regency_id', 'name', 'slug', 'centroid_lat', 'centroid_lng', 'is_monitored');
            }])->get(['id', 'name', 'slug', 'code', 'centroid_lat', 'centroid_lng']);
        });

        return response()->json(['data' => $regencies]);
    }

    /**
     * GET /api/v1/regions/geojson?kabupaten=serang -- poligon + skor terkini, cache 10 menit.
     */
    public function geojson(Request $request): JsonResponse
    {
        $slug = $request->query('kabupaten');
        $cacheKey = 'api:regions:geojson:'.($slug ?? 'all');

        $geojson = Cache::remember($cacheKey, 600, function () use ($slug) {
            $query = \App\Models\District::with('regency')->whereNotNull('geometry');

            if ($slug) {
                $query->whereHas('regency', fn ($q) => $q->where('slug', $slug));
            }

            $features = $query->get()->map(function (\App\Models\District $d) {
                $score = $d->riskScores()->latest('calculated_at')->first();

                return [
                    'type' => 'Feature',
                    'geometry' => $d->geometry,
                    'properties' => [
                        'id' => $d->id,
                        'name' => $d->name,
                        'slug' => $d->slug,
                        'score' => $score?->score,
                        'level' => $score?->level?->value,
                    ],
                ];
            });

            return ['type' => 'FeatureCollection', 'features' => $features];
        });

        return response()->json($geojson);
    }
}
