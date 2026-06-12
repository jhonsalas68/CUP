<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Docente extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'docentes';

    protected static function booted()
    {
        static::saving(function ($docente) {
            if (empty($docente->nombre) && $docente->user_id) {
                $docente->nombre = $docente->user?->name;
            }
        });
    }

    protected $fillable = [
        'user_id',
        'nombre',
        'ci',
        'telefono',
        'especialidad',
        'disponibilidad_horaria',
        'formacion_academica',
        'profesional_area',
        'tiene_maestria',
        'tiene_diplomado',
    ];

    protected $casts = [
        'disponibilidad_horaria' => 'array',
        'profesional_area' => 'boolean',
        'tiene_maestria' => 'boolean',
        'tiene_diplomado' => 'boolean',
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
