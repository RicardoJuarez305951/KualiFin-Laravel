<?php

namespace Database\Seeders;

use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\Ejecutivo;
use App\Models\Garantia;
use App\Models\InformacionFamiliar;
use App\Models\Ocupacion;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RealisticFilterCasesSeeder extends Seeder
{
    private const PASSWORD = '12345';

    private const CLIENT_CURPS = [
        'RFCD900101HDFLLA01',
        'RFAV810101HDFLCA02',
        'RFAV830303MDFLDA03',
        'RFAV920202MDFLRA04',
        'RFFL850505HDFLRA05',
        'RFAC880808MDFLGA06',
        'RFOP900909HDFLHE07',
        'RFPF910101HDFLAA08',
        'RFPF910202HDFLBB09',
        'RFPF910303HDFLCC10',
        'RFPF910404HDFLDD11',
        'RFPF910505HDFLEE12',
        'RFPF910606HDFLFF13',
        'RFPF910707HDFLGG14',
        'RFPF910808HDFLHH15',
        'RFPF910909HDFLII16',
        'RFPF911010HDFLJJ17',
        'RFPF911111HDFLKK18',
        'RFDD910101MDFLJO18',
        'RFDD920202MDFLKA19',
        'RFDD930303HDFLLA20',
        'RFRQ940404MDFLMA21',
        'RFRQ950505MDFLNA22',
        'RFRQ960606HDFLOA23',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->purgeExistingData();

            $hierarchy = $this->ensureHierarchy();
            $promotorPrincipal = $hierarchy['promotor'];
            $promotorSecundario = $hierarchy['promotor_secundario'];

            $this->seedCurpUnica($promotorPrincipal);
            $this->seedDobleFirmaAval($promotorPrincipal);
            $this->seedCreditoEnFalla($promotorPrincipal);
            $this->seedCreditoActivo($promotorPrincipal);
            $this->seedOtraPlaza($promotorPrincipal, $promotorSecundario);
            $this->seedBloqueoFallaPromotora($promotorPrincipal);
            $this->seedDobleDomicilio($promotorPrincipal);
            $this->seedBloqueoTiempoRecreditos($promotorPrincipal);
        });
    }

    private function purgeExistingData(): void
    {
        $curps = array_unique(self::CLIENT_CURPS);

        $clientes = Cliente::whereIn('CURP', $curps)->get();

        if ($clientes->isEmpty()) {
            return;
        }

        $clienteIds = $clientes->pluck('id');
        $creditos = Credito::whereIn('cliente_id', $clienteIds)->get();
        $creditoIds = $creditos->pluck('id');

        if ($creditoIds->isNotEmpty()) {
            DatoContacto::whereIn('credito_id', $creditoIds)->delete();
            Aval::whereIn('credito_id', $creditoIds)->delete();
            Ocupacion::whereIn('credito_id', $creditoIds)->delete();
            InformacionFamiliar::whereIn('credito_id', $creditoIds)->delete();
            Garantia::whereIn('credito_id', $creditoIds)->delete();
            Contrato::whereIn('credito_id', $creditoIds)->delete();
            Credito::whereIn('id', $creditoIds)->delete();
        }

        Cliente::whereIn('id', $clienteIds)->delete();
    }

    private function ensureHierarchy(): array
    {
        $ejecutivoUser = $this->ensureUser(
            'ejecutivo@example.com',
            'Ernesto Campos Delgado',
            'ejecutivo',
            '5552000001'
        );

        $ejecutivo = Ejecutivo::updateOrCreate(
            ['user_id' => $ejecutivoUser->id],
            [
                'nombre' => 'Ernesto',
                'apellido_p' => 'Campos',
                'apellido_m' => 'Delgado',
            ]
        );

        $supervisorUser = $this->ensureUser(
            'supervisor@example.com',
            'Beatriz Mendoza Rios',
            'supervisor',
            '5552000002'
        );

        $supervisor = Supervisor::updateOrCreate(
            ['user_id' => $supervisorUser->id],
            [
                'ejecutivo_id' => $ejecutivo->id,
                'nombre' => 'Beatriz',
                'apellido_p' => 'Mendoza',
                'apellido_m' => 'Rios',
            ]
        );

        $promotorPrincipalUser = $this->ensureUser(
            'promotor@example.com',
            'Claudia Vazquez Romero',
            'promotor',
            '5552000003'
        );

        $promotorPrincipal = Promotor::updateOrCreate(
            ['user_id' => $promotorPrincipalUser->id],
            [
                'supervisor_id' => $supervisor->id,
                'nombre' => 'Claudia',
                'apellido_p' => 'Vazquez',
                'apellido_m' => 'Romero',
                'venta_maxima' => 18500,
                'colonia' => 'Narvarte Poniente',
                'venta_proyectada_objetivo' => 12800,
                'bono' => 860,
                'dia_de_pago' => 'Lunes',
                'hora_de_pago' => '09:00',
            ]
        );

        $promotorSecundarioUser = $this->ensureUser(
            'promotor2@example.com',
            'Luis Serrano Cano',
            'promotor',
            '5552000004'
        );

        $promotorSecundario = Promotor::updateOrCreate(
            ['user_id' => $promotorSecundarioUser->id],
            [
                'supervisor_id' => $supervisor->id,
                'nombre' => 'Luis',
                'apellido_p' => 'Serrano',
                'apellido_m' => 'Cano',
                'venta_maxima' => 16250,
                'colonia' => 'Tacubaya',
                'venta_proyectada_objetivo' => 10200,
                'bono' => 720,
                'dia_de_pago' => 'Martes',
                'hora_de_pago' => '11:00',
            ]
        );

        return [
            'ejecutivo' => $ejecutivo,
            'supervisor' => $supervisor,
            'promotor' => $promotorPrincipal,
            'promotor_secundario' => $promotorSecundario,
        ];
    }

    private function ensureUser(string $email, string $name, string $role, string $phone): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'telefono' => $phone,
                'password' => Hash::make(self::PASSWORD),
                'rol' => $role,
            ]
        );
    }

    private function createCliente(Promotor $promotor, array $data): Cliente
    {
        $defaults = [
            'promotor_id' => $promotor->id,
            'fecha_nacimiento' => '1985-01-01',
            'tiene_credito_activo' => false,
            'cartera_estado' => 'inactivo',
            'monto_maximo' => 7500,
            'creado_en' => Carbon::now()->subMonths(2),
            'actualizado_en' => Carbon::now()->subWeeks(3),
            'activo' => false,
        ];

        return Cliente::create(array_merge($defaults, $data));
    }

    private function createCreditoCompleto(
        Cliente $cliente,
        array $creditData,
        array $domicilio,
        array $avales,
        array $extras = []
    ): Credito {
        $fechaInicio = $creditData['fecha_inicio'] instanceof Carbon
            ? $creditData['fecha_inicio']->copy()
            : Carbon::parse((string) $creditData['fecha_inicio']);

        $fechaFinal = isset($creditData['fecha_final'])
            ? ($creditData['fecha_final'] instanceof Carbon
                ? $creditData['fecha_final']->copy()
                : Carbon::parse((string) $creditData['fecha_final']))
            : $fechaInicio->copy()->addWeeks((int) ($creditData['duracion_semanas'] ?? 14));

        $credito = Credito::create([
            'cliente_id' => $cliente->id,
            'monto_total' => $creditData['monto_total'],
            'estado' => $creditData['estado'],
            'interes' => $creditData['interes'],
            'periodicidad' => $creditData['periodicidad'],
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_final' => $fechaFinal->toDateString(),
        ]);

        DatoContacto::create([
            'credito_id' => $credito->id,
            'calle' => $domicilio['calle'],
            'numero_ext' => $domicilio['numero_ext'],
            'numero_int' => $domicilio['numero_int'] ?? null,
            'monto_mensual' => $domicilio['monto_mensual'],
            'colonia' => $domicilio['colonia'],
            'municipio' => $domicilio['municipio'],
            'estado' => $domicilio['estado'] ?? 'Ciudad de Mexico',
            'cp' => $domicilio['cp'],
            'tiempo_en_residencia' => $domicilio['tiempo_en_residencia'],
            'tel_fijo' => $domicilio['tel_fijo'] ?? null,
            'tel_cel' => $domicilio['tel_cel'],
            'tipo_de_vivienda' => $domicilio['tipo_de_vivienda'],
            'creado_en' => Carbon::now(),
        ]);

        foreach ($avales as $aval) {
            Aval::create([
                'credito_id' => $credito->id,
                'CURP' => $aval['curp'],
                'nombre' => $aval['nombre'],
                'apellido_p' => $aval['apellido_p'],
                'apellido_m' => $aval['apellido_m'],
                'fecha_nacimiento' => $aval['fecha_nacimiento'],
                'direccion' => $aval['direccion'],
                'telefono' => $aval['telefono'],
                'parentesco' => $aval['parentesco'],
                'creado_en' => Carbon::now(),
            ]);
        }

        if (isset($extras['ocupacion'])) {
            $ocupacion = $extras['ocupacion'];

            Ocupacion::create([
                'credito_id' => $credito->id,
                'actividad' => $ocupacion['actividad'],
                'nombre_empresa' => $ocupacion['nombre_empresa'],
                'calle' => $ocupacion['calle'],
                'numero' => $ocupacion['numero'],
                'colonia' => $ocupacion['colonia'],
                'municipio' => $ocupacion['municipio'],
                'telefono' => $ocupacion['telefono'],
                'antiguedad' => $ocupacion['antiguedad'],
                'monto_percibido' => $ocupacion['monto_percibido'],
                'periodo_pago' => $ocupacion['periodo_pago'],
                'creado_en' => Carbon::now(),
            ]);
        }

        if (isset($extras['informacion_familiar'])) {
            $info = $extras['informacion_familiar'];

            InformacionFamiliar::create([
                'credito_id' => $credito->id,
                'nombre_conyuge' => $info['nombre_conyuge'],
                'celular_conyuge' => $info['celular_conyuge'],
                'actividad_conyuge' => $info['actividad_conyuge'],
                'ingresos_semanales_conyuge' => $info['ingresos_semanales_conyuge'],
                'domicilio_trabajo_conyuge' => $info['domicilio_trabajo_conyuge'],
                'personas_en_domicilio' => $info['personas_en_domicilio'],
                'dependientes_economicos' => $info['dependientes_economicos'],
                'conyuge_vive_con_cliente' => $info['conyuge_vive_con_cliente'],
                'creado_en' => Carbon::now(),
            ]);
        }

        if (isset($extras['garantia'])) {
            $garantia = $extras['garantia'];

            Garantia::create([
                'credito_id' => $credito->id,
                'propietario' => $garantia['propietario'],
                'tipo' => $garantia['tipo'],
                'marca' => $garantia['marca'] ?? null,
                'modelo' => $garantia['modelo'] ?? null,
                'num_serie' => $garantia['num_serie'] ?? null,
                'antiguedad' => $garantia['antiguedad'],
                'monto_garantizado' => $garantia['monto_garantizado'],
                'foto_url' => $garantia['foto_url'],
                'creado_en' => Carbon::now(),
            ]);
        }

        if (isset($extras['contrato'])) {
            $contrato = $extras['contrato'];

            $fechaGeneracion = $contrato['fecha_generacion'] instanceof Carbon
                ? $contrato['fecha_generacion']->toDateString()
                : Carbon::parse((string) $contrato['fecha_generacion'])->toDateString();

            Contrato::create([
                'credito_id' => $credito->id,
                'tipo_contrato' => $contrato['tipo_contrato'],
                'fecha_generacion' => $fechaGeneracion,
                'url_s3' => $contrato['url_s3'],
            ]);
        }

        return $credito;
    }

    private function seedCurpUnica(Promotor $promotor): void
    {
        $clienteOriginal = $this->createCliente($promotor, [
            'CURP' => 'RFCD900101HDFLLA01',
            'nombre' => 'Laura',
            'apellido_p' => 'Cardenas',
            'apellido_m' => 'Lopez',
            'fecha_nacimiento' => '1984-03-12',
            'cartera_estado' => 'regularizado',
            'tiene_credito_activo' => false,
            'monto_maximo' => 9800,
            'creado_en' => Carbon::now()->subMonths(14),
            'actualizado_en' => Carbon::now()->subMonths(4),
            'activo' => false,
        ]);

        $this->createCreditoCompleto(
            $clienteOriginal,
            [
                'monto_total' => 7800,
                'estado' => 'liquidado',
                'interes' => 12.5,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->subMonths(10),
                'duracion_semanas' => 14,
            ],
            [
                'calle' => 'Calle Lima',
                'numero_ext' => '54',
                'numero_int' => '2B',
                'monto_mensual' => 1800,
                'colonia' => 'Portales Norte',
                'municipio' => 'Benito Juarez',
                'estado' => 'Ciudad de Mexico',
                'cp' => '03300',
                'tiempo_en_residencia' => '5 anios',
                'tel_fijo' => '5556789012',
                'tel_cel' => '5543210987',
                'tipo_de_vivienda' => 'Propia',
            ],
            [
                [
                    'curp' => 'RFAV750101HDFLME01',
                    'nombre' => 'Marisol',
                    'apellido_p' => 'Esquivel',
                    'apellido_m' => 'Lozano',
                    'fecha_nacimiento' => '1975-01-01',
                    'direccion' => 'Calle Lima 50, Portales, CDMX',
                    'telefono' => '5512345678',
                    'parentesco' => 'Hermana',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Venta de catalogo',
                    'nombre_empresa' => 'Comercial Portales',
                    'calle' => 'Av. Universidad',
                    'numero' => '120',
                    'colonia' => 'Portales',
                    'municipio' => 'Benito Juarez',
                    'telefono' => '5557894321',
                    'antiguedad' => '4 anios',
                    'monto_percibido' => 3600.00,
                    'periodo_pago' => 'semanal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Jose Miguel Cardenas',
                    'celular_conyuge' => '5512349988',
                    'actividad_conyuge' => 'Chofer',
                    'ingresos_semanales_conyuge' => 2500.00,
                    'domicilio_trabajo_conyuge' => 'Av. Tlalpan 400, CDMX',
                    'personas_en_domicilio' => 4,
                    'dependientes_economicos' => 2,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Laura Cardenas',
                    'tipo' => 'Electrodomestico',
                    'marca' => 'Samsung',
                    'modelo' => 'RT38',
                    'num_serie' => 'SAMRT3898765',
                    'antiguedad' => '2 anios',
                    'monto_garantizado' => 5100.00,
                    'foto_url' => 'https://example.com/garantia/refrigerador-cardenas.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->subMonths(10)->subDay(),
                    'url_s3' => 'https://example.com/contratos/cardenas-liquida.pdf',
                ],
            ]
        );

        $clienteDuplicado = $this->createCliente($promotor, [
            'CURP' => 'RFCD900101HDFLLA01',
            'nombre' => 'Lucia',
            'apellido_p' => 'Cardenas',
            'apellido_m' => 'Lopez',
            'fecha_nacimiento' => '1990-07-18',
            'cartera_estado' => 'inactivo',
            'tiene_credito_activo' => false,
            'monto_maximo' => 7600,
            'creado_en' => Carbon::now()->subMonths(2),
            'actualizado_en' => Carbon::now()->subWeeks(2),
            'activo' => false,
        ]);

        $this->createCreditoCompleto(
            $clienteDuplicado,
            [
                'monto_total' => 6400,
                'estado' => 'prospectado',
                'interes' => 13.2,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->addWeeks(1),
                'duracion_semanas' => 14,
            ],
            [
                'calle' => 'Calle Fresno',
                'numero_ext' => '18',
                'numero_int' => null,
                'monto_mensual' => 1500,
                'colonia' => 'General Anaya',
                'municipio' => 'Benito Juarez',
                'estado' => 'Ciudad de Mexico',
                'cp' => '03340',
                'tiempo_en_residencia' => '1 anio',
                'tel_fijo' => null,
                'tel_cel' => '5534567890',
                'tipo_de_vivienda' => 'Renta',
            ],
            [
                [
                    'curp' => 'RFAV790202HDFLME02',
                    'nombre' => 'Rene',
                    'apellido_p' => 'Cardenas',
                    'apellido_m' => 'Lopez',
                    'fecha_nacimiento' => '1979-02-02',
                    'direccion' => 'Calzada de Tlalpan 1600, CDMX',
                    'telefono' => '5510012233',
                    'parentesco' => 'Hermano',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Diseno grafico',
                    'nombre_empresa' => 'Estudio Visual',
                    'calle' => 'Av. Patriotismo',
                    'numero' => '250',
                    'colonia' => 'San Pedro de los Pinos',
                    'municipio' => 'Benito Juarez',
                    'telefono' => '5556778890',
                    'antiguedad' => '2 anios',
                    'monto_percibido' => 4200.00,
                    'periodo_pago' => 'quincenal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Alberto Diaz',
                    'celular_conyuge' => '5511199922',
                    'actividad_conyuge' => 'Tecnico de soporte',
                    'ingresos_semanales_conyuge' => 2200.00,
                    'domicilio_trabajo_conyuge' => 'Av. Revolucion 130, CDMX',
                    'personas_en_domicilio' => 3,
                    'dependientes_economicos' => 1,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Lucia Cardenas',
                    'tipo' => 'Mobiliario',
                    'marca' => 'Ikea',
                    'modelo' => 'Bestar',
                    'num_serie' => 'MOB202401',
                    'antiguedad' => '1 anio',
                    'monto_garantizado' => 2800.00,
                    'foto_url' => 'https://example.com/garantia/mobiliario-cardenas.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->addWeeks(1)->subDay(),
                    'url_s3' => 'https://example.com/contratos/cardenas-lucia.pdf',
                ],
            ]
        );
    }

    private function seedDobleFirmaAval(Promotor $promotor): void
    {
        $avalCurp = 'RFMG750707HPLCND06';

        $clienteCarlos = $this->createCliente($promotor, [
            'CURP' => 'RFAV810101HDFLCA02',
            'nombre' => 'Carlos',
            'apellido_p' => 'Arredondo',
            'apellido_m' => 'Villalba',
            'fecha_nacimiento' => '1981-01-01',
            'cartera_estado' => 'activo',
            'tiene_credito_activo' => true,
            'monto_maximo' => 11000,
            'creado_en' => Carbon::now()->subMonths(6),
            'actualizado_en' => Carbon::now()->subWeeks(1),
            'activo' => true,
        ]);

        $this->createCreditoCompleto(
            $clienteCarlos,
            [
                'monto_total' => 8900,
                'estado' => 'desembolsado',
                'interes' => 11.8,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->subWeeks(6),
                'duracion_semanas' => 16,
            ],
            [
                'calle' => 'Av. Revolucion',
                'numero_ext' => '150',
                'numero_int' => null,
                'monto_mensual' => 2200,
                'colonia' => 'Tacubaya',
                'municipio' => 'Miguel Hidalgo',
                'estado' => 'Ciudad de Mexico',
                'cp' => '11870',
                'tiempo_en_residencia' => '6 anios',
                'tel_fijo' => '5554401122',
                'tel_cel' => '5511122233',
                'tipo_de_vivienda' => 'Propia',
            ],
            [
                [
                    'curp' => $avalCurp,
                    'nombre' => 'Miguel',
                    'apellido_p' => 'Medina',
                    'apellido_m' => 'Gonzalez',
                    'fecha_nacimiento' => '1978-03-03',
                    'direccion' => 'Av. Revolucion 150, Tacubaya, CDMX',
                    'telefono' => '5511122233',
                    'parentesco' => 'Hermano',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Venta de refacciones',
                    'nombre_empresa' => 'Auto Repuestos Tacubaya',
                    'calle' => 'Av. Jalisco',
                    'numero' => '45',
                    'colonia' => 'Tacubaya',
                    'municipio' => 'Miguel Hidalgo',
                    'telefono' => '5554432110',
                    'antiguedad' => '5 anios',
                    'monto_percibido' => 5400.00,
                    'periodo_pago' => 'semanal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Patricia Villalba',
                    'celular_conyuge' => '5522334455',
                    'actividad_conyuge' => 'Maestra',
                    'ingresos_semanales_conyuge' => 3000.00,
                    'domicilio_trabajo_conyuge' => 'Av. Patriotismo 250, CDMX',
                    'personas_en_domicilio' => 5,
                    'dependientes_economicos' => 2,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Carlos Arredondo',
                    'tipo' => 'Vehiculo',
                    'marca' => 'Nissan',
                    'modelo' => 'Tsuru',
                    'num_serie' => 'TSU202209',
                    'antiguedad' => '8 anios',
                    'monto_garantizado' => 7800.00,
                    'foto_url' => 'https://example.com/garantia/tsuru-carlos.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->subWeeks(7),
                    'url_s3' => 'https://example.com/contratos/carlos-arredondo.pdf',
                ],
            ]
        );

        $clienteDaniela = $this->createCliente($promotor, [
            'CURP' => 'RFAV830303MDFLDA03',
            'nombre' => 'Daniela',
            'apellido_p' => 'Arredondo',
            'apellido_m' => 'Lopez',
            'fecha_nacimiento' => '1983-03-03',
            'cartera_estado' => 'activo',
            'tiene_credito_activo' => true,
            'monto_maximo' => 10200,
            'creado_en' => Carbon::now()->subMonths(5),
            'actualizado_en' => Carbon::now()->subWeeks(2),
            'activo' => true,
        ]);

        $this->createCreditoCompleto(
            $clienteDaniela,
            [
                'monto_total' => 7600,
                'estado' => 'supervisado',
                'interes' => 12.0,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->subWeeks(3),
                'duracion_semanas' => 14,
            ],
            [
                'calle' => 'Av. Patriotismo',
                'numero_ext' => '250',
                'numero_int' => '5A',
                'monto_mensual' => 2400,
                'colonia' => 'San Pedro de los Pinos',
                'municipio' => 'Benito Juarez',
                'estado' => 'Ciudad de Mexico',
                'cp' => '03800',
                'tiempo_en_residencia' => '3 anios',
                'tel_fijo' => '5554328899',
                'tel_cel' => '5512233445',
                'tipo_de_vivienda' => 'Renta',
            ],
            [
                [
                    'curp' => $avalCurp,
                    'nombre' => 'Miguel',
                    'apellido_p' => 'Medina',
                    'apellido_m' => 'Gonzalez',
                    'fecha_nacimiento' => '1978-03-03',
                    'direccion' => 'Av. Revolucion 150, Tacubaya, CDMX',
                    'telefono' => '5511122233',
                    'parentesco' => 'Hermano',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Diseno de modas',
                    'nombre_empresa' => 'Colectivo Textil',
                    'calle' => 'Av. Revolucion',
                    'numero' => '200',
                    'colonia' => 'Tacubaya',
                    'municipio' => 'Miguel Hidalgo',
                    'telefono' => '5556262626',
                    'antiguedad' => '2 anios',
                    'monto_percibido' => 4800.00,
                    'periodo_pago' => 'quincenal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Ramon Aguilar',
                    'celular_conyuge' => '5522003300',
                    'actividad_conyuge' => 'Chef',
                    'ingresos_semanales_conyuge' => 3100.00,
                    'domicilio_trabajo_conyuge' => 'Patriotismo 100, CDMX',
                    'personas_en_domicilio' => 3,
                    'dependientes_economicos' => 1,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Daniela Arredondo',
                    'tipo' => 'Electrodomestico',
                    'marca' => 'LG',
                    'modelo' => 'Lavadora TwinWash',
                    'num_serie' => 'LG202304',
                    'antiguedad' => '3 anios',
                    'monto_garantizado' => 5200.00,
                    'foto_url' => 'https://example.com/garantia/lavadora-daniela.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->subWeeks(4),
                    'url_s3' => 'https://example.com/contratos/daniela-arredondo.pdf',
                ],
            ]
        );

        $clienteRocio = $this->createCliente($promotor, [
            'CURP' => 'RFAV920202MDFLRA04',
            'nombre' => 'Rocio',
            'apellido_p' => 'Arriaga',
            'apellido_m' => 'Delgado',
            'fecha_nacimiento' => '1992-02-02',
            'cartera_estado' => 'inactivo',
            'tiene_credito_activo' => false,
            'monto_maximo' => 8200,
            'creado_en' => Carbon::now()->subMonths(1),
            'actualizado_en' => Carbon::now(),
            'activo' => false,
        ]);

        $this->createCreditoCompleto(
            $clienteRocio,
            [
                'monto_total' => 6400,
                'estado' => 'prospectado',
                'interes' => 12.1,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->addWeeks(2),
                'duracion_semanas' => 14,
            ],
            [
                'calle' => 'Calle Jose Maria Vigil',
                'numero_ext' => '85',
                'numero_int' => '3',
                'monto_mensual' => 2100,
                'colonia' => 'Escandon',
                'municipio' => 'Miguel Hidalgo',
                'estado' => 'Ciudad de Mexico',
                'cp' => '11800',
                'tiempo_en_residencia' => '1 anio',
                'tel_fijo' => null,
                'tel_cel' => '5523345566',
                'tipo_de_vivienda' => 'Renta',
            ],
            [
                [
                    'curp' => $avalCurp,
                    'nombre' => 'Miguel',
                    'apellido_p' => 'Medina',
                    'apellido_m' => 'Gonzalez',
                    'fecha_nacimiento' => '1978-03-03',
                    'direccion' => 'Av. Revolucion 150, Tacubaya, CDMX',
                    'telefono' => '5511122233',
                    'parentesco' => 'Hermano',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Administracion',
                    'nombre_empresa' => 'Despacho Serrador',
                    'calle' => 'Benjamin Franklin',
                    'numero' => '190',
                    'colonia' => 'Escandon',
                    'municipio' => 'Miguel Hidalgo',
                    'telefono' => '5556655443',
                    'antiguedad' => '1 anio',
                    'monto_percibido' => 4300.00,
                    'periodo_pago' => 'quincenal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Omar Delgado',
                    'celular_conyuge' => '5522001100',
                    'actividad_conyuge' => 'Analista de datos',
                    'ingresos_semanales_conyuge' => 2800.00,
                    'domicilio_trabajo_conyuge' => 'Av. Nuevo Leon 50, CDMX',
                    'personas_en_domicilio' => 2,
                    'dependientes_economicos' => 0,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Rocio Arriaga',
                    'tipo' => 'Equipo de computo',
                    'marca' => 'Dell',
                    'modelo' => 'Latitude 5420',
                    'num_serie' => 'DEL20240102',
                    'antiguedad' => '1 anio',
                    'monto_garantizado' => 4100.00,
                    'foto_url' => 'https://example.com/garantia/laptop-rocio.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->addWeeks(2)->subDay(),
                    'url_s3' => 'https://example.com/contratos/rocio-arriaga.pdf',
                ],
            ]
        );
    }

    private function seedCreditoEnFalla(Promotor $promotor): void
    {
        $cliente = $this->createCliente($promotor, [
            'CURP' => 'RFFL850505HDFLRA05',
            'nombre' => 'Francisco',
            'apellido_p' => 'Lara',
            'apellido_m' => 'Medina',
            'fecha_nacimiento' => '1985-05-05',
            'cartera_estado' => 'moroso',
            'tiene_credito_activo' => true,
            'monto_maximo' => 8800,
            'creado_en' => Carbon::now()->subMonths(8),
            'actualizado_en' => Carbon::now()->subWeeks(1),
            'activo' => true,
        ]);

        $this->createCreditoCompleto(
            $cliente,
            [
                'monto_total' => 9100,
                'estado' => 'vencido',
                'interes' => 13.0,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->subWeeks(18),
                'duracion_semanas' => 18,
            ],
            [
                'calle' => 'Calle Lirios',
                'numero_ext' => '210',
                'numero_int' => null,
                'monto_mensual' => 2000,
                'colonia' => 'San Simon',
                'municipio' => 'Benito Juarez',
                'estado' => 'Ciudad de Mexico',
                'cp' => '03660',
                'tiempo_en_residencia' => '7 anios',
                'tel_fijo' => '5557788990',
                'tel_cel' => '5544789654',
                'tipo_de_vivienda' => 'Propia',
            ],
            [
                [
                    'curp' => 'RFAV740404HDFLDI04',
                    'nombre' => 'Diana',
                    'apellido_p' => 'Lara',
                    'apellido_m' => 'Ortega',
                    'fecha_nacimiento' => '1974-04-04',
                    'direccion' => 'Calle Lirios 200, San Simon, CDMX',
                    'telefono' => '5541102233',
                    'parentesco' => 'Hermana',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Carpinteria',
                    'nombre_empresa' => 'Taller Lara',
                    'calle' => 'Calle Abedules',
                    'numero' => '20',
                    'colonia' => 'San Simon',
                    'municipio' => 'Benito Juarez',
                    'telefono' => '5559988776',
                    'antiguedad' => '9 anios',
                    'monto_percibido' => 4800.00,
                    'periodo_pago' => 'semanal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Elena Medina',
                    'celular_conyuge' => '5523341100',
                    'actividad_conyuge' => 'Costurera',
                    'ingresos_semanales_conyuge' => 2100.00,
                    'domicilio_trabajo_conyuge' => 'Calz. de Tlalpan 900, CDMX',
                    'personas_en_domicilio' => 5,
                    'dependientes_economicos' => 3,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Francisco Lara',
                    'tipo' => 'Herramienta',
                    'marca' => 'Bosch',
                    'modelo' => 'GTS 10J',
                    'num_serie' => 'BOS2022001',
                    'antiguedad' => '3 anios',
                    'monto_garantizado' => 3500.00,
                    'foto_url' => 'https://example.com/garantia/herramienta-francisco.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->subMonths(5),
                    'url_s3' => 'https://example.com/contratos/francisco-lara.pdf',
                ],
            ]
        );
    }

    private function seedCreditoActivo(Promotor $promotor): void
    {
        $cliente = $this->createCliente($promotor, [
            'CURP' => 'RFAC880808MDFLGA06',
            'nombre' => 'Gabriela',
            'apellido_p' => 'Garcia',
            'apellido_m' => 'Andrade',
            'fecha_nacimiento' => '1988-08-08',
            'cartera_estado' => 'activo',
            'tiene_credito_activo' => true,
            'monto_maximo' => 11500,
            'creado_en' => Carbon::now()->subMonths(7),
            'actualizado_en' => Carbon::now()->subDays(3),
            'activo' => true,
        ]);

        $this->createCreditoCompleto(
            $cliente,
            [
                'monto_total' => 10400,
                'estado' => 'desembolsado',
                'interes' => 11.4,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->subWeeks(4),
                'duracion_semanas' => 18,
            ],
            [
                'calle' => 'Av. Coyoacan',
                'numero_ext' => '780',
                'numero_int' => '21',
                'monto_mensual' => 2600,
                'colonia' => 'Del Valle',
                'municipio' => 'Benito Juarez',
                'estado' => 'Ciudad de Mexico',
                'cp' => '03100',
                'tiempo_en_residencia' => '2 anios',
                'tel_fijo' => null,
                'tel_cel' => '5533102200',
                'tipo_de_vivienda' => 'Renta',
            ],
            [
                [
                    'curp' => 'RFAV760909HDFLGR01',
                    'nombre' => 'Graciela',
                    'apellido_p' => 'Andrade',
                    'apellido_m' => 'Ortega',
                    'fecha_nacimiento' => '1976-09-09',
                    'direccion' => 'Av. Coyoacan 780, CDMX',
                    'telefono' => '5522003344',
                    'parentesco' => 'Madre',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Consultoria administrativa',
                    'nombre_empresa' => 'Servicios Integrales Delta',
                    'calle' => 'Av. Insurgentes Sur',
                    'numero' => '600',
                    'colonia' => 'Del Valle',
                    'municipio' => 'Benito Juarez',
                    'telefono' => '5556654433',
                    'antiguedad' => '2 anios',
                    'monto_percibido' => 6800.00,
                    'periodo_pago' => 'quincenal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Eduardo Andrade',
                    'celular_conyuge' => '5511223344',
                    'actividad_conyuge' => 'Ingeniero civil',
                    'ingresos_semanales_conyuge' => 4200.00,
                    'domicilio_trabajo_conyuge' => 'Av. Universidad 300, CDMX',
                    'personas_en_domicilio' => 4,
                    'dependientes_economicos' => 2,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Gabriela Garcia',
                    'tipo' => 'Electrodomestico',
                    'marca' => 'Whirlpool',
                    'modelo' => 'WRS588',
                    'num_serie' => 'WH2023002',
                    'antiguedad' => '1 anio',
                    'monto_garantizado' => 6200.00,
                    'foto_url' => 'https://example.com/garantia/refrigerador-gabriela.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->subWeeks(4)->subDay(),
                    'url_s3' => 'https://example.com/contratos/gabriela-garcia.pdf',
                ],
            ]
        );
    }

    private function seedOtraPlaza(Promotor $promotorOriginal, Promotor $promotorSecundario): void
    {
        $cliente = $this->createCliente($promotorOriginal, [
            'CURP' => 'RFOP900909HDFLHE07',
            'nombre' => 'Hector',
            'apellido_p' => 'Estrada',
            'apellido_m' => 'Nolasco',
            'fecha_nacimiento' => '1990-09-09',
            'cartera_estado' => 'inactivo',
            'tiene_credito_activo' => false,
            'monto_maximo' => 9200,
            'creado_en' => Carbon::now()->subMonths(3),
            'actualizado_en' => Carbon::now()->subWeeks(1),
            'activo' => false,
        ]);

        $this->createCreditoCompleto(
            $cliente,
            [
                'monto_total' => 7800,
                'estado' => 'solicitado',
                'interes' => 12.7,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->addWeeks(1),
                'duracion_semanas' => 14,
            ],
            [
                'calle' => 'Calle Lago Alberto',
                'numero_ext' => '320',
                'numero_int' => '7',
                'monto_mensual' => 1900,
                'colonia' => 'Granada',
                'municipio' => 'Miguel Hidalgo',
                'estado' => 'Ciudad de Mexico',
                'cp' => '11520',
                'tiempo_en_residencia' => '1 anio',
                'tel_fijo' => null,
                'tel_cel' => '5511227766',
                'tipo_de_vivienda' => 'Renta',
            ],
            [
                [
                    'curp' => 'RFAV820202HDFLEH05',
                    'nombre' => 'Elena',
                    'apellido_p' => 'Nolasco',
                    'apellido_m' => 'Garcia',
                    'fecha_nacimiento' => '1982-02-02',
                    'direccion' => 'Calle Lago Alberto 320, CDMX',
                    'telefono' => '5522110033',
                    'parentesco' => 'Hermana',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Logistica',
                    'nombre_empresa' => 'Distribuciones HB',
                    'calle' => 'Av. Ejercito Nacional',
                    'numero' => '900',
                    'colonia' => 'Polanco',
                    'municipio' => 'Miguel Hidalgo',
                    'telefono' => '5556678899',
                    'antiguedad' => '3 anios',
                    'monto_percibido' => 5200.00,
                    'periodo_pago' => 'quincenal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Karen Soto',
                    'celular_conyuge' => '5511443300',
                    'actividad_conyuge' => 'Arquitecta',
                    'ingresos_semanales_conyuge' => 4100.00,
                    'domicilio_trabajo_conyuge' => 'Av. Reforma 300, CDMX',
                    'personas_en_domicilio' => 3,
                    'dependientes_economicos' => 1,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Hector Estrada',
                    'tipo' => 'Equipo de computo',
                    'marca' => 'HP',
                    'modelo' => 'EliteBook',
                    'num_serie' => 'HP202303',
                    'antiguedad' => '2 anios',
                    'monto_garantizado' => 4700.00,
                    'foto_url' => 'https://example.com/garantia/laptop-hector.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->addWeeks(1)->subDay(),
                    'url_s3' => 'https://example.com/contratos/hector-estrada.pdf',
                ],
            ]
        );
    }

    private function seedBloqueoFallaPromotora(Promotor $promotor): void
    {
        $escenarios = [
            ['curp' => 'RFPF910101HDFLAA08', 'nombre' => 'Adriana', 'estado' => 'desembolsado', 'cartera' => 'activo', 'activo' => true],
            ['curp' => 'RFPF910202HDFLBB09', 'nombre' => 'Brenda', 'estado' => 'desembolsado', 'cartera' => 'activo', 'activo' => true],
            ['curp' => 'RFPF910303HDFLCC10', 'nombre' => 'Carmen', 'estado' => 'desembolsado', 'cartera' => 'activo', 'activo' => true],
            ['curp' => 'RFPF910404HDFLDD11', 'nombre' => 'Dario', 'estado' => 'supervisado', 'cartera' => 'activo', 'activo' => true],
            ['curp' => 'RFPF910505HDFLEE12', 'nombre' => 'Elisa', 'estado' => 'supervisado', 'cartera' => 'activo', 'activo' => true],
            ['curp' => 'RFPF910606HDFLFF13', 'nombre' => 'Fernando', 'estado' => 'aprobado', 'cartera' => 'activo', 'activo' => true],
            ['curp' => 'RFPF910707HDFLGG14', 'nombre' => 'Georgina', 'estado' => 'desembolsado', 'cartera' => 'activo', 'activo' => true],
            ['curp' => 'RFPF910808HDFLHH15', 'nombre' => 'Hilda', 'estado' => 'desembolsado', 'cartera' => 'activo', 'activo' => true],
            ['curp' => 'RFPF910909HDFLII16', 'nombre' => 'Ines', 'estado' => 'vencido', 'cartera' => 'moroso', 'activo' => true],
            ['curp' => 'RFPF911010HDFLJJ17', 'nombre' => 'Jorge', 'estado' => 'cancelado', 'cartera' => 'moroso', 'activo' => true],
            ['curp' => 'RFPF911111HDFLKK18', 'nombre' => 'Isabel', 'estado' => 'prospectado', 'cartera' => 'inactivo', 'activo' => false],
        ];

        foreach ($escenarios as $index => $escenario) {
            $cliente = $this->createCliente($promotor, [
                'CURP' => $escenario['curp'],
                'nombre' => $escenario['nombre'],
                'apellido_p' => 'Quintero',
                'apellido_m' => 'Caso' . ($index + 1),
                'fecha_nacimiento' => '1991-01-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'cartera_estado' => $escenario['cartera'],
                'tiene_credito_activo' => $escenario['activo'],
                'monto_maximo' => 6800 + ($index * 300),
                'creado_en' => Carbon::now()->subMonths(5)->subWeeks($index),
                'actualizado_en' => Carbon::now()->subWeeks(max(1, 6 - $index)),
                'activo' => $escenario['activo'],
            ]);

            $this->createCreditoCompleto(
                $cliente,
                [
                    'monto_total' => 6000 + ($index * 320),
                    'estado' => $escenario['estado'],
                    'interes' => 11.0 + ($index % 4) * 0.5,
                    'periodicidad' => '14Semanas',
                    'fecha_inicio' => Carbon::now()->subWeeks(20 - ($index * 2)),
                    'duracion_semanas' => 16,
                ],
                [
                    'calle' => 'Calle Plaza Filtro',
                    'numero_ext' => (string) (200 + $index),
                    'numero_int' => null,
                    'monto_mensual' => 1700 + ($index * 60),
                    'colonia' => 'Industrial',
                    'municipio' => 'Azcapotzalco',
                    'estado' => 'Ciudad de Mexico',
                    'cp' => '02080',
                    'tiempo_en_residencia' => (3 + ($index % 3)) . ' anios',
                    'tel_fijo' => '555600' . str_pad((string) (1100 + $index), 4, '0', STR_PAD_LEFT),
                    'tel_cel' => '556600' . str_pad((string) (2100 + $index), 4, '0', STR_PAD_LEFT),
                    'tipo_de_vivienda' => $index % 2 === 0 ? 'Renta' : 'Propia',
                ],
                [
                    [
                        'curp' => sprintf('AVPF%02d0101HDFLAA%02d', $index + 1, $index + 1),
                        'nombre' => 'Aval ' . ($index + 1),
                        'apellido_p' => 'Respaldo',
                        'apellido_m' => 'Caso' . ($index + 1),
                        'fecha_nacimiento' => '1970-01-01',
                        'direccion' => 'Calle Plaza Filtro ' . (200 + $index) . ', Azcapotzalco, CDMX',
                        'telefono' => '557700' . str_pad((string) (3100 + $index), 4, '0', STR_PAD_LEFT),
                        'parentesco' => 'Amigo',
                    ],
                ],
                [
                    'ocupacion' => [
                        'actividad' => 'Comercio al detal',
                        'nombre_empresa' => 'Negocio Caso ' . ($index + 1),
                        'calle' => 'Av. De las Granjas',
                        'numero' => (string) (80 + $index),
                        'colonia' => 'Industrial Vallejo',
                        'municipio' => 'Azcapotzalco',
                        'telefono' => '555880' . str_pad((string) (4100 + $index), 4, '0', STR_PAD_LEFT),
                        'antiguedad' => (2 + ($index % 4)) . ' anios',
                        'monto_percibido' => 4100.00 + ($index * 150),
                        'periodo_pago' => 'semanal',
                    ],
                    'informacion_familiar' => [
                        'nombre_conyuge' => 'Conyuge Caso ' . ($index + 1),
                        'celular_conyuge' => '558800' . str_pad((string) (5100 + $index), 4, '0', STR_PAD_LEFT),
                        'actividad_conyuge' => 'Servicios',
                        'ingresos_semanales_conyuge' => 2300.00 + ($index * 80),
                        'domicilio_trabajo_conyuge' => 'Calzada Camarones ' . (300 + $index) . ', CDMX',
                        'personas_en_domicilio' => 3 + ($index % 3),
                        'dependientes_economicos' => 1 + ($index % 2),
                        'conyuge_vive_con_cliente' => $index % 3 !== 0,
                    ],
                    'garantia' => [
                        'propietario' => $escenario['nombre'] . ' Quintero',
                        'tipo' => 'Inventario',
                        'marca' => 'Generico',
                        'modelo' => 'Caso' . ($index + 1),
                        'num_serie' => 'INV' . str_pad((string) (202400 + $index), 6, '0', STR_PAD_LEFT),
                        'antiguedad' => (1 + ($index % 5)) . ' anios',
                        'monto_garantizado' => 3200.00 + ($index * 120),
                        'foto_url' => 'https://example.com/garantia/promotor-caso-' . ($index + 1) . '.jpg',
                    ],
                    'contrato' => [
                        'tipo_contrato' => 'credito individual',
                        'fecha_generacion' => Carbon::now()->subWeeks(22 - ($index * 2)),
                        'url_s3' => 'https://example.com/contratos/promotor-caso-' . ($index + 1) . '.pdf',
                    ],
                ]
            );
        }
    }

    private function seedDobleDomicilio(Promotor $promotor): void
    {
        $sharedBase = [
            'calle' => 'Av. Azcapotzalco',
            'numero_ext' => '312',
            'numero_int' => 'A',
            'monto_mensual' => 2100,
            'colonia' => 'San Alvaro',
            'municipio' => 'Azcapotzalco',
            'estado' => 'Ciudad de Mexico',
            'cp' => '02020',
            'tiempo_en_residencia' => '3 anios',
            'tel_fijo' => '5556677788',
            'tel_cel' => '5512346677',
            'tipo_de_vivienda' => 'Renta',
        ];

        $clienteJorge = $this->createCliente($promotor, [
            'CURP' => 'RFDD910101MDFLJO18',
            'nombre' => 'Jorge',
            'apellido_p' => 'Dominguez',
            'apellido_m' => 'Alvarez',
            'fecha_nacimiento' => '1991-01-01',
            'cartera_estado' => 'activo',
            'tiene_credito_activo' => true,
            'monto_maximo' => 8800,
            'creado_en' => Carbon::now()->subMonths(4),
            'actualizado_en' => Carbon::now()->subWeeks(2),
            'activo' => true,
        ]);

        $this->createCreditoCompleto(
            $clienteJorge,
            [
                'monto_total' => 8400,
                'estado' => 'desembolsado',
                'interes' => 12.3,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->subWeeks(5),
                'duracion_semanas' => 16,
            ],
            $sharedBase,
            [
                [
                    'curp' => 'RFAV780101HDFLJD07',
                    'nombre' => 'Judith',
                    'apellido_p' => 'Dominguez',
                    'apellido_m' => 'Campos',
                    'fecha_nacimiento' => '1978-01-01',
                    'direccion' => 'Av. Azcapotzalco 312, San Alvaro, CDMX',
                    'telefono' => '5512347700',
                    'parentesco' => 'Madre',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Mantenimiento industrial',
                    'nombre_empresa' => 'Servicios Norte',
                    'calle' => 'Calzada Camarones',
                    'numero' => '150',
                    'colonia' => 'San Alvaro',
                    'municipio' => 'Azcapotzalco',
                    'telefono' => '5556602233',
                    'antiguedad' => '5 anios',
                    'monto_percibido' => 5200.00,
                    'periodo_pago' => 'semanal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Martha Alvarez',
                    'celular_conyuge' => '5511998822',
                    'actividad_conyuge' => 'Enfermera',
                    'ingresos_semanales_conyuge' => 2600.00,
                    'domicilio_trabajo_conyuge' => 'Calz. Camarones 45, CDMX',
                    'personas_en_domicilio' => 4,
                    'dependientes_economicos' => 2,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Jorge Dominguez',
                    'tipo' => 'Electrodomestico',
                    'marca' => 'Mabe',
                    'modelo' => 'EM765',
                    'num_serie' => 'MB202311',
                    'antiguedad' => '1 anio',
                    'monto_garantizado' => 3600.00,
                    'foto_url' => 'https://example.com/garantia/estufa-jorge.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->subWeeks(5)->subDay(),
                    'url_s3' => 'https://example.com/contratos/jorge-dominguez.pdf',
                ],
            ]
        );

        $clienteKarina = $this->createCliente($promotor, [
            'CURP' => 'RFDD920202MDFLKA19',
            'nombre' => 'Karina',
            'apellido_p' => 'Dominguez',
            'apellido_m' => 'Benitez',
            'fecha_nacimiento' => '1992-02-02',
            'cartera_estado' => 'activo',
            'tiene_credito_activo' => true,
            'monto_maximo' => 8600,
            'creado_en' => Carbon::now()->subMonths(3),
            'actualizado_en' => Carbon::now()->subWeeks(1),
            'activo' => true,
        ]);

        $domicilioKarina = $sharedBase;
        $domicilioKarina['calle'] = 'av. azcapotzalco';
        $domicilioKarina['numero_int'] = 'B';
        $domicilioKarina['tiempo_en_residencia'] = '2 anios';
        $domicilioKarina['tel_cel'] = '5512346688';

        $this->createCreditoCompleto(
            $clienteKarina,
            [
                'monto_total' => 7900,
                'estado' => 'aprobado',
                'interes' => 11.9,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->subWeeks(3),
                'duracion_semanas' => 16,
            ],
            $domicilioKarina,
            [
                [
                    'curp' => 'RFAV790303HDFLKB08',
                    'nombre' => 'Belinda',
                    'apellido_p' => 'Benitez',
                    'apellido_m' => 'Cruz',
                    'fecha_nacimiento' => '1979-03-03',
                    'direccion' => 'Av. Azcapotzalco 312, San Alvaro, CDMX',
                    'telefono' => '5512346699',
                    'parentesco' => 'Tia',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Ventas en linea',
                    'nombre_empresa' => 'Catalogos KA',
                    'calle' => 'Av. De las Granjas',
                    'numero' => '200',
                    'colonia' => 'Industrial Vallejo',
                    'municipio' => 'Azcapotzalco',
                    'telefono' => '5556679900',
                    'antiguedad' => '3 anios',
                    'monto_percibido' => 4500.00,
                    'periodo_pago' => 'semanal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Ivan Salgado',
                    'celular_conyuge' => '5511890022',
                    'actividad_conyuge' => 'Tecnico de mantenimiento',
                    'ingresos_semanales_conyuge' => 2700.00,
                    'domicilio_trabajo_conyuge' => 'Calz. de los Gallos 12, CDMX',
                    'personas_en_domicilio' => 3,
                    'dependientes_economicos' => 1,
                    'conyuge_vive_con_cliente' => true,
                ],
                'garantia' => [
                    'propietario' => 'Karina Dominguez',
                    'tipo' => 'Mobiliario',
                    'marca' => 'Liverpool',
                    'modelo' => 'Sala Modular',
                    'num_serie' => 'SAL202307',
                    'antiguedad' => '2 anios',
                    'monto_garantizado' => 4800.00,
                    'foto_url' => 'https://example.com/garantia/sala-karina.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->subWeeks(3)->subDay(),
                    'url_s3' => 'https://example.com/contratos/karina-dominguez.pdf',
                ],
            ]
        );

        $clienteLaura = $this->createCliente($promotor, [
            'CURP' => 'RFDD930303HDFLLA20',
            'nombre' => 'Laura',
            'apellido_p' => 'Alvarado',
            'apellido_m' => 'Campos',
            'fecha_nacimiento' => '1993-03-03',
            'cartera_estado' => 'inactivo',
            'tiene_credito_activo' => false,
            'monto_maximo' => 7200,
            'creado_en' => Carbon::now()->subMonths(1),
            'actualizado_en' => Carbon::now(),
            'activo' => false,
        ]);

        $this->createCreditoCompleto(
            $clienteLaura,
            [
                'monto_total' => 6800,
                'estado' => 'prospectado',
                'interes' => 12.8,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->addWeeks(1),
                'duracion_semanas' => 14,
            ],
            [
                'calle' => 'AV. AZCAPOTZALCO',
                'numero_ext' => '312',
                'numero_int' => 'C',
                'monto_mensual' => 2150,
                'colonia' => 'San Alvaro',
                'municipio' => 'Azcapotzalco',
                'estado' => 'Ciudad de Mexico',
                'cp' => '02020',
                'tiempo_en_residencia' => '0 anios',
                'tel_fijo' => null,
                'tel_cel' => '5512346611',
                'tipo_de_vivienda' => 'Renta',
            ],
            [
                [
                    'curp' => 'RFAV850101HDFLAC09',
                    'nombre' => 'Carmen',
                    'apellido_p' => 'Campos',
                    'apellido_m' => 'Lopez',
                    'fecha_nacimiento' => '1985-01-01',
                    'direccion' => 'Av. Azcapotzalco 312, San Alvaro, CDMX',
                    'telefono' => '5512346622',
                    'parentesco' => 'Madre',
                ],
            ],
            [
                'ocupacion' => [
                    'actividad' => 'Reposteria por encargo',
                    'nombre_empresa' => 'Dulces Laura',
                    'calle' => 'Calle Cedro',
                    'numero' => '45',
                    'colonia' => 'San Alvaro',
                    'municipio' => 'Azcapotzalco',
                    'telefono' => '5556604488',
                    'antiguedad' => '1 anio',
                    'monto_percibido' => 3200.00,
                    'periodo_pago' => 'semanal',
                ],
                'informacion_familiar' => [
                    'nombre_conyuge' => 'Sin registro',
                    'celular_conyuge' => '5510000000',
                    'actividad_conyuge' => 'Independiente',
                    'ingresos_semanales_conyuge' => 0.00,
                    'domicilio_trabajo_conyuge' => 'N/A',
                    'personas_en_domicilio' => 2,
                    'dependientes_economicos' => 1,
                    'conyuge_vive_con_cliente' => false,
                ],
                'garantia' => [
                    'propietario' => 'Laura Alvarado',
                    'tipo' => 'Electrodomestico',
                    'marca' => 'KitchenAid',
                    'modelo' => 'Artisan',
                    'num_serie' => 'KA202401',
                    'antiguedad' => '0 anios',
                    'monto_garantizado' => 2800.00,
                    'foto_url' => 'https://example.com/garantia/batidora-laura.jpg',
                ],
                'contrato' => [
                    'tipo_contrato' => 'credito individual',
                    'fecha_generacion' => Carbon::now()->addWeeks(1)->subDay(),
                    'url_s3' => 'https://example.com/contratos/laura-alvarado.pdf',
                ],
            ]
        );
    }

    private function seedBloqueoTiempoRecreditos(Promotor $promotor): void
    {
        $escenarios = [
            [
                'curp' => 'RFRQ940404MDFLMA21',
                'nombre' => 'Manuel',
                'estado' => 'desembolsado',
                'cartera' => 'activo',
                'tiene_activo' => true,
                'fecha' => Carbon::now()->subWeeks(4),
                'periodicidad' => '14Semanas',
            ],
            [
                'curp' => 'RFRQ950505MDFLNA22',
                'nombre' => 'Natalia',
                'estado' => 'vencido',
                'cartera' => 'moroso',
                'tiene_activo' => true,
                'fecha' => Carbon::now()->subWeeks(20),
                'periodicidad' => '14Semanas',
            ],
            [
                'curp' => 'RFRQ960606HDFLOA23',
                'nombre' => 'Olivia',
                'estado' => 'liquidado',
                'cartera' => 'regularizado',
                'tiene_activo' => false,
                'fecha' => Carbon::now()->subWeeks(28),
                'periodicidad' => '14Semanas',
            ],
        ];

        foreach ($escenarios as $index => $escenario) {
            $cliente = $this->createCliente($promotor, [
                'CURP' => $escenario['curp'],
                'nombre' => $escenario['nombre'],
                'apellido_p' => 'Ramirez',
                'apellido_m' => 'Caso' . ($index + 1),
                'fecha_nacimiento' => '1987-08-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'cartera_estado' => $escenario['cartera'],
                'tiene_credito_activo' => $escenario['tiene_activo'],
                'monto_maximo' => 9400 + ($index * 450),
                'creado_en' => Carbon::now()->subMonths(6)->subWeeks($index),
                'actualizado_en' => Carbon::now()->subWeeks(1),
                'activo' => $escenario['tiene_activo'],
            ]);

            $this->createCreditoCompleto(
                $cliente,
                [
                    'monto_total' => 8600 + ($index * 380),
                    'estado' => $escenario['estado'],
                    'interes' => 9.5 + ($index * 0.5),
                    'periodicidad' => $escenario['periodicidad'],
                    'fecha_inicio' => $escenario['fecha'],
                    'duracion_semanas' => 14,
                ],
                [
                    'calle' => 'Calle Recredito',
                    'numero_ext' => (string) (60 + $index),
                    'numero_int' => null,
                    'monto_mensual' => 1900 + ($index * 120),
                    'colonia' => 'Anahuac',
                    'municipio' => 'Miguel Hidalgo',
                    'estado' => 'Ciudad de Mexico',
                    'cp' => '11320',
                    'tiempo_en_residencia' => (3 + $index) . ' anios',
                    'tel_fijo' => null,
                    'tel_cel' => '55155667' . str_pad((string) (80 + $index), 2, '0', STR_PAD_LEFT),
                    'tipo_de_vivienda' => $index === 1 ? 'Familiar' : 'Propia',
                ],
                [
                    [
                        'curp' => sprintf('AVRQ%02d0202HDFLAA%02d', $index + 1, $index + 1),
                        'nombre' => $index === 1 ? 'Rafael' : 'Juana',
                        'apellido_p' => 'Ramirez',
                        'apellido_m' => 'Apoyo',
                        'fecha_nacimiento' => '1979-02-02',
                        'direccion' => 'Calle Recredito ' . (60 + $index) . ', Miguel Hidalgo, CDMX',
                        'telefono' => '552210' . str_pad((string) (4400 + $index), 4, '0', STR_PAD_LEFT),
                        'parentesco' => 'Padre',
                    ],
                ],
                [
                    'ocupacion' => [
                        'actividad' => $index === 2 ? 'Servicios de diseno' : 'Comercio minorista',
                        'nombre_empresa' => 'Negocio Recredito ' . ($index + 1),
                        'calle' => 'Av. Marina Nacional',
                        'numero' => (string) (100 + $index),
                        'colonia' => 'Anahuac',
                        'municipio' => 'Miguel Hidalgo',
                        'telefono' => '555620' . str_pad((string) (3300 + $index), 4, '0', STR_PAD_LEFT),
                        'antiguedad' => (2 + $index) . ' anios',
                        'monto_percibido' => 5000.00 + ($index * 250),
                        'periodo_pago' => 'semanal',
                    ],
                    'informacion_familiar' => [
                        'nombre_conyuge' => 'Conyuge Recredito ' . ($index + 1),
                        'celular_conyuge' => '552290' . str_pad((string) (5500 + $index), 4, '0', STR_PAD_LEFT),
                        'actividad_conyuge' => $index === 0 ? 'Administracion' : 'Ventas',
                        'ingresos_semanales_conyuge' => 2600.00 + ($index * 120),
                        'domicilio_trabajo_conyuge' => 'Av. Marina Nacional ' . (200 + $index) . ', CDMX',
                        'personas_en_domicilio' => 4,
                        'dependientes_economicos' => $index === 2 ? 1 : 2,
                        'conyuge_vive_con_cliente' => true,
                    ],
                    'garantia' => [
                        'propietario' => $escenario['nombre'] . ' Ramirez',
                        'tipo' => 'Herramienta de trabajo',
                        'marca' => $index === 2 ? 'Apple' : 'Makita',
                        'modelo' => $index === 2 ? 'MacBook Air' : 'Taladro MT',
                        'num_serie' => 'REQ' . str_pad((string) (202410 + $index), 6, '0', STR_PAD_LEFT),
                        'antiguedad' => (1 + $index) . ' anios',
                        'monto_garantizado' => 4200.00 + ($index * 450),
                        'foto_url' => 'https://example.com/garantia/recredito-' . ($index + 1) . '.jpg',
                    ],
                    'contrato' => [
                        'tipo_contrato' => 'credito individual',
                        'fecha_generacion' => Carbon::now()->subWeeks(30 - ($index * 4)),
                        'url_s3' => 'https://example.com/contratos/recredito-' . ($index + 1) . '.pdf',
                    ],
                ]
            );
        }
    }
}

