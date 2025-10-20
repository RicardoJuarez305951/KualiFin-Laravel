<?php

namespace App\Http\Controllers;

class AdministrativoController extends Controller
{
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

    public function clientesCreate()
    {
        $promotores = [
            ['id' => 1, 'nombre' => 'Ana Sofia'],
            ['id' => 2, 'nombre' => 'Miguel Torres'],
            ['id' => 3, 'nombre' => 'Karina Diaz'],
        ];

        return view('clientes.create', compact('promotores'));
    }

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

    public function parametros()
    {
        return view('administrativo.parametros');
    }

    public function asignaciones()
    {
        return view('administrativo.asignaciones');
    }

    public function carteraGlobal()
    {
        return view('administrativo.cartera_global');
    }

    public function ventasDesembolsos()
    {
        return view('administrativo.ventas_desembolsos');
    }

    public function inversiones()
    {
        return view('administrativo.inversiones');
    }

    public function auditoriaSeguridad()
    {
        return view('administrativo.auditoria_seguridad');
    }
}
