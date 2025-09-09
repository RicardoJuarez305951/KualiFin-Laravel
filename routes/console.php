<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Forzar la descarga a las 3:05 AM
// Schedule::command('excel:sync --force')
//     ->dailyAt('03:05')
//     ->withoutOverlapping();
