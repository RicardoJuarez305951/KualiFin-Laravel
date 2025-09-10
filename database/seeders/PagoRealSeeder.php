<?php

namespace Database\Seeders;

use App\Models\PagoReal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PagoRealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            PagoReal::create([
                'pago_proyectado_id' => $i,
                'tipo' => 'completo',
                'fecha_pago' => Carbon::now()->subDays($i)->toDateString(),
                'comentario' => 'Pago ' . $i,
            ]);
        }
    }
}
