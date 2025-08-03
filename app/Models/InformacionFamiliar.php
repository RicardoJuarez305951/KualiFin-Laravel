<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformacionFamiliar extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }
}
