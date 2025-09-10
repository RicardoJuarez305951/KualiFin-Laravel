<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PagoCompleto;

class PagoCompletoSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            PagoCompleto::create([
                'pago_real_id' => $i,
                'monto_completo' => 1000 * $i,
            ]);
        }
    }
}
