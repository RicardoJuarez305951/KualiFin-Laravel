<?php

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Support\Str;
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

    foreach (['ejecutivo', 'supervisor', 'promotor'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }
});

it('permite a un ejecutivo autenticado ver el reporte de desembolso', function () {
    [$ejecutivoUser, $creditoAutorizado] = createDesembolsoContext();

    $response = $this->actingAs($ejecutivoUser)
        ->withSession([])
        ->get(route('mobile.ejecutivo.desembolso'));

    $response->assertOk();
    $response->assertViewIs('mobile.ejecutivo.venta.desembolso');
    $response->assertSee("#{$creditoAutorizado->id}", escape: false);
    $response->assertSee($creditoAutorizado->cliente->nombre_completo, escape: false);
    $response->assertSee(optional($creditoAutorizado->cliente->promotor)->nombre, escape: false);
    $response->assertSee(number_format($creditoAutorizado->monto_total, 2), escape: false);
});

it('no muestra créditos autorizados que pertenecen a otros ejecutivos', function () {
    [$ejecutivoUser, $creditoAutorizado, $creditoAjeno] = createDesembolsoContext(withForeignCredit: true);

    $response = $this->actingAs($ejecutivoUser)
        ->withSession([])
        ->get(route('mobile.ejecutivo.desembolso'));

    $response->assertOk();
    $response->assertSee($creditoAutorizado->cliente->nombre_completo, escape: false);
    $response->assertDontSee("#{$creditoAjeno->id}", escape: false);
    $response->assertDontSee($creditoAjeno->cliente->nombre_completo, escape: false);
});

it('permite marcar un crédito como desembolsado', function () {
    [$ejecutivoUser, $creditoAutorizado] = createDesembolsoContext(withForeignCredit: false);

    $response = $this->actingAs($ejecutivoUser)
        ->withSession([])
        ->post(route('mobile.ejecutivo.desembolso.update', $creditoAutorizado));

    $response->assertRedirect(route('mobile.ejecutivo.desembolso'));
    expect($creditoAutorizado->refresh()->estado)->toBe('Desembolsado');
});

/**
 * @return array<int, mixed>
 */
