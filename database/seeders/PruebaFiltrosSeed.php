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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Escenarios estaticos para validar todos los filtros de FiltrosController.
 *
 * Resumen de ejecucion rapida:
 * - curp_unica: solicitar un credito nuevo con CURP 'PFILT-CURP-UNICA-001'.
 * - doble_firma_aval: cliente 'PFILT-AVAL-CANDIDATO-003' con aval CURP 'PFILT-AVAL-CURP-001'.
 * - credito_en_falla: cliente 'PFILT-CREDITO-FALLA-001', tipo de solicitud 'nuevo'.
 * - credito_activo: cliente 'PFILT-CREDITO-ACTIVO-001', tipo de solicitud 'nuevo'.
 * - otra_plaza: cliente 'PFILT-OTRA-PLAZA-001' evaluado por promotor@example.com.
 * - bloqueo_falla_promotora_5: cliente 'PFILT-PROMOTOR-FALLA-004' con tipo 'nuevo'.
 * - doble_domicilio: cliente 'PFILT-DOMICILIO-NUEVO-003' usando el domicilio indicado.
 * - bloqueo_tiempo_recreditos: clientes 'PFILT-REACREDITO-*' con tipo 'recredito' segun instrucciones.
 */
class PruebaFiltrosSeed extends Seeder
{
    private const PASSWORD = '12345';

    private const CLIENT_CURPS = [
        'PFILT-CURP-UNICA-001', // Cliente base y duplicado para FILTER_CURP_UNICA
        'PFILT-AVAL-ACTIVO-001',
        'PFILT-AVAL-ACTIVO-002',
        'PFILT-AVAL-CANDIDATO-003',
        'PFILT-CREDITO-FALLA-001',
        'PFILT-CREDITO-ACTIVO-001',
        'PFILT-OTRA-PLAZA-001',
        'PFILT-PROMOTOR-FALLA-001',
        'PFILT-PROMOTOR-FALLA-002',
        'PFILT-PROMOTOR-FALLA-003',
        'PFILT-PROMOTOR-FALLA-004',
        'PFILT-DOMICILIO-ACT-001',
        'PFILT-DOMICILIO-ACT-002',
        'PFILT-DOMICILIO-NUEVO-003',
        'PFILT-REACREDITO-TEMPRANO-001',
        'PFILT-REACREDITO-EN-FALLA-002',
        'PFILT-REACREDITO-APROBADO-003',
    ];

    private const AVAL_CURP_DOBLE_FIRMA = 'PFILT-AVAL-CURP-001';

    private const DOMICILIO_CALLE = 'Calle Filtros';
    private const DOMICILIO_NUM_EXT = '123';
    private const DOMICILIO_COLONIA = 'Colonia Pruebas Filtros';
    private const DOMICILIO_MUNICIPIO = 'Alvaro Obregon';
    private const DOMICILIO_CP = '01010';

    public function run(): void
    {
        DB::transaction(function () {
            $this->resetScenarioData();

            $principal = $this->ensurePrincipalHierarchy();
            $secundario = $this->ensureSecondaryHierarchy($principal['ejecutivo']);

            $this->seedCurpUnicaEscenario($principal['promotor']);
            $this->seedDobleFirmaAvalEscenario($principal['promotor']);
            $this->seedCreditoEnFallaEscenario($principal['promotor']);
            $this->seedCreditoActivoEscenario($principal['promotor']);
            $this->seedOtraPlazaEscenario($principal['promotor'], $secundario['promotor']);
            $this->seedBloqueoFallaPromotoraEscenario($principal['promotor']);
            $this->seedDobleDomicilioEscenario($principal['promotor']);
            $this->seedBloqueoTiempoRecreditosEscenario($principal['promotor']);
        });
    }

