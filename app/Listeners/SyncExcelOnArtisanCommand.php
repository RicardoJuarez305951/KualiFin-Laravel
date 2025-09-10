<?php

namespace App\Listeners;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;

// Forza la descarga del Excel cada php artisan serve
class SyncExcelOnArtisanCommand
{
    public function handle(CommandStarting $event): void
    {
        $targets = ['serve']; 

        if (in_array($event->command, $targets, true)) {
            // Forzar siempre que arranca el server de dev
            Artisan::call('excel:sync', ['--force' => true]);
            // Opcional: loguear
            // \Log::info('[excel:sync] disparado por CommandStarting: '.$event->command);
        }
    }
}
