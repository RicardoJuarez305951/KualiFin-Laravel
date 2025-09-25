<?php

use App\Models\Ejecutivo;
use App\Models\Supervisor;
use App\Models\User;
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

    Role::firstOrCreate(['name' => 'ejecutivo', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
});

it('permite al ejecutivo acceder a las vistas de cartera y horarios de sus supervisores', function () {
    [$user, $supervisor] = createEjecutivoDetalleContext();

    $routes = [
        ['mobile.ejecutivo.cartera_activa', 'Cartera Activa'],
        ['mobile.ejecutivo.cartera_falla', 'Cartera Falla'],
        ['mobile.ejecutivo.cartera_vencida', 'Cartera Vencida'],
        ['mobile.ejecutivo.cartera_inactiva', 'Cartera Inactiva'],
        ['mobile.ejecutivo.horarios', 'Definir Horarios'],
    ];

    foreach ($routes as [$route, $text]) {
        $this->actingAs($user)
            ->get(route($route, ['supervisor' => $supervisor->id]))
            ->assertOk()
            ->assertSeeText($text);
    }
});

it('restringe el acceso a las vistas de cartera cuando el supervisor no pertenece al ejecutivo', function () {
    [$user, $supervisor] = createEjecutivoDetalleContext();

    $otroSupervisor = createSupervisorForEjecutivo();

    expect($otroSupervisor->id)->not()->toBe($supervisor->id);

    $this->actingAs($user)
        ->get(route('mobile.ejecutivo.cartera_activa', ['supervisor' => $otroSupervisor->id]))
        ->assertForbidden();
});

/**
 * @return array{0: User, 1: Supervisor}
 */
function createEjecutivoDetalleContext(): array
{
    $user = User::factory()->create(['rol' => 'ejecutivo']);
    $user->assignRole('ejecutivo');

    $ejecutivo = Ejecutivo::create([
        'user_id' => $user->id,
        'nombre' => 'Ernesto',
        'apellido_p' => 'Pérez',
        'apellido_m' => 'Salas',
    ]);

    $supervisor = createSupervisorForEjecutivo($ejecutivo);

    return [$user, $supervisor];
}

function createSupervisorForEjecutivo(?Ejecutivo $ejecutivo = null): Supervisor
{
    if (!$ejecutivo) {
        $ejecutivoUser = User::factory()->create(['rol' => 'ejecutivo']);
        $ejecutivoUser->assignRole('ejecutivo');

        $ejecutivo = Ejecutivo::create([
            'user_id' => $ejecutivoUser->id,
            'nombre' => 'Edgar',
            'apellido_p' => 'Valdez',
            'apellido_m' => 'Morales',
        ]);
    }

    $supervisorUser = User::factory()->create(['rol' => 'supervisor']);
    $supervisorUser->assignRole('supervisor');

    return Supervisor::create([
        'user_id' => $supervisorUser->id,
        'ejecutivo_id' => $ejecutivo->id,
        'nombre' => 'Silvia',
        'apellido_p' => 'García',
        'apellido_m' => 'Loera',
    ]);
}
