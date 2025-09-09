<?php

return [
    // Fuente oficial del Excel
    'source_type' => env('EXCEL_SOURCE_TYPE', 'local'),

    // Ruta local donde SIEMPRE leerá Laravel
    'local_path'  => env('EXCEL_LOCAL_PATH', storage_path('app/private/finanzas.xlsx')),

    // URL remota (Google Drive "export=download")
    'remote_url'  => env('EXCEL_REMOTE_URL', ''),

    // 0 = sin caché (leer directo del archivo)
    'cache_ttl'   => (int) env('EXCEL_CACHE_TTL', 0),
];
