<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrera extends Model
{
    use SoftDeletes;

    protected $table = 'carreras';

    protected $fillable = [
        'nombre',
        'sigla',
    ];

    public function cupos()
    {
        return $this->hasMany(Cupo::class, 'carrera_id');
    }

    public function materias()
    {
        return $this->hasMany(Materia::class, 'carrera_id');
    }

    public function postulantesPrimeraOpn()
    {
        return $this->hasMany(Postulante::class, 'carrera_primera_opcion_id');
    }

    public function postulantesSegundaOpn()
    {
        return $this->hasMany(Postulante::class, 'carrera_segunda_opcion_id');
    }
}
