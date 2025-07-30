@props(['promoters', 'clientsByPromoter', 'docTypes', 'clienteImages', 'avalImages'])

<div class="space-y-6">
    <!-- Promotora -->
    <section class="bg-white rounded-xl shadow-md border p-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center text-white text-xl">👩‍💼</div>
            <h2 class="text-xl font-bold text-blue-600">Selección de Promotora</h2>
        </div>
        <select wire:model="initialData.promotora"
                class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            <option value="">-- Seleccione una promotora --</option>
            @foreach($promoters as $p)
                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
            @endforeach
        </select>
    </section>

    <!-- Cliente -->
    @if($initialData['promotora'])
    <section class="bg-white rounded-xl shadow-md border p-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center text-white text-xl">👤</div>
            <h2 class="text-xl font-bold text-purple-600">Selección de Cliente</h2>
        </div>
        <select wire:model="initialData.cliente"
                class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200">
            <option value="">-- Seleccione un cliente --</option>
            @foreach($clientsByPromoter[$initialData['promotora']] as $name)
                <option value="{{ $name }}">{{ $name }}</option>
            @endforeach
        </select>
    </section>
    @endif

    <!-- Documentos del Cliente -->
    @if($initialData['cliente'])
    <section class="bg-white rounded-xl shadow-md border p-6">
        <h3 class="text-lg font-bold text-green-600 mb-4">Documentos de {{ $initialData['cliente'] }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($docTypes as $key)
            <div class="rounded-xl border p-4 text-center">
                <img src="{{ $clienteImages[$key] }}" class="w-full h-32 object-cover rounded mb-3 cursor-pointer" />
                <p class="font-medium">{{ strtoupper($key) }}</p>
                <div class="flex justify-center gap-2 mt-2">
                    <button wire:click="setClienteValidation('{{ $key }}','accepted')" class="px-3 py-1 bg-green-500 text-white rounded">✅</button>
                    <button wire:click="setClienteValidation('{{ $key }}','rejected')" class="px-3 py-1 bg-red-500 text-white rounded">❌</button>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Documentos del Aval -->
    <section class="bg-white rounded-xl shadow-md border p-6">
        <h3 class="text-lg font-bold text-indigo-600 mb-4">Documentos del Aval</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($docTypes as $key)
            <div class="rounded-xl border p-4 text-center">
                <img src="{{ $avalImages[$key] }}" class="w-full h-32 object-cover rounded mb-3 cursor-pointer" />
                <p class="font-medium">{{ strtoupper($key) }}</p>
                <div class="flex justify-center gap-2 mt-2">
                    <button wire:click="setAvalValidation('{{ $key }}','accepted')" class="px-3 py-1 bg-green-500 text-white rounded">✅</button>
                    <button wire:click="setAvalValidation('{{ $key }}','rejected')" class="px-3 py-1 bg-red-500 text-white rounded">❌</button>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
