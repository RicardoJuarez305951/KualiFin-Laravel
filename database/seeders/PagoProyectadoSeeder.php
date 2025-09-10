<?php

namespace Database\Seeders;

use App\Models\PagoProyectado;
use App\Models\Credito;
use Illuminate\Database\Seeder;

class PagoProyectadoSeeder extends Seeder
{
    public function run(): void
    {
        $creditos = Credito::all();

        for ($i = 0; $i < 20; $i++) {
            PagoProyectado::create([
                'credito_id' => $creditos->random()->id,
                'semana' => fake()->numberBetween(1, 52),
                'monto_proyectado' => fake()->randomFloat(2, 50, 500),
                'fecha_limite' => fake()->date(),
                'estado' => fake()->randomElement(['pendiente', 'pagado']),
            ]);
        }
    }
}
