<?php

namespace App\Livewire\Admin;

use App\Enums\ReportStatus;
use App\Models\Alert;
use App\Models\DataSyncLog;
use App\Models\District;
use App\Models\Hotspot;
use App\Models\Report;
use App\Models\RiskScore;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $hotspot24h = Hotspot::where('detected_at', '>=', now()->subDay())->count();

        $districtsByLevel = District::where('is_monitored', true)->get()
            ->map(fn (District $d) => $d->riskScores()->latest('calculated_at')->first()?->level?->value)
            ->filter()
            ->countBy();

        $pendingReports = Report::whereIn('status', [ReportStatus::Baru->value, ReportStatus::Ditinjau->value])->count();
        $activeAlerts = Alert::where('created_at', '>=', now()->subHours(24))->count();
        $userCount = User::count();

        $trend30d = RiskScore::where('calculated_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(calculated_at) as date, AVG(score) as avg_score, SUM(s_hotspot > 0) as hotspot_days')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $sourceStatus = DataSyncLog::query()
            ->selectRaw('source, MAX(created_at) as last_sync')
            ->groupBy('source')
            ->get()
            ->map(function ($row) {
                $latest = DataSyncLog::where('source', $row->source)->latest()->first();

                return [
                    'source' => $row->source,
                    'status' => $latest->status,
                    'last_sync' => $latest->created_at,
                ];
            });

        return view('livewire.admin.dashboard', [
            'hotspot24h' => $hotspot24h,
            'districtsByLevel' => $districtsByLevel,
            'pendingReports' => $pendingReports,
            'activeAlerts' => $activeAlerts,
            'userCount' => $userCount,
            'trend30d' => $trend30d,
            'sourceStatus' => $sourceStatus,
        ])->layout('components.layouts.admin', ['title' => 'Dasbor']);
    }
}
