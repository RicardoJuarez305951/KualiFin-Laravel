<?php

namespace App\Http\Controllers;

use App\Models\DocumentoCliente;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoClienteController extends Controller
{
    // Listar todos los documentos (JSON)
    public function index()
    {
        return response()->json(DocumentoCliente::all());
    }

    // Guardar un nuevo documento para un cliente
    public function store(Request $request)
    {
        // 1) Validamos datos básicos + archivo
        $validated = $request->validate([
            'cliente_id'   => 'required|exists:clientes,id',
            'credito_id'   => 'nullable|exists:creditos,id',
            'tipo_doc'     => 'required|string|max:20',
            'documento'    => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // 2) Buscamos el cliente para obtener su nombre/apellido
        $cliente = Cliente::findOrFail($validated['cliente_id']);
        $folderName = Str::slug($cliente->nombre . '_' . $cliente->apellido_p);

        // 3) Procesamos el archivo
        $file     = $request->file('documento');
        $tipo     = $validated['tipo_doc'];
        // Ruta: clientes/nombre_apellido/{$tipo}/archivo.ext
        $path     = $file->store("clientes/{$folderName}/{$tipo}", 'public');
        $original = $file->getClientOriginalName();

        // 4) Creamos el registro en DB
        $doc = DocumentoCliente::create([
            'cliente_id'   => $validated['cliente_id'],
            'credito_id'   => $validated['credito_id'] ?? null,
            'tipo_doc'     => $tipo,
            'url_s3'       => $path,
            'nombre_arch'  => $original,
        ]);

        // 5) Respondemos con el nuevo documento
        return response()->json($doc, 201);
    }

    // Mostrar un documento
    public function show($id)
    {
        return response()->json(DocumentoCliente::findOrFail($id));
    }

    // Actualizar metadatos (no re-subir archivo)
    public function update(Request $request, $id)
    {
        $doc = DocumentoCliente::findOrFail($id);

        $validated = $request->validate([
            'tipo_doc'    => 'sometimes|required|string|max:20',
            'nombre_arch' => 'sometimes|required|string|max:150',
            'documento'   => 'sometimes|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Si hay archivo nuevo, procesarlo
        if ($request->hasFile('documento')) {
            // 1) Borrar el anterior
            Storage::disk('public')->delete($doc->url_s3);

            // 2) Determinar carpeta del cliente
            $cliente    = $doc->cliente;
            $folderName = Str::slug($cliente->nombre . '_' . $cliente->apellido_p);

            // 3) Determinar tipo de documento (nuevo o existente)
            $tipo = $validated['tipo_doc'] ?? $doc->tipo_doc;

            // 4) Guardar el nuevo archivo
            $file = $request->file('documento');
            $path = $file->store("clientes/{$folderName}/{$tipo}", 'public');

            // 5) Actualizar datos de ruta y nombre
            $validated['url_s3']     = $path;
            $validated['nombre_arch'] = $file->getClientOriginalName();
        }

        // 6) Aplicar la actualización
        $doc->update($validated);

        return response()->json($doc);
    }


    // Eliminar un documento
    public function destroy($id)
    {
        $doc = DocumentoCliente::findOrFail($id);

        // Borrar el archivo físico
        Storage::disk('public')->delete($doc->url_s3);

        $doc->delete();

        return response()->json(null, 204);
    }
}
