<?php

namespace App\Services;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ExcelReaderService
{
    public function path(): string
    {
        return env('EXCEL_STORAGE_PATH', config('excel.local_path', 'excel/origen.xlsx'));
    }

    public function downloadAndStore(): array
    {
        $url = env('EXCEL_SOURCE_URL');
        $path = $this->path();
        $timeout = (int) env('EXCEL_TIMEOUT', 60);

        if (! $url) {
            Log::channel('excel')->warning('EXCEL_SOURCE_URL no configurada.');
            return ['ok' => false, 'message' => 'URL no configurada'];
        }

        try {
            Log::channel('excel')->info('Iniciando descarga del Excel', ['url' => $url]);
            $response = Http::timeout($timeout)->get($url);

            if (! $response->ok()) {
                Log::channel('excel')->error('Fallo al descargar Excel', [
                    'status' => $response->status(),
                    'reason' => $response->reason(),
                ]);
                return ['ok' => false, 'message' => 'HTTP '.$response->status()];
            }

            $bytes = $response->body();
            Storage::put($path, $bytes);

            $size = strlen($bytes);
            Log::channel('excel')->info('Excel guardado correctamente', [
                'path' => $path,
                'size_bytes' => $size,
            ]);

            return ['ok' => true, 'path' => $path, 'size' => $size];
        } catch (Throwable $e) {
            Log::channel('excel')->error('Error descargando Excel', ['error' => $e->getMessage()]);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function refreshIfStale(int $maxAgeMinutes = 60): array
    {
        $path = $this->path();
        $exists = Storage::exists($path);

        if ($exists) {
            $lastModified = Storage::lastModified($path);
            $ageMinutes = (time() - $lastModified) / 60;

            if ($ageMinutes < $maxAgeMinutes) {
                Log::channel('excel')->info('Excel local vigente, no se descarga', [
                    'path' => $path,
                    'age_minutes' => round($ageMinutes, 2),
                ]);

                return ['ok' => true, 'path' => $path, 'cached' => true];
            }
        }

        return $this->downloadAndStore();
    }

    public function listSheets(): array
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open(Storage::path($this->path()));

        $names = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $names[] = $sheet->getName();
        }
        $reader->close();

        return $names;
    }

    /**
     * Busca deudores en la hoja "DEUDORES-NO PRESTAR".
     * - Detecta índices por encabezado (izquierda y derecha).
     * - Normaliza fecha (Spout date, serial Excel, texto d/m/Y) a 'Y-m-d'.
     * - Aplica castAssoc() para entregar Carbon/float a quien consuma.
     */
    public function searchDebtors(string $cliente): array
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open(Storage::path($this->path()));

