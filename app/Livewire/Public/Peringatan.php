<?php

namespace App\Livewire\Public;

use App\Models\Alert;
use Livewire\Component;

class Peringatan extends Component
{
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
        ])->layout('components.layouts.public', ['title' => 'Peringatan & Status', 'withBottomNav' => true]);
    }
}
