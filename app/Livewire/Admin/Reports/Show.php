<?php

namespace App\Livewire\Admin\Reports;

use App\Enums\ReportStatus;
use App\Models\ActivityLog;
use App\Models\AuthorityContact;
use App\Models\Hotspot;
use App\Models\Report;
use App\Services\Notify\AuthorityNotifier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Report $report;

    public string $rejectionReason = '';

    public function mount(Report $report): void
    {
        $this->report = $report;
    }

    private function logStatus(ReportStatus $from, ReportStatus $to, ?string $note = null): void
    {
        $this->report->statusLogs()->create([
            'from_status' => $from->value,
            'to_status' => $to->value,
            'changed_by' => Auth::id(),
            'note' => $note,
        ]);
    }

    public function verify(): void
    {
        $from = $this->report->status;
        $this->report->update(['status' => ReportStatus::Terverifikasi->value, 'verified_by' => Auth::id(), 'verified_at' => now()]);
        $this->logStatus($from, ReportStatus::Terverifikasi);

        $this->report->user->increment('trust_score', 3);
        $this->report->user->update(['trust_score' => min(100, $this->report->user->trust_score)]);

        ActivityLog::record('verifikasi_laporan', $this->report);
        session()->flash('status', 'Laporan diverifikasi.');
    }

    public function reject(): void
    {
        $this->validate(['rejectionReason' => 'required|string|min:5']);

        $from = $this->report->status;
        $this->report->update(['status' => ReportStatus::Ditolak->value, 'rejection_reason' => $this->rejectionReason]);
        $this->logStatus($from, ReportStatus::Ditolak, $this->rejectionReason);

        $this->report->user->update(['trust_score' => max(0, $this->report->user->trust_score - 5)]);

        ActivityLog::record('tolak_laporan', $this->report, ['alasan' => $this->rejectionReason]);
        session()->flash('status', 'Laporan ditolak.');
    }

    public function forward(AuthorityNotifier $notifier): void
    {
        $from = $this->report->status;
        $this->report->update(['status' => ReportStatus::Diteruskan->value, 'forwarded_at' => now()]);
        $this->logStatus($from, ReportStatus::Diteruskan);

        $contacts = AuthorityContact::where('district_id', $this->report->district_id)
            ->orWhere('regency_id', $this->report->district?->regency_id)
            ->get();

        foreach ($contacts as $contact) {
            $notifier->notifyVerifiedReport($contact, $this->report);
        }

        ActivityLog::record('teruskan_laporan', $this->report, ['jumlah_kontak' => $contacts->count()]);
        session()->flash('status', 'Laporan diteruskan ke '.$contacts->count().' kontak instansi.');
    }

    public function complete(): void
    {
        $from = $this->report->status;
        $this->report->update(['status' => ReportStatus::Selesai->value]);
        $this->logStatus($from, ReportStatus::Selesai);

        ActivityLog::record('selesaikan_laporan', $this->report);
        session()->flash('status', 'Laporan ditandai selesai.');
    }

    public function render()
    {
        $nearestHotspot = Hotspot::where('detected_at', '>=', now()->subDay())
            ->get()
            ->map(fn (Hotspot $h) => [
                'hotspot' => $h,
                'distance' => (new \App\Services\Reports\TrustScorer)->distanceKm($this->report->lat, $this->report->lng, (float) $h->lat, (float) $h->lng),
            ])
            ->sortBy('distance')
            ->first();

        $nearbyReports = Report::where('id', '!=', $this->report->id)
            ->where('created_at', '>=', $this->report->created_at->copy()->subHours(6))
            ->get()
            ->filter(fn (Report $r) => (new \App\Services\Reports\TrustScorer)->distanceKm($this->report->lat, $this->report->lng, (float) $r->lat, (float) $r->lng) <= 2);

        $reporterHistory = Report::where('user_id', $this->report->user_id)
            ->where('id', '!=', $this->report->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.admin.reports.show', [
            'nearestHotspot' => $nearestHotspot,
            'nearbyReports' => $nearbyReports,
            'reporterHistory' => $reporterHistory,
        ])->layout('components.layouts.admin', ['title' => 'Detail Laporan']);
    }
}
