<?php
namespace App\Http\Controllers;

use App\Models\DocumentoAval;
use Illuminate\Http\Request;

class DocumentoAvalController extends Controller
{
    public function index()
    {
        $docs = DocumentoAval::all();
        return view('documentos_avales.index', compact('docs'));
    }

    public function create()
    {
        return view('documentos_avales.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'aval_id'   => 'required|exists:avales,id',
            'tipo_doc'  => 'required|string',
            'url_s3'    => 'required|url',
            'nombre_arch'=> 'required|string',
        ]);

        DocumentoAval::create($data);
        return redirect()->route('documentos_avales.index');
    }

    public function show(DocumentoAval $documentoAval)
    {
        return view('documentos_avales.show', compact('documentoAval'));
    }

    public function edit(DocumentoAval $documentoAval)
    {
        return view('documentos_avales.edit', compact('documentoAval'));
    }

    public function update(Request $request, DocumentoAval $documentoAval)
    {
        $data = $request->validate([
            'tipo_doc'   => 'required|string',
            'url_s3'     => 'required|url',
            'nombre_arch'=> 'required|string',
        ]);

        $documentoAval->update($data);
        return redirect()->route('documentos_avales.index');
    }

    public function destroy(DocumentoAval $documentoAval)
    {
        $documentoAval->delete();
        return redirect()->route('documentos_avales.index');
    }
}
