<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BusquedaClientesService
{
    /**
     * Ejecuta la búsqueda de clientes para el supervisor indicado.
     *
     * @return array{query: string, resultados: Collection<int, array>, puedeBuscar: bool}
     */
    public function buscar(Request $request, ?Supervisor $supervisor): array
    {
        $query = trim((string) $request->query('q', ''));

        $promotores = $supervisor?->promotores ?? collect();
        $promotores = $promotores instanceof Collection ? $promotores : collect($promotores);
        $promotorIds = $promotores->pluck('id');

        $resultados = collect();

        if ($query !== '' && $promotorIds->isNotEmpty()) {
            $pattern = '%' . str_replace(' ', '%', $query) . '%';

            $clientes = Cliente::query()
                ->whereIn('promotor_id', $promotorIds->all())
                ->where(function ($clienteQuery) use ($pattern) {
                    $clienteQuery
                        ->where('nombre', 'like', $pattern)
                        ->orWhere('apellido_p', 'like', $pattern)
                        ->orWhere('apellido_m', 'like', $pattern)
                        ->orWhereHas('credito.datoContacto', function ($contactoQuery) use ($pattern) {
                            $contactoQuery->where(function ($inner) use ($pattern) {
                                $inner->where('calle', 'like', $pattern)
                                    ->orWhere('colonia', 'like', $pattern)
                                    ->orWhere('municipio', 'like', $pattern)
                                    ->orWhere('estado', 'like', $pattern)
                                    ->orWhere('cp', 'like', $pattern);
                            });
                        });
                })
                ->with([
                    'promotor:id,supervisor_id,nombre,apellido_p,apellido_m',
                    'promotor.supervisor:id,nombre,apellido_p,apellido_m',
                    'credito' => function ($creditoQuery) {
                        $creditoQuery->select(
                            'creditos.id',
                            'creditos.cliente_id',
                            'creditos.estado',
                            'creditos.monto_total',
                            'creditos.periodicidad',
                            'creditos.fecha_inicio',
                            'creditos.fecha_final'
                        )
                            ->with([
                                'datoContacto:id,credito_id,calle,numero_ext,numero_int,colonia,municipio,estado,cp,tel_fijo,tel_cel',
                                'avales:id,credito_id,CURP,nombre,apellido_p,apellido_m,telefono,direccion',
                                'avales.documentos:id,aval_id,tipo_doc,url_s3,nombre_arch',
                            ]);
                    },
                    'documentos:id,cliente_id,credito_id,tipo_doc,url_s3,nombre_arch',
                ])
                ->orderBy('nombre')
                ->orderBy('apellido_p')
                ->limit(30)
                ->get();

            $resultados = $clientes->map(fn (Cliente $cliente) => $this->mapBusquedaCliente($cliente, $supervisor));
        }

        return [
            'query' => $query,
            'resultados' => $resultados,
            'puedeBuscar' => $promotorIds->isNotEmpty(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapBusquedaCliente(Cliente $cliente, ?Supervisor $contextSupervisor): array
    {
        $promotor = $cliente->promotor;
        $supervisor = $promotor?->supervisor;

        $belongsToContext = $contextSupervisor
            ? ($supervisor?->id === $contextSupervisor->id)
            : true;

        $credito = $cliente->credito;
        $estadoCredito = $credito?->estado ?? null;
        $estadoCreditoTexto = $estadoCredito
            ? (string) Str::of($estadoCredito)->replace('_', ' ')->title()
            : 'Sin crédito';

        $datoContacto = $credito?->datoContacto;
        $telefonosCliente = $datoContacto
            ? collect([$datoContacto->tel_cel, $datoContacto->tel_fijo])->filter()->unique()->values()->all()
            : [];

        $domicilioCliente = $datoContacto
            ? collect([
                trim((string) ($datoContacto->calle ?? '') . ' ' . ($datoContacto->numero_ext ?? '')),
                $datoContacto->numero_int ? 'Int. ' . $datoContacto->numero_int : null,
                $datoContacto->colonia,
                $datoContacto->municipio,
                $datoContacto->estado,
                $datoContacto->cp ? 'CP ' . $datoContacto->cp : null,
            ])->filter()->implode(', ')
            : null;

        $clienteDocumentos = $cliente->documentos instanceof Collection
            ? $cliente->documentos
            : collect($cliente->documentos ?? []);

        $ultimoCreditoId = $credito?->id;
        $documentosCliente = $ultimoCreditoId
            ? $clienteDocumentos->where('credito_id', $ultimoCreditoId)
            : collect();

        $clienteDocs = $this->extractDocumentPreviews($documentosCliente);

        $avales = $credito?->avales instanceof Collection
            ? $credito->avales
            : collect($credito?->avales ?? []);

        $aval = $avales->sortByDesc('id')->first();

        $avalDocumentos = $aval && $aval->documentos instanceof Collection
            ? $aval->documentos
            : collect($aval?->documentos ?? []);

        $avalDocs = $this->extractDocumentPreviews($avalDocumentos);

        $avalTelefonos = $aval ? collect([$aval->telefono])->filter()->unique()->values()->all() : [];
        $avalDireccion = $aval?->direccion;

        $supervisorNombre = $this->buildFullName($supervisor, 'Sin supervisor');
        $avalNombre = $aval ? $this->buildFullName($aval, 'Sin aval') : 'Sin aval';

        return [
            'id' => $cliente->id,
            'nombre' => $this->buildFullName($cliente, 'Sin nombre'),
            'estatus_credito' => $estadoCreditoTexto,
            'supervisor' => $supervisorNombre,
            'aval' => $avalNombre,
            'promotor' => $this->buildFullName($promotor, 'Sin promotor'),
            'puede_detallar' => $belongsToContext,
            'detalle' => [
                'supervisor' => $supervisorNombre,
                'estatus_credito' => $estadoCreditoTexto,
                'cliente' => [
                    'telefonos' => $telefonosCliente,
                    'domicilio' => $domicilioCliente,
                    'documentos' => $clienteDocs,
                ],
                'aval' => [
                    'nombre' => $avalNombre,
                    'telefonos' => $avalTelefonos,
                    'domicilio' => $avalDireccion,
                    'documentos' => $avalDocs,
                ],
                'garantias' => $credito?->garantias instanceof Collection
                    ? $credito->garantias->toArray()
                    : collect($credito?->garantias ?? [])->toArray(),
            ],
        ];
    }

    private function buildFullName($model, string $default = '—'): string
    {
        if (!$model) {
            return $default;
        }

        $parts = collect([
            data_get($model, 'nombre'),
            data_get($model, 'apellido_p'),
            data_get($model, 'apellido_m'),
        ])->filter(function ($value) {
            return $value !== null && $value !== '';
        });

        return $parts->isNotEmpty() ? $parts->implode(' ') : $default;
    }

    private function extractDocumentPreviews($documents): array
    {
        $documents = $documents instanceof Collection ? $documents : collect($documents);

        $mapDoc = function ($document) {
            return [
                'titulo' => (string) Str::of(data_get($document, 'tipo_doc', 'Documento'))->replace('_', ' ')->title(),
                'url' => data_get($document, 'url_s3'),
                'archivo' => data_get($document, 'nombre_arch'),
            ];
        };

        $filterKeywords = function (array $keywords) use ($documents, $mapDoc) {
            return $documents
                ->filter(function ($document) use ($keywords) {
                    $type = Str::lower((string) data_get($document, 'tipo_doc', ''));
                    foreach ($keywords as $keyword) {
                        if (Str::contains($type, $keyword)) {
                            return true;
                        }
                    }

                    return false;
                })
                ->map($mapDoc)
                ->filter(fn ($doc) => !empty($doc['url']))
                ->values()
                ->all();
        };

        return [
            'ine' => $filterKeywords(['ine', 'identificacion', 'id']),
            'comprobante' => $filterKeywords(['domic', 'comprobante']),
        ];
    }
}
