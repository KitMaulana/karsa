<?php

namespace App\Livewire\Public;

use App\Models\District;
use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class AksiPencegahan extends Component
{
    public string $audience = 'warga_umum';

    #[Url(as: 'kecamatan')]
    public string $kecamatanSlug = '';

    public ?District $district = null;

    public function mount(): void
    {
        $this->district = $this->kecamatanSlug
            ? District::where('slug', $this->kecamatanSlug)->first()
            : Auth::user()?->watchDistricts()->first();
    }

    public function setAudience(string $audience): void
    {
        $this->audience = $audience;
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
        ])->layout('components.layouts.public', ['title' => 'Aksi Pencegahan', 'withBottomNav' => true]);
    }
}
