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
        $searchAll = $request->boolean('search_all');

        // Listar hojas
        $sheets = $excel->listSheets();

        $data = null;
        $results = null;
        if (! $sheet || $searchAll) {
            $results = $excel->searchAllSheets($filters['q'] ?? '', $context);
        } else {
            $data = $excel->getSheetRows($sheet, $filters, $limit, $offset);
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
