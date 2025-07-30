<div>
    {{-- Mensaje de éxito tras el submit --}}
    @if (session()->has('success'))
        <div class="mb-6 px-4 py-3 text-green-800 bg-green-100 border border-green-200 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-8 text-center">
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-200">
            <div class="flex items-center justify-center gap-6 mb-6">
                <div class="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center text-white text-2xl">📋</div>
                <div>
                    <h1 class="text-3xl font-bold text-blue-600 mb-2">Nueva Solicitud de Crédito</h1>
                    <p class="text-slate-600">Paso {{ $step }} de 5</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Paso 0: Inicial --}}
    @if($step === 0)
        <x-solicitud.inicial-form 
            :promoters="$promoters" 
            :clients-by-promoter="$clientsByPromoter" 
            :docTypes="$docTypes" 
            :clienteImages="$clienteImages" 
            :avalImages="$avalImages" 
        />
    @endif

    {{-- Paso 1: Domicilio --}}
    @if($step === 1)
        <x-solicitud.domicilio-form wire:model="domicilio" />
    @endif

    {{-- Paso 2: Ocupación --}}
    @if($step === 2)
        <x-solicitud.ocupacion-form wire:model="ocupacion" />
    @endif

    {{-- Paso 3: Familiar --}}
    @if($step === 3)
        <x-solicitud.familiar-form wire:model="infoFamiliar" />
    @endif

    {{-- Paso 4: Avales --}}
    @if($step === 4)
        <x-solicitud.avales-form wire:model="avales" />
    @endif

    {{-- Paso 5: Garantías --}}
    @if($step === 5)
        <x-solicitud.garantias-form wire:model="garantias" />
    @endif

    {{-- Controles de navegación --}}
    <div class="mt-6 flex justify-between">
        @if($step > 0)
            <button 
                wire:click="previousStep" 
                class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition"
            >
                ← Anterior
            </button>
        @else
            <span></span> {{-- para mantener el espacio --}}
        @endif

        @if($step < 5)
            <button 
                wire:click="nextStep" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                Continuar →
            </button>
        @else
            <button 
                wire:click="submit" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
            >
                Enviar Solicitud
            </button>
        @endif
    </div>
</div>
