<?php

namespace App\Livewire\Public;

use App\Models\District;
use App\Models\Hotspot;
use App\Models\WeatherObservation;
use App\Services\Ai\GeminiRiskAnalyzer;
use Livewire\Component;

class DetailWilayah extends Component
{
    public District $district;

    public bool $aiLoading = true;
    public ?array $aiAnalysis = null;

    public function mount(District $district): void
    {
        $this->district = $district;
    }

    public function loadAiAnalysis(?GeminiRiskAnalyzer $analyzer = null): void
    {
        $analyzer ??= app(GeminiRiskAnalyzer::class);

        $score = $this->district->riskScores()->latest('calculated_at')->first();

        $weather = WeatherObservation::where('district_id', $this->district->id)
            ->where('is_forecast', false)
            ->latest('observed_at')
            ->first();

        $hotspots = Hotspot::where('district_id', $this->district->id)
            ->where('detected_at', '>=', now()->subHours(24))
            ->get();

        if ($hotspots->isEmpty()) {
            $hotspots = Hotspot::where('detected_at', '>=', now()->subHours(24))->limit(5)->get();
        }

        $this->aiAnalysis = $analyzer->analyzeRiskAndPredict($this->district, $score, $hotspots, $weather);
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
        $score = $this->district->riskScores()->latest('calculated_at')->first();

        $trend = $this->district->riskScores()
            ->where('calculated_at', '>=', now()->subDays(7))
            ->orderBy('calculated_at')
            ->get(['calculated_at', 'score'])
            ->groupBy(fn ($s) => $s->calculated_at->toDateString())
            ->map(fn ($day) => round($day->last()->score, 1))
            ->values();

        $weather = WeatherObservation::where('district_id', $this->district->id)
            ->where('is_forecast', false)
            ->latest('observed_at')
            ->first();

        $hotspots = Hotspot::where('district_id', $this->district->id)
            ->where('detected_at', '>=', now()->subHours(24))
            ->get();

        return view('livewire.public.detail-wilayah', [
            'score' => $score,
            'trend' => $trend,
            'weather' => $weather,
            'hotspots' => $hotspots,
            'aiAnalysis' => $this->aiAnalysis,
            'aiLoading' => $this->aiLoading,
        ])->layout('components.layouts.public', ['title' => $this->district->name]);
    }
}
