<?php

namespace App\Providers;

use App\Services\ExcelReaderService;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Events\MaintenanceModeDisabled;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;

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
        View::composer('mobile.index', function ($view) {
            $mensajes = Config::get('mensajes_positivos.mensajes', []);
            if (count($mensajes) > 0) {
                $mensajeDelDia = $mensajes[array_rand($mensajes)];
            } else {
                $mensajeDelDia = "Cada día es una nueva oportunidad para crecer.";
            }
            $view->with('mensajeDelDia', $mensajeDelDia);
        });

        // Prefetch de Vite
        \Illuminate\Support\Facades\Vite::prefetch(concurrency: 3);

        // Dispara la descarga cuando arranca `php artisan serve`
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if ($event->command === 'serve') {
                Cache::lock('excel-sync-on-serve', 60)->get(function () {
                    Log::channel('excel')->info('Detectado php artisan serve, iniciando descarga Excel...');
                    // ExcelReaderService usa un Excel historico independiente de MySQL; aqui solo se sincroniza su version local.
                    app(ExcelReaderService::class)->refreshIfStale(60);
                });
            }
        });

        // Dispara la descarga cuando sales de mantenimiento (`php artisan up`)
        Event::listen(MaintenanceModeDisabled::class, function () {
            Cache::lock('excel-sync-on-up', 60)->get(function () {
                Log::channel('excel')->info('Detectado php artisan up, iniciando descarga Excel...');
                // Recordatorio: la sincronizacion solo lee el Excel historico, sin relacion con migraciones o seeders.
                app(ExcelReaderService::class)->refreshIfStale(60);
            });
        });
    }
}
