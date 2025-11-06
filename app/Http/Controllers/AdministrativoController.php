<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Http\Request;

/**
 * Controlador temporal que expone las vistas del escritorio administrativo.
 * Usa datos mock para permitir maquetado/UI sin depender del backend real.
 */
class AdministrativoController extends Controller
{
    // --- Paneles con datos de muestra para prototipos ---
    /** Panel de reportes con metricas dummy. */
    public function reportes()
    {
        $summary = [
            ['title' => 'Total desembolsado', 'value' => 1250000, 'trend' => '+12.5%'],
            ['title' => 'Cartera vigente', 'value' => 870000, 'trend' => '+4.3%'],
            ['title' => 'Pagos recibidos', 'value' => 215000, 'trend' => '+8.1%'],
            ['title' => 'Clientes activos', 'value' => 186, 'trend' => '+5.0%'],
        ];

        $recentReports = [
            [
                'title' => 'Reporte de desembolsos - Enero 2025',
                'owner' => 'Ana Lopez',
                'status' => 'Generado',
                'updated_at' => '2025-01-18 10:32',
                'download_url' => '#',
            ],
            [
                'title' => 'Revision cartera en riesgo',
                'owner' => 'Carlos Ramirez',
                'status' => 'En revision',
                'updated_at' => '2025-01-17 18:11',
                'download_url' => '#',
            ],
            [
                'title' => 'Pagos y cobranza - Semana 02',
                'owner' => 'Brenda Martinez',
                'status' => 'Pendiente',
                'updated_at' => '2025-01-16 09:45',
                'download_url' => '#',
            ],
        ];

        $scheduled = [
            ['name' => 'Informe semanal de operacion', 'frequency' => 'Cada lunes', 'next_run' => '2025-01-20 08:00'],
            ['name' => 'Reporte de cartera vencida', 'frequency' => 'Cada miercoles', 'next_run' => '2025-01-22 08:00'],
            ['name' => 'Concentrado de desembolsos', 'frequency' => 'Mensual', 'next_run' => '2025-02-01 08:00'],
        ];

        return view('reportes', compact('summary', 'recentReports', 'scheduled'));
    }

    /** Consola de revision documental (placeholder). */
    public function panelRevision()
    {
        $checklist = [
            [
                'folio' => 'SOL-2025-0145',
                'cliente' => 'Laura Campos',
                'monto' => 42000,
                'estado' => 'Documentacion completa',
                'responsable' => 'Marcos Rivera',
            ],
            [
                'folio' => 'SOL-2025-0139',
                'cliente' => 'Ricardo Perez',
                'monto' => 38000,
                'estado' => 'Pendiente validacion domiciliar',
                'responsable' => 'Erika Flores',
            ],
            [
                'folio' => 'SOL-2025-0128',
                'cliente' => 'Gabriela Sanchez',
                'monto' => 35000,
                'estado' => 'Observaciones',
                'responsable' => 'Equipo Juridico',
            ],
        ];

        $alerts = [
            ['type' => 'warning', 'message' => '3 solicitudes con mas de 48h en espera de documentacion.'],
            ['type' => 'info', 'message' => 'Nueva politica de verificacion vigente desde el 15 de enero.'],
        ];

        return view('PanelRevision', compact('checklist', 'alerts'));
    }

    /** Tabla con solicitudes de recredito simuladas. */
    public function recreditoClientes()
    {
        $applications = [
            [
                'cliente' => 'Hector Molina',
                'curp' => 'MOHA831201HDFRLC06',
                'ciclo' => 3,
                'monto_solicitado' => 55000,
                'ultimo_pago' => '2025-01-12',
                'estatus' => 'Pre aprobado',
            ],
            [
                'cliente' => 'Claudia Trevino',
                'curp' => 'TECT900412MNLVRD05',
                'ciclo' => 2,
                'monto_solicitado' => 42000,
                'ultimo_pago' => '2025-01-10',
                'estatus' => 'En evaluacion',
            ],
            [
                'cliente' => 'Rogelio Nunez',
                'curp' => 'NURR850521HDFTRG02',
                'ciclo' => 4,
                'monto_solicitado' => 70000,
                'ultimo_pago' => '2025-01-08',
                'estatus' => 'Observaciones',
            ],
        ];

        return view('recreditoClientes', compact('applications'));
    }

    /** Catalogo general de clientes (sin filtros reales). */
    public function clientesIndex()
    {
        $clientes = [
            [
                'nombre' => 'Maria Elena Rodriguez',
                'curp' => 'RODM850324MDFRNN06',
                'promotor' => 'Ana Sofia',
                'estado' => 'Activo',
                'monto_maximo' => 45000,
            ],
            [
                'nombre' => 'Carlos Mendoza',
                'curp' => 'MENC900521HDFRRL08',
                'promotor' => 'Miguel Torres',
                'estado' => 'Revision',
                'monto_maximo' => 38000,
            ],
            [
                'nombre' => 'Lucia Torres',
                'curp' => 'TOLU911102MDFRRR05',
                'promotor' => 'Ana Sofia',
                'estado' => 'Vencido',
                'monto_maximo' => 32000,
            ],
        ];

        return view('clientes.index', compact('clientes'));
    }

    /** Formulario de alta rapida para clientes. */
    public function clientesCreate()
    {
        $promotores = [
            ['id' => 1, 'nombre' => 'Ana Sofia'],
            ['id' => 2, 'nombre' => 'Miguel Torres'],
            ['id' => 3, 'nombre' => 'Karina Diaz'],
        ];

        return view('clientes.create', compact('promotores'));
    }

    /** Ficha detalle de cliente con historial mock. */
    public function clientesShow(string $clienteId)
    {
        $cliente = [
            'id' => $clienteId,
            'nombre' => 'Maria Elena Rodriguez',
            'curp' => 'RODM850324MDFRNN06',
            'promotor' => 'Ana Sofia',
            'estado' => 'Activo',
            'monto_maximo' => 45000,
            'saldo_vigente' => 18000,
            'telefono' => '55 1234 5678',
            'ultima_actualizacion' => '2025-01-14',
        ];

        $historial = [
            ['evento' => 'Solicitud aprobada', 'fecha' => '2024-05-18', 'detalles' => 'Monto aprobado $35,000'],
            ['evento' => 'Reacreditacion', 'fecha' => '2024-11-02', 'detalles' => 'Incremento a $45,000'],
            ['evento' => 'Pago puntual', 'fecha' => '2024-12-28', 'detalles' => 'Pago semanal registrado'],
        ];

        return view('clientes.show', compact('cliente', 'historial'));
    }

    /** Formulario de edicion de cliente con valores precargados. */
    public function clientesEdit(string $clienteId)
    {
        $cliente = [
            'id' => $clienteId,
            'nombre' => 'Carlos Mendoza',
            'curp' => 'MENC900521HDFRRL08',
            'promotor_id' => 2,
            'estado' => 'Revision',
            'monto_maximo' => 38000,
            'telefono' => '55 9876 5432',
        ];

        $promotores = [
            ['id' => 1, 'nombre' => 'Ana Sofia'],
            ['id' => 2, 'nombre' => 'Miguel Torres'],
            ['id' => 3, 'nombre' => 'Karina Diaz'],
        ];

        return view('clientes.edit', compact('cliente', 'promotores'));
    }

    /** Lista de documentos por cliente para revision administrativa. */
    public function documentosIndex()
    {
        $documentos = [
            [
                'id' => 1,
                'cliente' => 'Maria Elena Rodriguez',
                'tipo' => 'INE Cliente',
                'estatus' => 'Validado',
                'actualizado' => '2025-01-15 09:30',
            ],
            [
                'id' => 2,
                'cliente' => 'Carlos Mendoza',
                'tipo' => 'Comprobante domicilio',
                'estatus' => 'Pendiente',
                'actualizado' => '2025-01-14 18:12',
            ],
            [
                'id' => 3,
                'cliente' => 'Lucia Torres',
                'tipo' => 'Contrato',
                'estatus' => 'Expirado',
                'actualizado' => '2025-01-12 16:52',
            ],
        ];

        return view('documentos_clientes.index', compact('documentos'));
    }

    /** Formulario dummy para adjuntar documentos. */
    public function documentosCreate()
    {
        $clientes = [
            ['id' => 1, 'nombre' => 'Maria Elena Rodriguez'],
            ['id' => 2, 'nombre' => 'Carlos Mendoza'],
            ['id' => 3, 'nombre' => 'Lucia Torres'],
        ];

        $creditos = [
            ['id' => 1001, 'folio' => 'CR-1001'],
            ['id' => 1002, 'folio' => 'CR-1002'],
            ['id' => 1003, 'folio' => 'CR-1003'],
        ];

        $tipos = ['INE Cliente', 'Comprobante domicilio', 'Contrato', 'Identificacion Aval'];

        return view('documentos_clientes.create', compact('clientes', 'creditos', 'tipos'));
    }

    /** Vista detalle de un documento con bitacora de acciones. */
    public function documentosShow(string $documentoId)
    {
        $documento = [
            'id' => $documentoId,
            'cliente' => 'Maria Elena Rodriguez',
            'credito' => 'CR-1001',
            'tipo' => 'INE Cliente',
            'url' => 'https://placehold.co/600x400?text=Documento',
            'estatus' => 'Validado',
            'ultima_revision' => '2025-01-15 09:30',
        ];

        $historial = [
            ['accion' => 'Documento cargado', 'usuario' => 'Ana Sofia', 'fecha' => '2025-01-14 16:20'],
            ['accion' => 'Validacion automatica', 'usuario' => 'Sistema', 'fecha' => '2025-01-14 16:22'],
            ['accion' => 'Validacion manual', 'usuario' => 'Carlos Ramirez', 'fecha' => '2025-01-15 09:30'],
        ];

        return view('documentos_clientes.show', compact('documento', 'historial'));
    }

    /** Formulario para actualizar metadatos del documento. */
    public function documentosEdit(string $documentoId)
    {
        $documento = [
            'id' => $documentoId,
            'cliente_id' => 2,
            'credito_id' => 1002,
            'tipo' => 'Comprobante domicilio',
            'url' => 'https://placehold.co/600x400?text=Documento',
            'estatus' => 'Pendiente',
        ];

        $clientes = [
            ['id' => 1, 'nombre' => 'Maria Elena Rodriguez'],
            ['id' => 2, 'nombre' => 'Carlos Mendoza'],
            ['id' => 3, 'nombre' => 'Lucia Torres'],
        ];

        $creditos = [
            ['id' => 1001, 'folio' => 'CR-1001'],
            ['id' => 1002, 'folio' => 'CR-1002'],
            ['id' => 1003, 'folio' => 'CR-1003'],
        ];

        $tipos = ['INE Cliente', 'Comprobante domicilio', 'Contrato', 'Identificacion Aval'];

        return view('documentos_clientes.edit', compact('documento', 'clientes', 'creditos', 'tipos'));
    }

    // --- Placeholders sin logica de negocio ---

