{{-- resources/views/mobile/MisDesembolsos.blade.php --}}
<x-layouts.mobile.mobile-layout title="Mis Desembolsos">
    @php
        $user = auth()->user();
        $promotor = $user?->promotor;
        $promotorId = $promotor?->id;

        $formatCurrency = static fn ($value): string => '$' . number_format(
            is_numeric($value) ? (float) $value : 0.0,
            2,
            '.',
            ','
        );
        $formatDate = static function (?string $value): ?string {
            if (!$value) {
                return null;
            }

            try {
                return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
            } catch (\Throwable) {
                return $value;
            }
        };
        $formatRange = static function (?string $inicio, ?string $fin) use ($formatDate): ?string {
            $inicioTexto = $formatDate($inicio);
            $finTexto = $formatDate($fin);

            if (!$inicioTexto && !$finTexto) {
                return null;
            }

            if (!$inicioTexto) {
                return $finTexto;
            }

            if (!$finTexto) {
                return $inicioTexto;
            }

            return $inicioTexto === $finTexto
                ? $inicioTexto
                : "{$inicioTexto} - {$finTexto}";
        };

        $pdfRouteCandidates = [
            'mobile.ejecutivo.desembolso.pdf',
            'mobile.ejecutivo.venta.desembolso.pdf',
            'mobile.supervisor.venta.recibo_desembolso.pdf',
        ];

        $resolvedPdfRoute = null;
        foreach ($pdfRouteCandidates as $candidate) {
            if (\Illuminate\Support\Facades\Route::has($candidate)) {
                $resolvedPdfRoute = $candidate;
                break;
            }
        }

        $blankPdfDataUri = 'data:application/pdf;base64,JVBERi0xLjQKMSAwIG9iago8PCAvVHlwZSAvQ2F0YWxvZyAvUGFnZXMgMiAwIFIgPj4KZW5kb2JqCjIgMCBvYmoKPDwgL1R5cGUgL1BhZ2VzIC9LaWRzIFszIDAgUl0gL0NvdW50IDEgPj4KZW5kb2JqCjMgMCBvYmoKPDwgL1R5cGUgL1BhZ2UgL1BhcmVudCAyIDAgUiAvTWVkaWFCb3ggWzAgMCA1OTUgODQyXSAvQ29udGVudHMgNCAwIFIgPj4KZW5kb2JqCjQgMCBvYmoKPDwgL0xlbmd0aCAwID4+CnN0cmVhbQplbmRzdHJlYW0KZW5kb2JqCnhyZWYKMCA1CjAwMDAwMDAwMDAgNjU1MzUgZiAKMDAwMDAwMDAxMCAwMDAwMCBuIAowMDAwMDAwMDUzIDAwMDAwIG4gCjAwMDAwMDAxMDIgMDAwMDAgbiAKMDAwMDAwMDE3MyAwMDAwMCBuIAp0cmFpbGVyCjw8IC9TaXplIDUgL1Jvb3QgMSAwIFIgPj4Kc3RhcnR4cmVmCjIzMwolJUVPRg==';
        $defaultPdfUrl = $blankPdfDataUri;
        $canGeneratePdf = $promotorId && $resolvedPdfRoute;

        $demoRows = [
            ['semana' => 'Semana 34', 'inicio' => '2024-08-19', 'fin' => '2024-08-26', 'monto' => 12800.00, 'estatus' => 'Liberado'],
            ['semana' => 'Semana 33', 'inicio' => '2024-08-12', 'fin' => '2024-08-19', 'monto' => 11950.00, 'estatus' => 'Enviado'],
            ['semana' => 'Semana 32', 'inicio' => '2024-08-05', 'fin' => '2024-08-12', 'monto' => 13400.00, 'estatus' => 'Liberado'],
            ['semana' => 'Semana 31', 'inicio' => '2024-07-29', 'fin' => '2024-08-05', 'monto' => 10250.00, 'estatus' => 'Liberado'],
            ['semana' => 'Semana 30', 'inicio' => '2024-07-22', 'fin' => '2024-07-29', 'monto' => 9875.00, 'estatus' => 'Liberado'],
            ['semana' => 'Semana 29', 'inicio' => '2024-07-15', 'fin' => '2024-07-22', 'monto' => 14100.00, 'estatus' => 'Liberado'],
            ['semana' => 'Semana 28', 'inicio' => '2024-07-08', 'fin' => '2024-07-15', 'monto' => 8600.00, 'estatus' => 'Liberado'],
            ['semana' => 'Semana 27', 'inicio' => '2024-07-01', 'fin' => '2024-07-08', 'monto' => 9950.00, 'estatus' => 'Liberado'],
            ['semana' => 'Semana 26', 'inicio' => '2024-06-24', 'fin' => '2024-07-01', 'monto' => 12300.00, 'estatus' => 'Liberado'],
            ['semana' => 'Semana 25', 'inicio' => '2024-06-17', 'fin' => '2024-06-24', 'monto' => 10700.00, 'estatus' => 'Liberado'],
        ];

        $desembolsosDemo = collect($demoRows)->map(function (array $row) use (
            $formatCurrency,
            $formatDate,
            $formatRange,
            $canGeneratePdf,
            $resolvedPdfRoute,
            $promotorId,
            $defaultPdfUrl
        ): array {
            $inicio = $row['inicio'] ?? null;
            $fin = $row['fin'] ?? null;

            $pdfParams = [];
            if ($canGeneratePdf && $promotorId) {
                $pdfParams['promotor'] = $promotorId;
                if ($inicio) {
                    $pdfParams['inicio'] = $inicio;
                }
                if ($fin) {
                    $pdfParams['fin'] = $fin;
                }
            }

            return [
                'semana' => $row['semana'] ?? 'Semana',
                'fecha_display' => $formatDate($fin),
                'range_display' => $formatRange($inicio, $fin),
                'estatus' => $row['estatus'] ?? 'Pendiente',
                'monto_display' => $formatCurrency($row['monto'] ?? 0),
                'pdf_url' => $canGeneratePdf && $promotorId && $resolvedPdfRoute
                    ? route($resolvedPdfRoute, $pdfParams)
                    : $defaultPdfUrl,
                'pdf_label' => $canGeneratePdf ? 'Descargar PDF' : 'Descargar PDF vacio',
                'download' => $canGeneratePdf ? null : 'desembolso-vacio.pdf',
            ];
        })->all();
    @endphp

    <div class="bg-gray-200 rounded-2xl shadow-lg p-6 w-full max-w-md space-y-6">
        <header class="text-center space-y-1">
            <h1 class="text-2xl font-bold text-gray-900 uppercase">Mis Desembolsos</h1>
            <p class="text-gray-600 text-sm">Lista cronologica compacta por semana</p>
        </header>

        @unless ($canGeneratePdf)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-3 text-xs text-amber-700">
                Genera PDF reales iniciando sesion con un perfil autorizado y promotora asignada. Mientras tanto, puedes descargar un PDF vacio para mostrar al cliente.
            </div>
        @endunless

        <section class="bg-white rounded-2xl shadow-inner p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Registro semanal</h2>
                <span class="text-xs uppercase tracking-wide text-gray-500">Cronologico</span>
            </div>
            <ul class="divide-y divide-gray-200">
                @foreach ($desembolsosDemo as $desembolso)
                    <li class="py-3 flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $desembolso['semana'] }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $desembolso['fecha_display'] ?? 'Fecha no disponible' }} - {{ $desembolso['estatus'] }}
                            </p>
                            @if ($desembolso['range_display'])
                                <p class="text-[11px] text-gray-400">{{ $desembolso['range_display'] }}</p>
                            @endif
                        </div>
                        <div class="text-right space-y-1">
                            <span class="block text-sm font-semibold text-gray-900">{{ $desembolso['monto_display'] }}</span>
                            <a href="{{ $desembolso['pdf_url'] }}"
                               class="inline-flex items-center text-xs font-medium text-blue-700 hover:text-blue-900"
                               target="_blank" rel="noopener"
                               @if(!empty($desembolso['download'])) download="{{ $desembolso['download'] }}" @endif>
                                {{ $desembolso['pdf_label'] }}
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layouts.mobile.mobile-layout>
