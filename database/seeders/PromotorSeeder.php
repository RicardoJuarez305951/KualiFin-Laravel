<?php

namespace Database\Seeders;

use App\Models\Ejecutivo;
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
        $this->ensurePromotorPruebaFiltros();

        $supervisores = Supervisor::all();

        if ($supervisores->isEmpty()) {
            $this->command->error('No se encontraron supervisores. Por favor, ejecuta el seeder de supervisores primero.');
            return;
        }

        $this->ensurePromotorPruebaFiltros($supervisores);

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

    private function ensurePromotorPruebaFiltros(): void

    {
        $emailObjetivo = 'PromotorPruebaFiltros@example.com';

        $promotorExistente = Promotor::whereHas('user', static function ($query) use ($emailObjetivo) {
            $query->where('email', $emailObjetivo);
        })->first();

        $supervisor = $this->ensureSupervisorExample();

        $user = $this->ensureUserWithRole(
            $emailObjetivo,
            'Paola Promotora',
            'promotor',
            '5553000003'
        );

        Promotor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'supervisor_id' => $supervisor->id,
                'nombre' => 'Paola',
                'apellido_p' => 'Promotora',
                'apellido_m' => 'Filtros',
                'venta_maxima' => 18000,
                'colonia' => 'Centro Histórico',
                'venta_proyectada_objetivo' => 11000,
                'bono' => 700,
                'dia_de_pago' => 'Lunes',
                'hora_de_pago' => '09:00',
            ]
        );

        if (! $promotorExistente && $this->command) {
            $this->command->info('PromotorPruebaFiltros@example.com creado bajo supervisor@example.com.');
        }
    }

    private function ensureSupervisorExample(): Supervisor
    {
        $supervisor = Supervisor::whereHas('user', static function ($query) {
            $query->where('email', 'supervisor@example.com');
        })->first();

        if ($supervisor) {
            $this->ensureUserHasRole($supervisor->user, 'supervisor');

            return $supervisor;
        }

        $ejecutivo = $this->ensureEjecutivoExample();

        $user = $this->ensureUserWithRole(
            'supervisor@example.com',
            'Samuel Supervisor',
            'supervisor',
            '5553000002'
        );

        return Supervisor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'ejecutivo_id' => $ejecutivo->id,
                'nombre' => 'Samuel',
                'apellido_p' => 'Supervisor',
                'apellido_m' => 'Principal',
            ]
        );
    }

    private function ensureEjecutivoExample(): Ejecutivo
    {
        $ejecutivo = Ejecutivo::whereHas('user', static function ($query) {
            $query->where('email', 'ejecutivo@example.com');
        })->first();

        if ($ejecutivo) {
            $this->ensureUserHasRole($ejecutivo->user, 'ejecutivo');

            return $ejecutivo;
        }

        $user = $this->ensureUserWithRole(
            'ejecutivo@example.com',
            'Eva Directora',
            'ejecutivo',
            '5553000001'
        );

        return Ejecutivo::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nombre' => 'Eva',
                'apellido_p' => 'Directora',
                'apellido_m' => 'Central',
            ]
        );
    }

    private function ensureUserWithRole(string $email, string $name, string $role, string $phone): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'telefono' => $phone,
                'password' => Hash::make('password'),
                'rol' => $role,
            ]
        );

        $this->ensureUserHasRole($user, $role);

        return $user;
    }

    private function ensureUserHasRole(User $user, string $role): void
    {
        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

    }
}
