{{-- Modal: Detalle genérico --}}
<div
    x-data="{ formatKey(k){ return k.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase()); } }"
    x-show="$store.details.show"
    x-cloak
    @keydown.escape.window="$store.details.close()"
    class="fixed inset-0 z-10 flex items-center justify-center bg-black/50"
>
    <div class="bg-white rounded-2xl p-6 w-80 max-h-[80vh] overflow-y-auto" @click.away="$store.details.close()" x-transition>
        <h3 class="text-lg font-bold mb-4"
            x-text="$store.details.data.apellido ? `${$store.details.data.apellido} ${$store.details.data.nombre}` : ($store.details.data.nombre || 'Detalle')"></h3>
        <div class="space-y-1 text-sm">
            <template x-for="[key, value] of Object.entries($store.details.data).filter(([k]) => !['nombre','apellido'].includes(k))" :key="key">
                <p>
                    <span class="font-semibold" x-text="formatKey(key) + ':'"></span>
                    <span x-text="value"></span>
                </p>
            </template>
        </div>
        <button class="w-full mt-5 py-2 bg-blue-600 text-white rounded-md" @click="$store.details.close()">Cerrar</button>
    </div>
</div>
