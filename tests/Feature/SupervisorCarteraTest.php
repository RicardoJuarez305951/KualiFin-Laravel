<?php

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejecutivo;
use App\Models\PagoCompleto;
use App\Models\PagoProyectado;
use App\Models\PagoReal;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

$kanbanTestPath = dirname(__DIR__, 2) . '/database/kanban_test.sqlite';
putenv('KANBAN_DB_DATABASE=' . $kanbanTestPath);
$_ENV['KANBAN_DB_DATABASE'] = $kanbanTestPath;
$_SERVER['KANBAN_DB_DATABASE'] = $kanbanTestPath;

if (file_exists($kanbanTestPath)) {
    unlink($kanbanTestPath);
}

if (!file_exists($kanbanTestPath)) {
    touch($kanbanTestPath);
}

$envPath = dirname(__DIR__, 2) . '/.env';
if (!file_exists($envPath)) {
    file_put_contents($envPath, "");
}

beforeEach(function () {
    global $kanbanTestPath;

    config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->withoutVite();

    if (file_exists($kanbanTestPath)) {
        unlink($kanbanTestPath);
    }

    if (!file_exists($kanbanTestPath)) {
        touch($kanbanTestPath);
    }

    Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'promotor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'ejecutivo', 'guard_name' => 'web']);
});

it('muestra el progreso semanal por promotor cuando existen ventas', function () {
    Carbon::setTestNow(Carbon::create(2024, 6, 18, 12));

    [$user, $supervisor] = createSupervisorCarteraContext();

    $promotor = createPromotorParaSupervisorCartera($supervisor, [
        'nombre' => 'Ana',
        'apellido_p' => 'Lopez',
        'venta_maxima' => 6000,
        'venta_proyectada_objetivo' => 25000,
    ]);

    $clienteSemana = createClienteParaSupervisorCartera($promotor, 'CURPCARTERA0001', 'Carla');
    $inicioSemana = now()->copy()->startOfWeek();

    Credito::create([
        'cliente_id' => $clienteSemana->id,
        'monto_total' => 3000,
        'estado' => 'activo',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $inicioSemana->copy()->addDays(1),
        'fecha_final' => $inicioSemana->copy()->addWeeks(12),
    ]);

    $clienteFueraSemana = createClienteParaSupervisorCartera($promotor, 'CURPCARTERA0002', 'Berenice');

    Credito::create([
        'cliente_id' => $clienteFueraSemana->id,
        'monto_total' => 4000,
        'estado' => 'activo',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $inicioSemana->copy()->subWeek()->addDay(),
        'fecha_final' => $inicioSemana->copy()->addWeeks(10),
    ]);

    $response = $this->actingAs($user)->get(route('mobile.supervisor.cartera'));

    $response
        ->assertOk()
        ->assertSeeText('Ana Lopez')
        ->assertSeeText('$6,000.00')
        ->assertSeeText('$3,000.00')
        ->assertSeeText('$25,000.00')
        ->assertSeeText('50.00%')
        ->assertSeeText('Faltan $3,000.00 para alcanzar el objetivo semanal.');

    Carbon::setTestNow();
});

it('muestra progreso en cero cuando el promotor no tiene ventas en la semana', function () {
    Carbon::setTestNow(Carbon::create(2024, 6, 18, 12));

    [$user, $supervisor] = createSupervisorCarteraContext();

    $promotor = createPromotorParaSupervisorCartera($supervisor, [
        'nombre' => 'Diego',
        'apellido_p' => 'Ruiz',
        'venta_maxima' => 4000,
        'venta_proyectada_objetivo' => 18000,
    ]);

    createClienteParaSupervisorCartera($promotor, 'CURPCARTERA0003', 'Elisa');

    $response = $this->actingAs($user)->get(route('mobile.supervisor.cartera'));

    $response
        ->assertOk()
        ->assertSeeText('Diego Ruiz')
        ->assertSeeText('$4,000.00')
        ->assertSeeText('$18,000.00')
        ->assertSeeText('$0.00')
        ->assertSeeText('0.00%')
        ->assertSeeText('Faltan $4,000.00 para alcanzar el objetivo semanal.');

    Carbon::setTestNow();
});

it('muestra una alerta cuando la promotora supera 8% de falla durante tres semanas consecutivas', function () {
    $baseWeek = Carbon::create(2024, 6, 3, 11); // Lunes
    Carbon::setTestNow($baseWeek);

    [$user, $supervisor] = createSupervisorCarteraContext();
    $promotor = createPromotorParaSupervisorCartera($supervisor, [
        'nombre' => 'Rocio',
        'apellido_p' => 'Campos',
    ]);
    $cliente = createClienteParaSupervisorCartera($promotor, 'CURPFAIL0001', 'Lucia');

    $credito = Credito::create([
        'cliente_id' => $cliente->id,
        'monto_total' => 5000,
        'estado' => 'activo',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $baseWeek->copy()->subWeeks(1),
        'fecha_final' => $baseWeek->copy()->addWeeks(12),
    ]);

    $montoProyectado = 1000;
    $montoPagado = 920; // 8% de falla

    for ($i = 0; $i < 3; $i++) {
        $weekStart = $baseWeek->copy()->addWeeks($i)->startOfWeek();
        crearPagoProyectadoConPago($credito, $weekStart->copy()->addDays(2), $i + 1, $montoProyectado, $montoPagado);
    }

    $response = null;
    for ($i = 0; $i < 3; $i++) {
        $currentWeek = $baseWeek->copy()->addWeeks($i)->addDays(1);
        Carbon::setTestNow($currentWeek);
        $response = $this->actingAs($user)->get(route('mobile.supervisor.cartera'));
        $response->assertOk();
    }

    $response
        ->assertSeeText('Falla de 8.00% por 3 semanas consecutivas.')
        ->assertSeeText('acumula una falla del 8.00% durante 3 semanas consecutivas.');

    $this->assertDatabaseHas('promotor_failure_streaks', [
        'promotor_id' => $promotor->id,
        'streak_count' => 3,
        'alert_active' => true,
    ]);

    Carbon::setTestNow();
});

