<?php

namespace App\Livewire\Public;

use App\Models\District;
use App\Services\Risk\DataConfidence;
use Livewire\Component;

class DetailWilayah extends Component
{
    public District $district;

    public function mount(District $district): void
    {
        $this->district = $district;
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

        $weather = \App\Models\WeatherObservation::where('district_id', $this->district->id)
            ->where('is_forecast', false)
            ->latest('observed_at')
            ->first();

        return view('livewire.public.detail-wilayah', [
            'score' => $score,
            'trend' => $trend,
            'weather' => $weather,
        ])->layout('components.layouts.public', ['title' => $this->district->name]);
    }
}
