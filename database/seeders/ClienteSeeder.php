<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Cliente::create([
                'promotor_id' => 1,
                'CURP' => 'CURP' . str_pad($i, 14, '0', STR_PAD_LEFT),
                'nombre' => 'Cliente ' . $i,
                'apellido_p' => 'ApellidoP ' . $i,
                'apellido_m' => 'ApellidoM ' . $i,
                'fecha_nacimiento' => now()->subYears(30)->subDays($i),
                'tiene_credito_activo' => false,
                'estatus' => 'activo',
                'monto_maximo' => 10000,
                'activo' => true,
            ]);
        }
    }
}

