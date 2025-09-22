<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Ejecutivo;
use App\Models\Supervisor;
use App\Models\Promotor;
use Database\Seeders\Concerns\LatinoNameGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

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

        $faker = fake();

        for ($i = 1; $i <= 10; $i++) {
            [$nombre, $apellidoPaterno, $apellidoMaterno] = LatinoNameGenerator::person();

            $supervisorUser = User::factory()->create([
                'name' => sprintf('%s %s %s', $nombre, $apellidoPaterno, $apellidoMaterno),
                'email' => sprintf('ejecutivo.supervisor%02d@example.com', $i),
                'password' => Hash::make('12345'),
                'telefono' => $faker->unique()->numerify('55########'),
                'rol' => 'supervisor',
            ]);
            $supervisorUser->assignRole('supervisor');

            Supervisor::create([
                'user_id' => $supervisorUser->id,
                'ejecutivo_id' => $ejecutivo->id,
                'nombre' => $nombre,
                'apellido_p' => $apellidoPaterno,
                'apellido_m' => $apellidoMaterno,
            ]);
        }

        $faker->unique(true);

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

        $faker = fake();

        for ($i = 1; $i <= 10; $i++) {
            [$nombre, $apellidoPaterno, $apellidoMaterno] = LatinoNameGenerator::person();

            $promotorUser = User::factory()->create([
                'name' => sprintf('%s %s %s', $nombre, $apellidoPaterno, $apellidoMaterno),
                'email' => sprintf('supervisor.promotor%02d@example.com', $i),
                'password' => Hash::make('12345'),
                'telefono' => $faker->unique()->numerify('56########'),
                'rol' => 'promotor',
            ]);
            $promotorUser->assignRole('promotor');

            Promotor::create([
                'user_id' => $promotorUser->id,
                'supervisor_id' => $supervisor->id,
                'nombre' => $nombre,
                'apellido_p' => $apellidoPaterno,
                'apellido_m' => $apellidoMaterno,
                'venta_maxima' => $faker->randomElement([3000, 4000, 5000, 6000, 7000, 8000, 10000, 12000, 15000, 20000]),
                'colonia' => $faker->streetName(),
                'venta_proyectada_objetivo' => $faker->randomElement([3000, 4000, 5000, 6000, 7000, 8000, 10000, 12000, 15000, 20000]),
                'bono' => $faker->randomFloat(2, 200, 1500),
            ]);
        }

        $faker->unique(true);

        $user = User::factory()->create([
            'name' => 'Promotor User',
            'email' => 'promotor@example.com',
            'password' => Hash::make('12345'),
            'telefono' => '0987654321',
            'rol' => 'promotor',
        ]);
        $user->assignRole('promotor');
        $promotor = Promotor::create([
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

        $faker = fake();

        $carteraEstados = [
            'activo',
            'moroso',
            'desembolsado',
            'regularizado',
            'inactivo',
        ];

        for ($i = 1; $i <= 10; $i++) {
            [$nombre, $apellidoPaterno, $apellidoMaterno] = LatinoNameGenerator::person();
            $carteraEstado = $faker->randomElement($carteraEstados);
            $tieneCreditoActivo = in_array($carteraEstado, ['activo', 'moroso', 'desembolsado'], true);

            Cliente::create([
                'promotor_id' => $promotor->id,
                'CURP' => $faker->unique()->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}'),
                'nombre' => $nombre,
                'apellido_p' => $apellidoPaterno,
                'apellido_m' => $apellidoMaterno,
                'fecha_nacimiento' => Carbon::instance($faker->dateTimeBetween('-65 years', '-18 years'))->toDateString(),
                'tiene_credito_activo' => $tieneCreditoActivo,
                'cartera_estado' => $carteraEstado,
                'monto_maximo' => $faker->randomElement([3000, 4000, 5000, 5500, 6000, 6500, 7000, 7500, 8000, 10000, 12000, 15000, 20000]),
                'creado_en' => Carbon::now(),
                'actualizado_en' => Carbon::now(),
                'activo' => $tieneCreditoActivo,
            ]);
        }

        $faker->unique(true);
    }
}
