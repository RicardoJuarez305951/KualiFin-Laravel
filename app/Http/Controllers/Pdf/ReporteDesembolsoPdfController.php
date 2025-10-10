<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Concerns\HandlesSupervisorContext;
use App\Http\Controllers\Controller;
use App\Models\Promotor;
use App\Services\Pdf\ReporteDesembolsoPdfService;
use App\Services\Reportes\ReporteDesembolsoDataService;
use App\Support\RoleHierarchy;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReporteDesembolsoPdfController extends Controller
{
    use HandlesSupervisorContext;

    public function __construct(
        private readonly ReporteDesembolsoDataService $dataService,
        private readonly ReporteDesembolsoPdfService $pdfService,
    ) {
    }

    public function __invoke(Request $request, Promotor $promotor)
    {
        $supervisor = $this->resolveSupervisorContext($request);
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());

        $this->ensurePromotorBelongsToContext($supervisor, $promotor, $primaryRole);

        [$start, $end] = $this->resolveRange($request);
        $acceptedCredits = $this->parseAcceptedCredits($request);
        $payload = $this->dataService->build($promotor, $start, $end, $acceptedCredits);
        $pdf = $this->pdfService->generate($payload);

        $promotorNombre = data_get($payload, 'contexto.promotor.nombre')
            ?: data_get($payload, 'base.promotorNombre')
            ?: $promotor->nombre
            ?: 'promotor';

        $slug = Str::slug($promotorNombre) ?: 'promotor';
        $suffix = $start->format('Ymd') . '_' . $end->format('Ymd');
        $fileName = "reporte_desembolso_{$slug}_{$suffix}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function resolveRange(Request $request): array
    {
        $start = $this->parseDate($request->query('inicio'));
        $end = $this->parseDate($request->query('fin'));

        if (!$start || !$end) {
            $today = CarbonImmutable::now()->endOfDay();
            $defaultStart = $today->previous(CarbonImmutable::SATURDAY)->startOfDay();

            $start ??= $defaultStart;
            $end ??= $today;
        }

        if ($end->lt($start)) {
            [$start, $end] = [$end->startOfDay(), $start->endOfDay()];
        }

        return [$start->startOfDay(), $end->endOfDay()];
    }

    private function parseDate(?string $value): ?CarbonImmutable
    {
        if (!$value) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return int[]
     */
    private function parseAcceptedCredits(Request $request): array
    {
        $raw = $request->query('aceptados');

        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            $values = $raw;
        } else {
            $values = explode(',', (string) $raw);
        }

        return collect($values)
            ->map(function ($value) {
                if (is_numeric($value)) {
                    return (int) $value;
                }

                if (is_string($value)) {
                    $numeric = (int) trim($value);
                    return $numeric > 0 ? $numeric : null;
                }

                return null;
            })
            ->filter(fn ($value) => $value && $value > 0)
            ->unique()
            ->values()
            ->all();
    }
}
