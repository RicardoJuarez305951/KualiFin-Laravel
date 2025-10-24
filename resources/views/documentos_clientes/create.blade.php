<x-layouts.authenticated title="Registrar documento">
    @php
        $clientes = $clientes ?? [];
        $creditos = $creditos ?? [];
        $tipos = $tipos ?? [];
    @endphp

    <div class="mx-auto max-w-3xl py-8 space-y-8">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-gray-900">Registrar documento</h1>
            <p class="text-gray-600">Formulario de ejemplo para adjuntar documentos de clientes.</p>
        </div>

        <form action="#" method="POST" class="bg-white border rounded-lg shadow-sm p-6 space-y-6">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <select name="cliente_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecciona un cliente</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente['id'] }}">{{ $cliente['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Credito</label>
                    <select name="credito_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecciona un credito</option>
                        @foreach ($creditos as $credito)
                            <option value="{{ $credito['id'] }}">{{ $credito['folio'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento</label>
                    <select name="tipo_doc" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecciona una opcion</option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL del documento</label>
                    <input type="url" name="url_s3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="https://">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del archivo</label>
                    <input type="text" name="nombre_arch" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="documento.pdf">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('administrativo.documentos.index') }}" class="inline-flex items-center rounded-md border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Guardar borrador
                </button>
            </div>
        </form>
    </div>
</x-layouts.authenticated>
