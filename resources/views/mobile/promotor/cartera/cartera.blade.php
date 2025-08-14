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
    <div x-data="{
            showCalc: false,
            mode: null,
            amount: '',
            client: '',
            openCalc(name) {
                this.client = name;
                this.amount = '';
                this.mode = null;
                this.showCalc = true;
            },
            setMode(m) {
                this.mode = m;
                if (m === 'full') this.accept();
            },
            addDigit(d) {
                this.amount += d;
            },
            delDigit() {
                this.amount = this.amount.slice(0, -1);
            },
            accept() {
                if (this.mode === 'deferred') {
                    console.log('Anticipo de', this.amount, 'para', this.client);
                } else {
                    console.log('Pago completo para', this.client);
                }
                this.showCalc = false;
                this.mode = null;
                this.amount = '';
            }
        }" class="bg-white rounded-2xl shadow p-4 w-full max-w-lg mx-auto">
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
            <a href="{{ route("mobile.$role.index") }}"
               class="block w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3">
                Regresar
            </a>
        </div>

        <div x-show="showCalc" x-cloak class="fixed inset-0 z-10 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl p-6 w-72" @click.away="showCalc = false; mode = null; amount = ''">
                <h3 class="text-lg font-bold mb-4" x-text="client"></h3>

                <template x-if="mode === null">
                    <div class="space-y-3">
                        <button @click="setMode('full')" class="w-full py-2 bg-green-600 text-white rounded">Completo</button>
                        <button @click="setMode('deferred')" class="w-full py-2 bg-yellow-500 text-white rounded">Diferido</button>
                    </div>
                </template>

                <template x-if="mode === 'deferred'">
                    <div class="space-y-4">
                        <div class="text-right text-2xl font-semibold" x-text="amount"></div>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="n in [1,2,3,4,5,6,7,8,9]" :key="n">
                                <button @click="addDigit(n)" x-text="n" class="py-2 bg-gray-100 rounded"></button>
                            </template>
                            <button @click="delDigit()" class="py-2 bg-gray-100 rounded">Borrar</button>
                            <button @click="addDigit(0)" class="py-2 bg-gray-100 rounded">0</button>
                            <button @click="accept()" class="py-2 bg-blue-600 text-white rounded">Aceptar</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-layouts.mobile.mobile-layout>
