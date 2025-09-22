<?php

namespace Database\Seeders;

use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\DocumentoAval;
use App\Models\DocumentoCliente;
use App\Models\Ejecutivo;
use App\Models\Garantia;
use App\Models\InformacionFamiliar;
use App\Models\Ocupacion;
use App\Models\PagoAnticipo;
use App\Models\PagoCompleto;
use App\Models\PagoDiferido;
use App\Models\PagoProyectado;
use App\Models\PagoReal;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
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

        $clientesPromotor = [];

        for ($i = 1; $i <= 10; $i++) {
            [$nombre, $apellidoPaterno, $apellidoMaterno] = LatinoNameGenerator::person();
            $carteraEstado = $faker->randomElement($carteraEstados);
            $tieneCreditoActivo = in_array($carteraEstado, ['activo', 'moroso', 'desembolsado'], true);

            $cliente = Cliente::create([
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

            $clientesPromotor[] = $cliente;
        }

        $faker->unique(true);

        $estadoCreditoPorCartera = [
            'activo' => 'supervisado',
            'moroso' => 'vencido',
            'desembolsado' => 'desembolsado',
            'regularizado' => 'liquidado',
            'inactivo' => 'cancelado',
        ];

        $montoPorEstado = [
            'activo' => 6400.00,
            'moroso' => 7500.00,
            'desembolsado' => 6800.00,
            'regularizado' => 6000.00,
            'inactivo' => 5500.00,
        ];

        $secuenciasPorEstado = [
            'activo' => ['pagado', 'pagado', 'pendiente', 'pendiente', 'pendiente', 'pendiente', 'pendiente', 'pendiente'],
            'moroso' => ['pagado', 'vencido', 'vencido', 'vencido', 'pendiente', 'pendiente', 'pendiente', 'pendiente'],
            'desembolsado' => ['pagado', 'pendiente', 'pendiente', 'pendiente', 'pendiente', 'pendiente', 'pendiente', 'pendiente'],
            'regularizado' => array_fill(0, 8, 'pagado'),
            'inactivo' => array_fill(0, 6, 'pagado'),
        ];

        foreach ($clientesPromotor as $cliente) {
            $carteraEstado = $cliente->cartera_estado;
            $estadoCredito = $estadoCreditoPorCartera[$carteraEstado] ?? 'supervisado';
            $montoTotal = $montoPorEstado[$carteraEstado] ?? 6000.00;
            $secuenciaEstados = $secuenciasPorEstado[$carteraEstado] ?? $secuenciasPorEstado['activo'];

            $numeroPagos = count($secuenciaEstados);
            $montoSemanal = round($montoTotal / max($numeroPagos, 1), 2);

            $indicePendiente = array_search('pendiente', $secuenciaEstados, true);
            if ($indicePendiente === false) {
                $fechaInicio = Carbon::now()->copy()->subWeeks($numeroPagos + 1);
            } else {
                $fechaInicio = Carbon::now()->copy()->addWeek()->subWeeks($indicePendiente);
            }
            $fechaFinal = $fechaInicio->copy()->addWeeks($numeroPagos - 1);

            $credito = Credito::create([
                'cliente_id' => $cliente->id,
                'monto_total' => $montoTotal,
                'estado' => $estadoCredito,
                'interes' => match ($carteraEstado) {
                    'moroso' => 14.5,
                    'desembolsado' => 12.0,
                    'regularizado' => 10.5,
                    'inactivo' => 9.5,
                    default => 11.5,
                },
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_final' => $fechaFinal->toDateString(),
            ]);

            DatoContacto::create([
                'credito_id' => $credito->id,
                'calle' => $faker->streetName(),
                'numero_ext' => $faker->buildingNumber(),
                'numero_int' => $faker->optional()->buildingNumber(),
                'monto_mensual' => $faker->numberBetween(1200, 4500),
                'colonia' => $faker->citySuffix(),
                'municipio' => $faker->city(),
                'estado' => $faker->state(),
                'cp' => $faker->postcode(),
                'tiempo_en_residencia' => $faker->numberBetween(1, 15) . ' años',
                'tel_fijo' => $faker->optional()->phoneNumber(),
                'tel_cel' => $faker->phoneNumber(),
                'tipo_de_vivienda' => $faker->randomElement(['propia', 'rentada', 'familiar']),
                'creado_en' => Carbon::now(),
            ]);

            Ocupacion::create([
                'credito_id' => $credito->id,
                'actividad' => $faker->jobTitle(),
                'nombre_empresa' => $faker->company(),
                'calle' => $faker->streetName(),
                'numero' => $faker->buildingNumber(),
                'colonia' => $faker->citySuffix(),
                'municipio' => $faker->city(),
                'telefono' => $faker->phoneNumber(),
                'antiguedad' => $faker->numberBetween(1, 12) . ' años',
                'monto_percibido' => $faker->randomFloat(2, 1500, 8000),
                'periodo_pago' => $faker->randomElement(['semanal', 'quincenal']),
                'creado_en' => Carbon::now(),
            ]);

            InformacionFamiliar::create([
                'credito_id' => $credito->id,
                'nombre_conyuge' => LatinoNameGenerator::fullName(),
                'celular_conyuge' => $faker->phoneNumber(),
                'actividad_conyuge' => $faker->jobTitle(),
                'ingresos_semanales_conyuge' => $faker->randomFloat(2, 800, 2500),
                'domicilio_trabajo_conyuge' => $faker->address(),
                'personas_en_domicilio' => $faker->numberBetween(2, 7),
                'dependientes_economicos' => $faker->numberBetween(0, 3),
                'conyuge_vive_con_cliente' => $faker->boolean(),
                'creado_en' => Carbon::now(),
            ]);

            [$avalNombre, $avalApellidoPaterno, $avalApellidoMaterno] = LatinoNameGenerator::person();

            $aval = Aval::create([
                'CURP' => strtoupper($faker->bothify('????######??????##')),
                'credito_id' => $credito->id,
                'nombre' => $avalNombre,
                'apellido_p' => $avalApellidoPaterno,
                'apellido_m' => $avalApellidoMaterno,
                'fecha_nacimiento' => Carbon::instance($faker->dateTimeBetween('-60 years', '-25 years'))->toDateString(),
                'direccion' => $faker->address(),
                'telefono' => $faker->phoneNumber(),
                'parentesco' => $faker->randomElement(['hermano', 'amigo', 'conyuge', 'primo']),
                'creado_en' => Carbon::now(),
            ]);

            DocumentoAval::create([
                'aval_id' => $aval->id,
                'tipo_doc' => 'identificacion',
                'url_s3' => $faker->url(),
                'nombre_arch' => 'identificacion-aval.pdf',
                'creado_en' => Carbon::now(),
            ]);

            DocumentoCliente::create([
                'cliente_id' => $cliente->id,
                'credito_id' => $credito->id,
                'tipo_doc' => 'identificacion',
                'url_s3' => $faker->url(),
                'nombre_arch' => 'ine-cliente.pdf',
                'creado_en' => Carbon::now(),
            ]);

            Garantia::create([
                'credito_id' => $credito->id,
                'propietario' => LatinoNameGenerator::fullName(),
                'tipo' => $faker->randomElement(['electrodomestico', 'vehiculo', 'mobiliario']),
                'marca' => $faker->company(),
                'modelo' => strtoupper($faker->bothify('MOD-###')), 
                'num_serie' => strtoupper($faker->bothify('SER-#####')), 
                'antiguedad' => $faker->numberBetween(1, 8) . ' años',
                'monto_garantizado' => $faker->randomFloat(2, 1500, 6000),
                'foto_url' => $faker->imageUrl(),
                'creado_en' => Carbon::now(),
            ]);

            Contrato::create([
                'credito_id' => $credito->id,
                'tipo_contrato' => 'credito individual',
                'fecha_generacion' => $fechaInicio->copy()->subDay()->toDateString(),
                'url_s3' => $faker->url(),
            ]);

            $pagosProyectados = [];
            $acumulado = 0.0;

            foreach ($secuenciaEstados as $indice => $estadoPago) {
                $fechaLimite = $fechaInicio->copy()->addWeeks($indice);

                if ($estadoPago === 'vencido' && $fechaLimite->greaterThan(Carbon::now())) {
                    $fechaLimite = Carbon::now()->copy()->subDays(2);
                }

                if ($estadoPago === 'pagado' && $fechaLimite->greaterThan(Carbon::now())) {
                    $fechaLimite = Carbon::now()->copy()->subDay();
                }

                if ($estadoPago === 'pendiente' && $fechaLimite->lessThan(Carbon::now())) {
                    $fechaLimite = Carbon::now()->copy()->addDays(3);
                }

                $montoPago = $indice === $numeroPagos - 1
                    ? round($montoTotal - $acumulado, 2)
                    : round($montoSemanal, 2);

                $acumulado += $montoPago;

                $pagosProyectados[$indice] = PagoProyectado::create([
                    'credito_id' => $credito->id,
                    'semana' => $indice + 1,
                    'monto_proyectado' => $montoPago,
                    'fecha_limite' => $fechaLimite->toDateString(),
                    'estado' => $estadoPago,
                ]);
            }

            if (count($pagosProyectados) >= 3) {
                $primerPago = $pagosProyectados[0];
                $segundoPago = $pagosProyectados[1];
                $tercerPago = $pagosProyectados[2];

                $fechaPagoCompleto = Carbon::parse($primerPago->fecha_limite);
                if ($fechaPagoCompleto->greaterThan(Carbon::now())) {
                    $fechaPagoCompleto = Carbon::now()->copy()->subDay();
                }

                $pagoCompleto = PagoReal::create([
                    'pago_proyectado_id' => $primerPago->id,
                    'tipo' => 'efectivo',
                    'fecha_pago' => $fechaPagoCompleto->toDateString(),
                    'comentario' => 'Pago semanal completado sin incidentes.',
                ]);

                PagoCompleto::create([
                    'pago_real_id' => $pagoCompleto->id,
                    'monto_completo' => (float) $primerPago->monto_proyectado,
                ]);

                $fechaPagoAnticipo = Carbon::parse($segundoPago->fecha_limite)->copy()->subDays(2);
                if ($fechaPagoAnticipo->greaterThan(Carbon::now())) {
                    $fechaPagoAnticipo = Carbon::now();
                }

                $montoAnticipo = match ($carteraEstado) {
                    'moroso' => round((float) $segundoPago->monto_proyectado * 0.4, 2),
                    'desembolsado' => round((float) $segundoPago->monto_proyectado * 0.5, 2),
                    'regularizado', 'inactivo' => (float) $segundoPago->monto_proyectado,
                    default => round((float) $segundoPago->monto_proyectado * 0.7, 2),
                };

                $pagoAnticipo = PagoReal::create([
                    'pago_proyectado_id' => $segundoPago->id,
                    'tipo' => 'transferencia',
                    'fecha_pago' => $fechaPagoAnticipo->toDateString(),
                    'comentario' => 'Pago anticipado registrado desde la app móvil.',
                ]);

                PagoAnticipo::create([
                    'pago_real_id' => $pagoAnticipo->id,
                    'monto_anticipo' => $montoAnticipo,
                ]);

                $fechaPagoDiferido = Carbon::parse($tercerPago->fecha_limite)->copy();
                if ($carteraEstado === 'moroso') {
                    $fechaPagoDiferido->addDays(5);
                }
                if ($fechaPagoDiferido->greaterThan(Carbon::now())) {
                    $fechaPagoDiferido = Carbon::now();
                }

                $montoDiferido = match ($carteraEstado) {
                    'moroso' => round((float) $tercerPago->monto_proyectado * 0.8, 2),
                    'regularizado' => round((float) $tercerPago->monto_proyectado * 0.1, 2),
                    'inactivo' => 0.0,
                    'desembolsado' => round((float) $tercerPago->monto_proyectado * 0.5, 2),
                    default => round((float) $tercerPago->monto_proyectado * 0.3, 2),
                };

                $pagoDiferido = PagoReal::create([
                    'pago_proyectado_id' => $tercerPago->id,
                    'tipo' => 'transferencia',
                    'fecha_pago' => $fechaPagoDiferido->toDateString(),
                    'comentario' => 'Se difirió parte del pago semanal.',
                ]);

                PagoDiferido::create([
                    'pago_real_id' => $pagoDiferido->id,
                    'monto_diferido' => $montoDiferido,
                ]);
            }
        }
    }
}
