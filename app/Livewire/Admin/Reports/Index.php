<?php

namespace App\Livewire\Admin\Reports;

use App\Enums\UserRole;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'baru';

    public function render()
    {
        $query = Report::with(['user', 'district'])->where('status', $this->status);

        $user = Auth::user();
        if ($user->role === UserRole::Verifikator) {
            $assignedDistrictIds = \App\Models\District::whereHas('watchers', fn ($q) => $q->where('user_id', $user->id))->pluck('id');
            // Verifikator hanya melihat laporan di wilayah tugasnya (memakai wilayah pantauan sebagai penugasan sederhana).
            $query->whereIn('district_id', $assignedDistrictIds);
        }

        $reports = $query->orderByDesc('trust_score')->orderBy('created_at')->paginate(20);

        return view('livewire.admin.reports.index', ['reports' => $reports])
            ->layout('components.layouts.admin', ['title' => 'Laporan Warga']);
    }
}
