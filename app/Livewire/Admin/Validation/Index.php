<?php

namespace App\Livewire\Admin\Validation;

use App\Enums\ReportStatus;
use App\Models\District;
use App\Models\Hotspot;
use App\Models\Report;
use App\Models\RiskScore;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Index extends Component
{
    public string $startDate;

    public string $endDate;

    public function mount(): void
    {
        $this->startDate = now()->subDays(30)->toDateString();
        $this->endDate = now()->toDateString();
    }

    /**
     * Bandingkan skor harian (prediksi: level >= tinggi) dengan kejadian nyata
     * (hotspot high-confidence atau laporan terverifikasi hari itu) per
     * kecamatan, CLAUDE.md §7 poin 5.
     */
    private function buildContingency(): array
    {
        $districts = District::where('is_monitored', true)->get();
        $tp = $fp = $fn = $tn = 0;

        $period = Carbon::parse($this->startDate)->toPeriod(Carbon::parse($this->endDate));

        foreach ($districts as $district) {
            foreach ($period as $date) {
                $dayStart = $date->copy()->startOfDay();
                $dayEnd = $date->copy()->endOfDay();

                $score = RiskScore::where('district_id', $district->id)
                    ->whereBetween('calculated_at', [$dayStart, $dayEnd])
                    ->orderByDesc('calculated_at')
                    ->first();

                if (! $score) {
                    continue;
                }

                $predicted = in_array($score->level->value, ['tinggi', 'sangat_tinggi'], true);

                $actual = Hotspot::where('district_id', $district->id)
                    ->where('confidence', 'high')
                    ->whereBetween('detected_at', [$dayStart, $dayEnd])
                    ->exists()
                    || Report::where('district_id', $district->id)
                        ->where('status', ReportStatus::Terverifikasi->value)
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->exists();

                match (true) {
                    $predicted && $actual => $tp++,
                    $predicted && ! $actual => $fp++,
                    ! $predicted && $actual => $fn++,
                    default => $tn++,
                };
            }
        }

        $total = $tp + $fp + $fn + $tn;

        return [
            'tp' => $tp, 'fp' => $fp, 'fn' => $fn, 'tn' => $tn,
            'accuracy' => $total > 0 ? round(($tp + $tn) / $total * 100, 1) : null,
            'recall' => ($tp + $fn) > 0 ? round($tp / ($tp + $fn) * 100, 1) : null,
            'false_alarm_rate' => ($fp + $tn) > 0 ? round($fp / ($fp + $tn) * 100, 1) : null,
        ];
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $result = $this->buildContingency();

        return response()->streamDownload(function () use ($result) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['periode_mulai', 'periode_selesai', 'true_positive', 'false_positive', 'false_negative', 'true_negative', 'akurasi_persen', 'recall_persen', 'false_alarm_rate_persen']);
            fputcsv($out, [$this->startDate, $this->endDate, $result['tp'], $result['fp'], $result['fn'], $result['tn'], $result['accuracy'], $result['recall'], $result['false_alarm_rate']]);
            fclose($out);
        }, 'validasi-historis.csv');
    }

    public function render()
    {
        return view('livewire.admin.validation.index', [
            'result' => $this->buildContingency(),
        ])->layout('components.layouts.admin', ['title' => 'Validasi Historis']);
    }
}
