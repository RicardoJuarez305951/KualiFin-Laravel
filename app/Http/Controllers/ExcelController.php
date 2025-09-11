<?php

namespace App\Http\Controllers;

use App\Services\ExcelReaderService;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    public function index(Request $request, ExcelReaderService $excel)
    {
        // Parámetros de búsqueda
        $sheet = $request->input('sheet');
        $filters = $request->only(['q', 'date_from', 'date_to', 'monto_min', 'monto_max']);
        $limit = (int) $request->input('limit', 50);
        $offset = (int) $request->input('offset', 0);
        $context = (int) $request->input('context', 3);

        // Listar hojas solo si se solicita explícitamente
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
        $cliente = $request->input('cliente');
        $filters = $request->only(['cliente']);
        $limit = (int) $request->input('limit', 50);
        $offset = (int) $request->input('offset', 0);
        $context = (int) $request->input('context', 3);

        $resultados = null;
        if ($cliente !== null && $cliente !== '') {
            $resultados = $excel->searchDebtors($cliente);
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
        $cliente = $request->input('cliente');
        $filters = $request->only(['cliente']);
        $limit = (int) $request->input('limit', 50);
        $offset = (int) $request->input('offset', 0);
        $context = (int) $request->input('context', 3);

        $historial = null;
        if ($cliente !== null && $cliente !== '') {
            $historial = $excel->searchClientHistory($cliente);
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
