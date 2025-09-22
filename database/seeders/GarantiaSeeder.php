<?php

namespace Database\Seeders;

use App\Models\Credito;
use App\Models\Garantia;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Illuminate\Database\Seeder;

class GarantiaSeeder extends Seeder
{
    public function run(): void
    {
        $creditos = Credito::all();

        for ($i = 0; $i < 20; $i++) {
            Garantia::create([
                'credito_id' => $creditos->random()->id,
                'propietario' => LatinoNameGenerator::fullName(),
                'tipo' => fake()->word(),
                'marca' => fake()->company(),
                'modelo' => fake()->word(),
                'num_serie' => fake()->bothify('SER###??'),
                'antiguedad' => fake()->numberBetween(1, 10).' años',
                'monto_garantizado' => fake()->randomFloat(2, 1000, 10000),
                'foto_url' => fake()->url(),
            ]);
        }
    }
}
