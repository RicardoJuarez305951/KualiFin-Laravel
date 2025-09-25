<?php

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

it('muestra un cliente de otro supervisor con detalle restringido', function () {
    $this->seed(BusquedaClientesSeeder::class);

    $supervisorUser = User::firstWhere('email', 'supervisor.busqueda@example.com');
    expect($supervisorUser)->not()->toBeNull();

    $response = $this->actingAs($supervisorUser)
        ->withSession([])
        ->get(route('mobile.supervisor.busqueda', ['q' => 'Carlos']));

    $response
        ->assertOk()
        ->assertSee('Carlos Nava', escape: false)
        ->assertSee('Detalles</button>', escape: false)
        ->assertSee('cursor-not-allowed', escape: false)
        ->assertSee('Asignado a otro supervisor', escape: false)
        ->assertDontSee('5598765432')
        ->assertDontSee('Industrial')
        ->assertDontSee('INE del cliente');
});
