<?php

namespace Database\Seeders;

use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class CreditoFiltroScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('es_MX');
        $horariosPago = [
            ['dia' => 'Lunes', 'hora' => '08:00:00'],
            ['dia' => 'Martes', 'hora' => '09:30:00'],
            ['dia' => 'Miércoles', 'hora' => '11:00:00'],
            ['dia' => 'Jueves', 'hora' => '13:30:00'],
            ['dia' => 'Viernes', 'hora' => '16:00:00'],
        ];

        for ($ejecutivoIndex = 1; $ejecutivoIndex <= 3; $ejecutivoIndex++) {
            $ejecutivoUser = User::factory()->create([
                'name' => sprintf('Ejecutivo %02d', $ejecutivoIndex),
                'email' => sprintf('ejecutivo.seed%02d@kualifin.com', $ejecutivoIndex),
                'password' => Hash::make('password'),
                'rol' => 'ejecutivo',
            ]);
            $ejecutivoUser->assignRole('ejecutivo');

            [$nombreE, $apellidoPE, $apellidoME] = LatinoNameGenerator::person();
            $ejecutivo = Ejecutivo::create([
                'user_id' => $ejecutivoUser->id,
                'nombre' => $nombreE,
                'apellido_p' => $apellidoPE,
                'apellido_m' => $apellidoME,
            ]);

            for ($supervisorIndex = 1; $supervisorIndex <= 5; $supervisorIndex++) {
                $supervisorUser = User::factory()->create([
                    'name' => sprintf('Supervisor %02d-%02d', $ejecutivoIndex, $supervisorIndex),
                    'email' => sprintf('supervisor.seed%02d%02d@kualifin.com', $ejecutivoIndex, $supervisorIndex),
                    'password' => Hash::make('password'),
                    'rol' => 'supervisor',
                ]);
                $supervisorUser->assignRole('supervisor');

                [$nombreS, $apellidoPS, $apellidoMS] = LatinoNameGenerator::person();
                $supervisor = Supervisor::create([
                    'user_id' => $supervisorUser->id,
                    'ejecutivo_id' => $ejecutivo->id,
                    'nombre' => $nombreS,
                    'apellido_p' => $apellidoPS,
                    'apellido_m' => $apellidoMS,
                ]);

                for ($promotorIndex = 1; $promotorIndex <= 7; $promotorIndex++) {
                    $promotorUser = User::factory()->create([
                        'name' => sprintf('Promotor %02d-%02d-%02d', $ejecutivoIndex, $supervisorIndex, $promotorIndex),
                        'email' => sprintf('promotor.seed%02d%02d%02d@kualifin.com', $ejecutivoIndex, $supervisorIndex, $promotorIndex),
                        'password' => Hash::make('password'),
                        'rol' => 'promotor',
                    ]);
                    $promotorUser->assignRole('promotor');

                    [$nombreP, $apellidoPP, $apellidoMP] = LatinoNameGenerator::person();
                    $horario = $faker->randomElement($horariosPago);

                    $promotor = Promotor::create([
                        'user_id' => $promotorUser->id,
                        'supervisor_id' => $supervisor->id,
                        'nombre' => $nombreP,
                        'apellido_p' => $apellidoPP,
                        'apellido_m' => $apellidoMP,
                        'venta_maxima' => $faker->numberBetween(8000, 25000),
                        'colonia' => $faker->streetName(),
                        'venta_proyectada_objetivo' => $faker->numberBetween(6000, 15000),
                        'bono' => $faker->randomFloat(2, 200, 1500),
                        'dia_de_pago' => $horario['dia'],
                        'hora_de_pago' => $horario['hora'],
                    ]);

                    $this->crearClientesParaPromotor($promotor, $faker);
                }
            }
        }
    }

    private function crearClientesParaPromotor(Promotor $promotor, \Faker\Generator $faker): void
    {
        $domicilios = [
            'A' => [
                'calle' => $faker->streetName(),
                'numero_ext' => (string) $faker->numberBetween(1, 200),
                'colonia' => $faker->citySuffix(),
                'municipio' => $faker->city(),
                'cp' => $faker->postcode(),
            ],
            'B' => [
                'calle' => $faker->streetName(),
                'numero_ext' => (string) $faker->numberBetween(201, 400),
                'colonia' => $faker->citySuffix(),
                'municipio' => $faker->city(),
                'cp' => $faker->postcode(),
            ],
            'C' => [
                'calle' => $faker->streetName(),
                'numero_ext' => (string) $faker->numberBetween(401, 600),
                'colonia' => $faker->citySuffix(),
                'municipio' => $faker->city(),
                'cp' => $faker->postcode(),
            ],
        ];

        $escenarios = [
            [
                'cartera_estado' => 'moroso',
                'tiene_credito_activo' => true,
                'estado_credito' => 'vencido',
                'periodicidad' => '14Semanas',
                'weeks_offset' => 20,
                'address_group' => 'A',
            ],
            [
                'cartera_estado' => 'activo',
                'tiene_credito_activo' => true,
                'estado_credito' => 'desembolsado',
                'periodicidad' => '15Semanas',
                'weeks_offset' => 4,
                'address_group' => 'A',
            ],
            [
                'cartera_estado' => 'inactivo',
                'tiene_credito_activo' => false,
                'estado_credito' => 'cancelado',
                'periodicidad' => 'Mes',
                'weeks_offset' => 8,
                'address_group' => 'B',
            ],
            [
                'cartera_estado' => 'regularizado',
                'tiene_credito_activo' => false,
                'estado_credito' => 'liquidado',
                'periodicidad' => '13Semanas',
                'weeks_offset' => 30,
                'address_group' => 'B',
            ],
            [
                'cartera_estado' => 'desembolsado',
                'tiene_credito_activo' => true,
                'estado_credito' => 'desembolsado',
                'periodicidad' => '14Semanas',
                'weeks_offset' => 10,
                'address_group' => 'C',
                'recredito_ready' => true,
            ],
        ];

        foreach ($escenarios as $escenario) {
            for ($i = 0; $i < 2; $i++) {
                $this->crearClienteConCredito($promotor, $faker, $escenario, $domicilios, $i);
            }
        }
    }

    private function crearClienteConCredito(Promotor $promotor, \Faker\Generator $faker, array $escenario, array $domicilios, int $indice): void
    {
        [$nombre, $apellidoP, $apellidoM] = LatinoNameGenerator::person();
        $curp = $this->generarCurpUnica($faker);
        $horarioPago = $faker->randomElement(['07:30', '08:00', '09:00', '10:15']);

        $cliente = Cliente::create([
            'promotor_id' => $promotor->id,
            'CURP' => $curp,
            'nombre' => $nombre,
            'apellido_p' => $apellidoP,
            'apellido_m' => $apellidoM,
            'fecha_nacimiento' => Carbon::now()->subYears($faker->numberBetween(25, 58))->toDateString(),
            'tiene_credito_activo' => $escenario['tiene_credito_activo'],
            'cartera_estado' => $escenario['cartera_estado'],
            'monto_maximo' => $faker->numberBetween(3000, 20000),
            'horario_de_pago' => $horarioPago,
            'activo' => $escenario['cartera_estado'] !== 'inactivo',
        ]);

        $fechaInicio = Carbon::now()->subWeeks($escenario['weeks_offset']);
        $fechaFinal = (clone $fechaInicio)->addWeeks(ceil($this->periodicidadEnSemanas($escenario['periodicidad']) ?? 12));

        $credito = Credito::create([
            'cliente_id' => $cliente->id,
            'monto_total' => $faker->numberBetween(3000, 15000),
            'estado' => $escenario['estado_credito'],
            'interes' => $faker->randomFloat(2, 1.5, 5.0),
            'periodicidad' => $escenario['periodicidad'],
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_final' => $fechaFinal->toDateString(),
        ]);

        $domicilio = $domicilios[$escenario['address_group']];
        if (!empty($escenario['recredito_ready'])) {
            // Para recrear escenarios con autorización especial, variamos algunos domicilios
            $domicilio['numero_ext'] = (string) ((int) $domicilio['numero_ext'] + $indice);
        }

        DatoContacto::create([
            'credito_id' => $credito->id,
            'calle' => $domicilio['calle'],
            'numero_ext' => $domicilio['numero_ext'],
            'numero_int' => null,
            'monto_mensual' => $faker->numberBetween(500, 2500),
            'colonia' => $domicilio['colonia'],
            'municipio' => $domicilio['municipio'],
            'estado' => $faker->state(),
            'cp' => $domicilio['cp'],
            'tiempo_en_residencia' => $faker->numberBetween(1, 10) . ' años',
            'tel_fijo' => null,
            'tel_cel' => $faker->numerify('55#######'),
            'tipo_de_vivienda' => $faker->randomElement(['Propia', 'Rentada', 'Familiar']),
        ]);

        Aval::create([
            'CURP' => $this->generarCurpUnica($faker),
            'credito_id' => $credito->id,
            'nombre' => $faker->firstName(),
            'apellido_p' => $faker->lastName(),
            'apellido_m' => $faker->lastName(),
            'fecha_nacimiento' => Carbon::now()->subYears($faker->numberBetween(28, 65))->toDateString(),
            'direccion' => $faker->streetAddress(),
            'telefono' => $faker->numerify('55#######'),
            'parentesco' => $faker->randomElement(['Padre', 'Madre', 'Hermano', 'Pareja', 'Amigo']),
        ]);
    }

    private function generarCurpUnica(\Faker\Generator $faker): string
    {
        return strtoupper($faker->unique()->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}'));
    }

    private function periodicidadEnSemanas(?string $periodicidad): ?int
    {
        if (!$periodicidad) {
            return null;
        }

        if (preg_match('/(\d+)/', $periodicidad, $coincidencias)) {
            return (int) $coincidencias[1];
        }

        return null;
    }
}
