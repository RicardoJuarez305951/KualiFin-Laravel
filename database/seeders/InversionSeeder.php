<?php

namespace Database\Seeders;

use App\Models\Inversion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class InversionSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Inversion::create([
                'promotor_id' => 1,
                'monto_solicitado' => 1000 * $i,
                'monto_aprobado' => 900 * $i,
                'fecha_solicitud' => Carbon::now()->subDays($i + 1),
                'fecha_aprobacion' => Carbon::now()->subDays($i),
            ]);
        }
    }
}
