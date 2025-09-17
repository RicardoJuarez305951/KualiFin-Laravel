<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Credito;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CreditoSeeder extends Seeder
{
    private const CREDITOS_TO_CREATE = 20;

    private const CREDIT_STATES = [
        'prospectado',
        'prospectado_recredito',
        'solicitado',
        'aprobado',
        'supervisado',
        'desembolsado',
        'liquidado',
        'vencido',
        'cancelado',
    ];

    private const PERIODICIDADES = [
        '14Semanas' => 14,
        '15Semanas' => 15,
        '22Semanas' => 22,
        'Mes' => null,
    ];

    private const MONTO_OPCIONES = [
        3000,
        4000,
        5000,
        5500,
        6000,
        6500,
        7000,
        7500,
        8000,
        10000,
        12000,
        15000,
        20000,
    ];

    public function run(): void
    {
        $clientes = Cliente::all();

        if ($clientes->isEmpty()) {
            return;
        }

        $faker = fake();

        for ($i = 0; $i < self::CREDITOS_TO_CREATE; $i++) {
            $cliente = $clientes->random();
            $start = Carbon::instance($faker->dateTimeBetween('-1 year', 'now'))->startOfDay();

            $periodicidad = $faker->randomElement(array_keys(self::PERIODICIDADES));
            $weeks = self::PERIODICIDADES[$periodicidad];
            $end = $weeks
                ? $start->copy()->addWeeks($weeks)
                : $start->copy()->addMonth();

            Credito::create([
                'cliente_id' => $cliente->id,
                'monto_total' => $faker->randomElement(self::MONTO_OPCIONES),
                'estado' => $faker->randomElement(self::CREDIT_STATES),
                'interes' => $faker->randomFloat(2, 1, 10),
                'periodicidad' => $periodicidad,
                'fecha_inicio' => $start,
                'fecha_final' => $end,
            ]);
        }
    }
}