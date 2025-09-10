<?php

namespace Database\Seeders;

use App\Models\PagoCompleto;
use App\Models\PagoReal;
use Illuminate\Database\Seeder;

class PagoCompletoSeeder extends Seeder
{
    public function run(): void
    {
        $pagos = PagoReal::all();

        for ($i = 0; $i < 20; $i++) {
            PagoCompleto::create([
                'pago_real_id' => $pagos->random()->id,
                'monto_completo' => fake()->randomFloat(2, 10, 100),
            ]);
        }
    }
}
