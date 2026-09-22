<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    /**
     * GET /api/v1/recommendations?level=&audience=
     */
    public function index(Request $request): JsonResponse
    {
        $query = Recommendation::query()->orderBy('order');

        if ($level = $request->query('level')) {
            $query->where('level', $level);
        }

        if ($audience = $request->query('audience')) {
            $query->where('audience', $audience);
        }

        return response()->json(['data' => $query->get()]);
    }
}
