<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class BusquedaClientesSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureRoles();

        $ejecutivoUser = User::updateOrCreate(
            ['email' => 'ejecutivo.busqueda@example.com'],
            [
                'name' => 'Eva Ejecutiva',
                'telefono' => '5551000000',
                'password' => Hash::make('password'),
                'rol' => 'ejecutivo',
                'creado_en' => now(),
            ]
        );
        $ejecutivoUser->assignRole('ejecutivo');

        $administrativoUser = User::updateOrCreate(
            ['email' => 'admin.busqueda@example.com'],
            [
                'name' => 'Andrea Administrativa',
                'telefono' => '5552000000',
                'password' => Hash::make('password'),
                'rol' => 'administrativo',
                'creado_en' => now(),
            ]
        );
        $administrativoUser->assignRole('administrativo');

        $otroEjecutivoUser = User::updateOrCreate(
            ['email' => 'otro.ejecutivo.busqueda@example.com'],
            [
                'name' => 'Oscar Ejecutivo',
                'telefono' => '5553000000',
                'password' => Hash::make('password'),
                'rol' => 'ejecutivo',
                'creado_en' => now(),
            ]
        );
        $otroEjecutivoUser->assignRole('ejecutivo');

        $ejecutivo = Ejecutivo::updateOrCreate(
            ['user_id' => $ejecutivoUser->id],
            [
                'nombre' => 'Eva',
                'apellido_p' => 'Gonzalez',
                'apellido_m' => 'Lopez',
            ]
        );

        $otroEjecutivo = Ejecutivo::updateOrCreate(
            ['user_id' => $otroEjecutivoUser->id],
            [
                'nombre' => 'Oscar',
                'apellido_p' => 'Diaz',
                'apellido_m' => 'Ramirez',
            ]
        );

        $supervisorUnoUser = User::updateOrCreate(
            ['email' => 'supervisor.busqueda@example.com'],
            [
                'name' => 'Sonia Supervisor',
                'telefono' => '5554000000',
                'password' => Hash::make('password'),
                'rol' => 'supervisor',
                'creado_en' => now(),
            ]
        );
        $supervisorUnoUser->assignRole('supervisor');

        $supervisorDosUser = User::updateOrCreate(
            ['email' => 'otro.supervisor.busqueda@example.com'],
            [
                'name' => 'Samuel Supervisor',
                'telefono' => '5555000000',
                'password' => Hash::make('password'),
                'rol' => 'supervisor',
                'creado_en' => now(),
            ]
        );
        $supervisorDosUser->assignRole('supervisor');

        $supervisorUno = Supervisor::updateOrCreate(
            ['user_id' => $supervisorUnoUser->id],
            [
                'ejecutivo_id' => $ejecutivo->id,
                'nombre' => 'Sonia',
                'apellido_p' => 'Navarro',
                'apellido_m' => 'Rios',
            ]
        );

        $supervisorDos = Supervisor::updateOrCreate(
            ['user_id' => $supervisorDosUser->id],
            [
                'ejecutivo_id' => $otroEjecutivo->id,
                'nombre' => 'Samuel',
                'apellido_p' => 'Torres',
                'apellido_m' => 'Mena',
            ]
        );

        $promotorUnoUser = User::updateOrCreate(
            ['email' => 'promotor.busqueda@example.com'],
            [
                'name' => 'Pamela Promotora',
                'telefono' => '5556000000',
                'password' => Hash::make('password'),
                'rol' => 'promotor',
                'creado_en' => now(),
            ]
        );
        $promotorUnoUser->assignRole('promotor');

        $promotorDosUser = User::updateOrCreate(
            ['email' => 'otro.promotor.busqueda@example.com'],
            [
                'name' => 'Pablo Promotor',
                'telefono' => '5557000000',
                'password' => Hash::make('password'),
                'rol' => 'promotor',
                'creado_en' => now(),
            ]
        );
        $promotorDosUser->assignRole('promotor');

        $promotorUno = Promotor::updateOrCreate(
            ['user_id' => $promotorUnoUser->id],
            array_merge([
                'supervisor_id' => $supervisorUno->id,
                'nombre' => 'Pamela',
                'apellido_p' => 'Jimenez',
                'apellido_m' => 'Flores',
                'venta_maxima' => 15000,
                'colonia' => 'Centro',
                'venta_proyectada_objetivo' => 60000,
                'bono' => 0,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ], $this->promotorScheduleData('lunes, jueves'))
        );

        $promotorDos = Promotor::updateOrCreate(
            ['user_id' => $promotorDosUser->id],
            array_merge([
                'supervisor_id' => $supervisorDos->id,
                'nombre' => 'Pablo',
                'apellido_p' => 'Ruiz',
                'apellido_m' => 'Santos',
                'venta_maxima' => 12000,
                'colonia' => 'Norte',
                'venta_proyectada_objetivo' => 50000,
                'bono' => 0,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ], $this->promotorScheduleData('martes, viernes'))
        );

        $clienteUno = Cliente::updateOrCreate(
            ['CURP' => 'BUSQCLIENTE001'],
            [
                'promotor_id' => $promotorUno->id,
                'nombre' => 'Carolina',
                'apellido_p' => 'Miranda',
                'apellido_m' => 'Soto',
                'fecha_nacimiento' => '1990-05-14',
                'tiene_credito_activo' => true,
                'cartera_estado' => 'activo',
                'monto_maximo' => 25000,
                'horario_de_pago' => '09:00',
                'creado_en' => now(),
                'actualizado_en' => now(),
                'activo' => true,
            ]
        );

        $clienteDos = Cliente::updateOrCreate(
            ['CURP' => 'BUSQCLIENTE002'],
            [
                'promotor_id' => $promotorDos->id,
                'nombre' => 'Carlos',
                'apellido_p' => 'Nava',
                'apellido_m' => 'Reyes',
                'fecha_nacimiento' => '1988-09-20',
                'tiene_credito_activo' => true,
                'cartera_estado' => 'activo',
                'monto_maximo' => 20000,
                'horario_de_pago' => '10:00',
                'creado_en' => now(),
                'actualizado_en' => now(),
                'activo' => true,
            ]
        );

        $creditoUno = Credito::updateOrCreate(
            ['cliente_id' => $clienteUno->id],
            [
                'monto_total' => 15000,
                'estado' => 'activo',
                'interes' => 1.5,
                'periodicidad' => 'Semanal',
                'fecha_inicio' => now()->subWeeks(2),
                'fecha_final' => now()->addMonths(6),
            ]
        );

        $creditoDos = Credito::updateOrCreate(
            ['cliente_id' => $clienteDos->id],
            [
                'monto_total' => 12000,
                'estado' => 'activo',
                'interes' => 1.5,
                'periodicidad' => 'Semanal',
                'fecha_inicio' => now()->subWeeks(3),
                'fecha_final' => now()->addMonths(5),
            ]
        );

        DatoContacto::updateOrCreate(
            ['credito_id' => $creditoUno->id],
            [
                'calle' => 'Av. Reforma',
                'numero_ext' => '123',
                'numero_int' => '2A',
                'monto_mensual' => 4000,
                'colonia' => 'Centro',
                'municipio' => 'Ciudad Busqueda',
                'estado' => 'CDMX',
                'cp' => '01000',
                'tiempo_en_residencia' => '5 años',
                'tel_fijo' => '5555555555',
                'tel_cel' => '5512345678',
                'tipo_de_vivienda' => 'Propia',
            ]
        );

        DatoContacto::updateOrCreate(
            ['credito_id' => $creditoDos->id],
            [
                'calle' => 'Calle Segunda',
                'numero_ext' => '45',
                'numero_int' => null,
                'monto_mensual' => 3500,
                'colonia' => 'Industrial',
                'municipio' => 'Ciudad Busqueda',
                'estado' => 'CDMX',
                'cp' => '02000',
                'tiempo_en_residencia' => '3 años',
                'tel_fijo' => null,
                'tel_cel' => '5598765432',
                'tipo_de_vivienda' => 'Rentada',
            ]
        );
    }

    private function ensureRoles(): void
    {
        foreach (['ejecutivo', 'administrativo', 'supervisor', 'promotor'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    private function promotorScheduleData(string $diasPago): array
    {
        if (Schema::hasColumn('promotores', 'dias_de_pago')) {
            return ['dias_de_pago' => $diasPago];
        }

        $dia = collect(explode(',', $diasPago))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->first();

        return [
            'dia_de_pago' => $dia ?: null,
            'hora_de_pago' => null,
        ];
    }
}
