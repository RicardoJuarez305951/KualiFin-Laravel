<x-layouts.mobile.mobile-layout title="Búsqueda">
    <div class="min-h-screen">
        <div class="mx-auto w-full max-w-md space-y-8 px-5 py-10">
            @include('mobile.supervisor.busqueda.partials.form-results', [
                'role' => $role ?? 'supervisor',
                'query' => $query ?? '',
                'resultados' => $resultados ?? collect(),
                'puedeBuscar' => $puedeBuscar ?? false,
                'supervisores' => $supervisores ?? collect(),
                'supervisorContextQuery' => $supervisorContextQuery ?? [],
            ])
        </div>
    </div>
</x-layouts.mobile.mobile-layout>
