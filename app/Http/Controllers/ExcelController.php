<?php

namespace App\Http\Controllers;

use App\Services\ExcelReaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExcelController extends Controller
{
    public function index(Request $request, ExcelReaderService $excel)
    {
        // Nota: ExcelReaderService consulta un Excel historico independiente de MySQL; aqui solo se lee.
        // Parametros de busqueda
        $sheet = $request->input('sheet');
        $filters = $request->only(['q', 'date_from', 'date_to', 'monto_min', 'monto_max']);
        $limit = (int) $request->input('limit', 50);
        $offset = (int) $request->input('offset', 0);
        $context = (int) $request->input('context', 3);

        // Listar hojas solo si se solicita explicitamente
        $sheets = [];
        if ($request->boolean('list_sheets')) {
            $sheets = $excel->listSheets();
        }

        $data = null;
        if ($sheet) {
            $data = $excel->getSheetRows($sheet, $filters, $limit, $offset);
        }

        $results = null;
        $q = $filters['q'] ?? null;
        if ($q !== null && $q !== '') {
            $resultsRaw = $excel->searchAllSheets($q, $context);

            $results = collect($resultsRaw)
                ->map(function ($item) {
                    $contextItems = collect($item['context'] ?? [])
                        ->mapWithKeys(function ($value, $key) {
                            if (is_array($value)) {
                                $name = $value['header'] ?? ($value['name'] ?? $key);
                                $val = $value['value'] ?? null;

                                return [$name => $val];
                            }

                            return [$key => $value];
                        })
                        ->all();

                    return [
                        'sheet' => $item['sheet'] ?? '',
                        'match_value' => $item['match_value'] ?? '',
                        'context' => $contextItems,
                    ];
                })
                ->all();
        }

        return view('consulta_base_datos_historica', [
            'sheets' => $sheets,
            'current' => $sheet,
            'data' => $data,
            'results' => $results,
            'filters' => $filters,
            'limit' => $limit,
            'offset' => $offset,
            'context' => $context,
        ]);
    }

    public function deudores(Request $request, ExcelReaderService $excel)
    {
        // La lista de deudores vive unicamente en el Excel historico; las migraciones o seeders de MySQL no alteran estos datos.
        $cliente = $request->input('cliente');
        $filters = $request->only(['cliente']);
        $limit = (int) $request->input('limit', 50);
        $offset = (int) $request->input('offset', 0);
        $context = (int) $request->input('context', 3);

        $resultados = null;
        if ($cliente !== null && $cliente !== '') {
            $resultados = $excel->searchDebtors($cliente);

            $normalize = fn (?string $value): string => Str::of($value)->ascii()->lower()->trim()->toString();

            $porClientePromotora = collect($resultados)->groupBy(function ($item) use ($normalize) {
                return $normalize($item['cliente'] ?? '') . '|' . $normalize($item['promotora'] ?? '');
            });

            $porCliente = $porClientePromotora->groupBy(function ($group, $key) {
                return explode('|', $key)[0];
            });

            $resultados = collect($resultados)
                ->map(function ($item) use ($porClientePromotora, $porCliente, $normalize) {
                    $clienteNorm = $normalize($item['cliente'] ?? '');
                    $pairKey = $clienteNorm . '|' . $normalize($item['promotora'] ?? '');

                    $alerta = null;
                    if (($porClientePromotora[$pairKey] ?? collect())->count() > 1) {
                        $alerta = 'Cliente y promotora repetidos';
                    } elseif (($porCliente[$clienteNorm] ?? collect())->count() > 1) {
                        $alerta = 'Coincidencia de deudor';
                    }

                    $item['alerta'] = $alerta;

                    return $item;
                })
                ->sortBy('cliente', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        }

        return view('consulta_base_datos_historica', [
            'sheets' => [],
            'current' => null,
            'data' => null,
            'results' => null,
            'deudores' => $resultados,
            'filters' => $filters,
            'limit' => $limit,
            'offset' => $offset,
            'context' => $context,
        ]);
    }

    public function historial(Request $request, ExcelReaderService $excel)
    {
        // Igual que en los otros metodos, solo se consulta la fuente historica del Excel sin modificar datos relacionales.
        $cliente = $request->input('cliente');
        $filters = $request->only(['cliente']);
        $limit = (int) $request->input('limit', 50);
        $offset = (int) $request->input('offset', 0);
        $context = (int) $request->input('context', 3);

        $historial = null;
        if ($cliente !== null && $cliente !== '') {
            $historial = collect($excel->searchClientHistory($cliente))
                ->sortBy(fn($item) => $item['cliente']['nombre'] ?? '', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        }

        return view('consulta_base_datos_historica', [
            'sheets' => [],
            'current' => null,
            'data' => null,
            'results' => null,
            'historial' => $historial,
            'filters' => $filters,
            'limit' => $limit,
            'offset' => $offset,
            'context' => $context,
        ]);
    }
}