it('reinicia la racha de fallas cuando la promotora se recupera por debajo del umbral', function () {
    $baseWeek = Carbon::create(2024, 6, 3, 11);
    Carbon::setTestNow($baseWeek);

    [$user, $supervisor] = createSupervisorCarteraContext();
    $promotor = createPromotorParaSupervisorCartera($supervisor, [
        'nombre' => 'Sara',
        'apellido_p' => 'Nuñez',
    ]);
    $cliente = createClienteParaSupervisorCartera($promotor, 'CURPFAIL0002', 'Marcela');

    $credito = Credito::create([
        'cliente_id' => $cliente->id,
        'monto_total' => 4000,
        'estado' => 'activo',
        'interes' => 1.3,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $baseWeek->copy()->subWeeks(1),
        'fecha_final' => $baseWeek->copy()->addWeeks(12),
    ]);

    crearPagoProyectadoConPago($credito, $baseWeek->copy()->addDays(2), 1, 1000, 920);
    crearPagoProyectadoConPago($credito, $baseWeek->copy()->addWeek()->addDays(2), 2, 1000, 920);
    crearPagoProyectadoConPago($credito, $baseWeek->copy()->addWeeks(2)->addDays(2), 3, 1000, 1000);

    $response = null;
    for ($i = 0; $i < 3; $i++) {
        $currentWeek = $baseWeek->copy()->addWeeks($i)->addDays(1);
        Carbon::setTestNow($currentWeek);
        $response = $this->actingAs($user)->get(route('mobile.supervisor.cartera'));
        $response->assertOk();
    }

    $response
        ->assertDontSeeText('Falla de 8.00% por 3 semanas consecutivas.')
        ->assertDontSeeText('acumula una falla del 8.00% durante 3 semanas consecutivas.');

    $this->assertDatabaseHas('promotor_failure_streaks', [
        'promotor_id' => $promotor->id,
        'streak_count' => 0,
        'alert_active' => false,
    ]);

    Carbon::setTestNow();
});

/**
 * @return array{0: User, 1: Supervisor}
 */
function createSupervisorCarteraContext(array $overrides = []): array
{
    $ejecutivoUser = User::factory()->create(['rol' => 'ejecutivo']);
    $ejecutivoUser->assignRole('ejecutivo');

    $ejecutivo = Ejecutivo::create([
        'user_id' => $ejecutivoUser->id,
        'nombre' => 'Ezequiel',
        'apellido_p' => 'Ramirez',
        'apellido_m' => 'Nava',
    ]);

    $supervisorUser = User::factory()->create(['rol' => 'supervisor']);
    $supervisorUser->assignRole('supervisor');

    $supervisor = Supervisor::create(array_merge([
        'user_id' => $supervisorUser->id,
        'ejecutivo_id' => $ejecutivo->id,
        'nombre' => 'Sandra',
        'apellido_p' => 'Perez',
        'apellido_m' => 'Lopez',
    ], $overrides));

    return [$supervisorUser, $supervisor];
}

function createPromotorParaSupervisorCartera(Supervisor $supervisor, array $overrides = []): Promotor
{
    $promotorUser = User::factory()->create(['rol' => 'promotor']);
    $promotorUser->assignRole('promotor');

    return Promotor::create(array_merge([
        'user_id' => $promotorUser->id,
        'supervisor_id' => $supervisor->id,
        'nombre' => 'Laura',
        'apellido_p' => 'Gomez',
        'apellido_m' => 'Diaz',
        'venta_maxima' => 5000,
        'venta_proyectada_objetivo' => 20000,
        'colonia' => 'Centro',
        'bono' => 0,
        'dia_de_pago' => 'Lunes',
        'hora_de_pago' => '09:30',
        'creado_en' => now(),
        'actualizado_en' => now(),
    ], $overrides));
}

function createClienteParaSupervisorCartera(Promotor $promotor, string $curp, string $nombre): Cliente
{
    return Cliente::create([
        'promotor_id' => $promotor->id,
        'CURP' => $curp,
        'nombre' => $nombre,
        'apellido_p' => 'Prueba',
        'apellido_m' => 'Demo',
        'fecha_nacimiento' => '1990-01-01',
        'tiene_credito_activo' => true,
        'cliente_estado' => 'activo',
        'monto_maximo' => 15000,
        'creado_en' => now(),
        'actualizado_en' => now(),
        'activo' => true,
    ]);
}

function crearPagoProyectadoConPago(Credito $credito, Carbon $fechaLimite, int $semana, float $montoProyectado, float $montoPagado): PagoProyectado
{
    $pago = PagoProyectado::create([
        'credito_id' => $credito->id,
        'semana' => $semana,
        'monto_proyectado' => $montoProyectado,
        'fecha_limite' => $fechaLimite->toDateString(),
        'estado' => 'pendiente',
    ]);

    $pagoReal = PagoReal::create([
        'pago_proyectado_id' => $pago->id,
        'tipo' => 'pago_completo',
        'fecha_pago' => $fechaLimite->toDateString(),
        'comentario' => null,
    ]);

    PagoCompleto::create([
        'pago_real_id' => $pagoReal->id,
        'monto_completo' => $montoPagado,
    ]);

    return $pago;
}
