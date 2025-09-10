<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Credito;
use App\Models\Cliente;
use Carbon\Carbon;

class CreditoSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();

        if ($clientes->isEmpty()) {
            return;
        }

        foreach (range(1, 20) as $i) {
            $cliente = $clientes->random();
            $fechaInicio = Carbon::now()->subMonths(rand(0, 12));
            Credito::create([
                'cliente_id' => $cliente->id,
                'monto_total' => rand(1000, 100000) / 100,
                'estado' => 'activo',
                'interes' => rand(5, 20),
                'periodicidad' => 'mensual',
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_final' => $fechaInicio->copy()->addMonths(rand(6, 24))->toDateString(),
            ]);
        }
    }
}
