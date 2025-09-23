<?php

namespace Database\Seeders;

use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Database\Seeders\Concerns\LatinoNameGenerator;
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

        $diasPagoOptions = [
            'lunes, miércoles',
            'martes, jueves',
            'viernes',
            'lunes a viernes',
            'martes a sábado',
        ];

        for ($i = 1; $i <= 20; $i++) {
            $email = "promotor{$i}@kualifin.com";

            // Generar nombre y apellidos primero
            [$nombre, $apellido_p, $apellido_m] = LatinoNameGenerator::person();

            // Crear usuario asociado
            $user = User::factory()->create([
                'name' => "{$nombre} {$apellido_p} {$apellido_m}",
                'email' => $email,
                'password' => Hash::make('password'),
                'rol' => 'promotor',
            ]);

            // Crear promotor asociado
            Promotor::create([
                'user_id' => $user->id,
                'supervisor_id' => $supervisores->random()->id,
                'nombre' => $nombre,
                'apellido_p' => $apellido_p,
                'apellido_m' => $apellido_m,
                'venta_maxima' => fake()->randomFloat(2, 1000, 10000),
                'colonia' => fake()->streetName(),
                'venta_proyectada_objetivo' => fake()->randomFloat(2, 1000, 10000),
                'bono' => fake()->randomFloat(2, 100, 1000),
                'dias_de_pago' => fake()->randomElement($diasPagoOptions),
            ]);

            $user->assignRole('promotor');
        }

        $this->command->info('¡Promotores creados con éxito!');
        $this->command->warn('Contraseña (para todos): password');
    }
}
