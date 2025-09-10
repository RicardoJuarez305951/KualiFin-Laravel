<?php

namespace Database\Seeders;

use App\Models\Promotor;
use App\Models\User;
use App\Models\Supervisor;
use Illuminate\Database\Seeder;

class PromotorSeeder extends Seeder
{
    public function run(): void
    {
        $supervisores = Supervisor::all();

        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create(['rol' => 'promotor']);
            Promotor::create([
                'user_id' => $user->id,
                'supervisor_id' => $supervisores->random()->id,
                'nombre' => fake()->firstName(),
                'apellido_p' => fake()->lastName(),
                'apellido_m' => fake()->lastName(),
                'venta_maxima' => fake()->randomFloat(2, 1000, 10000),
                'colonia' => fake()->streetName(),
                'venta_proyectada_objetivo' => fake()->randomFloat(2, 1000, 10000),
                'bono' => fake()->randomFloat(2, 100, 1000),
            ]);
            $user->assignRole('promotor');
        }
    }
}
