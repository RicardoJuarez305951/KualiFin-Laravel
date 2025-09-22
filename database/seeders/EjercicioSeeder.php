<?php

namespace Database\Seeders;

use App\Models\Ejercicio;
use App\Models\Ejecutivo;
use App\Models\Supervisor;
use App\Models\User;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EjercicioSeeder extends Seeder
{
    private const EJERCICIOS_TO_CREATE = 20;
    private const EJECUTIVOS_MIN = 5;
    private const SUPERVISORES_MIN = 5;

    public function run(): void
    {
        $ejecutivos = $this->ensureEjecutivos();
        $supervisores = $this->ensureSupervisores($ejecutivos);

        if ($supervisores->isEmpty() || $ejecutivos->isEmpty()) {
            $this->command?->warn('No se generaron ejercicios porque faltan supervisores o ejecutivos.');
            return;
        }

        for ($i = 0; $i < self::EJERCICIOS_TO_CREATE; $i++) {
            $supervisor = $supervisores->random();
            $start = Carbon::parse(fake()->dateTimeBetween('-1 year', 'now'))->startOfDay();
            $end = (clone $start)->addMonth();

            Ejercicio::create([
                'supervisor_id' => $supervisor->id,
                'ejecutivo_id' => $supervisor->ejecutivo_id ?? $ejecutivos->random()->id,
                'fecha_inicio' => $start,
                'fecha_final' => $end,
                'venta_objetivo' => fake()->randomFloat(2, 1000, 10000),
                'dinero_autorizado' => fake()->randomFloat(2, 1000, 10000),
            ]);
        }
    }

    private function ensureEjecutivos(): Collection
    {
        $existing = Ejecutivo::all();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $created = collect();

        for ($i = 0; $i < self::EJECUTIVOS_MIN; $i++) {
            $user = User::factory()->create(['rol' => 'ejecutivo']);

            [$nombre, $apellidoPaterno, $apellidoMaterno] = LatinoNameGenerator::person();

            $ejecutivo = Ejecutivo::create([
                'user_id' => $user->id,
                'nombre' => $nombre,
                'apellido_p' => $apellidoPaterno,
                'apellido_m' => $apellidoMaterno,
            ]);

            $this->assignRoleSafely($user, 'ejecutivo');
            $created->push($ejecutivo);
        }

        return $created;
    }

    private function ensureSupervisores(Collection $ejecutivos): Collection
    {
        $existing = Supervisor::all();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        if ($ejecutivos->isEmpty()) {
            return collect();
        }

        $created = collect();

        for ($i = 0; $i < self::SUPERVISORES_MIN; $i++) {
            $user = User::factory()->create(['rol' => 'supervisor']);
            $ejecutivo = $ejecutivos->random();

            [$nombre, $apellidoPaterno, $apellidoMaterno] = LatinoNameGenerator::person();

            $supervisor = Supervisor::create([
                'user_id' => $user->id,
                'ejecutivo_id' => $ejecutivo->id,
                'nombre' => $nombre,
                'apellido_p' => $apellidoPaterno,
                'apellido_m' => $apellidoMaterno,
            ]);

            $this->assignRoleSafely($user, 'supervisor');
            $created->push($supervisor);
        }

        return $created;
    }

    private function assignRoleSafely(User $user, string $role): void
    {
        if (!method_exists($user, 'assignRole')) {
            return;
        }

        try {
            $user->assignRole($role);
        } catch (\Throwable $exception) {
            static $warned = [];

            if (!isset($warned[$role])) {
                $this->command?->warn("No se pudo asignar el rol {$role}. Ejecuta RolePermissionSeeder si necesitas roles.");
                $warned[$role] = true;
            }
        }
    }
}
