<?php

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
    $this->withoutVite();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Role::firstOrCreate(['name' => 'ejecutivo', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'promotor', 'guard_name' => 'web']);
});

it('incluye créditos desembolsados dentro de la cartera activa del ejecutivo', function () {
    $user = User::factory()->create(['rol' => 'ejecutivo']);
    $user->assignRole('ejecutivo');

    $ejecutivo = Ejecutivo::create([
        'user_id' => $user->id,
        'nombre' => 'Daniela',
        'apellido_p' => 'Soto',
        'apellido_m' => 'Reyes',
    ]);

    $supervisorUser = User::factory()->create(['rol' => 'supervisor']);
    $supervisorUser->assignRole('supervisor');

    $supervisor = Supervisor::create([
        'user_id' => $supervisorUser->id,
        'ejecutivo_id' => $ejecutivo->id,
        'nombre' => 'Marcos',
        'apellido_p' => 'Pineda',
        'apellido_m' => 'Lara',
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
        'nombre' => 'Cliente Demo',
        'apellido_p' => 'Prueba',
        'apellido_m' => 'Activa',
        'fecha_nacimiento' => now()->subYears(30)->toDateString(),
        'tiene_credito_activo' => true,
        'cartera_estado' => 'activo',
        'monto_maximo' => 5000,
        'creado_en' => now(),
        'actualizado_en' => now(),
        'activo' => true,
    ]);

    $monto = 1500.00;

    Credito::create([
        'cliente_id' => $cliente->id,
        'monto_total' => $monto,
        'estado' => 'desembolsado',
        'interes' => 10,
        'periodicidad' => 'semanal',
        'fecha_inicio' => now()->subMonth()->toDateString(),
        'fecha_final' => now()->addMonths(5)->toDateString(),
    ]);

    $response = $this->actingAs($user)->get(route('mobile.ejecutivo.cartera'));

    $response->assertOk();
    $response->assertViewHas('cartera_activa', round($monto, 2));
    $response->assertSee('$1,500.00');
});
