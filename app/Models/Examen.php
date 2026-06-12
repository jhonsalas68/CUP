<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Examen extends Model
{
    use SoftDeletes, LogsActivity;

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

    public function getDocentesNamesAttribute()
    {
        $groups = $this->materia?->grupos?->where('gestion_id', $this->gestion_id) ?? collect();
        $docenteNames = $groups->flatMap(fn($g) => $g->docentes)->pluck('nombre')->unique();
        return $docenteNames->isNotEmpty() ? $docenteNames->implode(', ') : 'No asignado';
    }

    public function getAlumnosNamesAttribute()
    {
        $groups = $this->materia?->grupos?->where('gestion_id', $this->gestion_id) ?? collect();
        $alumnoNames = $groups->flatMap(fn($g) => $g->postulantes)->pluck('nombres_apellidos')->unique();
        return $alumnoNames->isNotEmpty() ? $alumnoNames->implode(', ') : 'Ninguno';
    }
}
