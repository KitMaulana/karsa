<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduler KARSA (CLAUDE.md §17)
|--------------------------------------------------------------------------
|
| Shared hosting cPanel tidak punya queue worker permanen, jadi
| queue:work dijalankan tiap menit dengan --stop-when-empty.
| Cron cPanel: * * * * * php /home/USER/karsa/artisan schedule:run
*/
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('karsa:sync-hotspots')
    ->hourlyAt(5)
    ->withoutOverlapping()
    ->onFailure(fn () => logger()->error('Scheduler: karsa:sync-hotspots gagal.'));

Schedule::command('karsa:sync-weather')
    ->hourlyAt(10)
    ->withoutOverlapping()
    ->onFailure(fn () => logger()->error('Scheduler: karsa:sync-weather gagal.'));

Schedule::command('karsa:calculate-risk')
    ->hourlyAt(15)
    ->withoutOverlapping()
    ->onFailure(fn () => logger()->error('Scheduler: karsa:calculate-risk gagal.'));

Schedule::command('karsa:snapshot-daily')
    ->dailyAt('00:30');

Schedule::command('karsa:cleanup-media')
    ->weekly();
