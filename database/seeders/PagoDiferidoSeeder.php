<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PagoDiferido;

class PagoDiferidoSeeder extends Seeder
{
    public function run(): void
    {
        PagoDiferido::create([
            'pago_real_id' => 1,
            'monto_diferido' => 100.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 2,
            'monto_diferido' => 200.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 3,
            'monto_diferido' => 300.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 4,
            'monto_diferido' => 400.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 5,
            'monto_diferido' => 500.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 6,
            'monto_diferido' => 600.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 7,
            'monto_diferido' => 700.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 8,
            'monto_diferido' => 800.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 9,
            'monto_diferido' => 900.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 10,
            'monto_diferido' => 1000.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 11,
            'monto_diferido' => 1100.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 12,
            'monto_diferido' => 1200.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 13,
            'monto_diferido' => 1300.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 14,
            'monto_diferido' => 1400.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 15,
            'monto_diferido' => 1500.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 16,
            'monto_diferido' => 1600.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 17,
            'monto_diferido' => 1700.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 18,
            'monto_diferido' => 1800.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 19,
            'monto_diferido' => 1900.00,
        ]);
        PagoDiferido::create([
            'pago_real_id' => 20,
            'monto_diferido' => 2000.00,
        ]);
    }
}
