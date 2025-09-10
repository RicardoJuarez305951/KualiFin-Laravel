<?php

namespace Database\Seeders;

use App\Models\InformacionFamiliar;
use App\Models\Credito;
use Illuminate\Database\Seeder;

class InformacionFamiliarSeeder extends Seeder
{
    public function run(): void
    {
        $creditos = Credito::all();

        for ($i = 0; $i < 20; $i++) {
            InformacionFamiliar::create([
                'credito_id' => $creditos->random()->id,
                'nombre_conyuge' => fake()->name(),
                'celular_conyuge' => fake()->phoneNumber(),
                'actividad_conyuge' => fake()->jobTitle(),
                'ingresos_semanales_conyuge' => fake()->randomFloat(2, 100, 1000),
                'domicilio_trabajo_conyuge' => fake()->address(),
                'personas_en_domicilio' => fake()->numberBetween(1, 10),
                'dependientes_economicos' => fake()->numberBetween(0, 5),
                'conyuge_vive_con_cliente' => fake()->boolean(),
            ]);
        }
    }
}
