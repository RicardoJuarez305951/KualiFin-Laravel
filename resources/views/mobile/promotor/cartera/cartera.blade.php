{{-- resources/views/mobile/promotor/cartera/cartera.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    $activos = collect(range(1, 5))->map(fn($i) => [
        'nombre' => $faker->firstName(),
        'apellido' => $faker->lastName(),
        'semana_credito' => $faker->numberBetween(1, 12),
        'monto_semanal' => $faker->randomFloat(2, 100, 1000),
    ]);

    $vencidos = collect(range(1, 3))->map(function ($i) use ($faker) {
        $monto = $faker->randomFloat(2, 100, 1000);

        return [
            'nombre' => $faker->firstName(),
            'apellido' => $faker->lastName(),
            'direccion' => $faker->address(),
            'telefono' => $faker->phoneNumber(),
            'aval_nombre' => $faker->name(),
            'aval_direccion' => $faker->address(),
            'aval_telefono' => $faker->phoneNumber(),
            'promotora' => $faker->name(),
            'supervisora' => $faker->name(),
            'monto_deuda' => $monto,
            'deuda_total' => $monto,
            'fecha_prestamo' => $faker->date('Y-m-d'),
        ];
    });

    $inactivos = collect(range(1, 2))->map(fn($i) => [
        'nombre' => $faker->firstName(),
        'apellido' => $faker->lastName(),
        'direccion' => $faker->address(),
        'telefono' => $faker->phoneNumber(),
        'aval_nombre' => $faker->name(),
        'aval_direccion' => $faker->address(),
        'aval_telefono' => $faker->phoneNumber(),
        'fecha_ultimo_credito' => $faker->date('Y-m-d'),
    ]);
@endphp

<x-layouts.mobile.mobile-layout title="Tu Cartera">
    <div class="bg-white rounded-2xl shadow p-4 w-full max-w-lg mx-auto">
        <h2 class="text-center text-2xl font-bold text-gray-800 mb-6">Tu Cartera</h2>

        <div class="space-y-6">
            <section>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">Cartera Activa</h3>
                @include('mobile.promotor.cartera.activa')
            </section>

            <section>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">Cartera Vencida</h3>
                @include('mobile.promotor.cartera.vencida')
            </section>

            <section>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">Cartera Inactiva</h3>
                @include('mobile.promotor.cartera.inactiva')
            </section>
        </div>
        <div class="mt-8">
            <a href="{{ route('mobile.' . ($role ?? 'promotor') . '.index') }}"
               class="block w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3">
                Regresar
            </a>
        </div>

        @include('mobile.modals.calculadora')
        @include('mobile.modals.detalle')
    </div>
</x-layouts.mobile.mobile-layout>
