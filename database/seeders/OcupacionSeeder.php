<?php

namespace Database\Seeders;

use App\Models\Ocupacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OcupacionSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Ocupacion::create([
                'credito_id' => 1,
                'actividad' => 'Ocupacion ' . $i,
                'nombre_empresa' => 'Empresa ' . $i,
                'calle' => 'Calle ' . $i,
                'numero' => (string) $i,
                'colonia' => 'Colonia ' . $i,
                'municipio' => 'Municipio ' . $i,
                'telefono' => '5550000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'antiguedad' => $i . ' años',
                'monto_percibido' => 1000 * $i,
                'periodo_pago' => 'Mensual',
                'creado_en' => Carbon::now(),
            ]);
        }
    }
}
