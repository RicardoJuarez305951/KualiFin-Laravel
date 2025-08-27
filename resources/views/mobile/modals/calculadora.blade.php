{{-- Modal: Calculadora de Pago --}}
<div
    x-show="showCalc"
    x-cloak
    @keydown.escape.window="showCalc=false; mode=null; amount='';"
    class="fixed inset-0 z-10 flex items-center justify-center bg-black/50"
>
    <div
        class="bg-white rounded-2xl p-6 w-72"
        @click.away="showCalc = false; mode = null; amount = ''"
        x-transition
    >
        <h3 class="text-lg font-bold mb-4" x-text="client"></h3>

        <template x-if="mode === null">
            <div class="space-y-3">
                <button @click="setMode('full')" class="w-full py-2 bg-green-600 text-white rounded">Completo</button>
                <button @click="setMode('deferred')" class="w-full py-2 bg-yellow-500 text-white rounded">Diferido</button>
            </div>
        </template>

        <template x-if="mode === 'deferred'">
            <div class="space-y-4">
                <div class="text-right text-2xl font-semibold" x-text="amount"></div>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="n in [1,2,3,4,5,6,7,8,9]" :key="n">
                        <button @click="addDigit(n)" x-text="n" class="py-2 bg-gray-100 rounded"></button>
                    </template>
                    <button @click="delDigit()" class="py-2 bg-gray-100 rounded">Borrar</button>
                    <button @click="addDigit(0)" class="py-2 bg-gray-100 rounded">0</button>
                    <button @click="accept()" class="py-2 bg-blue-600 text-white rounded">Aceptar</button>
                </div>
            </div>
        </template>
    </div>
</div>