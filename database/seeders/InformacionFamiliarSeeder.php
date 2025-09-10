<?php

namespace Database\Seeders;

use App\Models\InformacionFamiliar;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class InformacionFamiliarSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 20; $i++) {
            InformacionFamiliar::create([
                'credito_id' => $i,
                'nombre_conyuge' => $faker->name,
                'celular_conyuge' => $faker->phoneNumber,
                'actividad_conyuge' => $faker->jobTitle,
                'ingresos_semanales_conyuge' => $faker->randomFloat(2, 1000, 5000),
                'domicilio_trabajo_conyuge' => $faker->address,
                'personas_en_domicilio' => $faker->numberBetween(1, 10),
                'dependientes_economicos' => $faker->numberBetween(0, 5),
                'conyuge_vive_con_cliente' => $faker->boolean,
                'creado_en' => now(),
            ]);
        }
    }
}
