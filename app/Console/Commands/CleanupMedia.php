<?php

namespace App\Console\Commands;

use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Tugas mingguan: hapus berkas media laporan yang DITOLAK lebih dari 30
 * hari (CLAUDE.md §17). Record laporan tetap disimpan untuk audit, hanya
 * berkas fisik & path pada kolom media yang dibersihkan.
 */
class CleanupMedia extends Command
{
    protected $signature = 'karsa:cleanup-media';

    protected $description = 'Hapus berkas media laporan yang ditolak lebih dari 30 hari';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subDays(30);

        $reports = Report::where('status', ReportStatus::Ditolak->value)
            ->where('updated_at', '<', $cutoff)
            ->whereNotNull('media')
            ->get();

        $count = 0;

        foreach ($reports as $report) {
            foreach (($report->media ?? []) as $entry) {
                foreach (['private_path', 'public_path'] as $key) {
                    if (! empty($entry[$key]) && Storage::exists($entry[$key])) {
                        Storage::delete($entry[$key]);
                    }
                }
            }

            $report->update(['media' => null]);
            $count++;
        }

        $this->info("Selesai. Media pada {$count} laporan ditolak dibersihkan.");

        return self::SUCCESS;
    }
}