    /** Reutiliza datos mock para todos los modulos administrativos. */
    private function getDashboardData(): array
    {
        return [
            'autorizaciones' => [
                'summary' => [
                    ['label' => 'Filtros activos', 'value' => 12, 'badge' => '3 nuevos'],
                    ['label' => 'Solicitudes pendientes', 'value' => 7, 'badge' => 'Responder hoy'],
                    ['label' => 'Cambios autorizados', 'value' => 18, 'badge' => 'Semana 03'],
                    ['label' => 'Alertas clientes', 'value' => 5, 'badge' => 'Revisar'],
                ],
                'filters' => [
                    [
                        'name' => 'Evaluacion integral',
                        'description' => 'Clientes con score >= 650 y ventas vigentes.',
                        'fields' => ['Zona', 'Segmento', 'Score', 'Promotor'],
                        'owner' => 'Equipo riesgos',
                        'status' => 'Publicado',
                        'updated_at' => '2025-01-18',
                    ],
                    [
                        'name' => 'Clientes en seguimiento',
                        'description' => 'Casos con observaciones activas y mora < 15 dias.',
                        'fields' => ['Plaza', 'Mora', 'Tipo alerta'],
                        'owner' => 'Mesa control',
                        'status' => 'En revision',
                        'updated_at' => '2025-01-17',
                    ],
                    [
                        'name' => 'Promotoras con limites flexibles',
                        'description' => 'Promotoras con ampliacion temporal de montos.',
                        'fields' => ['Promotora', 'Limite', 'Fecha fin'],
                        'owner' => 'Direccion comercial',
                        'status' => 'Borrador',
                        'updated_at' => '2025-01-16',
                    ],
                ],
                'creditCaps' => [
                    [
                        'segment' => 'Microcredito',
                        'limit' => '$65,000',
                        'status' => 'Propuesto',
                        'owner' => 'Ana Lopez',
                        'effective' => '01 feb 2025',
                    ],
                    [
                        'segment' => 'Credito nomina',
                        'limit' => '$120,000',
                        'status' => 'Publicado',
                        'owner' => 'Rogelio Nunez',
                        'effective' => '15 ene 2025',
                    ],
                    [
                        'segment' => 'Reestructura',
                        'limit' => '$90,000',
                        'status' => 'En revision',
                        'owner' => 'Brenda Martinez',
                        'effective' => 'Por definir',
                    ],
                ],
                'salesLimits' => [
                    [
                        'zone' => 'CDMX Centro',
                        'daily_limit' => '$320,000',
                        'time_window' => '08:00 - 19:00',
                        'exceptions' => 'Viernes extension 21:00',
                    ],
                    [
                        'zone' => 'Toluca',
                        'daily_limit' => '$210,000',
                        'time_window' => '09:00 - 18:30',
                        'exceptions' => 'Venta extra sabado 13:00',
                    ],
                    [
                        'zone' => 'Puebla',
                        'daily_limit' => '$250,000',
                        'time_window' => '08:30 - 18:00',
                        'exceptions' => 'Bloqueo mora > 30 dias',
                    ],
                ],
                'clientFlags' => [
                    [
                        'client' => 'Veronica Solis',
                        'flag' => 'Mas de 2 familiares',
                        'promoter' => 'Ana Beltran',
                        'status' => 'En validacion',
                        'next_step' => 'Visita domiciliaria 22 ene',
                    ],
                    [
                        'client' => 'Carlos y Laura Cruz',
                        'flag' => 'Esposo es promotor (espera 7 semanas)',
                        'promoter' => 'Carlos Cruz',
                        'status' => 'Pausa obligatoria',
                        'next_step' => 'Revisar el 10 feb',
                    ],
                    [
                        'client' => 'Grupo Mujeres Avance',
                        'flag' => 'Clientes extra solicitados',
                        'promoter' => 'Erika Flores',
                        'status' => 'Autorizacion pendiente',
                        'next_step' => 'Revisar cupo viernes',
                    ],
                ],
                'changeRequests' => [
                    [
                        'folio' => 'AUT-554',
                        'type' => 'Cambio de plaza',
                        'requested_by' => 'Supervisor Toluca',
                        'status' => 'Pendiente confirmacion',
                        'deadline' => '21 ene 09:00',
                    ],
                    [
                        'folio' => 'AUT-559',
                        'type' => 'Cambiar falla crediticia',
                        'requested_by' => 'Promotor J. Ramirez',
                        'status' => 'En revision',
                        'deadline' => '21 ene 16:00',
                    ],
                    [
                        'folio' => 'AUT-561',
                        'type' => 'Clientes extra',
                        'requested_by' => 'Ejecutivo Vega',
                        'status' => 'Validacion documental',
                        'deadline' => '22 ene 12:00',
                    ],
                ],
                'workerChanges' => [
                    [
                        'employee' => 'Carlos Mendoza',
                        'change' => 'Comision 3.5% -> 4.0%',
                        'status' => 'En analisis',
                        'requested_at' => '20 ene 2025',
                    ],
                    [
                        'employee' => 'Marisol Ortega',
                        'change' => 'Cambio de plaza Toluca -> Queretaro',
                        'status' => 'Aprobado',
                        'requested_at' => '19 ene 2025',
                    ],
                ],
                'weeklyActions' => [
                    [
                        'role' => 'Promotor',
                        'actions' => 58,
                        'detail' => 'Reforzamientos de limites y solicitud de horarios adicionales.',
                    ],
                    [
                        'role' => 'Supervisor',
                        'actions' => 24,
                        'detail' => 'Validaciones y seguimiento a clientes con parentesco.',
                    ],
                    [
                        'role' => 'Ejecutivo',
                        'actions' => 12,
                        'detail' => 'Solicitudes de cupo extra y confirmacion de cambios.',
                    ],
                ],
                'executiveRequests' => [
                    [
                        'executive' => 'Mariana Torres',
                        'request' => 'Aumentar limite de venta para promotora Centro',
                        'status' => 'Pendiente',
                        'created_at' => '21 ene 2025 08:40',
                    ],
                    [
                        'executive' => 'Selene Vega',
                        'request' => 'Autorizar horario extendido viernes',
                        'status' => 'En revision',
                        'created_at' => '20 ene 2025 17:25',
                    ],
                ],
                'observations' => [
                    [
                        'author' => 'Mesa de control',
                        'note' => 'Recordar validacion manual de clientes con parentesco de primer grado.',
                        'timestamp' => '21 ene 2025 09:10',
                    ],
                    [
                        'author' => 'Auditoria interna',
                        'note' => 'Cruzar limite de venta extendido con resultados de mora semanal.',
                        'timestamp' => '20 ene 2025 18:45',
                    ],
                ],
            ],
            'parametros' => [
                'modules' => [
                    [
                        'name' => 'Horarios operativos',
                        'description' => 'Define ventanas de atencion presencial y telefonica.',
                        'status' => 'Publicado',
                        'updated_at' => '2025-01-18 09:20',
                    ],
                    [
                        'name' => 'Topes de credito',
                        'description' => 'Montos maximos por ciclo y segmento.',
                        'status' => 'En revision',
                        'updated_at' => '2025-01-17 16:45',
                    ],
                    [
                        'name' => 'Alertas automaticas',
                        'description' => 'Notificaciones de mora y desembolso.',
                        'status' => 'Publicado',
                        'updated_at' => '2025-01-15 11:10',
                    ],
                ],
                'schedules' => [
                    ['day' => 'Lunes - Viernes', 'window' => '08:30 - 18:00', 'channel' => 'Atencion sucursal'],
                    ['day' => 'Sabado', 'window' => '09:00 - 14:00', 'channel' => 'Atencion telefonica'],
                    ['day' => 'Festivos', 'window' => 'Cerrado', 'channel' => 'Todos los canales'],
                ],
                'pendingApprovals' => [
                    [
                        'id' => 'CFG-204',
                        'change' => 'Actualizar tope microcredito a $65,000',
                        'owner' => 'Ana Lopez',
                        'impact' => 'Medio',
                        'status' => 'Pendiente firma',
                    ],
                    [
                        'id' => 'CFG-205',
                        'change' => 'Activar alertas de vencimiento 48h',
                        'owner' => 'Rogelio Nunez',
                        'impact' => 'Alto',
                        'status' => 'Requiere QA',
                    ],
                    [
                        'id' => 'CFG-206',
                        'change' => 'Nueva politica de horario extendido',
                        'owner' => 'Maria Campos',
                        'impact' => 'Bajo',
                        'status' => 'Listo para publicar',
                    ],
                ],
            ],
            'asignaciones' => [
                'kpis' => [
                    ['label' => 'Promotores activos', 'value' => 48, 'delta' => '+6 esta semana'],
                    ['label' => 'Supervisores', 'value' => 12, 'delta' => 'Cobertura 95%'],
                    ['label' => 'Ejecutivos comerciales', 'value' => 6, 'delta' => '1 vacante abierta'],
                    ['label' => 'Zonas con riesgo', 'value' => 3, 'delta' => 'Reasignacion sugerida'],
                ],
                'zones' => [
                    [
                        'name' => 'Zona Centro CDMX',
                        'supervisor' => 'Claudia Trevino',
                        'promoters' => [
                            'Sergio Ortega',
                            'Ana Beltran',
                            'Luis Castaneda',
                            'Maria Villalobos',
                        ],
                        'coverage' => '100%',
                        'pending' => 'Sin pendientes',
                    ],
                    [
                        'name' => 'Zona Toluca',
                        'supervisor' => 'Jorge Ramirez',
                        'promoters' => [
                            'Josefina Diaz',
                            'Carlos Mendoza',
                        ],
                        'coverage' => '74%',
                        'pending' => 'Reclutar 1 promotor',
                    ],
                    [
                        'name' => 'Zona Puebla',
                        'supervisor' => 'Erika Flores',
                        'promoters' => [
                            'Marcos Rivera',
                            'Gabriela Sanchez',
                            'Rosa Mejia',
                        ],
                        'coverage' => '88%',
                        'pending' => 'Capacitar nuevo ingreso',
                    ],
                ],
                'alerts' => [
                    [
                        'type' => 'warning',
                        'title' => 'Sobrecarga en Toluca',
                        'message' => 'Carlos Mendoza atiende 140% de la meta. Evalua mover prospectos a la nueva vacante.',
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Rotacion reciente',
                        'message' => '2 promotores cambiaron de zona entre el 15 y 18 ene. Actualiza listas de seguimiento.',
                    ],
                ],
            ],
            'inversiones' => [
                'investmentMetrics' => [
                    ['label' => 'Capital comprometido', 'value' => '$ 9.6 M', 'trend' => '+1.2 M nuevos acuerdos', 'color' => 'blue'],
                    ['label' => 'Capital disponible', 'value' => '$ 2.1 M', 'trend' => 'Reserva para febrero', 'color' => 'emerald'],
                    ['label' => 'Rendimiento promedio', 'value' => '14.8%', 'trend' => '+0.6 pts vs 2024', 'color' => 'purple'],
                    ['label' => 'Inversionistas activos', 'value' => '28', 'trend' => '3 en onboarding', 'color' => 'amber'],
                ],
                'pipelineStages' => [
                    'Captura' => [
                        ['folio' => 'INV-245', 'inversionista' => 'Sofia Navarro', 'monto' => '$180,000', 'producto' => 'Pagare 6m', 'avance' => 'Documentacion recibida'],
                        ['folio' => 'INV-246', 'inversionista' => 'Fondo Delta', 'monto' => '$750,000', 'producto' => 'Nota estructurada', 'avance' => 'Pendiente KYC'],
                    ],
                    'Comite' => [
                        ['folio' => 'INV-238', 'inversionista' => 'Eduardo Morales', 'monto' => '$250,000', 'producto' => 'Pagare 12m', 'avance' => 'Revision legal 80%'],
                        ['folio' => 'INV-239', 'inversionista' => 'Capital Aurora', 'monto' => '$1,200,000', 'producto' => 'Pool Carrusel', 'avance' => 'Ajuste de terminos'],
                    ],
                    'Firma' => [
                        ['folio' => 'INV-233', 'inversionista' => 'Maria Contreras', 'monto' => '$320,000', 'producto' => 'Pagare 9m', 'avance' => 'Firma programada 22 ene'],
                        ['folio' => 'INV-231', 'inversionista' => 'Family Office GEA', 'monto' => '$1,500,000', 'producto' => 'Pool senior', 'avance' => 'Tesoreria confirmada'],
                    ],
                ],
                'upcomingFlows' => [
                    ['fecha' => '22 ene 2025', 'concepto' => 'Firma contrato INV-233', 'monto' => '$320,000', 'responsable' => 'Mesa Inversiones'],
                    ['fecha' => '24 ene 2025', 'concepto' => 'Pago intereses Fondo Delta', 'monto' => '$115,400', 'responsable' => 'Tesoreria'],
                    ['fecha' => '29 ene 2025', 'concepto' => 'Renovacion Capital Aurora (50%)', 'monto' => '$600,000', 'responsable' => 'Relacion Inversionistas'],
                ],
                'portfolio' => [
                    ['nombre' => 'Pagare 6 meses', 'monto' => '$3,150,000', 'tasa' => '13.2%', 'vencimientos' => 'Mar - Jun 2025', 'estatus' => 'Activo'],
                    ['nombre' => 'Pagare 12 meses', 'monto' => '$2,780,000', 'tasa' => '15.5%', 'vencimientos' => 'Jul - Dic 2025', 'estatus' => 'Activo'],
                    ['nombre' => 'Nota estructurada PYME', 'monto' => '$1,450,000', 'tasa' => '18.0%', 'vencimientos' => 'Sep 2025', 'estatus' => 'Seguimiento'],
                    ['nombre' => 'Pool senior', 'monto' => '$2,240,000', 'tasa' => '11.8%', 'vencimientos' => 'Ene 2026', 'estatus' => 'En armado'],
                ],
            ],
            'cartera_global' => [
                'metrics' => [
                    ['label' => 'Cartera vigente', 'value' => '$ 18.4 M', 'trend' => '+4.2% vs mes anterior', 'badge' => 'Estable', 'color' => 'emerald'],
                    ['label' => 'Cartera en riesgo', 'value' => '$ 2.7 M', 'trend' => '+0.8% alerta', 'badge' => 'Atencion', 'color' => 'amber'],
                    ['label' => 'Cartera vencida', 'value' => '$ 1.1 M', 'trend' => '-1.9% recuperacion', 'badge' => 'En mejora', 'color' => 'blue'],
                    ['label' => 'Clientes activos', 'value' => '1,286', 'trend' => '+32 altas', 'badge' => 'Crecimiento', 'color' => 'purple'],
                ],
                'segments' => [
                    ['name' => 'Microcredito', 'amount' => 8.2, 'accounts' => 612],
                    ['name' => 'Nomina', 'amount' => 4.9, 'accounts' => 342],
                    ['name' => 'PyME', 'amount' => 3.6, 'accounts' => 158],
                    ['name' => 'Reestructura', 'amount' => 1.7, 'accounts' => 94],
                ],
                'branches' => [
                    [
                        'plaza' => 'CDMX Centro',
                        'vigente' => '$5,120,000',
                        'riesgo' => '$420,500',
                        'vencida' => '$188,300',
                        'mora' => '4.2%',
                        'lead' => 'Claudia Trevino',
                    ],
                    [
                        'plaza' => 'Toluca',
                        'vigente' => '$3,840,000',
                        'riesgo' => '$365,200',
                        'vencida' => '$141,900',
                        'mora' => '3.6%',
                        'lead' => 'Jorge Ramirez',
                    ],
                    [
                        'plaza' => 'Puebla',
                        'vigente' => '$2,970,000',
                        'riesgo' => '$458,000',
                        'vencida' => '$205,400',
                        'mora' => '5.1%',
                        'lead' => 'Erika Flores',
                    ],
                    [
                        'plaza' => 'Queretaro',
                        'vigente' => '$2,130,000',
                        'riesgo' => '$289,600',
                        'vencida' => '$122,000',
                        'mora' => '3.9%',
                        'lead' => 'Luis Tellez',
                    ],
                ],
            ],
            'ventas_desembolsos' => [
                'weeklyMetrics' => [
                    ['label' => 'Colocacion semanal', 'value' => '$ 3.2 M', 'trend' => '+12% vs semana anterior'],
                    ['label' => 'Cantidad de creditos', 'value' => '186', 'trend' => '+24 nuevos expedientes'],
                    ['label' => 'Desembolsos hoy', 'value' => '$ 520 K', 'trend' => '9 operaciones liberadas'],
                    ['label' => 'Tiempo promedio', 'value' => '6h 20m', 'trend' => '-45m en validacion'],
                ],
                'topPromoters' => [
                    ['name' => 'Ana Beltran', 'zone' => 'CDMX Centro', 'amount' => '$412K', 'deals' => 18, 'badge' => 'Top 1'],
                    ['name' => 'Luis Castaneda', 'zone' => 'CDMX Centro', 'amount' => '$386K', 'deals' => 16, 'badge' => 'Top 2'],
                    ['name' => 'Marcos Rivera', 'zone' => 'Puebla', 'amount' => '$358K', 'deals' => 14, 'badge' => 'Top 3'],
                    ['name' => 'Rosa Mejia', 'zone' => 'Puebla', 'amount' => '$331K', 'deals' => 15, 'badge' => 'Top 4'],
                ],
                'dailyTimeline' => [
                    ['time' => '08:45', 'title' => 'Desembolso CR-5489', 'description' => 'Cliente: Laura Campos - $45,000 - Promotor: Ana Beltran', 'status' => 'Liberado'],
                    ['time' => '10:12', 'title' => 'Validacion CR-5531', 'description' => 'Cliente: Rogelio Nunez - $38,000 - En revision de documentacion', 'status' => 'En curso'],
                    ['time' => '11:40', 'title' => 'Cancelacion CR-5495', 'description' => 'Cliente: Diego Paez - Motivo: Documentacion incompleta', 'status' => 'Cancelado'],
                    ['time' => '13:05', 'title' => 'Desembolso CR-5542', 'description' => 'Cliente: Miriam Soto - $52,000 - Promotor: Marcos Rivera', 'status' => 'Liberado'],
                ],
                'pipeline' => [
                    'captura' => [
                        ['folio' => 'CR-5551', 'cliente' => 'Gina Duarte', 'monto' => '$36,500', 'responsable' => 'Carlos Mendoza', 'eta' => 'Hoy 16:00'],
                        ['folio' => 'CR-5552', 'cliente' => 'Luis Juarez', 'monto' => '$28,000', 'responsable' => 'Rosa Mejia', 'eta' => 'Hoy 17:30'],
                    ],
                    'revision' => [
                        ['folio' => 'CR-5538', 'cliente' => 'Mauro Perez', 'monto' => '$40,000', 'responsable' => 'Mesa documental', 'eta' => 'Manana 11:00'],
                        ['folio' => 'CR-5539', 'cliente' => 'Alma Maldonado', 'monto' => '$32,000', 'responsable' => 'Compliance', 'eta' => 'Manana 13:30'],
                    ],
                    'liberacion' => [
                        ['folio' => 'CR-5528', 'cliente' => 'Ignacio Torres', 'monto' => '$48,000', 'responsable' => 'Tesoreria', 'eta' => 'Hoy 18:00'],
                        ['folio' => 'CR-5529', 'cliente' => 'Diana Lopez', 'monto' => '$51,000', 'responsable' => 'Tesoreria', 'eta' => 'Hoy 19:00'],
                    ],
                ],
            ],
            'desembolsos_inversion' => [
                'metrics' => [
                    ['label' => 'Total programado hoy', 'value' => '$ 1.9 M', 'trend' => '26 operaciones'],
                    ['label' => 'Pendientes de firma', 'value' => 8, 'trend' => '4 requieren pagare'],
                    ['label' => 'Tiempo promedio liberacion', 'value' => '5h 10m', 'trend' => '-30m vs semana pasada'],
                    ['label' => 'Caja conciliada', 'value' => '$ 980 K', 'trend' => 'Ultimo corte 20 ene'],
                ],
                'filters' => [
                    'executives' => ['Todos', 'Mariana Torres', 'Eduardo Nunez', 'Selene Vega'],
                    'supervisors' => ['Todos', 'Luis Hernandez', 'Cecilia Ramos', 'Patricio Luna'],
                ],
                'disbursements' => [
                    [
                        'folio' => 'DES-9021',
                        'cliente' => 'Cooperativa Semilla',
                        'monto' => '$420,000',
                        'fecha' => '21 ene 2025',
                        'ejecutivo' => 'Mariana Torres',
                        'supervisor' => 'Luis Hernandez',
                        'estado' => 'Programado',
                        'ventanilla' => 'Sucursal Centro',
                        'documentos' => ['Desembolso', 'Recibo', 'Pagare grupal'],
                    ],
                    [
                        'folio' => 'DES-9025',
                        'cliente' => 'Grupo Amanecer',
                        'monto' => '$280,000',
                        'fecha' => '21 ene 2025',
                        'ejecutivo' => 'Selene Vega',
                        'supervisor' => 'Cecilia Ramos',
                        'estado' => 'Liberado',
                        'ventanilla' => 'Tesoreria Matriz',
                        'documentos' => ['Desembolso', 'Recibo'],
                    ],
                    [
                        'folio' => 'DES-9016',
                        'cliente' => 'Inversion Social Norte',
                        'monto' => '$360,000',
                        'fecha' => '20 ene 2025',
                        'ejecutivo' => 'Eduardo Nunez',
                        'supervisor' => 'Patricio Luna',
                        'estado' => 'Por recibir',
                        'ventanilla' => 'Sucursal Puebla',
                        'documentos' => ['Desembolso', 'Pagare grupal'],
                    ],
                ],
                'documentTemplates' => [
                    ['name' => 'Formato Desembolso Inversion', 'owner' => 'Tesoreria', 'status' => 'Listo', 'updated_at' => '2025-01-19'],
                    ['name' => 'Recibo de entrega', 'owner' => 'Caja Matriz', 'status' => 'En revision', 'updated_at' => '2025-01-20'],
                    ['name' => 'Pagare grupal', 'owner' => 'Juridico', 'status' => 'Requiere firma', 'updated_at' => '2025-01-18'],
                ],
                'cashMovements' => [
                    'recepcion' => [
                        ['hora' => '08:30', 'referencia' => 'Deposito ejecutivo MT-02', 'responsable' => 'Caja Matriz', 'monto' => '$250,000', 'recibo' => 'REC-7812'],
                        ['hora' => '10:45', 'referencia' => 'Recaudo promotoras zona norte', 'responsable' => 'Tesoreria', 'monto' => '$190,000', 'recibo' => 'REC-7819'],
                    ],
                    'entrega' => [
                        ['hora' => '09:15', 'referencia' => 'Entrega grupo Amanecer', 'responsable' => 'Sucursal Centro', 'monto' => '$280,000', 'recibo' => 'ENT-4451'],
                        ['hora' => '12:10', 'referencia' => 'Programacion Cooperativa Semilla', 'responsable' => 'Tesoreria', 'monto' => '$420,000', 'recibo' => 'ENT-4454'],
                    ],
                ],
                'receiptHistory' => [
                    ['folio' => 'REC-7798', 'entregado_a' => 'Equipo Puebla', 'monto' => '$180,000', 'fecha' => '20 ene 2025', 'firmado' => 'Si'],
                    ['folio' => 'REC-7802', 'entregado_a' => 'Grupo Amanecer', 'monto' => '$280,000', 'fecha' => '20 ene 2025', 'firmado' => 'Si'],
                    ['folio' => 'REC-7807', 'entregado_a' => 'Cooperativa Semilla', 'monto' => '$420,000', 'fecha' => '21 ene 2025', 'firmado' => 'Pendiente'],
                ],
            ],
            'cierre_semanal' => [
                'summary' => [
                    ['label' => 'Ventas netas', 'value' => '$ 3.8 M', 'trend' => '+8% vs semana 02'],
                    ['label' => 'Desembolsos', 'value' => '$ 3.2 M', 'trend' => '85% efectividad'],
                    ['label' => 'Nuevos clientes', 'value' => 112, 'trend' => '+14 contra meta'],
                    ['label' => 'Cartera en riesgo 8-30', 'value' => '$ 640 K', 'trend' => '+1.2%'],
                ],
                'filters' => [
                    'periods' => ['Semana 03 2025', 'Semana 02 2025', 'Semana 01 2025', 'Semana 52 2024'],
                    'supervisors' => ['Todos', 'Claudia Trevino', 'Jorge Ramirez', 'Erika Flores', 'Luis Tellez'],
                    'executives' => ['Todos', 'Mariana Torres', 'Selene Vega', 'Eduardo Nunez'],
                ],
                'teamBreakdown' => [
                    [
                        'promotora' => 'Equipo Centro',
                        'ventas' => '$1,280,000',
                        'desembolsos' => '$1,150,000',
                        'nuevos' => 28,
                        'recreditos' => 14,
                        'mora7' => '3.2%',
                        'supervisor' => 'Claudia Trevino',
                        'ejecutivo' => 'Mariana Torres',
                    ],
                    [
                        'promotora' => 'Equipo Toluca',
                        'ventas' => '$980,000',
                        'desembolsos' => '$860,000',
                        'nuevos' => 22,
                        'recreditos' => 11,
                        'mora7' => '4.6%',
                        'supervisor' => 'Jorge Ramirez',
                        'ejecutivo' => 'Selene Vega',
                    ],
                    [
                        'promotora' => 'Equipo Puebla',
                        'ventas' => '$920,000',
                        'desembolsos' => '$780,000',
                        'nuevos' => 24,
                        'recreditos' => 13,
                        'mora7' => '5.1%',
                        'supervisor' => 'Erika Flores',
                        'ejecutivo' => 'Eduardo Nunez',
                    ],
                    [
                        'promotora' => 'Equipo Queretaro',
                        'ventas' => '$620,000',
                        'desembolsos' => '$510,000',
                        'nuevos' => 18,
                        'recreditos' => 9,
                        'mora7' => '3.7%',
                        'supervisor' => 'Luis Tellez',
                        'ejecutivo' => 'Selene Vega',
                    ],
                ],
                'topPerformers' => [
                    ['promoter' => 'Ana Beltran', 'ventas' => '$180,000', 'colocaciones' => 9],
                    ['promoter' => 'Sergio Ortega', 'ventas' => '$165,000', 'colocaciones' => 8],
                    ['promoter' => 'Maria Villalobos', 'ventas' => '$158,000', 'colocaciones' => 8],
                ],
                'alerts' => [
                    ['type' => 'warning', 'title' => 'Mora 8-30 dias', 'detail' => 'Zona Toluca incremento 1.2 pts, revisar promotoras Alexis y Diana.'],
                    ['type' => 'info', 'title' => 'Bonificacion semanal', 'detail' => 'Equipo Centro alcanzo 105% de meta, liberar bono.'],
                    ['type' => 'critical', 'title' => 'Entrega atrasada', 'detail' => 'Pagare grupal Las Flores sin firma al corte.'],
                ],
                'weeklyActions' => [
                    ['area' => 'Supervision', 'item' => 'Visitas campo', 'value' => '12 completadas', 'note' => '2 reagendadas por clima'],
                    ['area' => 'Ejecutivo', 'item' => 'Reunion seguimiento', 'value' => '4 sesiones', 'note' => 'Foco reestructuras'],
                    ['area' => 'Promotoras', 'item' => 'Capacitacion producto', 'value' => '3 talleres', 'note' => 'Material actualizado'],
                ],
                'pendingFollowUps' => [
                    ['concepto' => 'Pagare grupal Las Flores', 'responsable' => 'Ejecutivo Vega', 'fecha' => '23 ene 2025', 'estatus' => 'En firma'],
                    ['concepto' => 'Reasignacion clientes extra', 'responsable' => 'Supervisor Toluca', 'fecha' => '22 ene 2025', 'estatus' => 'Pendiente aprobacion'],
                    ['concepto' => 'Reporte mora semanal', 'responsable' => 'Auditoria', 'fecha' => '21 ene 2025', 'estatus' => 'Publicado'],
                ],
            ],
            'auditoria_seguridad' => [
                'securityAlerts' => [
                    [
                        'type' => 'critical',
                        'title' => 'Cartera con mora critica',
                        'detail' => 'Cliente: CU-45812 - 47 dias vencido - Saldo $58,200 MXN.',
                        'timestamp' => '21 ene 2025 - 09:10',
                        'action' => 'Abrir expediente',
                    ],
                    [
                        'type' => 'warning',
                        'title' => 'Promotor con tasa de rechazo inusual',
                        'detail' => 'Promotor: PR-221 - 5 creditos rechazados y 3 cancelados en 48 h.',
                        'timestamp' => '20 ene 2025 - 19:25',
                        'action' => 'Analizar desempeno',
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Supervision de ajustes manuales reiterados',
                        'detail' => 'Supervisor: sgutierrez - Ajusto score de 4 clientes en 12 h.',
                        'timestamp' => '20 ene 2025 - 12:40',
                        'action' => 'Notificar auditoria',
                    ],
                ],
                'activityLog' => [
                    ['time' => '21 ene - 10:32', 'user' => 'promotor.lgomez', 'module' => 'Cartera', 'event' => 'Registro pago parcial manual por $12,500 MXN', 'origin' => 'Sucursal Puebla'],
                    ['time' => '21 ene - 09:58', 'user' => 'supervisor.aramirez', 'module' => 'Supervision', 'event' => 'Autorizo reestructura express para cliente con score 520', 'origin' => 'Regional Bajio'],
                    ['time' => '20 ene - 18:45', 'user' => 'finanzas@kuali', 'module' => 'Riesgos', 'event' => 'Actualizo lista de clientes con mora >30 dias', 'origin' => 'Comite de riesgo'],
                    ['time' => '20 ene - 16:10', 'user' => 'auditoria@kuali', 'module' => 'Auditoria interna', 'event' => 'Agrego evidencias al expediente PR-441', 'origin' => 'Mesa de control'],
                ],
                'sessionOverview' => [
                    ['user' => 'promotor.lgomez', 'role' => 'Promotor', 'region' => 'Zona Centro', 'status' => 'Bajo observacion', 'last_seen' => 'Hace 6 min', 'note' => '3 clientes con vencimiento >20 dias'],
                    ['user' => 'supervisor.aramirez', 'role' => 'Supervisor', 'region' => 'Bajio', 'status' => 'Seguimiento activo', 'last_seen' => 'Hace 14 min', 'note' => 'Revisando excepciones de bonificacion'],
                    ['user' => 'ejecutivo.mrivera', 'role' => 'Ejecutivo', 'region' => 'Metropolitana', 'status' => 'Sin hallazgos', 'last_seen' => 'Hace 45 min', 'note' => 'Sin ajustes manuales recientes'],
                    ['user' => 'analista.seguridad', 'role' => 'Analista de riesgo', 'region' => 'Matriz', 'status' => 'Monitoreo', 'last_seen' => 'Hace 3 min', 'note' => 'Generando reporte diario'],
                ],
            ],
        ];
    }

