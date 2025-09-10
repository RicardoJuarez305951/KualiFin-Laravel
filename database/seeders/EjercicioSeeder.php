<?php

namespace Database\Seeders;

use App\Models\Ejercicio;
use App\Models\Supervisor;
use App\Models\Ejecutivo;
use Illuminate\Database\Seeder;

class EjercicioSeeder extends Seeder
{
    public function run(): void
    {
        $supervisores = Supervisor::all();
        $ejecutivos = Ejecutivo::all();

        for ($i = 0; $i < 20; $i++) {
            $start = fake()->dateTimeBetween('-1 year', 'now');
            $end = (clone $start)->modify('+1 month');
            Ejercicio::create([
                'supervisor_id' => $supervisores->random()->id,
                'ejecutivo_id' => $ejecutivos->random()->id,
                'fecha_inicio' => $start,
                'fecha_final' => $end,
                'venta_objetivo' => fake()->randomFloat(2, 1000, 10000),
                'dinero_autorizado' => fake()->randomFloat(2, 1000, 10000),
            ]);
        }
    }
}
