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
            $results = $excel->searchAllSheets($q, $context);
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
}
