<?php

namespace Database\Seeders;

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
        $promotores = Promotor::with(['supervisor.ejecutivo'])->get();

        if ($promotores->isEmpty()) {
            return;
        }

        $faker = fake();
        $scenarios = $this->clientScenarios();
        $tipoDocumento = TipoDocumento::firstOrCreate(['nombre' => 'default']);
        $ocupaciones = [];

        foreach ($promotores as $promotor) {
            $creditosPromotor = [];

            foreach ($scenarios as $index => $scenario) {
                $cliente = $this->createClienteParaPromotor($promotor, $scenario, $index, $faker);
                $creditosPromotor[] = $this->createCreditoConRelaciones($cliente, $scenario, $faker, $ocupaciones);
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
    }

    private function clientScenarios(): array
    {
        return [
            [
                'slug' => 'prospectado',
                'cartera_estado' => 'inactivo',
                'credito_estado' => 'prospectado',
                'monto_total' => 4500.00,
                'periodicidad' => '14Semanas',
                'pagos' => [],
                'extras' => [],
                'interes' => 10.0,
            ],
            [
                'slug' => 'prospectado_recredito',
                'cartera_estado' => 'regularizado',
                'credito_estado' => 'prospectado_recredito',
                'monto_total' => 5200.00,
                'periodicidad' => '15Semanas',
                'pagos' => [],
                'extras' => [],
                'interes' => 10.5,
            ],
            [
                'slug' => 'solicitado',
                'cartera_estado' => 'inactivo',
                'credito_estado' => 'solicitado',
                'monto_total' => 5800.00,
                'periodicidad' => 'Mes',
                'pagos' => ['pendiente', 'pendiente', 'pendiente'],
                'extras' => [],
                'interes' => 12.5,
            ],
            [
                'slug' => 'aprobado',
                'cartera_estado' => 'activo',
                'credito_estado' => 'aprobado',
                'monto_total' => 6400.00,
                'periodicidad' => '15Semanas',
                'pagos' => ['pendiente', 'pendiente', 'pendiente', 'pendiente'],
                'extras' => [],
                'interes' => 11.8,
            ],
            [
                'slug' => 'supervisado',
                'cartera_estado' => 'activo',
                'credito_estado' => 'supervisado',
                'monto_total' => 7000.00,
                'periodicidad' => '14Semanas',
                'pagos' => ['pagado', 'pagado', 'pendiente', 'pendiente', 'pendiente'],
                'extras' => ['completo', 'anticipo'],
                'interes' => 12.2,
            ],
            [
                'slug' => 'desembolsado',
                'cartera_estado' => 'desembolsado',
                'credito_estado' => 'desembolsado',
                'monto_total' => 7600.00,
                'periodicidad' => '14Semanas',
                'pagos' => ['pagado', 'pendiente', 'pendiente', 'pendiente', 'pendiente'],
                'extras' => ['anticipo'],
                'interes' => 12.8,
            ],
            [
                'slug' => 'liquidado',
                'cartera_estado' => 'regularizado',
                'credito_estado' => 'liquidado',
                'monto_total' => 6200.00,
                'periodicidad' => '14Semanas',
                'pagos' => array_fill(0, 6, 'pagado'),
                'extras' => ['completo'],
                'interes' => 9.8,
            ],
            [
                'slug' => 'vencido',
                'cartera_estado' => 'moroso',
                'credito_estado' => 'vencido',
                'monto_total' => 7800.00,
                'periodicidad' => '14Semanas',
                'pagos' => ['pagado', 'vencido', 'vencido', 'pendiente', 'pendiente'],
                'extras' => ['diferido'],
                'interes' => 14.5,
            ],
            [
                'slug' => 'cancelado',
                'cartera_estado' => 'inactivo',
                'credito_estado' => 'cancelado',
                'monto_total' => 5000.00,
                'periodicidad' => '15Semanas',
                'pagos' => ['pagado', 'vencido'],
                'extras' => [],
                'interes' => 9.5,
            ],
            [
                'slug' => 'desembolsado_en_recuperacion',
                'cartera_estado' => 'activo',
                'credito_estado' => 'desembolsado',
                'monto_total' => 8100.00,
                'periodicidad' => '22Semanas',
                'pagos' => ['pagado', 'pagado', 'pendiente', 'pendiente', 'pendiente', 'pendiente'],
                'extras' => ['completo', 'anticipo', 'diferido'],
                'interes' => 13.5,
            ],
        ];
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
            'cartera_estado' => $scenario['cartera_estado'],
            'monto_maximo' => $scenario['monto_total'] + 2000,
            'creado_en' => Carbon::now(),
            'actualizado_en' => Carbon::now(),
            'activo' => $tieneActivo,
        ]);
    }

    private function createCreditoConRelaciones(Cliente $cliente, array $scenario, Generator $faker, array &$ocupacionesBucket): Credito
    {
        $pagos = $scenario['pagos'] ?? [];
        $montoTotal = $scenario['monto_total'];
        $periodicidad = $scenario['periodicidad'] ?? '14Semanas';
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
                'vencido' => 0.4,
                'desembolsado' => 0.5,
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

            $factorDiferido = match ($scenario['cartera_estado']) {
                'moroso' => 0.8,
                'regularizado' => 0.3,
                'inactivo' => 0.0,
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
        return in_array($estadoCredito, ['aprobado', 'supervisado', 'desembolsado', 'vencido'], true);
    }
}
