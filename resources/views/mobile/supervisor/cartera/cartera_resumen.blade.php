<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow space-y-4">
    <h2 class="text-2xl font-bold text-slate-900 leading-tight">Resumen</h2>

    <div class="space-y-3">
        {!! $statRow('Supervisor:', $nombre_supervisor) !!}

        {!! $statRow('Cartera Activa:', null, '
        <span class="inline-flex items-center gap-2">
            <span class="text-sm font-semibold text-blue-900">'.money_mx($cartera_activa).'</span>'.
            $pillLink(route('mobile.'.$role.'.cartera_activa', array_merge($supervisorContextQuery ?? [], [])), 'D').'
        </span>
        ') !!}

        {!! $statRow('Falla Actual:', null, '
        <span class="inline-flex items-center gap-2">
            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">'.$porcentaje_fallo.'%</span>
            <span class="text-sm font-semibold text-slate-900">'.money_mx($cartera_falla).'</span>'.
            $pillLink(route('mobile.'.$role.'.cartera_falla', array_merge($supervisorContextQuery ?? [], [])), 'D').'
        </span>
        ') !!}

        {!! $statRow('Cartera Vencida:', null, '
        <span class="inline-flex items-center gap-2">
            <span class="text-sm font-semibold text-rose-700">'.money_mx($cartera_vencida).'</span>'.
            $pillLink(route('mobile.'.$role.'.cartera_vencida', array_merge($supervisorContextQuery ?? [], [])), 'D').'
        </span>
        ') !!}

        {!! $statRow('Cartera Inactiva:', null, '
        <span class="inline-flex items-center gap-2">
            <span class="text-sm font-semibold text-slate-900">'.$cartera_inactivaP.'%</span>'.
            $pillLink(route('mobile.'.$role.'.cartera_inactiva', array_merge($supervisorContextQuery ?? [], [])), 'D').'
        </span>
        ') !!}
    </div>
</section>
