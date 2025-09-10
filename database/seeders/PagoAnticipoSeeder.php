<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PagoAnticipo;

class PagoAnticipoSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            PagoAnticipo::create([
                'pago_real_id' => $i,
                'monto_anticipo' => 1000 + ($i * 50),
            ]);
        }
    }
}
