<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ejecutivo;
use App\Models\Supervisor;
use App\Models\Promotor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            EjecutivoSeeder::class,
            SupervisorSeeder::class,
            PromotorSeeder::class,
            EjercicioSeeder::class,
            ClienteSeeder::class,
            CreditoSeeder::class,
            PagoProyectadoSeeder::class,
            PagoRealSeeder::class,
            PagoDiferidoSeeder::class,
            PagoCompletoSeeder::class,
            PagoAnticipoSeeder::class,
            OcupacionSeeder::class,
            IngresoAdicionalSeeder::class,
            DatoContactoSeeder::class,
            InformacionFamiliarSeeder::class,
            AvalSeeder::class,
            DocumentoClienteSeeder::class,
            DocumentoAvalSeeder::class,
            GarantiaSeeder::class,
            ContratoSeeder::class,
            InversionSeeder::class,
            ComisionSeeder::class,
            AdministrativoSeeder::class,
            TipoDocumentoSeeder::class,
            DocumentoSeeder::class,
            UserSeeder::class,
        ]);

        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '1234567890',
            'rol' => 'superadmin',
        ]);
        $user->assignRole('superadmin');

        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '1234567890',
            'rol' => 'administrador',
        ]);
        $user->assignRole('administrador');

        $user = User::factory()->create([
            'name' => 'Ejecutivo User',
            'email' => 'ejecutivo@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '0987654321',
            'rol' => 'ejecutivo',
        ]);
        $user->assignRole('ejecutivo');
        $ejecutivo = Ejecutivo::create([
            'user_id' => $user->id,
            'nombre' => 'Ejecutivo',
            'apellido_p' => 'User',
            'apellido_m' => 'Demo',
        ]);

        $user = User::factory()->create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '0987654321',
            'rol' => 'supervisor',
        ]);
        $user->assignRole('supervisor');
        $supervisor = Supervisor::create([
            'user_id' => $user->id,
            'ejecutivo_id' => $ejecutivo->id,
            'nombre' => 'Supervisor',
            'apellido_p' => 'User',
            'apellido_m' => 'Demo',
        ]);

        $user = User::factory()->create([
            'name' => 'Promotor User',
            'email' => 'promotor@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '0987654321',
            'rol' => 'promotor',
        ]);
        $user->assignRole('promotor');
        Promotor::create([
            'user_id' => $user->id,
            'supervisor_id' => $supervisor->id,
            'nombre' => 'Promotor',
            'apellido_p' => 'User',
            'apellido_m' => 'Demo',
            'venta_maxima' => 10000,
            'colonia' => 'Centro',
            'venta_proyectada_objetivo' => 5000,
            'bono' => 500,
        ]);

        // Test
        $this->call([
            KanbanSeeder::class,
        ]);
    }
}
