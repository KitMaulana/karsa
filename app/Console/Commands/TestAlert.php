<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\District;
use App\Services\Notify\AlertDispatcher;
use Illuminate\Console\Command;

/**
 * Simulasikan kenaikan level risiko untuk demo (CLAUDE.md §12).
 * Contoh: php artisan karsa:test-alert ciruas --to=tinggi
 */
class TestAlert extends Command
{
    protected $signature = 'karsa:test-alert {kecamatan : Slug kecamatan} {--from=sedang} {--to=tinggi}';

    protected $description = 'Simulasikan peringatan kenaikan level risiko untuk demo';

    public function handle(AlertDispatcher $dispatcher): int
    {
        $district = District::where('slug', $this->argument('kecamatan'))->first();

        if (! $district) {
            $this->error('Kecamatan tidak ditemukan: '.$this->argument('kecamatan'));

            return self::FAILURE;
        }

        $fromLevel = $this->option('from');
        $toLevel = $this->option('to');

        $alert = Alert::create([
            'district_id' => $district->id,
            'from_level' => $fromLevel,
            'to_level' => $toLevel,
            'score' => 70,
            'causes' => [['factor' => 's_hotspot', 'delta' => 25, 'label' => 'Peningkatan hotspot (simulasi demo)']],
            'is_manual' => true,
            'message' => "[DEMO] Risiko karhutla di {$district->name} naik dari ".ucfirst($fromLevel)." ke ".ucfirst(str_replace('_', ' ', $toLevel)).". Lihat langkah pencegahan 72 jam.",
            'sent_at' => now(),
        ]);

        $dispatcher->dispatch($alert);

        $this->info("Peringatan simulasi dibuat & dikirim untuk {$district->name}.");

        return self::SUCCESS;
    }
}
