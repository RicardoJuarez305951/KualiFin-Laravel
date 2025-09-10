<?php

namespace Database\Seeders;

use App\Models\Aval;
use App\Models\Credito;
use Illuminate\Database\Seeder;

class AvalSeeder extends Seeder
{
    public function run(): void
    {
        $creditos = Credito::all();

        for ($i = 0; $i < 20; $i++) {
            Aval::create([
                'CURP' => strtoupper(fake()->bothify('??????????????###')),
                'credito_id' => $creditos->random()->id,
                'nombre' => fake()->firstName(),
                'apellido_p' => fake()->lastName(),
                'apellido_m' => fake()->lastName(),
                'fecha_nacimiento' => fake()->date(),
                'direccion' => fake()->address(),
                'telefono' => fake()->phoneNumber(),
                'parentesco' => fake()->word(),
            ]);
        }
    }
}
