<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotor extends Model
{
    use HasFactory;
    protected $guarded = [];
<<<<<<< ours
<<<<<<< ours
<<<<<<< ours
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
=======
>>>>>>> theirs
    protected $table = 'promotores';
=======
>>>>>>> theirs
=======
    protected $table = 'promotores';
>>>>>>> theirs

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function inversiones()
    {
        return $this->hasMany(Inversion::class);
    }

    public function comisiones()
    {
        return $this->morphMany(Comision::class, 'comisionable');
    }
}
