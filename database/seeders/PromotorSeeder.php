<?php

namespace Database\Seeders;

use App\Models\Promotor;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PromotorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 20; $i++) {
            Promotor::create([
                'user_id' => 1,
                'supervisor_id' => 1,
                'nombre' => $faker->firstName,
                'apellido_p' => $faker->lastName,
                'apellido_m' => $faker->lastName,
                'venta_maxima' => $faker->randomFloat(2, 1000, 10000),
                'colonia' => $faker->city,
                'venta_proyectada_objetivo' => $faker->randomFloat(2, 1000, 10000),
                'bono' => $faker->randomFloat(2, 100, 1000),
            ]);
        }
    }
}
