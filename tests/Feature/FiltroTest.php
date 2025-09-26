<?php

namespace Tests\Feature;

use App\Http\Controllers\FiltrosController;
use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Instrucciones extremadamente detalladas para ejecutar estas pruebas de filtros:
 * 1. Asegúrate de tener la base de datos configurada; puedes refrescarla con `php artisan migrate:fresh`.
 * 2. Si deseas contar con los datos de ejemplo completos, ejecuta `php artisan db:seed`.
 * 3. Para ejecutar únicamente estas pruebas usa `php artisan test --filter=FiltroTest`.
 * 4. Para obtener más detalle añade `-vvv` al comando anterior (`php artisan test --filter=FiltroTest -vvv`).
 * 5. Si prefieres PHPUnit directo, utiliza `./vendor/bin/phpunit --filter FiltroTest`.
 */
class FiltroTest extends TestCase
{
    use RefreshDatabase;

    private FiltrosController $filtros;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filtros = $this->app->make(FiltrosController::class);
    }

    public function test_curp_unica_filter_detects_duplicate_curp(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        [$clienteOriginal] = $this->crearClienteConCredito($estructura['promotor']);
        [$clienteDuplicado] = $this->crearClienteConCredito($estructura['promotor'], ['CURP' => 'DUPLICADO123456789']);

        $form = [
            'cliente' => ['curp' => $clienteDuplicado->CURP],
            'aval' => ['curp' => 'AVALCURP111111111'],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($clienteOriginal, $form, ['tipo_solicitud' => 'nuevo']);

        $this->assertFalse($resultado['passed']);
        $this->assertSame(FiltrosController::FILTER_CURP_UNICA, $resultado['failed_filter']);
    }

    public function test_doble_firma_aval_filter_blocks_third_credit(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        $avalCurp = 'AVALCURP222222222';

        $this->crearClienteConCredito($estructura['promotor'], [], [
            'estado' => 'desembolsado',
            'aval_curp' => $avalCurp,
        ]);
        $this->crearClienteConCredito($estructura['promotor'], [], [
            'estado' => 'aprobado',
            'aval_curp' => $avalCurp,
        ]);

        [$clienteNuevo] = $this->crearClienteConCredito($estructura['promotor'], ['CURP' => 'CLIENTETERCE000000'], [
            'estado' => 'prospectado',
            'aval_curp' => 'UNICOAVAL33333333',
        ]);

        $form = [
            'cliente' => ['curp' => $clienteNuevo->CURP],
            'aval' => ['curp' => $avalCurp],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($clienteNuevo, $form, ['tipo_solicitud' => 'nuevo']);

        $this->assertFalse($resultado['passed']);
        $this->assertSame(FiltrosController::FILTER_DOBLE_FIRMA_AVAL, $resultado['failed_filter']);
    }

    public function test_credito_en_falla_filter_denies_when_client_is_in_default(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        [$clienteMoroso] = $this->crearClienteConCredito($estructura['promotor'], [
            'cartera_estado' => 'moroso',
            'tiene_credito_activo' => true,
        ], [
            'estado' => 'vencido',
        ]);

        $form = [
            'cliente' => ['curp' => $clienteMoroso->CURP],
            'aval' => ['curp' => 'AVALCURP444444444'],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($clienteMoroso, $form, ['tipo_solicitud' => 'nuevo']);

        $this->assertFalse($resultado['passed']);
        $this->assertSame(FiltrosController::FILTER_CREDITO_EN_FALLA, $resultado['failed_filter']);
    }

    public function test_credito_activo_filter_blocks_new_credit(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        [$clienteActivo] = $this->crearClienteConCredito($estructura['promotor'], [
            'tiene_credito_activo' => true,
            'cartera_estado' => 'activo',
        ], [
            'estado' => 'desembolsado',
        ]);

        $form = [
            'cliente' => ['curp' => $clienteActivo->CURP],
            'aval' => ['curp' => 'AVALCURP555555555'],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($clienteActivo, $form, ['tipo_solicitud' => 'nuevo']);

        $this->assertFalse($resultado['passed']);
        $this->assertSame(FiltrosController::FILTER_CREDITO_ACTIVO, $resultado['failed_filter']);
    }

    public function test_credito_activo_filter_allows_recredit(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        [$clienteActivo] = $this->crearClienteConCredito($estructura['promotor'], [
            'tiene_credito_activo' => true,
            'cartera_estado' => 'activo',
        ], [
            'estado' => 'desembolsado',
            'periodicidad' => '14Semanas',
            'fecha_inicio' => Carbon::now()->subWeeks(12)->toDateString(),
        ]);

        $form = [
            'cliente' => ['curp' => $clienteActivo->CURP],
            'aval' => ['curp' => 'AVALCURP666666666'],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($clienteActivo, $form, [
            'tipo_solicitud' => 'recredito',
            'ultimo_credito' => $clienteActivo->creditos->first(),
            'fecha_solicitud' => Carbon::now(),
        ]);

        $this->assertTrue($resultado['passed']);
    }

    public function test_otra_plaza_filter_blocks_when_promotor_differs(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        $otraJerarquia = $this->crearJerarquiaBasica(false);

        [$cliente] = $this->crearClienteConCredito($estructura['promotor']);

        $form = [
            'cliente' => ['curp' => $cliente->CURP],
            'aval' => ['curp' => 'AVALCURP777777777'],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($cliente, $form, [
            'tipo_solicitud' => 'nuevo',
            'promotor_id' => $otraJerarquia['promotor']->id,
            'supervisor_id' => $otraJerarquia['promotor']->supervisor_id,
            'fecha_solicitud' => Carbon::now(),
        ]);

        $this->assertFalse($resultado['passed']);
        $this->assertSame(FiltrosController::FILTER_OTRA_PLAZA, $resultado['failed_filter']);
    }

    public function test_bloqueo_falla_promotora_blocks_new_credit_when_threshold_exceeded(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        // Crear varios créditos con alto porcentaje en falla
        for ($i = 0; $i < 3; $i++) {
            $this->crearClienteConCredito($estructura['promotor'], [], ['estado' => 'desembolsado']);
        }
        $this->crearClienteConCredito($estructura['promotor'], [], ['estado' => 'vencido']);

        [$clienteNuevo] = $this->crearClienteConCredito($estructura['promotor'], ['CURP' => 'PROMOTORFAIL00001']);

        $form = [
            'cliente' => ['curp' => $clienteNuevo->CURP],
            'aval' => ['curp' => 'AVALCURP888888888'],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($clienteNuevo, $form, [
            'tipo_solicitud' => 'nuevo',
            'fecha_solicitud' => Carbon::now(),
        ]);

        $this->assertFalse($resultado['passed']);
        $this->assertSame(FiltrosController::FILTER_BLOQUEO_FALLA_PROMOTORA, $resultado['failed_filter']);
    }

    public function test_bloqueo_falla_promotora_allows_recredit_when_threshold_exceeded(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        for ($i = 0; $i < 2; $i++) {
            $this->crearClienteConCredito($estructura['promotor'], [], ['estado' => 'vencido']);
        }
        [$clienteNuevo] = $this->crearClienteConCredito($estructura['promotor'], [
            'CURP' => 'PROMOTORREAC0001',
            'tiene_credito_activo' => true,
            'cartera_estado' => 'activo',
        ], [
            'estado' => 'desembolsado',
            'fecha_inicio' => Carbon::now()->subWeeks(12)->toDateString(),
            'periodicidad' => '14Semanas',
        ]);

        $form = [
            'cliente' => ['curp' => $clienteNuevo->CURP],
            'aval' => ['curp' => 'AVALCURP999999999'],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($clienteNuevo, $form, [
            'tipo_solicitud' => 'recredito',
            'fecha_solicitud' => Carbon::now(),
        ]);

        $this->assertTrue($resultado['passed']);
    }

    public function test_doble_domicilio_filter_blocks_without_authorization(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        $direccion = [
            'calle' => 'Calle Siempre Viva',
            'numero_ext' => '742',
            'colonia' => 'Springfield',
            'municipio' => 'Springfield',
            'cp' => '12345',
        ];

        $this->crearClienteConCredito($estructura['promotor'], [], [
            'estado' => 'desembolsado',
            'direccion' => $direccion,
        ]);
        $this->crearClienteConCredito($estructura['promotor'], ['CURP' => 'OTROCLIENTE0002'], [
            'estado' => 'aprobado',
            'direccion' => $direccion,
        ]);

        [$clienteNuevo] = $this->crearClienteConCredito($estructura['promotor'], ['CURP' => 'DOMICILIONUEVO03'], [
            'estado' => 'liquidado',
        ]);

        $form = [
            'cliente' => ['curp' => $clienteNuevo->CURP],
            'aval' => ['curp' => 'AVALDOMICILIO11'],
            'contacto' => $direccion,
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($clienteNuevo, $form, [
            'tipo_solicitud' => 'nuevo',
            'fecha_solicitud' => Carbon::now(),
        ]);

        $this->assertFalse($resultado['passed']);
        $this->assertSame(FiltrosController::FILTER_DOBLE_DOMICILIO, $resultado['failed_filter']);
    }

    public function test_doble_domicilio_filter_allows_with_authorization_and_gap(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        $direccion = [
            'calle' => 'Calle Autorizada',
            'numero_ext' => '101',
            'colonia' => 'Centro',
            'municipio' => 'Ciudad',
            'cp' => '67890',
        ];

        $this->crearClienteConCredito($estructura['promotor'], [], [
            'estado' => 'desembolsado',
            'fecha_inicio' => Carbon::now()->subWeeks(20)->toDateString(),
            'direccion' => $direccion,
        ]);
        $this->crearClienteConCredito($estructura['promotor'], ['CURP' => 'AUTORIZADO0002'], [
            'estado' => 'aprobado',
            'fecha_inicio' => Carbon::now()->subWeeks(12)->toDateString(),
            'direccion' => $direccion,
        ]);

        [$clienteNuevo] = $this->crearClienteConCredito($estructura['promotor'], ['CURP' => 'AUTORIZADO0003'], [
            'estado' => 'liquidado',
        ]);

        $form = [
            'cliente' => ['curp' => $clienteNuevo->CURP],
            'aval' => ['curp' => 'AVALDOMICILIO22'],
            'contacto' => $direccion,
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($clienteNuevo, $form, [
            'tipo_solicitud' => 'nuevo',
            'autorizacion_especial_domicilio' => true,
            'fecha_solicitud' => Carbon::now(),
        ]);

        $this->assertTrue($resultado['passed']);
    }

    public function test_bloqueo_tiempo_recreditos_enforces_waiting_period(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        [$cliente] = $this->crearClienteConCredito($estructura['promotor'], [
            'tiene_credito_activo' => true,
            'cartera_estado' => 'activo',
        ], [
            'estado' => 'desembolsado',
            'periodicidad' => '13Semanas',
            'fecha_inicio' => Carbon::now()->subWeeks(5)->toDateString(),
        ]);

        $form = [
            'cliente' => ['curp' => $cliente->CURP],
            'aval' => ['curp' => 'AVALREACREDITO1'],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($cliente, $form, [
            'tipo_solicitud' => 'recredito',
            'ultimo_credito' => $cliente->creditos->first(),
            'fecha_solicitud' => Carbon::now(),
        ]);

        $this->assertFalse($resultado['passed']);
        $this->assertSame(FiltrosController::FILTER_BLOQUEO_TIEMPO_REACREDITOS, $resultado['failed_filter']);
    }

    public function test_bloqueo_tiempo_recreditos_passes_when_requirements_met(): void
    {
        $estructura = $this->crearJerarquiaBasica();
        [$cliente] = $this->crearClienteConCredito($estructura['promotor'], [
            'tiene_credito_activo' => true,
            'cartera_estado' => 'activo',
        ], [
            'estado' => 'desembolsado',
            'periodicidad' => '14Semanas',
            'fecha_inicio' => Carbon::now()->subWeeks(12)->toDateString(),
        ]);

        $form = [
            'cliente' => ['curp' => $cliente->CURP],
            'aval' => ['curp' => 'AVALREACREDITO2'],
            'contacto' => [],
            'credito' => ['fecha_inicio' => Carbon::now()->toDateString()],
        ];

        $resultado = $this->filtros->evaluar($cliente, $form, [
            'tipo_solicitud' => 'recredito',
            'ultimo_credito' => $cliente->creditos->first(),
            'fecha_solicitud' => Carbon::now(),
        ]);

        $this->assertTrue($resultado['passed']);
    }

    /**
     * @return array{ejecutivo: Ejecutivo, supervisor: Supervisor, promotor: Promotor}
     */
    private function crearJerarquiaBasica(bool $principal = true): array
    {
        [$nombreE, $apellidoPE, $apellidoME] = LatinoNameGenerator::person();
        $ejecutivoUser = User::factory()->create([
            'rol' => 'ejecutivo',
            'email' => $principal ? 'ejecutivo.test@kualifin.com' : fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ]);
        $ejecutivo = Ejecutivo::create([
            'user_id' => $ejecutivoUser->id,
            'nombre' => $nombreE,
            'apellido_p' => $apellidoPE,
            'apellido_m' => $apellidoME,
        ]);

        [$nombreS, $apellidoPS, $apellidoMS] = LatinoNameGenerator::person();
        $supervisorUser = User::factory()->create([
            'rol' => 'supervisor',
            'email' => $principal ? 'supervisor.test@kualifin.com' : fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ]);
        $supervisor = Supervisor::create([
            'user_id' => $supervisorUser->id,
            'ejecutivo_id' => $ejecutivo->id,
            'nombre' => $nombreS,
            'apellido_p' => $apellidoPS,
            'apellido_m' => $apellidoMS,
        ]);

        [$nombreP, $apellidoPP, $apellidoMP] = LatinoNameGenerator::person();
        $promotorUser = User::factory()->create([
            'rol' => 'promotor',
            'email' => $principal ? 'promotor.test@kualifin.com' : fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ]);
        $promotor = Promotor::create([
            'user_id' => $promotorUser->id,
            'supervisor_id' => $supervisor->id,
            'nombre' => $nombreP,
            'apellido_p' => $apellidoPP,
            'apellido_m' => $apellidoMP,
            'venta_maxima' => 15000,
            'colonia' => 'Centro',
            'venta_proyectada_objetivo' => 8000,
            'bono' => 500,
            'dia_de_pago' => 'Lunes',
            'hora_de_pago' => '08:00:00',
        ]);

        return compact('ejecutivo', 'supervisor', 'promotor');
    }

    /**
     * @param array<string, mixed> $clienteOverrides
     * @param array<string, mixed> $creditoOverrides
     * @return array{Cliente, Credito}
     */
    private function crearClienteConCredito(Promotor $promotor, array $clienteOverrides = [], array $creditoOverrides = []): array
    {
        [$nombre, $apellidoP, $apellidoM] = LatinoNameGenerator::person();
        $clienteData = array_merge([
            'promotor_id' => $promotor->id,
            'CURP' => strtoupper(fake()->unique()->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}')),
            'nombre' => $nombre,
            'apellido_p' => $apellidoP,
            'apellido_m' => $apellidoM,
            'fecha_nacimiento' => Carbon::now()->subYears(30)->toDateString(),
            'tiene_credito_activo' => $clienteOverrides['tiene_credito_activo'] ?? false,
            'cartera_estado' => $clienteOverrides['cartera_estado'] ?? 'inactivo',
            'monto_maximo' => 5000,
            'activo' => (($clienteOverrides['cartera_estado'] ?? 'inactivo') !== 'inactivo'),
        ], $clienteOverrides);
        $cliente = Cliente::create($clienteData);

        $fechaInicio = isset($creditoOverrides['fecha_inicio'])
            ? Carbon::parse($creditoOverrides['fecha_inicio'])
            : Carbon::now()->subWeeks(8);
        $periodicidad = $creditoOverrides['periodicidad'] ?? '14Semanas';

        $credito = Credito::create([
            'cliente_id' => $cliente->id,
            'monto_total' => $creditoOverrides['monto_total'] ?? 5000,
            'estado' => $creditoOverrides['estado'] ?? 'cancelado',
            'interes' => 1.5,
            'periodicidad' => $periodicidad,
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_final' => $fechaInicio->copy()->addWeeks(14)->toDateString(),
        ]);

        $direccion = $creditoOverrides['direccion'] ?? [
            'calle' => $creditoOverrides['calle'] ?? fake()->streetName(),
            'numero_ext' => $creditoOverrides['numero_ext'] ?? (string) fake()->numberBetween(1, 999),
            'colonia' => $creditoOverrides['colonia'] ?? 'Centro',
            'municipio' => $creditoOverrides['municipio'] ?? 'Ciudad',
            'cp' => $creditoOverrides['cp'] ?? '00000',
        ];

        DatoContacto::create([
            'credito_id' => $credito->id,
            'calle' => $direccion['calle'],
            'numero_ext' => $direccion['numero_ext'],
            'numero_int' => null,
            'monto_mensual' => 1200,
            'colonia' => $direccion['colonia'],
            'municipio' => $direccion['municipio'],
            'estado' => 'CDMX',
            'cp' => $direccion['cp'],
            'tiempo_en_residencia' => '5 años',
            'tel_fijo' => null,
            'tel_cel' => '5512345678',
            'tipo_de_vivienda' => 'Propia',
        ]);

        $avalCurp = $creditoOverrides['aval_curp'] ?? strtoupper(fake()->unique()->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}'));
        Aval::create([
            'CURP' => $avalCurp,
            'credito_id' => $credito->id,
            'nombre' => fake()->firstName(),
            'apellido_p' => fake()->lastName(),
            'apellido_m' => fake()->lastName(),
            'fecha_nacimiento' => Carbon::now()->subYears(35)->toDateString(),
            'direccion' => 'Dirección aval',
            'telefono' => '5511122233',
            'parentesco' => 'Familiar',
        ]);

        return [$cliente, $credito];
    }
}
