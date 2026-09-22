<?php

namespace App\Livewire\Public;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notifikasi extends Component
{
    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $notifications = Auth::user()->notifications()->latest()->limit(30)->get();

        return view('livewire.public.notifikasi', ['notifications' => $notifications])
            ->layout('components.layouts.public', ['title' => 'Notifikasi', 'withBottomNav' => true]);
    }
}
