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

    public function getActiveGestionDocentesNamesAttribute()
    {
        $activeGestion = \App\Models\Gestion::where('activo', true)->first();
        if (!$activeGestion) return 'No asignado';
        
        $groups = $this->grupos->where('gestion_id', $activeGestion->id);
        $docenteNames = $groups->flatMap(fn($g) => $g->docentes)->pluck('nombre')->unique();
        return $docenteNames->isNotEmpty() ? $docenteNames->implode(', ') : 'No asignado';
    }

    public function getActiveGestionAlumnosNamesAttribute()
    {
        $activeGestion = \App\Models\Gestion::where('activo', true)->first();
        if (!$activeGestion) return 'Ninguno';
        
        $groups = $this->grupos->where('gestion_id', $activeGestion->id);
        $alumnoNames = $groups->flatMap(fn($g) => $g->postulantes)->pluck('nombres_apellidos')->unique();
        return $alumnoNames->isNotEmpty() ? $alumnoNames->implode(', ') : 'Ninguno';
    }
}
