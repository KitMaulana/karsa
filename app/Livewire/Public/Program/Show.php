<?php

namespace App\Livewire\Public\Program;

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Program $program;

    public function mount(Program $program): void
    {
        $this->program = $program;
    }

    public function join(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login');

            return;
        }

        if ($this->program->isFull()) {
            session()->flash('status', 'Kuota relawan sudah penuh.');

            return;
        }

        $this->program->participants()->firstOrCreate(
            ['user_id' => Auth::id()],
            ['status' => 'terdaftar']
        );

        session()->flash('status', 'Anda terdaftar sebagai relawan. Terima kasih!');
    }

    public function render()
    {
        $isJoined = Auth::check() && $this->program->participants()->where('user_id', Auth::id())->exists();
        $donasiEnabled = (bool) \App\Models\Setting::get('donasi_enabled', config('karsa.donasi_enabled'));

        return view('livewire.public.program.show', ['isJoined' => $isJoined, 'donasiEnabled' => $donasiEnabled])
            ->layout('components.layouts.public', ['title' => $this->program->title]);
    }
}
