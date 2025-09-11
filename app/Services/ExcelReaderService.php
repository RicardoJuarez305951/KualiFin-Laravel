<?php

namespace App\Services;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ExcelReaderService
{
    public function path(): string
    {
        // Prioriza EXCEL_STORAGE_PATH si existe; si no, usa config('excel.local_path')
        $raw = env('EXCEL_STORAGE_PATH', config('excel.local_path', 'excel/origen.xlsx'));

        return sanitizeStoragePath($raw);
    }

    /**
     * Descarga el Excel desde EXCEL_SOURCE_URL y lo guarda en storage/app/{EXCEL_STORAGE_PATH}
     * Loggea usando el canal 'excel'. Regresa ['ok'=>bool, 'path'=>..., 'size'=>...|'message'=>...]
     */
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
            Log::channel('excel')->error('Error descargando Excel', [
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * (Opcional) Solo descarga si no existe o si está “viejo”
     */
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
                $cells = $row->toArray();

                if ($rowIndex === 1) {
                    $headersRaw = array_map(fn ($h) => $this->normalizeCellValue($h) ?? '', $cells);
                    $headers = array_map(function ($h) {
                        $formatted = $this->normalizeCellValue($h) ?? '';
                        return Str::of($formatted)->trim()->lower()->snake()->toString();
                    }, $cells);

                    continue;
                }

                if (! $headersRaw) {
                    $headersRaw = array_map(fn ($i) => "col_$i", range(0, count($cells) - 1));
                    $headers = $headersRaw;
                }

                foreach ($cells as $i => $value) {
                    $val = $this->normalizeCellValue($value);

                    if ($val === null || $val === '') {
                        continue;
                    }

                    $valLower = Str::lower($val);

                    if (Str::contains($valLower, $queryLower)) {
                        $contextPairs = [];
                        for ($j = $i + 1; $j < count($cells) && count($contextPairs) < $context; $j++) {
                            $nextVal = $this->normalizeCellValue($cells[$j]);
                            if ($nextVal === null || $nextVal === '') {
                                continue;
                            }
                            $contextPairs[$headersRaw[$j] ?? "col_$j"] = $nextVal;
                        }

                        $results[] = [
                            'sheet' => $sheet->getName(),
                            'match_value' => $val,
                            'context' => $contextPairs,
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
        $total = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() !== $sheetName) {
                continue;
            }

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells = $row->toArray();

                if ($rowIndex === 1) {
                    $headers = array_map(function ($h) {
                        $formatted = $this->normalizeCellValue($h) ?? '';
                        return Str::of($formatted)->trim()->lower()->snake()->toString();
                    }, $cells);

                    continue;
                }

                if (! $headers) {
                    $headers = array_map(fn ($i) => "col_$i", range(0, count($cells) - 1));
                }

                $assoc = [];
                foreach ($cells as $i => $value) {
                    $assoc[$headers[$i] ?? "col_$i"] = $this->normalizeCellValue($value) ?? '';
                }

                if (! $this->passFilters($assoc, $filters)) {
                    continue;
                }

                $total++;

                if ($total <= $offset) {
                    continue;
                }
                if (count($results) < $limit) {
                    $results[] = $assoc;
                } else {
                    break;
                }
            }
            break;
        }

        $reader->close();

        return [
            'headers' => $headers,
            'rows' => $results,
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
        ];
    }

    protected function normalizeCellValue(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return null;
    }

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
            $to = $filters['date_to'] ?? null;
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
}
