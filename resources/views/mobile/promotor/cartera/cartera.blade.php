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
    <div
        x-data="{
            // Vencida detail
            vencidaDetail: {
                nombre_cliente: '',
                direccion_cliente: '',
                telefono_cliente: '',
                nombre_aval: '',
                direccion_aval: '',
                telefono_aval: '',
                promotora: '',
                supervisora: '',
                monto_deuda: '',
                fecha_prestamo: '',
            },
            showVencidaDetail: false,

            // Inactiva detail
            showInactivaDetail: null,

            openInactivaDetail(c) {
                this.showInactivaDetail = {
                    client: {
                        nombre: c.nombre,
                        apellido: c.apellido,
                        direccion: c.direccion,
                        telefono: c.telefono,
                    },
                    aval: {
                        nombre: c.aval_nombre,
                        direccion: c.aval_direccion,
                        telefono: c.aval_telefono,
                    },
                    fecha_ultimo_credito: c.fecha_ultimo_credito,
                };
            },

            openVencidaDetail(c) {
                this.vencidaDetail = {
                    nombre_cliente: `${c['apellido'] ?? c.apellido ?? ''} ${c['nombre'] ?? c.nombre ?? ''}`.trim(),
                    direccion_cliente: c['direccion'] ?? c.direccion ?? '',
                    telefono_cliente: c['telefono'] ?? c.telefono ?? '',
                    nombre_aval: c['aval_nombre'] ?? c.aval_nombre ?? '',
                    direccion_aval: c['aval_direccion'] ?? c.aval_direccion ?? '',
                    telefono_aval: c['aval_telefono'] ?? c.aval_telefono ?? '',
                    promotora: c['promotora'] ?? c.promotora ?? '',
                    supervisora: c['supervisora'] ?? c.supervisora ?? '',
                    monto_deuda: c['monto_deuda'] ?? c.monto_deuda ?? '',
                    fecha_prestamo: c['fecha_prestamo'] ?? c.fecha_prestamo ?? '',
                };
                this.showVencidaDetail = true;
            }
        }"
        class="bg-white rounded-2xl shadow p-4 w-full max-w-lg mx-auto"
    >
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

        {{-- Modal: Detalle Cartera Vencida (estructura 4 grids) --}}
        <div
            x-show="showVencidaDetail"
            x-cloak
            @keydown.escape.window="showVencidaDetail=false"
            class="fixed inset-0 z-10 flex items-center justify-center bg-black/50"
        >
            <div class="bg-white rounded-2xl p-6 w-[22rem] sm:w-[26rem]" @click.away="showVencidaDetail=false" x-transition>
                <h3 class="text-lg font-bold mb-4" x-text="vencidaDetail.nombre_cliente"></h3>

                {{-- Grid fila 1: Cliente (11) | Aval (12) --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- 11: Datos de cliente --}}
                    <div class="space-y-1">
                        <p class="text-xs text-gray-500">Nombre cliente</p>
                        <p class="font-medium" x-text="vencidaDetail.nombre_cliente"></p>

                        <p class="text-xs text-gray-500 mt-2">Dirección</p>
                        <p class="break-words" x-text="vencidaDetail.direccion_cliente"></p>

                        <p class="text-xs text-gray-500 mt-2">Teléfono</p>
                        <p x-text="vencidaDetail.telefono_cliente"></p>
                    </div>

                    {{-- 12: Datos de aval --}}
                    <div class="space-y-1">
                        <p class="text-xs text-gray-500">Nombre aval</p>
                        <p class="font-medium" x-text="vencidaDetail.nombre_aval"></p>

                        <p class="text-xs text-gray-500 mt-2">Dir. aval</p>
                        <p class="break-words" x-text="vencidaDetail.direccion_aval"></p>

                        <p class="text-xs text-gray-500 mt-2">Tel. aval</p>
                        <p x-text="vencidaDetail.telefono_aval"></p>
                    </div>
                </div>

                {{-- Divisor --}}
                <div class="my-4 border-t border-gray-200"></div>

                {{-- Grid fila 2: Promotora + Deuda (21) | Supervisora + Fecha (22) --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- 21: Promotora y deuda --}}
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">Promotora</p>
                            <p class="font-medium" x-text="vencidaDetail.promotora"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Monto deuda</p>
                            <p class="font-semibold text-red-600"
                            x-text="new Intl.NumberFormat('es-MX',{style:'currency', currency:'MXN'})
                                        .format(Number(vencidaDetail.monto_deuda || 0))"></p>
                        </div>
                    </div>

                    {{-- 22: Supervisora y fecha --}}
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">Supervisora</p>
                            <p class="font-medium" x-text="vencidaDetail.supervisora"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Fecha préstamo</p>
                            <p class="font-medium" x-text="vencidaDetail.fecha_prestamo"></p>
                        </div>
                    </div>
                </div>

                <button class="w-full mt-5 py-2 bg-blue-600 text-white rounded-md"
                        @click="showVencidaDetail=false">
                    Cerrar
                </button>
            </div>
        </div>


        {{-- Modal: Detalle Cartera Inactiva --}}
        <div
            x-show="showInactivaDetail"
            x-cloak
            @keydown.escape.window="showInactivaDetail=null"
            class="fixed inset-0 z-10 flex items-center justify-center bg-black/50"
        >
            <div class="bg-white rounded-2xl p-6 w-80" @click.away="showInactivaDetail = null" x-transition>
                <div class="mb-4">
                    <h3 class="text-lg font-bold">Cliente</h3>
                    <p class="font-semibold" x-text="showInactivaDetail.client.apellido + ' ' + showInactivaDetail.client.nombre"></p>
                    <p x-text="showInactivaDetail.client.direccion"></p>
                    <p x-text="showInactivaDetail.client.telefono"></p>
                </div>
                <div class="mb-4">
                    <h3 class="text-lg font-bold">Aval</h3>
                    <p class="font-semibold" x-text="showInactivaDetail.aval.nombre"></p>
                    <p x-text="showInactivaDetail.aval.direccion"></p>
                    <p x-text="showInactivaDetail.aval.telefono"></p>
                </div>
                <p class="mb-4"><span class="font-semibold">Fecha último crédito:</span> <span x-text="showInactivaDetail.fecha_ultimo_credito"></span></p>
                <button @click="showInactivaDetail = null" class="w-full py-2 bg-blue-600 text-white rounded">Cerrar</button>
            </div>
        </div>
    </div>
</x-layouts.mobile.mobile-layout>
