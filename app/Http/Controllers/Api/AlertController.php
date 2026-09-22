<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    /**
     * GET /api/v1/alerts/active -- peringatan 24 jam terakhir untuk kecamatan level tinggi/sangat tinggi.
     */
    public function active(): JsonResponse
    {
        $alerts = Alert::with('district')
            ->whereIn('to_level', ['tinggi', 'sangat_tinggi'])
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Alert $a) => [
                'id' => $a->id,
                'district' => $a->district->name,
                'district_slug' => $a->district->slug,
                'from_level' => $a->from_level?->value,
                'to_level' => $a->to_level->value,
                'score' => $a->score,
                'causes' => $a->causes,
                'message' => $a->message,
                'is_manual' => $a->is_manual,
                'created_at' => $a->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $alerts]);
    }
}
