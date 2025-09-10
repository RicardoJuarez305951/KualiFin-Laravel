<?php

namespace Database\Seeders;

use App\Models\DocumentoCliente;
use Illuminate\Database\Seeder;

class DocumentoClienteSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            DocumentoCliente::create([
                'cliente_id' => 1,
                'credito_id' => 1,
                'tipo_doc' => 'doc-' . $i,
                'url_s3' => 'https://example.com/docs/doc-' . $i . '.pdf',
                'nombre_arch' => 'documento_' . $i . '.pdf',
            ]);
        }
    }
}
