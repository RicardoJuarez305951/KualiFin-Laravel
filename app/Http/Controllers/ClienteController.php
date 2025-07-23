<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClienteController extends Controller
{
    // Mostrar formulario
    public function create()
    {
        return Inertia::render('nuevoCliente'); 
    }

    // Listar todos (JSON)
    public function index()
    {
        return response()->json(Cliente::with('documentos')->get());
    }

    // Guardar nuevo cliente + documentos
    public function store(Request $request)
    {
        // 1) Validación
        $validated = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido_p'       => 'required|string|max:100',
            'apellido_m'       => 'nullable|string|max:100',
            'curp'             => 'required|string|size:18|unique:clientes,curp',
            'fecha_nac'        => 'required|date',
            'sexo'             => 'required|string|max:10',
            'estado_civil'     => 'required|string|max:20',
            'activo'           => 'required|boolean',
            'INE_doc'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'CURP_doc'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'comprobante_doc'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // 2) Calculamos edad
        $validated['edad'] = Carbon::parse($validated['fecha_nac'])->age;

        // 3) Creamos el cliente
        $cliente = Cliente::create([
            'nombre'        => $validated['nombre'],
            'apellido_p'    => $validated['apellido_p'],
            'apellido_m'    => $validated['apellido_m'] ?? null,
            'curp'          => $validated['curp'],
            'fecha_nac'     => $validated['fecha_nac'],
            'sexo'          => $validated['sexo'],
            'estado_civil'  => $validated['estado_civil'],
            'edad'          => $validated['edad'],
            'activo'        => $validated['activo'],
        ]);

        // 4) Generamos nombre de carpeta: nombre_apellidoP
        $folderName = Str::slug($cliente->nombre . '_' . $cliente->apellido_p);

        // 5) Manejo de archivos y relación documentos
        foreach ([
            'INE_doc'         => 'ine',
            'CURP_doc'        => 'curp',
            'comprobante_doc' => 'comprobante',
        ] as $field => $tipo) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                // Guardamos en storage/app/public/clientes/{folderName}/{tipo}/
                $path = $file->store("clientes/{$folderName}/{$tipo}", 'public');

                $cliente->documentos()->create([
                    'tipo_doc'    => $tipo,
                    'url_s3'      => $path,
                    'nombre_arch' => $file->getClientOriginalName(),
                ]);
            }
        }

        // 6) Redirigir con flash
        return redirect()
            ->route('nuevoCliente')
            ->with('success', "Cliente y documentos guardados en carpeta {$folderName}");
    }

    // Mostrar uno
    public function show($id)
    {
        return response()->json(Cliente::with('documentos')->findOrFail($id));
    }

    // Actualizar
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'nombre'      => 'sometimes|required|string|max:100',
            'apellido_p'  => 'sometimes|required|string|max:100',
            'apellido_m'  => 'sometimes|nullable|string|max:100',
            'curp'        => 'sometimes|required|string|size:18|unique:clientes,curp,' . $id,
            'fecha_nac'   => 'sometimes|required|date',
            'sexo'        => 'sometimes|required|string|max:10',
            'estado_civil'=> 'sometimes|required|string|max:20',
            'edad'        => 'sometimes|required|integer',
            'activo'      => 'sometimes|required|boolean',
        ]);

        $cliente->update($validated);

        return response()->json($cliente);
    }

    // Eliminar
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        return response()->json(null, 204);
    }
}
