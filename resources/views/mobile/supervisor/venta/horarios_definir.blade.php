<x-layouts.mobile.mobile-layout title="Definir horario">
    @php
        $diasSemana = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
        $seleccionActual = old('dias_de_pago', $diasPago ?? '');
    @endphp

    <div class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6 space-y-5">
        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="bg-white rounded-2xl shadow ring-1 ring-gray-900/5 px-4 py-4 space-y-2">
            <h1 class="text-base font-bold text-gray-900">Definir horario de cobro</h1>
            <div class="text-sm text-gray-700 space-y-1">
                <p>
                    <span class="font-semibold">Promotor:</span>
                    {{ trim(($promotor->nombre ?? '') . ' ' . ($promotor->apellido_p ?? '') . ' ' . ($promotor->apellido_m ?? '')) ?: 'Sin nombre' }}
                </p>
                <p>
                    <span class="font-semibold">Supervisor:</span>
                    {{ $supervisorNombre }}
                </p>
                <p>
                    <span class="font-semibold">Horario actual:</span>
                    {{ $diasPago !== '' ? $diasPago : 'Sin horario definido' }}
                </p>
            </div>
        </section>

        <form
            method="POST"
            action="{{ route('mobile.supervisor.horarios.actualizar', array_merge($supervisorContextQuery ?? [], ['promotor' => $promotor->id])) }}"
            class="space-y-4"
        >
            @csrf
            @method('PUT')

            <section class="bg-white rounded-2xl shadow ring-1 ring-gray-900/5 px-4 py-4 space-y-3">
                <div class="space-y-2">
                    <label for="dias_de_pago" class="text-sm font-semibold text-gray-800">Día de pago</label>

                    <select
                        id="dias_de_pago"
                        name="dias_de_pago"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    >
                        <option value="" @selected($seleccionActual === '' )>Selecciona un día</option>
                        @foreach($diasSemana as $dia)
                            <option value="{{ $dia }}" @selected(Str::lower($seleccionActual) === Str::lower($dia))>
                                {{ $dia }}
                            </option>
                        @endforeach
                    </select>

                    <p class="text-xs text-gray-500">Elige exactamente un día de la semana.</p>

                    @error('dias_de_pago')
                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <div class="flex items-center justify-between gap-3">
                <a
                    href="{{ route('mobile.supervisor.horarios', array_merge($supervisorContextQuery ?? [], [])) }}"
                    class="inline-flex flex-1 items-center justify-center rounded-2xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-100"
                >Cancelar</a>
                <button
                    type="submit"
                    class="inline-flex flex-1 items-center justify-center rounded-2xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
                >Guardar cambios</button>
            </div>
        </form>
    </div>
</x-layouts.mobile.mobile-layout>
