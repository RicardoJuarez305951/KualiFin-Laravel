<?php

namespace Database\Seeders;

use App\Models\Documento;
use Illuminate\Database\Seeder;

class DocumentoSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Documento::create([
                'credito_id' => 1,
                'promotor_id' => 1,
                'supervisor_id' => 1,
                'ejecutivo_id' => 1,
                'tipo_documento_id' => 1,
                'fecha_generacion' => now()->subDays($i),
                'url_s3' => "documents/documento_{$i}.pdf",
            ]);
        }
    }
}
