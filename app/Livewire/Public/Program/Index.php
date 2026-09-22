<?php

namespace App\Livewire\Public\Program;

use App\Models\Program;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $programs = Program::where('status', 'terbit')->latest('starts_at')->get();
        $donasiEnabled = (bool) \App\Models\Setting::get('donasi_enabled', config('karsa.donasi_enabled'));

        return view('livewire.public.program.index', ['programs' => $programs, 'donasiEnabled' => $donasiEnabled])
            ->layout('components.layouts.public', ['title' => 'Penggalangan Dana', 'withBottomNav' => true]);
    }
}
