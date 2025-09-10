<?php

namespace Database\Seeders;

use App\Models\Comision;
use App\Models\Promotor;
use Illuminate\Database\Seeder;

class ComisionSeeder extends Seeder
{
    public function run(): void
    {
        $promotores = Promotor::all();

        for ($i = 0; $i < 20; $i++) {
            $promotor = $promotores->random();
            Comision::create([
                'comisionable_type' => Promotor::class,
                'comisionable_id' => $promotor->id,
                'porcentaje' => fake()->randomFloat(2, 1, 10),
                'monto_base' => fake()->randomFloat(2, 100, 1000),
                'monto_pago' => fake()->randomFloat(2, 100, 1000),
                'fecha_pago' => fake()->date(),
            ]);
        }
    }
}
