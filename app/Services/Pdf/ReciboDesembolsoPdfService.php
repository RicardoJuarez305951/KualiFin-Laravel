<?php

namespace App\Services\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Contracts\View\Factory as ViewFactory;

class ReciboDesembolsoPdfService
{
    public function __construct(
        private readonly ViewFactory $viewFactory,
    ) {
    }

    /**
     * Genera el PDF y devuelve la instancia lista para descarga.
     *
     * @param  array<string, mixed>  $payload
     */
    public function generate(array $payload): DomPdf
    {
        $html = $this->viewFactory
            ->make('pdf.recibos.recibo_desembolso', $payload)
            ->render();

        $paper = config('pdf.paper', 'letter');
        $orientation = config('pdf.orientation', 'landscape');

        return Pdf::loadHTML($html)
            ->setPaper($paper, $orientation);
    }
}
