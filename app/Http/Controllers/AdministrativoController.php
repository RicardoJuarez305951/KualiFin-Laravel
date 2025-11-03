<?php

namespace App\Http\Controllers;

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
        $summary = [
            ['label' => 'Solicitudes pendientes', 'value' => 4, 'badge' => '3 urgentes'],
            ['label' => 'Tiempo promedio', 'value' => '3h 15m', 'badge' => 'Ultimas 24h'],
            ['label' => 'Autorizaciones hoy', 'value' => 9, 'badge' => 'Sin rechazos'],
            ['label' => 'Rechazos esta semana', 'value' => 2, 'badge' => 'Por seguimiento'],
        ];

        $pendingRequests = [
            [
                'folio' => 'AUT-2403',
                'cliente' => 'Lucia Salgado',
                'solicitud' => 'Ajuste de linea +15 %',
                'monto' => '$38,000 MXN',
                'ingreso' => '21 ene 2025 - 10:42',
                'responsable' => 'mjimenez',
                'riesgo' => 'Medio',
            ],
            [
                'folio' => 'AUT-2398',
                'cliente' => 'Mario Castaneda',
                'solicitud' => 'Desembolso extraordinario',
                'monto' => '$52,500 MXN',
                'ingreso' => '21 ene 2025 - 09:15',
                'responsable' => 'sgutierrez',
                'riesgo' => 'Alto',
            ],
            [
                'folio' => 'AUT-2392',
                'cliente' => 'Ana Valdez',
                'solicitud' => 'Liberar documento retenido',
                'monto' => '$0 MXN',
                'ingreso' => '20 ene 2025 - 18:05',
                'responsable' => 'analista.seguridad',
                'riesgo' => 'Bajo',
            ],
            [
                'folio' => 'AUT-2391',
                'cliente' => 'Grupo Sol',
                'solicitud' => 'Apertura de linea grupal',
                'monto' => '$120,000 MXN',
                'ingreso' => '20 ene 2025 - 17:33',
                'responsable' => 'coordinacion.norte',
                'riesgo' => 'Medio',
            ],
        ];

        $recentApprovals = [
            [
                'folio' => 'AUT-2390',
                'accion' => 'Incremento de linea',
                'autorizo' => 'mrodriguez',
                'fecha' => '20 ene 2025 - 14:25',
                'comentarios' => 'Score actualizado con buro positivo.',
            ],
            [
                'folio' => 'AUT-2387',
                'accion' => 'Liberacion de retencion',
                'autorizo' => 'analista.seguridad',
                'fecha' => '20 ene 2025 - 12:04',
                'comentarios' => 'Se incluyeron pruebas de domicilio adicionales.',
            ],
            [
                'folio' => 'AUT-2381',
                'accion' => 'Desembolso urgente',
                'autorizo' => 'supervisor.aramirez',
                'fecha' => '19 ene 2025 - 19:40',
                'comentarios' => 'Cliente con excelente historial de pago.',
            ],
        ];

        return view('administrativo.autorizacion', compact('summary', 'pendingRequests', 'recentApprovals'));
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
        $pipeline = [
            [
                'ciudad' => 'Queretaro',
                'estatus' => 'Analisis de mercado',
                'apertura_estimado' => 'Abr 2025',
                'responsable' => 'expansion.agarcia',
                'comentarios' => 'Poblacion objetivo con tasa de crecimiento 6 %.',
            ],
            [
                'ciudad' => 'Tuxtla Gutierrez',
                'estatus' => 'Viabilidad financiera',
                'apertura_estimado' => 'Jun 2025',
                'responsable' => 'finanzas.jrobles',
                'comentarios' => 'Negociacion con aliados estrategicos en curso.',
            ],
            [
                'ciudad' => 'Ciudad Obregon',
                'estatus' => 'Pre-aprobacion',
                'apertura_estimado' => 'May 2025',
                'responsable' => 'expansion.rarias',
                'comentarios' => 'Requiere validacion de cartera potencial.',
            ],
        ];

        $regionalSummary = [
            ['region' => 'Norte', 'sucursales_activas' => 8, 'proyeccion' => '+2', 'riesgo' => 'Medio'],
            ['region' => 'Centro', 'sucursales_activas' => 12, 'proyeccion' => '+1', 'riesgo' => 'Bajo'],
            ['region' => 'Sur', 'sucursales_activas' => 5, 'proyeccion' => '+2', 'riesgo' => 'Alto'],
        ];

        $nextSteps = [
            ['fecha' => '24 ene', 'actividad' => 'Presentar plan Tuxtla', 'responsable' => 'finanzas.jrobles'],
            ['fecha' => '29 ene', 'actividad' => 'Sesión comite expansion', 'responsable' => 'direccion.operaciones'],
            ['fecha' => '05 feb', 'actividad' => 'Visita de sitio Queretaro', 'responsable' => 'expansion.agarcia'],
        ];

        return view('administrativo.probables-aperturas', compact('pipeline', 'regionalSummary', 'nextSteps'));
    }

    /** Tablero concentrado de iniciativas generales. */
    public function administracionGeneral()
    {
        $initiatives = [
            [
                'nombre' => 'Modernizacion de oficinas',
                'avance' => 68,
                'lider' => 'infraestructura.mmendez',
                'riesgo' => 'Medio',
                'proximo_hito' => 'Entrega mobiliario - 31 ene',
            ],
            [
                'nombre' => 'Centralizacion de cobranza',
                'avance' => 45,
                'lider' => 'operaciones.jvilla',
                'riesgo' => 'Alto',
                'proximo_hito' => 'Piloto en region Bajio - 07 feb',
            ],
            [
                'nombre' => 'Programa de referidos',
                'avance' => 82,
                'lider' => 'marketing.lguerra',
                'riesgo' => 'Bajo',
                'proximo_hito' => 'Campaña digital - 24 ene',
            ],
        ];

        $compliance = [
            ['titulo' => 'Politica de gestion documental', 'estatus' => 'En actualizacion', 'responsable' => 'control.interno', 'limite' => '15 feb 2025'],
            ['titulo' => 'Manual de ciberseguridad 2025', 'estatus' => 'Completo', 'responsable' => 'seguridad.tic', 'limite' => 'Entregado'],
            ['titulo' => 'Informe de auditoria interna', 'estatus' => 'Revision', 'responsable' => 'auditoria@kuali', 'limite' => '05 feb 2025'],
        ];

        $alerts = [
            ['tipo' => 'warning', 'mensaje' => 'Contrato marco con proveedor logistica vence en 12 dias.'],
            ['tipo' => 'info', 'mensaje' => 'Se habilito tablero de indicadores para seguimiento diario.'],
            ['tipo' => 'danger', 'mensaje' => 'Se requiere renovacion de poliza de responsabilidad civil.'],
        ];

        return view('administrativo.administracion-general', compact('initiatives', 'compliance', 'alerts'));
    }
}
