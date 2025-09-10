<?php

namespace Database\Seeders;

use App\Models\PagoAnticipo;
use App\Models\PagoReal;
use Illuminate\Database\Seeder;

class PagoAnticipoSeeder extends Seeder
{
    public function run(): void
    {
        $pagos = PagoReal::all();

        for ($i = 0; $i < 20; $i++) {
            PagoAnticipo::create([
                'pago_real_id' => $pagos->random()->id,
                'monto_anticipo' => fake()->randomFloat(2, 10, 100),
            ]);
        }
    }
}
