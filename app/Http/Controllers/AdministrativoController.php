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
}
