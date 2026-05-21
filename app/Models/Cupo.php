<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cupo extends Model
{
    use SoftDeletes;

    protected $table = 'cupos';

    protected $fillable = [
        'carrera_id',
        'gestion_id',
        'cantidad_primera_opcion',
        'cantidad_segunda_opcion',
    ];

    protected $casts = [
        'cantidad_primera_opcion' => 'integer',
        'cantidad_segunda_opcion' => 'integer',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'gestion_id');
    }
}
