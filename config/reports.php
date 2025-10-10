<?php

return [
    'desembolso' => [
        'fondo_ahorro_rate' => (float) env('REPORTE_DESEMBOLSO_FONDO_RATE', 0.10),
        'pdf' => [
            'paper' => env('REPORTE_DESEMBOLSO_PDF_PAPER', 'letter'),
            'orientation' => env('REPORTE_DESEMBOLSO_PDF_ORIENTATION', 'portrait'),
        ],
    ],
];
