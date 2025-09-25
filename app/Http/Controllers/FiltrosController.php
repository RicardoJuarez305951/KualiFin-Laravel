<?php

namespace App\Http\Controllers;

use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\DatoContacto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FiltrosController extends Controller
{
    public const FILTER_CURP_UNICA = 'curp_unica';
    public const FILTER_DOBLE_FIRMA_AVAL = 'doble_firma_aval';
    public const FILTER_CREDITO_EN_FALLA = 'credito_en_falla';
    public const FILTER_CREDITO_ACTIVO = 'credito_activo';
    public const FILTER_OTRA_PLAZA = 'otra_plaza';
    public const FILTER_BLOQUEO_FALLA_PROMOTORA = 'bloqueo_falla_promotora_5';
    public const FILTER_DOBLE_DOMICILIO = 'doble_domicilio';
    public const FILTER_BLOQUEO_TIEMPO_REACREDITOS = 'bloqueo_tiempo_recreditos';

    private const CREDIT_ACTIVE_STATES = [
        'prospectado',
        'prospectado_recredito',
        'solicitado',
        'aprobado',
        'supervisado',
        'desembolsado',
    ];

    private const CREDIT_FAILURE_STATES = [
        'vencido',
    ];

    private const CREDIT_FAILURE_STATES_FOR_PROMOTOR = [
        'vencido',
        'cancelado',
    ];

    private const CARTERA_FAILURE_STATES = [
        'moroso',
        'falla',
    ];

    private const TIPO_SOLICITUD_REACREDITACION = 'recredito';

    /**
     * Evalua todas las reglas de negocio registradas para la autorización de créditos.
     */
    public function evaluar(Cliente $cliente, array $form, array $contexto = []): array
    {
        $resultados = [];

        foreach ($this->filtros() as $clave => $filtro) {
            $resultado = $filtro($cliente, $form, $contexto);
            $resultados[$clave] = $resultado;

            if (!$resultado['passed']) {
                return [
                    'passed' => false,
                    'failed_filter' => $clave,
                    'message' => $resultado['message'],
                    'results' => $resultados,
                ];
            }
        }

        return [
            'passed' => true,
            'failed_filter' => null,
            'message' => null,
            'results' => $resultados,
        ];
    }

    /**
     * @return array<string, callable>
     */
    private function filtros(): array
    {
        return [
            self::FILTER_CURP_UNICA => fn (Cliente $cliente, array $form, array $contexto) => $this->aplicarCurpUnica($cliente, $form),
            self::FILTER_DOBLE_FIRMA_AVAL => fn (Cliente $cliente, array $form, array $contexto) => $this->aplicarDobleFirmaAval($cliente, $form, $contexto),
            self::FILTER_CREDITO_EN_FALLA => fn (Cliente $cliente, array $form, array $contexto) => $this->aplicarCreditoEnFalla($cliente),
            self::FILTER_CREDITO_ACTIVO => fn (Cliente $cliente, array $form, array $contexto) => $this->aplicarCreditoActivo($cliente, $contexto),
            self::FILTER_OTRA_PLAZA => fn (Cliente $cliente, array $form, array $contexto) => $this->aplicarOtraPlaza($cliente, $contexto),
            self::FILTER_BLOQUEO_FALLA_PROMOTORA => fn (Cliente $cliente, array $form, array $contexto) => $this->aplicarBloqueoFallaPromotora($cliente, $contexto),
            self::FILTER_DOBLE_DOMICILIO => fn (Cliente $cliente, array $form, array $contexto) => $this->aplicarDobleDomicilio($cliente, $form, $contexto),
            self::FILTER_BLOQUEO_TIEMPO_REACREDITOS => fn (Cliente $cliente, array $form, array $contexto) => $this->aplicarBloqueoTiempoRecreditos($cliente, $contexto),
        ];
    }

    private function aplicarCurpUnica(Cliente $cliente, array $form): array
    {
        $curp = isset($form['cliente']['curp']) ? Str::upper(trim((string) $form['cliente']['curp'])) : '';

        if ($curp === '') {
            return $this->resultado(self::FILTER_CURP_UNICA, true, null, ['curp' => $curp]);
        }

        $duplicados = Cliente::where('CURP', $curp)
            ->where('id', '<>', $cliente->id)
            ->count();

        if ($duplicados > 0) {
            return $this->resultado(
                self::FILTER_CURP_UNICA,
                false,
                'La CURP proporcionada ya existe para otro cliente registrado.',
                ['curp' => $curp, 'coincidencias' => $duplicados]
            );
        }

        return $this->resultado(self::FILTER_CURP_UNICA, true, null, ['curp' => $curp]);
    }

    private function aplicarDobleFirmaAval(Cliente $cliente, array $form, array $contexto): array
    {
        $avalCurp = isset($form['aval']['curp']) ? Str::upper(trim((string) $form['aval']['curp'])) : '';

        if ($avalCurp === '') {
            return $this->resultado(self::FILTER_DOBLE_FIRMA_AVAL, true, null, ['aval_curp' => $avalCurp]);
        }

        $creditoActualId = $contexto['credito_actual_id'] ?? null;

        $creditosActivos = Aval::where('CURP', $avalCurp)
            ->when($creditoActualId, fn ($query) => $query->where('credito_id', '<>', $creditoActualId))
            ->whereHas('credito', function ($query) {
                $query->whereIn('estado', self::CREDIT_ACTIVE_STATES);
            })
            ->count();

        if ($creditosActivos >= 2) {
            return $this->resultado(
                self::FILTER_DOBLE_FIRMA_AVAL,
                false,
                'El aval ya participa en dos créditos activos y no puede respaldar un tercero.',
                ['aval_curp' => $avalCurp, 'creditos_activos' => $creditosActivos]
            );
        }

        return $this->resultado(self::FILTER_DOBLE_FIRMA_AVAL, true, null, ['aval_curp' => $avalCurp, 'creditos_activos' => $creditosActivos]);
    }

    private function aplicarCreditoEnFalla(Cliente $cliente): array
    {
        $creditos = $cliente->relationLoaded('creditos') ? $cliente->creditos : $cliente->creditos()->get();

        $enFalla = $creditos->filter(function (Credito $credito) {
            return in_array($credito->estado, self::CREDIT_FAILURE_STATES, true);
        })->count();

        $carteraEnFalla = in_array($cliente->cartera_estado, self::CARTERA_FAILURE_STATES, true);

        if ($enFalla > 0 || $carteraEnFalla) {
            return $this->resultado(
                self::FILTER_CREDITO_EN_FALLA,
                false,
                'El cliente tiene historial de créditos en falla y no puede solicitar uno nuevo.',
                [
                    'creditos_en_falla' => $enFalla,
                    'cartera_estado' => $cliente->cartera_estado,
                ]
            );
        }

        return $this->resultado(self::FILTER_CREDITO_EN_FALLA, true, null, [
            'creditos_en_falla' => $enFalla,
            'cartera_estado' => $cliente->cartera_estado,
        ]);
    }

    private function aplicarCreditoActivo(Cliente $cliente, array $contexto): array
    {
        $tipoSolicitud = $this->resolverTipoSolicitud($contexto);
        $creditos = $cliente->relationLoaded('creditos') ? $cliente->creditos : $cliente->creditos()->get();

        $activos = $creditos->filter(function (Credito $credito) {
            return in_array($credito->estado, self::CREDIT_ACTIVE_STATES, true);
        })->count();

        $tieneActivo = $cliente->tiene_credito_activo || $activos > 0;

        if ($tieneActivo && $tipoSolicitud !== self::TIPO_SOLICITUD_REACREDITACION) {
            return $this->resultado(
                self::FILTER_CREDITO_ACTIVO,
                false,
                'El cliente ya tiene un crédito activo. Solo es posible tramitar un recrédito.',
                [
                    'creditos_activos' => $activos,
                    'tiene_credito_activo' => (bool) $cliente->tiene_credito_activo,
                    'tipo_solicitud' => $tipoSolicitud,
                ]
            );
        }

        return $this->resultado(self::FILTER_CREDITO_ACTIVO, true, null, [
            'creditos_activos' => $activos,
            'tiene_credito_activo' => (bool) $cliente->tiene_credito_activo,
            'tipo_solicitud' => $tipoSolicitud,
        ]);
    }

    private function aplicarOtraPlaza(Cliente $cliente, array $contexto): array
    {
        $promotorActualId = $contexto['promotor_id'] ?? null;
        $supervisorActualId = $contexto['supervisor_id'] ?? null;

        $promotorClienteId = $cliente->promotor_id;
        $supervisorClienteId = $cliente->promotor->supervisor_id ?? null;

        if ($promotorClienteId && $promotorActualId && $promotorClienteId !== $promotorActualId) {
            return $this->resultado(
                self::FILTER_OTRA_PLAZA,
                false,
                'El cliente pertenece a otro promotor. Solicita el cambio de plaza antes de continuar.',
                [
                    'cliente_promotor_id' => $promotorClienteId,
                    'solicitante_promotor_id' => $promotorActualId,
                ]
            );
        }

        if ($supervisorClienteId && $supervisorActualId && $supervisorClienteId !== $supervisorActualId) {
            return $this->resultado(
                self::FILTER_OTRA_PLAZA,
                false,
                'El cliente pertenece a otro supervisor. Debe reasignarse administrativamente.',
                [
                    'cliente_supervisor_id' => $supervisorClienteId,
                    'solicitante_supervisor_id' => $supervisorActualId,
                ]
            );
        }

        return $this->resultado(self::FILTER_OTRA_PLAZA, true, null, [
            'cliente_promotor_id' => $promotorClienteId,
            'solicitante_promotor_id' => $promotorActualId,
            'cliente_supervisor_id' => $supervisorClienteId,
            'solicitante_supervisor_id' => $supervisorActualId,
        ]);
    }

    private function aplicarBloqueoFallaPromotora(Cliente $cliente, array $contexto): array
    {
        $promotor = $cliente->promotor;

        if (!$promotor) {
            return $this->resultado(self::FILTER_BLOQUEO_FALLA_PROMOTORA, true, null, ['promotor_id' => null]);
        }

        $creditosQuery = Credito::query()
            ->whereHas('cliente', fn ($query) => $query->where('promotor_id', $promotor->id));

        $total = (clone $creditosQuery)->count();

        if ($total === 0) {
            return $this->resultado(self::FILTER_BLOQUEO_FALLA_PROMOTORA, true, null, ['promotor_id' => $promotor->id, 'total_creditos' => 0, 'porcentaje_falla' => 0.0]);
        }

        $fallas = (clone $creditosQuery)
            ->whereIn('estado', self::CREDIT_FAILURE_STATES_FOR_PROMOTOR)
            ->count();

        $porcentajeFalla = $total > 0 ? $fallas / $total : 0.0;
        $tipoSolicitud = $this->resolverTipoSolicitud($contexto);

        if ($porcentajeFalla > 0.05 && $tipoSolicitud !== self::TIPO_SOLICITUD_REACREDITACION) {
            return $this->resultado(
                self::FILTER_BLOQUEO_FALLA_PROMOTORA,
                false,
                'La promotora supera el 5% de créditos en falla. Solo se permiten recréditos.',
                [
                    'promotor_id' => $promotor->id,
                    'total_creditos' => $total,
                    'creditos_en_falla' => $fallas,
                    'porcentaje_falla' => round($porcentajeFalla * 100, 2),
                    'tipo_solicitud' => $tipoSolicitud,
                ]
            );
        }

        return $this->resultado(self::FILTER_BLOQUEO_FALLA_PROMOTORA, true, null, [
            'promotor_id' => $promotor->id,
            'total_creditos' => $total,
            'creditos_en_falla' => $fallas,
            'porcentaje_falla' => round($porcentajeFalla * 100, 2),
            'tipo_solicitud' => $tipoSolicitud,
        ]);
    }

    private function aplicarDobleDomicilio(Cliente $cliente, array $form, array $contexto): array
    {
        $contacto = $form['contacto'] ?? [];
        $direccion = $this->normalizarDireccion($contacto);

        if ($direccion['calle'] === '' || $direccion['numero_ext'] === '' || $direccion['colonia'] === '') {
            return $this->resultado(self::FILTER_DOBLE_DOMICILIO, true, null, ['direccion' => $direccion]);
        }

        $fechaInicio = isset($form['credito']['fecha_inicio'])
            ? Carbon::parse($form['credito']['fecha_inicio'])
            : Carbon::now();

        $contactosSimilares = DatoContacto::query()
            ->whereRaw('LOWER(TRIM(calle)) = ?', [$direccion['calle']])
            ->whereRaw('LOWER(TRIM(colonia)) = ?', [$direccion['colonia']])
            ->whereRaw('LOWER(TRIM(municipio)) = ?', [$direccion['municipio']])
            ->where('cp', $direccion['cp'])
            ->whereRaw('LOWER(TRIM(numero_ext)) = ?', [$direccion['numero_ext']])
            ->whereHas('credito', function ($query) use ($cliente) {
                $query->whereIn('estado', self::CREDIT_ACTIVE_STATES)
                    ->whereHas('cliente', fn ($clienteQuery) => $clienteQuery->where('id', '<>', $cliente->id));
            })
            ->with('credito')
            ->get();

        $creditosRelacionados = $contactosSimilares->pluck('credito')->filter()->values();

        $totalRelacionados = $creditosRelacionados->count();
        $autorizacionEspecial = (bool) ($contexto['autorizacion_especial_domicilio'] ?? false);

        if ($totalRelacionados >= 2) {
            if (!$autorizacionEspecial) {
                return $this->resultado(
                    self::FILTER_DOBLE_DOMICILIO,
                    false,
                    'Ya existen dos créditos activos en este domicilio. Requiere autorización especial.',
                    [
                        'creditos_relacionados' => $totalRelacionados,
                        'autorizacion_especial' => $autorizacionEspecial,
                    ]
                );
            }

            $fechasInicio = $creditosRelacionados
                ->map(fn (Credito $credito) => $credito->fecha_inicio ? Carbon::parse($credito->fecha_inicio) : null)
                ->filter()
                ->values();

            $diferenciasValidas = $fechasInicio->every(fn (Carbon $fecha) => $fecha->diffInWeeks($fechaInicio) >= 7);

            if (!$diferenciasValidas) {
                return $this->resultado(
                    self::FILTER_DOBLE_DOMICILIO,
                    false,
                    'Aunque hay autorización especial, no se cumple la diferencia mínima de 7 semanas entre créditos.',
                    [
                        'creditos_relacionados' => $totalRelacionados,
                        'autorizacion_especial' => $autorizacionEspecial,
                        'fechas_existentes' => $fechasInicio->map(fn (Carbon $fecha) => $fecha->toDateString())->all(),
                        'fecha_solicitud' => $fechaInicio->toDateString(),
                    ]
                );
            }
        }

        return $this->resultado(self::FILTER_DOBLE_DOMICILIO, true, null, [
            'creditos_relacionados' => $totalRelacionados,
            'autorizacion_especial' => $autorizacionEspecial,
        ]);
    }

    private function aplicarBloqueoTiempoRecreditos(Cliente $cliente, array $contexto): array
    {
        $tipoSolicitud = $this->resolverTipoSolicitud($contexto);

        if ($tipoSolicitud !== self::TIPO_SOLICITUD_REACREDITACION) {
            return $this->resultado(self::FILTER_BLOQUEO_TIEMPO_REACREDITOS, true, null, ['tipo_solicitud' => $tipoSolicitud]);
        }

        /** @var Credito|null $ultimoCredito */
        $ultimoCredito = $contexto['ultimo_credito'] ?? null;

        if (!$ultimoCredito) {
            $ultimoCredito = $cliente->creditos()->latest('fecha_inicio')->first();
        }

        if (!$ultimoCredito) {
            return $this->resultado(self::FILTER_BLOQUEO_TIEMPO_REACREDITOS, true, null, ['mensaje' => 'Sin créditos previos']);
        }

        $periodicidadSemanas = $this->resolverSemanasPeriodicidad($ultimoCredito->periodicidad);
        $fechaInicio = Carbon::parse($ultimoCredito->fecha_inicio);
        $fechaEvaluacion = isset($contexto['fecha_solicitud'])
            ? ($contexto['fecha_solicitud'] instanceof Carbon
                ? $contexto['fecha_solicitud']
                : Carbon::parse((string) $contexto['fecha_solicitud']))
            : Carbon::now();
        $semanasTranscurridas = $fechaInicio->diffInWeeks($fechaEvaluacion);

        $requisitos = [
            13 => 9,
            14 => 11,
        ];

        $minimoRequerido = $requisitos[$periodicidadSemanas] ?? null;

        $sinAtrasos = $cliente->cartera_estado !== 'moroso'
            && !in_array($ultimoCredito->estado, self::CREDIT_FAILURE_STATES, true);

        if ($minimoRequerido !== null && $semanasTranscurridas < $minimoRequerido) {
            return $this->resultado(
                self::FILTER_BLOQUEO_TIEMPO_REACREDITOS,
                false,
                sprintf('El recrédito solo puede solicitarse a partir de la semana %d.', $minimoRequerido),
                [
                    'periodicidad' => $ultimoCredito->periodicidad,
                    'semanas_transcurridas' => $semanasTranscurridas,
                    'minimo_requerido' => $minimoRequerido,
                    'sin_atrasos' => $sinAtrasos,
                ]
            );
        }

        if (!$sinAtrasos) {
            return $this->resultado(
                self::FILTER_BLOQUEO_TIEMPO_REACREDITOS,
                false,
                'El cliente presenta atrasos, no puede solicitar un recrédito.',
                [
                    'periodicidad' => $ultimoCredito->periodicidad,
                    'semanas_transcurridas' => $semanasTranscurridas,
                    'sin_atrasos' => $sinAtrasos,
                ]
            );
        }

        return $this->resultado(self::FILTER_BLOQUEO_TIEMPO_REACREDITOS, true, null, [
            'periodicidad' => $ultimoCredito->periodicidad,
            'semanas_transcurridas' => $semanasTranscurridas,
            'sin_atrasos' => $sinAtrasos,
        ]);
    }

    private function resolverTipoSolicitud(array $contexto): string
    {
        $tipo = $contexto['tipo_solicitud'] ?? null;

        if (is_string($tipo)) {
            return strtolower($tipo);
        }

        return 'nuevo';
    }

    private function normalizarDireccion(array $contacto): array
    {
        $lower = static fn ($value) => Str::lower(trim((string) $value));

        return [
            'calle' => $lower($contacto['calle'] ?? ''),
            'numero_ext' => $lower($contacto['numero_ext'] ?? ''),
            'colonia' => $lower($contacto['colonia'] ?? ''),
            'municipio' => $lower($contacto['municipio'] ?? ''),
            'cp' => trim((string) ($contacto['cp'] ?? '')),
        ];
    }

    private function resolverSemanasPeriodicidad(?string $periodicidad): ?int
    {
        if (!$periodicidad) {
            return null;
        }

        if (preg_match('/(\d+)/', $periodicidad, $coincidencias)) {
            return (int) $coincidencias[1];
        }

        return null;
    }

    private function resultado(string $filtro, bool $aprobado, ?string $mensaje = null, array $meta = []): array
    {
        return [
            'filter' => $filtro,
            'passed' => $aprobado,
            'message' => $mensaje,
            'meta' => $meta,
        ];
    }
}
