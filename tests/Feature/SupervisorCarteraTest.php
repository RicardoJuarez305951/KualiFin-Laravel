<?php

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejecutivo;
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
        'cartera_estado' => 'activo',
        'monto_maximo' => 15000,
        'creado_en' => now(),
        'actualizado_en' => now(),
        'activo' => true,
    ]);
}
