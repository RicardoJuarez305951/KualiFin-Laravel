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
use Illuminate\Support\Facades\Hash;

/**
 * Escenarios deterministas para validar manualmente cada regla de FiltrosController.
 *
 * Clientes clave (todos bajo el árbol ejecutivo@example.com → supervisor@example.com):
 * - curp_unica: "Curp Duplicada Base", "Curp Duplicada Rechazo", "Curp Unica Aprobacion".
 * - doble_firma_aval: "Aval Saturado Uno", "Aval Saturado Dos", "Aval Disponible".
 * - credito_en_falla: "Credito Vencido Filtro" (rechazo) y "Historial Limpio Filtro" (aprueba).
 * - credito_activo: "Credito Activo Nuevo" (rechazo) y "Recredito Permitido" (aprueba con tipo_solicitud=recredito).
 * - otra_plaza: "Cliente Otra Plaza" (asignado a promotor.otra.plaza@example.com) y "Cliente Misma Plaza".
 * - bloqueo_falla_promotora_5: usar clientes del promotor@example.com (tiene >5% fallas) vs.
 *   "Cartera Saludable 01/02" bajo promotor.controlado@example.com.
 * - doble_domicilio: direcciones "Calle Filtro 123" (rechazo y rechazo con autorización) y
 *   "Calle Especial 77" (aprueba con autorización especial) además de "Domicilio Unico" para aprobación directa.
 * - bloqueo_tiempo_recreditos: "Recredito Temprano" (rechazo), "Recredito Sin Atrasos" (aprueba) y
 *   "Recredito Con Mora" (rechazo por atrasos).
 */
class FiltrosManualTestSeeder extends Seeder
{
    public function run(): void
    {
        $ejecutivo = Ejecutivo::whereHas('user', fn ($query) => $query->where('email', 'ejecutivo@example.com'))->first();
        $supervisor = Supervisor::whereHas('user', fn ($query) => $query->where('email', 'supervisor@example.com'))->first();
        $promotorBase = Promotor::whereHas('user', fn ($query) => $query->where('email', 'promotor@example.com'))->first();

        if (!$ejecutivo || !$supervisor || !$promotorBase) {
            return;
        }

        $now = Carbon::now();

        $promotorOtraPlaza = $this->ensurePromotor($supervisor, 'promotor.otra.plaza@example.com', 'Otra', 'Plaza', 'Referencia', $now);
        $promotorControlado = $this->ensurePromotor($supervisor, 'promotor.controlado@example.com', 'Control', 'Cartera', 'Saludable', $now);

        $this->crearEscenariosCurpUnica($promotorBase, $now);
        $this->crearEscenariosDobleFirmaAval($promotorBase, $now);
        $this->crearEscenariosCreditoEnFalla($promotorBase, $now);
        $this->crearEscenariosCreditoActivo($promotorBase, $now);
        $this->crearEscenariosOtraPlaza($promotorBase, $promotorOtraPlaza, $now);
        $this->crearEscenariosBloqueoFallaPromotora($promotorBase, $promotorControlado, $now);
        $this->crearEscenariosDobleDomicilio($promotorBase, $now);
        $this->crearEscenariosBloqueoTiempoRecreditos($promotorBase, $now);
    }

