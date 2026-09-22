<?php

namespace App\Livewire\Admin\ActivityLogs;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.admin.activity-logs.index', [
            'logs' => ActivityLog::with('user')->latest()->paginate(30),
        ])->layout('components.layouts.admin', ['title' => 'Log Aktivitas']);
    }
}
