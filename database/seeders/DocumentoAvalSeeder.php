<?php

namespace Database\Seeders;

use App\Models\DocumentoAval;
use App\Models\Aval;
use Illuminate\Database\Seeder;

class DocumentoAvalSeeder extends Seeder
{
    public function run(): void
    {
        $avales = Aval::all();

        for ($i = 0; $i < 20; $i++) {
            DocumentoAval::create([
                'aval_id' => $avales->random()->id,
                'tipo_doc' => fake()->word(),
                'url_s3' => fake()->url(),
                'nombre_arch' => fake()->word().'.pdf',
            ]);
        }
    }
}
