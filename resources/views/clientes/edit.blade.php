<x-layouts.authenticated title="Editar cliente">
    @php
        $cliente = $cliente ?? [];
        $promotores = $promotores ?? [];
    @endphp

    <div class="mx-auto max-w-3xl py-8 space-y-8">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-gray-900">Editar cliente</h1>
            <p class="text-gray-600">Formulario ilustrativo sin integracion a base de datos.</p>
        </div>

        <form action="#" method="POST" class="bg-white border rounded-lg shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                    <input type="text" name="nombre" value="{{ $cliente['nombre'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CURP</label>
                    <input type="text" name="curp" value="{{ $cliente['curp'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="tel" name="telefono" value="{{ $cliente['telefono'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Promotor asignado</label>
                    <select name="promotor_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecciona una opcion</option>
                        @foreach ($promotores as $promotor)
                            <option value="{{ $promotor['id'] }}"
                                @if (($cliente['promotor_id'] ?? null) === $promotor['id']) selected @endif>
                                {{ $promotor['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado del cliente</label>
                    <select name="estado" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        @foreach (['Activo', 'Revision', 'Vencido'] as $estado)
                            <option value="{{ $estado }}" @if (($cliente['estado'] ?? '') === $estado) selected @endif>
                                {{ $estado }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monto maximo</label>
                    <input type="number" name="monto_maximo" value="{{ $cliente['monto_maximo'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('administrativo.clientes.index') }}" class="inline-flex items-center rounded-md border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</x-layouts.authenticated>