    private function resetScenarioData(): void
    {
        $curps = array_unique(self::CLIENT_CURPS);

        $clientes = Cliente::whereIn('CURP', $curps)->get();
        if ($clientes->isEmpty()) {
            Aval::where('CURP', self::AVAL_CURP_DOBLE_FIRMA)->delete();
            return;
        }

        $creditoIds = Credito::whereIn('cliente_id', $clientes->pluck('id'))->pluck('id');

        if ($creditoIds->isNotEmpty()) {
            DatoContacto::whereIn('credito_id', $creditoIds)->delete();
            Aval::whereIn('credito_id', $creditoIds)->delete();
            Credito::whereIn('id', $creditoIds)->delete();
        }

        Cliente::whereIn('id', $clientes->pluck('id'))->delete();
        Aval::where('CURP', self::AVAL_CURP_DOBLE_FIRMA)->delete();
    }

        private function ensurePrincipalHierarchy(): array
    {
        $ejecutivoUser = $this->ensureUser('ejecutivo@example.com', 'Alonso Rivera', '5551000002', 'ejecutivo');
        $ejecutivo = Ejecutivo::updateOrCreate(
            ['user_id' => $ejecutivoUser->id],
            ['nombre' => 'Alonso', 'apellido_p' => 'Rivera', 'apellido_m' => 'Gomez']
        );

        $supervisorUser = $this->ensureUser('supervisor@example.com', 'Laura Hernandez', '5551000003', 'supervisor');
        $supervisor = Supervisor::updateOrCreate(
            ['user_id' => $supervisorUser->id],
            ['ejecutivo_id' => $ejecutivo->id, 'nombre' => 'Laura', 'apellido_p' => 'Hernandez', 'apellido_m' => 'Vega']
        );

        $promotorUser = $this->ensureUser('promotor@example.com', 'Mario Torres', '5551000004', 'promotor');
        $promotor = Promotor::updateOrCreate(
            ['user_id' => $promotorUser->id],
            [
                'supervisor_id' => $supervisor->id,
                'nombre' => 'Mario',
                'apellido_p' => 'Torres',
                'apellido_m' => 'Luna',
                'venta_maxima' => 20000,
                'colonia' => 'Centro CDMX',
                'venta_proyectada_objetivo' => 12000,
                'bono' => 800,
                'dia_de_pago' => 'Lunes',
                'hora_de_pago' => '08:00:00',
            ]
        );

        return compact('ejecutivo', 'supervisor', 'promotor');
    }


        private function ensureSecondaryHierarchy(Ejecutivo $ejecutivo): array
    {
        $supervisorUser = $this->ensureUser('supervisor.filtros@example.com', 'Sofia Duarte', '5551000013', 'supervisor');
        $supervisor = Supervisor::updateOrCreate(
            ['user_id' => $supervisorUser->id],
            ['ejecutivo_id' => $ejecutivo->id, 'nombre' => 'Sofia', 'apellido_p' => 'Duarte', 'apellido_m' => 'Reyes']
        );

        $promotorUser = $this->ensureUser('promotor.filtros.secundario@example.com', 'Diego Salgado', '5551000014', 'promotor');
        $promotor = Promotor::updateOrCreate(
            ['user_id' => $promotorUser->id],
            [
                'supervisor_id' => $supervisor->id,
                'nombre' => 'Diego',
                'apellido_p' => 'Salgado',
                'apellido_m' => 'Campos',
                'venta_maxima' => 15000,
                'colonia' => 'Centro CDMX',
                'venta_proyectada_objetivo' => 9000,
                'bono' => 600,
                'dia_de_pago' => 'Martes',
                'hora_de_pago' => '09:00:00',
            ]
        );

        return compact('supervisor', 'promotor');
    }


