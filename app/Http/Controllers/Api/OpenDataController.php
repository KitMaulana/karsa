<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class OpenDataController extends Controller
{
    /**
     * GET /api/v1/open/reports.geojson -- laporan terverifikasi TANPA identitas
     * pelapor, wujud dukungan data terbuka ke SiPongi+/instansi (CLAUDE.md §15, §19).
     */
    public function reports(): JsonResponse
    {
        $geojson = Cache::remember('api:open:reports', 300, function () {
            $features = Report::where('status', ReportStatus::Terverifikasi->value)
                ->latest('verified_at')
                ->limit(500)
                ->get()
                ->map(fn (Report $r) => [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [$r->lng, $r->lat]],
                    'properties' => [
                        'type' => $r->type,
                        'verified_at' => $r->verified_at?->toIso8601String(),
                    ],
                ]);

            return ['type' => 'FeatureCollection', 'features' => $features];
        });

        return response()->json($geojson);
    }
}
