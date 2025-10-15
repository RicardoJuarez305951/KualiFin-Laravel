<?php

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejecutivo;
use App\Models\Ejercicio;
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

it('muestra las métricas reales y los enlaces a los objetivos de cada promotor', function () {
    Carbon::setTestNow(Carbon::create(2024, 6, 19, 12));

    [$user, $supervisor] = createSupervisorObjetivoContext();

    $promotorUno = createPromotorParaSupervisorObjetivo($supervisor, [
        'nombre' => 'Ana',
        'apellido_p' => 'Lopez',
        'venta_maxima' => 5000,
        'venta_proyectada_objetivo' => 20000,
    ]);

    $promotorDos = createPromotorParaSupervisorObjetivo($supervisor, [
        'nombre' => 'Beto',
        'apellido_p' => 'Reyes',
        'venta_maxima' => 4000,
        'venta_proyectada_objetivo' => 15000,
    ]);

    Ejercicio::create([
        'supervisor_id' => $supervisor->id,
        'ejecutivo_id' => $supervisor->ejecutivo_id,
        'fecha_inicio' => Carbon::create(2024, 6, 1),
        'fecha_final' => Carbon::create(2024, 6, 30),
        'venta_objetivo' => 35000,
        'dinero_autorizado' => 0,
    ]);

    $weekStart = now()->copy()->startOfWeek();

    $clienteAnaActual = createClienteParaSupervisorObjetivo($promotorUno, 'CURP000000000001', 'Clara');
    Credito::create([
        'cliente_id' => $clienteAnaActual->id,
        'monto_total' => 3000,
        'estado' => 'desembolsado',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $weekStart->copy()->addDays(1),
        'fecha_final' => $weekStart->copy()->addWeeks(12),
    ]);

    $clienteAnaAnterior = createClienteParaSupervisorObjetivo($promotorUno, 'CURP000000000002', 'Dalia');
    Credito::create([
        'cliente_id' => $clienteAnaAnterior->id,
        'monto_total' => 4000,
        'estado' => 'desembolsado',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $weekStart->copy()->subWeek()->addDays(2),
        'fecha_final' => $weekStart->copy()->subWeek()->addWeeks(12),
    ]);

    $clienteBetoActual = createClienteParaSupervisorObjetivo($promotorDos, 'CURP000000000003', 'Elena');
    Credito::create([
        'cliente_id' => $clienteBetoActual->id,
        'monto_total' => 1000,
        'estado' => 'desembolsado',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $weekStart->copy()->addDays(2),
        'fecha_final' => $weekStart->copy()->addWeeks(10),
    ]);

    $clienteBetoAnterior = createClienteParaSupervisorObjetivo($promotorDos, 'CURP000000000004', 'Fernanda');
    Credito::create([
        'cliente_id' => $clienteBetoAnterior->id,
        'monto_total' => 2000,
        'estado' => 'desembolsado',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => Carbon::create(2024, 6, 4),
        'fecha_final' => Carbon::create(2024, 9, 4),
    ]);

    $response = $this->actingAs($user)->get(route('mobile.supervisor.objetivo'));

    $expectedRouteOne = route('mobile.promotor.objetivo', ['supervisor' => $supervisor->id, 'promotor' => $promotorUno->id]);
    $expectedRouteTwo = route('mobile.promotor.objetivo', ['supervisor' => $supervisor->id, 'promotor' => $promotorDos->id]);

    $response
        ->assertOk()
        ->assertSee('Ana Lopez', false)
        ->assertSee('Beto Reyes', false)
        ->assertSee('$9,000.00', false)
        ->assertSee('$4,000.00', false)
        ->assertSee('$5,000.00', false)
        ->assertSee('$35,000.00', false)
        ->assertSee('$10,000.00', false)
        ->assertSee($expectedRouteOne)
        ->assertSee($expectedRouteTwo);

    Carbon::setTestNow();
});

/**
 * @return array{0: User, 1: Supervisor}
 */
function createSupervisorObjetivoContext(array $overrides = []): array
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

function createPromotorParaSupervisorObjetivo(Supervisor $supervisor, array $overrides = []): Promotor
{
    $promotorUser = User::factory()->create(['rol' => 'promotor']);
    $promotorUser->assignRole('promotor');

    return Promotor::create(array_merge([
        'user_id' => $promotorUser->id,
        'supervisor_id' => $supervisor->id,
        'nombre' => 'Laura',
        'apellido_p' => 'Gomez',
        'apellido_m' => 'Diaz',
        'venta_maxima' => 4000,
        'venta_proyectada_objetivo' => 10000,
        'colonia' => 'Centro',
        'bono' => 0,
        'dia_de_pago' => 'Lunes',
        'hora_de_pago' => '09:30',
        'creado_en' => now(),
        'actualizado_en' => now(),
    ], $overrides));
}

function createClienteParaSupervisorObjetivo(Promotor $promotor, string $curp, string $nombre): Cliente
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