    private function ensureUser(string $email, string $name, string $telefono, string $role): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(self::PASSWORD),
                'telefono' => $telefono,
                'rol' => $role,
            ]
        );

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($role);
        }

        return $user;
    }

    /**
     * FILTER_CURP_UNICA
     * Instrucciones: solicitar un credito nuevo para cualquier cliente con CURP 'PFILT-CURP-UNICA-001'.
     * Expectativa: el filtro rechaza por CURP duplicada.
     */
    private function seedCurpUnicaEscenario(Promotor $promotor): void
    {
        $baseData = [
            'promotor_id' => $promotor->id,
            'fecha_nacimiento' => '1990-05-10',
            'monto_maximo' => 6000,
            'tiene_credito_activo' => false,
            'cliente_estado' => 'inactivo',
            'activo' => true,
        ];

        Cliente::create(array_merge($baseData, [
            'CURP' => 'PFILT-CURP-UNICA-001',
            'nombre' => 'Ana',
            'apellido_p' => 'Rivas',
            'apellido_m' => 'Montoya',
        ]));

        Cliente::create(array_merge($baseData, [
            'CURP' => 'PFILT-CURP-UNICA-001',
            'nombre' => 'Ana',
            'apellido_p' => 'Rivas',
            'apellido_m' => 'Navarro',
        ]));
    }


    /**
     * FILTER_DOBLE_FIRMA_AVAL
     * Instrucciones: evaluar al cliente 'PFILT-AVAL-CANDIDATO-003' enviando en 'aval.curp' el valor 'PFILT-AVAL-CURP-001'.
     * Expectativa: el filtro rechaza porque el aval ya participa en dos creditos activos.
     */
    private function seedDobleFirmaAvalEscenario(Promotor $promotor): void
    {
        $creditosActivos = [
            [
                'curp' => 'PFILT-AVAL-ACTIVO-001',
                'nombre' => 'Carlos',
                'apellido' => 'Quiroga',
                'estado_credito' => 'desembolsado',
            ],
            [
                'curp' => 'PFILT-AVAL-ACTIVO-002',
                'nombre' => 'Carolina',
                'apellido' => 'Quiroga',
                'estado_credito' => 'supervisado',
            ],
        ];

        foreach ($creditosActivos as $data) {
            $estados = $this->resolverEstados($data['estado_credito']);

            $cliente = Cliente::create(array_merge([
                'promotor_id' => $promotor->id,
                'CURP' => $data['curp'],
                'nombre' => $data['nombre'],
                'apellido_p' => $data['apellido'],
                'apellido_m' => 'Valdez',
                'fecha_nacimiento' => '1988-03-15',
                'monto_maximo' => 8200,
                'activo' => true,
            ], $estados));

            $credit = Credito::create([
                'cliente_id' => $cliente->id,
                'monto_total' => 7000,
                'estado' => $data['estado_credito'],
                'interes' => 12.5,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->subWeeks(16)->toDateString(),
                'fecha_final' => Carbon::now()->addWeeks(2)->toDateString(),
            ]);

            DatoContacto::create([
                'credito_id' => $credit->id,
                'calle' => 'Calle Aval ' . $data['apellido'],
                'numero_ext' => '10',
                'numero_int' => null,
                'monto_mensual' => 1500,
                'colonia' => 'Residencial Aval',
                'municipio' => 'Cuauhtemoc',
                'estado' => 'CDMX',
                'cp' => '02020',
                'tiempo_en_residencia' => '4 anios',
                'tel_fijo' => null,
                'tel_cel' => '5512345678',
                'tipo_de_vivienda' => 'Propia',
            ]);

            Aval::create([
                'CURP' => self::AVAL_CURP_DOBLE_FIRMA,
                'credito_id' => $credit->id,
                'nombre' => 'Rafael',
                'apellido_p' => 'Avalos',
                'apellido_m' => 'Mejia',
                'fecha_nacimiento' => '1975-09-09',
                'direccion' => 'Av. Unidad 123',
                'telefono' => '5598765432',
                'parentesco' => 'Familiar',
            ]);
        }

        Cliente::create([
            'promotor_id' => $promotor->id,
            'CURP' => 'PFILT-AVAL-CANDIDATO-003',
            'nombre' => 'Claudia',
            'apellido_p' => 'Serrano',
            'apellido_m' => 'Mendoza',
            'fecha_nacimiento' => '1992-07-07',
            'tiene_credito_activo' => false,
            'cliente_estado' => 'inactivo',
            'monto_maximo' => 6000,
            'activo' => true,
        ]);
    }


    /**
     * FILTER_CREDITO_EN_FALLA
     * Instrucciones: evaluar al cliente 'PFILT-CREDITO-FALLA-001' con tipo de solicitud 'nuevo'.
     * Expectativa: el filtro rechaza por cartera 'moroso' (falla) y credito 'vencido'.
     */
    private function seedCreditoEnFallaEscenario(Promotor $promotor): void
    {
        $estados = $this->resolverEstados('vencido', 'moroso');

        $cliente = Cliente::create(array_merge([
            'promotor_id' => $promotor->id,
            'CURP' => 'PFILT-CREDITO-FALLA-001',
            'nombre' => 'Luis',
            'apellido_p' => 'Martinez',
            'apellido_m' => 'Solano',
            'fecha_nacimiento' => '1985-02-20',
            'monto_maximo' => 5000,
            'activo' => true,
        ], $estados));

        $credit = Credito::create([
            'cliente_id' => $cliente->id,
            'monto_total' => 5000,
            'estado' => 'vencido',
            'interes' => 14.0,
            'periodicidad' => '14Semanas',
            'fecha_inicio' => Carbon::now()->subWeeks(20)->toDateString(),
            'fecha_final' => Carbon::now()->subWeeks(6)->toDateString(),
        ]);

        DatoContacto::create([
            'credito_id' => $credit->id,
            'calle' => 'Calle Morelos',
            'numero_ext' => '45',
            'numero_int' => null,
            'monto_mensual' => 1300,
            'colonia' => 'El Rosario',
            'municipio' => 'Azcapotzalco',
            'estado' => 'CDMX',
            'cp' => '03030',
            'tiempo_en_residencia' => '3 anios',
            'tel_fijo' => null,
            'tel_cel' => '5511122233',
            'tipo_de_vivienda' => 'Rentada',
        ]);
    }


    /**
     * FILTER_CREDITO_ACTIVO
     * Instrucciones: evaluar al cliente 'PFILT-CREDITO-ACTIVO-001' con tipo de solicitud 'nuevo'.
     * Expectativa: el filtro rechaza porque el cliente ya tiene un credito activo.
     */
    private function seedCreditoActivoEscenario(Promotor $promotor): void
    {
        $estados = $this->resolverEstados('desembolsado', 'activo');

        $cliente = Cliente::create(array_merge([
            'promotor_id' => $promotor->id,
            'CURP' => 'PFILT-CREDITO-ACTIVO-001',
            'nombre' => 'Luisa',
            'apellido_p' => 'Castaneda',
            'apellido_m' => 'Rocha',
            'fecha_nacimiento' => '1991-11-11',
            'monto_maximo' => 9000,
            'activo' => true,
        ], $estados));

        $credit = Credito::create([
            'cliente_id' => $cliente->id,
            'monto_total' => 8000,
            'estado' => 'desembolsado',
            'interes' => 11.5,
            'periodicidad' => '15Semanas',
            'fecha_inicio' => Carbon::now()->subWeeks(10)->toDateString(),
            'fecha_final' => Carbon::now()->addWeeks(5)->toDateString(),
        ]);

        DatoContacto::create([
            'credito_id' => $credit->id,
            'calle' => 'Calle Reforma',
            'numero_ext' => '50',
            'numero_int' => null,
            'monto_mensual' => 1600,
            'colonia' => 'San Rafael',
            'municipio' => 'Cuauhtemoc',
            'estado' => 'CDMX',
            'cp' => '04040',
            'tiempo_en_residencia' => '2 anios',
            'tel_fijo' => null,
            'tel_cel' => '5512233445',
            'tipo_de_vivienda' => 'Propia',
        ]);
    }


    /**
     * FILTER_OTRA_PLAZA
     * Instrucciones: desde promotor@example.com evaluar al cliente 'PFILT-OTRA-PLAZA-001' con contexto ['promotor_id' => promotor principal id, 'supervisor_id' => supervisor principal id].
     * Expectativa: el filtro rechaza porque el cliente pertenece a la plaza secundaria.
     */
    private function seedOtraPlazaEscenario(Promotor $promotorPrincipal, Promotor $promotorSecundario): void
    {
        Cliente::create([
            'promotor_id' => $promotorSecundario->id,
            'CURP' => 'PFILT-OTRA-PLAZA-001',
            'nombre' => 'Rocio',
            'apellido_p' => 'Trevino',
            'apellido_m' => 'Zamora',
            'fecha_nacimiento' => '1993-08-08',
            'tiene_credito_activo' => false,
            'cliente_estado' => 'inactivo',
            'monto_maximo' => 7000,
            'activo' => true,
        ]);
    }


    /**
     * FILTER_BLOQUEO_FALLA_PROMOTORA
     * Instrucciones: evaluar al cliente 'PFILT-PROMOTOR-FALLA-004' desde promotor@example.com con tipo 'nuevo'.
     * Expectativa: el filtro bloquea por superar 5% de creditos en falla dentro de la cartera del promotor.
     */
    private function seedBloqueoFallaPromotoraEscenario(Promotor $promotor): void
    {
        $clientes = [
            ['curp' => 'PFILT-PROMOTOR-FALLA-001', 'estado' => 'vencido', 'cartera' => 'moroso', 'nombre' => 'Gabriel'],
            ['curp' => 'PFILT-PROMOTOR-FALLA-002', 'estado' => 'vencido', 'cartera' => 'moroso', 'nombre' => 'Gloria'],
            ['curp' => 'PFILT-PROMOTOR-FALLA-003', 'estado' => 'desembolsado', 'cartera' => 'activo', 'nombre' => 'Gustavo'],
        ];

        foreach ($clientes as $index => $data) {
            $estados = $this->resolverEstados($data['estado'], $data['cartera']);

            $cliente = Cliente::create(array_merge([
                'promotor_id' => $promotor->id,
                'CURP' => $data['curp'],
                'nombre' => $data['nombre'],
                'apellido_p' => 'Figueroa',
                'apellido_m' => 'Nava',
                'fecha_nacimiento' => '1987-01-01',
                'monto_maximo' => 6500,
                'activo' => true,
            ], $estados));

            $credit = Credito::create([
                'cliente_id' => $cliente->id,
                'monto_total' => 6000,
                'estado' => $data['estado'],
                'interes' => 10.5,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => Carbon::now()->subWeeks(18)->toDateString(),
                'fecha_final' => Carbon::now()->addWeeks(4)->toDateString(),
            ]);

            DatoContacto::create([
                'credito_id' => $credit->id,
                'calle' => 'Calle Libertad ' . ($index + 1),
                'numero_ext' => '20',
                'numero_int' => null,
                'monto_mensual' => 1400,
                'colonia' => 'Santa Maria la Ribera',
                'municipio' => 'Cuauhtemoc',
                'estado' => 'CDMX',
                'cp' => '05050',
                'tiempo_en_residencia' => '5 anios',
                'tel_fijo' => null,
                'tel_cel' => '5513344556',
                'tipo_de_vivienda' => 'Rentada',
            ]);
        }

        Cliente::create([
            'promotor_id' => $promotor->id,
            'CURP' => 'PFILT-PROMOTOR-FALLA-004',
            'nombre' => 'Fernanda',
            'apellido_p' => 'Lopez',
            'apellido_m' => 'Cruz',
            'fecha_nacimiento' => '1994-04-14',
            'tiene_credito_activo' => false,
            'cliente_estado' => 'inactivo',
            'monto_maximo' => 7000,
            'activo' => true,
        ]);
    }


    /**
     * FILTER_DOBLE_DOMICILIO
     * Instrucciones: usar al cliente 'PFILT-DOMICILIO-NUEVO-003' y en el formulario enviar contacto con
     * calle=Calle Filtros, numero_ext=123, colonia=Colonia Pruebas Filtros, municipio=Alvaro Obregon, cp=01010.
     * Expectativa: sin autorizacion especial el filtro rechaza por existir dos creditos activos en ese domicilio.
     * Para aprobar: agregar ['autorizacion_especial_domicilio' => true] y definir credito.fecha_inicio al menos 7 semanas despues.
     */
    private function seedDobleDomicilioEscenario(Promotor $promotor): void
    {
        $clientesActivos = [
            ['curp' => 'PFILT-DOMICILIO-ACT-001', 'nombre' => 'Diana', 'fecha' => Carbon::now()->subWeeks(24)],
            ['curp' => 'PFILT-DOMICILIO-ACT-002', 'nombre' => 'Diego', 'fecha' => Carbon::now()->subWeeks(16)],
        ];

        foreach ($clientesActivos as $data) {
            $estados = $this->resolverEstados('desembolsado');

            $cliente = Cliente::create(array_merge([
                'promotor_id' => $promotor->id,
                'CURP' => $data['curp'],
                'nombre' => $data['nombre'],
                'apellido_p' => 'Mercado',
                'apellido_m' => 'Perez',
                'fecha_nacimiento' => '1990-12-12',
                'monto_maximo' => 7500,
                'activo' => true,
            ], $estados));

            $credit = Credito::create([
                'cliente_id' => $cliente->id,
                'monto_total' => 7000,
                'estado' => 'desembolsado',
                'interes' => 10.8,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $data['fecha']->toDateString(),
                'fecha_final' => $data['fecha']->copy()->addWeeks(14)->toDateString(),
            ]);

            DatoContacto::create([
                'credito_id' => $credit->id,
                'calle' => self::DOMICILIO_CALLE,
                'numero_ext' => self::DOMICILIO_NUM_EXT,
                'numero_int' => null,
                'monto_mensual' => 1700,
                'colonia' => self::DOMICILIO_COLONIA,
                'municipio' => self::DOMICILIO_MUNICIPIO,
                'estado' => 'CDMX',
                'cp' => self::DOMICILIO_CP,
                'tiempo_en_residencia' => '6 anios',
                'tel_fijo' => null,
                'tel_cel' => '5514456677',
                'tipo_de_vivienda' => 'Rentada',
            ]);
        }

        Cliente::create([
            'promotor_id' => $promotor->id,
            'CURP' => 'PFILT-DOMICILIO-NUEVO-003',
            'nombre' => 'Daniel',
            'apellido_p' => 'Mercado',
            'apellido_m' => 'Perez',
            'fecha_nacimiento' => '1995-06-16',
            'tiene_credito_activo' => false,
            'cliente_estado' => 'inactivo',
            'monto_maximo' => 6500,
            'activo' => true,
        ]);
    }


    /**
     * FILTER_BLOQUEO_TIEMPO_REACREDITOS
     * Instrucciones: evaluar los clientes 'PFILT-REACREDITO-*' con tipo 'recredito' y ajustar fecha_solicitud segun el escenario.
     * Expectativa: validar bloqueo por semanas transcurridas o estatus de cartera antes de autorizar el recredito.
     */
    private function seedBloqueoTiempoRecreditosEscenario(Promotor $promotor): void
    {
        $escenarios = [
            [
                'curp' => 'PFILT-REACREDITO-TEMPRANO-001',
                'nombre' => 'Bruno',
                'apellido_p' => 'Rios',
                'apellido_m' => 'Alvarez',
                'fecha' => Carbon::now()->subWeeks(4),
                'estado' => 'desembolsado',
                'cartera' => 'activo',
            ],
            [
                'curp' => 'PFILT-REACREDITO-EN-FALLA-002',
                'nombre' => 'Brenda',
                'apellido_p' => 'Rios',
                'apellido_m' => 'Alvarez',
                'fecha' => Carbon::now()->subWeeks(18),
                'estado' => 'vencido',
                'cartera' => 'moroso',
            ],
            [
                'curp' => 'PFILT-REACREDITO-APROBADO-003',
                'nombre' => 'Beatriz',
                'apellido_p' => 'Rios',
                'apellido_m' => 'Alvarez',
                'fecha' => Carbon::now()->subWeeks(20),
                'estado' => 'liquidado',
                'cartera' => 'regularizado',
            ],
        ];

        foreach ($escenarios as $data) {
            $estados = $this->resolverEstados($data['estado'], $data['cartera']);

            $cliente = Cliente::create(array_merge([
                'promotor_id' => $promotor->id,
                'CURP' => $data['curp'],
                'nombre' => $data['nombre'],
                'apellido_p' => $data['apellido_p'],
                'apellido_m' => $data['apellido_m'],
                'fecha_nacimiento' => '1989-09-09',
                'monto_maximo' => 9000,
                'activo' => true,
            ], $estados));

            $credit = Credito::create([
                'cliente_id' => $cliente->id,
                'monto_total' => 8500,
                'estado' => $data['estado'],
                'interes' => 9.9,
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $data['fecha']->toDateString(),
                'fecha_final' => $data['fecha']->copy()->addWeeks(14)->toDateString(),
            ]);

            DatoContacto::create([
                'credito_id' => $credit->id,
                'calle' => 'Calle Recredito',
                'numero_ext' => '60',
                'numero_int' => null,
                'monto_mensual' => 1500,
                'colonia' => 'Recredito',
                'municipio' => 'Miguel Hidalgo',
                'estado' => 'CDMX',
                'cp' => '06060',
                'tiempo_en_residencia' => '4 anios',
                'tel_fijo' => null,
                'tel_cel' => '5515566778',
                'tipo_de_vivienda' => 'Propia',
            ]);
        }
    }

    /**
     * Ajusta cartera y bandera de credito activo a partir del estado del credito.
     *
     * @return array{cliente_estado: string, tiene_credito_activo: bool}
     */
    private function resolverEstados(?string $estadoCredito, ?string $carteraEstado = null, ?bool $tieneCreditoActivo = null): array
    {
        $map = [
            'desembolsado' => ['cliente_estado' => 'activo', 'tiene_credito_activo' => true],
            'supervisado' => ['cliente_estado' => 'activo', 'tiene_credito_activo' => true],
            'aprobado' => ['cliente_estado' => 'activo', 'tiene_credito_activo' => true],
            'vencido' => ['cliente_estado' => 'moroso', 'tiene_credito_activo' => true],
            'cancelado' => ['cliente_estado' => 'moroso', 'tiene_credito_activo' => true],
            'liquidado' => ['cliente_estado' => 'regularizado', 'tiene_credito_activo' => false],
            'prospectado' => ['cliente_estado' => 'inactivo', 'tiene_credito_activo' => false],
            'prospectado_recredito' => ['cliente_estado' => 'inactivo', 'tiene_credito_activo' => false],
            'solicitado' => ['cliente_estado' => 'inactivo', 'tiene_credito_activo' => false],
        ];

        $defaults = $estadoCredito
            ? ($map[$estadoCredito] ?? ['cliente_estado' => 'inactivo', 'tiene_credito_activo' => false])
            : ['cliente_estado' => 'inactivo', 'tiene_credito_activo' => false];

        $cartera = $carteraEstado ?? $defaults['cliente_estado'];
        $tiene = $tieneCreditoActivo ?? $defaults['tiene_credito_activo'];

        if (in_array($cartera, ['activo', 'moroso', 'falla', 'desembolsado'], true)) {
            $tiene = true;
        }

        if (in_array($cartera, ['inactivo', 'regularizado'], true)) {
            $tiene = false;
        }

        return [
            'cliente_estado' => $cartera,
            'tiene_credito_activo' => $tiene,
        ];
    }

}














