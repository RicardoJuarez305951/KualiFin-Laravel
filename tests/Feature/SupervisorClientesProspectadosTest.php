<?php

use App\Models\Ejecutivo;
use App\Models\Supervisor;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
    $this->withoutVite();
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('muestra los botones de recargar y regreso en clientes prospectados', function () {
    [$user, $supervisor] = createSupervisorClienteProspectadoContext();

    $response = $this->actingAs($user)->get(route('mobile.supervisor.clientes_prospectados'));

    $response
        ->assertOk()
        ->assertSeeText('Recargar')
        ->assertSee(route('mobile.supervisor.clientes_prospectados', ['supervisor' => $supervisor->id]), false)
        ->assertSeeText('Regresar a Venta')
        ->assertSee(route('mobile.supervisor.venta', ['supervisor' => $supervisor->id]), false);
});

/**
 * @return array{0: User, 1: Supervisor}
 */
function createSupervisorClienteProspectadoContext(): array
{
    $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
    $ejecutivoRole = Role::firstOrCreate(['name' => 'ejecutivo', 'guard_name' => 'web']);

    $ejecutivoUser = User::factory()->create(['rol' => 'ejecutivo']);
    $ejecutivoUser->assignRole($ejecutivoRole);

    $ejecutivo = Ejecutivo::create([
        'user_id' => $ejecutivoUser->id,
        'nombre' => 'Elena',
        'apellido_p' => 'Ramírez',
        'apellido_m' => 'López',
    ]);

    $user = User::factory()->create(['rol' => 'supervisor']);
    $user->assignRole($supervisorRole);

    $supervisor = Supervisor::create([
        'user_id' => $user->id,
        'ejecutivo_id' => $ejecutivo->id,
        'nombre' => 'Sonia',
        'apellido_p' => 'García',
        'apellido_m' => 'Nava',
    ]);

    return [$user, $supervisor];
}
