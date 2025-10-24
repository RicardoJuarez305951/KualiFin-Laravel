<x-layouts.authenticated title="Editar documento">
    @php
        $documento = $documento ?? [];
        $clientes = $clientes ?? [];
        $creditos = $creditos ?? [];
        $tipos = $tipos ?? [];
    @endphp

    <div class="mx-auto max-w-3xl py-8 space-y-8">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-gray-900">Editar documento</h1>
            <p class="text-gray-600">Solo maqueta de la pantalla, sin guardar en base de datos.</p>
        </div>

        <form action="#" method="POST" class="bg-white border rounded-lg shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <select name="cliente_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente['id'] }}"
                                @if (($documento['cliente_id'] ?? null) === $cliente['id']) selected @endif>
                                {{ $cliente['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Credito</label>
                    <select name="credito_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($creditos as $credito)
                            <option value="{{ $credito['id'] }}"
                                @if (($documento['credito_id'] ?? null) === $credito['id']) selected @endif>
                                {{ $credito['folio'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento</label>
                    <select name="tipo_doc" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo }}" @if (($documento['tipo'] ?? '') === $tipo) selected @endif>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                    <input type="url" name="url_s3" value="{{ $documento['url'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estatus</label>
                    <select name="estatus" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        @foreach (['Validado', 'Pendiente', 'Observaciones'] as $estatus)
                            <option value="{{ $estatus }}" @if (($documento['estatus'] ?? '') === $estatus) selected @endif>
                                {{ $estatus }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('administrativo.documentos.index') }}" class="inline-flex items-center rounded-md border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</x-layouts.authenticated>
