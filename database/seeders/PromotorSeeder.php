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

        $horariosPago = [
            ['dia' => 'Lunes', 'hora' => '08:00'],
            ['dia' => 'Martes', 'hora' => '09:30'],
            ['dia' => 'Miércoles', 'hora' => '11:00'],
            ['dia' => 'Jueves', 'hora' => '13:30'],
            ['dia' => 'Viernes', 'hora' => '16:00'],
            ['dia' => 'Sábado', 'hora' => '12:00'],
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
            $horario = fake()->randomElement($horariosPago);

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
                'dia_de_pago' => $horario['dia'],
                'hora_de_pago' => $horario['hora'],
            ]);

            $user->assignRole('promotor');
        }

        $this->command->info('¡Promotores creados con éxito!');
        $this->command->warn('Contraseña (para todos): password');
    }
}
