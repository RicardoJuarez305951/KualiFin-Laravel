<x-layouts.authenticated title="Nuevo cliente">
    @php
        $promotores = $promotores ?? [];
    @endphp

    <div class="mx-auto max-w-3xl py-8 space-y-8">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-gray-900">Registrar nuevo cliente</h1>
            <p class="text-gray-600">Captura basica sin validaciones de backend.</p>
        </div>

        <form action="#" method="POST" class="bg-white border rounded-lg shadow-sm p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                    <input type="text" name="nombre" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. Maria Elena Rodriguez">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CURP</label>
                    <input type="text" name="curp" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 uppercase" placeholder="RODM850324MDFRNN06">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="tel" name="telefono" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="55 1234 5678">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Promotor asignado</label>
                    <select name="promotor_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecciona una opcion</option>
                        @foreach ($promotores as $promotor)
                            <option value="{{ $promotor['id'] }}">{{ $promotor['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado del cliente</label>
                    <select name="estado" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="Activo">Activo</option>
                        <option value="Revision">Revision</option>
                        <option value="Vencido">Vencido</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monto maximo</label>
                    <input type="number" name="monto_maximo" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="45000">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('administrativo.clientes.index') }}" class="inline-flex items-center rounded-md border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Guardar borrador
                </button>
            </div>
        </form>
    </div>
</x-layouts.authenticated>
