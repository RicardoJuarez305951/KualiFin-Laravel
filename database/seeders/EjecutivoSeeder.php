<?php

namespace Database\Seeders;

use App\Models\Ejecutivo;
use App\Models\User;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Illuminate\Database\Seeder;

class EjecutivoSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create(['rol' => 'ejecutivo']);

            [$nombre, $apellidoPaterno, $apellidoMaterno] = LatinoNameGenerator::person();

            Ejecutivo::create([
                'user_id' => $user->id,
                'nombre' => $nombre,
                'apellido_p' => $apellidoPaterno,
                'apellido_m' => $apellidoMaterno,
            ]);

            $user->assignRole('ejecutivo');
        }
    }
}
