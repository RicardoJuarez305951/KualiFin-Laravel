<?php

namespace Database\Seeders;

use App\Models\Credito;
use App\Models\Cliente;
use Illuminate\Database\Seeder;

class CreditoSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();

        for ($i = 0; $i < 20; $i++) {
            $start = fake()->dateTimeBetween('-1 year', 'now');
            $end = (clone $start)->modify('+6 months');
            Credito::create([
                'cliente_id' => $clientes->random()->id,
                'monto_total' => fake()->randomFloat(2, 1000, 5000),
                'estado' => fake()->randomElement(['activo', 'pagado', 'mora']),
                'interes' => fake()->randomFloat(2, 1, 10),
                'periodicidad' => fake()->randomElement(['semanal', 'quincenal', 'mensual']),
                'fecha_inicio' => $start,
                'fecha_final' => $end,
            ]);
        }
    }
}
