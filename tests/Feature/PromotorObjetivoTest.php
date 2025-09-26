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

beforeEach(function () {
    config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->withoutVite();
});

it('muestra las métricas del objetivo con avance parcial', function () {
    Carbon::setTestNow(Carbon::create(2024, 6, 20, 12));

    [$user, $promotor] = createPromotorContext([
        'venta_maxima' => 5000,
        'venta_proyectada_objetivo' => 10000,
    ]);

    $clienteSemanaActual = createClienteParaPromotor($promotor, 'CURPTEST0000000001', 'Ana');
    $inicioSemanaActual = now()->copy()->startOfWeek();

    Credito::create([
        'cliente_id' => $clienteSemanaActual->id,
        'monto_total' => 3000,
        'estado' => 'desembolsado',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $inicioSemanaActual->copy()->addDays(1),
        'fecha_final' => $inicioSemanaActual->copy()->addWeeks(14),
    ]);

    $clienteSemanaAnterior = createClienteParaPromotor($promotor, 'CURPTEST0000000002', 'Bruno');
    $inicioSemanaAnterior = now()->copy()->subWeek()->startOfWeek();

    Credito::create([
        'cliente_id' => $clienteSemanaAnterior->id,
        'monto_total' => 2000,
        'estado' => 'desembolsado',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $inicioSemanaAnterior->copy()->addDays(2),
        'fecha_final' => $inicioSemanaAnterior->copy()->addWeeks(14),
    ]);

    $response = $this->actingAs($user)->get(route('mobile.promotor.objetivo'));

    $response
        ->assertOk()
        ->assertSee('$5,000.00', false)
        ->assertSee('$3,000.00', false)
        ->assertSee('$10,000.00', false)
        ->assertSee('$2,000.00', false)
        ->assertSeeText('Vas por buen camino, mantén el ritmo.')
        ->assertSeeText('60.0%');

    Carbon::setTestNow();
});

it('muestra un mensaje motivacional cuando se supera el objetivo semanal', function () {
    Carbon::setTestNow(Carbon::create(2024, 6, 20, 12));

    [$user, $promotor] = createPromotorContext([
        'venta_maxima' => 4000,
        'venta_proyectada_objetivo' => 9000,
    ]);

    $cliente = createClienteParaPromotor($promotor, 'CURPTEST0000000003', 'Carla');
    $inicioSemana = now()->copy()->startOfWeek();

    Credito::create([
        'cliente_id' => $cliente->id,
        'monto_total' => 5000,
        'estado' => 'desembolsado',
        'interes' => 1.5,
        'periodicidad' => 'Semanal',
        'fecha_inicio' => $inicioSemana->copy()->addDay(),
        'fecha_final' => $inicioSemana->copy()->addWeeks(14),
    ]);

    $response = $this->actingAs($user)->get(route('mobile.promotor.objetivo'));

    $response
        ->assertOk()
        ->assertSee('$4,000.00', false)
        ->assertSee('$5,000.00', false)
        ->assertSeeText('¡Impresionante! Superaste tu objetivo semanal, sigue así.')
        ->assertSeeText('125.0%');

    Carbon::setTestNow();
});

/**
 * @return array{0: User, 1: Promotor}
 */
function createPromotorContext(array $promotorOverrides = []): array
{
    $role = Role::firstOrCreate(
        ['name' => 'promotor', 'guard_name' => 'web']
    );

    $ejecutivoUser = User::factory()->create(['rol' => 'ejecutivo']);
    $ejecutivo = Ejecutivo::create([
        'user_id' => $ejecutivoUser->id,
        'nombre' => 'Ezequiel',
        'apellido_p' => 'Ramirez',
        'apellido_m' => 'Nava',
    ]);

    $supervisorUser = User::factory()->create(['rol' => 'supervisor']);
    $supervisor = Supervisor::create([
        'user_id' => $supervisorUser->id,
        'ejecutivo_id' => $ejecutivo->id,
        'nombre' => 'Sandra',
        'apellido_p' => 'Perez',
        'apellido_m' => 'Lopez',
    ]);

    $promotorUser = User::factory()->create(['rol' => 'promotor']);
    $promotorUser->assignRole($role);

    $promotor = Promotor::create(array_merge([
        'user_id' => $promotorUser->id,
        'supervisor_id' => $supervisor->id,
        'nombre' => 'Laura',
        'apellido_p' => 'Gomez',
        'apellido_m' => 'Diaz',
        'venta_maxima' => 5000,
        'colonia' => 'Centro',
        'venta_proyectada_objetivo' => 10000,
        'bono' => 1000,
        'dia_de_pago' => 'Lunes',
        'hora_de_pago' => '09:30',
    ], $promotorOverrides));

    return [$promotorUser, $promotor];
}

function createClienteParaPromotor(Promotor $promotor, string $curp, string $nombre): Cliente
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

