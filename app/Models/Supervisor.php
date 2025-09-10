<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    use HasFactory;
    protected $guarded = [];
<<<<<<< ours
<<<<<<< ours
<<<<<<< ours
    public $timestamps = false;
=======
>>>>>>> theirs
    protected $table = 'supervisores';
=======
>>>>>>> theirs
=======
    protected $table = 'supervisores';
>>>>>>> theirs

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ejecutivo()
    {
        return $this->belongsTo(Ejecutivo::class);
    }

    public function promotores()
    {
        return $this->hasMany(Promotor::class);
    }

    public function ejercicios()
    {
        return $this->hasMany(Ejercicio::class);
    }

    public function comisiones()
    {
        return $this->morphMany(Comision::class, 'comisionable');
    }
}
