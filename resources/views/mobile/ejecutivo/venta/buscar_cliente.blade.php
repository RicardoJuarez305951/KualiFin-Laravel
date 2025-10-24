@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    // Clientes falsos para búsqueda
    $clientes = collect(range(1, 5))->map(fn () => [
        'nombre'    => $faker->name(),
        'domicilio' => $faker->streetAddress() . ', ' . $faker->city(),
        'promotor'  => $faker->name(),
        'tipo'      => $faker->randomElement(['activo', 'en falla', 'finalizado']),
        'monto'     => $faker->randomFloat(2, 3000, 20000),
        'fecha'     => $faker->date('d/m/Y'),
    ]);

    function formatCurrency($v) {
        return '$' . number_format($v, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Buscar Cliente">
    <div class="mx-auto w-full max-w-md space-y-6 px-5 py-10">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <input type="text"
                   placeholder="Buscar cliente"
                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-inner focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-200"/>
        </div>

        <div class="space-y-4">
            @foreach($clientes as $c)
                <div x-data="{ open: false }" class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <button @click="open = !open"
                                type="button"
                                class="flex-1 text-left text-sm font-semibold text-slate-900">
                            {{ $c['nombre'] }}
                        </button>
                        <a href="#"
                           class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm transition hover:bg-blue-500">
                            D
                        </a>
                    </div>
                    <div x-show="open" x-collapse class="px-4 pb-4 text-sm text-slate-700">
                        <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                            <p class="col-span-2">
                                <span class="font-semibold text-slate-900">Domicilio:</span> {{ $c['domicilio'] }}
                            </p>
                            <p><span class="font-semibold text-slate-900">Promotor:</span> {{ $c['promotor'] }}</p>
                            <p><span class="font-semibold text-slate-900">Tipo de crédito:</span> {{ ucfirst($c['tipo']) }}</p>
                            <p><span class="font-semibold text-slate-900">Crédito:</span> {{ formatCurrency($c['monto']) }}</p>
                            <p><span class="font-semibold text-slate-900">Creado:</span> {{ $c['fecha'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.mobile.mobile-layout>
