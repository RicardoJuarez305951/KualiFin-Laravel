<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">Resumen</h2>
        </div>
        <div class="space-y-3">
            {!! $statRow('Supervisor:', $nombre_supervisor) !!}

            {!! $statRow('Cartera Activa:', null, '
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">'.money_mx($cartera_activa).'</span>'.
                    $pillLink(route('mobile.'.$role.'.cartera_activa'), 'D').'
                </div>
            ') !!}

            {!! $statRow('Falla Actual:', null, '
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">
                        '.$porcentaje_fallo.'%
                    </span>
                    <span class="text-sm font-semibold text-slate-900">'.money_mx($cartera_falla).'</span>'.
                    $pillLink(route('mobile.'.$role.'.cartera_falla'), 'D').'
                </div>
            ') !!}

            {!! $statRow('Cartera Vencida:', null, '
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">'.money_mx($cartera_vencida).'</span>'.
                    $pillLink(route('mobile.'.$role.'.cartera_vencida'), 'D').'
                </div>
            ') !!}

            {!! $statRow('Cartera Inactiva:', null, '
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">'.$cartera_inactivaP.'%</span>'.
                    $pillLink(route('mobile.'.$role.'.cartera_inactiva'), 'D').'
                </div>
            ') !!}
        </div>
    </div>
</section>
