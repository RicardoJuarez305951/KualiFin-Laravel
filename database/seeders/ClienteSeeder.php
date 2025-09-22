<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ClienteSeeder extends Seeder
{
    private const CLIENTES_TO_CREATE = 20;
    private const PROMOTORES_MIN = 5;
    private const SUPERVISORES_MIN = 5;
    private const EJECUTIVOS_MIN = 5;

    private const CARTERA_ESTADOS = [
        'activo',
        'moroso',
        'desembolsado',
        'regularizado',
        'inactivo',
    ];
    
    // activo
    // acvivo con deuda
    // inactivo con deuda
    // liquidado

    private const MONTO_OPCIONES = [
        3000,
        4000,
        5000,
        5500,
        6000,
        6500,
        7000,
        7500,
        8000,
        10000,
        12000,
        15000,
        20000,
    ];

    public function run(): void
    {
        $promotores = $this->ensurePromotores();

        if ($promotores->isEmpty()) {
            $this->command?->warn('No se generaron clientes porque faltan promotores.');
            return;
        }

        $faker = fake();

        for ($i = 0; $i < self::CLIENTES_TO_CREATE; $i++) {
            $promotor = $promotores->random();
            $carteraEstado = $faker->randomElement(self::CARTERA_ESTADOS);
            $tieneCreditoActivo = in_array($carteraEstado, ['activo', 'moroso', 'desembolsado'], true);

            Cliente::create([
                'promotor_id' => $promotor->id,
                'CURP' => $faker->unique()->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}'),
                'nombre' => $faker->firstName(),
                'apellido_p' => $faker->lastName(),
                'apellido_m' => $faker->lastName(),
                'fecha_nacimiento' => Carbon::instance($faker->dateTimeBetween('-65 years', '-18 years'))->toDateString(),
                'tiene_credito_activo' => $tieneCreditoActivo,
                'cartera_estado' => $carteraEstado,
                'monto_maximo' => $faker->randomElement(self::MONTO_OPCIONES),
                'creado_en' => Carbon::now(),
                'actualizado_en' => Carbon::now(),
                'activo' => $tieneCreditoActivo,
            ]);
        }

        $faker->unique(true);
    }

    private function ensurePromotores(): Collection
    {
        $existing = Promotor::with('supervisor')->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $supervisores = $this->ensureSupervisores();

        if ($supervisores->isEmpty()) {
            return collect();
        }

        $created = collect();
        $faker = fake();

        for ($i = 0; $i < self::PROMOTORES_MIN; $i++) {
            $user = User::factory()->create(['rol' => 'promotor']);
            $supervisor = $supervisores->random();

            $promotor = Promotor::create([
                'user_id' => $user->id,
                'supervisor_id' => $supervisor->id,
                'nombre' => $faker->firstName(),
                'apellido_p' => $faker->lastName(),
                'apellido_m' => $faker->lastName(),
                'venta_maxima' => $faker->randomElement(self::MONTO_OPCIONES),
                'colonia' => $faker->streetName(),
                'venta_proyectada_objetivo' => $faker->randomElement(self::MONTO_OPCIONES),
                'bono' => $faker->randomFloat(2, 200, 1500),
            ]);

            $this->assignRoleSafely($user, 'promotor');
            $created->push($promotor);
        }

        return $created;
    }

    private function ensureSupervisores(): Collection
    {
        $existing = Supervisor::all();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $ejecutivos = $this->ensureEjecutivos();

        if ($ejecutivos->isEmpty()) {
            return collect();
        }

        $created = collect();
        $faker = fake();

        for ($i = 0; $i < self::SUPERVISORES_MIN; $i++) {
            $user = User::factory()->create(['rol' => 'supervisor']);
            $ejecutivo = $ejecutivos->random();

            $supervisor = Supervisor::create([
                'user_id' => $user->id,
                'ejecutivo_id' => $ejecutivo->id,
                'nombre' => $faker->firstName(),
                'apellido_p' => $faker->lastName(),
                'apellido_m' => $faker->lastName(),
            ]);

            $this->assignRoleSafely($user, 'supervisor');
            $created->push($supervisor);
        }

        return $created;
    }

    private function ensureEjecutivos(): Collection
    {
        $existing = Ejecutivo::all();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $created = collect();
        $faker = fake();

        for ($i = 0; $i < self::EJECUTIVOS_MIN; $i++) {
            $user = User::factory()->create(['rol' => 'ejecutivo']);

            $ejecutivo = Ejecutivo::create([
                'user_id' => $user->id,
                'nombre' => $faker->firstName(),
                'apellido_p' => $faker->lastName(),
                'apellido_m' => $faker->lastName(),
            ]);

            $this->assignRoleSafely($user, 'ejecutivo');
            $created->push($ejecutivo);
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