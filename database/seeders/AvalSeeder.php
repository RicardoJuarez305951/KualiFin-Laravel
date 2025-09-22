<?php

namespace Database\Seeders;

use App\Models\Aval;
use App\Models\Credito;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Illuminate\Database\Seeder;

class AvalSeeder extends Seeder
{
    public function run(): void
    {
        $creditos = Credito::all();

        for ($i = 0; $i < 20; $i++) {
            [$nombre, $apellidoPaterno, $apellidoMaterno] = LatinoNameGenerator::person();

            Aval::create([
                'CURP' => strtoupper(fake()->bothify('??????????????###')),
                'credito_id' => $creditos->random()->id,
                'nombre' => $nombre,
                'apellido_p' => $apellidoPaterno,
                'apellido_m' => $apellidoMaterno,
                'fecha_nacimiento' => fake()->date(),
                'direccion' => fake()->address(),
                'telefono' => fake()->phoneNumber(),
                'parentesco' => fake()->word(),
            ]);
        }
    }
}
