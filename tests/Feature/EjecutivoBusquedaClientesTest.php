<?php

use App\Models\Supervisor;
use App\Models\User;
use Database\Seeders\BusquedaClientesSeeder;
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
});

it('permite que un ejecutivo identifique clientes ajenos en los resultados', function () {
    $this->seed(BusquedaClientesSeeder::class);

    $ejecutivoUser = User::firstWhere('email', 'ejecutivo.busqueda@example.com');
    expect($ejecutivoUser)->not()->toBeNull();

    $response = $this->actingAs($ejecutivoUser)
        ->withSession([])
        ->get(route('mobile.ejecutivo.busqueda', ['q' => 'Car']));

    $response
        ->assertOk()
        ->assertSee('Carolina Miranda', escape: false)
        ->assertSee('Carlos Nava', escape: false)
        ->assertSee('Asignado a otro supervisor', escape: false)
        ->assertSee('cursor-not-allowed', escape: false);
});

it('permite que un administrativo consulte clientes de cualquier supervisor', function () {
    $this->seed(BusquedaClientesSeeder::class);

    $administrativoUser = User::firstWhere('email', 'admin.busqueda@example.com');
    expect($administrativoUser)->not()->toBeNull();

    $otroSupervisor = Supervisor::firstWhere('nombre', 'Samuel');
    expect($otroSupervisor)->not()->toBeNull();

    $response = $this->actingAs($administrativoUser)
        ->withSession([])
        ->get(route('mobile.ejecutivo.busqueda', [
            'q' => 'Carlos',
            'supervisor' => $otroSupervisor->id,
        ]));

    $response
        ->assertOk()
        ->assertSee('Carlos Nava', escape: false)
        ->assertDontSee('No hay promotores asociados al supervisor seleccionado.');
});
