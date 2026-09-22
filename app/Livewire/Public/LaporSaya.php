<?php

namespace App\Livewire\Public;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LaporSaya extends Component
{
    public function render()
    {
        $reports = Auth::user()->reports()->latest()->get();

        return view('livewire.public.lapor-saya', ['reports' => $reports])
            ->layout('components.layouts.public', ['title' => 'Laporan Saya', 'withBottomNav' => true]);
    }
}