        $clienteLower = Str::lower($cliente);
        $results = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() !== 'DEUDORES-NO PRESTAR') {
                continue;
            }

            // Fallback por si no encontramos encabezados
            $layout = [
                'left'  => ['fecha' => 0, 'cliente' => 1, 'promotora' => 2, 'deuda' => 3],
                'right' => ['fecha' => 6, 'cliente' => 7, 'promotora' => 8, 'deuda' => 9],
                'header_row' => null,
            ];

            // Detectar encabezados en primeras filas
            $headerFound = false;
            foreach ($sheet->getRowIterator() as $rIndex => $row) {
                if ($rIndex > 15) break; // no más de 15 filas para detección
                $cells = $row->getCells();
                $vals  = array_map(fn($c) => $this->getCellText($c), $cells);
                $lower = array_map(fn($t) => Str::of($t)->trim()->lower()->toString(), $vals);

                $ixFechaL     = $this->findIndexLike($lower, ['fecha']);
                $ixClienteL   = $this->findIndexLike($lower, ['cliente']);
                $ixPromotoraL = $this->findIndexLike($lower, ['promotora','promotor','promotora/ promotor']);
                $ixDeudaL     = $this->findIndexLike($lower, ['deuda','adeudo','saldo']);

                $rightStart   = ($ixDeudaL ?? -1) + 1;
                $ixFechaR     = $this->findIndexLike($lower, ['fecha'], minIndex: $rightStart);
                $ixClienteR   = $this->findIndexLike($lower, ['cliente'], minIndex: $rightStart);
                $ixPromotoraR = $this->findIndexLike($lower, ['promotora','promotor','promotora/ promotor'], minIndex: $rightStart);
                $ixDeudaR     = $this->findIndexLike($lower, ['deuda','adeudo','saldo'], minIndex: $rightStart);

                $leftOk  = $ixFechaL   !== null && $ixClienteL   !== null && $ixPromotoraL   !== null && $ixDeudaL   !== null;
                $rightOk = $ixFechaR   !== null && $ixClienteR   !== null && $ixPromotoraR   !== null && $ixDeudaR   !== null;

                if ($leftOk) {
                    $layout['left'] = ['fecha'=>$ixFechaL,'cliente'=>$ixClienteL,'promotora'=>$ixPromotoraL,'deuda'=>$ixDeudaL];
                    $headerFound = true;
                    $layout['header_row'] = $rIndex;
                }
                if ($rightOk) {
                    $layout['right'] = ['fecha'=>$ixFechaR,'cliente'=>$ixClienteR,'promotora'=>$ixPromotoraR,'deuda'=>$ixDeudaR];
                    $headerFound = true;
                    $layout['header_row'] = $rIndex;
                }

                if ($headerFound) break;
            }

            $startIndex = $layout['header_row'] !== null ? ($layout['header_row'] + 1) : 5;

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex < $startIndex) continue;

                $cells = $row->getCells();

                // Helper lectura segura (si getCellText da vacío, intenta valor crudo)
                $cellText = function(array $cells, ?int $i): ?string {
                    if ($i === null || !isset($cells[$i])) return null;
                    $txt = $this->getCellText($cells[$i]);
                    if ($txt === '' || $txt === null) {
                        $raw = $cells[$i]->getValue();
                        return is_scalar($raw) ? (string)$raw : null;
                    }
                    return $txt;
                };

                // ---- tabla izquierda ----
                $cliente1 = $cellText($cells, $layout['left']['cliente'] ?? null);
                if ($cliente1 !== null && $cliente1 !== '' &&
                    Str::contains(Str::lower($cliente1), $clienteLower)) {

                    $fechaRaw = $cellText($cells, $layout['left']['fecha'] ?? null);
                    $prom     = $cellText($cells, $layout['left']['promotora'] ?? null);
                    $deuda    = $cellText($cells, $layout['left']['deuda'] ?? null);

                    $record = [
                        'fecha_prestamo' => $this->normalizeDateValue($cells[$layout['left']['fecha']] ?? null, $fechaRaw),
                        'cliente'        => $cliente1,
                        'promotora'      => $prom,
                        'deuda'          => $deuda,
                    ];
                    $results[] = $this->castAssoc($record);
                }

                // ---- tabla derecha ----
                $cliente2 = $cellText($cells, $layout['right']['cliente'] ?? null);
                if ($cliente2 !== null && $cliente2 !== '' &&
                    Str::contains(Str::lower($cliente2), $clienteLower)) {

                    $fechaRaw = $cellText($cells, $layout['right']['fecha'] ?? null);
                    $prom     = $cellText($cells, $layout['right']['promotora'] ?? null);
                    $deuda    = $cellText($cells, $layout['right']['deuda'] ?? null);

                    $record = [
                        'fecha_prestamo' => $this->normalizeDateValue($cells[$layout['right']['fecha']] ?? null, $fechaRaw),
                        'cliente'        => $cliente2,
                        'promotora'      => $prom,
                        'deuda'          => $deuda,
                    ];
                    $results[] = $this->castAssoc($record);
                }
            }

            break; // solo esa hoja
        }

        $reader->close();

        return $results;
    }

    public function searchClientHistory(string $cliente): array
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open(Storage::path($this->path()));

        $clienteLower = Str::lower($cliente);
        $results = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            $headerRowIndex = null;
            $headerRow = null;
            $startCol = null;

            // Guardar primeras filas para detectar fechas de pago
            $topRows = [];
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex <= 5) {
                    $topRows[$rowIndex] = $row->getCells();
                }

                if ($headerRowIndex === null) {
                    $cells = $row->getCells();
                    foreach ($cells as $colIndex => $cell) {
                        $txt = Str::of($this->getCellText($cell))->trim()->lower();
                        if ($txt === 'fecha') {
                            $headerRowIndex = $rowIndex;
                            $headerRow = $row;
                            $startCol = $colIndex;
                            break 2;
                        }
                    }
                }
            }

            if ($headerRowIndex === null || $headerRow === null) {
                continue;
            }

            // Mapear columnas principales
            $cols = [];
            $cells = $headerRow->getCells();
            for ($i = $startCol; $i < count($cells); $i++) {
                $label = Str::of($this->getCellText($cells[$i]))->trim()->lower();
                switch ($label) {
                    case 'fecha':
                        $cols['fecha_credito'] = $i;
                        break;
                    case 'nombre':
                        $cols['nombre'] = $i;
                        break;
                    case 'prestamo':
                        $cols['prestamo'] = $i;
                        break;
                    case 'abono':
                        $cols['abono'] = $i;
                        break;
                    case 'debe':
                        $cols['debe'] = $i;
                        break;
                    case 'observaciones':
                        $cols['observaciones'] = $i;
                        break 2;
                }
            }

            // Detectar columnas de fechas de pago en filas superiores
            $paymentCols = [];
            $row1 = $topRows[1] ?? [];
            $row2 = $topRows[2] ?? [];
            $fechaIndexes = [];
            foreach ($row1 as $i => $cell) {
                $txt = Str::of($this->getCellText($cell))->trim()->lower();
                if ($txt === 'fecha') {
                    $fechaIndexes[] = $i;
                }
            }
            // Ignorar la primera 'FECHA'
            if (count($fechaIndexes) > 1) {
                foreach (array_slice($fechaIndexes, 1) as $i) {
                    $raw = $this->getCellText($row2[$i] ?? null);
                    $norm = $this->normalizeDateValue($row2[$i] ?? null, $raw);
                    if ($norm) {
                        $paymentCols[$norm] = $i;
                    }
                }
            }

            $clienteFound = false;
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex <= $headerRowIndex) {
                    continue;
                }

                $cells = $row->getCells();
                $nombre = $this->getCellText($cells[$cols['nombre'] ?? -1] ?? null);
                if ($nombre && Str::lower($nombre) === $clienteLower) {
                    $assoc = [
                        'fecha_credito' => $this->normalizeDateValue($cells[$cols['fecha_credito'] ?? -1] ?? null, $this->getCellText($cells[$cols['fecha_credito'] ?? -1] ?? null)),
                        'nombre'        => $nombre,
                        'prestamo'      => $this->getCellText($cells[$cols['prestamo'] ?? -1] ?? null),
                        'abono'         => $this->getCellText($cells[$cols['abono'] ?? -1] ?? null),
                        'debe'          => $this->getCellText($cells[$cols['debe'] ?? -1] ?? null),
                    ];
                    if (isset($cols['observaciones'])) {
                        $assoc['observaciones'] = $this->getCellText($cells[$cols['observaciones']] ?? null);
                    }

                    $pagos = [];
                    foreach ($paymentCols as $fecha => $colIndex) {
                        $val = $this->getCellText($cells[$colIndex] ?? null);
                        if ($val !== null && $val !== '') {
                            $pagos[$fecha] = $this->parseMoney($val);
                        }
                    }

                    $results[] = [
                        'promotora' => $sheet->getName(),
                        'cliente'   => $this->castAssoc($assoc),
                        'pagos'     => $pagos,
                    ];
                    $clienteFound = true;
                    break;
                }
            }

            if ($clienteFound) {
                continue;
            }
        }

        $reader->close();

        return $results;
    }

    public function searchAllSheets(string $query, int $context = 3): array
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open(Storage::path($this->path()));

        $queryLower = Str::lower($query);
        $results = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            $headersRaw = [];
            $headers = [];

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells = $row->getCells();

                if ($rowIndex === 1) {
                    $headersRaw = array_map(fn ($cell) => $this->getCellText($cell), $cells);
                    $headers = array_map(function ($cell) {
                        $formatted = $this->getCellText($cell);
                        return Str::of($formatted)->trim()->lower()->snake()->toString();
                    }, $cells);
                    continue;
                }

                if (! $headersRaw) {
                    $headersRaw = array_map(fn ($i) => "col_$i", range(0, count($cells) - 1));
                    $headers    = $headersRaw;
                }

                foreach ($cells as $i => $cell) {
                    $val = $this->getCellText($cell);

                    if ($val === null || $val === '') {
                        continue;
                    }

                    $valLower = Str::lower($val);

                    if (Str::contains($valLower, $queryLower)) {
                        $contextPairs = [];
                        for ($j = $i + 1; $j < count($cells) && count($contextPairs) < $context; $j++) {
                            $nextCell = $cells[$j] ?? null;
                            if (! $nextCell) {
                                continue;
                            }
                            $nextVal = $this->getCellText($nextCell);
                            if ($nextVal === null || $nextVal === '') {
                                continue;
                            }
                            $contextPairs[$headersRaw[$j] ?? "col_$j"] = $nextVal;
                        }

                        $results[] = [
                            'sheet'       => $sheet->getName(),
                            'match_value' => $val,
                            'context'     => $contextPairs,
                        ];
                    }
                }
            }
        }

        $reader->close();

        return $results;
    }

    public function getSheetRows(string $sheetName, ?array $filters = null, int $limit = 200, int $offset = 0): array
    {
        $filters = $filters ?? [];

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open(Storage::path($this->path()));

        $headers = [];
        $results = [];
        $total   = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() !== $sheetName) {
                continue;
            }

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells = $row->getCells();

                if ($rowIndex === 1) {
                    $headers = array_map(function ($cell) {
                        $formatted = $this->getCellText($cell);
                        return Str::of($formatted)->trim()->lower()->snake()->toString();
                    }, $cells);
                    continue;
                }

                if (! $headers) {
                    $headers = array_map(fn ($i) => "col_$i", range(0, count($cells) - 1));
                }

                $assoc = [];
                foreach ($cells as $i => $cell) {
                    $assoc[$headers[$i] ?? "col_$i"] = $this->getCellText($cell);
                }

                if (! $this->passFilters($assoc, $filters)) {
                    continue;
                }

                $total++;

                if ($total <= $offset) {
                    continue;
                }

                if (count($results) < $limit) {
                    // Normaliza tipos por nombre de columna (fecha / dinero)
                    $results[] = $this->castAssoc($assoc);
                } else {
                    break;
                }
            }
            break;
        }

        $reader->close();

        return [
            'headers' => $headers,
            'rows'    => $results,
            'offset'  => $offset,
            'limit'   => $limit,
            'total'   => $total,
        ];
    }

    /* =================== Helpers de extracción y casteo =================== */

    protected function getCellText(Cell $cell): string
    {
        $value  = $cell->getValue();
        $format = $cell->getStyle()->getFormat();

        if ($cell->isDate() && $value instanceof \DateTimeInterface) {
            // Intenta respetar formato de celda
            if (is_string($format) && $format !== '') {
                $phpFormat = strtr(strtolower($format), [
                    'yyyy' => 'Y',
                    'yy'   => 'y',
                    'mm'   => 'm',
                    'dd'   => 'd',
                ]);
                return $value->format($phpFormat);
            }
            return $value->format('Y-m-d');
        }

        if ($cell->isNumeric() && is_numeric($value)) {
            if (is_string($format) && $format !== '') {
                // Formato tipo 0000...
                if (preg_match('/^0+$/', $format)) {
                    return str_pad((string) $value, strlen($format), '0', STR_PAD_LEFT);
                }
                // Moneda
                if (str_contains($format, '$')) {
                    $decials = 0;
                    if (preg_match('/\.(0+)/', $format, $m)) {
                        $decials = strlen($m[1]);
                    }
                    return '$' . number_format((float) $value, $decials, '.', ',');
                }
            }
            return (string) $value; // si fuese serial y no está marcado como fecha, queda número
        }

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * Castea por nombre de campo: fechas -> Carbon, dinero -> float
     */
    protected function castAssoc(array $assoc): array
    {
        $out = [];
        foreach ($assoc as $key => $val) {
            if ($this->looksLikeDateField($key)) {
                $out[$key] = $this->parseDateFlexible($val);   // Carbon|null
            } elseif ($this->looksLikeMoneyField($key)) {
                $out[$key] = $this->parseMoney($val);          // float|null
            } else {
                $out[$key] = $val; // string u otros
            }
        }
        return $out;
    }

    protected function looksLikeDateField(string $name): bool
    {
        $name = mb_strtolower($name);
        return str_contains($name, 'fecha')
            || in_array($name, ['fecha_prestamo','fecha_pago','fecha','date','fecha_inicio','fecha_fin'], true);
    }

    protected function looksLikeMoneyField(string $name): bool
    {
        $name = mb_strtolower($name);
        return str_contains($name, 'monto')
            || str_contains($name, 'deuda')
            || str_contains($name, 'importe')
            || str_contains($name, 'saldo')
            || str_contains($name, 'abono')
            || str_contains($name, 'pago')
            || str_contains($name, 'inversion');
    }

    /**
     * Intenta parsear fechas:
     * - Serial Excel (número de días desde 1899-12-30).
     * - 'd/m/Y' (prioritario por tu caso).
     * - 'Y-m-d', 'd-m-Y', 'm/d/Y', etc.
     */
    protected function parseDateFlexible(?string $text): ?Carbon
    {
        if ($text === null || $text === '') return null;

        $txt = trim((string)$text);

        // 1) Serial numérico de Excel (rango razonable de días)
        if (is_numeric($txt)) {
            $days = (int)$txt;
            if ($days > 30000 && $days < 60000) {
                // 25569 días de 1899-12-30 a 1970-01-01
                $timestamp = ($days - 25569) * 86400;
                try {
                    return Carbon::createFromTimestampUTC($timestamp);
                } catch (\Throwable $e) {}
            }
        }

        // 2) Tu formato por defecto: d/m/Y
        try {
            $dt = Carbon::createFromFormat('d/m/Y', $txt);
            if ($dt !== false) return $dt;
        } catch (\Throwable $e) {}

        // 3) Otros formatos comunes
        $candidates = ['Y-m-d', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
        foreach ($candidates as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $txt);
                if ($dt !== false) return $dt;
            } catch (\Throwable $e) {}
        }

        // 4) Último recurso
        try {
            return Carbon::parse($txt);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseMoney($text): ?float
    {
        if ($text === null || $text === '') return null;
        if (is_numeric($text)) return (float) $text;

        // Limpia $, comas y espacios
        $clean = str_replace(['$', ',', ' '], '', (string) $text);
        return is_numeric($clean) ? (float) $clean : null;
    }

    /* =================== Filtros (igual que los tuyos) =================== */

    protected function passFilters(array $row, array $filters): bool
    {
        $q = $filters['q'] ?? null;
        if ($q) {
            $found = false;
            foreach ($row as $v) {
                if (is_scalar($v) && Str::contains(Str::lower((string) $v), Str::lower($q))) {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                return false;
            }
        }

        foreach ($filters as $key => $val) {
            if (in_array($key, ['q', 'date_from', 'date_to', 'monto_min', 'monto_max'], true)) {
                continue;
            }
            if ($val === null || $val === '') {
                continue;
            }
            if (! array_key_exists($key, $row)) {
                continue;
            }
            if (Str::lower((string) $row[$key]) !== Str::lower((string) $val)) {
                return false;
            }
        }

        $fechaKey = array_key_exists('fecha', $row) ? 'fecha' : (array_key_exists('date', $row) ? 'date' : null);
        if ($fechaKey) {
            $from = $filters['date_from'] ?? null;
            $to   = $filters['date_to'] ?? null;
            if ($from || $to) {
                $ts = strtotime((string) $row[$fechaKey]);
                if ($from && $ts < strtotime($from)) {
                    return false;
                }
                if ($to && $ts > strtotime($to)) {
                    return false;
                }
            }
        }

        $mKey = array_key_exists('inversion', $row) ? 'inversion' : (array_key_exists('monto', $row) ? 'monto' : null);
        if ($mKey) {
            $min = $filters['monto_min'] ?? null;
            $max = $filters['monto_max'] ?? null;
            $val = is_numeric($row[$mKey]) ? (float) $row[$mKey] : (float) str_replace([',', '$', ' '], '', (string) $row[$mKey]);

            if ($min !== null && $val < (float) $min) {
                return false;
            }
            if ($max !== null && $val > (float) $max) {
                return false;
            }
        }

        return true;
    }

    /* =================== Helpers privados extra =================== */

    /**
     * Busca el índice de la primera celda cuyo texto contenga alguno de los patrones,
     * comenzando en minIndex.
     */
    private function findIndexLike(array $lowerRow, array $needles, int $minIndex = 0): ?int
    {
        foreach ($lowerRow as $i => $txt) {
            if ($i < $minIndex) continue;
            foreach ($needles as $needle) {
                if ($txt !== '' && Str::contains($txt, Str::lower($needle))) {
                    return $i;
                }
            }
        }
        return null;
    }

    /**
     * Normaliza el valor de fecha a string 'Y-m-d' utilizando:
     * - Spout -> DateTime (si la celda es fecha),
     * - Serial Excel (numérico),
     * - Texto 'd/m/Y' u otros comunes.
     */
    private function normalizeDateValue(?Cell $cell, ?string $raw): ?string
    {
        // 1) Spout marcó como fecha real
        if ($cell && $cell->isDate()) {
            $v = $cell->getValue();
            if ($v instanceof \DateTimeInterface) {
                return $v->format('Y-m-d');
            }
        }

        if ($raw === null || $raw === '') {
            return null;
        }

        // 2) Serial Excel
        if (is_numeric($raw)) {
            $days = (int)$raw;
            if ($days > 30000 && $days < 60000) {
                $timestamp = ($days - 25569) * 86400;
                return gmdate('Y-m-d', $timestamp);
            }
        }

        // 3) Texto d/m/Y (tu caso)
        try {
            $dt = Carbon::createFromFormat('d/m/Y', trim($raw));
            return $dt->format('Y-m-d');
        } catch (\Throwable $e) {}

        // 4) Otros comunes
        foreach (['Y-m-d','d-m-Y','m/d/Y','Y/m/d'] as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, trim($raw));
                return $dt->format('Y-m-d');
            } catch (\Throwable $e2) {}
        }

        // 5) Parse libre
        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
