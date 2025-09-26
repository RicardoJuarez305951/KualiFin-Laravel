<?php

namespace Database\Seeders;

use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * SeederFiltrosBasicos crea 20 clientes asociados al promotor promotor@example.com
 * y datos auxiliares que permiten disparar los filtros principales del
 * FiltrosController.
 *
 * Ejecución: php artisan db:seed --class=SeederFiltrosBasicos
 *
 * Instrucciones para probar los filtros:
 * - FILTER_CURP_UNICA: El cliente "Bruno Curp Duplicado" debe capturar manualmente
 *   la CURP BFB-CURP-UNICA-001 en el formulario al evaluarse
 *   ['cliente' => ['curp' => 'BFB-CURP-UNICA-001']]. El seeder deja preparada a
 *   "Ana Curp Base" como registro base con esa CURP para disparar el filtro.
 * 
 * - FILTER_DOBLE_FIRMA_AVAL: El aval "Rosa Aval Compromiso" (CURP BFB-AVAL-DOBLE-001)
 *   ya respalda los créditos activos de "Carla Aval Primera" y "Diego Aval Segundo".
 *   Al evaluar a "Elena Aval Candidata" capture la CURP BFB-AVAL-DOBLE-001 en el
 *   apartado del aval ['aval' => ['curp' => 'BFB-AVAL-DOBLE-001']].
 * 
 * - FILTER_OTRA_PLAZA: "Fernando Plaza Local" pertenece al promotor principal.
 *   Evalúelo enviando en el contexto ['promotor_id' => $promotorAlterno->id,
 *   'supervisor_id' => $promotorAlterno->supervisor_id] donde $promotorAlterno es el
 *   promotor del correo promotor.secundario@example.com.
 * 
 * - FILTER_DOBLE_DOMICILIO: "Irene Domicilio Candidata" debe capturar en el
 *   formulario de contacto la dirección de Av. de las Flores 123, interior B,
 *   colonia Jardines del Centro, Miguel Hidalgo, CP 11510 y los mismos datos
 *   telefónicos para activar el filtro. Use los campos contacto[...] y el campo
 *   credito['fecha_inicio'] de la solicitud.
 * 
 * - FILTER_BLOQUEO_TIEMPO_REACREDITOS: Para semanas insuficientes evalúe a
 *   "Jorge Recredito Temprano" con ['tipo_solicitud' => 'recredito'].
 *   Para atrasos utilice a "Karla Recredito Morosa" con el mismo contexto.
 *   "Luis Recredito Listo" sirve como caso aprobado.
 */
class SeederFiltrosBasicos extends Seeder
{
    private const PASSWORD = '12345';

    private const AVAL_CURP_DOBLE_FIRMA = 'BFB-AVAL-DOBLE-001';