    private function crearEscenariosCurpUnica(Promotor $promotor, Carbon $now): void
    {
        // curp_unica: "Curp Duplicada Base" sirve como cliente original.
        $this->crearCliente(
            $promotor,
            'FILT010101HDFRLL01',
            'Curp',
            'Duplicada',
            'Base',
            [
                'tiene_credito_activo' => false,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '09:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );

        // curp_unica: "Curp Duplicada Rechazo" comparte la misma CURP para disparar el filtro.
        $this->crearCliente(
            $promotor,
            'FILT010101HDFRLL01',
            'Curp',
            'Duplicada',
            'Rechazo',
            [
                'tiene_credito_activo' => false,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '10:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );

        // curp_unica: "Curp Unica Aprobacion" asegura un caso positivo.
        $this->crearCliente(
            $promotor,
            'FILT020202HDFRLL02',
            'Curp',
            'Unica',
            'Aprobacion',
            [
                'tiene_credito_activo' => false,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '11:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
    }

    private function crearEscenariosDobleFirmaAval(Promotor $promotor, Carbon $now): void
    {
        // doble_firma_aval: aval CURP "AVALSAT0101HDFLLL01" ya respalda dos créditos activos.
        $avalCurpSaturado = 'AVALSAT0101HDFLLL01';

        $clienteAvalUno = $this->crearCliente(
            $promotor,
            'AVALSAT0101CL1',
            'Aval',
            'Saturado',
            'Uno',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '12:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoAvalUno = $this->crearCredito(
            $clienteAvalUno,
            [
                'estado' => 'supervisado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(6),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto(
            $creditoAvalUno,
            [
                'calle' => 'Calle Aval 1',
                'numero_ext' => '101',
                'colonia' => 'Colonia Avales',
                'municipio' => 'Ciudad Filtro',
                'cp' => '02020',
                'tel_cel' => '5550001001',
            ],
            $now
        );
        $this->crearAval(
            $creditoAvalUno,
            $avalCurpSaturado,
            [
                'nombre' => 'Aval',
                'apellido_p' => 'Saturado',
                'apellido_m' => 'Principal',
                'fecha_nacimiento' => '1975-05-01',
                'direccion' => 'Calle Aval 1 #101, Ciudad Filtro',
                'telefono' => '5550002001',
                'parentesco' => 'amigo',
            ],
            $now
        );

        $clienteAvalDos = $this->crearCliente(
            $promotor,
            'AVALSAT0101CL2',
            'Aval',
            'Saturado',
            'Dos',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '12:30',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoAvalDos = $this->crearCredito(
            $clienteAvalDos,
            [
                'estado' => 'desembolsado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(8),
                'duracion_weeks' => 16,
            ]
        );
        $this->crearDatoContacto(
            $creditoAvalDos,
            [
                'calle' => 'Calle Aval 2',
                'numero_ext' => '202',
                'colonia' => 'Colonia Avales',
                'municipio' => 'Ciudad Filtro',
                'cp' => '02020',
                'tel_cel' => '5550001002',
            ],
            $now
        );
        $this->crearAval(
            $creditoAvalDos,
            $avalCurpSaturado,
            [
                'nombre' => 'Aval',
                'apellido_p' => 'Saturado',
                'apellido_m' => 'Secundario',
                'fecha_nacimiento' => '1976-06-01',
                'direccion' => 'Calle Aval 2 #202, Ciudad Filtro',
                'telefono' => '5550002002',
                'parentesco' => 'primo',
            ],
            $now
        );

        // doble_firma_aval: "Aval Disponible" tiene un aval distinto y queda listo para aprobación.
        $clienteAvalLibre = $this->crearCliente(
            $promotor,
            'AVALDISP0101',
            'Aval',
            'Disponible',
            'Cliente',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'desembolsado',
                'horario_de_pago' => '13:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoAvalLibre = $this->crearCredito(
            $clienteAvalLibre,
            [
                'estado' => 'supervisado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(5),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto(
            $creditoAvalLibre,
            [
                'calle' => 'Calle Aval 3',
                'numero_ext' => '303',
                'colonia' => 'Colonia Avales',
                'municipio' => 'Ciudad Filtro',
                'cp' => '02020',
                'tel_cel' => '5550001003',
            ],
            $now
        );
        $this->crearAval(
            $creditoAvalLibre,
            'AVALDIS0101HDFLLL02',
            [
                'nombre' => 'Aval',
                'apellido_p' => 'Disponible',
                'apellido_m' => 'Garantia',
                'fecha_nacimiento' => '1980-07-01',
                'direccion' => 'Calle Aval 3 #303, Ciudad Filtro',
                'telefono' => '5550002003',
                'parentesco' => 'hermano',
            ],
            $now
        );
    }

    private function crearEscenariosCreditoEnFalla(Promotor $promotor, Carbon $now): void
    {
        // credito_en_falla: "Credito Vencido Filtro" mantiene estado vencido y cartera morosa.
        $clienteFalla = $this->crearCliente(
            $promotor,
            'FALLA0101',
            'Credito',
            'Vencido',
            'Filtro',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'moroso',
                'horario_de_pago' => '14:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoFalla = $this->crearCredito(
            $clienteFalla,
            [
                'estado' => 'vencido',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(24),
                'duracion_weeks' => 16,
            ]
        );
        $this->crearDatoContacto(
            $creditoFalla,
            [
                'calle' => 'Calle Falla 10',
                'numero_ext' => '10',
                'colonia' => 'Colonia Cartera',
                'municipio' => 'Ciudad Filtro',
                'cp' => '03030',
                'tel_cel' => '5550003001',
            ],
            $now
        );

        // credito_en_falla: "Historial Limpio Filtro" es un caso aprobado.
        $clienteLimpio = $this->crearCliente(
            $promotor,
            'FALLA0202',
            'Historial',
            'Limpio',
            'Filtro',
            [
                'tiene_credito_activo' => false,
                'cartera_estado' => 'regularizado',
                'horario_de_pago' => '15:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoLimpio = $this->crearCredito(
            $clienteLimpio,
            [
                'estado' => 'liquidado',
                'periodicidad' => '13Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(40),
                'duracion_weeks' => 13,
            ]
        );
        $this->crearDatoContacto(
            $creditoLimpio,
            [
                'calle' => 'Calle Limpia 20',
                'numero_ext' => '20',
                'colonia' => 'Colonia Historial',
                'municipio' => 'Ciudad Filtro',
                'cp' => '03030',
                'tel_cel' => '5550003002',
            ],
            $now
        );
    }

    private function crearEscenariosCreditoActivo(Promotor $promotor, Carbon $now): void
    {
        // credito_activo: "Credito Activo Nuevo" rechaza solicitudes nuevas.
        $clienteActivo = $this->crearCliente(
            $promotor,
            'ACTIVO0101',
            'Credito',
            'Activo',
            'Nuevo',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'desembolsado',
                'horario_de_pago' => '16:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoActivo = $this->crearCredito(
            $clienteActivo,
            [
                'estado' => 'desembolsado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(4),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto(
            $creditoActivo,
            [
                'calle' => 'Calle Activa 1',
                'numero_ext' => '11',
                'colonia' => 'Colonia Activa',
                'municipio' => 'Ciudad Filtro',
                'cp' => '04040',
                'tel_cel' => '5550004001',
            ],
            $now
        );

        // credito_activo: "Recredito Permitido" aprueba si el tipo de solicitud es "recredito".
        $clienteRecredito = $this->crearCliente(
            $promotor,
            'ACTIVO0202',
            'Recredito',
            'Permitido',
            'Filtro',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'desembolsado',
                'horario_de_pago' => '16:30',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoRecredito = $this->crearCredito(
            $clienteRecredito,
            [
                'estado' => 'desembolsado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(12),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto(
            $creditoRecredito,
            [
                'calle' => 'Calle Recredito 2',
                'numero_ext' => '22',
                'colonia' => 'Colonia Activa',
                'municipio' => 'Ciudad Filtro',
                'cp' => '04040',
                'tel_cel' => '5550004002',
            ],
            $now
        );
    }

    private function crearEscenariosOtraPlaza(Promotor $promotorBase, Promotor $promotorOtraPlaza, Carbon $now): void
    {
        // otra_plaza: "Cliente Otra Plaza" pertenece a promotor.otra.plaza@example.com.
        $this->crearCliente(
            $promotorOtraPlaza,
            'PLAZA0101',
            'Cliente',
            'Otra',
            'Plaza',
            [
                'tiene_credito_activo' => false,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '17:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );

        // otra_plaza: "Cliente Misma Plaza" queda bajo promotor@example.com para aprobación.
        $this->crearCliente(
            $promotorBase,
            'PLAZA0202',
            'Cliente',
            'Misma',
            'Plaza',
            [
                'tiene_credito_activo' => false,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '17:30',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
    }

    private function crearEscenariosBloqueoFallaPromotora(Promotor $promotorBase, Promotor $promotorControlado, Carbon $now): void
    {
        // bloqueo_falla_promotora_5: reforzamos la tasa de falla del promotor base.
        $clienteCancelado = $this->crearCliente(
            $promotorBase,
            'FALLA0303',
            'Promotora',
            'Cancelado',
            'Filtro',
            [
                'tiene_credito_activo' => false,
                'cartera_estado' => 'inactivo',
                'horario_de_pago' => '18:00',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoCancelado = $this->crearCredito(
            $clienteCancelado,
            [
                'estado' => 'cancelado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(20),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto(
            $creditoCancelado,
            [
                'calle' => 'Calle Falla 30',
                'numero_ext' => '30',
                'colonia' => 'Colonia Cartera',
                'municipio' => 'Ciudad Filtro',
                'cp' => '03030',
                'tel_cel' => '5550003003',
            ],
            $now
        );

        // bloqueo_falla_promotora_5: promotor.controlado@example.com mantiene créditos sanos para referencia.
        for ($i = 1; $i <= 2; $i++) {
            $clienteControlado = $this->crearCliente(
                $promotorControlado,
                sprintf('CONTROL%02d', $i),
                'Cartera',
                'Saludable',
                sprintf('0%d', $i),
                [
                    'tiene_credito_activo' => true,
                    'cartera_estado' => 'desembolsado',
                    'horario_de_pago' => '08:3' . $i,
                    'creado_en' => $now,
                    'actualizado_en' => $now,
                ]
            );
            $creditoControlado = $this->crearCredito(
                $clienteControlado,
                [
                    'estado' => 'desembolsado',
                    'periodicidad' => '14Semanas',
                    'fecha_inicio' => $now->copy()->subWeeks(15 + $i),
                    'duracion_weeks' => 14,
                ]
            );
            $this->crearDatoContacto(
                $creditoControlado,
                [
                    'calle' => 'Calle Saludable ' . $i,
                    'numero_ext' => (string) (40 + $i),
                    'colonia' => 'Colonia Salud',
                    'municipio' => 'Ciudad Control',
                    'cp' => '05050',
                    'tel_cel' => '555000500' . $i,
                ],
                $now
            );
        }
    }

    private function crearEscenariosDobleDomicilio(Promotor $promotor, Carbon $now): void
    {
        // doble_domicilio: dos créditos activos recientes en "Calle Filtro 123" provocan rechazo general.
        $direccionRechazo = [
            'calle' => 'Calle Filtro 123',
            'numero_ext' => '10',
            'colonia' => 'Colonia Compartida',
            'municipio' => 'Ciudad Doble',
            'cp' => '06060',
            'tel_cel' => '5550006001',
        ];

        $clienteDomicilioUno = $this->crearCliente(
            $promotor,
            'DOMIC0101',
            'Domicilio',
            'Compartido',
            'Uno',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '09:15',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoDomicilioUno = $this->crearCredito(
            $clienteDomicilioUno,
            [
                'estado' => 'supervisado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(3),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto($creditoDomicilioUno, $direccionRechazo, $now);

        $clienteDomicilioDos = $this->crearCliente(
            $promotor,
            'DOMIC0202',
            'Domicilio',
            'Compartido',
            'Dos',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'supervisado',
                'horario_de_pago' => '09:45',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoDomicilioDos = $this->crearCredito(
            $clienteDomicilioDos,
            [
                'estado' => 'desembolsado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(4),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto($creditoDomicilioDos, $direccionRechazo, $now);

        // doble_domicilio: créditos antiguos en "Calle Especial 77" permiten autorización especial (>=7 semanas).
        $direccionAutorizable = [
            'calle' => 'Calle Especial 77',
            'numero_ext' => '77',
            'colonia' => 'Colonia Especial',
            'municipio' => 'Ciudad Doble',
            'cp' => '06060',
            'tel_cel' => '5550006002',
        ];

        $clienteAutorizableUno = $this->crearCliente(
            $promotor,
            'DOMIC0303',
            'Domicilio',
            'Autorizable',
            'Uno',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '10:15',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoAutorizableUno = $this->crearCredito(
            $clienteAutorizableUno,
            [
                'estado' => 'supervisado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(20),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto($creditoAutorizableUno, $direccionAutorizable, $now);

        $clienteAutorizableDos = $this->crearCliente(
            $promotor,
            'DOMIC0404',
            'Domicilio',
            'Autorizable',
            'Dos',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '10:45',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoAutorizableDos = $this->crearCredito(
            $clienteAutorizableDos,
            [
                'estado' => 'desembolsado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(12),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto($creditoAutorizableDos, $direccionAutorizable, $now);

        // doble_domicilio: "Domicilio Unico" sirve para aprobación inmediata.
        $clienteUnico = $this->crearCliente(
            $promotor,
            'DOMIC0505',
            'Domicilio',
            'Unico',
            'Filtro',
            [
                'tiene_credito_activo' => false,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '11:15',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoUnico = $this->crearCredito(
            $clienteUnico,
            [
                'estado' => 'liquidado',
                'periodicidad' => '13Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(30),
                'duracion_weeks' => 13,
            ]
        );
        $this->crearDatoContacto(
            $creditoUnico,
            [
                'calle' => 'Calle Unica 55',
                'numero_ext' => '55',
                'colonia' => 'Colonia Individual',
                'municipio' => 'Ciudad Doble',
                'cp' => '06060',
                'tel_cel' => '5550006003',
            ],
            $now
        );
    }

    private function crearEscenariosBloqueoTiempoRecreditos(Promotor $promotor, Carbon $now): void
    {
        // bloqueo_tiempo_recreditos: "Recredito Temprano" inicia hace 4 semanas (rechazo por semanas insuficientes).
        $clienteTemprano = $this->crearCliente(
            $promotor,
            'RECRE0101',
            'Recredito',
            'Temprano',
            'Filtro',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'desembolsado',
                'horario_de_pago' => '12:15',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoTemprano = $this->crearCredito(
            $clienteTemprano,
            [
                'estado' => 'desembolsado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(4),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto(
            $creditoTemprano,
            [
                'calle' => 'Calle Recredito 10',
                'numero_ext' => '10',
                'colonia' => 'Colonia Recredito',
                'municipio' => 'Ciudad Recredito',
                'cp' => '07070',
                'tel_cel' => '5550007001',
            ],
            $now
        );

        // bloqueo_tiempo_recreditos: "Recredito Sin Atrasos" cumple semanas y no presenta fallas.
        $clienteSinAtrasos = $this->crearCliente(
            $promotor,
            'RECRE0202',
            'Recredito',
            'Sin',
            'Atrasos',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'activo',
                'horario_de_pago' => '12:45',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoSinAtrasos = $this->crearCredito(
            $clienteSinAtrasos,
            [
                'estado' => 'desembolsado',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(12),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto(
            $creditoSinAtrasos,
            [
                'calle' => 'Calle Recredito 20',
                'numero_ext' => '20',
                'colonia' => 'Colonia Recredito',
                'municipio' => 'Ciudad Recredito',
                'cp' => '07070',
                'tel_cel' => '5550007002',
            ],
            $now
        );

        // bloqueo_tiempo_recreditos: "Recredito Con Mora" rechaza por atrasos en cartera.
        $clienteConMora = $this->crearCliente(
            $promotor,
            'RECRE0303',
            'Recredito',
            'Con',
            'Mora',
            [
                'tiene_credito_activo' => true,
                'cartera_estado' => 'moroso',
                'horario_de_pago' => '13:15',
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
        $creditoConMora = $this->crearCredito(
            $clienteConMora,
            [
                'estado' => 'vencido',
                'periodicidad' => '14Semanas',
                'fecha_inicio' => $now->copy()->subWeeks(15),
                'duracion_weeks' => 14,
            ]
        );
        $this->crearDatoContacto(
            $creditoConMora,
            [
                'calle' => 'Calle Recredito 30',
                'numero_ext' => '30',
                'colonia' => 'Colonia Recredito',
                'municipio' => 'Ciudad Recredito',
                'cp' => '07070',
                'tel_cel' => '5550007003',
            ],
            $now
        );
    }

    private function crearCliente(
        Promotor $promotor,
        string $curp,
        string $nombre,
        string $apellidoP,
        string $apellidoM,
        array $attributes
    ): Cliente {
        $defaults = [
            'promotor_id' => $promotor->id,
            'fecha_nacimiento' => Carbon::parse('1985-01-01')->toDateString(),
            'monto_maximo' => 10000.00,
            'activo' => true,
        ];

        return Cliente::updateOrCreate(
            [
                'CURP' => $curp,
                'nombre' => $nombre,
                'apellido_p' => $apellidoP,
                'apellido_m' => $apellidoM,
            ],
            array_merge($defaults, $attributes)
        );
    }

    private function crearCredito(Cliente $cliente, array $attributes): Credito
    {
        $fechaInicio = $attributes['fecha_inicio'] ?? Carbon::now()->subWeeks(8);
        if (!$fechaInicio instanceof Carbon) {
            $fechaInicio = Carbon::parse((string) $fechaInicio);
        }

        $duracion = $attributes['duracion_weeks'] ?? 14;
        $fechaFinal = $attributes['fecha_final'] ?? $fechaInicio->copy()->addWeeks($duracion);
        if (!$fechaFinal instanceof Carbon) {
            $fechaFinal = Carbon::parse((string) $fechaFinal);
        }

        return Credito::updateOrCreate(
            ['cliente_id' => $cliente->id],
            [
                'monto_total' => $attributes['monto_total'] ?? 8000.00,
                'estado' => $attributes['estado'] ?? 'supervisado',
                'interes' => $attributes['interes'] ?? 12.50,
                'periodicidad' => $attributes['periodicidad'] ?? '14Semanas',
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_final' => $fechaFinal->toDateString(),
            ]
        );
    }

    private function crearDatoContacto(Credito $credito, array $direccion, Carbon $now): void
    {
        $defaults = [
            'numero_int' => null,
            'monto_mensual' => 2500,
            'estado' => 'Ciudad de México',
            'tiempo_en_residencia' => '3 años',
            'tel_fijo' => null,
            'tipo_de_vivienda' => 'rentada',
            'creado_en' => $now,
        ];

        DatoContacto::updateOrCreate(
            ['credito_id' => $credito->id],
            array_merge($defaults, $direccion)
        );
    }

    private function crearAval(Credito $credito, string $curp, array $attributes, Carbon $now): void
    {
        $fechaNacimiento = $attributes['fecha_nacimiento'] ?? '1970-01-01';
        if (!$fechaNacimiento instanceof Carbon) {
            $fechaNacimiento = Carbon::parse((string) $fechaNacimiento);
        }

        Aval::updateOrCreate(
            ['credito_id' => $credito->id],
            [
                'CURP' => $curp,
                'nombre' => $attributes['nombre'],
                'apellido_p' => $attributes['apellido_p'],
                'apellido_m' => $attributes['apellido_m'],
                'fecha_nacimiento' => $fechaNacimiento->toDateString(),
                'direccion' => $attributes['direccion'] ?? 'Direccion Aval',
                'telefono' => $attributes['telefono'] ?? '5550000000',
                'parentesco' => $attributes['parentesco'] ?? 'amigo',
                'creado_en' => $now,
            ]
        );
    }

    private function ensurePromotor(
        Supervisor $supervisor,
        string $email,
        string $nombre,
        string $apellidoP,
        string $apellidoM,
        Carbon $now
    ): Promotor {
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => sprintf('%s %s %s', $nombre, $apellidoP, $apellidoM),
                'email' => $email,
                'password' => Hash::make('12345'),
                'telefono' => '5551112233',
                'rol' => 'promotor',
            ]);
            $user->assignRole('promotor');
        }

        $promotor = Promotor::where('user_id', $user->id)->first();
        if (!$promotor) {
            $promotor = Promotor::create([
                'user_id' => $user->id,
                'supervisor_id' => $supervisor->id,
                'nombre' => $nombre,
                'apellido_p' => $apellidoP,
                'apellido_m' => $apellidoM,
                'venta_maxima' => 15000,
                'colonia' => 'Centro',
                'venta_proyectada_objetivo' => 8000,
                'bono' => 600,
                'dia_de_pago' => 'Lunes',
                'hora_de_pago' => '09:00:00',
            ]);
        }

        return $promotor;
    }
}
