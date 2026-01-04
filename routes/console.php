<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//operazioni schedulate 
Schedule::command('app:reset-license-table')
    //->everyMinute()
    ->dailyAt(config('app_settings.reset_time',"23:00"))        // Frequenza ogni giorno alle 23
    ->withoutOverlapping() // Opzionale: evita che il comando riparta se il precedente è ancora in esecuzione
    ->appendOutputTo(storage_path('logs/reset_license_table.log'));