    private const CLIENTS = [
        [
            'nombre' => 'Ana',
            'apellido_p' => 'Curp',
            'apellido_m' => 'Base',
            'CURP' => 'BFB-CURP-UNICA-001',
        ],
        [
            'nombre' => 'Bruno',
            'apellido_p' => 'Curp',
            'apellido_m' => 'Duplicado',
            'CURP' => 'BFB-CURP-UNICA-002',
        ],
        [
            'nombre' => 'Carla',
            'apellido_p' => 'Aval',
            'apellido_m' => 'Primera',
            'CURP' => 'BFB-AVA-CLI-101',
            'tiene_credito_activo' => true,
            'creditos' => [
                [
                    'estado' => 'desembolsado',
                    'monto_total' => 15000,
                    'interes' => 16.5,
                    'periodicidad' => 'Semanal 13',
                    'fecha_inicio_weeks_ago' => 30,
                    'fecha_final_weeks_ahead' => 10,
                    'avales' => [
                        [
                            'CURP' => self::AVAL_CURP_DOBLE_FIRMA,
                            'nombre' => 'Rosa',
                            'apellido_p' => 'Aval',
                            'apellido_m' => 'Compromiso',
                            'fecha_nacimiento' => '1978-05-12',
                            'direccion' => 'Calle Compromiso 123, Ciudad de México',
                            'telefono' => '5553001001',
                            'parentesco' => 'Hermana',
                        ],
                    ],
                ],
            ],
        ],
        [
            'nombre' => 'Diego',
            'apellido_p' => 'Aval',
            'apellido_m' => 'Segundo',
            'CURP' => 'BFB-AVA-CLI-102',
            'tiene_credito_activo' => true,
            'creditos' => [
                [
                    'estado' => 'supervisado',
                    'monto_total' => 14200,
                    'interes' => 15.0,
                    'periodicidad' => 'Semanal 14',
                    'fecha_inicio_weeks_ago' => 26,
                    'fecha_final_weeks_ahead' => 8,
                    'avales' => [
                        [
                            'CURP' => self::AVAL_CURP_DOBLE_FIRMA,
                            'nombre' => 'Rosa',
                            'apellido_p' => 'Aval',
                            'apellido_m' => 'Compromiso',
                            'fecha_nacimiento' => '1978-05-12',
                            'direccion' => 'Calle Compromiso 123, Ciudad de México',
                            'telefono' => '5553001001',
                            'parentesco' => 'Hermana',
                        ],
                    ],
                ],
            ],
        ],
        [
            'nombre' => 'Elena',
            'apellido_p' => 'Aval',
            'apellido_m' => 'Candidata',
            'CURP' => 'BFB-AVA-CLI-103',
        ],
        [
            'nombre' => 'Fernando',
            'apellido_p' => 'Plaza',
            'apellido_m' => 'Local',
            'CURP' => 'BFB-PLZ-CLI-201',
        ],
        [
            'nombre' => 'Gabriela',
            'apellido_p' => 'Domicilio',
            'apellido_m' => 'Uno',
            'CURP' => 'BFB-DOM-CLI-301',
            'tiene_credito_activo' => true,
            'creditos' => [
                [
                    'estado' => 'desembolsado',
                    'monto_total' => 11000,
                    'interes' => 14.5,
                    'periodicidad' => 'Semanal 13',
                    'fecha_inicio_weeks_ago' => 40,
                    'fecha_final_weeks_ahead' => 20,
                    'contacto' => [
                        'calle' => 'Av. de las Flores',
                        'numero_ext' => '123',
                        'numero_int' => 'B',
                        'monto_mensual' => 2500,
                        'colonia' => 'Jardines del Centro',
                        'municipio' => 'Miguel Hidalgo',
                        'estado' => 'Ciudad de México',
                        'cp' => '11510',
                        'tiempo_en_residencia' => '5 años',
                        'tel_fijo' => '5553002001',
                        'tel_cel' => '5512340001',
                        'tipo_de_vivienda' => 'Propia',
                    ],
                ],
            ],
        ],
        [
            'nombre' => 'Hector',
            'apellido_p' => 'Domicilio',
            'apellido_m' => 'Dos',
            'CURP' => 'BFB-DOM-CLI-302',
            'tiene_credito_activo' => true,
            'creditos' => [
                [
                    'estado' => 'supervisado',
                    'monto_total' => 10800,
                    'interes' => 13.5,
                    'periodicidad' => 'Semanal 14',
                    'fecha_inicio_weeks_ago' => 32,
                    'fecha_final_weeks_ahead' => 18,
                    'contacto' => [
                        'calle' => 'Av. de las Flores',
                        'numero_ext' => '123',
                        'numero_int' => 'B',
                        'monto_mensual' => 2400,
                        'colonia' => 'Jardines del Centro',
                        'municipio' => 'Miguel Hidalgo',
                        'estado' => 'Ciudad de México',
                        'cp' => '11510',
                        'tiempo_en_residencia' => '4 años',
                        'tel_fijo' => '5553002002',
                        'tel_cel' => '5512340002',
                        'tipo_de_vivienda' => 'Renta',
                    ],
                ],
            ],
        ],
        [
            'nombre' => 'Irene',
            'apellido_p' => 'Domicilio',
            'apellido_m' => 'Candidata',
            'CURP' => 'BFB-DOM-CLI-303',
        ],
        [
            'nombre' => 'Jorge',
            'apellido_p' => 'Recredito',
            'apellido_m' => 'Temprano',
            'CURP' => 'BFB-REA-CLI-401',
            'creditos' => [
                [
                    'estado' => 'liquidado',
                    'periodicidad' => 'Semanal 13',
                    'monto_total' => 9800,
                    'interes' => 11.5,
                    'fecha_inicio_weeks_ago' => 6,
                    'fecha_final_weeks_ahead' => 1,
                ],
            ],
        ],
        [
            'nombre' => 'Karla',
            'apellido_p' => 'Recredito',
            'apellido_m' => 'Morosa',
            'CURP' => 'BFB-REA-CLI-402',
            'cartera_estado' => 'moroso',
            'creditos' => [
                [
                    'estado' => 'vencido',
                    'periodicidad' => 'Semanal 14',
                    'monto_total' => 10500,
                    'interes' => 12.5,
                    'fecha_inicio_weeks_ago' => 20,
                    'fecha_final_weeks_ahead' => 5,
                ],
            ],
        ],
        [
            'nombre' => 'Luis',
            'apellido_p' => 'Recredito',
            'apellido_m' => 'Listo',
            'CURP' => 'BFB-REA-CLI-403',
            'creditos' => [
                [
                    'estado' => 'liquidado',
                    'periodicidad' => 'Semanal 13',
                    'monto_total' => 11200,
                    'interes' => 12.0,
                    'fecha_inicio_weeks_ago' => 12,
                    'fecha_final_weeks_ahead' => 2,
                ],
            ],
        ],
        [
            'nombre' => 'Monica',
            'apellido_p' => 'Base',
            'apellido_m' => 'Uno',
            'CURP' => 'BFB-BASE-CLI-501',
        ],
        [
            'nombre' => 'Nestor',
            'apellido_p' => 'Base',
            'apellido_m' => 'Dos',
            'CURP' => 'BFB-BASE-CLI-502',
        ],
        [
            'nombre' => 'Olga',
            'apellido_p' => 'Base',
            'apellido_m' => 'Tres',
            'CURP' => 'BFB-BASE-CLI-503',
        ],
        [
            'nombre' => 'Pablo',
            'apellido_p' => 'Base',
            'apellido_m' => 'Cuatro',
            'CURP' => 'BFB-BASE-CLI-504',
        ],
        [
            'nombre' => 'Quetzal',
            'apellido_p' => 'Base',
            'apellido_m' => 'Cinco',
            'CURP' => 'BFB-BASE-CLI-505',
        ],
        [
            'nombre' => 'Rosa',
            'apellido_p' => 'Base',
            'apellido_m' => 'Seis',
            'CURP' => 'BFB-BASE-CLI-506',
        ],
        [
            'nombre' => 'Sergio',
            'apellido_p' => 'Base',
            'apellido_m' => 'Siete',
            'CURP' => 'BFB-BASE-CLI-507',
        ],
        [
            'nombre' => 'Teresa',
            'apellido_p' => 'Base',
            'apellido_m' => 'Ocho',
            'CURP' => 'BFB-BASE-CLI-508',
        ],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->purgeExistingData();

            $hierarchy = $this->ensureHierarchy();
            $promotor = $hierarchy['promotor'];

            foreach (self::CLIENTS as $clientData) {
                $creditos = $clientData['creditos'] ?? [];
                unset($clientData['creditos']);

                $cliente = $this->createCliente($promotor, $clientData);

                foreach ($creditos as $creditData) {
                    $this->createCreditoConRelacionados($cliente, $creditData);
                }
            }
        });
    }

    private function purgeExistingData(): void
    {
        $curps = array_unique(array_map(static fn ($cliente) => $cliente['CURP'], self::CLIENTS));

        $clientes = Cliente::whereIn('CURP', $curps)->get();

        if ($clientes->isNotEmpty()) {
            $clienteIds = $clientes->pluck('id');
            $creditoIds = Credito::whereIn('cliente_id', $clienteIds)->pluck('id');

            if ($creditoIds->isNotEmpty()) {
                DatoContacto::whereIn('credito_id', $creditoIds)->delete();
                Aval::whereIn('credito_id', $creditoIds)->delete();
                Credito::whereIn('id', $creditoIds)->delete();
            }

            Cliente::whereIn('id', $clienteIds)->delete();
        }

        Aval::where('CURP', self::AVAL_CURP_DOBLE_FIRMA)->delete();
    }

    private function ensureHierarchy(): array
    {
        $ejecutivoUser = $this->ensureUser('ejecutivo@example.com', 'Eva Directora', 'ejecutivo', '5553000001');
        $ejecutivo = Ejecutivo::updateOrCreate(
            ['user_id' => $ejecutivoUser->id],
            ['nombre' => 'Eva', 'apellido_p' => 'Directora', 'apellido_m' => 'Central']
        );

        $supervisorUser = $this->ensureUser('supervisor@example.com', 'Samuel Supervisor', 'supervisor', '5553000002');
        $supervisor = Supervisor::updateOrCreate(
            ['user_id' => $supervisorUser->id],
            ['ejecutivo_id' => $ejecutivo->id, 'nombre' => 'Samuel', 'apellido_p' => 'Supervisor', 'apellido_m' => 'Principal']
        );

        $promotorPrincipalUser = $this->ensureUser('promotor@example.com', 'Paola Promotora', 'promotor', '5553000003');
        $promotorPrincipal = Promotor::updateOrCreate(
            ['user_id' => $promotorPrincipalUser->id],
            [
                'supervisor_id' => $supervisor->id,
                'nombre' => 'Paola',
                'apellido_p' => 'Promotora',
                'apellido_m' => 'Principal',
                'venta_maxima' => 20000,
                'colonia' => 'Centro Histórico',
                'venta_proyectada_objetivo' => 12000,
                'bono' => 800,
                'dia_de_pago' => 'Lunes',
                'hora_de_pago' => '08:30',
            ]
        );

        $promotorAlternoUser = $this->ensureUser('promotor.secundario@example.com', 'Alberto Alterno', 'promotor', '5553000004');
        $promotorAlterno = Promotor::updateOrCreate(
            ['user_id' => $promotorAlternoUser->id],
            [
                'supervisor_id' => $supervisor->id,
                'nombre' => 'Alberto',
                'apellido_p' => 'Alterno',
                'apellido_m' => 'Plaza',
                'venta_maxima' => 15000,
                'colonia' => 'Azcapotzalco',
                'venta_proyectada_objetivo' => 9000,
                'bono' => 600,
                'dia_de_pago' => 'Martes',
                'hora_de_pago' => '10:00',
            ]
        );

        return [
            'ejecutivo' => $ejecutivo,
            'supervisor' => $supervisor,
            'promotor' => $promotorPrincipal,
            'promotor_alterno' => $promotorAlterno,
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
            'cartera_estado' => 'vigente',
            'monto_maximo' => 8000,
            'creado_en' => Carbon::now()->subMonths(2),
            'actualizado_en' => Carbon::now()->subWeeks(3),
            'activo' => true,
        ];

        return Cliente::create(array_merge($defaults, $data));
    }

    private function createCreditoConRelacionados(Cliente $cliente, array $data): void
    {
        $contacto = $data['contacto'] ?? null;
        $avales = $data['avales'] ?? [];

        $attributes = Arr::except($data, ['contacto', 'avales', 'fecha_inicio_weeks_ago', 'fecha_final_weeks_ahead']);
        $attributes['cliente_id'] = $cliente->id;

        if (isset($data['fecha_inicio_weeks_ago'])) {
            $attributes['fecha_inicio'] = Carbon::now()->subWeeks($data['fecha_inicio_weeks_ago'])->toDateString();
        }

        if (isset($data['fecha_final_weeks_ahead'])) {
            $attributes['fecha_final'] = Carbon::now()->addWeeks($data['fecha_final_weeks_ahead'])->toDateString();
        }

        $credito = Credito::create(array_merge([
            'monto_total' => 7500,
            'estado' => 'liquidado',
            'interes' => 10.5,
            'periodicidad' => 'Semanal 13',
            'fecha_inicio' => Carbon::now()->subWeeks(10)->toDateString(),
            'fecha_final' => Carbon::now()->addWeeks(5)->toDateString(),
        ], $attributes));

        if ($contacto) {
            DatoContacto::create(array_merge([
                'credito_id' => $credito->id,
                'calle' => 'Calle Uno',
                'numero_ext' => '100',
                'numero_int' => null,
                'monto_mensual' => 2000,
                'colonia' => 'Centro',
                'municipio' => 'Cuauhtémoc',
                'estado' => 'Ciudad de México',
                'cp' => '06000',
                'tiempo_en_residencia' => '2 años',
                'tel_fijo' => '5550000000',
                'tel_cel' => '5511111111',
                'tipo_de_vivienda' => 'Renta',
            ], $contacto));
        }

        foreach ($avales as $aval) {
            Aval::create(array_merge([
                'credito_id' => $credito->id,
                'CURP' => self::AVAL_CURP_DOBLE_FIRMA,
                'nombre' => 'Rosa',
                'apellido_p' => 'Aval',
                'apellido_m' => 'Compromiso',
                'fecha_nacimiento' => '1978-05-12',
                'direccion' => 'Calle Compromiso 123, Ciudad de México',
                'telefono' => '5553001001',
                'parentesco' => 'Hermana',
            ], $aval));
        }
    }
}
