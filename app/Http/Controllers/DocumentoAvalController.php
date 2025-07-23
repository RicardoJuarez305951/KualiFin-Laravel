<?php

namespace App\Http\Controllers;

use App\Models\DocumentoAval;
use Illuminate\Http\Request;

class DocumentoAvalController extends Controller
{
    public function index() { return response()->json(DocumentoAval::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'aval_id' => 'required|exists:avales,id',
            'tipo_doc' => 'required|string|max:20',
            'url_s3' => 'required|string|max:255',
            'nombre_arch' => 'required|string|max:150',
            'creado_en' => 'nullable|date',
        ]);
        $doc = DocumentoAval::create($validated);
        return response()->json($doc, 201);
    }
    public function show($id) { return response()->json(DocumentoAval::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $doc = DocumentoAval::findOrFail($id);
        $validated = $request->validate([
            'aval_id' => 'sometimes|exists:avales,id',
            'tipo_doc' => 'sometimes|string|max:20',
            'url_s3' => 'sometimes|string|max:255',
            'nombre_arch' => 'sometimes|string|max:150',
            'creado_en' => 'nullable|date',
        ]);
        $doc->update($validated);
        return response()->json($doc);
    }
    public function destroy($id)
    {
        $doc = DocumentoAval::findOrFail($id);
        $doc->delete();
        return response()->json(null, 204);
    }
}
