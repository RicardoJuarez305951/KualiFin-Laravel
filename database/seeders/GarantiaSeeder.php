<?php

namespace Database\Seeders;

use App\Models\Garantia;
use Illuminate\Database\Seeder;

class GarantiaSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Garantia::create([
                'credito_id' => 1,
                'propietario' => 'Propietario ' . $i,
                'tipo' => 'Tipo ' . $i,
                'marca' => 'Marca ' . $i,
                'modelo' => 'Modelo ' . $i,
                'num_serie' => 'NUMSERIE' . $i,
                'antiguedad' => $i . ' años',
                'monto_garantizado' => 1000 + ($i * 100),
                'foto_url' => 'https://example.com/foto' . $i . '.jpg',
            ]);
        }
    }
}

