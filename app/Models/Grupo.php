<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grupo extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'grupos';

    protected $fillable = [
        'nombre',
        'materia_id',
        'gestion_id',
        'cupo_maximo',
    ];

    protected $casts = [
        'cupo_maximo' => 'integer',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'gestion_id');
    }

    public function postulantes()
    {
        return $this->belongsToMany(Postulante::class, 'postulante_grupo', 'grupo_id', 'postulante_id')
            ->withPivot('nro_asiento')
            ->withTimestamps();
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'asignaciones_docente', 'grupo_id', 'docente_id')
            ->withTimestamps();
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'grupo_id');
    }
}