    private function renderDashboard(string $activeSection)
    {
        $sections = $this->getDashboardData();

        return view('administrativo.dashboard', [
            'sections' => $sections,
            'activeSection' => $activeSection,
        ]);
    }

    /** Placeholder para el panel de autorizaciones y controles. */
    public function autorizaciones()
    {
        return $this->renderDashboard('autorizaciones');
    }

    /** Placeholder de configuracion general del sistema. */
    public function parametros()
    {
        return $this->renderDashboard('parametros');
    }

    /** Placeholder para reasignar jerarquias y zonas. */
    public function asignaciones()
    {
        return $this->renderDashboard('asignaciones');
    }

    /** Placeholder para la vista consolidada de cartera. */
    public function carteraGlobal()
    {
        return $this->renderDashboard('cartera_global');
    }

    /** Placeholder para seguimiento de ventas y desembolsos. */
    public function ventasDesembolsos()
    {
        return $this->renderDashboard('ventas_desembolsos');
    }

    /** Placeholder para seguimiento de desembolsos orientados a inversion. */
    public function desembolsosInversion()
    {
        return $this->renderDashboard('desembolsos_inversion');
    }

    /** Placeholder para resumen operativo semanal. */
    public function cierreSemanal()
    {
        return $this->renderDashboard('cierre_semanal');
    }

