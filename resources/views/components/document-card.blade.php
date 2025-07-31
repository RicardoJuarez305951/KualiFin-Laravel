@props([
    'type',
    'title',
    'imageUrl'
])

<div 
    x-data="{ showModal: false }"
    class="bg-gray-50 rounded-lg p-4 border transition-all" 
    :class="{
        'border-green-400 ring-2 ring-green-200': docStatus['{{ $type }}'] === 'aprobado', 
        'border-red-400 ring-2 ring-red-200': docStatus['{{ $type }}'] === 'denegado',
        'border-gray-200': docStatus['{{ $type }}'] !== 'aprobado' && docStatus['{{ $type }}'] !== 'denegado'
    }"
>
    <h4 class="font-medium text-sm text-gray-700 mb-2">{{ $title }}</h4>

    <!-- Estado: Pendiente o Aprobado -->
    <div x-show="docStatus['{{ $type }}'] !== 'denegado'">
        <div class="aspect-video bg-gray-200 rounded-md mb-3 flex items-center justify-center">
            <button type="button" @click="showModal = true" class="w-full h-full cursor-pointer">
                <img :src="{{ $imageUrl }}" alt="Vista previa del documento" class="object-contain h-full w-full rounded-md">
            </button>
        </div>
        
        <input type="hidden" name="{{ $type }}_status" x-model="docStatus['{{ $type }}']">
        
        <div class="flex items-center justify-center gap-4">
            <button type="button" @click="docStatus['{{ $type }}'] = 'aprobado'" class="p-2 rounded-full transition" :class="{'bg-green-100 text-green-600 ring-2 ring-green-500': docStatus['{{ $type }}'] === 'aprobado', 'text-gray-400 hover:bg-green-100': docStatus['{{ $type }}'] !== 'aprobado'}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </button>
            <button type="button" @click="docStatus['{{ $type }}'] = 'denegado'" class="p-2 rounded-full transition" :class="{'bg-red-100 text-red-600 ring-2 ring-red-500': docStatus['{{ $type }}'] === 'denegado', 'text-gray-400 hover:bg-red-100': docStatus['{{ $type }}'] !== 'denegado'}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <!-- Estado: Denegado -->
    <div x-show="docStatus['{{ $type }}'] === 'denegado'" x-transition class="text-center py-8">
        <p class="font-semibold text-red-600">Documento Rechazado</p>
        <p class="text-sm text-gray-600 mb-4">Se requiere una nueva imagen.</p>
        <label class="cursor-pointer inline-block text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5">
            Retomar Foto
            <input type="file" class="hidden" @change="docStatus['{{ $type }}'] = ''">
        </label>
    </div>

    <!-- Modal para ver imagen en grande -->
    <div 
        x-show="showModal" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75"
        @keydown.escape.window="showModal = false"
        style="display: none;"
    >
        <div @click.away="showModal = false" class="relative">
            <img :src="{{ $imageUrl }}" class="block max-h-[90vh] max-w-[90vw] rounded-lg shadow-lg">
            <button @click="showModal = false" class="absolute -top-3 -right-3 h-8 w-8 bg-white rounded-full flex items-center justify-center text-gray-800 hover:bg-gray-200 shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
</div>
