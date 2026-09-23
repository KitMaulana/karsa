<?php

namespace App\Livewire\Public;

use App\Models\Alert;
use App\Models\District;
use App\Models\Hotspot;
use App\Models\WeatherObservation;
use App\Services\Ai\GeminiRiskAnalyzer;
use Livewire\Component;

class Peringatan extends Component
{
    public bool $aiLoading = true;
    public ?array $aiAnalysis = null;

    public function loadAiAnalysis(?GeminiRiskAnalyzer $analyzer = null): void
    {
        $analyzer ??= app(GeminiRiskAnalyzer::class);

        $active = Alert::with('district')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereIn('to_level', ['tinggi', 'sangat_tinggi'])
            ->latest()
            ->get();

        $districtWithHotspot = District::whereHas('hotspots', function ($q) {
            $q->where('detected_at', '>=', now()->subHours(24));
        })->first();

        $targetDistrict = $active->first()?->district
            ?? $districtWithHotspot
            ?? District::where('slug', 'ciruas')->first()
            ?? District::where('is_monitored', true)->first();

        if ($targetDistrict) {
            $latestScore = $targetDistrict->riskScores()->latest('calculated_at')->first();
            $hotspots = Hotspot::where('district_id', $targetDistrict->id)
                ->where('detected_at', '>=', now()->subHours(24))
                ->get();

            // Jika di kecamatan ini belum ada hotspot, ambil sampel hotspot aktif di wilayah Kabupaten Serang
            if ($hotspots->isEmpty()) {
                $hotspots = Hotspot::where('detected_at', '>=', now()->subHours(24))->limit(5)->get();
            }

            $weather = WeatherObservation::where('district_id', $targetDistrict->id)
                ->where('is_forecast', false)
                ->latest('observed_at')
                ->first();

            $analysisData = $analyzer->analyzeRiskAndPredict($targetDistrict, $latestScore, $hotspots, $weather);

            $this->aiAnalysis = [
                'district' => $targetDistrict,
                'district_name' => $targetDistrict->name,
                'district_slug' => $targetDistrict->slug,
                'score' => $latestScore,
                'data' => $analysisData,
                'hotspot_count' => $hotspots->count(),
            ];
        }

        $this->aiLoading = false;
    }

    public function refreshAiAnalysis(): void
    {
        $this->aiLoading = true;
        $this->aiAnalysis = null;
        $this->loadAiAnalysis();
    }

    public function render()
    {
        $active = Alert::with('district')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereIn('to_level', ['tinggi', 'sangat_tinggi'])
            ->latest()
            ->get();

        $history = Alert::with('district')
            ->latest()
            ->limit(20)
            ->get();

        return view('livewire.public.peringatan', [
            'active' => $active,
            'history' => $history,
            'aiAnalysis' => $this->aiAnalysis,
            'aiLoading' => $this->aiLoading,
        ])->layout('components.layouts.public', ['title' => 'Peringatan & Status', 'withBottomNav' => true]);
    }
}
