<?php

namespace Database\Seeders;

use App\Models\Promotor;
use App\Models\User;
use App\Models\Supervisor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PromotorSeeder extends Seeder
{
    public function run(): void
    {
        $supervisores = Supervisor::all();

        if ($supervisores->isEmpty()) {
            $this->command->error('No se encontraron supervisores. Por favor, ejecuta el seeder de supervisores primero.');
            return;
        }

        $this->command->info('Creando 20 promotores con logins de prueba...');

        for ($i = 1; $i <= 20; $i++) {
            $email = "promotor{$i}@kualifin.com";

            $user = User::factory()->create([
                'name' => "{$nombre} {$apellido}",
                'email' => $email,
                'password' => Hash::make('password'),
                'rol' => 'promotor',
            ]);

            Promotor::create([
                'user_id' => $user->id,
                'supervisor_id' => $supervisores->random()->id,
                'nombre' => fake()->firstname(),
                'apellido_p' => fake()->lastname(),
                'apellido_m' => fake()->lastName(),
                'venta_maxima' => fake()->randomFloat(2, 1000, 10000),
                'colonia' => fake()->streetName(),
                'venta_proyectada_objetivo' => fake()->randomFloat(2, 1000, 10000),
                'bono' => fake()->randomFloat(2, 100, 1000),
            ]);
            $user->assignRole('promotor');
        }

        $this->command->info('¡Promotores creados con éxito!');
        $this->command->warn('Contraseña (para todos): password');
    }
}