    /** Placeholder para las solicitudes y aprobaciones de inversion. */
    public function inversiones()
    {
        return $this->renderDashboard('inversiones');
    }

    /** Placeholder para auditoria y eventos de seguridad. */
    public function auditoriaSeguridad()
    {
        return $this->renderDashboard('auditoria_seguridad');
    }

    /** Vista dedicada al flujo de autorizacion granular. */
    public function autorizacion()
    {
        $authorizations = [
            [
                'numero' => '1',
                'title' => 'Creditos extraordinarios',
                'description' => 'Solicitudes cuyo monto supera el limite aprobado para el cliente y necesitan validacion especial.',
                'columns' => [
                    ['key' => 'folio', 'label' => 'Folio'],
                    ['key' => 'cliente', 'label' => 'Cliente'],
                    ['key' => 'monto_total', 'label' => 'Monto solicitado'],
                    ['key' => 'monto_maximo', 'label' => 'Limite autorizado'],
                    ['key' => 'excedente', 'label' => 'Monto excedente'],
                    ['key' => 'periodicidad', 'label' => 'Periodicidad de pago'],
                    ['key' => 'estado', 'label' => 'Estatus'],
                    ['key' => 'promotor', 'label' => 'Promotor responsable'],
                    ['key' => 'riesgo', 'label' => 'Riesgo'],
                ],
                'records' => [
                    [
                        'folio' => 'CR-2025-0118',
                        'cliente' => 'Laura Campos (CURP CACL820514MDFRMN04)',
                        'monto_total' => '$150,000.00',
                        'monto_maximo' => '$120,000.00',
                        'excedente' => '$30,000.00',
                        'periodicidad' => 'semanal',
                        'estado' => 'solicitado',
                        'promotor' => 'PROM-142 / promotor.lgomez',
                        'riesgo' => 'Alto',
                    ],
                    [
                        'folio' => 'CR-2025-0152',
                        'cliente' => 'Diego Nunez (CURP NUDD841210HDFJRG08)',
                        'monto_total' => '$98,500.00',
                        'monto_maximo' => '$70,000.00',
                        'excedente' => '$28,500.00',
                        'periodicidad' => 'quincenal',
                        'estado' => 'prospectado',
                        'promotor' => 'PROM-208 / promotor.ymedina',
                        'riesgo' => 'Medio',
                    ],
                    [
                        'folio' => 'CR-2025-0160',
                        'cliente' => 'Grupo Aurora (CURP GAAI771001MOCBRN07)',
                        'monto_total' => '$185,000.00',
                        'monto_maximo' => '$140,000.00',
                        'excedente' => '$45,000.00',
                        'periodicidad' => 'semanal',
                        'estado' => 'aprobado',
                        'promotor' => 'PROM-099 / promotor.ssolano',
                        'riesgo' => 'Alto',
                    ],
                ],
            ],
            [
                'numero' => '2',
                'title' => 'Recreditos fuera de tiempo',
                'description' => 'Clientes que piden un nuevo credito aun cuando el anterior sigue activo o ya vencio.',
                'columns' => [
                    ['key' => 'folio', 'label' => 'Folio'],
                    ['key' => 'cliente', 'label' => 'Cliente'],
                    ['key' => 'estado', 'label' => 'Estatus actual'],
                    ['key' => 'fecha_final', 'label' => 'Fecha de cierre prevista'],
                    ['key' => 'dias_fuera_tiempo', 'label' => 'Dias fuera de tiempo'],
                    ['key' => 'tiene_credito_activo', 'label' => 'Credito activo'],
                    ['key' => 'monto_total', 'label' => 'Monto solicitado'],
                    ['key' => 'riesgo', 'label' => 'Riesgo'],
                ],
                'records' => [
                    [
                        'folio' => 'CR-2024-0871',
                        'cliente' => 'Brenda Morales (CURP MOBB890623MDFRRL05)',
                        'estado' => 'prospectado_recredito',
                        'fecha_final' => '2024-12-20',
                        'dias_fuera_tiempo' => '32 dias',
                        'tiene_credito_activo' => 'Si',
                        'monto_total' => '$65,000.00',
                        'riesgo' => 'Alto',
                    ],
                    [
                        'folio' => 'CR-2024-0820',
                        'cliente' => 'Luis Ortega (CURP OERL851102HDFNRD02)',
                        'estado' => 'prospectado_recredito',
                        'fecha_final' => '2024-11-28',
                        'dias_fuera_tiempo' => '54 dias',
                        'tiene_credito_activo' => 'Si',
                        'monto_total' => '$48,500.00',
                        'riesgo' => 'Medio',
                    ],
                    [
                        'folio' => 'CR-2024-0799',
                        'cliente' => 'Yesenia Gomez (CURP GOYJ901007MDFSMN03)',
                        'estado' => 'prospectado_recredito',
                        'fecha_final' => '2024-10-30',
                        'dias_fuera_tiempo' => '83 dias',
                        'tiene_credito_activo' => 'Si',
                        'monto_total' => '$72,300.00',
                        'riesgo' => 'Alto',
                    ],
                ],
            ],
            [
                'numero' => '3',
                'title' => 'Montos superiores a la meta del promotor',
                'description' => 'Ventas que rebasan los topes habituales del promotor y requieren confirmacion antes de aprobarse.',
                'columns' => [
                    ['key' => 'promotor', 'label' => 'Promotor'],
                    ['key' => 'folio', 'label' => 'Folio'],
                    ['key' => 'monto_total', 'label' => 'Monto solicitado'],
                    ['key' => 'venta_maxima', 'label' => 'Topes personales'],
                    ['key' => 'venta_proyectada_objetivo', 'label' => 'Meta mensual'],
                    ['key' => 'porcentaje_exceso', 'label' => 'Exceso vs meta'],
                    ['key' => 'municipio', 'label' => 'Municipio del cliente'],
                    ['key' => 'riesgo', 'label' => 'Riesgo'],
                ],
                'records' => [
                    [
                        'promotor' => 'PROM-071 / promotor.aflores',
                        'folio' => 'CR-2025-0175',
                        'monto_total' => '$92,000.00',
                        'venta_maxima' => '$70,000.00',
                        'venta_proyectada_objetivo' => '$68,500.00',
                        'porcentaje_exceso' => '+31 %',
                        'municipio' => 'Iztapalapa',
                        'riesgo' => 'Medio',
                    ],
                    [
                        'promotor' => 'PROM-132 / promotor.jvelazquez',
                        'folio' => 'CR-2025-0179',
                        'monto_total' => '$110,500.00',
                        'venta_maxima' => '$80,000.00',
                        'venta_proyectada_objetivo' => '$75,000.00',
                        'porcentaje_exceso' => '+38 %',
                        'municipio' => 'Tlalnepantla',
                        'riesgo' => 'Alto',
                    ],
                    [
                        'promotor' => 'PROM-204 / promotor.rcortes',
                        'folio' => 'CR-2025-0182',
                        'monto_total' => '$68,200.00',
                        'venta_maxima' => '$55,000.00',
                        'venta_proyectada_objetivo' => '$53,000.00',
                        'porcentaje_exceso' => '+24 %',
                        'municipio' => 'Naucalpan',
                        'riesgo' => 'Medio',
                    ],
                ],
            ],
            [
                'numero' => '5',
                'title' => 'Domicilios y familiares repetidos',
                'description' => 'Familias o domicilios que comparten varios creditos y requieren una revision para evitar duplicidades.',
                'columns' => [
                    ['key' => 'folio', 'label' => 'Folio'],
                    ['key' => 'cliente', 'label' => 'Cliente'],
                    ['key' => 'domicilio', 'label' => 'Domicilio'],
                    ['key' => 'colonia_cp', 'label' => 'Colonia y CP'],
                    ['key' => 'personas_en_domicilio', 'label' => 'Personas en el hogar'],
                    ['key' => 'dependientes', 'label' => 'Dependientes economicos'],
                    ['key' => 'conyuge_vive_con_cliente', 'label' => 'Conyuge vive con el cliente'],
                    ['key' => 'comentarios', 'label' => 'Notas de seguimiento'],
                    ['key' => 'riesgo', 'label' => 'Riesgo'],
                ],
                'records' => [
                    [
                        'folio' => 'CR-2025-0189',
                        'cliente' => 'Marisol Vega (CURP VEMM900311MDFSRG06)',
                        'domicilio' => 'Calle Jade 112',
                        'colonia_cp' => 'Col. San Pedro, CP 09876',
                        'personas_en_domicilio' => '6',
                        'dependientes' => '4',
                        'conyuge_vive_con_cliente' => 'Si',
                        'comentarios' => 'Comparte domicilio con credito CR-2025-0192.',
                        'riesgo' => 'Medio',
                    ],
                    [
                        'folio' => 'CR-2025-0191',
                        'cliente' => 'Daniel Pineda (CURP PIDC870902HDFRNL03)',
                        'domicilio' => 'Av. Bosques 45',
                        'colonia_cp' => 'Col. Las Flores, CP 07650',
                        'personas_en_domicilio' => '8',
                        'dependientes' => '5',
                        'conyuge_vive_con_cliente' => 'No',
                        'comentarios' => 'Cuatro familiares activos como avales en el mismo domicilio.',
                        'riesgo' => 'Alto',
                    ],
                    [
                        'folio' => 'CR-2025-0194',
                        'cliente' => 'Rocio Hernandez (CURP HERJ931125MDFLRN01)',
                        'domicilio' => 'Priv. Encino 6',
                        'colonia_cp' => 'Col. Villas del Sur, CP 09730',
                        'personas_en_domicilio' => '5',
                        'dependientes' => '3',
                        'conyuge_vive_con_cliente' => 'Si',
                        'comentarios' => 'Informacion familiar coincide con cliente CR-2025-0188.',
                        'riesgo' => 'Medio',
                    ],
                ],
            ],
            [
                'numero' => '6',
                'title' => 'Clientes con adeudos',
                'description' => 'Personas con atrasos importantes o creditos cancelados que mantienen un saldo pendiente.',
                'columns' => [
                    ['key' => 'cliente', 'label' => 'Cliente'],
                    ['key' => 'cliente_estado', 'label' => 'Estatus del cliente'],
                    ['key' => 'folio', 'label' => 'Folio'],
                    ['key' => 'estado_credito', 'label' => 'Estatus del credito'],
                    ['key' => 'fecha_final', 'label' => 'Fecha de cierre prevista'],
                    ['key' => 'dias_atraso', 'label' => 'Dias de atraso'],
                    ['key' => 'monto_total', 'label' => 'Saldo pendiente'],
                    ['key' => 'riesgo', 'label' => 'Riesgo'],
                ],
                'records' => [
                    [
                        'cliente' => 'Isabel Cortes (CURP COII870215MDFNRB07)',
                        'cliente_estado' => 'deudor',
                        'folio' => 'CR-2024-0755',
                        'estado_credito' => 'vencido',
                        'fecha_final' => '2024-11-30',
                        'dias_atraso' => '52 dias',
                        'monto_total' => '$43,200.00',
                        'riesgo' => 'Alto',
                    ],
                    [
                        'cliente' => 'Jorge Herrera (CURP HEGJ880901HDFRRN02)',
                        'cliente_estado' => 'deudor',
                        'folio' => 'CR-2024-0719',
                        'estado_credito' => 'cancelado',
                        'fecha_final' => '2024-09-18',
                        'dias_atraso' => '118 dias',
                        'monto_total' => '$58,900.00',
                        'riesgo' => 'Alto',
                    ],
                    [
                        'cliente' => 'Paola Oviedo (CURP OVPF900406MDFVLR04)',
                        'cliente_estado' => 'deudor',
                        'folio' => 'CR-2024-0694',
                        'estado_credito' => 'vencido',
                        'fecha_final' => '2024-10-12',
                        'dias_atraso' => '94 dias',
                        'monto_total' => '$36,700.00',
                        'riesgo' => 'Medio',
                    ],
                ],
            ],
            [
                'numero' => '7',
                'title' => 'Cambio de plaza o supervisor',
                'description' => 'Clientes que mudaron su negocio a otra zona y necesitan reasignacion de supervisor.',
                'columns' => [
                    ['key' => 'cliente', 'label' => 'Cliente'],
                    ['key' => 'folio', 'label' => 'Folio'],
                    ['key' => 'municipio', 'label' => 'Nuevo municipio'],
                    ['key' => 'supervisor_actual', 'label' => 'Supervisor actual'],
                    ['key' => 'supervisor_sugerido', 'label' => 'Supervisor sugerido'],
                    ['key' => 'promotor', 'label' => 'Promotor'],
                    ['key' => 'comentarios', 'label' => 'Notas de seguimiento'],
                    ['key' => 'riesgo', 'label' => 'Riesgo'],
                ],
                'records' => [
                    [
                        'cliente' => 'Gabriel Rios (CURP RIOG880715HDFLSB01)',
                        'folio' => 'CR-2025-0186',
                        'municipio' => 'Queretaro',
                        'supervisor_actual' => 'Erika Flores',
                        'supervisor_sugerido' => 'Carlos Ramirez',
                        'promotor' => 'PROM-188 / promotor.lgallardo',
                        'comentarios' => 'Cliente cambio su negocio a plaza Bajio centro.',
                        'riesgo' => 'Medio',
                    ],
                    [
                        'cliente' => 'Monica Estrada (CURP ESMN900923MDFRRL06)',
                        'folio' => 'CR-2025-0190',
                        'municipio' => 'Tuxtla Gutierrez',
                        'supervisor_actual' => 'Luis Cabrera',
                        'supervisor_sugerido' => 'Andrea Molina',
                        'promotor' => 'PROM-215 / promotor.zmurillo',
                        'comentarios' => 'Requiere soporte del cluster sureste por nueva ubicacion.',
                        'riesgo' => 'Alto',
                    ],
                    [
                        'cliente' => 'Roberto Salazar (CURP SARJ851210HDFBRT05)',
                        'folio' => 'CR-2025-0193',
                        'municipio' => 'Ciudad Obregon',
                        'supervisor_actual' => 'Ana Lopez',
                        'supervisor_sugerido' => 'Hector Molina',
                        'promotor' => 'PROM-164 / promotor.cparedes',
                        'comentarios' => 'Sucursal original sin presencia en la nueva plaza.',
                        'riesgo' => 'Medio',
                    ],
                ],
            ],
        ];

        $totalSolicitudes = array_sum(array_map(static fn ($section) => count($section['records']), $authorizations));
        $altoRiesgo = array_sum(array_map(
            static fn ($section) => count(array_filter(
                $section['records'],
                static fn ($record) => ($record['riesgo'] ?? null) === 'Alto'
            )),
            $authorizations
        ));

        $stats = [
            'categorias' => count($authorizations),
            'solicitudes' => $totalSolicitudes,
            'alertas_altas' => $altoRiesgo,
        ];

        return view('administrativo.autorizacion', compact('authorizations', 'stats'));
    }

