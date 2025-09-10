<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aval;

class AvalSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Aval::create([
                'CURP' => 'CURP' . str_pad((string) $i, 14, '0', STR_PAD_LEFT),
                'credito_id' => 1,
                'nombre' => 'Nombre ' . $i,
                'apellido_p' => 'ApellidoP ' . $i,
                'apellido_m' => 'ApellidoM ' . $i,
                'fecha_nacimiento' => '1990-01-01',
                'direccion' => 'Direccion ' . $i,
                'telefono' => '555000000' . $i,
                'parentesco' => 'Amigo',
            ]);
        }
    }
}
