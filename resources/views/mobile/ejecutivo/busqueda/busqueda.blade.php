<x-layouts.mobile.mobile-layout title="Búsqueda">
    @include('mobile.supervisor.busqueda.partials.form-results', [
        'role' => $role ?? 'ejecutivo',
        'query' => $query ?? '',
        'resultados' => $resultados ?? collect(),
        'puedeBuscar' => $puedeBuscar ?? false,
        'supervisores' => $supervisores ?? collect(),
        'supervisorContextQuery' => $supervisorContextQuery ?? [],
    ])
</x-layouts.mobile.mobile-layout>
