<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supervisor;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Supervisor::create([
                'user_id' => ($i % 5) + 1,
                'ejecutivo_id' => 1,
                'nombre' => 'Supervisor ' . $i,
                'apellido_p' => 'ApellidoP' . $i,
                'apellido_m' => 'ApellidoM' . $i,
            ]);
        }
    }
}
