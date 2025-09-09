<?php

namespace App\Providers;

use App\Services\ExcelReaderService;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Events\MaintenanceModeDisabled;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prefetch de Vite
        \Illuminate\Support\Facades\Vite::prefetch(concurrency: 3);

        // Dispara la descarga cuando arranca `php artisan serve`
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if ($event->command === 'serve') {
                Cache::lock('excel-sync-on-serve', 60)->get(function () {
                    Log::channel('excel')->info('Detectado php artisan serve, iniciando descarga Excel...');
                    app(ExcelReaderService::class)->refreshIfStale(60);
                });
            }
        });

        // Dispara la descarga cuando sales de mantenimiento (`php artisan up`)
        Event::listen(MaintenanceModeDisabled::class, function () {
            Cache::lock('excel-sync-on-up', 60)->get(function () {
                Log::channel('excel')->info('Detectado php artisan up, iniciando descarga Excel...');
                app(ExcelReaderService::class)->refreshIfStale(60);
            });
        });
    }
}
