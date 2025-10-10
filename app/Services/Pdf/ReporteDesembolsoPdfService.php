<?php

namespace App\Services\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Contracts\View\Factory as ViewFactory;

class ReporteDesembolsoPdfService
{
    public function __construct(
        private readonly ViewFactory $viewFactory,
    ) {
    }

    /**
     * Genera el PDF del reporte de desembolso.
     *
     * @param  array<string, mixed>  $payload
     */
    public function generate(array $payload): DomPdf
    {
        $html = $this->viewFactory
            ->make('pdf.reportes.reporte_desembolso', $payload)
            ->render();

        $paper = config('reports.desembolso.pdf.paper', 'letter');
        $orientation = config('reports.desembolso.pdf.orientation', 'portrait');

        return Pdf::loadHTML($html)->setPaper($paper, $orientation);
    }
}
