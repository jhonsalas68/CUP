<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horarios';

    protected $fillable = [
        'grupo_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'aula',
        'aula_id',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function aulaRel()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }
}
