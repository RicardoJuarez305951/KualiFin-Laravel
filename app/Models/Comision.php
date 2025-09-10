<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comision extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;
    protected $table = 'comisiones';

    public function comisionable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'comisionable_type', 'comisionable_id');
    }
}
