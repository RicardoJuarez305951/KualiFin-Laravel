{{-- resources/views/mobile/promotor/venta/modal_enviar_clientes.blade.php --}}
<div
    x-show="showModal"
    x-cloak
    @keydown.escape.window="showModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
>
    <div
        @click.away="showModal = false"
        class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-6 text-center"
        x-transition
    >
        {{-- Contenido dinámico del modal --}}
        <div class="space-y-4">
            {{-- Icono --}}
            <div
                class="w-16 h-16 rounded-full mx-auto flex items-center justify-center"
                :class="{
                    'bg-green-100 text-green-600': modalSuccess,
                    'bg-red-100 text-red-600': !modalSuccess
                }"
            >
                {{-- Icono de Éxito (Check) --}}
                <svg x-show="modalSuccess" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{-- Icono de Error (X) --}}
                <svg x-show="!modalSuccess" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>

            {{-- Mensaje --}}
            <h2 class="text-xl font-bold" x-text="modalMessage"></h2>

            {{-- Botón Continuar --}}
            <button
                @click="showModal = false"
                class="w-full px-4 py-3 font-semibold text-white rounded-xl transition-transform transform hover:scale-105"
                :class="{
                    'bg-green-600 hover:bg-green-700': modalSuccess,
                    'bg-red-600 hover:bg-red-700': !modalSuccess
                }"
            >
                Continuar
            </button>
        </div>
    </div>
</div>