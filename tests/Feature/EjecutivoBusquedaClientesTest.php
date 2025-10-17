<?php

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Database\Seeders\BusquedaClientesSeeder;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Schema;

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

it('habilita detalles sin seleccionar supervisor para todos los clientes de sus supervisores', function () {
    $this->seed(BusquedaClientesSeeder::class);

    $ejecutivoUser = User::firstWhere('email', 'ejecutivo.busqueda@example.com');
    expect($ejecutivoUser)->not()->toBeNull();

    $ejecutivo = Ejecutivo::firstWhere('user_id', $ejecutivoUser->id);
    expect($ejecutivo)->not()->toBeNull();

    $nuevoSupervisorUser = User::factory()->create([
        'rol' => 'supervisor',
        'remember_token' => false,
    ]);
    $nuevoSupervisorUser->assignRole('supervisor');

    $nuevoSupervisor = Supervisor::create([
        'user_id' => $nuevoSupervisorUser->id,
        'ejecutivo_id' => $ejecutivo->id,
        'nombre' => 'Silvia',
        'apellido_p' => 'Campos',
        'apellido_m' => 'Luna',
    ]);

    $nuevoPromotorUser = User::factory()->create([
        'rol' => 'promotor',
        'remember_token' => false,
    ]);
    $nuevoPromotorUser->assignRole('promotor');

    $nuevoPromotorData = [
        'user_id' => $nuevoPromotorUser->id,
        'supervisor_id' => $nuevoSupervisor->id,
        'nombre' => 'Paula',
        'apellido_p' => 'Jimenez',
        'apellido_m' => 'Cruz',
        'venta_maxima' => 11000,
        'colonia' => 'Centro',
        'venta_proyectada_objetivo' => 40000,
        'bono' => 0,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ];

    if (Schema::hasColumn('promotores', 'dias_de_pago')) {
        $nuevoPromotorData['dias_de_pago'] = 'miercoles';
    } else {
        $nuevoPromotorData['dia_de_pago'] = 'miercoles';
        $nuevoPromotorData['hora_de_pago'] = '11:30';
    }

    $nuevoPromotor = Promotor::create($nuevoPromotorData);

    $nuevoCliente = Cliente::create([
        'promotor_id' => $nuevoPromotor->id,
        'CURP' => 'BUSQCLIENTE003',
        'nombre' => 'Carla',
        'apellido_p' => 'Jimenez',
        'apellido_m' => 'Lopez',
        'fecha_nacimiento' => '1992-03-10',
        'tiene_credito_activo' => true,
        'cliente_estado' => 'activo',
        'monto_maximo' => 18000,
        'creado_en' => now(),
        'actualizado_en' => now(),
        'activo' => true,
    ]);

    $nuevoCredito = Credito::create([
        'cliente_id' => $nuevoCliente->id,
        'monto_total' => 18000,
        'estado' => 'activo',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => now()->subWeeks(1),
        'fecha_final' => now()->addMonths(6),
    ]);

    DatoContacto::create([
        'credito_id' => $nuevoCredito->id,
        'calle' => 'Calle Tercera',
        'numero_ext' => '10',
        'numero_int' => '1',
        'monto_mensual' => 3800,
        'colonia' => 'Centro',
        'municipio' => 'Ciudad Busqueda',
        'estado' => 'CDMX',
        'cp' => '03000',
        'tiempo_en_residencia' => '2 años',
        'tel_fijo' => null,
        'tel_cel' => '5511122233',
        'tipo_de_vivienda' => 'Rentada',
        'creado_en' => now(),
    ]);

    $response = $this->actingAs($ejecutivoUser)
        ->withSession([])
        ->get(route('mobile.ejecutivo.busqueda', ['q' => 'Car']));

    $response
        ->assertOk()
        ->assertSee('Carolina Miranda', escape: false)
        ->assertSee('Carla Jimenez', escape: false);

    $content = $response->getContent();

    expect(substr_count($content, 'bg-blue-600 text-white hover:bg-blue-700'))->toBe(2);
    expect(substr_count($content, 'cursor-not-allowed'))->toBe(1);
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
