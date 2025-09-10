<?php

namespace Database\Seeders;

use App\Models\PagoReal;
use App\Models\PagoProyectado;
use Illuminate\Database\Seeder;

class PagoRealSeeder extends Seeder
{
    public function run(): void
    {
        $proyectados = PagoProyectado::all();

        for ($i = 0; $i < 20; $i++) {
            PagoReal::create([
                'pago_proyectado_id' => $proyectados->random()->id,
                'tipo' => fake()->randomElement(['efectivo', 'transferencia']),
                'fecha_pago' => fake()->date(),
                'comentario' => fake()->sentence(),
            ]);
        }
    }
}
