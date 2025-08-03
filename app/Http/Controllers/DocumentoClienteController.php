<?php
namespace App\Http\Controllers;

use App\Models\DocumentoCliente;
use Illuminate\Http\Request;

class DocumentoClienteController extends Controller
{
    public function index()
    {
        $docs = DocumentoCliente::all();
        return view('documentos_clientes.index', compact('docs'));
    }

    public function create()
    {
        return view('documentos_clientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'credito_id' => 'required|exists:creditos,id',
            'tipo_doc'   => 'required|string',
            'url_s3'     => 'required|url',
            'nombre_arch'=> 'required|string',
        ]);

        DocumentoCliente::create($data);
        return redirect()->route('documentos_clientes.index');
    }

    public function show(DocumentoCliente $documentoCliente)
    {
        return view('documentos_clientes.show', compact('documentoCliente'));
    }

    public function edit(DocumentoCliente $documentoCliente)
    {
        return view('documentos_clientes.edit', compact('documentoCliente'));
    }

    public function update(Request $request, DocumentoCliente $documentoCliente)
    {
        $data = $request->validate([
            'tipo_doc'   => 'required|string',
            'url_s3'     => 'required|url',
            'nombre_arch'=> 'required|string',
        ]);

        $documentoCliente->update($data);
        return redirect()->route('documentos_clientes.index');
    }

    public function destroy(DocumentoCliente $documentoCliente)
    {
        $documentoCliente->delete();
        return redirect()->route('documentos_clientes.index');
    }
}
