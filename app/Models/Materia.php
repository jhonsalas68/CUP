<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materia extends Model
{
    use SoftDeletes;

    protected $table = 'materias';

    protected $fillable = [
        'nombre',
        'sigla',
        'carrera_id',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'materia_id');
    }

    public function examenes()
    {
        return $this->hasMany(Examen::class, 'materia_id');
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'docente_materia', 'materia_id', 'docente_id')
                    ->withTimestamps();
    }
}
