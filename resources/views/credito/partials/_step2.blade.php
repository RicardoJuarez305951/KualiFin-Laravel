<h3 class="text-lg font-semibold mb-4 text-gray-800">Paso 2: Información Personal del Cliente</h3>
<div class="space-y-6">
    <div>
        <label for="nombre_completo" class="block mb-2 text-sm font-medium text-gray-900">Nombre Completo</label>
        <input 
            type="text" 
            id="nombre_completo" 
            name="nombre_completo" 
            value="{{ old('nombre_completo', $solicitud['nombre_completo'] ?? $solicitud['cliente_info']['nombre'] ?? '') }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
        >
        @error('nombre_completo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Correo Electrónico</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            value="{{ old('email', $solicitud['email'] ?? '') }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
            placeholder="ejemplo@correo.com"
        >
        @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
