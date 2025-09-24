<?php

namespace Database\Seeders;

use App\Models\Ejecutivo;
use App\Models\Supervisor;
use App\Models\User;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Illuminate\Database\Seeder;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        $ejecutivos = Ejecutivo::all();

        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create(['rol' => 'supervisor']);
            [$nombre, $apellidoPaterno, $apellidoMaterno] = LatinoNameGenerator::person();

            Supervisor::create([
                'user_id' => $user->id,
                'ejecutivo_id' => $ejecutivos->random()->id,
                'nombre' => $nombre,
                'apellido_p' => $apellidoPaterno,
                'apellido_m' => $apellidoMaterno,
            ]);
            $user->assignRole('supervisor');
        }
    }
}
