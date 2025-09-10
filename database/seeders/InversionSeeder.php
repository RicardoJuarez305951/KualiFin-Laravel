<?php

namespace Database\Seeders;

use App\Models\Inversion;
use App\Models\Promotor;
use Illuminate\Database\Seeder;

class InversionSeeder extends Seeder
{
    public function run(): void
    {
        $promotores = Promotor::all();

        for ($i = 0; $i < 20; $i++) {
            $start = fake()->dateTimeBetween('-1 year', 'now');
            $end = (clone $start)->modify('+1 month');
            Inversion::create([
                'promotor_id' => $promotores->random()->id,
                'monto_solicitado' => fake()->randomFloat(2, 1000, 10000),
                'monto_aprobado' => fake()->randomFloat(2, 1000, 10000),
                'fecha_solicitud' => $start,
                'fecha_aprobacion' => $end,
            ]);
        }
    }
}
