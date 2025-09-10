<?php

namespace Database\Seeders;

use App\Models\Contrato;
use App\Models\Credito;
use Illuminate\Database\Seeder;

class ContratoSeeder extends Seeder
{
    public function run(): void
    {
        $creditos = Credito::all();

        for ($i = 0; $i < 20; $i++) {
            Contrato::create([
                'credito_id' => $creditos->random()->id,
                'tipo_contrato' => fake()->word(),
                'fecha_generacion' => fake()->date(),
                'url_s3' => fake()->url(),
            ]);
        }
    }
}
