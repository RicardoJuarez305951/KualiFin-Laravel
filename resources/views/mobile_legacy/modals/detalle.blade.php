{{-- Modal: Detalle --}}
<div
    x-show="$store.details.show"
    x-cloak
    @keydown.escape.window="$store.details.close()"
    class="fixed inset-0 z-10 flex items-center justify-center bg-black/50"
>
    <div
        class="bg-white rounded-2xl p-6 w-72"
        @click.away="$store.details.close()"
        x-transition
    >
        <h3 class="text-lg font-bold mb-4" x-text="$store.details.data.header"></h3>
        <div class="mb-4" x-text="$store.details.data.body"></div>
        <button @click="$store.details.close()" class="w-full py-2 bg-blue-600 text-white rounded">Cerrar</button>
    </div>
</div>

