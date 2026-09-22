<?php

namespace App\Livewire\Public;

use App\Models\District;
use App\Models\Regency;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PetaRisiko extends Component
{
    public ?int $focusRegencyId = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->focusRegencyId = $user?->home_regency_id
            ?? Regency::where('code', '3604')->value('id'); // default: Kabupaten Serang
    }

    public function render()
    {
        $districts = District::with('regency')
            ->where('is_monitored', true)
            ->get()
            ->map(function (District $d) {
                $score = $d->riskScores()->latest('calculated_at')->first();

                return [
                    'id' => $d->id,
                    'slug' => $d->slug,
                    'name' => $d->name,
                    'lat' => (float) $d->centroid_lat,
                    'lng' => (float) $d->centroid_lng,
                    'geometry' => $d->geometry,
                    'score' => $score?->score,
                    'level' => $score?->level?->value,
                    'color' => $score?->level?->color() ?? '#DDEBD3',
                ];
            });

        $focusRegency = $this->focusRegencyId ? Regency::find($this->focusRegencyId) : null;

        return view('livewire.public.peta-risiko', [
            'districts' => $districts,
            'focusRegency' => $focusRegency,
        ])->layout('components.layouts.public', ['title' => 'Peta Risiko Karhutla', 'withBottomNav' => true]);
    }
}