    /** Registro y onboarding de nuevos colaboradores. */
    public function nuevosColaboradores()
    {
        $pipeline = [
            [
                'nombre' => 'Karen Ruiz',
                'posicion' => 'Promotor regional',
                'estatus' => 'Documentacion',
                'ingreso_estimado' => '03 feb 2025',
                'region' => 'Bajio',
                'responsable' => 'rrhh.cnavarro',
            ],
            [
                'nombre' => 'Jose Martinez',
                'posicion' => 'Analista de riesgo',
                'estatus' => 'Psicometrico',
                'ingreso_estimado' => '10 feb 2025',
                'region' => 'Matriz',
                'responsable' => 'rrhh.lherrera',
            ],
            [
                'nombre' => 'Sandra Ochoa',
                'posicion' => 'Ejecutivo de cobranza',
                'estatus' => 'Capacitacion',
                'ingreso_estimado' => '27 ene 2025',
                'region' => 'Occidente',
                'responsable' => 'operaciones.jvilla',
            ],
        ];

        $inductionSchedule = [
            [
                'fecha' => '23 ene 2025',
                'tema' => 'Modelo de negocio y valores',
                'ponente' => 'Direccion general',
            ],
            [
                'fecha' => '24 ene 2025',
                'tema' => 'Politicas operativas y compliance',
                'ponente' => 'Control interno',
            ],
            [
                'fecha' => '27 ene 2025',
                'tema' => 'Herramientas digitales de promotor',
                'ponente' => 'Innovacion tecnologica',
            ],
        ];

        $openPositions = [
            ['puesto' => 'Supervisor de zona', 'region' => 'Sureste', 'vacantes' => 1, 'prioridad' => 'Alta'],
            ['puesto' => 'Analista documental', 'region' => 'Matriz', 'vacantes' => 2, 'prioridad' => 'Media'],
            ['puesto' => 'Promotor senior', 'region' => 'Centro', 'vacantes' => 3, 'prioridad' => 'Alta'],
        ];

        return view('administrativo.nuevos-colaboradores', compact('pipeline', 'inductionSchedule', 'openPositions'));
    }

