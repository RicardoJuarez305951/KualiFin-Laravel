@php
    $links = [
        'autorizaciones' => ['label' => 'Autorizaciones', 'route' => 'administrativo.autorizaciones'],
        'parametros' => ['label' => 'Parametros del sistema', 'route' => 'administrativo.parametros'],
        'asignaciones' => ['label' => 'Asignaciones y jerarquias', 'route' => 'administrativo.asignaciones'],
        'cartera_global' => ['label' => 'Cartera global', 'route' => 'administrativo.cartera_global'],
        'ventas_desembolsos' => ['label' => 'Ventas y desembolsos', 'route' => 'administrativo.ventas_desembolsos'],
        'desembolsos_inversion' => ['label' => 'Desembolsos inversion', 'route' => 'administrativo.desembolsos_inversion'],
        'cierre_semanal' => ['label' => 'Cierre semanal', 'route' => 'administrativo.cierre_semanal'],
        'inversiones' => ['label' => 'Gestion de inversiones', 'route' => 'administrativo.inversiones'],
        'auditoria_seguridad' => ['label' => 'Auditoria y seguridad', 'route' => 'administrativo.auditoria_seguridad'],
    ];

    $titles = [
        'autorizaciones' => 'Autorizaciones operativas',
        'parametros' => 'Parametros del sistema',
        'asignaciones' => 'Asignaciones y jerarquias',
        'cartera_global' => 'Cartera global',
        'ventas_desembolsos' => 'Ventas y desembolsos',
        'desembolsos_inversion' => 'Desembolsos para inversion',
        'cierre_semanal' => 'Cierre semanal de ventas',
        'inversiones' => 'Gestion de inversiones',
        'auditoria_seguridad' => 'Auditoria y seguridad financiera',
    ];

    $active = $activeSection ?? 'parametros';
    $pageTitle = $titles[$active] ?? 'Panel Administrativo';
@endphp

<x-layouts.authenticated :title="$pageTitle">

    <div class="mx-auto max-w-7xl py-10 space-y-8">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <div class="flex flex-wrap gap-2">
                @foreach ($links as $key => $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $active === $key ? 'bg-blue-600 text-white shadow' : 'bg-gray-50 text-gray-700 hover:bg-gray-100' }}"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div>
            @if (isset($sections[$active]))
                @includeIf("administrativo.partials.$active", ['data' => $sections[$active]])
            @else
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-gray-600">
                    Seccion no disponible.
                </div>
            @endif
        </div>
    </div>
</x-layouts.authenticated>
