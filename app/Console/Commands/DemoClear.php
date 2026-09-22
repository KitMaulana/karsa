<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Hotspot;
use App\Models\Report;
use App\Models\RiskScore;
use App\Models\User;
use App\Models\WeatherObservation;
use Illuminate\Console\Command;

class DemoClear extends Command
{
    protected $signature = 'karsa:demo-clear';

    protected $description = 'Hapus semua data demo (is_demo=true)';

    public function handle(): int
    {
        $counts = [
            'hotspots' => Hotspot::where('is_demo', true)->delete(),
            'weather_observations' => WeatherObservation::where('is_demo', true)->delete(),
            'risk_scores' => RiskScore::where('is_demo', true)->delete(),
            'reports' => Report::where('is_demo', true)->delete(),
            'alerts' => Alert::where('is_demo', true)->delete(),
        ];

        User::where('email', 'demo@karsa.local')->delete();

        foreach ($counts as $table => $count) {
            $this->info("{$table}: {$count} baris dihapus.");
        }

        return self::SUCCESS;
    }
}
