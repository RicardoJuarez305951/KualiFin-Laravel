<?php

namespace Database\Seeders;

use App\Models\Contrato;
use Illuminate\Database\Seeder;

class ContratoSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Contrato::create([
                'credito_id' => $i,
                'tipo_contrato' => 'Tipo '.$i,
                'fecha_generacion' => now()->subDays($i),
                'url_s3' => 'https://example.com/contratos/contrato'.$i.'.pdf',
            ]);
        }
    }
}
