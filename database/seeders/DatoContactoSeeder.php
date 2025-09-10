<?php

namespace Database\Seeders;

use App\Models\DatoContacto;
use App\Models\Credito;
use Illuminate\Database\Seeder;

class DatoContactoSeeder extends Seeder
{
    public function run(): void
    {
        $creditos = Credito::all();

        for ($i = 0; $i < 20; $i++) {
            DatoContacto::create([
                'credito_id' => $creditos->random()->id,
                'calle' => fake()->streetName(),
                'numero_ext' => fake()->buildingNumber(),
                'numero_int' => fake()->optional()->buildingNumber(),
                'monto_mensual' => fake()->numberBetween(1000, 5000),
                'colonia' => fake()->citySuffix(),
                'municipio' => fake()->city(),
                'estado' => fake()->state(),
                'cp' => fake()->postcode(),
                'tiempo_en_residencia' => fake()->numberBetween(1, 20).' años',
                'tel_fijo' => fake()->optional()->phoneNumber(),
                'tel_cel' => fake()->phoneNumber(),
                'tipo_de_vivienda' => fake()->word(),
            ]);
        }
    }
}
