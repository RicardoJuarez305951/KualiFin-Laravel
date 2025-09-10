<?php

namespace Database\Seeders;

use App\Models\Ejecutivo;
use App\Models\User;
use Illuminate\Database\Seeder;

class EjecutivoSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create(['rol' => 'ejecutivo']);

            Ejecutivo::create([
                'user_id' => $user->id,
                'nombre' => fake()->firstName(),
                'apellido_p' => fake()->lastName(),
                'apellido_m' => fake()->lastName(),
            ]);

            $user->assignRole('ejecutivo');
        }
    }
}