function createDesembolsoContext(bool $withForeignCredit = true): array
{
    $ejecutivoUser = User::factory()->create(['rol' => 'ejecutivo']);
    $ejecutivoUser->assignRole('ejecutivo');

    $ejecutivo = Ejecutivo::create([
        'user_id' => $ejecutivoUser->id,
        'nombre' => 'Laura',
        'apellido_p' => 'Ramírez',
        'apellido_m' => 'Soto',
    ]);

    $supervisorUser = User::factory()->create(['rol' => 'supervisor']);
    $supervisorUser->assignRole('supervisor');

    $supervisor = Supervisor::create([
        'user_id' => $supervisorUser->id,
        'ejecutivo_id' => $ejecutivo->id,
        'nombre' => 'Marcos',
        'apellido_p' => 'Ortega',
        'apellido_m' => 'Luna',
    ]);

    $promotorUser = User::factory()->create(['rol' => 'promotor']);
    $promotorUser->assignRole('promotor');

    $promotor = Promotor::create([
        'user_id' => $promotorUser->id,
        'supervisor_id' => $supervisor->id,
        'nombre' => 'Patricia',
        'apellido_p' => 'Nava',
        'apellido_m' => 'Campos',
        'venta_maxima' => 5000,
        'colonia' => 'Centro',
        'venta_proyectada_objetivo' => 3000,
        'bono' => 0,
        'dia_de_pago' => 'Lunes',
    ]);

    $cliente = Cliente::create([
        'promotor_id' => $promotor->id,
        'CURP' => Str::upper(Str::random(18)),
        'nombre' => 'Diego',
        'apellido_p' => 'Martínez',
        'apellido_m' => 'Juárez',
        'fecha_nacimiento' => now()->subYears(30)->toDateString(),
        'tiene_credito_activo' => false,
        'cartera_estado' => 'activo',
        'monto_maximo' => 12000,
        'creado_en' => now(),
        'actualizado_en' => now(),
        'activo' => true,
    ]);

    $creditoAutorizado = Credito::create([
        'cliente_id' => $cliente->id,
        'monto_total' => 12500.50,
        'estado' => 'Autorizado',
        'interes' => 1.8,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => now()->subWeek()->toDateString(),
        'fecha_final' => now()->addMonths(6)->toDateString(),
    ]);

    DatoContacto::create([
        'credito_id' => $creditoAutorizado->id,
        'calle' => 'Calle Principal',
        'numero_ext' => '123',
        'numero_int' => '1',
        'monto_mensual' => 4500,
        'colonia' => 'Centro',
        'municipio' => 'Ciudad de México',
        'estado' => 'CDMX',
        'cp' => '01000',
        'tiempo_en_residencia' => '3 años',
        'tel_fijo' => null,
        'tel_cel' => '5512345678',
        'tipo_de_vivienda' => 'Propia',
        'creado_en' => now(),
    ]);

    if (!$withForeignCredit) {
        return [$ejecutivoUser, $creditoAutorizado];
    }

    $otroEjecutivoUser = User::factory()->create(['rol' => 'ejecutivo']);
    $otroEjecutivoUser->assignRole('ejecutivo');

    $otroEjecutivo = Ejecutivo::create([
        'user_id' => $otroEjecutivoUser->id,
        'nombre' => 'Sofía',
        'apellido_p' => 'Vargas',
        'apellido_m' => 'Mena',
    ]);

    $otroSupervisorUser = User::factory()->create(['rol' => 'supervisor']);
    $otroSupervisorUser->assignRole('supervisor');

    $otroSupervisor = Supervisor::create([
        'user_id' => $otroSupervisorUser->id,
        'ejecutivo_id' => $otroEjecutivo->id,
        'nombre' => 'Raúl',
        'apellido_p' => 'García',
        'apellido_m' => 'Ibarra',
    ]);

    $otroPromotorUser = User::factory()->create(['rol' => 'promotor']);
    $otroPromotorUser->assignRole('promotor');

    $otroPromotor = Promotor::create([
        'user_id' => $otroPromotorUser->id,
        'supervisor_id' => $otroSupervisor->id,
        'nombre' => 'Julia',
        'apellido_p' => 'Salas',
        'apellido_m' => 'Cuevas',
        'venta_maxima' => 4000,
        'colonia' => 'Norte',
        'venta_proyectada_objetivo' => 2500,
        'bono' => 0,
        'dia_de_pago' => 'Viernes',
    ]);

    $otroCliente = Cliente::create([
        'promotor_id' => $otroPromotor->id,
        'CURP' => Str::upper(Str::random(18)),
        'nombre' => 'Ismael',
        'apellido_p' => 'Rojas',
        'apellido_m' => 'Silva',
        'fecha_nacimiento' => now()->subYears(28)->toDateString(),
        'tiene_credito_activo' => false,
        'cartera_estado' => 'activo',
        'monto_maximo' => 10000,
        'creado_en' => now(),
        'actualizado_en' => now(),
        'activo' => true,
    ]);

    $creditoAjeno = Credito::create([
        'cliente_id' => $otroCliente->id,
        'monto_total' => 9800,
        'estado' => 'Autorizado',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => now()->subWeek()->toDateString(),
        'fecha_final' => now()->addMonths(4)->toDateString(),
    ]);

    DatoContacto::create([
        'credito_id' => $creditoAjeno->id,
        'calle' => 'Av. Norte',
        'numero_ext' => '45',
        'numero_int' => null,
        'monto_mensual' => 3200,
        'colonia' => 'Norte',
        'municipio' => 'Ciudad de México',
        'estado' => 'CDMX',
        'cp' => '02000',
        'tiempo_en_residencia' => '1 año',
        'tel_fijo' => null,
        'tel_cel' => '5598765432',
        'tipo_de_vivienda' => 'Rentada',
        'creado_en' => now(),
    ]);

    return [$ejecutivoUser, $creditoAutorizado, $creditoAjeno];
}
