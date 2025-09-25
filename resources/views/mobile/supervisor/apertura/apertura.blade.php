{{-- resources/views/mobile/supervisor/apertura/apertura.blade.php --}}
<x-layouts.mobile.mobile-layout title="Alta de Promotor">
@php
    $formContext = $formData ?? [];

    $nombreCompleto = trim(collect([
        data_get($formContext, 'nombre'),
        data_get($formContext, 'apellido_p'),
        data_get($formContext, 'apellido_m'),
    ])->filter()->join(' '));

    $formValues = [
        'nombre' => old('nombre', $nombreCompleto ?: data_get($formContext, 'nombre', '')),
        'domicilio' => old('domicilio', data_get($formContext, 'domicilio', '')),
        'telefono' => old('telefono', data_get($formContext, 'telefono', '')),
        'correo' => old('correo', data_get($formContext, 'correo', data_get($formContext, 'email', ''))),
        'diasPago' => old('dias_pago', data_get($formContext, 'dias_pago', data_get($formContext, 'diasPago', data_get($formContext, 'dias_de_pago', '')))),
    ];
@endphp
    <div
        x-data="{
            nombre: @js($formValues['nombre']),
            domicilio: @js($formValues['domicilio']),
            telefono: @js($formValues['telefono']),
            correo: @js($formValues['correo']),
            diasPago: @js($formValues['diasPago']),
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
                this.diasPago = '';
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
                    name="nombre"
                    value="{{ $formValues['nombre'] }}"
                    x-model="nombre"
                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Nombre y apellidos"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Domicilio</label>
                <input
                    type="text"
                    name="domicilio"
                    value="{{ $formValues['domicilio'] }}"
                    x-model="domicilio"
                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Calle, número, colonia, ciudad"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input
                    type="text"
                    name="telefono"
                    value="{{ $formValues['telefono'] }}"
                    x-model="telefono"
                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ej. 5551234567"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input
                    type="email"
                    name="correo"
                    value="{{ $formValues['correo'] }}"
                    x-model="correo"
                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="persona@dominio.com"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Días de pago</label>
                <input
                    type="text"
                    name="dias_pago"
                    x-model="diasPago"
                    placeholder="Ej. lunes, miércoles"
                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
                <p class="mt-1 text-xs text-gray-500">Indica los días habituales de cobro separados por comas.</p>
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

