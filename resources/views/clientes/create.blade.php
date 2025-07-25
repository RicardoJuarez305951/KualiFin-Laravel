<x-layouts.authenticated>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-6">Nuevo Cliente</h1>
        
        <form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data" class="max-w-2xl">
            @csrf
            <div class="space-y-4">
                <!-- Datos personales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" name="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                        <input type="text" name="apellido_p" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
                
                <!-- ... más campos del formulario ... -->
                
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Guardar Cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.authenticated>