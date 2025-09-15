<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Ejercicio;
use App\Models\Promotor;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SupervisorController extends Controller
{
    /*
     * -----------------------------------------------------------------
     * Métodos administrativos
     * -----------------------------------------------------------------
     */

    public function adminIndex()
    {
        $supers = Supervisor::all();
        return view('supervisores.index', compact('supers'));
    }

    public function create()
    {
        return view('supervisores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'ejecutivo_id'=> 'required|exists:ejecutivos,id',
            'nombre'      => 'required|string',
            'apellido_p'  => 'required|string',
            'apellido_m'  => 'nullable|string',
        ]);

        Supervisor::create($data);
        return redirect()->route('supervisores.index');
    }

    public function show(Supervisor $supervisor)
    {
        return view('supervisores.show', compact('supervisor'));
    }

    public function edit(Supervisor $supervisor)
    {
        return view('supervisores.edit', compact('supervisor'));
    }

    public function update(Request $request, Supervisor $supervisor)
    {
        $data = $request->validate([
            'nombre'     => 'required|string',
            'apellido_p' => 'required|string',
            'apellido_m' => 'nullable|string',
        ]);

        $supervisor->update($data);
        return redirect()->route('supervisores.index');
    }

    public function destroy(Supervisor $supervisor)
    {
        $supervisor->delete();
        return redirect()->route('supervisores.index');
    }
    /*
     * -----------------------------------------------------------------
     * Métodos para vista mobile
     * -----------------------------------------------------------------
     */

    public function index()
    {
        return view('mobile.index');
    }

    public function objetivo()
    {
        return view('mobile.supervisor.objetivo.objetivo');
    }

    public function venta()
    {
        $clientesProspectados   = Cliente::count();
        $clientesPorSupervisar  = Cliente::where('estatus', 'inactivo')->count();

        $ejercicio        = Ejercicio::latest('fecha_inicio')->first();
        $moneyWeeklyNow   = $ejercicio?->dinero_autorizado ?? 0;
        $moneyWeeklyTarget= $ejercicio?->venta_objetivo ?? 0;
        $fechaLimite      = $ejercicio?->fecha_final?->format('d/m/Y');

        $moneyProgress = $moneyWeeklyTarget > 0
            ? min(100, ($moneyWeeklyNow / $moneyWeeklyTarget) * 100)
            : 0;

        $promotoresSupervisados = Promotor::with('clientes')
            ->get()
            ->map(function ($p) {
                $debe  = (float) $p->venta_maxima;
                $falla = max(0, $debe - (float) $p->venta_proyectada_objetivo);

                return [
                    'nombre'          => trim($p->nombre . ' ' . $p->apellido_p),
                    'debe'            => $debe,
                    'falla'           => $falla,
                    'porcentajeFalla' => $debe > 0 ? ($falla / $debe) * 100 : 0,
                    'ventaRegistrada' => (float) $p->venta_proyectada_objetivo,
                    'prospectados'    => $p->clientes->pluck('nombre'),
                    'porSupervisar'   => $p->clientes
                        ->where('estatus', 'inactivo')
                        ->pluck('nombre'),
                ];
            });

        return view('mobile.supervisor.venta.venta', compact(
            'clientesProspectados',
            'clientesPorSupervisar',
            'moneyWeeklyNow',
            'moneyWeeklyTarget',
            'fechaLimite',
            'moneyProgress',
            'promotoresSupervisados'
        ));
    }

    public function solicitar_venta()
    {
        return view('mobile.supervisor.venta.solicitar_venta');
    }

    public function ingresar_cliente()
    {
        return view('mobile.supervisor.venta.ingresar_cliente');
    }

    public function clientes_prospectados()
    {
        return view('mobile.supervisor.venta.clientes_prospectados');
    }

    public function clientes_supervisados()
    {
        return view('mobile.supervisor.venta.clientes_supervisados');
    }

    public function cartera()
    {
        $user = auth()->user();

        // Busca el perfil de supervisor por user_id
        $supervisor = Supervisor::firstWhere('user_id', $user->id);

        // Si no hay perfil de supervisor, devuelve colección vacía
        $promotores = $supervisor
            ? Promotor::where('supervisor_id', $supervisor->id)
                ->select('id', 'nombre', 'apellido_p', 'apellido_m')
                ->orderBy('nombre')
                ->get()
            : collect();

        return view('mobile.supervisor.cartera.cartera', compact('promotores'));
    }

    public function carteraPromotor(Promotor $promotor)
    {
        $user = auth()->user();
        $supervisor = Supervisor::firstWhere('user_id', $user->id);

        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');
        abort_unless($promotor->supervisor_id === $supervisor->id, 403);

        $clientes = Cliente::where('promotor_id', $promotor->id)
            ->with('credito')
            ->get();

        return view('mobile.supervisor.cartera.promotor', compact('promotor', 'clientes'));
    }

    public function reporte()
    {
        return view('mobile.supervisor.cartera.reporte');
    }

    public function cliente_historial(Cliente $cliente)
    {
        $user = auth()->user();
        $supervisor = Supervisor::firstWhere('user_id', $user?->id);

        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');

        $cliente->load([
            'promotor.supervisor',
            'credito.pagosProyectados.pagosReales',
            'credito.garantias',
            'credito.avales.documentos',
            'credito.datoContacto',
            'documentos',
        ]);

        abort_unless(optional($cliente->promotor)->supervisor_id === $supervisor->id, 403);

        $credito = $cliente->credito;

        abort_unless($credito, 404, 'El cliente no cuenta con crédito activo.');

        $totalWeeks = $credito->pagosProyectados->count();
        $fechaCredito = $credito->fecha_inicio ? Carbon::parse($credito->fecha_inicio) : null;

        $currentWeek = 0;
        if ($totalWeeks > 0 && $fechaCredito) {
            $currentWeek = min(now()->diffInWeeks($fechaCredito) + 1, $totalWeeks);
        }

        $semanas = $credito->pagosProyectados
            ->sortBy('semana')
            ->map(function ($pago) {
                $fechaLimite = Carbon::parse($pago->fecha_limite);
                $primerPago = $pago->pagosReales->sortBy('fecha_pago')->first();

                if ($primerPago) {
                    $fechaPago = Carbon::parse($primerPago->fecha_pago);

                    if ($fechaPago->lt($fechaLimite)) {
                        $estado = 'Adelantado';
                    } elseif ($fechaPago->gt($fechaLimite)) {
                        $estado = 'Atrasado';
                    } else {
                        $estado = 'Pagado';
                    }
                } else {
                    $estado = $fechaLimite->isPast() ? 'Atrasado' : 'Por pagar';
                }

                return [
                    'semana' => $pago->semana,
                    'monto' => (float) $pago->monto_proyectado,
                    'estado' => $estado,
                ];
            })
            ->values();

        $datoContacto = $credito->datoContacto;
        $clienteDireccion = $datoContacto
            ? collect([
                trim($datoContacto->calle . ' ' . $datoContacto->numero_ext),
                $datoContacto->numero_int ? 'Int. ' . $datoContacto->numero_int : null,
                $datoContacto->colonia,
                $datoContacto->municipio,
                $datoContacto->estado,
                $datoContacto->cp ? 'CP ' . $datoContacto->cp : null,
            ])->filter()->implode(', ')
            : null;

        $clienteTelefonos = $datoContacto
            ? collect([$datoContacto->tel_cel, $datoContacto->tel_fijo])->filter()->unique()->values()
            : collect();

        $garantiasCliente = $credito->garantias
            ->filter(fn ($garantia) => Str::lower((string) $garantia->propietario) === 'cliente')
            ->map(function ($garantia) {
                $descripcion = collect([
                    $garantia->tipo,
                    $garantia->marca,
                    $garantia->modelo,
                    $garantia->num_serie,
                ])->filter()->implode(' - ');

                return [
                    'descripcion' => $descripcion !== '' ? $descripcion : ($garantia->tipo ?? 'Garantía'),
                    'monto' => (float) $garantia->monto_garantizado,
                    'foto_url' => $garantia->foto_url,
                ];
            })
            ->values();

        $garantiasAval = $credito->garantias
            ->filter(fn ($garantia) => Str::lower((string) $garantia->propietario) === 'aval')
            ->map(function ($garantia) {
                $descripcion = collect([
                    $garantia->tipo,
                    $garantia->marca,
                    $garantia->modelo,
                    $garantia->num_serie,
                ])->filter()->implode(' - ');

                return [
                    'descripcion' => $descripcion !== '' ? $descripcion : ($garantia->tipo ?? 'Garantía'),
                    'monto' => (float) $garantia->monto_garantizado,
                    'foto_url' => $garantia->foto_url,
                ];
            })
            ->values();

        $documentosCliente = $cliente->documentos
            ->map(fn ($documento) => [
                'titulo' => (string) Str::of($documento->tipo_doc)->replace('_', ' ')->title(),
                'url' => $documento->url_s3,
            ])
            ->values();

        $documentosAval = $credito->avales
            ->flatMap(function ($aval) {
                $avalNombre = collect([
                    $aval->nombre,
                    $aval->apellido_p,
                    $aval->apellido_m,
                ])->filter()->implode(' ');

                return $aval->documentos->map(function ($documento) use ($avalNombre) {
                    $tituloDocumento = (string) Str::of($documento->tipo_doc)->replace('_', ' ')->title();

                    return [
                        'titulo' => $avalNombre
                            ? trim($avalNombre . ' - ' . $tituloDocumento)
                            : $tituloDocumento,
                        'url' => $documento->url_s3,
                    ];
                });
            })
            ->values();

        $aval = $credito->avales->first();

        $clienteNombre = collect([
            $cliente->nombre,
            $cliente->apellido_p,
            $cliente->apellido_m,
        ])->filter()->implode(' ');

        $promotor = $cliente->promotor;
        $promotorNombre = $promotor
            ? collect([
                $promotor->nombre,
                $promotor->apellido_p,
                $promotor->apellido_m,
            ])->filter()->implode(' ')
            : '';

        $supervisorNombre = collect([
            $supervisor->nombre,
            $supervisor->apellido_p,
            $supervisor->apellido_m,
        ])->filter()->implode(' ');

        $avalNombre = $aval
            ? collect([
                $aval->nombre,
                $aval->apellido_p,
                $aval->apellido_m,
            ])->filter()->implode(' ')
            : '';

        $avalDireccion = $aval?->direccion;
        $avalTelefonos = $aval
            ? collect([$aval->telefono])->filter()->unique()->values()
            : collect();

        $fechaCreditoTexto = $fechaCredito
            ? $fechaCredito->clone()->locale('es')->translatedFormat('j \de F \de Y')
            : null;

        $montoCredito = (float) $credito->monto_total;

        return view('mobile.supervisor.cartera.cliente_historial', compact(
            'clienteNombre',
            'supervisorNombre',
            'promotorNombre',
            'totalWeeks',
            'currentWeek',
            'fechaCreditoTexto',
            'montoCredito',
            'clienteDireccion',
            'clienteTelefonos',
            'garantiasCliente',
            'documentosCliente',
            'avalNombre',
            'avalDireccion',
            'avalTelefonos',
            'garantiasAval',
            'documentosAval',
            'semanas'
        ));
    }

    public function cartera_activa()
    {
        return view('mobile.supervisor.cartera.cartera_activa');
    }

    public function cartera_vencida()
    {
        return view('mobile.supervisor.cartera.cartera_vencida');
    }

    public function cartera_inactiva()
    {
        return view('mobile.supervisor.cartera.cartera_inactiva');
    }

    public function cartera_falla()
    {
        return view('mobile.supervisor.cartera.cartera_falla');
    }

    public function cartera_historial_promotor()
    {
        return view('mobile.supervisor.cartera.historial_promotor');
    }

    public function cartera_reacreditacion()
    {
        return view('mobile.supervisor.cartera.reacreditacion');
    }

    public function busqueda()
    {
        return view('mobile.supervisor.busqueda.busqueda');
    }

    public function apertura()
    {
        return view('mobile.supervisor.apertura.apertura');
    }
}
