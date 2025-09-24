{{-- resources/views/mobile/supervisor/Apertura/apertura.blade.php --}}
<x-layouts.mobile.mobile-layout title="Alta de Promotor">
@php
    $faker = \Faker\Factory::create('es_MX');
    $diasPagoEjemplo = [
        'dia' => $faker->randomElement(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']),
        'hora' => $faker->randomElement(['08:00', '09:30', '11:00', '13:30', '16:00']),
    ];
@endphp
    <div
        x-data="{
            nombre: '',
            domicilio: '',
            telefono: '',
            correo: '',
            diaPago: @json($diasPagoEjemplo['dia']),
            horaPago: @json($diasPagoEjemplo['hora']),
            ineUploaded: false,
            compUploaded: false,
            submit() {
                if (!this.nombre.trim()) {
                    alert('Falta el campo Nombre completo');
                    return;
                }
                if (!this.domicilio.trim()) {
                    alert('Falta el campo Domicilio');
                    return;
                }
                if (!this.correo.trim()) {
                    alert('Falta el campo Correo electrónico');
                    return;
                }
                if (!this.diaPago.trim()) {
                    alert('Falta seleccionar el día de pago');
                    return;
                }
                if (!this.horaPago) {
                    alert('Falta capturar la hora de pago');
                    return;
                }
                if (!this.ineUploaded) {
                    alert('Falta subir el archivo INE');
                    return;
                }
                if (!this.compUploaded) {
                    alert('Falta subir el Comprobante Domicilio');
                    return;
                }
                alert('Se ha añadido un nuevo promotor');
                this.$refs.form.reset();
                this.nombre = '';
                this.domicilio = '';
                this.telefono = '';
                this.correo = '';
                this.diaPago = '';
                this.horaPago = '';
                this.ineUploaded = false;
                this.compUploaded = false;
            }
        }"
        class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md space-y-6"
    >
        <h1 class="text-xl font-bold text-gray-900 text-center">Alta de Promotor</h1>

        <form class="space-y-4" x-ref="form" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
                <input
                    type="text"
                    value="{{ $faker->name() }}"
                    x-model="nombre"
                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Domicilio</label>
                <input
                    type="text"
                    value="{{ $faker->streetAddress() . ', ' . $faker->city() }}"
                    x-model="domicilio"
                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input
                    type="text"
                    value="{{ $faker->phoneNumber() }}"
                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input
                    type="email"
                    value="{{ $faker->safeEmail() }}"
                    x-model="correo"
                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Día de pago</label>
                    <select
                        x-model="diaPago"
                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Selecciona un día</option>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Elige el día habitual para realizar los cobros.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Hora de pago</label>
                    <input
                        type="time"
                        x-model="horaPago"
                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">Registra la hora (formato 24 hrs) en la que inicia la ruta.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">INE</label>
                    <input
                        type="file"
                        accept="image/*,application/pdf"
                        class="hidden"
                        x-ref="ine"
                        @change="ineUploaded = true"
                    />
                    <button
                        type="button"
                        @click="$refs.ine.click()"
                        :class="ineUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                        class="w-full rounded-md py-2 font-medium transition"
                    >
                        <span x-text="ineUploaded ? '✔ INE cargado' : 'Subir INE'"></span>
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Comprobante Domicilio</label>
                    <input
                        type="file"
                        accept="image/*,application/pdf"
                        class="hidden"
                        x-ref="comp"
                        @change="compUploaded = true"
                    />
                    <button
                        type="button"
                        @click="$refs.comp.click()"
                        :class="compUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                        class="w-full rounded-md py-2 font-medium transition"
                    >
                        <span x-text="compUploaded ? '✔ Comprobante cargado' : 'Subir Comprobante'"></span>
                    </button>
                </div>
            </div>
        </form>

        <div class="space-y-3 pt-4">
            <button class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg shadow-sm">
                Subir Documentos
            </button>
            <button class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg shadow-sm">
                Registro de firmas
            </button>
        </div>

        <div class="flex gap-4 pt-4">
            <a
                href="{{ route('mobile.supervisor.index') }}"
                class="flex-1 text-center bg-gray-300 text-gray-700 font-semibold py-3 rounded-lg hover:bg-gray-400"
            >
                Regresar
            </a>
            <button
                class="flex-1 bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg shadow-sm"
                @click="submit"
            >
                Confirmar
            </button>
        </div>
    </div>
</x-layouts.mobile.mobile-layout>

