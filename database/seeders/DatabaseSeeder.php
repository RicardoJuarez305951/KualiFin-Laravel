<?php

namespace Database\Seeders;

use App\Models\User;
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
            DocumentoSeeder::class,
            UserSeeder::class,
        ]);

        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '1234567890',
        ]);
        $user->assignRole('superadmin');

        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '1234567890',
        ]);
        $user->assignRole('administrador');

        $user = User::factory()->create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '0987654321',
        ]);
        $user->assignRole('supervisor');

        $user = User::factory()->create([
            'name' => 'Ejecutivo User',
            'email' => 'ejecutivo@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '0987654321',
        ]);
        $user->assignRole('ejecutivo');

        $user = User::factory()->create([
            'name' => 'Promotor User',
            'email' => 'promotor@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '0987654321',
        ]);
        $user->assignRole('promotor');
    }
}
