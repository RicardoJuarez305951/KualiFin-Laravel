<?php

namespace Database\Seeders;

use App\Models\IngresoAdicional;
use App\Models\Ocupacion;
use Illuminate\Database\Seeder;

class IngresoAdicionalSeeder extends Seeder
{
    public function run(): void
    {
        $ocupaciones = Ocupacion::all();

        for ($i = 0; $i < 20; $i++) {
            IngresoAdicional::create([
                'ocupacion_id' => $ocupaciones->random()->id,
                'concepto' => fake()->word(),
                'monto' => fake()->randomFloat(2, 100, 1000),
                'frecuencia' => fake()->randomElement(['mensual', 'quincenal', 'semanal']),
            ]);
        }
    }
}
