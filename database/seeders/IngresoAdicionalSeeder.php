<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IngresoAdicional;

class IngresoAdicionalSeeder extends Seeder
{
    public function run(): void
    {
        $ingresos = [
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 1', 'monto' => 100.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 2', 'monto' => 200.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 3', 'monto' => 300.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 4', 'monto' => 400.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 5', 'monto' => 500.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 6', 'monto' => 600.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 7', 'monto' => 700.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 8', 'monto' => 800.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 9', 'monto' => 900.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 10', 'monto' => 1000.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 11', 'monto' => 1100.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 12', 'monto' => 1200.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 13', 'monto' => 1300.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 14', 'monto' => 1400.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 15', 'monto' => 1500.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 16', 'monto' => 1600.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 17', 'monto' => 1700.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 18', 'monto' => 1800.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 19', 'monto' => 1900.00, 'frecuencia' => 'mensual'],
            ['ocupacion_id' => 1, 'concepto' => 'Concepto 20', 'monto' => 2000.00, 'frecuencia' => 'mensual'],
        ];

        foreach ($ingresos as $ingreso) {
            IngresoAdicional::create($ingreso);
        }
    }
}
