<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Concerns\HandlesSupervisorContext;
use App\Http\Controllers\Controller;
use App\Models\Promotor;
use App\Services\Pdf\ReciboDesembolsoPdfService;
use App\Services\Recibos\ReciboDesembolsoDataService;
use App\Support\RoleHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReciboDesembolsoPdfController extends Controller
{
    use HandlesSupervisorContext;

    public function __construct(
        private readonly ReciboDesembolsoDataService $dataService,
        private readonly ReciboDesembolsoPdfService $pdfService,
    ) {
    }

    public function __invoke(Request $request, Promotor $promotor)
    {
        $supervisor = $this->resolveSupervisorContext($request);
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());

        $this->ensurePromotorBelongsToContext($supervisor, $promotor, $primaryRole);

        $payload = $this->dataService->build($promotor);
        $pdf = $this->pdfService->generate($payload);

        $baseName = $payload['promotorNombre'] ?? $promotor->nombre ?? 'promotor';
        $slug = Str::slug($baseName) ?: 'promotor';
        $fecha = now()->format('Ymd');
        $fileName = "recibo_desembolso_{$slug}_{$fecha}.pdf";

        return $pdf->download($fileName);
    }
}
