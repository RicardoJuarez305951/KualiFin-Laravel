<?php

namespace Database\Seeders;

use App\Models\PagoDiferido;
use App\Models\PagoReal;
use Illuminate\Database\Seeder;

class PagoDiferidoSeeder extends Seeder
{
    public function run(): void
    {
        $pagos = PagoReal::all();

        for ($i = 0; $i < 20; $i++) {
            PagoDiferido::create([
                'pago_real_id' => $pagos->random()->id,
                'monto_diferido' => fake()->randomFloat(2, 10, 100),
            ]);
        }
    }
}
