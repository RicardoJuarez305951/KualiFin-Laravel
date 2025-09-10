<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Promotor;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $promotores = Promotor::all();

        for ($i = 0; $i < 20; $i++) {
            Cliente::create([
                'promotor_id' => $promotores->random()->id,
                'CURP' => strtoupper(fake()->bothify('??????????????###')),
                'nombre' => fake()->firstName(),
                'apellido_p' => fake()->lastName(),
                'apellido_m' => fake()->lastName(),
                'fecha_nacimiento' => fake()->date(),
                'tiene_credito_activo' => fake()->boolean(),
                'estatus' => fake()->randomElement(['activo', 'inactivo']),
                'monto_maximo' => fake()->randomFloat(2, 1000, 10000),
                'activo' => fake()->boolean(),
            ]);
        }
    }
}
