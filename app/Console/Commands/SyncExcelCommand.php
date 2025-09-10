<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SyncExcelCommand extends Command
{
    // Nombre del comando que vas a correr: php artisan excel:sync
    protected $signature = 'excel:sync {--force : Fuerza la descarga aunque exista un archivo local}';

    protected $description = 'Descarga el Excel remoto (EXCEL_REMOTE_URL) y lo guarda en EXCEL_LOCAL_PATH.';

    public function handle(): int
    {
        $url   = config('excel.remote_url');   // viene de .env EXCEL_REMOTE_URL
        $local = config('excel.local_path');   // viene de .env EXCEL_LOCAL_PATH

        if (! $url) {
            $this->error('EXCEL_REMOTE_URL no está configurado en .env');
            return self::FAILURE;
        }
        if (! $local) {
            $this->error('EXCEL_LOCAL_PATH no está configurado en .env');
            return self::FAILURE;
        }

        // Asegurar que el directorio exista
        $relative = str_replace(storage_path('app/'), '', $local);
        $dir = dirname(storage_path('app/'.$relative));
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        // Evitar descarga si ya existe y no se pasó --force
        if (Storage::exists($relative) && ! $this->option('force')) {
            $this->info('Archivo local ya existe. Usa --force para re-descargar.');
            $this->line('Ruta: '.$relative);
            return self::SUCCESS;
        }

        $this->info('Descargando Excel');

        try {
            // Descarga (GET simple). Si tu servidor requiere verificación de SSL más laxa, ajusta ->withOptions(['verify'=>false])
            $res = Http::timeout(120)->get($url);
        } catch (\Throwable $e) {
            $this->error('Error de red: '.$e->getMessage());
            return self::FAILURE;
        }

        if (! $res->ok()) {
            $this->error("Fallo descarga: HTTP {$res->status()}");
            return self::FAILURE;
        }

        // Guardar en storage/app/... (NO público)
        Storage::put($relative, $res->body());

        // Info de tamaño
        $bytes = strlen($res->body());
        $mb = round($bytes / 1024 / 1024, 2);

        $this->info("Excel sincronizado en local: {$relative} ({$mb} MB)");
        return self::SUCCESS;
    }
}
