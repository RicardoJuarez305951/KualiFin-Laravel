<?php

namespace Database\Seeders;

use App\Models\Documento;
use App\Models\Credito;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\Ejecutivo;
use Illuminate\Database\Seeder;

class DocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $creditos = Credito::all();
        $promotores = Promotor::all();
        $supervisores = Supervisor::all();
        $ejecutivos = Ejecutivo::all();

        for ($i = 0; $i < 20; $i++) {
            Documento::create([
                'credito_id' => $creditos->random()->id,
                'promotor_id' => $promotores->random()->id,
                'supervisor_id' => $supervisores->random()->id,
                'ejecutivo_id' => $ejecutivos->random()->id,
                'tipo_documento_id' => 1,
                'fecha_generacion' => fake()->date(),
                'url_s3' => fake()->url(),
            ]);
        }
    }
}
