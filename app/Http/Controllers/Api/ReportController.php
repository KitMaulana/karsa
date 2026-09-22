<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Services\Reports\ReportSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;

class ReportController extends Controller
{
    /**
     * POST /api/v1/reports -- dipakai form /lapor & antrean offline (service worker).
     * Rate limit tambahan per IP (CLAUDE.md §11.2: 10/jam/IP di luar 3/jam/akun).
     */
    public function store(StoreReportRequest $request, ReportSubmissionService $service): JsonResponse
    {
        $ipKey = 'reports-ip:'.$request->ip();

        if (RateLimiter::tooManyAttempts($ipKey, config('karsa.reports.rate_limit_per_hour_ip', 10))) {
            return response()->json(['message' => 'Terlalu banyak laporan dari alamat ini, coba lagi nanti.'], 429);
        }
        RateLimiter::hit($ipKey, 3600);

        $report = $service->submit(
            $request->user(),
            [
                'lat' => (float) $request->input('lat'),
                'lng' => (float) $request->input('lng'),
                'accuracy_m' => $request->input('accuracy_m') ? (float) $request->input('accuracy_m') : null,
                'type' => $request->input('type'),
                'description' => $request->input('description'),
            ],
            $request->file('media', [])
        );

        return response()->json([
            'message' => 'Laporan diterima, terima kasih atas partisipasi Anda.',
            'data' => ['id' => $report->id, 'status' => $report->status->value],
        ], 201);
    }
}
