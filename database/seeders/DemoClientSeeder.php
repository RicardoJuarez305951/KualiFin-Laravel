<?php

namespace Database\Seeders;

use App\Enums\ClienteEstado;
use App\Enums\CreditoEstado;
use App\Enums\PeriodicidadCreditos;
use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\Documento;
use App\Models\DocumentoAval;
use App\Models\DocumentoCliente;
use App\Models\Garantia;
use App\Models\IngresoAdicional;
use App\Models\InformacionFamiliar;
use App\Models\Ocupacion;
use App\Models\PagoAnticipo;
use App\Models\PagoCompleto;
use App\Models\PagoDiferido;
use App\Models\PagoProyectado;
use App\Models\PagoReal;
use App\Models\Promotor;
use App\Models\TipoDocumento;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoClientSeeder extends Seeder
{
    public function run(): void
    {
        $promotores = Promotor::with(['supervisor.ejecutivo', 'user'])->get();

        if ($promotores->isEmpty()) {
            return;
        }

        $faker = fake();
        $tipoDocumento = TipoDocumento::firstOrCreate(['nombre' => 'default']);
        $ocupaciones = [];
        $promotorStats = [];

        foreach ($promotores as $promotor) {
            $plan = $this->promotorPlan($promotor);
            $creditosPromotor = [];
            $promotorStats[$promotor->id] = [
                'totalCredits' => 0,
                'failureCredits' => 0,
                'failureAmount' => 0.0,
                'totalAmount' => 0.0,
                'email' => optional($promotor->user)->email ?? 'sin-correo',
            ];

            foreach ($plan['states'] as $index => $creditState) {
                $scenario = $this->buildScenarioForState(
                    $creditState,
                    $index,
                    $plan['override_amounts'][$index] ?? null,
                    $faker
                );

                $cliente = $this->createClienteParaPromotor($promotor, $scenario, $index, $faker);
                $credito = $this->createCreditoConRelaciones($cliente, $scenario, $faker, $ocupaciones);
                $creditosPromotor[] = $credito;

                $promotorStats[$promotor->id]['totalCredits']++;
                $promotorStats[$promotor->id]['totalAmount'] += (float) $scenario['monto_total'];

                if (in_array($scenario['credito_estado'], $this->failureStates(), true)) {
                    $promotorStats[$promotor->id]['failureCredits']++;
                    $promotorStats[$promotor->id]['failureAmount'] += (float) $scenario['monto_total'];
                }
            }

            $faker->unique(true);

            if (!empty($creditosPromotor) && $promotor->supervisor && $promotor->supervisor->ejecutivo) {
                Documento::create([
                    'credito_id' => $creditosPromotor[0]->id,
                    'promotor_id' => $promotor->id,
                    'supervisor_id' => $promotor->supervisor->id,
                    'ejecutivo_id' => $promotor->supervisor->ejecutivo->id,
                    'tipo_documento_id' => $tipoDocumento->id,
                    'fecha_generacion' => Carbon::now()->subDays(2),
                    'url_s3' => $faker->url(),
                ]);
            }
        }

        foreach ($ocupaciones as $ocupacion) {
            IngresoAdicional::create([
                'ocupacion_id' => $ocupacion->id,
                'concepto' => 'Ventas adicionales',
                'monto' => $faker->randomFloat(2, 250, 950),
                'frecuencia' => $faker->randomElement(['semanal', 'quincenal', 'mensual']),
            ]);
        }

        $faker->unique(true);

        $this->printPromotorFailureSummary($promotorStats);
    }

    private function createClienteParaPromotor(Promotor $promotor, array $scenario, int $scenarioIndex, Generator $faker): Cliente
    {
        [$nombre, $apellidoP, $apellidoM] = LatinoNameGenerator::person();
        $tieneActivo = $scenario['tiene_credito_activo'] ?? $this->hasActiveCredit($scenario['credito_estado']);

        return Cliente::create([
            'promotor_id' => $promotor->id,
            'CURP' => $faker->unique()->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}'),
            'nombre' => $nombre,
            'apellido_p' => $apellidoP,
            'apellido_m' => $apellidoM,
            'fecha_nacimiento' => Carbon::instance($faker->dateTimeBetween('-63 years', '-19 years'))->toDateString(),
            'tiene_credito_activo' => $tieneActivo,
            'cliente_estado' => $scenario['cliente_estado'],
            'monto_maximo' => $scenario['monto_total'] + 2000,
            'creado_en' => Carbon::now(),
            'actualizado_en' => Carbon::now(),
            'activo' => $tieneActivo,
        ]);
    }

    private function buildScenarioForState(string $creditState, int $index, ?float $overrideAmount, Generator $faker): array
    {
        $amounts = $this->allowedCreditAmounts();
        $blueprints = $this->scenarioBlueprints();
        $scenario = $blueprints[$creditState] ?? $blueprints[CreditoEstado::PROSPECTADO->value];

        $scenario['credito_estado'] = $creditState;
        $scenario['monto_total'] = $overrideAmount ?? $amounts[$index % count($amounts)];
        $scenario['slug'] = sprintf('%s-%d', $creditState, $index + 1);

        if (!isset($scenario['tiene_credito_activo'])) {
            $scenario['tiene_credito_activo'] = $this->hasActiveCredit($creditState);
        }

        if (!isset($scenario['periodicidad'])) {
            $scenario['periodicidad'] = PeriodicidadCreditos::default()->value;
        } else {
            $scenario['periodicidad'] = PeriodicidadCreditos::tryFromLabel((string) $scenario['periodicidad'])?->value
                ?? (string) $scenario['periodicidad'];
        }

        if (!isset($scenario['interes'])) {
            $scenario['interes'] = $faker->randomFloat(1, 9.0, 14.5);
        }

        if (!isset($scenario['pagos'])) {
            $scenario['pagos'] = [];
        }

        if (!isset($scenario['extras'])) {
            $scenario['extras'] = [];
        }

        return $scenario;
    }

    /**
     * @return array<int, float>
     */
    private function allowedCreditAmounts(): array
    {
        static $amounts;

        if ($amounts === null) {
            $amounts = array_map(static fn (int $value): float => (float) $value, range(3000, 10000, 500));
        }

        return $amounts;
    }

    private function scenarioBlueprints(): array
    {
        return [
            CreditoEstado::PROSPECTADO->value => [
                'cliente_estado' => ClienteEstado::PROSPECTO->value,
                'pagos' => [],
                'extras' => [],
                'interes' => 10.0,
            ],
            CreditoEstado::PROSPECTADO_REACREDITO->value => [
                'cliente_estado' => ClienteEstado::REGULARIZADO->value,
                'pagos' => [],
                'extras' => [],
                'interes' => 10.5,
            ],
            CreditoEstado::SOLICITADO->value => [
                'cliente_estado' => ClienteEstado::INACTIVO->value,
                'pagos' => ['pendiente', 'pendiente', 'pendiente'],
                'extras' => [],
                'interes' => 12.0,
            ],
            CreditoEstado::APROBADO->value => [
                'cliente_estado' => ClienteEstado::ACTIVO->value,
                'pagos' => ['pendiente', 'pendiente', 'pendiente', 'pendiente'],
                'extras' => [],
                'interes' => 11.2,
                'tiene_credito_activo' => true,
            ],
            CreditoEstado::SUPERVISADO->value => [
                'cliente_estado' => ClienteEstado::SUPERVISADO->value,
                'pagos' => ['pagado', 'pagado', 'pendiente', 'pendiente', 'pendiente'],
                'extras' => ['completo', 'anticipo'],
                'interes' => 11.8,
                'tiene_credito_activo' => true,
            ],
            CreditoEstado::DESEMBOLSADO->value => [
                'cliente_estado' => ClienteEstado::DESEMBOLSADO->value,
                'pagos' => ['pagado', 'pendiente', 'pendiente', 'pendiente', 'pendiente'],
                'extras' => ['anticipo'],
                'interes' => 12.6,
                'tiene_credito_activo' => true,
            ],
            CreditoEstado::LIQUIDADO->value => [
                'cliente_estado' => ClienteEstado::REGULARIZADO->value,
                'pagos' => array_fill(0, 6, 'pagado'),
                'extras' => ['completo'],
                'interes' => 9.6,
            ],
            CreditoEstado::ACTIVO->value => [
                'cliente_estado' => ClienteEstado::ACTIVO->value,
                'pagos' => ['pagado', 'pagado', 'pendiente', 'pendiente'],
                'extras' => ['completo'],
                'interes' => 11.0,
                'tiene_credito_activo' => true,
            ],
            CreditoEstado::RECHAZADO->value => [
                'cliente_estado' => ClienteEstado::CANCELADO->value,
                'pagos' => [],
                'extras' => [],
                'interes' => 0.0,
                'tiene_credito_activo' => false,
            ],
            CreditoEstado::VENCIDO->value => [
                'cliente_estado' => ClienteEstado::MOROSO->value,
                'pagos' => ['pagado', 'vencido', 'vencido', 'pendiente', 'pendiente'],
                'extras' => ['diferido'],
                'interes' => 14.0,
                'tiene_credito_activo' => true,
            ],
            CreditoEstado::CANCELADO->value => [
                'cliente_estado' => ClienteEstado::CANCELADO->value,
                'pagos' => ['pagado', 'vencido'],
                'extras' => [],
                'interes' => 9.4,
            ],
            CreditoEstado::AVAL_RIESGO->value => [
                'cliente_estado' => ClienteEstado::POR_SUPERVISAR->value,
                'pagos' => ['pendiente', 'pendiente', 'pendiente'],
                'extras' => ['anticipo'],
                'interes' => 12.2,
            ],
            CreditoEstado::CLIENTE_RIESGO->value => [
                'cliente_estado' => ClienteEstado::FALLA->value,
                'pagos' => ['pagado', 'vencido', 'pendiente', 'pendiente'],
                'extras' => ['diferido'],
                'interes' => 13.2,
            ],
        ];
    }

    private function promotorPlan(Promotor $promotor): array
    {
        $email = optional($promotor->user)->email;

        return match ($email) {
            'promotor@example.com' => [
                'states' => [
                    CreditoEstado::PROSPECTADO->value,
                    CreditoEstado::PROSPECTADO_REACREDITO->value,
                    CreditoEstado::SOLICITADO->value,
                    CreditoEstado::APROBADO->value,
                    CreditoEstado::SUPERVISADO->value,
                    CreditoEstado::DESEMBOLSADO->value,
                    CreditoEstado::LIQUIDADO->value,
                    CreditoEstado::ACTIVO->value,
                    CreditoEstado::RECHAZADO->value,
                    CreditoEstado::AVAL_RIESGO->value,
                    CreditoEstado::CLIENTE_RIESGO->value,
                    CreditoEstado::VENCIDO->value,
                    CreditoEstado::DESEMBOLSADO->value,
                    CreditoEstado::SUPERVISADO->value,
                    CreditoEstado::APROBADO->value,
                    CreditoEstado::SOLICITADO->value,
                    CreditoEstado::PROSPECTADO->value,
                    CreditoEstado::ACTIVO->value,
                    CreditoEstado::LIQUIDADO->value,
                    CreditoEstado::PROSPECTADO_REACREDITO->value,
                ],
                'override_amounts' => [
                    11 => 3000.0,
                ],
            ],
            'promotor2@example.com' => [
                'states' => [
                    CreditoEstado::PROSPECTADO->value,
                    CreditoEstado::PROSPECTADO_REACREDITO->value,
                    CreditoEstado::SOLICITADO->value,
                    CreditoEstado::APROBADO->value,
                    CreditoEstado::SUPERVISADO->value,
                    CreditoEstado::DESEMBOLSADO->value,
                    CreditoEstado::LIQUIDADO->value,
                    CreditoEstado::ACTIVO->value,
                    CreditoEstado::RECHAZADO->value,
                    CreditoEstado::AVAL_RIESGO->value,
                    CreditoEstado::CLIENTE_RIESGO->value,
                    CreditoEstado::APROBADO->value,
                    CreditoEstado::SUPERVISADO->value,
                    CreditoEstado::DESEMBOLSADO->value,
                    CreditoEstado::ACTIVO->value,
                    CreditoEstado::LIQUIDADO->value,
                    CreditoEstado::PROSPECTADO->value,
                    CreditoEstado::PROSPECTADO_REACREDITO->value,
                    CreditoEstado::SOLICITADO->value,
                    CreditoEstado::APROBADO->value,
                ],
                'override_amounts' => [],
            ],
            'ejecutivo1.supervisor2.promotor1@example.com' => [
                'states' => $this->defaultStateSequence(),
                'override_amounts' => [
                    11 => 4500.0,
                    12 => 4000.0,
                ],
            ],
            'ejecutivo1.supervisor2.promotor2@example.com' => [
                'states' => [
                    CreditoEstado::PROSPECTADO->value,
                    CreditoEstado::PROSPECTADO_REACREDITO->value,
                    CreditoEstado::SOLICITADO->value,
                    CreditoEstado::APROBADO->value,
                    CreditoEstado::SUPERVISADO->value,
                    CreditoEstado::DESEMBOLSADO->value,
                    CreditoEstado::LIQUIDADO->value,
                    CreditoEstado::ACTIVO->value,
                    CreditoEstado::RECHAZADO->value,
                    CreditoEstado::AVAL_RIESGO->value,
                    CreditoEstado::CLIENTE_RIESGO->value,
                    CreditoEstado::VENCIDO->value,
                    CreditoEstado::CANCELADO->value,
                    CreditoEstado::VENCIDO->value,
                    CreditoEstado::CANCELADO->value,
                    CreditoEstado::DESEMBOLSADO->value,
                    CreditoEstado::SUPERVISADO->value,
                    CreditoEstado::APROBADO->value,
                    CreditoEstado::SOLICITADO->value,
                    CreditoEstado::ACTIVO->value,
                ],
                'override_amounts' => [
                    11 => 10000.0,
                    12 => 9500.0,
                    13 => 9000.0,
                    14 => 8500.0,
                ],
            ],
            default => [
                'states' => $this->defaultStateSequence(),
                'override_amounts' => [],
            ],
        };
    }

    /**
     * @return array<int, string>
     */
    private function defaultStateSequence(): array
    {
        return [
            CreditoEstado::PROSPECTADO->value,
            CreditoEstado::PROSPECTADO_REACREDITO->value,
            CreditoEstado::SOLICITADO->value,
            CreditoEstado::APROBADO->value,
            CreditoEstado::SUPERVISADO->value,
            CreditoEstado::DESEMBOLSADO->value,
            CreditoEstado::LIQUIDADO->value,
            CreditoEstado::ACTIVO->value,
            CreditoEstado::RECHAZADO->value,
            CreditoEstado::AVAL_RIESGO->value,
            CreditoEstado::CLIENTE_RIESGO->value,
            CreditoEstado::VENCIDO->value,
            CreditoEstado::CANCELADO->value,
            CreditoEstado::DESEMBOLSADO->value,
            CreditoEstado::SUPERVISADO->value,
            CreditoEstado::APROBADO->value,
            CreditoEstado::SOLICITADO->value,
            CreditoEstado::PROSPECTADO->value,
            CreditoEstado::ACTIVO->value,
            CreditoEstado::LIQUIDADO->value,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function failureStates(): array
    {
        return [
            CreditoEstado::VENCIDO->value,
            CreditoEstado::CANCELADO->value,
        ];
    }

    private function printPromotorFailureSummary(array $promotorStats): void
    {
        if (empty($promotorStats)) {
            return;
        }

        $collection = collect($promotorStats)->map(static function (array $stats) {
            $percentage = $stats['totalAmount'] > 0
                ? round(($stats['failureAmount'] / $stats['totalAmount']) * 100, 2)
                : 0.0;

            return array_merge($stats, ['failurePercentage' => $percentage]);
        });

        $lessThanFive = $collection
            ->reject(static fn (array $stats) => $stats['email'] === 'promotor@example.com')
            ->first(static fn (array $stats) => $stats['failurePercentage'] < 5.0);

        $betweenFiveAndTen = $collection
            ->first(static fn (array $stats) => $stats['failurePercentage'] > 5.0 && $stats['failurePercentage'] < 10.0);

        $greaterThanTen = $collection
            ->first(static fn (array $stats) => $stats['failurePercentage'] > 10.0);

        foreach ([$lessThanFive, $betweenFiveAndTen, $greaterThanTen] as $stats) {
            if (!$stats) {
                continue;
            }

            printf("%s | falla del %.2f%%\n", $stats['email'], $stats['failurePercentage']);
        }
    }

    private function createCreditoConRelaciones(Cliente $cliente, array $scenario, Generator $faker, array &$ocupacionesBucket): Credito
    {
        $pagos = $scenario['pagos'] ?? [];
        $montoTotal = $scenario['monto_total'];
        $periodicidad = $scenario['periodicidad'] ?? PeriodicidadCreditos::default()->value;
        $extras = $scenario['extras'] ?? [];
        $interes = $scenario['interes'] ?? 11.5;

        if (empty($pagos)) {
            $fechaInicio = Carbon::now()->addDays(5);
        } else {
            $indicePendiente = array_search('pendiente', $pagos, true);
            if ($indicePendiente === false) {
                $fechaInicio = Carbon::now()->subWeeks(count($pagos));
            } else {
                $fechaInicio = Carbon::now()->addWeek()->subWeeks($indicePendiente);
            }
        }

        $fechaFinal = $periodicidad === 'Mes'
            ? (clone $fechaInicio)->addMonth()
            : (clone $fechaInicio)->addWeeks(max(count($pagos), 1));

        $credito = Credito::create([
            'cliente_id' => $cliente->id,
            'monto_total' => $montoTotal,
            'estado' => $scenario['credito_estado'],
            'interes' => $interes,
            'periodicidad' => $periodicidad,
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_final' => $fechaFinal->toDateString(),
        ]);

        DatoContacto::create([
            'credito_id' => $credito->id,
            'calle' => $faker->streetName(),
            'numero_ext' => $faker->buildingNumber(),
            'numero_int' => $faker->optional()->buildingNumber(),
            'monto_mensual' => $faker->numberBetween(1500, 4500),
            'colonia' => $faker->citySuffix(),
            'municipio' => $faker->city(),
            'estado' => $faker->state(),
            'cp' => $faker->postcode(),
            'tiempo_en_residencia' => $faker->numberBetween(1, 12) . ' anos',
            'tel_fijo' => $faker->optional()->phoneNumber(),
            'tel_cel' => $faker->phoneNumber(),
            'tipo_de_vivienda' => $faker->randomElement(['propia', 'rentada', 'familiar']),
            'creado_en' => Carbon::now(),
        ]);

        $ocupacion = Ocupacion::create([
            'credito_id' => $credito->id,
            'actividad' => $faker->jobTitle(),
            'nombre_empresa' => $faker->company(),
            'calle' => $faker->streetName(),
            'numero' => $faker->buildingNumber(),
            'colonia' => $faker->citySuffix(),
            'municipio' => $faker->city(),
            'telefono' => $faker->phoneNumber(),
            'antiguedad' => $faker->numberBetween(1, 12) . ' anos',
            'monto_percibido' => $faker->randomFloat(2, 1500, 7500),
            'periodo_pago' => $faker->randomElement(['semanal', 'quincenal', 'mensual']),
            'creado_en' => Carbon::now(),
        ]);
        $ocupacionesBucket[] = $ocupacion;

        InformacionFamiliar::create([
            'credito_id' => $credito->id,
            'nombre_conyuge' => LatinoNameGenerator::fullName(),
            'celular_conyuge' => $faker->phoneNumber(),
            'actividad_conyuge' => $faker->jobTitle(),
            'ingresos_semanales_conyuge' => $faker->randomFloat(2, 900, 2400),
            'domicilio_trabajo_conyuge' => $faker->address(),
            'personas_en_domicilio' => $faker->numberBetween(2, 7),
            'dependientes_economicos' => $faker->numberBetween(0, 3),
            'conyuge_vive_con_cliente' => $faker->boolean(),
            'creado_en' => Carbon::now(),
        ]);

        [$avalNombre, $avalApellidoP, $avalApellidoM] = LatinoNameGenerator::person();

        $aval = Aval::create([
            'CURP' => strtoupper($faker->bothify('????######??????##')),
            'credito_id' => $credito->id,
            'nombre' => $avalNombre,
            'apellido_p' => $avalApellidoP,
            'apellido_m' => $avalApellidoM,
            'fecha_nacimiento' => Carbon::instance($faker->dateTimeBetween('-60 years', '-25 years'))->toDateString(),
            'direccion' => $faker->address(),
            'telefono' => $faker->phoneNumber(),
            'parentesco' => $faker->randomElement(['hermano', 'amigo', 'conyuge', 'primo']),
            'creado_en' => Carbon::now(),
        ]);

        DocumentoAval::create([
            'aval_id' => $aval->id,
            'tipo_doc' => 'identificacion',
            'url_s3' => $faker->url(),
            'nombre_arch' => 'identificacion-aval.pdf',
            'creado_en' => Carbon::now(),
        ]);

        DocumentoCliente::create([
            'cliente_id' => $cliente->id,
            'credito_id' => $credito->id,
            'tipo_doc' => 'identificacion',
            'url_s3' => $faker->url(),
            'nombre_arch' => 'ine-cliente.pdf',
            'creado_en' => Carbon::now(),
        ]);

        Garantia::create([
            'credito_id' => $credito->id,
            'propietario' => LatinoNameGenerator::fullName(),
            'tipo' => $faker->randomElement(['electrodomestico', 'vehiculo', 'mobiliario']),
            'marca' => $faker->company(),
            'modelo' => strtoupper($faker->bothify('MOD###')),
            'num_serie' => strtoupper($faker->bothify('SER###??')),
            'antiguedad' => $faker->numberBetween(1, 8) . ' anos',
            'monto_garantizado' => $faker->randomFloat(2, 1800, 6200),
            'foto_url' => $faker->imageUrl(),
            'creado_en' => Carbon::now(),
        ]);

        Contrato::create([
            'credito_id' => $credito->id,
            'tipo_contrato' => 'credito individual',
            'fecha_generacion' => $fechaInicio->copy()->subDay()->toDateString(),
            'url_s3' => $faker->url(),
        ]);

        $pagosProyectados = [];
        $acumulado = 0.0;
        $totalPagos = count($pagos);

        foreach ($pagos as $index => $estadoPago) {
            $fechaLimite = (clone $fechaInicio)->addWeeks($index);

            if ($estadoPago === 'vencido' && $fechaLimite->greaterThan(Carbon::now())) {
                $fechaLimite = Carbon::now()->subDays(3);
            }

            if ($estadoPago === 'pagado' && $fechaLimite->greaterThan(Carbon::now())) {
                $fechaLimite = Carbon::now()->subDay();
            }

            if ($estadoPago === 'pendiente' && $fechaLimite->lessThan(Carbon::now())) {
                $fechaLimite = Carbon::now()->addDays(4);
            }

            $restantes = $totalPagos - $index;
            $montoPago = $restantes === 1
                ? round($montoTotal - $acumulado, 2)
                : round($montoTotal / max($totalPagos, 1), 2);

            $acumulado += $montoPago;

            $pagosProyectados[] = PagoProyectado::create([
                'credito_id' => $credito->id,
                'semana' => $index + 1,
                'monto_proyectado' => $montoPago,
                'fecha_limite' => $fechaLimite->toDateString(),
                'estado' => $estadoPago,
            ]);
        }

        $this->createPagoRealidades($pagosProyectados, $extras, $scenario);

        return $credito;
    }

    private function createPagoRealidades(array $pagosProyectados, array $extras, array $scenario): void
    {
        if (empty($extras) || empty($pagosProyectados)) {
            return;
        }

        if (in_array('completo', $extras, true) && isset($pagosProyectados[0])) {
            $primerPago = $pagosProyectados[0];
            $fechaPagoCompleto = Carbon::parse($primerPago->fecha_limite);
            if ($fechaPagoCompleto->greaterThan(Carbon::now())) {
                $fechaPagoCompleto = Carbon::now()->subDay();
            }

            $pagoReal = PagoReal::create([
                'pago_proyectado_id' => $primerPago->id,
                'tipo' => 'efectivo',
                'fecha_pago' => $fechaPagoCompleto->toDateString(),
                'comentario' => 'Pago semanal completado.',
            ]);

            PagoCompleto::create([
                'pago_real_id' => $pagoReal->id,
                'monto_completo' => (float) $primerPago->monto_proyectado,
            ]);
        }

        if (in_array('anticipo', $extras, true) && isset($pagosProyectados[1])) {
            $segundoPago = $pagosProyectados[1];
            $fechaAnticipo = Carbon::parse($segundoPago->fecha_limite)->subDays(2);
            if ($fechaAnticipo->greaterThan(Carbon::now())) {
                $fechaAnticipo = Carbon::now();
            }

            $pagoReal = PagoReal::create([
                'pago_proyectado_id' => $segundoPago->id,
                'tipo' => 'transferencia',
                'fecha_pago' => $fechaAnticipo->toDateString(),
                'comentario' => 'Anticipo registrado por app.',
            ]);

            $factorAnticipo = match ($scenario['credito_estado']) {
                CreditoEstado::VENCIDO->value => 0.4,
                CreditoEstado::DESEMBOLSADO->value => 0.5,
                default => 0.7,
            };

            PagoAnticipo::create([
                'pago_real_id' => $pagoReal->id,
                'monto_anticipo' => round((float) $segundoPago->monto_proyectado * $factorAnticipo, 2),
            ]);
        }

        if (in_array('diferido', $extras, true) && isset($pagosProyectados[2])) {
            $tercerPago = $pagosProyectados[2];
            $fechaDiferido = Carbon::parse($tercerPago->fecha_limite)->addDays(3);
            if ($fechaDiferido->greaterThan(Carbon::now())) {
                $fechaDiferido = Carbon::now();
            }

            $pagoReal = PagoReal::create([
                'pago_proyectado_id' => $tercerPago->id,
                'tipo' => 'transferencia',
                'fecha_pago' => $fechaDiferido->toDateString(),
                'comentario' => 'Pago diferido autorizado.',
            ]);

            $factorDiferido = match ($scenario['cliente_estado']) {
                ClienteEstado::MOROSO->value => 0.8,
                ClienteEstado::REGULARIZADO->value => 0.3,
                ClienteEstado::INACTIVO->value => 0.0,
                default => 0.5,
            };

            PagoDiferido::create([
                'pago_real_id' => $pagoReal->id,
                'monto_diferido' => round((float) $tercerPago->monto_proyectado * $factorDiferido, 2),
            ]);
        }
    }

    private function hasActiveCredit(string $estadoCredito): bool
    {
        return in_array($estadoCredito, [
            CreditoEstado::ACTIVO->value,
            CreditoEstado::APROBADO->value,
            CreditoEstado::SUPERVISADO->value,
            CreditoEstado::DESEMBOLSADO->value,
            CreditoEstado::VENCIDO->value,
        ], true);
    }
}
