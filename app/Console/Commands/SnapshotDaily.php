<?php

namespace App\Console\Commands;

use App\Models\Hotspot;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Tugas harian (00.30): risk_scores sudah tersimpan tiap jam sehingga
 * berfungsi sebagai snapshot untuk Validasi Historis (CLAUDE.md §7.5).
 * Tugas ini membersihkan hotspot > 180 hari, mengarsipkan ringkasan
 * (jumlah per kecamatan per level confidence) ke log sebelum dihapus.
 */
class SnapshotDaily extends Command
{
    protected $signature = 'karsa:snapshot-daily';

    protected $description = 'Snapshot harian & pembersihan hotspot lama (>180 hari)';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subDays(180);

        $summary = Hotspot::where('detected_at', '<', $cutoff)
            ->select('district_id', 'confidence', DB::raw('count(*) as total'))
            ->groupBy('district_id', 'confidence')
            ->get();

        if ($summary->isNotEmpty()) {
            Log::info('SnapshotDaily: arsip ringkasan hotspot sebelum dihapus', [
                'cutoff' => $cutoff->toDateString(),
                'summary' => $summary->toArray(),
            ]);
        }

        $deleted = Hotspot::where('detected_at', '<', $cutoff)->delete();

        $this->info("Selesai. {$deleted} hotspot > 180 hari dihapus (ringkasan diarsipkan ke log).");

        return self::SUCCESS;
    }
}
