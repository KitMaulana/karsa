<?php

namespace App\Livewire\Public;

use App\Models\District;
use App\Models\Hotspot;
use App\Models\Recommendation;
use App\Models\WeatherObservation;
use App\Services\Ai\GeminiRiskAnalyzer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class AksiPencegahan extends Component
{
    public string $audience = 'warga_umum';

    #[Url(as: 'kecamatan')]
    public string $kecamatanSlug = '';

    public ?District $district = null;

    public bool $aiLoading = true;
    public ?array $aiMitigation = null;

    public function mount(): void
    {
        $this->district = $this->kecamatanSlug
            ? District::where('slug', $this->kecamatanSlug)->first()
            : (Auth::user()?->watchDistricts()->first() ?? District::where('slug', 'ciruas')->first() ?? District::where('is_monitored', true)->first());
    }

    public function setAudience(string $audience): void
    {
        if ($this->audience !== $audience) {
            $this->audience = $audience;
            $this->aiLoading = true;
            $this->aiMitigation = null;
            $this->loadAiMitigation();
        }
    }

    public function loadAiMitigation(?GeminiRiskAnalyzer $analyzer = null): void
    {
        $analyzer ??= app(GeminiRiskAnalyzer::class);

        if ($this->district) {
            $latestScore = $this->district->riskScores()->latest('calculated_at')->first();
            $level = $latestScore?->level ?? \App\Enums\RiskLevel::Rendah;

            $hotspots = Hotspot::where('district_id', $this->district->id)
                ->where('detected_at', '>=', now()->subHours(24))
                ->get();

            if ($hotspots->isEmpty()) {
                $hotspots = Hotspot::where('detected_at', '>=', now()->subHours(24))->limit(5)->get();
            }

            $weather = WeatherObservation::where('district_id', $this->district->id)
                ->where('is_forecast', false)
                ->latest('observed_at')
                ->first();

            $this->aiMitigation = $analyzer->generateMitigationActions($this->district, $level, $hotspots, $weather, $this->audience);
        }

        $this->aiLoading = false;
    }

    public function refreshAiMitigation(): void
    {
        $this->aiLoading = true;
        $this->aiMitigation = null;
        $this->loadAiMitigation();
    }

    public function render()
    {
        $latestScore = $this->district?->riskScores()->latest('calculated_at')->first();
        $level = $latestScore?->level ?? \App\Enums\RiskLevel::Rendah;

        $recommendations = Recommendation::where('level', $level->value)
            ->where('audience', $this->audience)
            ->orderBy('order')
            ->get();

        $projection = $latestScore?->detail['projection_72h'] ?? [];

        return view('livewire.public.aksi-pencegahan', [
            'level' => $level,
            'recommendations' => $recommendations,
            'projection' => $projection,
            'aiMitigation' => $this->aiMitigation,
            'aiLoading' => $this->aiLoading,
        ])->layout('components.layouts.public', ['title' => 'Aksi Pencegahan', 'withBottomNav' => true]);
    }
}
