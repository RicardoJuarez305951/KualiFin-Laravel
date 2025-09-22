<section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
    <div class="p-5 space-y-4">
    <h2 class="text-base font-bold text-gray-900">Resumen</h2>

    <div class="space-y-3">
        {!! $statRow('Supervisor:', $nombre_supervisor) !!}

        {!! $statRow('Cartera Activa:', null, '
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-900">'.money_mx($cartera_activa).'</span>'.
            $pillLink(route('mobile.'.$role.'.cartera_activa', array_merge($supervisorContextQuery ?? [], [])), 'D').'
        </div>
        ') !!}

        {!! $statRow('Falla Actual:', null, '
        <div class="flex items-center gap-2">
            <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 ring-1 ring-yellow-200">
            '.$porcentaje_fallo.'%
            </span>
            <span class="text-sm font-semibold text-gray-900">'.money_mx($cartera_falla).'</span>'.
            $pillLink(route('mobile.'.$role.'.cartera_falla', array_merge($supervisorContextQuery ?? [], [])), 'D').'
        </div>
        ') !!}

        {!! $statRow('Cartera Vencida:', null, '
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-900">'.money_mx($cartera_vencida).'</span>'.
            $pillLink(route('mobile.'.$role.'.cartera_vencida', array_merge($supervisorContextQuery ?? [], [])), 'D').'
        </div>
        ') !!}

        {!! $statRow('Cartera Inactiva:', null, '
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-900">'.$cartera_inactivaP.'%</span>'.
            $pillLink(route('mobile.'.$role.'.cartera_inactiva', array_merge($supervisorContextQuery ?? [], [])), 'D').'
        </div>
        ') !!}
    </div>
    </div>
</section>