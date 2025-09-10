<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoCliente extends Model
{
    use HasFactory;
    protected $guarded = [];
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;
    protected $table = 'documentos_clientes';

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }
}
