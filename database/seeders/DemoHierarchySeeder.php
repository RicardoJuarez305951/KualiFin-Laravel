<?php

namespace Database\Seeders;

use App\Models\Comision;
use App\Models\Ejecutivo;
use App\Models\Ejercicio;
use App\Models\Inversion;
use App\Models\Promotor;
use App\Models\Supervisor;
use Database\Seeders\Concerns\CreatesUsersWithRoles;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoHierarchySeeder extends Seeder
{
    use CreatesUsersWithRoles;

    public function run(): void
    {
        $faker = fake();
        $horarios = $this->horariosPago();

        foreach ($this->executiveBlueprint($faker, $horarios) as $executiveData) {
            $ejecutivoUser = $this->createUserWithRole($executiveData['user'], 'ejecutivo', $faker);

            $ejecutivo = Ejecutivo::create([
                'user_id' => $ejecutivoUser->id,
                'nombre' => $executiveData['person']['nombre'],
                'apellido_p' => $executiveData['person']['apellido_p'],
                'apellido_m' => $executiveData['person']['apellido_m'],
            ]);

            foreach ($executiveData['supervisors'] as $supervisorData) {
                $supervisorUser = $this->createUserWithRole($supervisorData['user'], 'supervisor', $faker);

                $supervisor = Supervisor::create([
                    'user_id' => $supervisorUser->id,
                    'ejecutivo_id' => $ejecutivo->id,
                    'nombre' => $supervisorData['person']['nombre'],
                    'apellido_p' => $supervisorData['person']['apellido_p'],
                    'apellido_m' => $supervisorData['person']['apellido_m'],
                ]);

                Ejercicio::create([
                    'supervisor_id' => $supervisor->id,
                    'ejecutivo_id' => $ejecutivo->id,
                    'fecha_inicio' => Carbon::now()->subWeeks(2),
                    'fecha_final' => Carbon::now()->addWeeks(6),
                    'venta_objetivo' => 25000,
                    'dinero_autorizado' => 18000,
                ]);

                foreach ($supervisorData['promotores'] as $promotorData) {
                    $promotorUser = $this->createUserWithRole($promotorData['user'], 'promotor', $faker);

                    $promotor = Promotor::create([
                        'user_id' => $promotorUser->id,
                        'supervisor_id' => $supervisor->id,
                        'nombre' => $promotorData['person']['nombre'],
                        'apellido_p' => $promotorData['person']['apellido_p'],
                        'apellido_m' => $promotorData['person']['apellido_m'],
                        'venta_maxima' => $promotorData['venta_maxima'],
                        'colonia' => $promotorData['colonia'],
                        'venta_proyectada_objetivo' => $promotorData['venta_proyectada_objetivo'],
                        'bono' => $promotorData['bono'],
                        'dia_de_pago' => $promotorData['horario']['dia'],
                        'hora_de_pago' => $promotorData['horario']['hora'],
                    ]);

                    $this->crearInversionesParaPromotor($promotor, $faker);
                    $this->crearComisionesParaPromotor($promotor, $faker);
                }
            }
        }

        $faker->unique(true);
    }

    private function horariosPago(): array
    {
        return [
            ['dia' => 'Lunes', 'hora' => '08:00'],
            ['dia' => 'Martes', 'hora' => '09:30'],
            ['dia' => 'Miercoles', 'hora' => '11:00'],
            ['dia' => 'Jueves', 'hora' => '13:30'],
            ['dia' => 'Viernes', 'hora' => '16:00'],
            ['dia' => 'Sabado', 'hora' => '12:00'],
        ];
    }

    private function executiveBlueprint(Generator $faker, array $horarios): array
    {
        $executives = [];

        for ($execIndex = 1; $execIndex <= 2; $execIndex++) {
            [$nombre, $apellidoP, $apellidoM] = LatinoNameGenerator::person();

            $executive = [
                'user' => [
                    'name' => sprintf('%s %s %s', $nombre, $apellidoP, $apellidoM),
                    'email' => $execIndex === 1 ? 'ejecutivo@example.com' : sprintf('ejecutivo%d@example.com', $execIndex),
                    'telefono' => sprintf('555%07d', 102000 + $execIndex),
                ],
                'person' => [
                    'nombre' => $nombre,
                    'apellido_p' => $apellidoP,
                    'apellido_m' => $apellidoM,
                ],
                'supervisors' => [],
            ];

            for ($supervisorIndex = 1; $supervisorIndex <= 2; $supervisorIndex++) {
                [$supNombre, $supApellidoP, $supApellidoM] = LatinoNameGenerator::person();

                $emailSupervisor = match (true) {
                    $execIndex === 1 && $supervisorIndex === 1 => 'supervisor@example.com',
                    $execIndex === 1 && $supervisorIndex === 2 => 'supervisor2@example.com',
                    default => sprintf('ejecutivo%d.supervisor%d@example.com', $execIndex, $supervisorIndex),
                };

                $supervisor = [
                    'user' => [
                        'name' => sprintf('%s %s %s', $supNombre, $supApellidoP, $supApellidoM),
                        'email' => $emailSupervisor,
                        'telefono' => sprintf('555%07d', 202000 + ($execIndex - 1) * 20 + $supervisorIndex),
                    ],
                    'person' => [
                        'nombre' => $supNombre,
                        'apellido_p' => $supApellidoP,
                        'apellido_m' => $supApellidoM,
                    ],
                    'promotores' => [],
                ];

                for ($promotorIndex = 1; $promotorIndex <= 2; $promotorIndex++) {
                    [$proNombre, $proApellidoP, $proApellidoM] = LatinoNameGenerator::person();

                    $emailPromotor = match (true) {
                        $execIndex === 1 && $supervisorIndex === 1 && $promotorIndex === 1 => 'promotor@example.com',
                        $execIndex === 1 && $supervisorIndex === 1 && $promotorIndex === 2 => 'promotor2@example.com',
                        default => sprintf(
                            'ejecutivo%d.supervisor%d.promotor%d@example.com',
                            $execIndex,
                            $supervisorIndex,
                            $promotorIndex
                        ),
                    };

                    $horarioIndex = (($execIndex - 1) * 4) + (($supervisorIndex - 1) * 2) + ($promotorIndex - 1);
                    $horario = $horarios[$horarioIndex % count($horarios)];

                    $supervisor['promotores'][] = [
                        'user' => [
                            'name' => sprintf('%s %s %s', $proNombre, $proApellidoP, $proApellidoM),
                            'email' => $emailPromotor,
                            'telefono' => sprintf('555%07d', 302000 + ($execIndex - 1) * 40 + ($supervisorIndex - 1) * 10 + $promotorIndex),
                        ],
                        'person' => [
                            'nombre' => $proNombre,
                            'apellido_p' => $proApellidoP,
                            'apellido_m' => $proApellidoM,
                        ],
                        'horario' => $horario,
                        'venta_maxima' => $faker->randomFloat(2, 8000, 15000),
                        'venta_proyectada_objetivo' => $faker->randomFloat(2, 4000, 9000),
                        'bono' => $faker->randomFloat(2, 350, 800),
                        'colonia' => $faker->streetName(),
                    ];
                }

                $executive['supervisors'][] = $supervisor;
            }

            $executives[] = $executive;
        }

        return $executives;
    }

    private function crearInversionesParaPromotor(Promotor $promotor, Generator $faker): void
    {
        for ($i = 0; $i < 2; $i++) {
            $fechaSolicitud = Carbon::now()->subWeeks($i + 1);
            $fechaAprobacion = (clone $fechaSolicitud)->addDays(7);

            Inversion::create([
                'promotor_id' => $promotor->id,
                'monto_solicitado' => $faker->randomFloat(2, 5000, 20000),
                'monto_aprobado' => $faker->randomFloat(2, 4000, 18000),
                'fecha_solicitud' => $fechaSolicitud,
                'fecha_aprobacion' => $fechaAprobacion,
            ]);
        }
    }

    private function crearComisionesParaPromotor(Promotor $promotor, Generator $faker): void
    {
        for ($i = 0; $i < 2; $i++) {
            Comision::create([
                'comisionable_type' => Promotor::class,
                'comisionable_id' => $promotor->id,
                'porcentaje' => $faker->randomFloat(2, 1, 10),
                'monto_base' => $faker->randomFloat(2, 500, 3000),
                'monto_pago' => $faker->randomFloat(2, 200, 1200),
                'fecha_pago' => Carbon::now()->subDays($i * 7)->toDateString(),
            ]);
        }
    }
}
