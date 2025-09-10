<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentoAval;

class DocumentoAvalSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            DocumentoAval::create([
                'aval_id' => $i,
                'tipo_doc' => 'doc' . $i,
                'url_s3' => 'https://example.com/doc' . $i,
                'nombre_arch' => 'documento' . $i . '.pdf',
            ]);
        }
    }
}
