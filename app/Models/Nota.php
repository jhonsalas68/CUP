<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $table = 'notas';

    protected $fillable = [
        'postulante_id',
        'examen_id',
        'puntaje',
        'registrado_por',
    ];

    protected $casts = [
        'puntaje' => 'float',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class, 'examen_id');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
