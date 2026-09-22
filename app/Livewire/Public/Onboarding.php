<?php

namespace App\Livewire\Public;

use Livewire\Component;

class Onboarding extends Component
{
    public function render()
    {
        return view('livewire.public.onboarding')
            ->layout('components.layouts.public', ['title' => 'Selamat datang di KARSA']);
    }
}
