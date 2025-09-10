<?php

namespace Database\Seeders;

use App\Models\Comision;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComisionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        for ($i = 1; $i <= 20; $i++) {
            Comision::create([
                'comisionable_type' => User::class,
                'comisionable_id' => $user?->id ?? 1,
                'porcentaje' => 5 + $i,
                'monto_base' => 1000 * $i,
                'monto_pago' => (1000 * $i) * ((5 + $i) / 100),
                'fecha_pago' => now()->subDays($i)->toDateString(),
            ]);
        }
    }
}
