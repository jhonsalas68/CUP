<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Docente extends Model
{
    use SoftDeletes;

    protected $table = 'docentes';

    protected $fillable = [
        'user_id',
        'ci',
        'telefono',
        'especialidad',
        'disponibilidad_horaria',
        'formacion_academica',
    ];

    protected $casts = [
        'disponibilidad_horaria' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'asignaciones_docente', 'docente_id', 'grupo_id')
                    ->withTimestamps();
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'docente_materia', 'docente_id', 'materia_id')
                    ->withTimestamps();
    }
}
