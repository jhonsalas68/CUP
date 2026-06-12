<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostulanteGrupo extends Model
{
    protected $table = 'postulante_grupo';

    protected $fillable = [
        'postulante_id',
        'grupo_id',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }
}
