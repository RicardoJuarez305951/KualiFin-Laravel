<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PagoProyectado;
use Carbon\Carbon;

class PagoProyectadoSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            PagoProyectado::create([
                'credito_id' => 1,
                'semana' => $i,
                'monto_proyectado' => 1000 * $i,
                'fecha_limite' => Carbon::now()->addWeeks($i)->toDateString(),
                'estado' => 'pendiente',
            ]);
        }
    }
}
