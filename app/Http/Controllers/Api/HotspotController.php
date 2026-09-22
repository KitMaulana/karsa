<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotspot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HotspotController extends Controller
{
    /**
     * GET /api/v1/hotspots?bbox=west,south,east,north&hours=24 -- GeoJSON.
     */
    public function index(Request $request): JsonResponse
    {
        $hours = min(168, max(1, (int) $request->query('hours', 24)));

        $query = Hotspot::where('detected_at', '>=', now()->subHours($hours));

        if ($bbox = $request->query('bbox')) {
            $parts = array_map('floatval', explode(',', $bbox));

            if (count($parts) === 4) {
                [$west, $south, $east, $north] = $parts;
                $query->whereBetween('lat', [$south, $north])->whereBetween('lng', [$west, $east]);
            }
        }

        $features = $query->limit(2000)->get()->map(fn (Hotspot $h) => [
            'type' => 'Feature',
            'geometry' => ['type' => 'Point', 'coordinates' => [$h->lng, $h->lat]],
            'properties' => [
                'id' => $h->id,
                'confidence' => $h->confidence->value,
                'confidence_color' => $h->confidence->color(),
                'sources' => $h->sources,
                'corroborated' => $h->corroborated,
                'detected_at' => $h->detected_at->toIso8601String(),
                'satellite' => $h->satellite,
                'frp' => $h->frp,
            ],
        ]);

        return response()->json(['type' => 'FeatureCollection', 'features' => $features]);
    }
}
