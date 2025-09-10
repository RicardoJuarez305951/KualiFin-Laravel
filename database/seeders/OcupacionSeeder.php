<?php

namespace Database\Seeders;

use App\Models\Ocupacion;
use App\Models\Credito;
use Illuminate\Database\Seeder;

class OcupacionSeeder extends Seeder
{
    public function run(): void
    {
        $creditos = Credito::all();

        for ($i = 0; $i < 20; $i++) {
            Ocupacion::create([
                'credito_id' => $creditos->random()->id,
                'actividad' => fake()->jobTitle(),
                'nombre_empresa' => fake()->company(),
                'calle' => fake()->streetName(),
                'numero' => fake()->buildingNumber(),
                'colonia' => fake()->citySuffix(),
                'municipio' => fake()->city(),
                'telefono' => fake()->phoneNumber(),
                'antiguedad' => fake()->numberBetween(1, 30).' años',
                'monto_percibido' => fake()->randomFloat(2, 1000, 10000),
                'periodo_pago' => fake()->randomElement(['semanal', 'mensual', 'quincenal']),
            ]);
        }
    }
}
