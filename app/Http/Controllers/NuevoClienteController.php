<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Supervisor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class NuevoClienteController extends Controller
{
    public function __construct(private FiltrosController $filtrosController)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $formInput = $request->input('form', []);
        if (is_string($formInput)) {
            $decoded = json_decode($formInput, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $form = $decoded;
            } else {
                $form = [];
            }
        } else {
            $form = (array) $formInput;
        }

        $ocupacion = (array) ($form['ocupacion'] ?? []);
        $ocupacion['tiene_ingresos_adicionales'] = $this->toBoolean($ocupacion['tiene_ingresos_adicionales'] ?? null) ?? false;
        if (!$ocupacion['tiene_ingresos_adicionales']) {
            $ocupacion['ingresos_adicionales'] = [];
        }
        $form['ocupacion'] = $ocupacion;

        $form['ocupacion']['ingresos_adicionales'] = collect($form['ocupacion']['ingresos_adicionales'] ?? [])
            ->map(function ($item) {
                return [
                    'concepto' => isset($item['concepto']) ? trim((string) $item['concepto']) : null,
                    'monto' => isset($item['monto']) && $item['monto'] !== '' ? $item['monto'] : null,
                    'frecuencia' => isset($item['frecuencia']) ? trim((string) $item['frecuencia']) : null,
                ];
            })
            ->filter(fn ($item) => collect($item)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->values()
            ->all();

        $garantiaFiles = array_values($request->file('garantia_archivos', []));

        $form['garantias'] = collect($form['garantias'] ?? [])
            ->map(function ($item) {
                return [
                    'propietario' => isset($item['propietario']) ? trim((string) $item['propietario']) : null,
                    'tipo' => isset($item['tipo']) ? trim((string) $item['tipo']) : null,
                    'marca' => isset($item['marca']) ? trim((string) $item['marca']) : null,
                    'modelo' => isset($item['modelo']) ? trim((string) $item['modelo']) : null,
                    'num_serie' => isset($item['num_serie']) ? trim((string) $item['num_serie']) : null,
                    'antiguedad' => isset($item['antiguedad']) ? trim((string) $item['antiguedad']) : null,
                    'monto_garantizado' => isset($item['monto_garantizado']) && $item['monto_garantizado'] !== '' ? $item['monto_garantizado'] : null,
                    'foto_url' => isset($item['foto_url']) ? trim((string) $item['foto_url']) : null,
                ];
            })
            ->filter(fn ($item) => collect($item)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->values()
            ->all();

        $familiares = (array) ($form['familiares'] ?? []);
        $familiares['tiene_conyuge'] = $this->toBoolean($familiares['tiene_conyuge'] ?? null) ?? false;
        $familiares['conyuge_vive_con_cliente'] = $this->toBoolean($familiares['conyuge_vive_con_cliente'] ?? null);
        if (!$familiares['tiene_conyuge']) {
            $familiares['nombre_conyuge'] = null;
            $familiares['celular_conyuge'] = null;
            $familiares['actividad_conyuge'] = null;
            $familiares['ingresos_semanales_conyuge'] = null;
            $familiares['domicilio_trabajo_conyuge'] = null;
            $familiares['conyuge_vive_con_cliente'] = false;
        }
        $form['familiares'] = $familiares;

        $request->merge([
            'cliente_id' => $request->input('cliente_id'),
            'form' => $form,
        ]);

        $rules = [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'form' => ['required', 'array'],

            'form.cliente.curp' => ['required', 'string', 'size:18'],
            'form.cliente.nombre' => ['required', 'string', 'max:100'],
            'form.cliente.apellido_p' => ['required', 'string', 'max:100'],
            'form.cliente.apellido_m' => ['required', 'string', 'max:100'],
            'form.cliente.fecha_nacimiento' => ['required', 'date'],

            'form.credito.monto_total' => ['required', 'numeric', 'min:0'],
            'form.credito.periodicidad' => ['required', 'string', 'max:100'],
            'form.credito.tipo_solicitud' => ['nullable', 'string', Rule::in(['nuevo', 'recredito'])],
            'form.credito.autorizacion_especial_domicilio' => ['nullable', 'boolean'],
            'form.credito.fecha_inicio' => ['required', 'date'],
            'form.credito.fecha_final' => ['required', 'date', 'after_or_equal:form.credito.fecha_inicio'],

            'form.ocupacion.actividad' => ['required', 'string', 'max:100'],
            'form.ocupacion.nombre_empresa' => ['required', 'string', 'max:100'],
            'form.ocupacion.calle' => ['required', 'string', 'max:100'],
            'form.ocupacion.numero' => ['required', 'string', 'max:10'],
            'form.ocupacion.colonia' => ['required', 'string', 'max:100'],
            'form.ocupacion.municipio' => ['required', 'string', 'max:100'],
            'form.ocupacion.telefono' => ['required', 'string', 'max:20'],
            'form.ocupacion.antiguedad' => ['required', 'string', 'max:20'],
            'form.ocupacion.monto_percibido' => ['required', 'numeric', 'min:0'],
            'form.ocupacion.periodo_pago' => ['required', 'string', 'max:20'],
            'form.ocupacion.tiene_ingresos_adicionales' => ['boolean'],
            'form.ocupacion.ingresos_adicionales' => ['array'],
            'form.ocupacion.ingresos_adicionales.*.concepto' => ['nullable', 'string', 'max:100'],
            'form.ocupacion.ingresos_adicionales.*.monto' => ['nullable', 'numeric', 'min:0'],
            'form.ocupacion.ingresos_adicionales.*.frecuencia' => ['nullable', 'string', 'max:20'],

            'form.contacto.calle' => ['required', 'string', 'max:150'],
            'form.contacto.numero_ext' => ['required', 'string', 'max:10'],
            'form.contacto.numero_int' => ['nullable', 'string', 'max:10'],
            'form.contacto.monto_mensual' => ['required', 'numeric', 'min:0'],
            'form.contacto.colonia' => ['required', 'string', 'max:100'],
            'form.contacto.municipio' => ['required', 'string', 'max:100'],
            'form.contacto.estado' => ['nullable', 'string', 'max:100'],
            'form.contacto.cp' => ['required', 'string', 'max:10'],
            'form.contacto.tiempo_en_residencia' => ['required', 'string', 'max:20'],
            'form.contacto.tel_fijo' => ['nullable', 'string', 'max:20'],
            'form.contacto.tel_cel' => ['required', 'string', 'max:20'],
            'form.contacto.tipo_de_vivienda' => ['required', 'string', 'max:100'],

            'form.familiares.tiene_conyuge' => ['boolean'],
            'form.familiares.nombre_conyuge' => ['nullable', 'string', 'max:100', 'required_if:form.familiares.tiene_conyuge,true'],
            'form.familiares.celular_conyuge' => ['nullable', 'string', 'max:20', 'required_if:form.familiares.tiene_conyuge,true'],
            'form.familiares.actividad_conyuge' => ['nullable', 'string', 'max:100', 'required_if:form.familiares.tiene_conyuge,true'],
            'form.familiares.ingresos_semanales_conyuge' => ['nullable', 'numeric', 'min:0', 'required_if:form.familiares.tiene_conyuge,true'],
            'form.familiares.domicilio_trabajo_conyuge' => ['nullable', 'string', 'max:255', 'required_if:form.familiares.tiene_conyuge,true'],
            'form.familiares.personas_en_domicilio' => ['required', 'integer', 'min:0'],
            'form.familiares.dependientes_economicos' => ['required', 'integer', 'min:0'],
            'form.familiares.conyuge_vive_con_cliente' => ['nullable', 'boolean', 'required_if:form.familiares.tiene_conyuge,true'],

            'form.aval.curp' => ['required', 'string', 'size:18'],
            'form.aval.nombre' => ['required', 'string', 'max:100'],
            'form.aval.apellido_p' => ['required', 'string', 'max:100'],
            'form.aval.apellido_m' => ['required', 'string', 'max:100'],
            'form.aval.fecha_nacimiento' => ['required', 'date'],
            'form.aval.direccion' => ['required', 'string', 'max:255'],
            'form.aval.telefono' => ['required', 'string', 'max:20'],
            'form.aval.parentesco' => ['required', 'string', 'max:20'],

            'form.garantias' => ['array', 'max:8'],
            'form.garantias.*.propietario' => ['nullable', 'string', 'max:100'],
            'form.garantias.*.tipo' => ['nullable', 'string', 'max:100'],
            'form.garantias.*.marca' => ['nullable', 'string', 'max:100'],
            'form.garantias.*.modelo' => ['nullable', 'string', 'max:100'],
            'form.garantias.*.num_serie' => ['nullable', 'string', 'max:100'],
            'form.garantias.*.antiguedad' => ['nullable', 'string', 'max:20'],
            'form.garantias.*.monto_garantizado' => ['nullable', 'numeric', 'min:0'],
            'form.garantias.*.foto_url' => ['nullable', 'string', 'max:255'],
            'garantia_archivos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($form, $garantiaFiles) {
            $requiresIngresos = $form['ocupacion']['tiene_ingresos_adicionales'] ?? false;
            if ($requiresIngresos) {
                foreach ($form['ocupacion']['ingresos_adicionales'] ?? [] as $index => $ingreso) {
                    if (in_array(null, $ingreso, true) || in_array('', $ingreso, true)) {
                        $validator->errors()->add("form.ocupacion.ingresos_adicionales.$index", 'Completa todos los campos de cada ingreso adicional.');
                    }
                }
            }

            foreach ($form['garantias'] ?? [] as $index => $garantia) {
                $missingRequired = false;
                foreach (['propietario', 'tipo', 'antiguedad', 'monto_garantizado'] as $field) {
                    if ($garantia[$field] === null || $garantia[$field] === '') {
                        $validator->errors()->add("form.garantias.$index.$field", 'Completa todos los campos obligatorios de la garantia.');
                        $missingRequired = true;
                        break;
                    }
                }

                if ($missingRequired) {
                    continue;
                }

                $hasFile = isset($garantiaFiles[$index]);
                $hasUrl = isset($garantia['foto_url']) && $garantia['foto_url'] !== null && $garantia['foto_url'] !== '';

                if (!$hasFile && !$hasUrl) {
                    $validator->errors()->add("form.garantias.$index.foto", 'Agrega una foto de la garantia.');
                }
            }
        });

        $validated = $validator->validate();
        $form = $validated['form'];
        if (isset($form['credito']['autorizacion_especial_domicilio'])) {
            $form['credito']['autorizacion_especial_domicilio'] = (bool) ($this->toBoolean($form['credito']['autorizacion_especial_domicilio']) ?? false);
        } else {
            $form['credito']['autorizacion_especial_domicilio'] = false;
        }
        if (isset($form['credito']['tipo_solicitud']) && is_string($form['credito']['tipo_solicitud'])) {
            $form['credito']['tipo_solicitud'] = strtolower($form['credito']['tipo_solicitud']);
        }

        $clienteContexto = Cliente::with(['promotor.supervisor', 'creditos' => fn ($query) => $query->orderByDesc('fecha_inicio')])
            ->findOrFail($validated['cliente_id']);

        $usuario = Auth::user();
        $promotorActual = $usuario?->promotor;
        $supervisorActualId = $usuario ? Supervisor::where('user_id', $usuario->id)->value('id') : null;

        $ultimoCredito = $clienteContexto->creditos->first();

        $tipoSolicitud = $form['credito']['tipo_solicitud'] ?? ($clienteContexto->cartera_estado === 'moroso' ? 'recredito' : 'nuevo');

        $contextoFiltros = [
            'tipo_solicitud' => $tipoSolicitud,
            'promotor_id' => $promotorActual?->id,
            'supervisor_id' => $supervisorActualId,
            'autorizacion_especial_domicilio' => (bool) ($form['credito']['autorizacion_especial_domicilio'] ?? false),
            'fecha_solicitud' => now(),
            'ultimo_credito' => $ultimoCredito,
            'credito_actual_id' => $ultimoCredito?->id,
        ];

        // Aquí esta CURP unica filtro
        // Aquí esta Doble Firma Aval filtro
        // Aquí esta CreditoEnFalla filtro
        // Aquí esta CreditoActivo filtro
        // Aquí esta OtraPlaza filtro
        // Aquí esta BloqueoFallaPromotora_5 filtro
        // Aquí esta DobleDomicilio filtro
        // Aquí esta BloqueoDeTiempoRecreditos filtro
        $resultadoFiltros = $this->filtrosController->evaluar($clienteContexto, $form, $contextoFiltros);

        if (!$resultadoFiltros['passed']) {
            return response()->json([
                'message' => $resultadoFiltros['message'] ?? 'El cliente no superó los filtros requeridos.',
                'failed_filter' => $resultadoFiltros['failed_filter'],
                'detalles' => $resultadoFiltros['results'],
            ], 422);
        }

        try {
                $cliente = DB::transaction(function () use ($validated, $form, $garantiaFiles) {
                    $cliente = Cliente::lockForUpdate()->findOrFail($validated['cliente_id']);
                    $cliente->fecha_nacimiento = $form['cliente']['fecha_nacimiento'];
                    $cliente->save();

                $credito = Credito::firstOrNew(['cliente_id' => $cliente->id]);
                $credito->monto_total = (float) $form['credito']['monto_total'];
                $credito->periodicidad = $form['credito']['periodicidad'];
                $credito->fecha_inicio = $form['credito']['fecha_inicio'];
                $credito->fecha_final = $form['credito']['fecha_final'];
                $credito->estado = $credito->estado ?? 'pendiente';
                $credito->interes = $credito->interes ?? 0;
                $credito->save();

                $ocupacion = $credito->ocupacion()->firstOrNew();
                $ocupacion->fill([
                    'credito_id' => $credito->id,
                    'actividad' => $form['ocupacion']['actividad'],
                    'nombre_empresa' => $form['ocupacion']['nombre_empresa'],
                    'calle' => $form['ocupacion']['calle'],
                    'numero' => $form['ocupacion']['numero'],
                    'colonia' => $form['ocupacion']['colonia'],
                    'municipio' => $form['ocupacion']['municipio'],
                    'telefono' => $form['ocupacion']['telefono'],
                    'antiguedad' => $form['ocupacion']['antiguedad'],
                    'monto_percibido' => (float) $form['ocupacion']['monto_percibido'],
                    'periodo_pago' => $form['ocupacion']['periodo_pago'],
                ]);
                $ocupacion->save();

                $ocupacion->ingresosAdicionales()->delete();
                foreach ($form['ocupacion']['ingresos_adicionales'] as $ingreso) {
                    $ocupacion->ingresosAdicionales()->create([
                        'concepto' => $ingreso['concepto'],
                        'monto' => (float) $ingreso['monto'],
                        'frecuencia' => $ingreso['frecuencia'],
                    ]);
                }

                $credito->datoContacto()->updateOrCreate(
                    ['credito_id' => $credito->id],
                    [
                        'credito_id' => $credito->id,
                        'calle' => $form['contacto']['calle'],
                        'numero_ext' => $form['contacto']['numero_ext'],
                        'numero_int' => $this->nullIfBlank($form['contacto']['numero_int'] ?? null),
                        'monto_mensual' => (int) round($form['contacto']['monto_mensual']),
                        'colonia' => $form['contacto']['colonia'],
                        'municipio' => $form['contacto']['municipio'],
                        'estado' => $this->nullIfBlank($form['contacto']['estado'] ?? null),
                        'cp' => $form['contacto']['cp'],
                        'tiempo_en_residencia' => $form['contacto']['tiempo_en_residencia'],
                        'tel_fijo' => $this->nullIfBlank($form['contacto']['tel_fijo'] ?? null),
                        'tel_cel' => $form['contacto']['tel_cel'],
                        'tipo_de_vivienda' => $form['contacto']['tipo_de_vivienda'],
                    ]
                );

                $tieneConyuge = (bool) ($form['familiares']['tiene_conyuge'] ?? false);
                $credito->informacionFamiliar()->updateOrCreate(
                    ['credito_id' => $credito->id],
                    [
                        'credito_id' => $credito->id,
                        'nombre_conyuge' => $tieneConyuge ? ($this->nullIfBlank($form['familiares']['nombre_conyuge'] ?? null) ?? '') : '',
                        'celular_conyuge' => $tieneConyuge ? ($this->nullIfBlank($form['familiares']['celular_conyuge'] ?? null) ?? '') : '',
                        'actividad_conyuge' => $tieneConyuge ? ($this->nullIfBlank($form['familiares']['actividad_conyuge'] ?? null) ?? '') : '',
                        'ingresos_semanales_conyuge' => $tieneConyuge ? (float) ($form['familiares']['ingresos_semanales_conyuge'] ?? 0) : 0.0,
                        'domicilio_trabajo_conyuge' => $tieneConyuge ? ($this->nullIfBlank($form['familiares']['domicilio_trabajo_conyuge'] ?? null) ?? '') : '',
                        'personas_en_domicilio' => (int) $form['familiares']['personas_en_domicilio'],
                        'dependientes_economicos' => (int) $form['familiares']['dependientes_economicos'],
                        'conyuge_vive_con_cliente' => $tieneConyuge ? (bool) ($form['familiares']['conyuge_vive_con_cliente'] ?? false) : false,
                    ]
                );

                $credito->avales()->updateOrCreate(
                    ['credito_id' => $credito->id, 'CURP' => $form['aval']['curp']],
                    [
                        'CURP' => $form['aval']['curp'],
                        'credito_id' => $credito->id,
                        'nombre' => $form['aval']['nombre'],
                        'apellido_p' => $form['aval']['apellido_p'],
                        'apellido_m' => $form['aval']['apellido_m'],
                        'fecha_nacimiento' => $form['aval']['fecha_nacimiento'],
                        'direccion' => $form['aval']['direccion'],
                        'telefono' => $form['aval']['telefono'],
                        'parentesco' => $form['aval']['parentesco'],
                    ]
                );

                $credito->garantias()->delete();
                foreach ($form['garantias'] as $index => $garantia) {
                    $file = $garantiaFiles[$index] ?? null;
                    $fotoUrl = $garantia['foto_url'];

                    if ($file) {
                        $path = $file->store('garantias', 'public');
                        $fotoUrl = Storage::disk('public')->url($path);
                    }

                    $credito->garantias()->create([
                        'propietario' => $garantia['propietario'],
                        'tipo' => $garantia['tipo'],
                        'marca' => $this->nullIfBlank($garantia['marca']),
                        'modelo' => $this->nullIfBlank($garantia['modelo']),
                        'num_serie' => $this->nullIfBlank($garantia['num_serie']),
                        'antiguedad' => $garantia['antiguedad'],
                        'monto_garantizado' => (float) $garantia['monto_garantizado'],
                        'foto_url' => $fotoUrl,
                    ]);
                }

                return $cliente;
            });

            return response()->json([
                'message' => 'Informacion guardada correctamente.',
                'cliente' => [
                    'id' => $cliente->id,
                    'nombre' => trim($cliente->nombre . ' ' . $cliente->apellido_p . ' ' . $cliente->apellido_m),
                ],
            ], 201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo guardar la informacion del cliente.',
            ], 500);
        }
    }

    public function RegistrarCredito(Request $request, Cliente $cliente): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'accion' => ['required', Rule::in(['aprobar', 'rechazar'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('accion') ?? 'Acción inválida proporcionada.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $accion = $validator->validated()['accion'];
        $credito = $cliente->credito;

        if (!$credito) {
            return response()->json(['message' => 'El cliente no tiene un credito asociado.'], 404);
        }

        DB::transaction(function () use ($accion, $cliente, $credito) {
            if ($accion === 'aprobar') {
                $credito->estado = 'Supervisado';
                $cliente->cartera_estado = 'activo';
                $cliente->activo = true;
            } else {
                $credito->estado = 'Rechazado';
                $cliente->cartera_estado = 'inactivo';
                $cliente->activo = false;
            }

            $credito->save();
            $cliente->save();
        });

        $cliente->refresh();
        $credito->refresh();

        $message = $accion === 'aprobar'
            ? 'Cliente supervisado correctamente.'
            : 'Cliente rechazado correctamente.';

        return response()->json([
            'message' => $message,
            'cliente' => [
                'id' => $cliente->id,
                'cartera_estado' => $cliente->cartera_estado,
                'credito_estado' => $credito->estado,
            ],
        ]);
    }

    private function toBoolean($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return match (strtolower((string) $value)) {
            '1', 'true', 'si', 'sÃƒÂ­', 'on', 'yes' => true,
            '0', 'false', 'no', 'off' => false,
            default => null,
        };
    }

    private function nullIfBlank($value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === null || $value === '' ? null : (string) $value;
    }
}



