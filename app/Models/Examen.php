<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Examen extends Model
{
    use SoftDeletes;

    protected $table = 'examenes';

    protected $fillable = [
        'nombre',
        'materia_id',
        'gestion_id',
        'ponderacion',
        'fecha',
    ];

    protected $casts = [
        'ponderacion' => 'float',
        'fecha' => 'date',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'gestion_id');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'examen_id');
    }
}