    /** Seguimiento a planes de apertura de nuevas plazas. */
    public function probablesAperturas()
    {
        $promotores = collect($this->probablesAperturasCatalog())
            ->map(function (array $registro) {
                return [
                    'id' => $registro['id'],
                    'promotor_aperturado' => $registro['promotor_aperturado'],
                    'promotor_responsable' => $registro['promotor_responsable'],
                    'territorio' => $registro['territorio'],
                    'fase' => $registro['fase'],
                    'ultima_actualizacion' => $registro['ultima_actualizacion'],
                ];
            })
            ->values()
            ->all();

        return view('administrativo.probables-aperturas.index', compact('promotores'));
    }

    public function probablesAperturasShow(int $promotor)
    {
        $registro = collect($this->probablesAperturasCatalog())->firstWhere('id', $promotor);

        if (! $registro) {
            abort(404);
        }

        return view('administrativo.probables-aperturas.show', [
            'promotor' => $registro,
        ]);
    }

    protected function probablesAperturasCatalog(): array
    {
        return [
            [
                'id' => 1,
                'promotor_aperturado' => 'Laura Hernandez',
                'promotor_responsable' => 'Marcos Diaz',
                'territorio' => 'Queretaro Norte',
                'fase' => 'Encuesta validada',
                'ultima_actualizacion' => '22 ene 2025',
                'resumen' => 'Perfil con experiencia previa en microfinanzas y cartera comunitaria en zona norte.',
                'siguiente_paso' => 'Agendar visita con supervisor regional y validar referencias laborales.',
                'encuesta' => [
                    [
                        'titulo' => 'Datos generales',
                        'items' => [
                            ['label' => 'Edad', 'value' => '29 anos'],
                            ['label' => 'Estado civil', 'value' => 'Casada'],
                            ['label' => 'Dependientes economicos', 'value' => '2'],
                        ],
                    ],
                    [
                        'titulo' => 'Vivienda y entorno',
                        'items' => [
                            ['label' => 'Tipo de vivienda', 'value' => 'Arrendada'],
                            ['label' => 'Antiguedad en domicilio', 'value' => '4 anos'],
                            ['label' => 'Servicios basicos', 'value' => 'Agua, luz, internet'],
                        ],
                    ],
                    [
                        'titulo' => 'Ingresos y gastos',
                        'items' => [
                            ['label' => 'Ingreso mensual', 'value' => '$16,500'],
                            ['label' => 'Gasto fijo mensual', 'value' => '$9,800'],
                            ['label' => 'Capacidad de ahorro', 'value' => '$2,300'],
                        ],
                    ],
                    [
                        'titulo' => 'Referencias',
                        'items' => [
                            ['label' => 'Referencia personal', 'value' => 'Sandra Mendez (coordinadora escolar)'],
                            ['label' => 'Referencia laboral', 'value' => 'Pedro Campos (ex supervisor)'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 2,
                'promotor_aperturado' => 'Kevin Valenzuela',
                'promotor_responsable' => 'Rocio Martinez',
                'territorio' => 'Tuxtla Centro',
                'fase' => 'Validacion documental',
                'ultima_actualizacion' => '19 ene 2025',
                'resumen' => 'Recomendado por la red de supervisores, con historial de venta directa y alto cierre.',
                'siguiente_paso' => 'Completar verificaciones domiciliarias y revisar comprobantes fiscales.',
                'encuesta' => [
                    [
                        'titulo' => 'Datos generales',
                        'items' => [
                            ['label' => 'Edad', 'value' => '33 anos'],
                            ['label' => 'Estado civil', 'value' => 'Soltero'],
                            ['label' => 'Dependientes economicos', 'value' => '1'],
                        ],
                    ],
                    [
                        'titulo' => 'Actividad actual',
                        'items' => [
                            ['label' => 'Ocupacion', 'value' => 'Promotor independiente'],
                            ['label' => 'Antiguedad en actividad', 'value' => '5 anos'],
                            ['label' => 'Disponibilidad de horario', 'value' => 'Completa'],
                        ],
                    ],
                    [
                        'titulo' => 'Ingresos y egresos',
                        'items' => [
                            ['label' => 'Ingreso mensual', 'value' => '$18,200'],
                            ['label' => 'Gasto fijo mensual', 'value' => '$7,900'],
                            ['label' => 'Compromisos crediticios', 'value' => 'Credito automotriz $3,500'],
                        ],
                    ],
                    [
                        'titulo' => 'Referencias',
                        'items' => [
                            ['label' => 'Referencia personal', 'value' => 'Lucia Ortega (cliente)'],
                            ['label' => 'Referencia laboral', 'value' => 'Mario Espinosa (supervisor externo)'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 3,
                'promotor_aperturado' => 'Beatriz Lara',
                'promotor_responsable' => 'Hector Aguilar',
                'territorio' => 'Ciudad Obregon Sur',
                'fase' => 'Entrevista inicial',
                'ultima_actualizacion' => '17 ene 2025',
                'resumen' => 'Lider comunitaria con enfoque en proyectos productivos y alto potencial de vinculacion.',
                'siguiente_paso' => 'Programar taller introductorio y levantar referencias comunitarias.',
                'encuesta' => [
                    [
                        'titulo' => 'Datos generales',
                        'items' => [
                            ['label' => 'Edad', 'value' => '41 anos'],
                            ['label' => 'Estado civil', 'value' => 'Unida'],
                            ['label' => 'Dependientes economicos', 'value' => '3'],
                        ],
                    ],
                    [
                        'titulo' => 'Vivienda y entorno',
                        'items' => [
                            ['label' => 'Tipo de vivienda', 'value' => 'Propia'],
                            ['label' => 'Antiguedad en comunidad', 'value' => '10 anos'],
                            ['label' => 'Participacion social', 'value' => 'Consejo de colonos'],
                        ],
                    ],
                    [
                        'titulo' => 'Economia familiar',
                        'items' => [
                            ['label' => 'Ingreso familiar', 'value' => '$22,000'],
                            ['label' => 'Egreso mensual', 'value' => '$13,500'],
                            ['label' => 'Apoyos adicionales', 'value' => 'Programa local de emprendimiento'],
                        ],
                    ],
                    [
                        'titulo' => 'Referencias',
                        'items' => [
                            ['label' => 'Referencia personal', 'value' => 'Ana Cordova (dirigente comunitaria)'],
                            ['label' => 'Referencia laboral', 'value' => 'Luis Rios (ONG Desarrollo Sur)'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /** Vista dedicada para capturar y revisar movimientos de entradas y salidas. */
    public function entradasSalidas(Request $request)
    {
        $periodos = [
            [
                'key' => 'dia',
                'label' => 'Dia',
                'descripcion' => 'Movimientos capturados el 24 ene 2025',
                'movimientos' => [
                    [
                        'tipo' => 'Entrada',
                        'concepto' => 'Cobranza diaria Tuxtla',
                        'notas' => 'Liquidacion de grupo "Fenix"',
                        'costo' => '$58,200.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Pago de comisiones',
                        'notas' => 'Referidos promotores',
                        'costo' => '$12,750.00',
                    ],
                ],
            ],
            [
                'key' => 'semana',
                'label' => 'Semana',
                'descripcion' => 'Resumen del 20 al 24 ene 2025',
                'movimientos' => [
                    [
                        'tipo' => 'Entrada',
                        'concepto' => 'Cobranza semanal promotores',
                        'notas' => 'Abonos consolidados mobile',
                        'costo' => '$385,400.00',
                    ],
                    [
                        'tipo' => 'Entrada',
                        'concepto' => 'Recuperacion cartera vencida',
                        'notas' => 'Plan de regularizacion',
                        'costo' => '$91,200.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Desembolsos autorizados',
                        'notas' => 'Comite semana 04',
                        'costo' => '$278,900.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Reposicion sucursales',
                        'notas' => 'Fondo operativo regional',
                        'costo' => '$66,000.00',
                    ],
                ],
            ],
            [
                'key' => 'mes',
                'label' => 'Mes',
                'descripcion' => 'Cifras acumuladas enero 2025',
                'movimientos' => [
                    [
                        'tipo' => 'Entrada',
                        'concepto' => 'Inversionistas serie B',
                        'notas' => 'Aporte extraordinario',
                        'costo' => '$1,200,000.00',
                    ],
                    [
                        'tipo' => 'Entrada',
                        'concepto' => 'Cobranza consolidada',
                        'notas' => 'Sucursales y mobile',
                        'costo' => '$1,845,750.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Desembolsos totales',
                        'notas' => 'Incluye nvos y renovaciones',
                        'costo' => '$1,612,430.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Gastos operativos',
                        'notas' => 'Nominas, logistica y soporte',
                        'costo' => '$412,600.00',
                    ],
                ],
            ],
            [
                'key' => 'trimestre',
                'label' => 'Trimestre',
                'descripcion' => 'Oct - Dic 2024',
                'movimientos' => [
                    [
                        'tipo' => 'Entrada',
                        'concepto' => 'Recuperacion total Q4',
                        'notas' => 'Cierre fiscal 2024',
                        'costo' => '$4,892,000.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Expansiones y mejoras',
                        'notas' => 'Infraestructura y tecnologia',
                        'costo' => '$1,280,500.00',
                    ],
                ],
            ],
            [
                'key' => 'semestre',
                'label' => 'Semestre',
                'descripcion' => 'Jul - Dic 2024',
                'movimientos' => [
                    [
                        'tipo' => 'Entrada',
                        'concepto' => 'Cobranza H2',
                        'notas' => 'Programas vigentes',
                        'costo' => '$9,540,000.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Desembolsos H2',
                        'notas' => 'Incluye fondo rotativo',
                        'costo' => '$8,470,300.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Inversion capital humano',
                        'notas' => 'Capacitaciones y contrataciones',
                        'costo' => '$680,000.00',
                    ],
                ],
            ],
            [
                'key' => 'anio',
                'label' => 'Anual',
                'descripcion' => 'Ejercicio 2024 completo',
                'movimientos' => [
                    [
                        'tipo' => 'Entrada',
                        'concepto' => 'Ingresos totales',
                        'notas' => 'Cobranza + inversionistas',
                        'costo' => '$18,920,000.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Egresos totales',
                        'notas' => 'Desembolsos + gastos operativos',
                        'costo' => '$16,870,000.00',
                    ],
                    [
                        'tipo' => 'Salida',
                        'concepto' => 'Provisiones',
                        'notas' => 'Reservas de cartera y riesgos',
                        'costo' => '$1,120,000.00',
                    ],
                ],
            ],
        ];

        $periodoActivo = $request->query('periodo', 'semana');

        if (! collect($periodos)->contains(fn ($periodo) => $periodo['key'] === $periodoActivo)) {
            $periodoActivo = $periodos[0]['key'] ?? 'semana';
        }

        return view('administrativo.entradas-salidas', [
            'periodos' => $periodos,
            'periodoActivo' => $periodoActivo,
        ]);
    }

    /** Tablero concentrado de administracion general. */
    public function administracionGeneral()
    {
        $faker = FakerFactory::create('es_MX');
        $faker->seed(202501);
        $today = Carbon::now();

        $summaryCards = [
            [
                'title' => 'Desembolsos inversion',
                'value' => '$1,250,000',
                'subtext' => 'Monto entregado esta semana',
            ],
            [
                'title' => 'Creditos activos',
                'value' => '864',
                'subtext' => 'Estados aprobado, supervisado o desembolsado',
            ],
            [
                'title' => 'Entradas netas',
                'value' => '$732,500',
                'subtext' => 'Liquidez disponible',
            ],
            [
                'title' => 'Alertas de fallo',
                'value' => '18',
                'subtext' => 'Clientes con semana extra en seguimiento',
            ],
        ];

        $kualifinHierarchy = [];
        $promoterSequence = 101;
        $formatCurrency = static fn (float $value): string => '$' . number_format($value, 2, '.', ',');
        $zoneCatalog = ['Zona Norte', 'Zona Centro', 'Zona Sur', 'Zona Bajio', 'Zona Sureste', 'Zona Metropolitana'];

        for ($executiveIndex = 1; $executiveIndex <= 3; $executiveIndex++) {
            $executiveCode = 'EJ-' . str_pad((string) $executiveIndex, 2, '0', STR_PAD_LEFT);
            $executive = [
                'id' => $executiveCode,
                'codigo' => $executiveCode,
                'nombre' => $faker->name(),
                'usuario' => $faker->unique()->userName(),
                'supervisores' => [],
            ];

            for ($supervisorIndex = 1; $supervisorIndex <= 2; $supervisorIndex++) {
                $supervisorCode = $executiveCode . '-SUP-' . str_pad((string) $supervisorIndex, 2, '0', STR_PAD_LEFT);
                $supervisor = [
                    'id' => $supervisorCode,
                    'codigo' => $supervisorCode,
                    'nombre' => $faker->name(),
                    'usuario' => $faker->unique()->userName(),
                    'promotores' => [],
                ];

                for ($promoterIndex = 1; $promoterIndex <= 3; $promoterIndex++) {
                    $promoterCode = 'PROM-' . $promoterSequence++;
                    $promoter = [
                        'id' => $supervisorCode . '-PROM-' . str_pad((string) $promoterIndex, 2, '0', STR_PAD_LEFT),
                        'codigo' => $promoterCode,
                        'nombre' => $faker->name(),
                        'usuario' => $faker->unique()->userName(),
                        'ejecutivo' => $executive['nombre'],
                        'supervisor' => $supervisor['nombre'],
                        'zona' => $faker->randomElement($zoneCatalog),
                        'porcentaje_rendimiento' => number_format($faker->randomFloat(1, 8.5, 13.5), 1) . '%',
                        'clientes' => [],
                    ];

                    $clientTotal = $faker->numberBetween(9, 16);
                    $loanTotal = 0.0;
                    $weeklyTotal = 0.0;
                    $balanceTotal = 0.0;
                    $dateBuckets = [];

                    for ($clientIndex = 0; $clientIndex < $clientTotal; $clientIndex++) {
                        $weeksAgo = $faker->numberBetween(4, 40);
                        $daysAdjustment = $faker->numberBetween(0, 3);
                        $creditStart = $today->copy()->subWeeks($weeksAgo)->subDays($daysAdjustment);

                        $loanAmount = (float) $faker->numberBetween(15000, 160000);
                        $weeklyTerm = $faker->numberBetween(16, 48);
                        $weeklyPayment = round($loanAmount / max($weeklyTerm, 1), 2);
                        $paidWeeks = $faker->numberBetween(0, (int) floor($weeklyTerm * 0.6));
                        $paidAmount = round($weeklyPayment * $paidWeeks, 2);
                        $balance = round(max($loanAmount - $paidAmount, 0), 2);

                        $paymentCursor = $creditStart->copy();
                        if ($paymentCursor->dayOfWeek !== Carbon::MONDAY) {
                            $paymentCursor = $paymentCursor->next(Carbon::MONDAY);
                        }

                        $paymentSchedule = [];
                        $matrixPayments = [];
                        $currentPayment = $paymentCursor->copy();
                        for ($termWeek = 0; $termWeek < $weeklyTerm; $termWeek++) {
                            $dateKey = $currentPayment->format('Y-m-d');
                            $displayDate = $currentPayment->format('d/m/Y');
                            $isFuture = $currentPayment->gt($today);

                            $paymentSchedule[] = $displayDate;
                            $matrixPayments[$dateKey] = [
                                'amount' => $weeklyPayment,
                                'display' => $formatCurrency($weeklyPayment),
                                'is_future' => $isFuture,
                            ];

                            if (!isset($dateBuckets[$dateKey])) {
                                $dateBuckets[$dateKey] = 0.0;
                            }
                            $dateBuckets[$dateKey] += $weeklyPayment;

                            $currentPayment->addWeek();
                        }

                        $loanTotal += $loanAmount;
                        $weeklyTotal += $weeklyPayment;
                        $balanceTotal += $balance;

                        $promoter['clientes'][] = [
                            'nombre' => $faker->name(),
                            'fecha_credito' => $creditStart->format('d/m/Y'),
                            'prestamo' => $formatCurrency($loanAmount),
                            'prestamo_valor' => $loanAmount,
                            'abono_semanal' => $formatCurrency($weeklyPayment),
                            'abono_semanal_valor' => $weeklyPayment,
                            'saldo_pendiente' => $formatCurrency($balance),
                            'saldo_pendiente_valor' => $balance,
                            'estatus' => $faker->randomElement(['Activo', 'En seguimiento', 'Atraso leve', 'Renovacion']),
                            'fechas_pago' => $paymentSchedule,
                            'total_fechas_pago' => count($paymentSchedule),
                            'pagos_por_fecha' => $matrixPayments,
                            'pagos_por_fecha_filtrados' => [],
                        ];
                    }

                    ksort($dateBuckets);
                    $calendarColumns = [];
                    foreach ($dateBuckets as $dateKey => $totalValue) {
                        $dateInstance = Carbon::createFromFormat('Y-m-d', $dateKey);
                        $calendarColumns[] = [
                            'key' => $dateKey,
                            'label' => $dateInstance->format('d/M/y'),
                            'label_full' => $dateInstance->format('d/m/Y'),
                            'total_value' => round($totalValue, 2),
                            'total_formatted' => $formatCurrency($totalValue),
                            'is_future' => $dateInstance->gt($today),
                        ];
                    }
                    $calendarColumns = array_slice($calendarColumns, 0, 24);
                    $allowedKeys = array_map(static fn ($column) => $column['key'], $calendarColumns);

                    foreach ($promoter['clientes'] as &$clientData) {
                        $filtered = [];
                        foreach ($allowedKeys as $allowedKey) {
                            $filtered[$allowedKey] = $clientData['pagos_por_fecha'][$allowedKey] ?? null;
                        }
                        $clientData['pagos_por_fecha_filtrados'] = $filtered;
                    }
                    unset($clientData);

                    $projectedInvestment = $loanTotal * $faker->randomFloat(2, 0.18, 0.32);
                    $maxSales = max($projectedInvestment * $faker->randomFloat(2, 1.05, 1.28), $projectedInvestment + $faker->numberBetween(1500, 8500));
                    $previousFlow = $loanTotal * $faker->randomFloat(2, 0.45, 1.05);
                    $totalRecover = $loanTotal + ($loanTotal * $faker->randomFloat(2, 0.18, 0.36));
                    $realLoan = $loanTotal * $faker->randomFloat(2, 0.72, 0.96);
                    $t4Recovered = $loanTotal * $faker->randomFloat(2, 0.42, 0.68);
                    $fallPercentage = $faker->randomFloat(2, 0, 4.5);
                    $commissionPromoter = $loanTotal * $faker->randomFloat(2, 0.02, 0.04);
                    $commissionSupervisor = $loanTotal * $faker->randomFloat(2, 0.01, 0.025);
                    $savingsFund = $loanTotal * $faker->randomFloat(2, 0.015, 0.035);
                    $cashFlow = max($loanTotal * $faker->randomFloat(2, 0.18, 0.32), 0.0) + $faker->numberBetween(1200, 6800);

                    $promoter['totals'] = [
                        'prestamo_total' => $formatCurrency($loanTotal),
                        'abono_total' => $formatCurrency($weeklyTotal),
                        'saldo_total' => $formatCurrency($balanceTotal),
                        'clientes' => $clientTotal,
                    ];

                    $promoter['financial_summary'] = [
                        'proyeccion' => $formatCurrency($projectedInvestment),
                        'ventas_maximas' => $formatCurrency($maxSales),
                        'flujo_anterior' => $formatCurrency($previousFlow),
                        'prestamo' => $formatCurrency($loanTotal),
                        'total_recuperar' => $formatCurrency($totalRecover),
                        'prestamo_real' => $formatCurrency($realLoan),
                        't4_recuperado' => $formatCurrency($t4Recovered),
                        'fallo' => number_format($fallPercentage, 2) . '%',
                        'comision_promotor' => $formatCurrency($commissionPromoter),
                        'comision_supervisor' => $formatCurrency($commissionSupervisor),
                        'fondo_ahorro' => $formatCurrency($savingsFund),
                        'flujo_efectivo' => $formatCurrency($cashFlow),
                    ];

                    $promoter['calendar_columns'] = $calendarColumns;
                    $promoter['calendar_keys'] = $allowedKeys;
                    $promoter['calendar_total_global'] = $formatCurrency(array_sum(array_map(
                        static fn ($column) => $column['total_value'],
                        $calendarColumns
                    )));

                    $supervisor['promotores'][] = $promoter;
                }

                $executive['supervisores'][] = $supervisor;
            }

            $kualifinHierarchy[] = $executive;
        }

        $investmentDisbursements = [
            [
                'folio' => 'INV-2025-014',
                'cliente' => 'Grupo Crecer (CURP GRCG851214MDFNRG07)',
                'monto' => '$420,000.00',
                'fecha' => '21 ene 2025',
                'estado' => 'Programado',
                'destino' => 'Capital de trabajo',
            ],
            [
                'folio' => 'INV-2025-013',
                'cliente' => 'Ana Valdez (CURP VAAA900321MDFRLN03)',
                'monto' => '$310,000.00',
                'fecha' => '20 ene 2025',
                'estado' => 'Desembolsado',
                'destino' => 'Apertura de sucursal',
            ],
            [
                'folio' => 'INV-2025-012',
                'cliente' => 'Constructora Bajio (CURP COBE861122HDFRGN06)',
                'monto' => '$285,000.00',
                'fecha' => '18 ene 2025',
                'estado' => 'En revision',
                'destino' => 'Compra de maquinaria',
            ],
        ];

        $creditOverview = [
            [
                'estado' => 'Prospectado',
                'total' => 142,
                'monto' => '$5,640,000.00',
                'comentario' => 'Solicitudes capturadas en Kualifin.',
            ],
            [
                'estado' => 'Solicitado',
                'total' => 98,
                'monto' => '$4,210,000.00',
                'comentario' => 'Pendientes de autorizacion administrativa.',
            ],
            [
                'estado' => 'Aprobado',
                'total' => 215,
                'monto' => '$11,580,000.00',
                'comentario' => 'Listos para supervision y firmas.',
            ],
            [
                'estado' => 'Desembolsado',
                'total' => 327,
                'monto' => '$18,430,000.00',
                'comentario' => 'Calendario activo y cobranzas en curso.',
            ],
        ];

        $cashFlow = [
            [
                'tipo' => 'Entrada',
                'origen' => 'Cobranza promotores',
                'monto' => '$385,400.00',
                'detalle' => 'Pagos registrados en pagos_reales.',
            ],
            [
                'tipo' => 'Entrada',
                'origen' => 'Recuperacion inversiones',
                'monto' => '$198,700.00',
                'detalle' => 'Capital devuelto de contratos de inversion.',
            ],
            [
                'tipo' => 'Salida',
                'origen' => 'Nuevos desembolsos',
                'monto' => '$410,000.00',
                'detalle' => 'Creditos entregados en la semana.',
            ],
            [
                'tipo' => 'Salida',
                'origen' => 'Gastos operativos',
                'monto' => '$98,600.00',
                'detalle' => 'Pagos de soporte y servicios generales.',
            ],
        ];

        $expenses = [
            [
                'concepto' => 'Capacitacion promotores',
                'monto' => '$24,500.00',
                'responsable' => 'operaciones.jvilla',
                'fecha' => '19 ene 2025',
                'comentario' => 'Curso onboarding zona centro.',
            ],
            [
                'concepto' => 'Renta sucursal Tuxtla',
                'monto' => '$38,900.00',
                'responsable' => 'expansion.rarias',
                'fecha' => '18 ene 2025',
                'comentario' => 'Pago mensual sucursal piloto.',
            ],
            [
                'concepto' => 'Plataforma Kualifin',
                'monto' => '$21,300.00',
                'responsable' => 'tecnologia.mvera',
                'fecha' => '17 ene 2025',
                'comentario' => 'Licenciamiento mensual y soporte.',
            ],
            [
                'concepto' => 'Viaticos supervision',
                'monto' => '$14,200.00',
                'responsable' => 'supervision.elopez',
                'fecha' => '16 ene 2025',
                'comentario' => 'Visitas a clientes con atrasos.',
            ],
        ];

        $weeklyProjection = [
            'semana_actual' => [
                'prestamos' => '$1,050,000.00',
                'cobrado' => '$745,300.00',
                'saldo_activo' => '$19,820,000.00',
            ],
            'semana_siguiente' => [
                'meta_prestamos' => '$1,200,000.00',
                'estimado_cobranza' => '$812,400.00',
                'saldo_programado' => '$20,150,000.00',
            ],
            'notas' => 'Priorizar cierre de prospectos en zona norte y regularizar clientes con semana extra.',
        ];

        $failureHistory = [
            [
                'cliente' => 'Luis Ortega (CURP OERL851102HDFNRD02)',
                'folio' => 'CR-2024-0820',
                'promotor' => 'PROM-188 / promotor.lgallardo',
                'semanas_extra' => 2,
                'monto_pendiente' => '$18,400.00',
                'ultimo_pago' => '12 ene 2025',
            ],
            [
                'cliente' => 'Paola Oviedo (CURP OVPF900406MDFVLR04)',
                'folio' => 'CR-2024-0694',
                'promotor' => 'PROM-204 / promotor.rcortes',
                'semanas_extra' => 1,
                'monto_pendiente' => '$12,900.00',
                'ultimo_pago' => '08 ene 2025',
            ],
            [
                'cliente' => 'Jorge Herrera (CURP HEGJ880901HDFRRN02)',
                'folio' => 'CR-2024-0719',
                'promotor' => 'PROM-132 / promotor.jvelazquez',
                'semanas_extra' => 3,
                'monto_pendiente' => '$21,600.00',
                'ultimo_pago' => '29 dic 2024',
            ],
        ];

        $reports = [
            [
                'nombre' => 'Reporte mensual de cartera',
                'periodo' => 'Diciembre 2024',
                'responsable' => 'finanzas.jrobles',
                'estatus' => 'Entregado',
                'descarga' => '#',
            ],
            [
                'nombre' => 'Concentrado de desembolsos',
                'periodo' => 'Enero 2025',
                'responsable' => 'inversiones.cluna',
                'estatus' => 'En validacion',
                'descarga' => '#',
            ],
            [
                'nombre' => 'Reporte anual indicadores',
                'periodo' => '2024',
                'responsable' => 'direccion.finanzas',
                'estatus' => 'Programado',
                'descarga' => '#',
            ],
        ];

        return view('administrativo.administracion-general', [
            'kualifinHierarchy' => $kualifinHierarchy,
            'summaryCards' => $summaryCards,
            'investmentDisbursements' => $investmentDisbursements,
            'creditOverview' => $creditOverview,
            'cashFlow' => $cashFlow,
            'expenses' => $expenses,
            'weeklyProjection' => $weeklyProjection,
            'failureHistory' => $failureHistory,
            'reports' => $reports,
        ]);
    }
}

