<?php

namespace Database\Seeders;

use App\Models\Supervisor;
use App\Models\User;
use App\Models\Ejecutivo;
use Illuminate\Database\Seeder;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        $ejecutivos = Ejecutivo::all();

        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create();
            Supervisor::create([
                'user_id' => $user->id,
                'ejecutivo_id' => $ejecutivos->random()->id,
                'nombre' => fake()->firstName(),
                'apellido_p' => fake()->lastName(),
                'apellido_m' => fake()->lastName(),
            ]);
            $user->assignRole('supervisor');
        }
    }
}
