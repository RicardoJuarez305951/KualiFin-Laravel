<?php

namespace Database\Seeders;

use App\Models\Ejercicio;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EjercicioSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $inicio = Carbon::now()->subMonths($i);
            $final = (clone $inicio)->addMonths(3);

            Ejercicio::create([
                'supervisor_id' => 1,
                'ejecutivo_id' => 1,
                'fecha_inicio' => $inicio,
                'fecha_final' => $final,
                'venta_objetivo' => 10000 * $i,
                'dinero_autorizado' => 8000 * $i,
            ]);
        }
    }
}
