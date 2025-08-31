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
    <div class="max-w-md mx-auto space-y-4">
        <input type="text" placeholder="Buscar cliente" class="w-full border rounded-xl px-3 py-2"/>
        <div class="space-y-3">
            @foreach($clientes as $c)
                <div x-data="{ open: false }" class="bg-white rounded-lg shadow border">
                    <div class="flex items-center justify-between p-3">
                        <button @click="open = !open" class="text-left font-semibold flex-1">{{ $c['nombre'] }}</button>
                        <a href="#" class="ml-3 px-2 py-1 text-xs font-bold text-white bg-blue-600 rounded">D</a>
                    </div>
                    <div x-show="open" x-collapse class="px-3 pb-3 text-sm">
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                            <p class="col-span-2"><span class="font-semibold">Domicilio:</span> {{ $c['domicilio'] }}</p>
                            <p><span class="font-semibold">Promotor:</span> {{ $c['promotor'] }}</p>
                            <p><span class="font-semibold">Tipo de crédito:</span> {{ ucfirst($c['tipo']) }}</p>
                            <p><span class="font-semibold">Crédito:</span> {{ formatCurrency($c['monto']) }}</p>
                            <p><span class="font-semibold">Creado:</span> {{ $c['fecha'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.mobile.mobile-layout>
